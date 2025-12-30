<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Services\AI\AIEssayGradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuizAttemptController extends Controller
{
    public function __construct(
        private AIEssayGradingService $gradingService
    ) {}
    /**
     * عرض محاولات اختبار معين
     */
    public function index(Request $request, string $quizId)
    {
        $quiz = Quiz::with('subject')->findOrFail($quizId);
        
        $query = QuizAttempt::with(['user'])
            ->where('quiz_id', $quizId);

        // تصفية حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // تصفية حسب النجاح/الرسوب
        if ($request->filled('passed')) {
            $query->where('passed', $request->passed === '1');
        }

        // البحث حسب اسم الطالب
        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $attempts = $query->latest('started_at')->paginate(20)->withQueryString();

        return view('admin.pages.quiz-attempts.index', compact('quiz', 'attempts'));
    }

    /**
     * عرض تفاصيل محاولة
     */
    public function show(string $id)
    {
        $attempt = QuizAttempt::with([
            'quiz.subject',
            'user',
            'answers.question.options',
            'grader',
        ])->findOrFail($id);

        return view('admin.pages.quiz-attempts.show', compact('attempt'));
    }

    /**
     * صفحة تصحيح المحاولة
     */
    public function grade(string $id)
    {
        $attempt = QuizAttempt::with([
            'quiz',
            'user',
            'answers' => function ($q) {
                $q->where('needs_manual_grading', true)->where('is_graded', false);
            },
            'answers.question.options',
            'answers.aiGradingModel',
        ])->findOrFail($id);

        if ($attempt->answers->isEmpty()) {
            return redirect()
                ->route('admin.quiz-attempts.show', $id)
                ->with('info', 'لا توجد أسئلة تحتاج تصحيح يدوي');
        }

        return view('admin.pages.quiz-attempts.grade', compact('attempt'));
    }

    /**
     * حفظ التصحيح اليدوي
     */
    public function saveGrade(Request $request, string $id)
    {
        try {
            $attempt = QuizAttempt::findOrFail($id);
            
            $request->validate([
                'grades' => ['required', 'array'],
                'grades.*.answer_id' => ['required', 'exists:quiz_answers,id'],
                'grades.*.points' => ['required', 'numeric', 'min:0'],
                'grades.*.feedback' => ['nullable', 'string'],
            ]);

            foreach ($request->grades as $gradeData) {
                $answer = QuizAnswer::find($gradeData['answer_id']);
                if ($answer && $answer->attempt_id == $attempt->id) {
                    $answer->manualGrade(
                        $gradeData['points'],
                        $gradeData['feedback'] ?? null,
                        auth()->id()
                    );
                }
            }

            // التحقق من اكتمال التصحيح
            $remainingToGrade = $attempt->answers()
                ->where('needs_manual_grading', true)
                ->where('is_graded', false)
                ->count();

            if ($remainingToGrade === 0 && $attempt->status === 'under_review') {
                $attempt->status = 'completed';
                $attempt->graded_by = auth()->id();
                $attempt->graded_at = now();
                $attempt->save();
            }

            return redirect()
                ->route('admin.quiz-attempts.show', $id)
                ->with('success', 'تم حفظ التصحيح بنجاح');

        } catch (\Exception $e) {
            Log::error('Error saving grade: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء حفظ التصحيح');
        }
    }

    /**
     * إعادة تصحيح محاولة
     */
    public function regrade(string $id)
    {
        try {
            $attempt = QuizAttempt::with('answers')->findOrFail($id);
            
            foreach ($attempt->answers as $answer) {
                if (!$answer->needs_manual_grading) {
                    $answer->autoGrade();
                }
            }

            $attempt->calculateScore();

            return redirect()
                ->back()
                ->with('success', 'تم إعادة التصحيح التلقائي بنجاح');

        } catch (\Exception $e) {
            Log::error('Error regrading attempt: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء إعادة التصحيح');
        }
    }

    /**
     * حذف محاولة
     */
    public function destroy(string $id)
    {
        try {
            $attempt = QuizAttempt::findOrFail($id);
            $quizId = $attempt->quiz_id;
            
            $attempt->delete();

            return redirect()
                ->route('admin.quizzes.results', $quizId)
                ->with('success', 'تم حذف المحاولة بنجاح');

        } catch (\Exception $e) {
            Log::error('Error deleting attempt: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء حذف المحاولة');
        }
    }

    /**
     * إعادة تعيين محاولات طالب
     */
    public function resetUserAttempts(Request $request, string $quizId)
    {
        try {
            $request->validate([
                'user_id' => ['required', 'exists:users,id'],
            ]);

            QuizAttempt::where('quiz_id', $quizId)
                ->where('user_id', $request->user_id)
                ->delete();

            return redirect()
                ->back()
                ->with('success', 'تم إعادة تعيين محاولات الطالب بنجاح');

        } catch (\Exception $e) {
            Log::error('Error resetting user attempts: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء إعادة التعيين');
        }
    }

    /**
     * المحاولات التي تحتاج تصحيح
     */
    public function needsGrading(Request $request)
    {
        $attempts = QuizAttempt::with(['quiz.subject', 'user'])
            ->where('status', 'under_review')
            ->latest('finished_at')
            ->paginate(20);

        return view('admin.pages.quiz-attempts.needs-grading', compact('attempts'));
    }

    /**
     * إحصائيات المحاولات
     */
    public function statistics(string $quizId)
    {
        $quiz = Quiz::with('subject')->findOrFail($quizId);
        
        $stats = [
            'total_attempts' => $quiz->attempts()->count(),
            'completed_attempts' => $quiz->attempts()->completed()->count(),
            'in_progress' => $quiz->attempts()->inProgress()->count(),
            'passed' => $quiz->attempts()->passed()->count(),
            'failed' => $quiz->attempts()->failed()->count(),
            'average_score' => round($quiz->attempts()->completed()->avg('percentage') ?? 0, 2),
            'highest_score' => $quiz->attempts()->completed()->max('percentage') ?? 0,
            'lowest_score' => $quiz->attempts()->completed()->min('percentage') ?? 0,
            'average_time' => round($quiz->attempts()->completed()->avg('time_spent') ?? 0),
        ];

        // توزيع الدرجات
        $scoreDistribution = [
            '0-20' => $quiz->attempts()->completed()->whereBetween('percentage', [0, 20])->count(),
            '21-40' => $quiz->attempts()->completed()->whereBetween('percentage', [21, 40])->count(),
            '41-60' => $quiz->attempts()->completed()->whereBetween('percentage', [41, 60])->count(),
            '61-80' => $quiz->attempts()->completed()->whereBetween('percentage', [61, 80])->count(),
            '81-100' => $quiz->attempts()->completed()->whereBetween('percentage', [81, 100])->count(),
        ];

        // أصعب الأسئلة (أقل نسبة إجابة صحيحة)
        $hardestQuestions = $quiz->questions()
            ->withCount([
                'answers as total_answers' => function ($q) use ($quiz) {
                    $q->whereHas('attempt', function ($qa) use ($quiz) {
                        $qa->where('quiz_id', $quiz->id);
                    });
                },
                'answers as correct_answers' => function ($q) use ($quiz) {
                    $q->where('is_correct', true)
                      ->whereHas('attempt', function ($qa) use ($quiz) {
                          $qa->where('quiz_id', $quiz->id);
                      });
                },
            ])
            ->get()
            ->map(function ($question) {
                $question->correct_percentage = $question->total_answers > 0 
                    ? round(($question->correct_answers / $question->total_answers) * 100, 2) 
                    : 0;
                return $question;
            })
            ->sortBy('correct_percentage')
            ->take(5);

        return view('admin.pages.quiz-attempts.statistics', compact('quiz', 'stats', 'scoreDistribution', 'hardestQuestions'));
    }

    /**
     * تصحيح إجابة مقالية واحدة باستخدام AI
     */
    public function gradeWithAI(string $attemptId, string $answerId)
    {
        try {
            $attempt = QuizAttempt::findOrFail($attemptId);
            $answer = QuizAnswer::with('question')
                ->where('id', $answerId)
                ->where('attempt_id', $attemptId)
                ->firstOrFail();

            if ($answer->question->type !== 'essay') {
                return redirect()->back()
                    ->with('error', 'هذا السؤال ليس مقالي');
            }

            $this->gradingService->gradeEssay($answer);

            return redirect()->back()
                ->with('success', 'تم تصحيح السؤال باستخدام AI بنجاح');

        } catch (\Exception $e) {
            Log::error('Error grading with AI: ' . $e->getMessage(), [
                'attempt_id' => $attemptId,
                'answer_id' => $answerId,
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء التصحيح: ' . $e->getMessage());
        }
    }

    /**
     * تصحيح جميع الأسئلة المقالية في المحاولة باستخدام AI
     */
    public function gradeMultipleWithAI(string $attemptId, Request $request)
    {
        try {
            $attempt = QuizAttempt::with(['answers.question'])->findOrFail($attemptId);

            $essayAnswers = $attempt->answers->filter(function ($answer) {
                return $answer->question->type === 'essay' && !empty($answer->answer_text);
            });

            if ($essayAnswers->isEmpty()) {
                return redirect()->back()
                    ->with('info', 'لا توجد أسئلة مقالية تحتاج تصحيح');
            }

            $results = $this->gradingService->gradeMultipleEssays($essayAnswers);

            $successCount = $results->where('success', true)->count();
            $failCount = $results->where('success', false)->count();

            $message = "تم تصحيح {$successCount} إجابة بنجاح";
            if ($failCount > 0) {
                $message .= "، فشل تصحيح {$failCount} إجابة";
            }

            return redirect()->back()
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Error grading multiple with AI: ' . $e->getMessage(), [
                'attempt_id' => $attemptId,
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء التصحيح: ' . $e->getMessage());
        }
    }
}

