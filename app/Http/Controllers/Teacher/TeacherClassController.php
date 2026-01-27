<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherClassController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'check.user.active']);
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->hasRole('teacher')) {
                abort(403, 'غير مصرح لك بالوصول');
            }
            return $next($request);
        });
    }

    /**
     * عرض قائمة الصفوف المخصصة للمعلم
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // جلب الصفوف المخصصة للمعلم فقط
        $classesQuery = $user->assignedClasses()->with('stage');

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

        return view('teacher.pages.classes.index', compact('classes', 'stages'));
    }

    /**
     * عرض تفاصيل الصف
     */
    public function show(SchoolClass $class)
    {
        $user = Auth::user();
        
        // التحقق من التخصيص
        if (!$user->isAssignedToClass($class->id)) {
            abort(403, 'غير مصرح لك بالوصول إلى هذا الصف');
        }

        // جلب المواد التابعة للصف
        $subjects = $class->subjects()->with('schoolClass.stage')->ordered()->get();
        
        // إحصائيات
        $stats = [
            'total_subjects' => $subjects->count(),
            'total_students' => \App\Models\ClassEnrollment::where('class_id', $class->id)
                ->where('status', 'active')
                ->count(),
        ];

        return view('teacher.pages.classes.show', compact('class', 'subjects', 'stats'));
    }
}
