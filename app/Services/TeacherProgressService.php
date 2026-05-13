<?php

namespace App\Services;

use App\Models\AcademicWeek;
use App\Models\Lesson;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TeacherProgressService
{
    /**
     * عدد صفحات الدرس (من book_page_from و book_page_to)، أو 1 إذا كانت null.
     */
    public static function lessonPageCount(Lesson $lesson): int
    {
        $from = $lesson->book_page_from;
        $to = $lesson->book_page_to;
        if ($from !== null && $to !== null) {
            return max(0, $to - $from + 1);
        }
        return 1;
    }

    /**
     * عدد الدروس المعتمدة في مادة معينة.
     */
    public static function getSubjectApprovedLessonsCount(int $subjectId): int
    {
        return Lesson::query()
            ->where('review_status', Lesson::REVIEW_STATUS_APPROVED)
            ->whereHas('unit', function ($q) use ($subjectId) {
                $q->whereHas('section', function ($q2) use ($subjectId) {
                    $q2->where('subject_id', $subjectId);
                });
            })
            ->count();
    }

    /**
     * إجمالي الصفحات المنجزة لمادة معينة (دروس معتمدة فقط).
     */
    public static function getSubjectCompletedPages(int $subjectId): int
    {
        $lessons = Lesson::query()
            ->where('review_status', Lesson::REVIEW_STATUS_APPROVED)
            ->whereHas('unit', function ($q) use ($subjectId) {
                $q->whereHas('section', function ($q2) use ($subjectId) {
                    $q2->where('subject_id', $subjectId);
                });
            })
            ->get();

        return $lessons->sum(fn (Lesson $l) => self::lessonPageCount($l));
    }

    /**
     * تقدم المعلم في الصفحات لكل مادة مخصصة له.
     * المفتاح: subject_id، القيمة: ['subject' => Subject, 'required_pages' => int, 'completed_pages' => int, 'remaining_pages' => int, 'percentage' => float|null]
     */
    public static function getTeacherPagesProgress(User $teacher): array
    {
        $result = [];
        // المواد المحذوفة ناعماً تبقى في teacher_subjects؛ بدون withTrashed تختفي من الواجهة والإحصائيات.
        $teacher->unsetRelation('assignedSubjects');
        $teacher->load(['assignedSubjects' => function ($query) {
            $query->withTrashed()->with([
                'schoolClass' => fn ($q) => $q->withTrashed(),
                'schoolClass.stage',
            ]);
        }]);
        foreach ($teacher->assignedSubjects as $subject) {
            $required = (int) ($subject->pivot->required_pages ?? 0);
            $completed = self::getSubjectCompletedPages($subject->id);
            $remaining = max(0, $required - $completed);
            $percentage = $required > 0
                ? min(100.0, round(($completed / $required) * 100, 1))
                : null;

            $result[] = [
                'subject' => $subject,
                'required_pages' => $required,
                'completed_pages' => $completed,
                'remaining_pages' => $remaining,
                'percentage' => $percentage,
            ];
        }
        return $result;
    }

    /**
     * حدود التاريخ للأسبوع: من AcademicWeek إن وُجد، وإلا أسبوع Carbon الحالي.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function getWeekDateRange(?AcademicWeek $week): array
    {
        if ($week !== null) {
            $start = $week->start_date->copy()->startOfDay();
            $end = $week->end_date->copy()->endOfDay();

            return [$start, $end];
        }

        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        return [$start, $end];
    }

    /**
     * عدد الدروس المعتمدة في الفترة الأسبوعية في مواد المعلم (حسب reviewed_at).
     *
     * @param  int|null  $weekId  معرف الأسبوع الدراسي، أو null للأسبوع الحالي (دراسي أو Carbon)
     */
    /**
     * عدد الدروس المعتمدة التي وُسِم تاريخ اعتمادها (reviewed_at) ضمن نطاق تواريخ أسبوع دراسي محدد.
     */
    public static function getTeacherWeeklyLessonsCompletedInAcademicWeek(User $teacher, AcademicWeek $week): int
    {
        $subjectIds = $teacher->assignedSubjects()->withTrashed()->pluck('subjects.id')->toArray();
        if (empty($subjectIds)) {
            return 0;
        }

        [$startOfWeek, $endOfWeek] = self::getWeekDateRange($week);

        return Lesson::query()
            ->where('review_status', Lesson::REVIEW_STATUS_APPROVED)
            ->whereNotNull('reviewed_at')
            ->whereBetween('reviewed_at', [$startOfWeek, $endOfWeek])
            ->whereHas('unit', function ($q) use ($subjectIds) {
                $q->whereHas('section', function ($q2) use ($subjectIds) {
                    $q2->whereIn('subject_id', $subjectIds);
                });
            })
            ->count();
    }

    public static function getTeacherWeeklyLessonsCompleted(User $teacher, ?int $weekId = null): int
    {
        $subjectIds = $teacher->assignedSubjects()->withTrashed()->pluck('subjects.id')->toArray();
        if (empty($subjectIds)) {
            return 0;
        }

        $week = AcademicWeekService::getWeekForProgress($weekId);
        if ($week !== null) {
            return self::getTeacherWeeklyLessonsCompletedInAcademicWeek($teacher, $week);
        }

        [$startOfWeek, $endOfWeek] = self::getWeekDateRange(null);

        return Lesson::query()
            ->where('review_status', Lesson::REVIEW_STATUS_APPROVED)
            ->whereNotNull('reviewed_at')
            ->whereBetween('reviewed_at', [$startOfWeek, $endOfWeek])
            ->whereHas('unit', function ($q) use ($subjectIds) {
                $q->whereHas('section', function ($q2) use ($subjectIds) {
                    $q2->whereIn('subject_id', $subjectIds);
                });
            })
            ->count();
    }

    /**
     * تقدم المعلم في الدروس الأسبوعية: الهدف، المنفذ، النسبة، والأسبوع المستخدم.
     *
     * @param  int|null  $weekId  معرف الأسبوع الدراسي، أو null للأسبوع الحالي
     */
    public static function getTeacherWeeklyLessonsProgress(User $teacher, ?int $weekId = null): array
    {
        $week = AcademicWeekService::getWeekForProgress($weekId);
        $target = AcademicWeekService::getWeeklyTargetForTeacher($teacher, $week);
        $completed = self::getTeacherWeeklyLessonsCompleted($teacher, $weekId);
        $percentage = $target > 0
            ? min(100.0, round(($completed / $target) * 100, 1))
            : null;

        return [
            'target' => $target,
            'completed' => $completed,
            'percentage' => $percentage,
            'current_week' => $week,
        ];
    }

    /**
     * تقدم الدروس الأسبوعية اعتماداً على نموذج الأسبوع الدراسي (يفضّل لجدول «كل أسابيع السنة» حتى لا يُعاد جلب الأسبوع بالمعرف فقط).
     */
    public static function getTeacherWeeklyLessonsProgressForWeek(User $teacher, AcademicWeek $week): array
    {
        $target = AcademicWeekService::getWeeklyTargetForTeacher($teacher, $week);
        $completed = self::getTeacherWeeklyLessonsCompletedInAcademicWeek($teacher, $week);
        $percentage = $target > 0
            ? min(100.0, round(($completed / $target) * 100, 1))
            : null;

        return [
            'target' => $target,
            'completed' => $completed,
            'percentage' => $percentage,
            'current_week' => $week,
        ];
    }

    /**
     * تجميع بيانات تقدم كل المعلمين للأدمن.
     *
     * @param  int|null  $weekId  أسبوع محدد أو null للأسبوع الحالي
     */
    public static function getAllTeachersProgress(?int $weekId = null): array
    {
        $query = User::query()
            ->with(['assignedSubjects' => fn ($q) => $q->withTrashed()])
            ->orderBy('name');

        $rolesTable = config('permission.table_names.roles', 'roles');
        if (Schema::hasColumn($rolesTable, 'staff_profile')) {
            // نعتمد على staff_profile في جدول الأدوار حتى يعمل مع تعدد أسماء أدوار المعلمين
            $query->whereHas('roles', function ($q) {
                $q->where('staff_profile', 'teacher');
            });
        } else {
            // fallback للتوافق مع قواعد بيانات قديمة لا تحتوي staff_profile
            $query->role('teacher');
        }

        $teachers = $query->get();
        $out = [];
        foreach ($teachers as $teacher) {
            $out[] = [
                'teacher' => $teacher,
                'pages_progress' => self::getTeacherPagesProgress($teacher),
                'weekly_progress' => self::getTeacherWeeklyLessonsProgress($teacher, $weekId),
            ];
        }
        return $out;
    }

    /**
     * ملخص مؤشرات التقدم لعدة معلمين دفعة واحدة (لجدول قائمة المعلمين).
     * المفتاح: teacher_id، القيمة: مصفوفة تحتوي pages_required, pages_completed, pages_percentage,
     * weekly_target, weekly_completed, weekly_percentage, total_approved_lessons, current_week.
     *
     * @param  Collection<int, User>  $teachers  المعلمون مع تحميل assignedSubjects
     * @param  int|null  $weekId  أسبوع دراسي محدد أو null للأسبوع الحالي
     * @return array<int, array{pages_required: int, pages_completed: int, pages_percentage: float|null, weekly_target: int, weekly_completed: int, weekly_percentage: float|null, total_approved_lessons: int, current_week: AcademicWeek|null}>
     */
    public static function getTeachersProgressSummary(Collection $teachers, ?int $weekId = null): array
    {
        if ($teachers->isEmpty()) {
            return [];
        }

        $week = AcademicWeekService::getWeekForProgress($weekId);
        [$startOfWeek, $endOfWeek] = self::getWeekDateRange($week);

        $teacherIds = $teachers->pluck('id')->toArray();
        $subjectIds = [];
        $teacherSubjectRequired = [];
        $teacherSubjectIds = [];

        foreach ($teachers as $teacher) {
            $tid = $teacher->id;
            $teacherSubjectRequired[$tid] = [];
            $teacherSubjectIds[$tid] = [];
            foreach ($teacher->assignedSubjects ?? [] as $subject) {
                $sid = $subject->id;
                $subjectIds[$sid] = true;
                $teacherSubjectIds[$tid][] = $sid;
                $teacherSubjectRequired[$tid][$sid] = (int) ($subject->pivot->required_pages ?? 0);
            }
        }

        $subjectIds = array_keys($subjectIds);
        if (empty($subjectIds)) {
            $bySubjectPages = [];
            $bySubjectCount = [];
            $bySubjectWeekly = [];
        } else {
            $bySubjectPages = DB::table('lessons as l')
                ->join('units as u', 'u.id', '=', 'l.unit_id')
                ->join('subject_sections as ss', 'ss.id', '=', 'u.section_id')
                ->where('l.review_status', Lesson::REVIEW_STATUS_APPROVED)
                ->whereIn('ss.subject_id', $subjectIds)
                ->whereNull('l.deleted_at')
                ->whereNull('u.deleted_at')
                ->whereNull('ss.deleted_at')
                ->selectRaw('ss.subject_id as subject_id, SUM(CASE WHEN l.book_page_from IS NOT NULL AND l.book_page_to IS NOT NULL THEN GREATEST(0, l.book_page_to - l.book_page_from + 1) ELSE 1 END) as completed_pages')
                ->groupBy('ss.subject_id')
                ->pluck('completed_pages', 'subject_id')
                ->map(fn ($v) => (int) $v)
                ->all();

            $bySubjectCount = DB::table('lessons as l')
                ->join('units as u', 'u.id', '=', 'l.unit_id')
                ->join('subject_sections as ss', 'ss.id', '=', 'u.section_id')
                ->where('l.review_status', Lesson::REVIEW_STATUS_APPROVED)
                ->whereIn('ss.subject_id', $subjectIds)
                ->whereNull('l.deleted_at')
                ->whereNull('u.deleted_at')
                ->whereNull('ss.deleted_at')
                ->selectRaw('ss.subject_id as subject_id, COUNT(*) as cnt')
                ->groupBy('ss.subject_id')
                ->pluck('cnt', 'subject_id')
                ->map(fn ($v) => (int) $v)
                ->all();

            $bySubjectWeekly = DB::table('lessons as l')
                ->join('units as u', 'u.id', '=', 'l.unit_id')
                ->join('subject_sections as ss', 'ss.id', '=', 'u.section_id')
                ->where('l.review_status', Lesson::REVIEW_STATUS_APPROVED)
                ->whereNotNull('l.reviewed_at')
                ->whereBetween('l.reviewed_at', [$startOfWeek, $endOfWeek])
                ->whereIn('ss.subject_id', $subjectIds)
                ->whereNull('l.deleted_at')
                ->whereNull('u.deleted_at')
                ->whereNull('ss.deleted_at')
                ->selectRaw('ss.subject_id as subject_id, COUNT(*) as cnt')
                ->groupBy('ss.subject_id')
                ->pluck('cnt', 'subject_id')
                ->map(fn ($v) => (int) $v)
                ->all();
        }

        $result = [];
        foreach ($teachers as $teacher) {
            $tid = $teacher->id;
            $subjectIdsForTeacher = $teacherSubjectIds[$tid] ?? [];
            $requiredBySubject = $teacherSubjectRequired[$tid] ?? [];

            $pages_required = 0;
            $pages_completed = 0;
            $total_approved_lessons = 0;
            $weekly_completed = 0;

            foreach ($subjectIdsForTeacher as $sid) {
                $pages_required += $requiredBySubject[$sid] ?? 0;
                $pages_completed += (int) ($bySubjectPages[$sid] ?? 0);
                $total_approved_lessons += (int) ($bySubjectCount[$sid] ?? 0);
                $weekly_completed += (int) ($bySubjectWeekly[$sid] ?? 0);
            }

            $pages_percentage = $pages_required > 0
                ? min(100.0, round(($pages_completed / $pages_required) * 100, 1))
                : null;

            $weekly_target = AcademicWeekService::getWeeklyTargetForTeacher($teacher, $week);
            $weekly_percentage = $weekly_target > 0
                ? min(100.0, round(($weekly_completed / $weekly_target) * 100, 1))
                : null;

            $result[$tid] = [
                'pages_required' => $pages_required,
                'pages_completed' => $pages_completed,
                'pages_percentage' => $pages_percentage,
                'weekly_target' => $weekly_target,
                'weekly_completed' => $weekly_completed,
                'weekly_percentage' => $weekly_percentage,
                'total_approved_lessons' => $total_approved_lessons,
                'current_week' => $week,
            ];
        }

        return $result;
    }

    /**
     * إحصائيات تفصيلية لمعلم واحد (لصفحة تفاصيل التقدم).
     * ترجع: teacher, pages_progress (مع approved_lessons_count لكل مادة), weekly_progress,
     * total_approved_lessons, total_pages_required, total_pages_completed, total_pages_percentage.
     *
     * @param  int|null  $weekId  أسبوع دراسي محدد أو null للأسبوع الحالي
     */
    public static function getTeacherDetailStats(User $teacher, ?int $weekId = null): array
    {
        $pages_progress = self::getTeacherPagesProgress($teacher);
        $subjectIds = array_map(fn ($row) => $row['subject']->id, $pages_progress);

        $bySubjectCount = [];
        if (! empty($subjectIds)) {
            $bySubjectCount = DB::table('lessons as l')
                ->join('units as u', 'u.id', '=', 'l.unit_id')
                ->join('subject_sections as ss', 'ss.id', '=', 'u.section_id')
                ->where('l.review_status', Lesson::REVIEW_STATUS_APPROVED)
                ->whereIn('ss.subject_id', $subjectIds)
                ->whereNull('l.deleted_at')
                ->whereNull('u.deleted_at')
                ->whereNull('ss.deleted_at')
                ->selectRaw('ss.subject_id as subject_id, COUNT(*) as cnt')
                ->groupBy('ss.subject_id')
                ->pluck('cnt', 'subject_id')
                ->map(fn ($v) => (int) $v)
                ->all();
        }

        $total_approved_lessons = 0;
        foreach ($pages_progress as &$row) {
            $cnt = (int) ($bySubjectCount[$row['subject']->id] ?? 0);
            $row['approved_lessons_count'] = $cnt;
            $total_approved_lessons += $cnt;
        }
        unset($row);

        $total_pages_required = array_sum(array_column($pages_progress, 'required_pages'));
        $total_pages_completed = array_sum(array_column($pages_progress, 'completed_pages'));
        $total_pages_percentage = $total_pages_required > 0
            ? min(100.0, round(($total_pages_completed / $total_pages_required) * 100, 1))
            : null;

        return [
            'teacher' => $teacher,
            'pages_progress' => $pages_progress,
            'weekly_progress' => self::getTeacherWeeklyLessonsProgress($teacher, $weekId),
            'total_approved_lessons' => $total_approved_lessons,
            'total_pages_required' => $total_pages_required,
            'total_pages_completed' => $total_pages_completed,
            'total_pages_percentage' => $total_pages_percentage,
        ];
    }

    /**
     * تفصيل أهداف الدروس الأسبوعية لكل أسابيع السنة النشطة + إجمالي الهدف والمنفذ.
     *
     * @param  \Illuminate\Support\Collection<int, AcademicWeek>  $activeWeeks
     * @return array{
     *   per_week: array<int, array{target: int, completed: int, percentage: float|null}>,
     *   year_total_target: int,
     *   year_total_completed: int,
     *   year_percentage: float|null
     * }
     */
    public static function getTeacherActiveYearWeeksLessonsBreakdown(User $teacher, Collection $activeWeeks): array
    {
        $perWeek = [];
        $yearTotalTarget = 0;
        $yearTotalCompleted = 0;

        foreach ($activeWeeks as $w) {
            $p = self::getTeacherWeeklyLessonsProgressForWeek($teacher, $w);
            $perWeek[$w->id] = [
                'target' => (int) $p['target'],
                'completed' => (int) $p['completed'],
                'percentage' => $p['percentage'],
            ];
            $yearTotalTarget += (int) $p['target'];
            $yearTotalCompleted += (int) $p['completed'];
        }

        $yearPercentage = $yearTotalTarget > 0
            ? min(100.0, round(($yearTotalCompleted / $yearTotalTarget) * 100, 1))
            : null;

        return [
            'per_week' => $perWeek,
            'year_total_target' => $yearTotalTarget,
            'year_total_completed' => $yearTotalCompleted,
            'year_percentage' => $yearPercentage,
        ];
    }

    /**
     * تقدم الدروس الأسبوعية للمعلم لكل الأسابيع الماضية ضمن السنة الدراسية النشطة فقط.
     *
     * @return array<int, array{week: AcademicWeek, target: int, completed: int, percentage: float|null}>
     */
    public static function getTeacherPastWeeksProgress(User $teacher): array
    {
        $weeks = AcademicWeekService::getPastActiveYearWeeks();
        if ($weeks->isEmpty()) {
            return [];
        }

        $out = [];
        foreach ($weeks as $week) {
            $target = AcademicWeekService::getWeeklyTargetForTeacher($teacher, $week);
            $completed = self::getTeacherWeeklyLessonsCompletedInAcademicWeek($teacher, $week);
            $percentage = $target > 0
                ? min(100.0, round(($completed / $target) * 100, 1))
                : null;

            $out[] = [
                'week' => $week,
                'target' => $target,
                'completed' => $completed,
                'percentage' => $percentage,
            ];
        }

        return $out;
    }
}
