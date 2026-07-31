<?php

namespace Tests\Backup;

use App\Models\BackupSchedule;
use Carbon\Carbon;

class BackupScheduleModelTest extends BackupTestCase
{
    public function test_should_run_is_false_when_inactive(): void
    {
        $schedule = BackupSchedule::factory()->create(['is_active' => false, 'next_run_at' => now()->subMinute()]);

        $this->assertFalse($schedule->shouldRun());
    }

    public function test_should_run_is_false_when_no_next_run_at(): void
    {
        $schedule = BackupSchedule::factory()->create(['is_active' => true, 'next_run_at' => null]);

        $this->assertFalse($schedule->shouldRun());
    }

    public function test_should_run_is_true_when_next_run_at_has_passed(): void
    {
        $schedule = BackupSchedule::factory()->create(['is_active' => true, 'next_run_at' => now()->subMinute()]);
        $notYet = BackupSchedule::factory()->create(['is_active' => true, 'next_run_at' => now()->addDay()]);

        $this->assertTrue($schedule->shouldRun());
        $this->assertFalse($notYet->shouldRun());
    }

    public function test_calculate_next_run_daily_uses_today_if_time_not_passed_else_tomorrow(): void
    {
        $from = Carbon::parse('2026-03-10 08:00:00');

        $notYetPassed = BackupSchedule::factory()->make(['frequency' => 'daily', 'time' => '20:00']);
        $alreadyPassed = BackupSchedule::factory()->make(['frequency' => 'daily', 'time' => '02:00']);

        $this->assertTrue($notYetPassed->calculateNextRun($from)->isSameDay($from));
        $this->assertTrue($alreadyPassed->calculateNextRun($from)->isSameDay($from->copy()->addDay()));
    }

    public function test_calculate_next_run_weekly_picks_next_matching_weekday(): void
    {
        // 2026-03-10 هو الثلاثاء (dayOfWeek=2)
        $from = Carbon::parse('2026-03-10 08:00:00');

        $schedule = BackupSchedule::factory()->make([
            'frequency' => 'weekly',
            'time' => '10:00',
            'days_of_week' => [2], // نفس اليوم، لكن الوقت لم يفت بعد
        ]);
        $next = $schedule->calculateNextRun($from);
        $this->assertTrue($next->isSameDay($from));
        $this->assertSame(2, $next->dayOfWeek);

        $scheduleFriday = BackupSchedule::factory()->make([
            'frequency' => 'weekly',
            'time' => '10:00',
            'days_of_week' => [5], // الجمعة القادمة
        ]);
        $nextFriday = $scheduleFriday->calculateNextRun($from);
        $this->assertSame(5, $nextFriday->dayOfWeek);
        $this->assertTrue($nextFriday->greaterThan($from));
    }

    public function test_calculate_next_run_monthly_clamps_day_for_short_months(): void
    {
        // فبراير 2026 غير كبيسة (28 يوماً) — اليوم 31 يجب أن يُقلَّم إلى 28
        $from = Carbon::parse('2026-02-01 00:00:00');
        $schedule = BackupSchedule::factory()->make(['frequency' => 'monthly', 'time' => '03:00', 'day_of_month' => 31]);

        $next = $schedule->calculateNextRun($from);

        $this->assertSame(2, $next->month);
        $this->assertSame(28, $next->day);
    }

    public function test_calculate_next_run_monthly_handles_leap_february(): void
    {
        // فبراير 2028 كبيسة (29 يوماً)
        $from = Carbon::parse('2028-02-01 00:00:00');
        $schedule = BackupSchedule::factory()->make(['frequency' => 'monthly', 'time' => '03:00', 'day_of_month' => 31]);

        $next = $schedule->calculateNextRun($from);

        $this->assertSame(2, $next->month);
        $this->assertSame(29, $next->day);
    }

    public function test_calculate_next_run_monthly_moves_to_next_month_when_day_passed(): void
    {
        $from = Carbon::parse('2026-03-20 12:00:00');
        $schedule = BackupSchedule::factory()->make(['frequency' => 'monthly', 'time' => '03:00', 'day_of_month' => 5]);

        $next = $schedule->calculateNextRun($from);

        $this->assertSame(4, $next->month);
        $this->assertSame(5, $next->day);
    }

    public function test_calculate_next_run_custom_falls_back_to_daily_logic(): void
    {
        $from = Carbon::parse('2026-03-10 08:00:00');
        $schedule = BackupSchedule::factory()->make(['frequency' => 'custom', 'time' => '20:00']);

        $this->assertTrue($schedule->calculateNextRun($from)->isSameDay($from));
    }
}
