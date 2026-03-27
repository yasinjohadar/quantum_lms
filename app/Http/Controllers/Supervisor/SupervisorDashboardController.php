<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupervisorDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'check.user.active']);
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->hasRole('supervisor')) {
                abort(403, 'غير مصرح لك بالوصول');
            }
            return $next($request);
        });
    }

    /**
     * عرض قائمة الصفوف المخصصة للمشرف
     */
    public function myClasses(Request $request)
    {
        $user = Auth::user();
        
        // جلب الصفوف المخصصة للمشرف فقط
        $classesQuery = $user->assignedClassesAsSupervisor()->with('stage');

        // فلترة حسب البحث
        if ($request->filled('search')) {
            $search = $request->input('search');
            $classesQuery->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // فلترة حسب المرحلة
        if ($request->filled('stage_id')) {
            $classesQuery->where('stage_id', $request->input('stage_id'));
        }

        $classes = $classesQuery->ordered()->paginate(12);
        $stages = \App\Models\Stage::ordered()->get();

        return view('supervisor.my-classes.index', compact('classes', 'stages'));
    }

    /**
     * عرض تفاصيل الصف
     */
    public function showClass(SchoolClass $class)
    {
        $user = Auth::user();
        
        // التحقق من التخصيص
        if (!$user->isAssignedToClassAsSupervisor($class->id)) {
            abort(403, 'غير مصرح لك بالوصول إلى هذا الصف');
        }
        
        // توحيد التجربة: افتح نفس صفحة تفاصيل الصف الخاصة بالأدمن
        return redirect()->route('admin.classes.show', $class->id);
    }

    /**
     * عرض قائمة المواد المخصصة للمشرف
     */
    public function mySubjects(Request $request)
    {
        $user = Auth::user();
        
        // جلب المواد المخصصة للمشرف (من الصفوف + المواد المباشرة)
        $accessibleSubjects = $user->getAccessibleSubjectsAsSupervisor();
        
        $subjectsQuery = $accessibleSubjects->with('schoolClass.stage');

        // فلترة حسب البحث
        if ($request->filled('search')) {
            $search = $request->input('search');
            $subjectsQuery->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // فلترة حسب الصف
        if ($request->filled('class_id')) {
            $subjectsQuery->where('class_id', $request->input('class_id'));
        }

        $subjects = $subjectsQuery->ordered()->paginate(12);
        $classes = $user->assignedClassesAsSupervisor()->with('stage')->get();

        return view('supervisor.my-subjects.index', compact('subjects', 'classes'));
    }

    /**
     * عرض تفاصيل المادة
     */
    public function showSubject(Subject $subject)
    {
        $user = Auth::user();
        
        // التحقق من التخصيص
        $isAssignedDirectly = $user->isAssignedToSubjectAsSupervisor($subject->id);
        $isAssignedViaClass = $user->isAssignedToClassAsSupervisor($subject->class_id);
        
        if (!$isAssignedDirectly && !$isAssignedViaClass) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه المادة');
        }
        
        // توحيد التجربة: افتح نفس صفحة إدارة المادة الخاصة بالأدمن
        return redirect()->route('admin.subjects.show', $subject->id);
    }
}
