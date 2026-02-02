<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\LoginLog;
use App\Models\UserSession;
use Illuminate\Http\Request;

class TeacherAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:user-edit']);
    }

    /**
     * عرض قائمة المعلمين لإدارة التخصيصات
     */
    public function index(Request $request)
    {
        $teachersQuery = User::teachers()->with(['assignedClasses', 'assignedSubjects']);

        // فلترة حسب البحث
        if ($request->filled('search')) {
            $search = $request->input('search');
            $teachersQuery->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $teachers = $teachersQuery->orderBy('name')->paginate(20);

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
            ->where(function($q) {
                $q->whereHas('assignedClasses')
                  ->orWhereHas('assignedSubjects');
            })
            ->count();
        $unassignedTeachers = $totalTeachers - $assignedTeachers;

        return view('admin.pages.teachers.index', compact(
            'teachers', 
            'totalTeachers', 
            'assignedTeachers', 
            'unassignedTeachers',
            'lastLogins',
            'onlineUserIds'
        ));
    }

    /**
     * عرض صفحة تخصيص المعلم
     */
    public function show(User $teacher)
    {
        // التحقق من أن المستخدم معلم
        if (!$teacher->hasRole('teacher')) {
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
        $request->validate([
            'classes' => 'nullable|array',
            'classes.*' => 'exists:classes,id',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ]);

        $assignedBy = auth()->id();
        $assignedAt = now();

        // تحديث الصفوف المخصصة
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

        // تحديث المواد المخصصة
        $subjectsData = [];
        if ($request->has('subjects') && is_array($request->input('subjects'))) {
            foreach ($request->input('subjects') as $subjectId) {
                $subjectsData[$subjectId] = [
                    'assigned_by' => $assignedBy,
                    'assigned_at' => $assignedAt,
                ];
            }
        }
        $teacher->assignedSubjects()->sync($subjectsData);

        return redirect()->back()->with('success', 'تم تحديث تخصيصات المعلم بنجاح');
    }
}
