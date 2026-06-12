<?php

namespace App\Services;

use App\Models\AcademicWeek;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TeacherProgressService
{
    /**
     * عدد صفحات الدرس من book_page_from و book_page_to.
     * بدون نطاق كامل (الاثنان null) يُعامل كـ 0 في الإحصائيات.
     * إذا وُجدت إحدى الصفحات فقط، يُحسب 1 (تقدير إلى أن الدرس له وزن غير صفري غير قابل للاحتساب بدقة).
     */
    public static function lessonPageCount(Lesson $lesson): int
    {
        $from = $lesson->book_page_from;
        $to = $lesson->book_page_to;
        if ($from !== null && $to !== null) {
            return max(0, $to - $from + 1);
        }
        if ($from === null && $to === null) {
            return 0;
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

        usort($result, [self::class, 'comparePagesProgressRowsByClassThenSubject']);

        return $result;
    }

    /**
     * ترتيب صفوف تقدم الصفحات: مرحلة ← صف ← مادة (لعرض مواد كل صف معاً).
     *
     * @param  array{subject: Subject, required_pages: int, completed_pages: int, remaining_pages: int, percentage: float|null}  $a
     * @param  array{subject: Subject, required_pages: int, completed_pages: int, remaining_pages: int, percentage: float|null}  $b
     */
    private static function comparePagesProgressRowsByClassThenSubject(array $a, array $b): int
    {
        return self::compareAssignedSubjectsForDisplayOrder($a['subject'], $b['subject']);
    }

    /**
     * ترتيب المواد المخصّصة للمعلم: مرحلة ← صف ← مادة.
     */
    public static function compareAssignedSubjectsForDisplayOrder(Subject $sa, Subject $sb): int
    {
        $ca = $sa->schoolClass;
        $cb = $sb->schoolClass;

        if ($ca === null && $cb === null) {
            return strcmp((string) $sa->name, (string) $sb->name);
        }
        if ($ca === null) {
            return 1;
        }
        if ($cb === null) {
            return -1;
        }

        $stageA = $ca->relationLoaded('stage') ? $ca->stage : null;
        $stageB = $cb->relationLoaded('stage') ? $cb->stage : null;
        $stageOrdA = $stageA ? (int) ($stageA->order ?? 0) : (int) ($ca->stage_id ?? 0);
        $stageOrdB = $stageB ? (int) ($stageB->order ?? 0) : (int) ($cb->stage_id ?? 0);
        if ($stageOrdA !== $stageOrdB) {
            return $stageOrdA <=> $stageOrdB;
        }
        $stageIdA = (int) ($ca->stage_id ?? 0);
        $stageIdB = (int) ($cb->stage_id ?? 0);
        if ($stageIdA !== $stageIdB) {
            return $stageIdA <=> $stageIdB;
        }

        $classOrdA = (int) ($ca->order ?? 0);
        $classOrdB = (int) ($cb->order ?? 0);
        if ($classOrdA !== $classOrdB) {
            return $classOrdA <=> $classOrdB;
        }
        if ($ca->id !== $cb->id) {
            return $ca->id <=> $cb->id;
        }

        $subOrdA = (int) ($sa->order ?? 0);
        $subOrdB = (int) ($sb->order ?? 0);
        if ($subOrdA !== $subOrdB) {
            return $subOrdA <=> $subOrdB;
        }

        return strcmp((string) $sa->name, (string) $sb->name);
    }

    /**
     * مواد المعلم المخصّصة مرتّبة للعرض.
     *
     * @return Collection<int, Subject>
     */
    public static function getTeacherAssignedSubjectsOrdered(User $teacher): Collection
    {
        $teacher->unsetRelation('assignedSubjects');

        return $teacher->assignedSubjects()->withTrashed()->with([
            'schoolClass' => fn ($q) => $q->withTrashed(),
            'schoolClass.stage',
        ])->get()->sort(function (Subject $a, Subject $b) {
            return self::compareAssignedSubjectsForDisplayOrder($a, $b);
        })->values();
    }

    /**
     * @return array<int, int>
     */
    public static function getTeacherAssignedSubjectIds(User $teacher): array
    {
        return self::getTeacherAssignedSubjectsOrdered($teacher)->pluck('id')->all();
    }

    /**
     * @return array{lessons_count: int, total_pages: int}
     */
    public static function getTeacherApprovedLessonsGrandTotals(User $teacher): array
    {
        $subjectIds = self::getTeacherAssignedSubjectIds($teacher);
        if ($subjectIds === []) {
            return ['lessons_count' => 0, 'total_pages' => 0];
        }

        $pageSql = self::lessonPageCountSql('lessons');
        $row = self::approvedLessonsForTeacherSubjectsQuery($subjectIds)
            ->selectRaw("COUNT(*) as lessons_count, COALESCE(SUM({$pageSql}), 0) as total_pages")
            ->first();

        return [
            'lessons_count' => (int) ($row->lessons_count ?? 0),
            'total_pages' => (int) ($row->total_pages ?? 0),
        ];
    }

    /**
     * ملخص الدروس المعتمدة لكل مادة (بدون تحميل كل الدروس).
     *
     * @return array<int, array{subject: Subject, lessons_count: int, total_pages: int}>
     */
    public static function getTeacherApprovedLessonsSubjectSummaries(User $teacher): array
    {
        $subjects = self::getTeacherAssignedSubjectsOrdered($teacher);
        $subjectIds = $subjects->pluck('id')->all();
        if ($subjectIds === []) {
            return [];
        }

        $countsBySubject = self::getTeacherApprovedLessonsCountsBySubject($subjectIds);
        $out = [];
        foreach ($subjects as $subject) {
            $stats = $countsBySubject[$subject->id] ?? ['lessons_count' => 0, 'total_pages' => 0];
            if ($stats['lessons_count'] === 0) {
                continue;
            }
            $out[] = [
                'subject' => $subject,
                'lessons_count' => $stats['lessons_count'],
                'total_pages' => $stats['total_pages'],
            ];
        }

        return $out;
    }

    /**
     * صفحة من الدروس المعتمدة (لتجنب استنفاد الذاكرة عند آلاف الدروس).
     *
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public static function paginateTeacherApprovedLessons(User $teacher, ?int $subjectId = null, int $perPage = 50): LengthAwarePaginator
    {
        $subjects = self::getTeacherAssignedSubjectsOrdered($teacher);
        $subjectIds = $subjects->pluck('id')->all();
        $subjectsById = $subjects->keyBy('id');

        $query = self::approvedLessonsForTeacherSubjectsQuery($subjectIds, $subjectId)
            ->select('lessons.*')
            ->with([
                'unit' => fn ($q) => $q->withTrashed()->with(['section' => fn ($q2) => $q2->withTrashed()]),
                'section' => fn ($q) => $q->withTrashed(),
            ]);

        $query = self::applyApprovedLessonsDisplayOrder($query);

        return $query->paginate($perPage)->withQueryString()->through(function (Lesson $lesson) use ($subjectsById) {
            $sid = self::resolveLessonSubjectId($lesson);

            return [
                'lesson' => $lesson,
                'subject' => $sid !== null ? $subjectsById->get($sid) : null,
                'pages_count' => self::lessonPageCount($lesson),
                'pages_label' => self::formatLessonPagesLabel($lesson),
                'section_title' => ($lesson->unit?->section ?? $lesson->section)?->title,
                'unit_title' => $lesson->unit?->title,
            ];
        });
    }

    /**
     * @deprecated استخدم getTeacherApprovedLessonsSubjectSummaries + paginateTeacherApprovedLessons
     *
     * @return array<int, array{subject: Subject, lessons: array<int, array<string, mixed>>, total_pages: int, lessons_count: int}>
     */
    public static function getTeacherApprovedLessonsDetailBySubject(User $teacher): array
    {
        $summaries = self::getTeacherApprovedLessonsSubjectSummaries($teacher);

        return array_map(function (array $row) {
            return array_merge($row, ['lessons' => []]);
        }, $summaries);
    }

    /**
     * @param  array<int, int>  $subjectIds
     */
    public static function approvedLessonsForTeacherSubjectsQuery(array $subjectIds, ?int $filterSubjectId = null): Builder
    {
        if ($filterSubjectId !== null) {
            $subjectIds = in_array($filterSubjectId, $subjectIds, true) ? [$filterSubjectId] : [];
        }

        if ($subjectIds === []) {
            return Lesson::query()->whereRaw('1 = 0');
        }

        return Lesson::query()
            ->where('lessons.review_status', Lesson::REVIEW_STATUS_APPROVED)
            ->where(function ($outer) use ($subjectIds) {
                $outer->whereHas('unit', function ($q) use ($subjectIds) {
                    $q->whereHas('section', function ($q2) use ($subjectIds) {
                        $q2->whereIn('subject_id', $subjectIds);
                    });
                })->orWhereHas('section', function ($q) use ($subjectIds) {
                    $q->whereIn('subject_id', $subjectIds);
                });
            });
    }

    /**
     * @param  array<int, int>  $subjectIds
     * @return array<int, array{lessons_count: int, total_pages: int}>
     */
    protected static function getTeacherApprovedLessonsCountsBySubject(array $subjectIds): array
    {
        if ($subjectIds === []) {
            return [];
        }

        $pageSql = self::lessonPageCountSql('l');
        $placeholders = implode(',', array_fill(0, count($subjectIds), '?'));

        $rows = DB::table('lessons as l')
            ->leftJoin('units as u', function ($join) {
                $join->on('u.id', '=', 'l.unit_id')->whereNull('u.deleted_at');
            })
            ->leftJoin('subject_sections as ss_u', function ($join) {
                $join->on('ss_u.id', '=', 'u.section_id')->whereNull('ss_u.deleted_at');
            })
            ->leftJoin('subject_sections as ss_d', function ($join) {
                $join->on('ss_d.id', '=', 'l.section_id')->whereNull('ss_d.deleted_at');
            })
            ->where('l.review_status', Lesson::REVIEW_STATUS_APPROVED)
            ->whereNull('l.deleted_at')
            ->where(function ($q) use ($subjectIds) {
                $q->whereIn('ss_u.subject_id', $subjectIds)
                    ->orWhereIn('ss_d.subject_id', $subjectIds);
            })
            ->whereRaw('COALESCE(ss_u.subject_id, ss_d.subject_id) IN (' . $placeholders . ')', $subjectIds)
            ->groupBy(DB::raw('COALESCE(ss_u.subject_id, ss_d.subject_id)'))
            ->selectRaw("COALESCE(ss_u.subject_id, ss_d.subject_id) as subject_id, COUNT(*) as lessons_count, COALESCE(SUM({$pageSql}), 0) as total_pages")
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row->subject_id] = [
                'lessons_count' => (int) $row->lessons_count,
                'total_pages' => (int) $row->total_pages,
            ];
        }

        return $out;
    }

    protected static function lessonPageCountSql(string $table = 'lessons'): string
    {
        return "(CASE WHEN {$table}.book_page_from IS NOT NULL AND {$table}.book_page_to IS NOT NULL "
            . "THEN GREATEST(0, {$table}.book_page_to - {$table}.book_page_from + 1) "
            . "WHEN {$table}.book_page_from IS NULL AND {$table}.book_page_to IS NULL THEN 0 ELSE 1 END)";
    }

    protected static function applyApprovedLessonsDisplayOrder(Builder $query): Builder
    {
        return $query
            ->leftJoin('units as sort_u', function ($join) {
                $join->on('sort_u.id', '=', 'lessons.unit_id')->whereNull('sort_u.deleted_at');
            })
            ->leftJoin('subject_sections as sort_ss_u', function ($join) {
                $join->on('sort_ss_u.id', '=', 'sort_u.section_id')->whereNull('sort_ss_u.deleted_at');
            })
            ->leftJoin('subject_sections as sort_ss_d', function ($join) {
                $join->on('sort_ss_d.id', '=', 'lessons.section_id')->whereNull('sort_ss_d.deleted_at');
            })
            ->orderByRaw('COALESCE(sort_ss_u.`order`, sort_ss_d.`order`, 99999)')
            ->orderByRaw('COALESCE(sort_u.`order`, 99999)')
            ->orderBy('lessons.order')
            ->orderBy('lessons.id');
    }

    private static function resolveLessonSubjectId(Lesson $lesson): ?int
    {
        if ($lesson->unit && $lesson->unit->section) {
            return (int) $lesson->unit->section->subject_id;
        }
        if ($lesson->section) {
            return (int) $lesson->section->subject_id;
        }

        return null;
    }

    private static function formatLessonPagesLabel(Lesson $lesson): string
    {
        $from = $lesson->book_page_from;
        $to = $lesson->book_page_to;
        if ($from !== null && $to !== null) {
            return 'من ' . $from . ' إلى ' . $to;
        }
        if ($from !== null) {
            return 'من صفحة ' . $from;
        }
        if ($to !== null) {
            return 'إلى صفحة ' . $to;
        }

        return '— (بدون نطاق؛ يُحسب 0 في إجمالي صفحات الإحصائيات)';
    }

    private static function lessonDisplaySortTuple(Lesson $lesson): array
    {
        $section = $lesson->unit?->section ?? $lesson->section;
        $secOrder = $section ? (int) ($section->order ?? 0) : 99999;
        $unitOrder = $lesson->unit ? (int) ($lesson->unit->order ?? 0) : 99999;
        $lessonOrder = (int) ($lesson->order ?? 0);

        return [$secOrder, $unitOrder, $lessonOrder, $lesson->id];
    }

    private static function compareApprovedLessonsForDisplay(Lesson $a, Lesson $b): int
    {
        $ta = self::lessonDisplaySortTuple($a);
        $tb = self::lessonDisplaySortTuple($b);
        foreach ([0, 1, 2, 3] as $i) {
            if ($ta[$i] !== $tb[$i]) {
                return $ta[$i] <=> $tb[$i];
            }
        }

        return 0;
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
                ->selectRaw('ss.subject_id as subject_id, SUM(CASE WHEN l.book_page_from IS NOT NULL AND l.book_page_to IS NOT NULL THEN GREATEST(0, l.book_page_to - l.book_page_from + 1) WHEN l.book_page_from IS NULL AND l.book_page_to IS NULL THEN 0 ELSE 1 END) as completed_pages')
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
