<?php

namespace App\Services\WhatsApp;

use App\DTOs\WhatsApp\SendMessageResponseDTO;

/**
 * Interface for WhatsApp providers
 */
interface WhatsAppProviderService
{
    /**
     * Send text message
     *
     * @param string $to Phone number
     * @param string $text Message content
     * @param bool $previewUrl Enable URL preview
     * @return SendMessageResponseDTO
     */
    public function sendText(string $to, string $text, bool $previewUrl = false): SendMessageResponseDTO;

    /**
     * Send template message
     *
     * @param string $to Phone number
     * @param string $templateName Template name
     * @param string $language Language code
     * @param array $components Template components
     * @return SendMessageResponseDTO
     */
    public function sendTemplate(string $to, string $templateName, string $language = 'ar', array $components = []): SendMessageResponseDTO;

    /**
     * Test connection to WhatsApp provider
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function testConnection(): array;
}

