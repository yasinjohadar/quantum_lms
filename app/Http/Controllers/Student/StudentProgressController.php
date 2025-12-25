<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\StudentProgressService;
use App\Models\Subject;
use App\Models\SubjectSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentProgressController extends Controller
{
    protected $progressService;

    public function __construct(StudentProgressService $progressService)
    {
        $this->progressService = $progressService;
        $this->middleware('auth');
    }

    /**
     * عرض صفحة التقدم الرئيسية
     */
    public function index()
    {
        $user = Auth::user();
        
        // جلب جميع المواد المسجل بها الطالب
        $subjects = $user->subjects()
            ->where('enrollments.status', 'active')
            ->with(['schoolClass.stage'])
            ->get();
        
        // حساب التقدم لكل مادة
        $progressList = $this->progressService->getAllStudentProgress($user->id);
        
        return view('student.pages.progress.index', compact('subjects', 'progressList'));
    }

    /**
     * عرض تقدم طالب في مادة معينة
     */
    public function showSubject(Subject $subject)
    {
        $user = Auth::user();
        
        // التحقق من أن الطالب مسجل في هذه المادة
        $isEnrolled = $user->subjects()
            ->where('subjects.id', $subject->id)
            ->where('enrollments.status', 'active')
            ->exists();
        
        if (!$isEnrolled) {
            abort(403, 'ليس لديك صلاحية للوصول إلى هذه المادة');
        }
        
        // حساب التقدم في المادة
        $progress = $this->progressService->calculateSubjectProgress($user->id, $subject->id);
        
        // جلب الأقسام
        $sections = $subject->sections()
            ->with(['units.lessons', 'units.quizzes', 'units.questions'])
            ->orderBy('order')
            ->get();
        
        // حساب التقدم لكل قسم
        $sectionsProgress = [];
        foreach ($sections as $section) {
            $sectionProgress = $this->progressService->calculateSectionProgress($user->id, $section->id);
            $sectionsProgress[$section->id] = $sectionProgress;
        }
        
        return view('student.pages.progress.subject', compact('subject', 'progress', 'sections', 'sectionsProgress'));
    }

    /**
     * عرض تقدم طالب في قسم معين
     */
    public function showSection(SubjectSection $section)
    {
        $user = Auth::user();
        
        // التحقق من أن الطالب مسجل في مادة هذا القسم
        $subject = $section->subject;
        $isEnrolled = $user->subjects()
            ->where('subjects.id', $subject->id)
            ->where('enrollments.status', 'active')
            ->exists();
        
        if (!$isEnrolled) {
            abort(403, 'ليس لديك صلاحية للوصول إلى هذا القسم');
        }
        
        // جلب تفاصيل القسم مع التقدم
        $sectionDetails = $this->progressService->getSectionDetails($user->id, $section->id);
        $progress = $this->progressService->calculateSectionProgress($user->id, $section->id);
        
        return view('student.pages.progress.section', compact('section', 'progress', 'sectionDetails'));
    }
}

