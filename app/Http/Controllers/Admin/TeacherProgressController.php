<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicWeek;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherWeekTarget;
use App\Models\User;
use App\Services\AcademicWeekService;
use App\Services\TeacherProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TeacherProgressController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:teacher-progress-view']);
    }

    /**
     * عرض تقدم المعلمين (صفحات مطلوبة + دروس أسبوعية)
     */
    public function index(Request $request)
    {
        $weekId = $request->input('week_id') ? (int) $request->input('week_id') : null;
        $progress = TeacherProgressService::getAllTeachersProgress($weekId);
        $activeWeeks = AcademicWeekService::getActiveYearWeeks();
        $currentWeek = AcademicWeekService::getCurrentAcademicWeek();

        return view('admin.pages.teachers.progress', compact('progress', 'activeWeeks', 'currentWeek'));
    }

    /**
     * تفاصيل الدروس المعتمدة وصفحات الكتاب لكل مادة (لمعلم محدد — للإدارة).
     */
    public function approvedLessonsDetail(Request $request, User $teacher)
    {
        if (! $teacher->matchesAdminTeacherListingCriteria()) {
            return redirect()->back()->with('error', 'المستخدم المحدد ليس معلم');
        }

        $subjectId = $request->filled('subject_id') ? (int) $request->input('subject_id') : null;
        $grandTotals = TeacherProgressService::getTeacherApprovedLessonsGrandTotals($teacher);
        $subjectSummaries = TeacherProgressService::getTeacherApprovedLessonsSubjectSummaries($teacher);
        $lessons = TeacherProgressService::paginateTeacherApprovedLessons($teacher, $subjectId, 50);

        return view('admin.pages.teachers.my-approved-lessons', [
            'subjectSummaries' => $subjectSummaries,
            'lessons' => $lessons,
            'selectedSubjectId' => $subjectId,
            'grandTotalPages' => $grandTotals['total_pages'],
            'grandLessonsCount' => $grandTotals['lessons_count'],
            'viewedTeacher' => $teacher,
        ]);
    }

    /**
     * صفحة مخصصة: تقدم الصفحات الموكّلة لكل مادة (واضحة بصرياً).
     */
    public function materialPages(User $teacher)
    {
        if (! $teacher->matchesAdminTeacherListingCriteria()) {
            return redirect()->back()->with('error', 'المستخدم المحدد ليس معلم');
        }

        $stats = TeacherProgressService::getTeacherDetailStats($teacher, null);

        return view('admin.pages.teachers.progress-material-pages', array_merge($stats, [
            'teacher' => $teacher,
        ]));
    }

    /**
     * عرض تفاصيل تقدم معلم واحد
     */
    public function show(Request $request, User $teacher)
    {
        if (! $teacher->matchesAdminTeacherListingCriteria()) {
            return redirect()->back()->with('error', 'المستخدم المحدد ليس معلم');
        }

        $weekId = $request->input('week_id') ? (int) $request->input('week_id') : null;
        $stats = TeacherProgressService::getTeacherDetailStats($teacher, $weekId);
        $activeWeeks = AcademicWeekService::getActiveYearWeeks();
        $currentWeek = $stats['weekly_progress']['current_week'] ?? null;
        $displayWeekId = $weekId ?? $currentWeek?->id;
        $activeWeekIds = $activeWeeks->pluck('id')->all();
        $weekTargets = ! empty($activeWeekIds)
            ? TeacherWeekTarget::query()
                ->where('teacher_id', $teacher->id)
                ->whereIn('academic_week_id', $activeWeekIds)
                ->pluck('required_lessons_target', 'academic_week_id')
                ->toArray()
            : [];

        $yearWeeksLessons = $activeWeeks->isNotEmpty()
            ? TeacherProgressService::getTeacherActiveYearWeeksLessonsBreakdown($teacher, $activeWeeks)
            : [
                'per_week' => [],
                'year_total_target' => 0,
                'year_total_completed' => 0,
                'year_percentage' => null,
            ];

        $assignedClasses = $teacher->assignedClasses()->with('stage')->get();
        $assignedSubjects = $teacher->assignedSubjects()->withTrashed()->with([
            'schoolClass' => fn ($q) => $q->withTrashed(),
            'schoolClass.stage',
        ])->get();

        $unassignedClasses = collect();
        $unassignedSubjects = collect();
        $user = auth()->user();
        if ($user?->can('teacher-assignment-update') && $user->can('teacher-assignment-manage-classes')) {
            $assignedClassIds = $assignedClasses->pluck('id')->all();
            $classQuery = SchoolClass::with('stage')->ordered();
            if ($assignedClassIds !== []) {
                $classQuery->whereNotIn('id', $assignedClassIds);
            }
            $unassignedClasses = $classQuery->get();
        }
        if ($user?->can('teacher-assignment-update') && $user->can('teacher-assignment-manage-subjects')) {
            $assignedSubjectIds = $assignedSubjects->pluck('id')->all();
            $subjectQuery = Subject::with('schoolClass.stage')->ordered();
            if ($assignedSubjectIds !== []) {
                $subjectQuery->whereNotIn('id', $assignedSubjectIds);
            }
            $unassignedSubjects = $subjectQuery->get();
        }

        return view('admin.pages.teachers.progress-show', array_merge($stats, [
            'activeWeeks' => $activeWeeks,
            'currentWeek' => $currentWeek,
            'displayWeekId' => $displayWeekId,
            'weekTargets' => $weekTargets,
            'yearWeeksLessons' => $yearWeeksLessons,
            'assignedClasses' => $assignedClasses,
            'assignedSubjects' => $assignedSubjects,
            'unassignedClasses' => $unassignedClasses,
            'unassignedSubjects' => $unassignedSubjects,
        ]));
    }

    /**
     * صفحة إحصائيات سابقة للمعلم (الأسابيع الماضية فقط في السنة النشطة).
     */
    public function history(User $teacher)
    {
        if (! $teacher->matchesAdminTeacherListingCriteria()) {
            return redirect()->back()->with('error', 'المستخدم المحدد ليس معلم');
        }

        $stats = TeacherProgressService::getTeacherDetailStats($teacher, null);
        $pastWeeksProgress = TeacherProgressService::getTeacherPastWeeksProgress($teacher);

        return view('admin.pages.teachers.progress-history', array_merge($stats, [
            'pastWeeksProgress' => $pastWeeksProgress,
        ]));
    }

    /**
     * حفظ أو تحديث هدف الدروس الأسبوعية المخصص للمعلم لأسبوع معين
     */
    public function storeWeekTarget(Request $request, User $teacher)
    {
        if (! $teacher->matchesAdminTeacherListingCriteria()) {
            return redirect()->back()->with('error', 'المستخدم المحدد ليس معلم');
        }

        $request->validate([
            'academic_week_id' => 'required|exists:academic_weeks,id',
            'required_lessons_target' => 'required|integer|min:0',
        ]);

        $week = AcademicWeek::findOrFail((int) $request->academic_week_id);
        if (AcademicWeekService::isWeekPast($week)) {
            return redirect()->back()->with('error', 'لا يمكن تعديل هدف أسبوع منتهٍ');
        }

        TeacherWeekTarget::updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'academic_week_id' => $request->academic_week_id,
            ],
            ['required_lessons_target' => (int) $request->required_lessons_target]
        );

        return redirect()->back()->with('success', 'تم حفظ الهدف المخصص للأسبوع');
    }

    /**
     * حفظ أهداف الدروس الأسبوعية للمعلم لعدة أسابيع دفعة واحدة
     */
    public function storeWeekTargetsBulk(Request $request, User $teacher)
    {
        if (! $teacher->matchesAdminTeacherListingCriteria()) {
            return redirect()->back()->with('error', 'المستخدم المحدد ليس معلم');
        }

        $targets = $request->input('required_lessons_targets', []);
        if (! is_array($targets)) {
            $targets = [];
        }

        // Normalize empty values to 0
        foreach ($targets as $weekId => $value) {
            if ($value === '' || $value === null) {
                $targets[$weekId] = 0;
            }
        }

        $validator = Validator::make(
            ['required_lessons_targets' => $targets],
            [
                'required_lessons_targets' => ['required', 'array'],
                'required_lessons_targets.*' => ['required', 'integer', 'min:0'],
            ],
            [],
            ['required_lessons_targets.*' => 'هدف الدروس']
        );

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $activeWeeks = AcademicWeekService::getActiveYearWeeks();
        $activeWeekIds = $activeWeeks->pluck('id')->map(fn ($id) => (string) $id)->all();
        $activeWeekIdsInt = $activeWeeks->pluck('id')->all();
        $activeWeeksById = $activeWeeks->keyBy('id');

        // If any submitted week is past, reject the whole request (anti-tampering).
        foreach ($targets as $weekId => $value) {
            if (! in_array((string) $weekId, $activeWeekIds, true) && ! in_array((int) $weekId, $activeWeekIdsInt, true)) {
                continue;
            }

            $week = $activeWeeksById->get((int) $weekId);
            if ($week && AcademicWeekService::isWeekPast($week)) {
                return redirect()->back()->with('error', 'لا يمكن تعديل أهداف أسبوع منتهٍ');
            }
        }

        $updated = 0;
        foreach ($targets as $weekId => $value) {
            // Only allow saving for active-year weeks
            if (! in_array((string) $weekId, $activeWeekIds, true) && ! in_array((int) $weekId, $activeWeekIdsInt, true)) {
                continue;
            }

            TeacherWeekTarget::updateOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'academic_week_id' => (int) $weekId,
                ],
                ['required_lessons_target' => (int) $value]
            );
            $updated++;
        }

        return redirect()->back()->with('success', 'تم حفظ أهداف الأسابيع (' . $updated . ')');
    }
}
