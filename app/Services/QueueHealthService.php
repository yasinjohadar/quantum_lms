<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * حالة عامل الطابور بدقة عملية:
 * - نبض Cache يُحدَّث عند Looping / JobProcessing من الـ worker
 * - عدد الوظائف المعلّقة والفاشلة والعالقة
 */
class QueueHealthService
{
    public const HEARTBEAT_KEY = 'queue:worker:heartbeat';

    public const HEARTBEAT_TTL_SECONDS = 180;

    /** أقصى عمر مقبول للنبض لاعتبار العامل يعمل */
    public const HEARTBEAT_FRESH_SECONDS = 90;

    /** وظيفة معلّقة أقدم من هذا تُعدّ عالقة */
    public const STUCK_AFTER_SECONDS = 300;

    public function touchHeartbeat(): void
    {
        Cache::put(self::HEARTBEAT_KEY, now()->toIso8601String(), self::HEARTBEAT_TTL_SECONDS);
    }

    /**
     * @return array{
     *     driver: string,
     *     status: string,
     *     label: string,
     *     running: bool,
     *     pending: int|null,
     *     failed: int|null,
     *     stuck: int|null,
     *     heartbeat_at: string|null,
     *     heartbeat_age_seconds: int|null,
     *     detail: string,
     *     gamification_note: string
     * }
     */
    public function snapshot(): array
    {
        $driver = (string) config('queue.default', 'sync');
        $heartbeatRaw = Cache::get(self::HEARTBEAT_KEY);
        $heartbeatAt = null;
        $heartbeatAge = null;

        if (is_string($heartbeatRaw) && $heartbeatRaw !== '') {
            try {
                $heartbeatAt = \Illuminate\Support\Carbon::parse($heartbeatRaw);
                $heartbeatAge = (int) $heartbeatAt->diffInSeconds(now());
            } catch (Throwable) {
                $heartbeatAt = null;
            }
        }

        $pending = null;
        $failed = null;
        $stuck = null;

        if ($driver === 'database') {
            try {
                if (Schema::hasTable('jobs')) {
                    $pending = (int) DB::table('jobs')->count();
                    $threshold = now()->subSeconds(self::STUCK_AFTER_SECONDS)->getTimestamp();
                    $stuck = (int) DB::table('jobs')
                        ->where('available_at', '<=', $threshold)
                        ->count();
                }
                if (Schema::hasTable('failed_jobs')) {
                    $failed = (int) DB::table('failed_jobs')->count();
                }
            } catch (Throwable) {
                // تجاهل أخطاء الجدول أثناء الترحيل
            }
        }

        if ($driver === 'sync') {
            return [
                'driver' => $driver,
                'status' => 'sync',
                'label' => 'وضع متزامن (لا يحتاج عامل طابور)',
                'running' => true,
                'pending' => 0,
                'failed' => $failed ?? 0,
                'stuck' => 0,
                'heartbeat_at' => $heartbeatAt?->toDateTimeString(),
                'heartbeat_age_seconds' => $heartbeatAge,
                'detail' => 'QUEUE_CONNECTION=sync — المهام تُنفَّذ فوراً داخل الطلب.',
                'gamification_note' => 'منح نقاط التحفيز والشارات والإنجازات يعمل مباشرة دون الطابور.',
            ];
        }

        $running = $heartbeatAge !== null && $heartbeatAge <= self::HEARTBEAT_FRESH_SECONDS;

        if ($running) {
            $status = 'running';
            $label = 'يعمل';
            $detail = $pending
                ? "عامل الطابور نشط — {$pending} مهمة في الانتظار."
                : 'عامل الطابور نشط ولا توجد مهام معلّقة.';
        } elseif (($pending ?? 0) > 0 || ($stuck ?? 0) > 0) {
            $status = 'stopped';
            $label = 'متوقف أو متأخر';
            $detail = 'توجد مهام معلّقة بدون نبض حديث من العامل — شغّل: php artisan queue:work';
        } else {
            $status = 'unknown';
            $label = 'لا يوجد نشاط حديث';
            $detail = 'لم يُرصد نبض من عامل الطابور خلال آخر 90 ثانية. إن لم تكن هناك مهام فهذا قد يعني أن العامل متوقف أو لم يعمل بعد.';
        }

        if (($failed ?? 0) > 0 && $status === 'running') {
            $detail .= " — يوجد {$failed} مهمة فاشلة.";
        }

        return [
            'driver' => $driver,
            'status' => $status,
            'label' => $label,
            'running' => $running,
            'pending' => $pending,
            'failed' => $failed,
            'stuck' => $stuck,
            'heartbeat_at' => $heartbeatAt?->toDateTimeString(),
            'heartbeat_age_seconds' => $heartbeatAge,
            'detail' => $detail,
            'gamification_note' => 'منح نقاط التحفيز والشارات والإنجازات يعمل مباشرة دون الطابور. الطابور يخدم مهام أخرى (واتساب، وسائط، نسخ احتياطي…).',
        ];
    }
}
