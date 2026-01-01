<?php

namespace App\Services\SMS\Providers;

use App\Services\SMS\SMSProviderService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class TwilioProvider implements SMSProviderService
{
    protected string $accountSid;
    protected string $authToken;
    protected string $fromNumber;

    public function __construct(array $config)
    {
        $this->accountSid = $config['account_sid'] ?? '';
        $this->authToken = $config['auth_token'] ?? '';
        $this->fromNumber = $config['from_number'] ?? '';
    }

    /**
     * Send SMS message via Twilio
     */
    public function send(string $to, string $message): array
    {
        try {
            // Validate Account SID format (should start with AC)
            if (empty($this->accountSid) || !str_starts_with($this->accountSid, 'AC')) {
                return [
                    'success' => false,
                    'message' => 'Account SID غير صحيح. يجب أن يبدأ بـ AC',
                    'message_id' => null,
                ];
            }

            // Validate From Number format
            if (empty($this->fromNumber)) {
                return [
                    'success' => false,
                    'message' => 'رقم المرسل مطلوب',
                    'message_id' => null,
                ];
            }

            // Clean and format phone numbers
            $to = $this->formatPhoneNumber($to);
            $from = $this->formatPhoneNumber($this->fromNumber);

            // Twilio API endpoint
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json";

            // Make API request
            $response = Http::timeout(30)
                ->withBasicAuth($this->accountSid, $this->authToken)
                ->asForm()
                ->post($url, [
                    'From' => $from,
                    'To' => $to,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                return [
                    'success' => true,
                    'message' => 'تم إرسال الرسالة بنجاح',
                    'message_id' => $responseData['sid'] ?? null,
                ];
            } else {
                $errorMessage = $response->json()['message'] ?? 'فشل إرسال الرسالة';
                
                Log::error('Twilio SMS API Error', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'phone' => $to,
                ]);

                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'message_id' => null,
                ];
            }
        } catch (Exception $e) {
            Log::error('Twilio SMS Send Exception', [
                'message' => $e->getMessage(),
                'phone' => $to,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'خطأ في الاتصال: ' . $e->getMessage(),
                'message_id' => null,
            ];
        }
    }

    /**
     * Test connection to Twilio
     */
    public function testConnection(): array
    {
        try {
            // Validate Account SID format
            if (empty($this->accountSid)) {
                return [
                    'success' => false,
                    'message' => 'Account SID مطلوب',
                ];
            }

            if (!str_starts_with($this->accountSid, 'AC')) {
                return [
                    'success' => false,
                    'message' => 'Account SID غير صحيح. يجب أن يبدأ بـ AC (مثال: ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx)',
                ];
            }

            // Validate Auth Token
            if (empty($this->authToken)) {
                return [
                    'success' => false,
                    'message' => 'Auth Token مطلوب',
                ];
            }

            // Test by fetching account details
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}.json";

            $response = Http::timeout(10)
                ->withBasicAuth($this->accountSid, $this->authToken)
                ->get($url);

            if ($response->successful()) {
                $accountData = $response->json();
                $status = $accountData['status'] ?? 'unknown';
                $friendlyName = $accountData['friendly_name'] ?? '';
                
                return [
                    'success' => true,
                    'message' => "تم الاتصال بنجاح. الحساب: {$friendlyName} (حالة: {$status})",
                ];
            } else {
                $errorData = $response->json();
                $errorCode = $errorData['code'] ?? null;
                $errorMessage = $errorData['message'] ?? 'فشل الاتصال';
                
                // Provide more specific error messages
                if ($errorCode == 20003) {
                    $errorMessage = 'Account SID أو Auth Token غير صحيح';
                } elseif ($errorCode == 20005) {
                    $errorMessage = 'Account SID غير موجود';
                }
                
                Log::error('Twilio Connection Test Failed', [
                    'status' => $response->status(),
                    'code' => $errorCode,
                    'message' => $errorMessage,
                    'response' => $response->body(),
                ]);
                
                return [
                    'success' => false,
                    'message' => 'فشل الاتصال: ' . $errorMessage,
                ];
            }
        } catch (Exception $e) {
            Log::error('Twilio Connection Test Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'فشل الاتصال: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Format phone number to E.164 format (+[country code][number])
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-digit characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // If phone doesn't start with +, format it
        if (!str_starts_with($phone, '+')) {
            // Remove leading zeros
            $phone = ltrim($phone, '0');
            // Add + if not present
            $phone = '+' . $phone;
        }
        
        return $phone;
    }
}

