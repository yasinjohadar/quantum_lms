<?php

namespace App\Services\Media;

use App\Models\Media;
use App\Models\MediaMetric;
use App\Models\MediaConversion;
use App\Models\StorageSyncBatch;
use App\Models\StorageSyncDeadLetter;
use Illuminate\Support\Facades\DB;

class MediaMetricsService
{
    /**
     * تسجيل metric
     */
    public static function record(string $type, string $name, float $value, ?string $unit = null, ?string $provider = null, array $data = []): void
    {
        MediaMetric::create([
            'metric_type' => $type,
            'metric_name' => $name,
            'value' => $value,
            'unit' => $unit,
            'provider' => $provider,
            'data' => $data,
            'recorded_at' => now(),
        ]);
    }

    /**
     * لوحة المراقبة الرئيسية
     */
    public static function getDashboardData(): array
    {
        $now = now();
        $today = $now->copy()->startOfDay();
        $weekAgo = $now->copy()->subDays(7);

        return [
            'overview' => [
                'total_files' => Media::whereNull('deleted_at')->count(),
                'total_size' => Media::whereNull('deleted_at')->sum('size'),
                'total_size_formatted' => self::formatBytes(Media::whereNull('deleted_at')->sum('size')),
                'synced_files' => Media::whereNull('deleted_at')->where('is_synced', true)->count(),
                'pending_sync' => Media::whereNull('deleted_at')->where('is_synced', false)->count(),
                'orphaned_files' => Media::whereNull('deleted_at')->where('reference_count', 0)->count(),
                'soft_deleted' => Media::whereNotNull('deleted_at')->count(),
            ],
            'today' => [
                'uploads' => Media::where('created_at', '>=', $today)->count(),
                'upload_size' => Media::where('created_at', '>=', $today)->sum('size'),
                'upload_size_formatted' => self::formatBytes(Media::where('created_at', '>=', $today)->sum('size')),
            ],
            'sync_health' => [
                'success_rate' => self::getSyncSuccessRate(),
                'failed_conversions' => MediaConversion::where('status', 'failed')->count(),
                'pending_conversions' => MediaConversion::where('status', 'pending')->count(),
                'dead_letters' => StorageSyncDeadLetter::where('resolved', false)->count(),
            ],
            'storage_by_provider' => Media::whereNull('deleted_at')
                ->select('provider', DB::raw('count(*) as count'), DB::raw('sum(size) as total_size'))
                ->groupBy('provider')
                ->get()
                ->map(fn($row) => [
                    'provider' => $row->provider,
                    'count' => $row->count,
                    'total_size' => $row->total_size,
                    'total_size_formatted' => self::formatBytes($row->total_size),
                ])
                ->toArray(),
            'recent_failures' => MediaConversion::where('status', 'failed')
                ->with('media')
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($c) => [
                    'media_id' => $c->media_id,
                    'type' => $c->type,
                    'error' => $c->error,
                    'attempts' => $c->attempts,
                    'created_at' => $c->created_at->diffForHumans(),
                ])
                ->toArray(),
            'recent_dead_letters' => StorageSyncDeadLetter::where('resolved', false)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($dl) => [
                    'file_path' => $dl->file_path,
                    'error' => $dl->error,
                    'attempts' => $dl->attempts,
                    'created_at' => $dl->created_at->diffForHumans(),
                ])
                ->toArray(),
            'migration_batches' => StorageSyncBatch::orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(fn($b) => [
                    'id' => $b->id,
                    'name' => $b->name,
                    'status' => $b->status,
                    'progress' => $b->progress_percentage,
                    'created_at' => $b->created_at->diffForHumans(),
                ])
                ->toArray(),
        ];
    }

    /**
     * نسبة نجاح المزامنة
     */
    private static function getSyncSuccessRate(): float
    {
        $total = StorageSyncBatch::whereNotNull('completed_at')->count();
        if ($total === 0) return 100;

        $successful = StorageSyncBatch::whereNotNull('completed_at')
            ->where('failed_files', 0)
            ->count();

        return round(($successful / $total) * 100, 1);
    }

    private static function formatBytes(int $bytes): string
    {
        if ($bytes === 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }
}
