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

        // جلب المواد التابعة للصف
        $subjects = $class->subjects()->with('schoolClass.stage')->orderBy('order')->get();
        
        // جلب الطلاب المسجلين في الصف
        $enrolledStudents = \App\Models\ClassEnrollment::where('class_id', $class->id)
            ->where('status', 'active')
            ->with('user')
            ->paginate(20);
        
        // إحصائيات
        $stats = [
            'total_subjects' => $subjects->count(),
            'total_students' => \App\Models\ClassEnrollment::where('class_id', $class->id)
                ->where('status', 'active')
                ->count(),
            'total_quizzes' => \App\Models\Quiz::whereHas('subject', function($q) use ($class) {
                $q->where('class_id', $class->id);
            })->count(),
        ];

        return view('supervisor.my-classes.show', compact('class', 'subjects', 'enrolledStudents', 'stats'));
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

        // جلب أقسام المادة
        $sections = $subject->sections()->with('units.lessons')->get();
        
        // جلب الطلاب المسجلين في المادة
        $enrolledStudents = \App\Models\Enrollment::where('subject_id', $subject->id)
            ->where('status', 'active')
            ->with('user')
            ->paginate(20);
        
        // جلب الاختبارات
        $quizzes = \App\Models\Quiz::where('subject_id', $subject->id)
            ->with('subject')
            ->latest()
            ->paginate(10);
        
        // إحصائيات
        $stats = [
            'total_sections' => $sections->count(),
            'total_units' => $sections->sum(function($section) {
                return $section->units->count();
            }),
            'total_lessons' => $sections->sum(function($section) {
                return $section->units->sum(function($unit) {
                    return $unit->lessons->count();
                });
            }),
            'total_students' => \App\Models\Enrollment::where('subject_id', $subject->id)
                ->where('status', 'active')
                ->count(),
            'total_quizzes' => $quizzes->total(),
        ];

        return view('supervisor.my-subjects.show', compact('subject', 'sections', 'enrolledStudents', 'quizzes', 'stats'));
    }
}
