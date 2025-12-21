<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StudentProgressService;
use App\Models\User;
use App\Models\Subject;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStudentProgressController extends Controller
{
    protected $progressService;

    public function __construct(StudentProgressService $progressService)
    {
        $this->progressService = $progressService;
    }

    /**
     * قائمة جميع الطلاب مع إحصائيات عامة
     */
    public function index(Request $request)
    {
        $query = User::students();

        // البحث
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // فلترة حسب الكورس
        if ($request->filled('subject_id')) {
            $subjectId = $request->input('subject_id');
            $query->whereHas('subjects', function($q) use ($subjectId) {
                $q->where('subjects.id', $subjectId)
                  ->where('enrollments.status', 'active');
            });
        }

        // فلترة حسب الصف
        if ($request->filled('class_id')) {
            $classId = $request->input('class_id');
            $query->whereHas('subjects', function($q) use ($classId) {
                $q->where('subjects.class_id', $classId)
                  ->where('enrollments.status', 'active');
            });
        }

        $students = $query->with(['subjects' => function($query) {
                $query->wherePivot('status', 'active');
            }])
            ->paginate(20);

        // حساب الإحصائيات لكل طالب
        $studentsStats = [];
        foreach ($students as $student) {
            $allProgress = $this->progressService->getAllStudentProgress($student->id);
            $totalSubjects = count($allProgress);
            $avgProgress = $totalSubjects > 0 
                ? collect($allProgress)->avg('progress.overall_percentage') 
                : 0;

            $studentsStats[$student->id] = [
                'total_subjects' => $totalSubjects,
                'avg_progress' => round($avgProgress, 2),
            ];
        }

        $subjects = Subject::active()->ordered()->get();
        $classes = SchoolClass::with('stage')->orderBy('name')->get();

        return view('admin.pages.student-progress.index', compact('students', 'studentsStats', 'subjects', 'classes'));
    }

    /**
     * عرض تفاصيل طالب معين مع جميع الكورسات
     */
    public function showStudent($userId)
    {
        $student = User::students()->findOrFail($userId);
        
        $progressList = $this->progressService->getAllStudentProgress($student->id);

        // إحصائيات عامة
        $totalLessons = 0;
        $totalQuizzes = 0;
        $totalQuestions = 0;
        $completedLessons = 0;
        $completedQuizzes = 0;
        $completedQuestions = 0;

        foreach ($progressList as $item) {
            $progress = $item['progress'];
            $totalLessons += $progress['lessons_total'];
            $totalQuizzes += $progress['quizzes_total'];
            $totalQuestions += $progress['questions_total'];
            $completedLessons += $progress['lessons_completed'];
            $completedQuizzes += $progress['quizzes_completed'];
            $completedQuestions += $progress['questions_completed'];
        }

        $overallStats = [
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'total_quizzes' => $totalQuizzes,
            'completed_quizzes' => $completedQuizzes,
            'total_questions' => $totalQuestions,
            'completed_questions' => $completedQuestions,
        ];

        return view('admin.pages.student-progress.show', compact('student', 'progressList', 'overallStats'));
    }

    /**
     * عرض تفاصيل كورس معين لطالب محدد
     */
    public function showStudentSubject($userId, $subjectId)
    {
        $student = User::students()->findOrFail($userId);
        $subject = Subject::findOrFail($subjectId);

        // التحقق من أن الطالب مسجل في المادة
        $isEnrolled = $subject->students()
            ->where('users.id', $userId)
            ->where('enrollments.status', 'active')
            ->exists();

        if (!$isEnrolled) {
            return redirect()->route('admin.student-progress.show', $userId)
                ->with('error', 'الطالب غير مسجل في هذه المادة');
        }

        $stats = $this->progressService->getStudentSubjectStats($userId, $subjectId);

        return view('admin.pages.student-progress.subject', compact('student', 'stats', 'subject'));
    }
}

