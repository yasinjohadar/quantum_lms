<?php

namespace App\Listeners;

use App\Events\WhatsAppMessageReceived;
use App\Services\WhatsApp\SendWhatsAppMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class AutoReplyWhatsAppListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(WhatsAppMessageReceived $event): void
    {
        // Only process text messages
        if ($event->message->type !== 'text' || $event->message->direction !== 'inbound') {
            return;
        }

        // Check if auto-reply is enabled
        $autoReplyEnabled = config('whatsapp.auto_reply', false);
        if (!$autoReplyEnabled) {
            return;
        }

        try {
            $contact = $event->message->contact;
            $autoReplyMessage = config('whatsapp.auto_reply_message', 'شكراً لك، تم استلام رسالتك. سنرد عليك قريباً.');

            $sendService = app(SendWhatsAppMessage::class);
            $sendService->sendText($contact->wa_id, $autoReplyMessage);

            Log::channel('whatsapp')->info('Auto-reply sent', [
                'original_message_id' => $event->message->id,
                'to' => $contact->wa_id,
            ]);
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('Failed to send auto-reply', [
                'original_message_id' => $event->message->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
