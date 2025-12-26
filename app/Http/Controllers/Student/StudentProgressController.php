<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Services\StudentProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentProgressController extends Controller
{
    protected $progressService;

    public function __construct(StudentProgressService $progressService)
    {
        $this->middleware('auth');
        $this->progressService = $progressService;
    }

    /**
     * عرض صفحة التقدم الرئيسية
     */
    public function index()
    {
        $user = Auth::user();
        $progressList = $this->progressService->getAllStudentProgress($user->id);
        
        return view('student.pages.progress.index', [
            'progressList' => $progressList,
        ]);
    }

    /**
     * عرض تقدم في مادة معينة
     */
    public function showSubject(Subject $subject)
    {
        $user = Auth::user();

        // التحقق من التسجيل في المادة
        if (!$subject->students()->where('users.id', $user->id)->exists()) {
            abort(403, 'يجب أن تكون مسجل في هذه المادة لعرض التقدم.');
        }

        $progress = $this->progressService->calculateSubjectProgress($user->id, $subject->id);
        $stats = $this->progressService->getStudentSubjectStats($user->id, $subject->id);
        
        // جلب الأقسام مع التقدم
        $sections = $subject->sections()->where('is_active', true)->get();
        $sectionsProgress = [];
        foreach ($sections as $section) {
            $sectionsProgress[$section->id] = $this->progressService->calculateSectionProgress($user->id, $section->id);
        }
        
        return view('student.pages.progress.subject', [
            'subject' => $subject,
            'progress' => $progress,
            'sections' => $sections,
            'sectionsProgress' => $sectionsProgress,
        ]);
    }

    /**
     * عرض تقدم في قسم معين
     */
    public function showSection(SubjectSection $section)
    {
        $user = Auth::user();

        // التحقق من التسجيل في المادة
        $subject = $section->subject;
        if (!$subject->students()->where('users.id', $user->id)->exists()) {
            abort(403, 'يجب أن تكون مسجل في هذه المادة لعرض التقدم.');
        }

        $progress = $this->progressService->calculateSectionProgress($user->id, $section->id);
        
        return view('student.pages.progress.section', [
            'section' => $section,
            'subject' => $subject,
            'progress' => $progress,
        ]);
    }
}

