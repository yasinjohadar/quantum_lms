<?php

use App\Support\SafeEvent;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

uses(TestCase::class);

test('safe event does not throw when dispatch fails', function () {
    Event::shouldReceive('dispatch')
        ->once()
        ->andThrow(new RuntimeException('Pusher error: cURL error 7'));

    SafeEvent::dispatch(new stdClass);

    expect(true)->toBeTrue();
});
