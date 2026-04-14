<?php

/**
 * إعدادات Echo/Reverb للمتصفح — تُحقَن في الصفحة (window.__echoReverbConfig)
 * لتعمل تعديلات .env على السيرفر دون الاعتماد فقط على قيم Vite المخزّنة في build.
 *
 * الأولوية: VITE_* ثم REVERB_*.
 */
return [
    'app_key' => (string) (env('VITE_REVERB_APP_KEY') ?: env('REVERB_APP_KEY', '')),
    'host' => (string) (env('VITE_REVERB_HOST') ?: env('REVERB_HOST', 'localhost')),
    'port' => (int) (filled(env('VITE_REVERB_PORT')) ? env('VITE_REVERB_PORT') : env('REVERB_PORT', 443)),
    'scheme' => (string) (env('VITE_REVERB_SCHEME') ?: env('REVERB_SCHEME', 'https')),
];
