<?php

namespace App\Services\WhatsApp;

use App\Exceptions\WhatsAppApiException;
use App\Jobs\SendWhatsAppMessageJob;
use App\Models\WhatsAppContact;
use App\Models\WhatsAppMessage;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessage
{
    protected WhatsAppSettingsService $settingsService;

    public function __construct(WhatsAppSettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Send text message
     */
    public function sendText(
        string $to,
        string $text,
        bool $previewUrl = false,
        string $messageCategory = WhatsAppMessage::CATEGORY_SYSTEM
    ): WhatsAppMessage
    {
        // Find or create contact
        $contact = WhatsAppContact::findOrCreateByWaId($to);

        // Create message record with queued status
        $message = WhatsAppMessage::create([
            'direction' => WhatsAppMessage::DIRECTION_OUTBOUND,
            'contact_id' => $contact->id,
            'type' => WhatsAppMessage::TYPE_TEXT,
            'body' => $text,
            'status' => WhatsAppMessage::STATUS_QUEUED,
            'message_category' => $messageCategory,
        ]);

        // Dispatch job to send message
        SendWhatsAppMessageJob::dispatch($message, [
            'type' => 'text',
            'text' => $text,
            'preview_url' => $previewUrl,
        ]);

        return $message;
    }

    /**
     * Send text message immediately (without queue).
     */
    public function sendTextNow(
        string $to,
        string $text,
        bool $previewUrl = false,
        string $messageCategory = WhatsAppMessage::CATEGORY_SYSTEM
    ): WhatsAppMessage
    {
        $contact = WhatsAppContact::findOrCreateByWaId($to);

        $message = WhatsAppMessage::create([
            'direction' => WhatsAppMessage::DIRECTION_OUTBOUND,
            'contact_id' => $contact->id,
            'type' => WhatsAppMessage::TYPE_TEXT,
            'body' => $text,
            'status' => WhatsAppMessage::STATUS_QUEUED,
            'message_category' => $messageCategory,
        ]);

        try {
            $settings = $this->settingsService->getSettings();
            $provider = $settings['whatsapp_provider'] ?? 'meta';
            $config = $this->settingsService->getProviderConfig();
            $providerInstance = WhatsAppProviderFactory::create($provider, $config);

            $response = $providerInstance->sendText($to, $text, $previewUrl);

            $message->update([
                'meta_message_id' => $response->metaMessageId,
                'status' => WhatsAppMessage::STATUS_SENT,
                'payload' => array_merge($message->payload ?? [], [
                    'response' => $response->rawResponse,
                    'delivery_mode' => 'immediate',
                ]),
            ]);

            Log::channel('whatsapp')->info('WhatsApp message sent immediately', [
                'message_id' => $message->id,
                'meta_message_id' => $response->metaMessageId,
                'to' => $to,
            ]);
        } catch (WhatsAppApiException $e) {
            $message->update([
                'status' => WhatsAppMessage::STATUS_FAILED,
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'details' => $e->getDetails(),
                ],
            ]);

            Log::channel('whatsapp')->error('Failed to send immediate WhatsApp message', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
                'details' => $e->getDetails(),
            ]);

            throw $e;
        } catch (\Exception $e) {
            $message->update([
                'status' => WhatsAppMessage::STATUS_FAILED,
                'error' => [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ],
            ]);

            Log::channel('whatsapp')->error('Exception sending immediate WhatsApp message', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        return $message;
    }

    /**
     * Send template message
     */
    public function sendTemplate(
        string $to,
        string $templateName,
        string $language = 'ar',
        array $components = [],
        string $messageCategory = WhatsAppMessage::CATEGORY_SYSTEM
    ): WhatsAppMessage {
        // Find or create contact
        $contact = WhatsAppContact::findOrCreateByWaId($to);

        // Create message record with queued status
        $message = WhatsAppMessage::create([
            'direction' => WhatsAppMessage::DIRECTION_OUTBOUND,
            'contact_id' => $contact->id,
            'type' => WhatsAppMessage::TYPE_TEMPLATE,
            'body' => $templateName,
            'status' => WhatsAppMessage::STATUS_QUEUED,
            'message_category' => $messageCategory,
            'payload' => [
                'template_name' => $templateName,
                'language' => $language,
                'components' => $components,
            ],
        ]);

        // Dispatch job to send message
        SendWhatsAppMessageJob::dispatch($message, [
            'type' => 'template',
            'template_name' => $templateName,
            'language' => $language,
            'components' => $components,
        ]);

        return $message;
    }
}




