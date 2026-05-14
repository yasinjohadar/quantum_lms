<?php

namespace App\Services;

use App\Models\AcademicWeek;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AcademicWeekService
{
    /**
     * الأسبوع الدراسي الحالي (ضمن السنة النشطة حيث التاريخ الحالي بين start_date و end_date).
     */
    public static function getCurrentAcademicWeek(): ?AcademicWeek
    {
        $now = Carbon::now()->startOfDay();

        return AcademicWeek::query()
            ->whereHas('academicYear', fn ($q) => $q->where('is_active', true))
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $now)
            ->whereDate('end_date', '>=', $now)
            ->with('academicYear')
            ->first();
    }

    /**
     * الحصول على أسبوع دراسي بالمعرف (للعرض حسب أسبوع محدد).
     */
    public static function getAcademicWeekById(?int $weekId): ?AcademicWeek
    {
        if ($weekId === null) {
            return null;
        }

        return AcademicWeek::with('academicYear')->find($weekId);
    }

    /**
     * تحديد الأسبوع المستخدم في الحساب: إن وُجد week_id استخدمه، وإلا الأسبوع الحالي.
     */
    public static function getWeekForProgress(?int $weekId = null): ?AcademicWeek
    {
        if ($weekId !== null) {
            return self::getAcademicWeekById($weekId);
        }

        return self::getCurrentAcademicWeek();
    }

    /**
     * حساب الهدف الأسبوعي لمعلم: سجل teacher_week_targets للأسبوع إن وُجد، وإلا هدف الأسبوع من academic_weeks.
     * (لا يُستخدم حقل users.weekly_lessons_target — أُلغي من الواجهة والإحصائيات.)
     */
    public static function getWeeklyTargetForTeacher(User $teacher, ?AcademicWeek $week): int
    {
        if ($week === null) {
            return 0;
        }

        $saved = $teacher->teacherWeekTargets()
            ->where('academic_week_id', $week->id)
            ->first();

        if ($saved !== null) {
            return (int) ($saved->required_lessons_target ?? 0);
        }

        return (int) ($week->required_lessons_target ?? 0);
    }

    /**
     * أسابيع السنة النشطة (للقوائم والفلاتر).
     *
     * @return Collection<int, AcademicWeek>
     */
    public static function getActiveYearWeeks(): Collection
    {
        return AcademicWeek::query()
            ->whereHas('academicYear', fn ($q) => $q->where('is_active', true))
            ->where('is_active', true)
            ->orderBy('start_date')
            ->with('academicYear')
            ->get();
    }

    /**
     * هل الأسبوع منتهٍ (انتهى تاريخ end_date قبل اليوم).
     */
    public static function isWeekPast(AcademicWeek $week): bool
    {
        $today = Carbon::now()->startOfDay();

        return $week->end_date->copy()->endOfDay()->lt($today);
    }

    /**
     * أسابيع السنة النشطة التي انتهت فقط (للإحصائيات التاريخية).
     *
     * @return Collection<int, AcademicWeek>
     */
    public static function getPastActiveYearWeeks(): Collection
    {
        $today = Carbon::now()->startOfDay();

        return AcademicWeek::query()
            ->whereHas('academicYear', fn ($q) => $q->where('is_active', true))
            ->where('is_active', true)
            ->whereDate('end_date', '<', $today)
            ->orderBy('start_date')
            ->with('academicYear')
            ->get();
    }
}
