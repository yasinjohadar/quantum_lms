<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SupervisorAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:supervisor-assignment-list'])->only(['index', 'getSubjectsByClass']);
        $this->middleware(['permission:supervisor-assignment-show'])->only('show');
        $this->middleware(['permission:supervisor-assignment-update'])->only('update');
    }

    /**
     * عرض قائمة المشرفين لإدارة التخصيصات
     */
    public function index(Request $request)
    {
        $request->validate([
            'class_id' => 'nullable|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'role' => 'nullable|string|exists:roles,name',
        ]);

        $supervisorsQuery = User::supervisors()->with(['roles', 'assignedClassesAsSupervisor', 'assignedSubjectsAsSupervisor']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $supervisorsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('class_id')) {
            $classId = (int) $request->input('class_id');
            $supervisorsQuery->where(function ($q) use ($classId) {
                $q->whereHas('assignedClassesAsSupervisor', function ($q2) use ($classId) {
                    $q2->where('classes.id', $classId);
                })->orWhereHas('assignedSubjectsAsSupervisor', function ($q2) use ($classId) {
                    $q2->where('subjects.class_id', $classId);
                });
            });
        }

        if ($request->filled('subject_id')) {
            $subjectId = (int) $request->input('subject_id');
            $supervisorsQuery->whereHas('assignedSubjectsAsSupervisor', function ($q) use ($subjectId) {
                $q->where('subjects.id', $subjectId);
            });
        }

        if ($request->filled('role')) {
            $roleName = $request->input('role');
            $rolesTable = config('permission.table_names.roles', 'roles');
            $supervisorsQuery->whereHas('roles', function ($q) use ($roleName, $rolesTable) {
                $q->where('name', $roleName);
                if (Schema::hasColumn($rolesTable, 'staff_profile')) {
                    $q->where('staff_profile', 'supervisor');
                }
            });
        }

        $perPage = min(100, max(1, (int) $request->input('per_page', 25)));
        $supervisors = $supervisorsQuery->orderBy('name')->paginate($perPage);

        $ids = $supervisors->pluck('id');
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

        $totalSupervisors = User::supervisors()->count();
        $assignedSupervisors = User::supervisors()
            ->where(function ($q) {
                $q->whereHas('assignedClassesAsSupervisor')
                    ->orWhereHas('assignedSubjectsAsSupervisor');
            })
            ->count();
        $unassignedSupervisors = $totalSupervisors - $assignedSupervisors;

        $filterClasses = SchoolClass::with('stage')->ordered()->get();
        $filterSubjects = collect();
        $rolesTable = config('permission.table_names.roles', 'roles');
        $filterRolesQuery = Role::query()->orderBy('name');
        if (Schema::hasColumn($rolesTable, 'staff_profile')) {
            $filterRolesQuery->where('staff_profile', 'supervisor');
        } else {
            $filterRolesQuery->where('name', 'supervisor');
        }
        $filterRoles = $filterRolesQuery->get(['name']);
        if ($request->filled('class_id')) {
            $filterSubjects = Subject::active()->ordered()->where('class_id', $request->input('class_id'))->get();
        }

        if ($request->expectsJson() || $request->ajax()) {
            $html = view('admin.pages.supervisors.partials.table-rows', compact('supervisors', 'lastLogins', 'onlineUserIds'))->render();
            $pagination = view('admin.pages.supervisors.partials.pagination', compact('supervisors'))->render();
            $modals = view('admin.pages.supervisors.partials.delete-modals', compact('supervisors'))->render();
            $impersonateModals = view('admin.pages.users.partials.impersonate-modals', ['users' => $supervisors])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination,
                'modals' => $modals,
                'impersonate_modals' => $impersonateModals,
                'count' => $supervisors->total(),
            ]);
        }

        return view('admin.pages.supervisors.index', compact(
            'supervisors',
            'totalSupervisors',
            'assignedSupervisors',
            'unassignedSupervisors',
            'lastLogins',
            'onlineUserIds',
            'filterClasses',
            'filterSubjects',
            'filterRoles',
        ));
    }

    /**
     * المواد النشطة حسب الصف (للفلاتر عبر Ajax)
     */
    public function getSubjectsByClass(Request $request)
    {
        $classId = $request->input('class_id');

        if (! $classId) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $request->validate([
            'class_id' => 'exists:classes,id',
        ]);

        $subjects = Subject::with('schoolClass.stage')
            ->active()
            ->ordered()
            ->where('class_id', $classId)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subjects,
        ]);
    }

    /**
     * عرض صفحة تخصيص المشرف
     */
    public function show(User $supervisor)
    {
        // التحقق من أن المستخدم مشرف
        if (! $supervisor->hasSupervisorStaffIdentity()) {
            return redirect()->back()->with('error', 'المستخدم المحدد ليس مشرف');
        }

        $assignedClasses = $supervisor->assignedClassesAsSupervisor()->with('stage')->get();
        $assignedSubjects = $supervisor->assignedSubjectsAsSupervisor()->with('schoolClass.stage')->get();

        // جميع الصفوف والمواد المتاحة
        $allClasses = SchoolClass::with('stage')->ordered()->get();
        $allSubjects = Subject::with('schoolClass.stage')->ordered()->get();

        return view('admin.pages.supervisors.assignments', compact(
            'supervisor',
            'assignedClasses',
            'assignedSubjects',
            'allClasses',
            'allSubjects'
        ));
    }

    /**
     * تحديث تخصيصات المشرف
     */
    public function update(Request $request, User $supervisor)
    {
        if (! $supervisor->hasSupervisorStaffIdentity()) {
            return redirect()->back()->with('error', 'المستخدم المحدد ليس مشرف');
        }

        $request->validate([
            'classes' => 'nullable|array',
            'classes.*' => 'exists:classes,id',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ]);

        $assignedBy = auth()->id();
        $assignedAt = now();
        $canManageClasses = auth()->user()?->can('supervisor-assignment-manage-classes');
        $canManageSubjects = auth()->user()?->can('supervisor-assignment-manage-subjects');

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
            $supervisor->assignedClassesAsSupervisor()->sync($classesData);
        }

        // تحديث المواد المخصصة
        if ($canManageSubjects) {
            $subjectsData = [];
            if ($request->has('subjects') && is_array($request->input('subjects'))) {
                foreach ($request->input('subjects') as $subjectId) {
                    $subjectsData[$subjectId] = [
                        'assigned_by' => $assignedBy,
                        'assigned_at' => $assignedAt,
                    ];
                }
            }
            $supervisor->assignedSubjectsAsSupervisor()->sync($subjectsData);
        }

        return redirect()->back()->with('success', 'تم تحديث تخصيصات المشرف بنجاح');
    }
}
