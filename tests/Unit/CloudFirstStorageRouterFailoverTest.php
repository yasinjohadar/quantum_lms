<?php

namespace Tests\Unit;

use App\Models\AppStorageConfig;
use App\Models\StorageDiskMapping;
use App\Services\Storage\CloudFirstStorageRouter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_sqlite')]
class CloudFirstStorageRouterFailoverTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_falls_back_to_secondary_storage_when_primary_fails(): void
    {
        $primary = AppStorageConfig::create([
            'name' => 'failing-s3',
            'driver' => 's3',
            'config' => [
                'access_key_id' => 'invalid',
                'secret_access_key' => 'invalid',
                'region' => 'us-east-1',
                'bucket' => 'none',
                'endpoint' => 'http://127.0.0.1:1',
                'use_path_style' => true,
            ],
            'is_active' => true,
            'priority' => 10,
            'redundancy' => false,
        ]);

        $fallback = AppStorageConfig::create([
            'name' => 'local-fallback',
            'driver' => 'local',
            'config' => ['path' => 'public'],
            'is_active' => true,
            'priority' => 0,
            'redundancy' => false,
        ]);

        $diskName = 'img_failover_'.uniqid();

        StorageDiskMapping::create([
            'disk_name' => $diskName,
            'label' => 'Images',
            'primary_storage_id' => $primary->id,
            'fallback_storage_ids' => [$fallback->id],
            'is_active' => true,
        ]);

        $router = app(CloudFirstStorageRouter::class);
        $file = UploadedFile::fake()->image('probe.jpg', 10, 10);
        $path = 'subjects/images/'.uniqid('up_', true).'.jpg';

        $result = $router->uploadToDisk($diskName, $file, $path);

        $this->assertTrue($result['success']);
        $this->assertSame('local', $result['storage_type'] ?? '');
    }
}
