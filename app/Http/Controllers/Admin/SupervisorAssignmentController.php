<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\LoginLog;
use App\Models\UserSession;
use Illuminate\Http\Request;

class SupervisorAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:supervisor-assignment-list'])->only('index');
        $this->middleware(['permission:supervisor-assignment-show'])->only('show');
        $this->middleware(['permission:supervisor-assignment-update'])->only('update');
    }

    /**
     * عرض قائمة المشرفين لإدارة التخصيصات
     */
    public function index(Request $request)
    {
        $supervisorsQuery = User::supervisors()->with(['assignedClassesAsSupervisor', 'assignedSubjectsAsSupervisor']);

        // فلترة حسب البحث
        if ($request->filled('search')) {
            $search = $request->input('search');
            $supervisorsQuery->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $supervisors = $supervisorsQuery->orderBy('name')->paginate(20);

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
        
        // إحصائيات
        $totalSupervisors = User::supervisors()->count();
        $assignedSupervisors = User::supervisors()
            ->where(function($q) {
                $q->whereHas('assignedClassesAsSupervisor')
                  ->orWhereHas('assignedSubjectsAsSupervisor');
            })
            ->count();
        $unassignedSupervisors = $totalSupervisors - $assignedSupervisors;

        return view('admin.pages.supervisors.index', compact(
            'supervisors', 
            'totalSupervisors', 
            'assignedSupervisors', 
            'unassignedSupervisors',
            'lastLogins',
            'onlineUserIds'
        ));
    }

    /**
     * عرض صفحة تخصيص المشرف
     */
    public function show(User $supervisor)
    {
        // التحقق من أن المستخدم مشرف
        if (!$supervisor->hasRole('supervisor')) {
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
        if (! $supervisor->hasRole('supervisor')) {
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
