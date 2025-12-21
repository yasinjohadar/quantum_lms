<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Models\Question;
use App\Services\GamificationService;
use App\Events\QuizStarted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class StudentQuizController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'check.user.active']);
    }

    /**
     * بدء اختبار
     */
    public function startQuiz($quizId)
    {
        $user = Auth::user();
        $quiz = Quiz::with(['questions' => function($query) {
            $query->orderBy('quiz_questions.order');
        }])->where('is_active', true)
        ->where('is_published', true)
        ->findOrFail($quizId);

        // التحقق من إمكانية بدء الاختبار
        $canAttempt = $quiz->canUserAttempt($user);
        if (!$canAttempt['can']) {
            return redirect()->back()
                ->with('error', $canAttempt['reason']);
        }

        // التحقق من وجود محاولة جارية
        $inProgressAttempt = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quizId)
            ->where('status', 'in_progress')
            ->first();

        if ($inProgressAttempt) {
            return redirect()->route('student.quizzes.show', [
                'quiz' => $quizId,
                'attempt' => $inProgressAttempt->id
            ]);
        }

        try {
            DB::beginTransaction();

            // الحصول على آخر رقم محاولة
            $lastAttempt = QuizAttempt::where('user_id', $user->id)
                ->where('quiz_id', $quizId)
                ->orderBy('attempt_number', 'desc')
                ->first();

            $attemptNumber = $lastAttempt ? $lastAttempt->attempt_number + 1 : 1;

            // إنشاء محاولة جديدة
            $attempt = QuizAttempt::create([
                'user_id' => $user->id,
                'quiz_id' => $quizId,
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

            // إرسال Event
            Event::dispatch(new QuizStarted($user, $quiz, [
                'attempt_id' => $attempt->id,
                'time_limit' => $quiz->time_limit,
            ]));

            DB::commit();

            return redirect()->route('student.quizzes.show', [
                'quiz' => $quizId,
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

        $attempt = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quizId)
            ->findOrFail($attemptId);

        if ($attempt->status !== 'in_progress') {
            return redirect()->back()
                ->with('info', 'هذه المحاولة مكتملة. يمكنك بدء محاولة جديدة.');
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
                'answer' => $answer
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
        $attempt = QuizAttempt::where('user_id', $user->id)
            ->findOrFail($attemptId);

        if ($attempt->status !== 'in_progress') {
            return redirect()->back()
                ->with('error', 'لا يمكن إرسال محاولة مكتملة');
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

            // إنهاء المحاولة
            $attempt->finish();

            // ربط مع نظام التحفيز
            $gamificationService = app(GamificationService::class);
            $gamificationService->processQuizCompletion($attempt);

            DB::commit();

            return redirect()->route('student.quizzes.result', [
                'quiz' => $attempt->quiz_id,
                'attempt' => $attemptId
            ])->with('success', 'تم إرسال الاختبار بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting quiz: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إرسال الاختبار: ' . $e->getMessage());
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
        $attempt = QuizAttempt::where('user_id', $user->id)
            ->findOrFail($attemptId);

        if ($attempt->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'المحاولة غير جارية'
            ], 400);
        }

        $remaining = $attempt->remaining_time;

        // التحقق من انتهاء الوقت
        if ($remaining !== null && $remaining <= 0) {
            $attempt->timeout();
            return response()->json([
                'success' => false,
                'timeout' => true,
                'message' => 'انتهى الوقت'
            ]);
        }

        return response()->json([
            'success' => true,
            'remaining' => $remaining,
            'formatted' => $attempt->formatted_remaining_time
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
                $data['selected_options'] = [$request->input('option_id')];
                break;

            case 'multiple_choice':
                $data['selected_options'] = $request->input('option_ids', []);
                break;

            case 'true_false':
                $data['selected_options'] = [$request->input('option_id')];
                break;

            case 'short_answer':
            case 'essay':
                $data['answer_text'] = $request->input('answer_text');
                break;

            case 'matching':
                $data['matching_pairs'] = $request->input('matching_pairs', []);
                break;

            case 'ordering':
                $data['ordering'] = $request->input('ordering', []);
                break;

            case 'numerical':
                $data['numeric_answer'] = $request->input('numeric_answer');
                break;

            case 'fill_blanks':
                $data['fill_blanks_answers'] = $request->input('fill_blanks_answers', []);
                break;

            case 'drag_drop':
                $data['answer'] = $request->input('answer');
                break;
        }

        // حفظ ترتيب الخيارات إذا كان موجوداً
        if ($request->has('options_order')) {
            $data['options_order'] = $request->input('options_order');
        }

        return $data;
    }
}
