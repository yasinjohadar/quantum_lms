<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserSession;
use App\Services\AcademicWeekService;
use App\Services\TeacherProgressService;
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
        $this->middleware(['permission:teacher-assignment-update'])->only('update');
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

        $teachersQuery = User::teachers()->with(['roles', 'assignedClasses', 'assignedSubjects']);

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
            // جلب كل المعلمين المطابقين (حد أقصى 500) ثم فلترة وترتيب حسب النسب
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
        if (! $teacher->hasTeacherStaffIdentity()) {
            return redirect()->back()->with('error', 'المستخدم المحدد ليس معلم');
        }

        $assignedClasses = $teacher->assignedClasses()->with('stage')->get();
        $assignedSubjects = $teacher->assignedSubjects()->with('schoolClass.stage')->get();

        // جميع الصفوف والمواد المتاحة
        $allClasses = SchoolClass::with('stage')->ordered()->get();
        $allSubjects = Subject::with('schoolClass.stage')->ordered()->get();

        return view('admin.pages.teachers.assignments', compact(
            'teacher',
            'assignedClasses',
            'assignedSubjects',
            'allClasses',
            'allSubjects'
        ));
    }

    /**
     * تحديث تخصيصات المعلم
     */
    public function update(Request $request, User $teacher)
    {
        if (! $teacher->hasTeacherStaffIdentity()) {
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

        $assignedBy = auth()->id();
        $assignedAt = now();
        $canManageClasses = auth()->user()?->can('teacher-assignment-manage-classes');
        $canManageSubjects = auth()->user()?->can('teacher-assignment-manage-subjects');

        // تحديث الصفوف المخصصة
        if ($canManageClasses) {
            $classesData = [];
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

        // تحديث المواد المخصصة (مع عدد الصفحات المطلوبة)
        if ($canManageSubjects) {
            $subjectsData = [];
            if ($request->has('subjects') && is_array($request->input('subjects'))) {
                foreach ($request->input('subjects') as $subjectId) {
                    $requiredPages = $request->input('required_pages.'.$subjectId);
                    $subjectsData[$subjectId] = [
                        'assigned_by' => $assignedBy,
                        'assigned_at' => $assignedAt,
                        'required_pages' => $requiredPages !== null && $requiredPages !== '' ? (int) $requiredPages : null,
                    ];
                }
            }
            $teacher->assignedSubjects()->sync($subjectsData);
        }

        // تحديث عدد الدروس الأسبوعية المطلوبة
        if ($canManageSubjects || $canManageClasses) {
            $teacher->weekly_lessons_target = $request->input('weekly_lessons_target') !== null && $request->input('weekly_lessons_target') !== ''
                ? (int) $request->input('weekly_lessons_target')
                : null;
            $teacher->save();
        }

        return redirect()->back()->with('success', 'تم تحديث تخصيصات المعلم بنجاح');
    }
}
