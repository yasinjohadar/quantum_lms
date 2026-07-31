<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BackupSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'backup_type',
        'frequency',
        'time',
        'days_of_week',
        'day_of_month',
        'storage_drivers',
        'compression_types',
        'retention_days',
        'is_active',
        'last_run_at',
        'next_run_at',
        'created_by',
    ];

    protected $casts = [
        'time' => 'string',
        'days_of_week' => 'array',
        'storage_drivers' => 'array',
        'compression_types' => 'array',
        'retention_days' => 'integer',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    /**
     * أنواع المحتوى
     */
    public const BACKUP_TYPES = [
        'full' => 'كامل',
        'database' => 'قاعدة البيانات',
        'files' => 'الملفات',
        'config' => 'الإعدادات',
    ];

    /**
     * التكرارات
     */
    public const FREQUENCIES = [
        'daily' => 'يومي',
        'weekly' => 'أسبوعي',
        'monthly' => 'شهري',
        'custom' => 'مخصص',
    ];

    /**
     * العلاقة مع منشئ الجدولة
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * العلاقة مع النسخ
     */
    public function backups()
    {
        return $this->hasMany(Backup::class, 'backup_schedule_id');
    }

    /**
     * التحقق من وجوب التشغيل
     */
    public function shouldRun(?Carbon $at = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if (! $this->next_run_at) {
            return false;
        }

        $at = $at ?? now();

        return $at->greaterThanOrEqualTo($this->next_run_at);
    }

    /**
     * حساب وقت التشغيل التالي بعد لحظة معينة (افتراضياً الآن).
     * يعيد أول موعد مستقبلي صارم (أكبر من $from).
     */
    public function calculateNextRun(?Carbon $from = null): Carbon
    {
        $from = ($from ?? now())->copy();

        return match ($this->frequency) {
            'daily', 'custom' => $this->calculateNextDailyRun($from),
            'weekly' => $this->calculateNextWeeklyRun($from),
            'monthly' => $this->calculateNextMonthlyRun($from),
            default => $from->copy()->addDay(),
        };
    }

    /**
     * يومي: إن لم يفت وقت اليوم يُستخدم اليوم، وإلا غداً.
     */
    private function calculateNextDailyRun(Carbon $from): Carbon
    {
        $candidate = $from->copy()->setTimeFromTimeString($this->normalizedTime());

        if ($candidate->greaterThan($from)) {
            return $candidate;
        }

        return $candidate->addDay();
    }

    /**
     * أسبوعي: يشمل اليوم الحالي إن كان مختاراً والوقت لم يفت بعد.
     */
    private function calculateNextWeeklyRun(Carbon $from): Carbon
    {
        $daysOfWeek = collect($this->days_of_week ?? [])
            ->map(static fn ($day) => (int) $day)
            ->filter(static fn ($day) => $day >= 0 && $day <= 6)
            ->unique()
            ->sort()
            ->values()
            ->all();

        if ($daysOfWeek === []) {
            return $this->calculateNextDailyRun($from);
        }

        for ($offset = 0; $offset < 8; $offset++) {
            $candidate = $from->copy()
                ->startOfDay()
                ->addDays($offset)
                ->setTimeFromTimeString($this->normalizedTime());

            if (in_array($candidate->dayOfWeek, $daysOfWeek, true) && $candidate->greaterThan($from)) {
                return $candidate;
            }
        }

        // احتياط نظري
        return $from->copy()->addWeek()->setTimeFromTimeString($this->normalizedTime());
    }

    /**
     * شهري: يراعي أيام الشهر القصيرة (مثل 31 في فبراير).
     */
    private function calculateNextMonthlyRun(Carbon $from): Carbon
    {
        $desiredDay = max(1, min(31, (int) ($this->day_of_month ?? 1)));

        $candidate = $this->buildMonthlyCandidate($from->copy()->startOfMonth(), $desiredDay);

        if ($candidate->greaterThan($from)) {
            return $candidate;
        }

        return $this->buildMonthlyCandidate(
            $from->copy()->startOfMonth()->addMonthNoOverflow(),
            $desiredDay
        );
    }

    private function buildMonthlyCandidate(Carbon $monthStart, int $desiredDay): Carbon
    {
        $day = min($desiredDay, $monthStart->daysInMonth);

        return $monthStart->copy()->day($day)->setTimeFromTimeString($this->normalizedTime());
    }

    /**
     * تطبيع الوقت المخزّن (H:i أو H:i:s) إلى H:i:s.
     */
    private function normalizedTime(): string
    {
        $raw = (string) $this->time;

        if (preg_match('/^\d{2}:\d{2}$/', $raw)) {
            return $raw . ':00';
        }

        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $raw)) {
            return $raw;
        }

        return Carbon::parse($raw)->format('H:i:s');
    }
}
