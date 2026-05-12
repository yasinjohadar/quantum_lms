<?php

namespace App\Enums;

enum StorageDriverMode: string
{
    case LOCAL_ONLY = 'local_only';
    case CLOUD_ONLY = 'cloud_only';
    case CLOUD_FIRST = 'cloud_first';
    case LOCAL_FIRST = 'local_first';
    case DUAL_WRITE = 'dual_write';

    public function label(): string
    {
        return match ($this) {
            self::LOCAL_ONLY => 'Local Only',
            self::CLOUD_ONLY => 'Cloud Only',
            self::CLOUD_FIRST => 'Cloud First (with local fallback)',
            self::LOCAL_FIRST => 'Local First (with cloud sync)',
            self::DUAL_WRITE => 'Dual Write (sync to both)',
        };
    }

    public function shouldAttemptCloudFirst(): bool
    {
        return in_array($this, [self::CLOUD_FIRST, self::CLOUD_ONLY, self::DUAL_WRITE], true);
    }

    public function shouldAttemptLocalFirst(): bool
    {
        return in_array($this, [self::LOCAL_FIRST, self::LOCAL_ONLY, self::DUAL_WRITE], true);
    }

    public function requiresCloud(): bool
    {
        return in_array($this, [self::CLOUD_ONLY, self::CLOUD_FIRST], true);
    }

    public function allowsLocalFallback(): bool
    {
        return in_array($this, [self::CLOUD_FIRST, self::LOCAL_FIRST, self::DUAL_WRITE], true);
    }

    public function performsDualWrite(): bool
    {
        return $this === self::DUAL_WRITE;
    }
}
