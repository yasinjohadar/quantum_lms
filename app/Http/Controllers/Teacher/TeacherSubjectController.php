<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherSubjectController extends Controller
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
     * عرض قائمة المواد المخصصة للمعلم
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // جلب المواد المخصصة للمعلم (من الصفوف + مباشرة)
        $subjectsQuery = $user->getAccessibleSubjects()->with('schoolClass.stage');

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
        
        // جلب الصفوف المخصصة للمعلم للفلترة
        $classes = $user->assignedClasses()->with('stage')->ordered()->get();

        return view('teacher.pages.subjects.index', compact('subjects', 'classes'));
    }

    /**
     * عرض تفاصيل المادة
     */
    public function show(Subject $subject)
    {
        $user = Auth::user();
        
        // التحقق من التخصيص
        if (!$user->isAssignedToSubject($subject->id) && 
            !$user->isAssignedToClass($subject->class_id)) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه المادة');
        }

        // جلب البيانات الكاملة
        $subject->load([
            'schoolClass.stage',
            'sections' => function ($q) {
                $q->orderBy('order')->orderBy('title');
            },
            'sections.units' => function ($q) {
                $q->orderBy('order')->orderBy('title');
            },
            'sections.units.lessons' => function ($q) {
                $q->orderBy('order');
            },
        ]);

        return view('teacher.pages.subjects.show', compact('subject'));
    }
}
