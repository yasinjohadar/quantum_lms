<?php

namespace App\Support;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class SafeEvent
{
    /**
     * Dispatch an event without failing the caller if broadcast/Reverb is unavailable.
     */
    public static function dispatch(object $event): void
    {
        try {
            Event::dispatch($event);
        } catch (\Throwable $e) {
            Log::warning('Event dispatch failed (non-fatal)', [
                'event' => $event::class,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
