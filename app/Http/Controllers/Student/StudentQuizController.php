<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Models\Question;
use App\Services\GamificationService;
use App\Services\AuditLogService;
use App\Services\AnalyticsService;
use App\Events\QuizStarted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class StudentQuizController extends Controller
{
    protected AuditLogService $auditLogService;
    protected AnalyticsService $analyticsService;

    public function __construct(AuditLogService $auditLogService, AnalyticsService $analyticsService)
    {
        $this->middleware(['auth', 'check.user.active']);
        $this->auditLogService = $auditLogService;
        $this->analyticsService = $analyticsService;
    }

    /**
     * بدء اختبار
     */
    public function startQuiz(Quiz $quiz)
    {
        $user = Auth::user();

        $quiz->load(['questions' => function ($query) {
            $query->orderBy('quiz_questions.order');
        }]);

        if (!$quiz->is_active || !$quiz->is_published) {
            return redirect()->back()->with('error', 'الاختبار غير متاح حالياً');
        }

        // التحقق من إمكانية بدء الاختبار
        $canAttempt = $quiz->canUserAttempt($user);
        
        if (!$canAttempt['can']) {
            return redirect()->back()
                ->with('error', $canAttempt['reason']);
        }

        // التحقق من وجود محاولة جارية
        $inProgressAttempt = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->where('status', 'in_progress')
            ->first();

        if ($inProgressAttempt) {
            return redirect()->route('student.quizzes.show', [
                'quiz' => $quiz,
                'attempt' => $inProgressAttempt->id
            ]);
        }

        try {
            DB::beginTransaction();

            // الحصول على آخر رقم محاولة
            $lastAttempt = QuizAttempt::where('user_id', $user->id)
                ->where('quiz_id', $quiz->id)
                ->orderBy('attempt_number', 'desc')
                ->first();

            $attemptNumber = $lastAttempt ? $lastAttempt->attempt_number + 1 : 1;

            // إنشاء محاولة جديدة
            $attempt = QuizAttempt::create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'attempt_number' => $attemptNumber,
                'started_at' => now(),
                'status' => 'in_progress',
                'max_score' => $quiz->total_points,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // حفظ ترتيب الأسئلة (مع خلط إذا كان مطلوباً)
            $questionIds = $quiz->questions->pluck('id')->toArray();
            if ($quiz->shuffle_questions) {
                shuffle($questionIds);
            }
            $attempt->question_order = $questionIds;
            $attempt->save();

            // إرسال Event (غير حرج - لا يمنع بدء الاختبار عند الفشل)
            try {
                Event::dispatch(new QuizStarted($user, $quiz, [
                    'attempt_id' => $attempt->id,
                    'time_limit' => $quiz->time_limit,
                ]));
            } catch (\Throwable $e) {
                Log::warning('QuizStarted event broadcast failed (non-fatal)', [
                    'quiz_id' => $quiz->id,
                    'attempt_id' => $attempt->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // تسجيل حدث في Analytics
            $this->analyticsService->trackEvent('start_quiz', $user->id, [
                'quiz_id' => $quiz->id,
                'subject_id' => $quiz->subject_id,
                'attempt_id' => $attempt->id,
            ]);

            DB::commit();

            return redirect()->route('student.quizzes.show', [
                'quiz' => $quiz,
                'attempt' => $attempt->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error starting quiz: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء بدء الاختبار: ' . $e->getMessage());
        }
    }

    /**
     * عرض صفحة الاختبار
     */
    public function showQuiz($quizId, $attemptId)
    {
        $user = Auth::user();
        $quiz = Quiz::with(['questions.options' => function($query) {
            $query->orderBy('order');
        }])->where('is_active', true)
        ->findOrFail($quizId);

        $attempt = QuizAttempt::with('quiz')
            ->where('user_id', $user->id)
            ->where('quiz_id', $quizId)
            ->findOrFail($attemptId);

        if ($attempt->status !== 'in_progress') {
            return redirect()->route('student.quizzes.result', [
                'quiz' => $quizId,
                'attempt' => $attemptId,
            ])->with('info', 'هذه المحاولة مكتملة.');
        }

        // الحصول على الأسئلة بالترتيب المحفوظ
        $questionIds = $attempt->question_order ?? $quiz->questions->pluck('id')->toArray();
        $questions = Question::whereIn('id', $questionIds)
            ->with(['options' => function($query) {
                $query->orderBy('order');
            }])
            ->get()
            ->sortBy(function($question) use ($questionIds) {
                return array_search($question->id, $questionIds);
            })
            ->values();

        // الحصول على الإجابات الحالية
        $answers = $attempt->answers()->with('question')->get()->keyBy('question_id');

        // تمرير ثوابت الأنواع
        $questionTypes = Question::TYPES;
        $questionTypeIcons = Question::TYPE_ICONS;
        $questionTypeColors = Question::TYPE_COLORS;
        $questionDifficulties = Question::DIFFICULTIES;

        return view('student.pages.quizzes.show', compact(
            'quiz',
            'attempt',
            'questions',
            'answers',
            'questionTypes',
            'questionTypeIcons',
            'questionTypeColors',
            'questionDifficulties'
        ));
    }

    /**
     * حفظ إجابة (AJAX)
     */
    public function saveAnswer(Request $request, $attemptId)
    {
        $user = Auth::user();
        $attempt = QuizAttempt::where('user_id', $user->id)
            ->findOrFail($attemptId);

        if ($attempt->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تعديل محاولة مكتملة'
            ], 400);
        }

        $request->validate([
            'question_id' => 'required|exists:questions,id',
        ]);

        try {
            $question = Question::findOrFail($request->question_id);
            $answerData = $this->prepareAnswerData($request, $question);

            $existingAnswer = QuizAnswer::where('attempt_id', $attemptId)
                ->where('question_id', $request->question_id)
                ->first();

            if ($this->isAnswerDataEmpty($answerData, $question)
                && $existingAnswer
                && $this->answerHasContent($existingAnswer)) {
                $attempt->updateActivity();

                return response()->json([
                    'success' => true,
                    'message' => 'تم حفظ الإجابة بنجاح',
                    'answer' => [
                        'selected_options' => $existingAnswer->selected_options,
                        'answer_text' => $existingAnswer->answer_text,
                        'numeric_answer' => $existingAnswer->numeric_answer,
                        'matching_pairs' => $existingAnswer->matching_pairs,
                        'ordering' => $existingAnswer->ordering,
                        'fill_blanks_answers' => $existingAnswer->fill_blanks_answers,
                        'drag_drop_assignments' => $existingAnswer->drag_drop_assignments,
                    ],
                ]);
            }

            $answer = QuizAnswer::updateOrCreate(
                [
                    'attempt_id' => $attemptId,
                    'question_id' => $request->question_id,
                ],
                array_merge($answerData, [
                    'answered_at' => now(),
                    'time_spent' => $attempt->started_at->diffInSeconds(now()),
                    'max_points' => $question->default_points ?? 0,
                ])
            );

            $attempt->updateActivity();

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ الإجابة بنجاح',
                'answer' => [
                    'selected_options' => $answer->selected_options,
                    'answer_text' => $answer->answer_text,
                    'numeric_answer' => $answer->numeric_answer,
                    'matching_pairs' => $answer->matching_pairs,
                    'ordering' => $answer->ordering,
                    'fill_blanks_answers' => $answer->fill_blanks_answers,
                    'drag_drop_assignments' => $answer->drag_drop_assignments,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving quiz answer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ الإجابة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إرسال الاختبار
     */
    public function submitQuiz(Request $request, $attemptId)
    {
        $user = Auth::user();
        $attempt = QuizAttempt::with('quiz')->where('user_id', $user->id)
            ->findOrFail($attemptId);

        if ($attempt->status !== 'in_progress') {
            $resultUrl = $this->quizResultUrl($attempt);

            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => true,
                    'redirect_url' => $resultUrl,
                    'message' => 'تم إرسال الاختبار مسبقاً',
                ]);
            }

            return redirect()->to($resultUrl)
                ->with('info', 'تم إرسال هذه المحاولة مسبقاً');
        }

        try {
            DB::beginTransaction();

            // حفظ آخر إجابة إذا كانت موجودة
            if ($request->has('question_id')) {
                $question = Question::findOrFail($request->question_id);
                $answerData = $this->prepareAnswerData($request, $question);

                QuizAnswer::updateOrCreate(
                    [
                        'attempt_id' => $attemptId,
                        'question_id' => $request->question_id,
                    ],
                    array_merge($answerData, [
                        'answered_at' => now(),
                        'max_points' => $question->default_points ?? 0,
                    ])
                );
            }

            // تصحيح جميع الإجابات غير المصححة
            $answers = $attempt->answers()->with('question')->get();
            foreach ($answers as $answer) {
                if (!$answer->is_graded && !$answer->needs_manual_grading) {
                    $answer->autoGrade();
                }
            }

            // إنهاء المحاولة
            $attempt->finish();

            DB::commit();

            // ربط مع نظام التحفيز (غير حرج — لا يمنع إرسال الاختبار)
            try {
                $gamificationService = app(GamificationService::class);
                $gamificationService->processQuizCompletion($attempt->fresh(['user', 'quiz']));
            } catch (\Throwable $e) {
                Log::warning('Gamification after quiz submit failed (non-fatal)', [
                    'attempt_id' => $attempt->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // تسجيل حدث في Analytics (غير حرج)
            try {
                $this->analyticsService->trackEvent('complete_quiz', $user->id, [
                    'quiz_id' => $attempt->quiz_id,
                    'subject_id' => $attempt->quiz->subject_id ?? null,
                    'attempt_id' => $attempt->id,
                    'score' => $attempt->score,
                    'percentage' => $attempt->percentage,
                    'passed' => $attempt->passed,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Analytics after quiz submit failed (non-fatal)', [
                    'attempt_id' => $attempt->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $resultUrl = $this->quizResultUrl($attempt);

            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => true,
                    'redirect_url' => $resultUrl,
                    'message' => 'تم إرسال الاختبار بنجاح',
                ]);
            }

            return redirect()->to($resultUrl)
                ->with('success', 'تم إرسال الاختبار بنجاح');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error submitting quiz: ' . $e->getMessage(), [
                'attempt_id' => $attemptId,
                'exception' => $e,
            ]);

            $userMessage = 'حدث خطأ أثناء إرسال الاختبار. يرجى المحاولة مرة أخرى أو التواصل مع الدعم.';

            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => false,
                    'message' => $userMessage,
                ], 500);
            }

            return redirect()->route('student.quizzes.show', [
                'quiz' => $attempt->quiz_id,
                'attempt' => $attemptId,
            ])->with('error', $userMessage);
        }
    }

    /**
     * عرض نتيجة الاختبار
     */
    public function showResult($quizId, $attemptId)
    {
        $user = Auth::user();
        $quiz = Quiz::findOrFail($quizId);
        
        $attempt = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quizId)
            ->findOrFail($attemptId);

        $answers = $attempt->answers()->with('question.options')->get();

        return view('student.pages.quizzes.result', compact('quiz', 'attempt', 'answers'));
    }

    /**
     * API للحصول على الوقت المتبقي
     */
    public function getRemainingTime($attemptId)
    {
        $user = Auth::user();
        $attempt = QuizAttempt::with('quiz')
            ->where('user_id', $user->id)
            ->findOrFail($attemptId);

        if ($attempt->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'timeout' => true,
                'redirect_url' => $this->quizResultUrl($attempt),
                'message' => 'المحاولة مكتملة',
            ]);
        }

        if (! $attempt->quiz->hasTimeLimit()) {
            return response()->json([
                'success' => true,
                'unlimited' => true,
                'elapsed' => $attempt->elapsed_seconds,
                'formatted_elapsed' => $attempt->formatted_elapsed_time,
            ]);
        }

        $remaining = $attempt->remaining_time;

        // التحقق من انتهاء الوقت
        if ($remaining !== null && $remaining <= 0) {
            $this->finalizeTimedOutAttempt($attempt);

            $this->auditLogService->logQuizSecurity($user, 'quiz_timeout', [
                'quiz_id' => $attempt->quiz_id,
                'attempt_id' => $attempt->id,
            ]);

            return response()->json([
                'success' => false,
                'timeout' => true,
                'redirect_url' => $this->quizResultUrl($attempt),
                'message' => 'انتهى الوقت',
            ]);
        }

        return response()->json([
            'success' => true,
            'unlimited' => false,
            'remaining' => $remaining,
            'formatted' => $attempt->formatted_remaining_time,
        ]);
    }

    /**
     * تحضير بيانات الإجابة حسب نوع السؤال
     */
    private function prepareAnswerData(Request $request, Question $question): array
    {
        $data = [];

        switch ($question->type) {
            case 'single_choice':
                // Support both option_id (legacy) and selected_options (new)
                $selectedOptions = $request->input('selected_options', []);
                if (empty($selectedOptions)) {
                    $optionId = $request->input('option_id');
                    $selectedOptions = $optionId ? [$optionId] : [];
                }
                if (!is_array($selectedOptions)) {
                    $selectedOptions = $selectedOptions ? [$selectedOptions] : [];
                }
                // Filter out null/empty values
                $selectedOptions = array_filter($selectedOptions, function($v) {
                    return $v !== null && $v !== '';
                });
                $data['selected_options'] = array_values($selectedOptions);
                break;

            case 'multiple_choice':
                $selectedOptions = $request->input('selected_options', []);
                if (!is_array($selectedOptions)) {
                    $selectedOptions = $selectedOptions ? [$selectedOptions] : [];
                }
                // Filter out null/empty values
                $selectedOptions = array_filter($selectedOptions, function($v) {
                    return $v !== null && $v !== '';
                });
                $data['selected_options'] = array_values($selectedOptions);
                break;

            case 'true_false':
                // Support both option_id (legacy) and selected_options (new)
                $selectedOptions = $request->input('selected_options', []);
                if (empty($selectedOptions)) {
                    $optionId = $request->input('option_id');
                    $selectedOptions = $optionId ? [$optionId] : [];
                }
                if (!is_array($selectedOptions)) {
                    $selectedOptions = $selectedOptions ? [$selectedOptions] : [];
                }
                // Filter out null/empty values
                $selectedOptions = array_filter($selectedOptions, function($v) {
                    return $v !== null && $v !== '';
                });
                $data['selected_options'] = array_values($selectedOptions);
                break;

            case 'short_answer':
            case 'essay':
                $data['answer_text'] = $request->input('answer_text');
                break;

            case 'matching':
                $data['matching_pairs'] = $request->input('matching_pairs', []);
                break;

            case 'ordering':
                $ordering = $request->input('ordering');
                if (is_string($ordering)) {
                    $ordering = explode(',', $ordering);
                    $ordering = array_filter(array_map('trim', $ordering));
                }
                $data['ordering'] = $ordering ?? [];
                break;

            case 'numerical':
                $data['numeric_answer'] = $request->input('numeric_answer');
                break;

            case 'fill_blanks':
                $fillBlanksAnswers = $request->input('fill_blanks_answers', []);
                // Ensure array format and preserve keys
                if (!is_array($fillBlanksAnswers)) {
                    $fillBlanksAnswers = [];
                }
                // Convert keys to integers for proper indexing
                $result = [];
                foreach ($fillBlanksAnswers as $key => $value) {
                    $result[(int)$key] = $value;
                }
                $data['fill_blanks_answers'] = $result;
                break;

            case 'drag_drop':
                $dragDropAssignments = $request->input('drag_drop_assignments');
                if (is_string($dragDropAssignments)) {
                    $dragDropAssignments = json_decode($dragDropAssignments, true);
                }
                $data['drag_drop_assignments'] = $dragDropAssignments ?? [];
                break;
        }

        // حفظ ترتيب الخيارات إذا كان موجوداً
        if ($request->has('options_order')) {
            $data['options_order'] = $request->input('options_order');
        }

        return $data;
    }

    private function wantsJsonResponse(Request $request): bool
    {
        return $request->expectsJson()
            || $request->ajax()
            || $request->wantsJson();
    }

    private function quizResultUrl(QuizAttempt $attempt): string
    {
        return route('student.quizzes.result', [
            'quiz' => $attempt->quiz_id,
            'attempt' => $attempt->id,
        ]);
    }

    private function finalizeTimedOutAttempt(QuizAttempt $attempt): void
    {
        if ($attempt->status !== 'in_progress') {
            return;
        }

        $attempt->timeout();

        foreach ($attempt->answers()->with('question')->get() as $answer) {
            if (!$answer->is_graded && !$answer->needs_manual_grading) {
                $answer->autoGrade();
            }
        }
    }

    private function isAnswerDataEmpty(array $data, Question $question): bool
    {
        if (empty($data)) {
            return true;
        }

        return match ($question->type) {
            'single_choice', 'multiple_choice', 'true_false' => empty($data['selected_options'] ?? []),
            'short_answer', 'essay' => !isset($data['answer_text']) || trim((string) $data['answer_text']) === '',
            'matching' => empty($data['matching_pairs'] ?? []),
            'ordering' => empty($data['ordering'] ?? []),
            'numerical', 'numeric' => !isset($data['numeric_answer']) || $data['numeric_answer'] === '' || $data['numeric_answer'] === null,
            'fill_blanks', 'fill_blank' => empty($data['fill_blanks_answers'] ?? []),
            'drag_drop' => empty($data['drag_drop_assignments'] ?? []),
            default => empty(array_filter($data, fn ($value) => $value !== null && $value !== '' && $value !== [])),
        };
    }

    private function answerHasContent(QuizAnswer $answer): bool
    {
        if (!empty($answer->selected_options)) {
            return true;
        }

        if ($answer->answer_text !== null && trim((string) $answer->answer_text) !== '') {
            return true;
        }

        if ($answer->numeric_answer !== null && $answer->numeric_answer !== '') {
            return true;
        }

        if (!empty($answer->matching_pairs)) {
            return true;
        }

        if (!empty($answer->ordering)) {
            return true;
        }

        if (!empty($answer->fill_blanks_answers)) {
            return true;
        }

        if (!empty($answer->drag_drop_assignments)) {
            return true;
        }

        return false;
    }
}
