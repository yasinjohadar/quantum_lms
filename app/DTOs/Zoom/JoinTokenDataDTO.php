<?php

namespace App\DTOs\Zoom;

class JoinTokenDataDTO
{
    public function __construct(
        public string $meetingNumber,
        public string $userName,
        public string $userEmail,
        public string $signature,
        public ?string $passcode = null,
        public int $role = 0, // 0 = participant, 1 = host
        public string $sdkKey,
    ) {
    }

    /**
     * Convert to array for frontend
     */
    public function toArray(): array
    {
        return [
            'meetingNumber' => $this->meetingNumber,
            'userName' => $this->userName,
            'userEmail' => $this->userEmail,
            'signature' => $this->signature,
            'passcode' => $this->passcode,
            'role' => $this->role,
            'sdkKey' => $this->sdkKey,
        ];
    }
}



