<?php

namespace App\DTOs\Zoom;

class MeetingSettingsDTO
{
    public function __construct(
        public bool $waiting_room = true,
        public bool $join_before_host = false,
        public bool $participant_video = false,
        public bool $mute_upon_entry = true,
        public int $approval_type = 2, // 0 = Auto, 1 = Manual, 2 = No registration
        public int $registration_type = 0,
        public string $audio = 'both', // both, telephony, voip
        public string $auto_recording = 'none', // local, cloud, none
        public bool $enforce_login = false,
        public string $enforce_login_domains = '',
        public string $alternative_hosts = '',
        public bool $alternative_hosts_email_notification = true,
        public bool $close_registration = false,
        public bool $show_share_button = true,
        public bool $allow_multiple_devices = false,
        public bool $registrants_confirmation_email = true,
        public array $waiting_room_settings = [],
    ) {
    }

    /**
     * Convert to array for Zoom API
     */
    public function toArray(): array
    {
        return [
            'waiting_room' => $this->waiting_room,
            'join_before_host' => $this->join_before_host,
            'participant_video' => $this->participant_video,
            'mute_upon_entry' => $this->mute_upon_entry,
            'approval_type' => $this->approval_type,
            'registration_type' => $this->registration_type,
            'audio' => $this->audio,
            'auto_recording' => $this->auto_recording,
            'enforce_login' => $this->enforce_login,
            'enforce_login_domains' => $this->enforce_login_domains,
            'alternative_hosts' => $this->alternative_hosts,
            'alternative_hosts_email_notification' => $this->alternative_hosts_email_notification,
            'close_registration' => $this->close_registration,
            'show_share_button' => $this->show_share_button,
            'allow_multiple_devices' => $this->allow_multiple_devices,
            'registrants_confirmation_email' => $this->registrants_confirmation_email,
            'waiting_room_settings' => $this->waiting_room_settings,
        ];
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            waiting_room: $data['waiting_room'] ?? true,
            join_before_host: $data['join_before_host'] ?? false,
            participant_video: $data['participant_video'] ?? false,
            mute_upon_entry: $data['mute_upon_entry'] ?? true,
            approval_type: $data['approval_type'] ?? 2,
            registration_type: $data['registration_type'] ?? 0,
            audio: $data['audio'] ?? 'both',
            auto_recording: $data['auto_recording'] ?? 'none',
            enforce_login: $data['enforce_login'] ?? false,
            enforce_login_domains: $data['enforce_login_domains'] ?? '',
            alternative_hosts: $data['alternative_hosts'] ?? '',
            alternative_hosts_email_notification: $data['alternative_hosts_email_notification'] ?? true,
            close_registration: $data['close_registration'] ?? false,
            show_share_button: $data['show_share_button'] ?? true,
            allow_multiple_devices: $data['allow_multiple_devices'] ?? false,
            registrants_confirmation_email: $data['registrants_confirmation_email'] ?? true,
            waiting_room_settings: $data['waiting_room_settings'] ?? [],
        );
    }
}




