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
    }

    /**
     * عرض قائمة جميع الكورسات مع نسب التقدم
     */
    public function index()
    {
        $user = Auth::user();
        $progressList = $this->progressService->getAllStudentProgress($user->id);

        return view('student.pages.progress.index', compact('progressList'));
    }

    /**
     * عرض تفاصيل كورس معين مع الأقسام
     */
    public function showSubject($subjectId)
    {
        $user = Auth::user();
        
        // التحقق من أن الطالب مسجل في هذه المادة
        $subject = Subject::whereHas('students', function($query) use ($user) {
                $query->where('users.id', $user->id)
                      ->where('enrollments.status', 'active');
            })
            ->findOrFail($subjectId);

        $stats = $this->progressService->getStudentSubjectStats($user->id, $subjectId);

        return view('student.pages.progress.subject', compact('stats', 'subject'));
    }

    /**
     * عرض تفاصيل قسم معين مع الدروس والاختبارات والأسئلة
     */
    public function showSection($sectionId)
    {
        $user = Auth::user();
        
        $section = SubjectSection::with('subject')->findOrFail($sectionId);
        
        // التحقق من أن الطالب مسجل في المادة
        $subject = $section->subject;
        $isEnrolled = $subject->students()
            ->where('users.id', $user->id)
            ->where('enrollments.status', 'active')
            ->exists();

        if (!$isEnrolled) {
            abort(403, 'ليس لديك صلاحية للوصول إلى هذا القسم');
        }

        $details = $this->progressService->getSectionDetails($user->id, $sectionId);

        return view('student.pages.progress.section', compact('details', 'section', 'subject'));
    }
}

