<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Services\AcademicWeekService;
use App\Services\TeacherProgressService;
use App\Models\UserSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class TeacherAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:teacher-assignment-list'])->only('index');
        $this->middleware(['permission:teacher-assignment-show'])->only('show');
        $this->middleware(['permission:teacher-assignment-update'])->only([
            'update',
            'attachClass',
            'detachClass',
            'attachSubject',
            'detachSubject',
            'patchSubjectRequiredPages',
        ]);
    }

    /**
     * إعادة التوجيه إلى صفحة تقدم المعلم مع الحفاظ على week_id عند وجوده.
     */
    private function redirectToTeacherProgress(User $teacher, ?string $message = null, string $messageKey = 'success'): RedirectResponse
    {
        $url = route('admin.teachers.progress.show', $teacher);
        if (request()->filled('week_id')) {
            $url .= '?week_id='.(int) request('week_id');
        }
        if ($message === null) {
            return redirect()->to($url);
        }

        return redirect()->to($url)->with($messageKey, $message);
    }

    /**
     * إرفاق جميع مواد صف واحد بالمعلم (بدون استبدال المواد الأخرى).
     */
    private function attachAllSubjectsForClass(User $teacher, int $classId, ?int $assignedBy, $assignedAt): void
    {
        $subjectIds = Subject::query()->where('class_id', $classId)->pluck('id')->all();
        foreach ($subjectIds as $sid) {
            if ($teacher->assignedSubjects()->where('subjects.id', (int) $sid)->exists()) {
                continue;
            }
            $teacher->assignedSubjects()->attach((int) $sid, [
                'assigned_by' => $assignedBy,
                'assigned_at' => $assignedAt,
                'required_pages' => null,
            ]);
        }
    }

    /**
     * عرض قائمة المعلمين لإدارة التخصيصات
     */
    public function index(Request $request)
    {
        $request->validate([
            'role' => 'nullable|string|exists:roles,name',
        ]);

        $perPage = min(100, max(1, (int) $request->input('per_page', 25)));

        $teachersQuery = User::teachers()->with([
            'roles',
            'assignedClasses',
            'assignedSubjects' => fn ($q) => $q->withTrashed(),
        ]);

        // فلترة حسب البحث
        if ($request->filled('search')) {
            $search = $request->input('search');
            $teachersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        // فلتر التخصيص
        $assignment = $request->input('assignment', 'all');
        if ($assignment === 'assigned') {
            $teachersQuery->where(function ($q) {
                $q->whereHas('assignedClasses')->orWhereHas('assignedSubjects');
            });
        } elseif ($assignment === 'unassigned') {
            $teachersQuery->whereDoesntHave('assignedClasses')
                ->whereDoesntHave('assignedSubjects');
        }

        if ($request->filled('role')) {
            $roleName = $request->input('role');
            $rolesTable = config('permission.table_names.roles', 'roles');
            $teachersQuery->whereHas('roles', function ($q) use ($roleName, $rolesTable) {
                $q->where('name', $roleName);
                if (Schema::hasColumn($rolesTable, 'staff_profile')) {
                    $q->where('staff_profile', 'teacher');
                }
            });
        }

        $pagesProgressFilter = $request->input('pages_progress', 'all');
        $weeklyProgressFilter = $request->input('weekly_progress', 'all');
        $hasProgressFilter = $pagesProgressFilter !== 'all' || $weeklyProgressFilter !== 'all';

        $sort = $request->input('sort', 'name_asc');
        $validSorts = ['name_asc', 'name_desc', 'pages_asc', 'pages_desc', 'weekly_asc', 'weekly_desc'];
        if (! in_array($sort, $validSorts, true)) {
            $sort = 'name_asc';
        }
        $sortByProgress = in_array($sort, ['pages_asc', 'pages_desc', 'weekly_asc', 'weekly_desc'], true);
        $needProgressData = $hasProgressFilter || $sortByProgress;

        $weekId = $request->input('week_id') ? (int) $request->input('week_id') : null;

        if ($needProgressData) {
            // حد أقصى 500 معلم للفلترة/الترتيب حسب التقدم (أداء)، وليس له علاقة بعدد الدروس أو الصفحات في إحصائيات التقدم.
            $allTeachers = $teachersQuery->orderBy('name')->limit(500)->get();
            $progress = TeacherProgressService::getTeachersProgressSummary($allTeachers, $weekId);

            $filtered = $allTeachers->filter(function ($teacher) use ($progress, $pagesProgressFilter, $weeklyProgressFilter) {
                $p = $progress[$teacher->id] ?? null;
                if (! $p) {
                    return $pagesProgressFilter === 'all' && $weeklyProgressFilter === 'all';
                }
                $matchPages = true;
                if ($pagesProgressFilter !== 'all') {
                    $perc = $p['pages_percentage'];
                    $matchPages = match ($pagesProgressFilter) {
                        'below_50' => $perc !== null && $perc < 50,
                        'below_100' => $perc !== null && $perc < 100,
                        'completed' => $perc !== null && $perc >= 100,
                        default => true,
                    };
                }
                $matchWeekly = true;
                if ($weeklyProgressFilter !== 'all') {
                    $perc = $p['weekly_percentage'];
                    $matchWeekly = match ($weeklyProgressFilter) {
                        'below_50' => $perc !== null && $perc < 50,
                        'below_100' => $perc !== null && $perc < 100,
                        'completed' => $perc !== null && $perc >= 100,
                        default => true,
                    };
                }

                return $matchPages && $matchWeekly;
            });

            $filtered = $filtered->values();

            // ترتيب النتائج
            if ($sort === 'name_asc') {
                $filtered = $filtered->sortBy('name')->values();
            } elseif ($sort === 'name_desc') {
                $filtered = $filtered->sortByDesc('name')->values();
            } else {
                // ترتيب حسب النسبة: null في النهاية عند تصاعدي، في البداية عند تنازلي
                $filtered = $filtered->sort(function ($a, $b) use ($progress, $sort) {
                    $pA = $progress[$a->id] ?? null;
                    $pB = $progress[$b->id] ?? null;
                    $valA = match ($sort) {
                        'pages_asc', 'pages_desc' => $pA['pages_percentage'] ?? null,
                        'weekly_asc', 'weekly_desc' => $pA['weekly_percentage'] ?? null,
                        default => null,
                    };
                    $valB = match ($sort) {
                        'pages_asc', 'pages_desc' => $pB['pages_percentage'] ?? null,
                        'weekly_asc', 'weekly_desc' => $pB['weekly_percentage'] ?? null,
                        default => null,
                    };
                    $nullLast = $sort === 'pages_asc' || $sort === 'weekly_asc';
                    if ($valA === null && $valB === null) {
                        return strcmp($a->name, $b->name);
                    }
                    if ($valA === null) {
                        return $nullLast ? 1 : -1;
                    }
                    if ($valB === null) {
                        return $nullLast ? -1 : 1;
                    }
                    $cmp = $valA <=> $valB;
                    if ($cmp !== 0) {
                        return ($sort === 'pages_desc' || $sort === 'weekly_desc') ? -$cmp : $cmp;
                    }

                    return strcmp($a->name, $b->name);
                })->values();
            }

            $page = (int) $request->input('page', 1);
            $total = $filtered->count();
            $slice = $filtered->slice(($page - 1) * $perPage, $perPage)->values();
            $teachers = new LengthAwarePaginator(
                $slice,
                $total,
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            $teachersProgress = TeacherProgressService::getTeachersProgressSummary(new Collection($slice->all()), $weekId);
        } else {
            $nameDir = $sort === 'name_desc' ? 'desc' : 'asc';
            $teachers = $teachersQuery->orderBy('name', $nameDir)->paginate($perPage);
            $teachers->withQueryString();
            $teachersProgress = TeacherProgressService::getTeachersProgressSummary($teachers->getCollection(), $weekId);
        }

        $ids = $teachers->pluck('id');
        $lastLogins = $ids->isNotEmpty()
            ? LoginLog::whereIn('user_id', $ids)
                ->where('is_successful', true)
                ->selectRaw('user_id, max(login_at) as last_login_at')
                ->groupBy('user_id')
                ->pluck('last_login_at', 'user_id')
            : collect();
        $onlineUserIds = $ids->isNotEmpty()
            ? UserSession::whereIn('user_id', $ids)->where('status', 'active')->distinct()->pluck('user_id')
            : collect();

        // إحصائيات
        $totalTeachers = User::teachers()->count();
        $assignedTeachers = User::teachers()
            ->where(function ($q) {
                $q->whereHas('assignedClasses')
                    ->orWhereHas('assignedSubjects');
            })
            ->count();
        $unassignedTeachers = $totalTeachers - $assignedTeachers;

        $activeWeeks = AcademicWeekService::getActiveYearWeeks();
        $currentWeek = AcademicWeekService::getCurrentAcademicWeek();
        $rolesTable = config('permission.table_names.roles', 'roles');
        $filterRolesQuery = Role::query()->orderBy('name');
        if (Schema::hasColumn($rolesTable, 'staff_profile')) {
            $filterRolesQuery->where('staff_profile', 'teacher');
        } else {
            $filterRolesQuery->where('name', 'teacher');
        }
        $filterRoles = $filterRolesQuery->get(['name']);

        if ($request->expectsJson() || $request->ajax()) {
            $html = view('admin.pages.teachers.partials.table-rows', compact('teachers', 'teachersProgress', 'lastLogins', 'onlineUserIds'))->render();
            $pagination = view('admin.pages.teachers.partials.pagination', compact('teachers'))->render();
            $impersonateModals = view('admin.pages.users.partials.impersonate-modals', ['users' => $teachers])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination,
                'impersonate_modals' => $impersonateModals,
                'count' => $teachers->total(),
            ]);
        }

        return view('admin.pages.teachers.index', compact(
            'teachers',
            'teachersProgress',
            'totalTeachers',
            'assignedTeachers',
            'unassignedTeachers',
            'lastLogins',
            'onlineUserIds',
            'activeWeeks',
            'currentWeek',
            'filterRoles'
        ));
    }

    /**
     * عرض صفحة تخصيص المعلم
     */
    public function show(User $teacher)
    {
        if (! $teacher->matchesAdminTeacherListingCriteria()) {
            return redirect()->back()->with('error', 'المستخدم المحدد ليس معلم');
        }

        $assignedClasses = $teacher->assignedClasses()->with('stage')->get();
        $assignedSubjects = $teacher->assignedSubjects()->withTrashed()->with([
            'schoolClass' => fn ($q) => $q->withTrashed(),
            'schoolClass.stage',
        ])->get();

        // جميع الصفوف والمواد المتاحة
        $allClasses = SchoolClass::with('stage')->ordered()->get();
        $allSubjects = Subject::with('schoolClass.stage')->ordered()->get();

        $activeWeeks = AcademicWeekService::getActiveYearWeeks();
        $teacherProgressStats = TeacherProgressService::getTeacherDetailStats($teacher, null);
        $yearWeeksLessons = $activeWeeks->isNotEmpty()
            ? TeacherProgressService::getTeacherActiveYearWeeksLessonsBreakdown($teacher, $activeWeeks)
            : [
                'per_week' => [],
                'year_total_target' => 0,
                'year_total_completed' => 0,
                'year_percentage' => null,
            ];

        return view('admin.pages.teachers.assignments', compact(
            'teacher',
            'assignedClasses',
            'assignedSubjects',
            'allClasses',
            'allSubjects',
            'teacherProgressStats',
            'yearWeeksLessons',
        ));
    }

    /**
     * تحديث تخصيصات المعلم
     */
    public function update(Request $request, User $teacher)
    {
        if (! $teacher->matchesAdminTeacherListingCriteria()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'المستخدم المحدد ليس معلم'], 422);
            }

            return redirect()->back()->with('error', 'المستخدم المحدد ليس معلم');
        }

        $request->validate([
            'classes' => 'nullable|array',
            'classes.*' => 'exists:classes,id',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
            'required_pages' => 'nullable|array',
            'required_pages.*' => 'nullable|integer|min:0',
            'weekly_lessons_target' => 'nullable|integer|min:0',
        ]);

        $this->applyTeacherAssignmentsFromRequest($request, $teacher);

        if ($request->wantsJson()) {
            return response()->json($this->buildAssignmentsSyncJsonResponse($teacher));
        }

        return redirect()->back()->with('success', 'تم تحديث تخصيصات المعلم بنجاح');
    }

    /**
     * تطبيق حمولة النموذج على علاقات المعلم (صفوف ومواد وأهداف أسبوعية).
     */
    private function applyTeacherAssignmentsFromRequest(Request $request, User $teacher): void
    {
        $assignedBy = auth()->id();
        $assignedAt = now();
        $canManageClasses = auth()->user()?->can('teacher-assignment-manage-classes');
        $canManageSubjects = auth()->user()?->can('teacher-assignment-manage-subjects');

        $classesData = [];
        if ($canManageClasses) {
            if ($request->has('classes') && is_array($request->input('classes'))) {
                foreach ($request->input('classes') as $classId) {
                    $classesData[$classId] = [
                        'assigned_by' => $assignedBy,
                        'assigned_at' => $assignedAt,
                    ];
                }
            }
            $teacher->assignedClasses()->sync($classesData);
        }

        $keptClassIds = $canManageClasses
            ? array_values(array_unique(array_map('intval', array_keys($classesData))))
            : null;

        if ($canManageSubjects) {
            $subjectsData = [];
            if ($request->has('subjects') && is_array($request->input('subjects'))) {
                foreach ($request->input('subjects') as $subjectId) {
                    $requiredPages = $request->input('required_pages.'.$subjectId);
                    $subjectsData[(int) $subjectId] = [
                        'assigned_by' => $assignedBy,
                        'assigned_at' => $assignedAt,
                        'required_pages' => $requiredPages !== null && $requiredPages !== '' ? (int) $requiredPages : null,
                    ];
                }
            }

            // عند عدم اختيار أي صف: إفراغ المواد كما كان. عند وجود صفوف محددة: نحترم قائمة المواد المرسلة كاملةً
            // (يشمل تخصيص مواد من صف دون تفعيل «تضمين الصف» في العمود الرئيسي — قسم التخصيص المفصّل).
            if ($keptClassIds !== null && $keptClassIds === []) {
                $subjectsData = [];
            }

            $teacher->assignedSubjects()->sync($subjectsData);
        } elseif ($canManageClasses && $keptClassIds !== null) {
            if ($keptClassIds === []) {
                $teacher->assignedSubjects()->detach();
            } else {
                $orphanSubjectIds = $teacher->assignedSubjects()
                    ->whereNotIn('subjects.class_id', $keptClassIds)
                    ->pluck('subjects.id');
                if ($orphanSubjectIds->isNotEmpty()) {
                    $teacher->assignedSubjects()->detach($orphanSubjectIds->all());
                }
            }
        }

        if ($canManageSubjects || $canManageClasses) {
            $teacher->weekly_lessons_target = $request->input('weekly_lessons_target') !== null && $request->input('weekly_lessons_target') !== ''
                ? (int) $request->input('weekly_lessons_target')
                : null;
            $teacher->save();
        }
    }

    /**
     * بيانات JSON بعد الحفظ (أجزاء HTML + حالة التخصيص) لتحديث الواجهة دون إعادة تحميل الصفحة.
     *
     * @return array<string, mixed>
     */
    private function buildAssignmentsSyncJsonResponse(User $teacher): array
    {
        $teacher->refresh();

        $assignedClasses = $teacher->assignedClasses()->with('stage')->get();
        $assignedSubjects = $teacher->assignedSubjects()->withTrashed()->with([
            'schoolClass' => fn ($q) => $q->withTrashed(),
            'schoolClass.stage',
        ])->get();
        $allClasses = SchoolClass::with('stage')->ordered()->get();
        $allSubjects = Subject::with('schoolClass.stage')->ordered()->get();

        $activeWeeks = AcademicWeekService::getActiveYearWeeks();
        $teacherProgressStats = TeacherProgressService::getTeacherDetailStats($teacher, null);
        $yearWeeksLessons = $activeWeeks->isNotEmpty()
            ? TeacherProgressService::getTeacherActiveYearWeeksLessonsBreakdown($teacher, $activeWeeks)
            : [
                'per_week' => [],
                'year_total_target' => 0,
                'year_total_completed' => 0,
                'year_percentage' => null,
            ];

        $requiredPages = [];
        foreach ($assignedSubjects as $s) {
            $requiredPages[(int) $s->id] = $s->pivot->required_pages !== null && $s->pivot->required_pages !== ''
                ? (int) $s->pivot->required_pages
                : null;
        }

        return [
            'ok' => true,
            'message' => 'تم تحديث تخصيصات المعلم بنجاح',
            'assigned_classes_count' => $assignedClasses->count(),
            'assigned_subjects_count' => $assignedSubjects->count(),
            'assigned_class_ids' => $assignedClasses->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'assigned_subject_ids' => $assignedSubjects->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'required_pages' => $requiredPages,
            'html' => [
                'progress_card' => view('admin.pages.teachers.partials.assignments-sync.progress-card', compact(
                    'teacher',
                    'teacherProgressStats',
                    'yearWeeksLessons',
                    'assignedClasses',
                    'assignedSubjects'
                ))->render(),
                'side_panel' => view('admin.pages.teachers.partials.assignments-sync.side-panel-inner', compact(
                    'assignedSubjects'
                ))->render(),
                'indep_body' => view('admin.pages.teachers.partials.assignments-sync.indep-card-body', compact(
                    'allClasses',
                    'allSubjects',
                    'assignedSubjects'
                ))->render(),
            ],
        ];
    }

    /**
     * إرفاق صف واحد بالمعلم (من صفحة التقدم أو غيرها).
     */
    public function attachClass(Request $request, User $teacher)
    {
        if (! $teacher->matchesAdminTeacherListingCriteria()) {
            return redirect()->back()->with('error', 'المستخدم المحدد ليس معلم');
        }

        if (! auth()->user()?->can('teacher-assignment-manage-classes')) {
            abort(403);
        }

        $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        $classId = (int) $request->input('class_id');
        if ($teacher->assignedClasses()->where('classes.id', $classId)->exists()) {
            return $this->redirectToTeacherProgress($teacher, 'هذا الصف مخصّص للمعلم بالفعل.');
        }

        $teacher->assignedClasses()->attach($classId, [
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
        ]);

        if (auth()->user()?->can('teacher-assignment-manage-subjects')) {
            $this->attachAllSubjectsForClass($teacher, $classId, auth()->id(), now());
        }

        return $this->redirectToTeacherProgress($teacher, 'تم إضافة الصف للمعلم.');
    }

    /**
     * فصل صف واحد عن المعلم.
     */
    public function detachClass(User $teacher, SchoolClass $schoolClass)
    {
        if (! $teacher->matchesAdminTeacherListingCriteria()) {
            return redirect()->back()->with('error', 'المستخدم المحدد ليس معلم');
        }

        if (! auth()->user()?->can('teacher-assignment-manage-classes')) {
            abort(403);
        }

        if (! $teacher->assignedClasses()->where('classes.id', $schoolClass->id)->exists()) {
            return $this->redirectToTeacherProgress($teacher, 'المعلم غير مخصّص لهذا الصف.', 'error');
        }

        $teacher->assignedClasses()->detach($schoolClass->id);

        if (auth()->user()?->can('teacher-assignment-manage-subjects')) {
            $subjectIds = Subject::query()->where('class_id', $schoolClass->id)->pluck('id')->all();
            if ($subjectIds !== []) {
                $teacher->assignedSubjects()->detach($subjectIds);
            }
        }

        return $this->redirectToTeacherProgress($teacher, 'تم فصل الصف عن المعلم.');
    }

    /**
     * إرفاق مادة واحدة بالمعلم.
     */
    public function attachSubject(Request $request, User $teacher)
    {
        if (! $teacher->matchesAdminTeacherListingCriteria()) {
            return redirect()->back()->with('error', 'المستخدم المحدد ليس معلم');
        }

        if (! auth()->user()?->can('teacher-assignment-manage-subjects')) {
            abort(403);
        }

        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'required_pages' => 'nullable|integer|min:0',
        ]);

        $subjectId = (int) $request->input('subject_id');
        if ($teacher->assignedSubjects()->where('subjects.id', $subjectId)->exists()) {
            return $this->redirectToTeacherProgress($teacher, 'هذه المادة مخصّصة للمعلم بالفعل.');
        }

        $requiredPages = $request->input('required_pages');
        $requiredPagesValue = ($requiredPages !== null && $requiredPages !== '') ? (int) $requiredPages : null;

        $teacher->assignedSubjects()->attach($subjectId, [
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'required_pages' => $requiredPagesValue,
        ]);

        return $this->redirectToTeacherProgress($teacher, 'تم إضافة المادة للمعلم.');
    }

    /**
     * فصل مادة واحدة عن المعلم.
     */
    public function detachSubject(User $teacher, Subject $subject)
    {
        if (! $teacher->matchesAdminTeacherListingCriteria()) {
            return redirect()->back()->with('error', 'المستخدم المحدد ليس معلم');
        }

        if (! auth()->user()?->can('teacher-assignment-manage-subjects')) {
            abort(403);
        }

        if (! $teacher->assignedSubjects()->where('subjects.id', $subject->id)->exists()) {
            return $this->redirectToTeacherProgress($teacher, 'المعلم غير مخصّص لهذه المادة.', 'error');
        }

        $teacher->assignedSubjects()->detach($subject->id);

        return $this->redirectToTeacherProgress($teacher, 'تم فصل المادة عن المعلم.');
    }

    /**
     * تحديث عدد الصفحات المطلوبة لمادة مخصّصة (لصفحة تقدم المعلم + طلبات Ajax).
     */
    public function patchSubjectRequiredPages(Request $request, User $teacher, Subject $subject)
    {
        if (! $teacher->matchesAdminTeacherListingCriteria()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'المستخدم المحدد ليس معلم'], 422);
            }

            return redirect()->back()->with('error', 'المستخدم المحدد ليس معلم');
        }

        if (! auth()->user()?->can('teacher-assignment-manage-subjects')) {
            abort(403);
        }

        if (! $teacher->assignedSubjects()->withTrashed()->where('subjects.id', $subject->id)->exists()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'المعلم غير مخصّص لهذه المادة.'], 422);
            }

            return redirect()->back()->with('error', 'المعلم غير مخصّص لهذه المادة.');
        }

        $request->validate([
            'required_pages' => ['nullable', 'integer', 'min:0'],
        ]);

        $raw = $request->input('required_pages');
        $value = ($raw === null || $raw === '') ? null : (int) $raw;

        $teacher->assignedSubjects()->updateExistingPivot($subject->id, [
            'required_pages' => $value,
        ]);

        $teacher->unsetRelation('assignedSubjects');

        $weekId = $request->input('week_id');
        $weekId = ($weekId !== null && $weekId !== '') ? (int) $weekId : null;

        $rowSubject = $teacher->assignedSubjects()->withTrashed()->where('subjects.id', $subject->id)->first();
        $required = (int) ($rowSubject?->pivot?->required_pages ?? 0);
        $completed = TeacherProgressService::getSubjectCompletedPages($subject->id);
        $remaining = max(0, $required - $completed);
        $percentage = $required > 0
            ? min(100.0, round(($completed / $required) * 100, 1))
            : null;

        if ($request->wantsJson()) {
            $stats = TeacherProgressService::getTeacherDetailStats($teacher->fresh(), $weekId);

            return response()->json([
                'ok' => true,
                'subject_id' => (int) $subject->id,
                'required_pages' => $value,
                'required_pages_effective' => $required,
                'completed_pages' => $completed,
                'remaining_pages' => $remaining,
                'percentage' => $percentage,
                'summary' => [
                    'total_pages_required' => $stats['total_pages_required'],
                    'total_pages_completed' => $stats['total_pages_completed'],
                    'total_pages_percentage' => $stats['total_pages_percentage'],
                ],
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث الصفحات المطلوبة.');
    }
}
