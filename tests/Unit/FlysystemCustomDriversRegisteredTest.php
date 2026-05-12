<?php

namespace Tests\Unit;

use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FlysystemCustomDriversRegisteredTest extends TestCase
{
    public function test_google_dropbox_and_azure_custom_creators_are_registered(): void
    {
        $manager = Storage::getFacadeRoot();
        $this->assertInstanceOf(FilesystemManager::class, $manager);

        $ref = new \ReflectionProperty(FilesystemManager::class, 'customCreators');
        $ref->setAccessible(true);
        /** @var array<string, mixed> $creators */
        $creators = $ref->getValue($manager);

        $this->assertArrayHasKey('google', $creators);
        $this->assertArrayHasKey('dropbox', $creators);
        $this->assertArrayHasKey('azure', $creators);
    }
}
