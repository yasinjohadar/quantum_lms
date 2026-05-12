<?php

namespace Tests\Unit;

use App\Enums\StorageDriverMode;
use App\Services\Storage\StorageDriverModeResolver;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class StorageDriverModeResolverTest extends TestCase
{
    protected function tearDown(): void
    {
        Config::set('storage.driver_mode', 'cloud_first');
        Config::set('storage.cloud_first', true);
        parent::tearDown();
    }

    public function test_resolves_from_driver_mode_env_string(): void
    {
        Config::set('storage.driver_mode', 'local_only');
        Config::set('storage.cloud_first', true);

        $this->assertSame(StorageDriverMode::LOCAL_ONLY, StorageDriverModeResolver::current());
    }

    public function test_legacy_cloud_first_false_maps_to_local_only(): void
    {
        Config::set('storage.driver_mode', 'invalid_mode_value');
        Config::set('storage.cloud_first', false);

        $this->assertSame(StorageDriverMode::LOCAL_ONLY, StorageDriverModeResolver::current());
    }

    public function test_legacy_cloud_first_true_defaults_to_cloud_first(): void
    {
        Config::set('storage.driver_mode', 'invalid_mode_value');
        Config::set('storage.cloud_first', true);

        $this->assertSame(StorageDriverMode::CLOUD_FIRST, StorageDriverModeResolver::current());
    }
}
