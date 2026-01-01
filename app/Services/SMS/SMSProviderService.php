<?php

namespace App\Services\SMS;

interface SMSProviderService
{
    /**
     * Send SMS message
     *
     * @param string $to Phone number
     * @param string $message Message content
     * @return array ['success' => bool, 'message' => string, 'message_id' => string|null]
     */
    public function send(string $to, string $message): array;

    /**
     * Test connection to SMS provider
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function testConnection(): array;
}




