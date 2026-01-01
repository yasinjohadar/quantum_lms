<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Zoom Account Credentials
    |--------------------------------------------------------------------------
    |
    | Server-to-Server OAuth credentials for Zoom API
    |
    */
    'account_id' => env('ZOOM_ACCOUNT_ID'),
    'client_id' => env('ZOOM_CLIENT_ID'),
    'client_secret' => env('ZOOM_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Zoom Meeting SDK Credentials
    |--------------------------------------------------------------------------
    |
    | Credentials for generating join signatures
    |
    */
    'sdk_key' => env('ZOOM_MEETING_SDK_KEY'),
    'sdk_secret' => env('ZOOM_MEETING_SDK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Zoom API Configuration
    |--------------------------------------------------------------------------
    |
    */
    'api_base_url' => env('ZOOM_API_BASE_URL', 'https://api.zoom.us/v2'),

    /*
    |--------------------------------------------------------------------------
    | Token Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache access tokens to avoid frequent API calls
    |
    */
    'token_cache_ttl' => env('ZOOM_TOKEN_CACHE_TTL', 3600), // 1 hour default

    /*
    |--------------------------------------------------------------------------
    | Join Window Configuration
    |--------------------------------------------------------------------------
    |
    | Time window for allowing students to join sessions
    |
    */
    'join_window_before_minutes' => env('ZOOM_JOIN_WINDOW_BEFORE_MINUTES', 10),
    'join_window_after_minutes' => env('ZOOM_JOIN_WINDOW_AFTER_MINUTES', 15),

    /*
    |--------------------------------------------------------------------------
    | Join Token Configuration
    |--------------------------------------------------------------------------
    |
    */
    'token_ttl_minutes' => env('ZOOM_TOKEN_TTL_MINUTES', 5),
    'token_max_uses' => env('ZOOM_TOKEN_MAX_USES', 1),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limits for join token requests
    |
    */
    'rate_limits' => [
        'per_user' => env('ZOOM_RATE_LIMIT_PER_USER', 10), // requests per minute
        'per_session' => env('ZOOM_RATE_LIMIT_PER_SESSION', 50), // requests per minute
        'per_ip' => env('ZOOM_RATE_LIMIT_PER_IP', 20), // requests per minute
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Meeting Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for Zoom meetings
    |
    */
    'default_meeting_settings' => [
        'waiting_room' => true,
        'join_before_host' => false,
        'participant_video' => false,
        'mute_upon_entry' => true,
        'approval_type' => 2, // 0 = Automatically approve, 1 = Manually approve, 2 = No registration required
        'registration_type' => 0, // 0 = Attendees register once and can attend any occurrence, 1 = Attendees must register each occurrence, 2 = Attendees register once and can select one or more occurrences
        'audio' => 'both', // both, telephony, voip
        'auto_recording' => 'none', // local, cloud, none
        'enforce_login' => false,
        'enforce_login_domains' => '',
        'alternative_hosts' => '',
        'alternative_hosts_email_notification' => true,
        'close_registration' => false,
        'show_share_button' => true,
        'allow_multiple_devices' => false,
        'registrants_confirmation_email' => true,
        'waiting_room_settings' => [
            'participants_to_place_in_waiting_room' => 0, // 0 = All participants, 1 = Users not in your account, 2 = Users not in your account and not part of your allowed domains
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    */
    'security' => [
        'enable_device_binding' => env('ZOOM_ENABLE_DEVICE_BINDING', true),
        'enable_ip_binding' => env('ZOOM_ENABLE_IP_BINDING', true),
        'ip_prefix_length' => env('ZOOM_IP_PREFIX_LENGTH', 3), // First 3 octets
    ],
];


