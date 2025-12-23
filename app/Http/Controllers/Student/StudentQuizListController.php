<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentQuizListController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'check.user.active']);
    }

    /**
     * عرض الاختبارات المتاحة للطالب
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // جلب المواد المسجلة
        $subjects = $user->subjects()->wherePivot('status', 'active')->get();
        $subjectIds = $subjects->pluck('id')->toArray();

        // جلب الاختبارات المتاحة
        $query = Quiz::with(['subject', 'unit'])
            ->where('is_active', true)
            ->where('is_published', true)
            ->whereIn('subject_id', $subjectIds)
            ->available();

        // فلترة حسب المادة
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // فلترة حسب الحالة (متاح، منتهي، قادم)
        if ($request->filled('status')) {
            $now = now();
            switch ($request->status) {
                case 'available':
                    $query->where(function($q) use ($now) {
                        $q->whereNull('available_from')->orWhere('available_from', '<=', $now);
                    })->where(function($q) use ($now) {
                        $q->whereNull('available_to')->orWhere('available_to', '>=', $now);
                    });
                    break;
                case 'upcoming':
                    $query->where('available_from', '>', $now);
                    break;
                case 'expired':
                    $query->where('available_to', '<', $now);
                    break;
            }
        }

        $quizzes = $query->orderBy('available_from', 'desc')
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        // إضافة معلومات عن محاولات الطالب
        foreach ($quizzes as $quiz) {
            $attempts = QuizAttempt::where('user_id', $user->id)
                ->where('quiz_id', $quiz->id)
                ->get();
            
            $quiz->user_attempts = $attempts;
            $quiz->can_attempt = $quiz->canUserAttempt($user)['can'];
            $quiz->last_attempt = $attempts->sortByDesc('started_at')->first();
        }

        return view('student.pages.quizzes.index', compact('quizzes', 'subjects'));
    }

    /**
     * عرض نتائج الاختبارات للطالب
     */
    public function results(Request $request)
    {
        $user = Auth::user();

        $query = QuizAttempt::with(['quiz.subject', 'quiz.unit'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['completed', 'graded', 'timeout']);

        // فلترة حسب المادة
        if ($request->filled('subject_id')) {
            $query->whereHas('quiz', function($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            });
        }

        // فلترة حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // فلترة حسب النجاح/الرسوب
        if ($request->filled('passed')) {
            $query->where('passed', $request->passed === '1');
        }

        $attempts = $query->latest('finished_at')
            ->paginate(15)
            ->withQueryString();

        // جلب المواد المسجلة للفلترة
        $subjects = $user->subjects()->wherePivot('status', 'active')->get();

        return view('student.pages.quizzes.results', compact('attempts', 'subjects'));
    }
}
