<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use App\Services\QuizPreviewSessionService;
use Illuminate\Http\Request;

class QuizPreviewController extends Controller
{
    public function __construct(
        protected QuizPreviewSessionService $previewSession
    ) {
        $this->middleware(['permission:quiz-preview']);
    }

    /**
     * بدء معاينة كاملة بنفس قوالب الطالب (بدون حفظ في DB).
     */
    public function start(string $quiz)
    {
        $quizModel = Quiz::with(['questions' => function ($query) {
            $query->orderBy('quiz_questions.order');
        }])->findOrFail($quiz);

        if ($quizModel->questions->isEmpty()) {
            return redirect()
                ->route('admin.quizzes.show', $quizModel->id)
                ->with('error', 'لا يمكن معاينة اختبار بدون أسئلة.');
        }

        $this->previewSession->start($quizModel);

        return redirect()->route('admin.quizzes.preview.show', $quizModel->id);
    }

    public function show(string $quiz)
    {
        $quizModel = $this->loadQuizForPreview($quiz);
        $state = $this->previewSession->get($quizModel->id);

        if (! $state) {
            return redirect()->route('admin.quizzes.preview', $quizModel->id);
        }

        if (($state['status'] ?? null) !== 'in_progress') {
            return redirect()->route('admin.quizzes.preview.result', $quizModel->id);
        }

        // انتهاء الوقت تلقائياً
        $attempt = $this->previewSession->buildAttempt($quizModel, $state);
        if ($quizModel->hasTimeLimit() && ($attempt->remaining_time ?? 1) <= 0) {
            $this->previewSession->submit($quizModel, $state, 'timed_out');

            return redirect()->route('admin.quizzes.preview.result', $quizModel->id)
                ->with('info', 'انتهى وقت المعاينة.');
        }

        $questionIds = $state['question_order'] ?? $quizModel->questions->pluck('id')->all();
        $questions = Question::whereIn('id', $questionIds)
            ->with(['options' => fn ($q) => $q->orderBy('order')])
            ->get()
            ->sortBy(fn ($question) => array_search($question->id, $questionIds))
            ->values();

        $answers = $this->previewSession->buildShowAnswers($state);
        $isAdminPreview = true;
        $previewReturnUrl = route('admin.quizzes.show', $quizModel->id);

        $quizAttemptRoutes = [
            'save' => route('admin.quizzes.preview.save-answer', $quizModel->id),
            'submit' => route('admin.quizzes.preview.submit', $quizModel->id),
            'result' => route('admin.quizzes.preview.result', $quizModel->id),
            'time' => route('admin.quizzes.preview.time', $quizModel->id),
        ];

        $questionTypes = Question::TYPES;
        $questionTypeIcons = Question::TYPE_ICONS;
        $questionTypeColors = Question::TYPE_COLORS;
        $questionDifficulties = Question::DIFFICULTIES;
        $quiz = $quizModel;

        return view('student.pages.quizzes.show', compact(
            'quiz',
            'attempt',
            'questions',
            'answers',
            'questionTypes',
            'questionTypeIcons',
            'questionTypeColors',
            'questionDifficulties',
            'isAdminPreview',
            'previewReturnUrl',
            'quizAttemptRoutes'
        ));
    }

    public function saveAnswer(Request $request, string $quiz)
    {
        $quizModel = $this->loadQuizForPreview($quiz);
        $state = $this->previewSession->requireInProgress($quizModel->id);

        $request->validate([
            'question_id' => 'required|integer',
        ]);

        $questionId = (int) $request->input('question_id');
        $order = array_map('intval', $state['question_order'] ?? []);
        if (! in_array($questionId, $order, true)) {
            return response()->json(['success' => false, 'message' => 'سؤال غير صالح'], 422);
        }

        $question = Question::findOrFail($questionId);
        $answerData = $this->previewSession->prepareAnswerData($request, $question);
        $this->previewSession->saveAnswer($quizModel, $state, $question, $answerData);

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ الإجابة (معاينة)',
        ]);
    }

    public function submit(Request $request, string $quiz)
    {
        $quizModel = $this->loadQuizForPreview($quiz);
        $state = $this->previewSession->get($quizModel->id);

        if (! $state) {
            return $this->submitResponse($request, route('admin.quizzes.show', $quiz), false, 'جلسة المعاينة غير موجودة.');
        }

        if (($state['status'] ?? null) !== 'in_progress') {
            return $this->submitResponse($request, route('admin.quizzes.preview.result', $quizModel->id), true, 'تم إنهاء المعاينة مسبقاً');
        }

        if ($request->has('question_id')) {
            $question = Question::findOrFail($request->input('question_id'));
            if (in_array((int) $question->id, $state['question_order'] ?? [], true)) {
                $answerData = $this->previewSession->prepareAnswerData($request, $question);
                $state = $this->previewSession->saveAnswer($quizModel, $state, $question, $answerData);
            }
        }

        $this->previewSession->submit($quizModel, $state, 'completed');

        return $this->submitResponse(
            $request,
            route('admin.quizzes.preview.result', $quizModel->id),
            true,
            'انتهت المعاينة — هذه نتيجة تجريبية فقط'
        );
    }

    public function result(string $quiz)
    {
        $quizModel = $this->loadQuizForPreview($quiz);
        $state = $this->previewSession->get($quizModel->id);

        if (! $state || ($state['status'] ?? null) === 'in_progress') {
            return redirect()->route('admin.quizzes.preview', $quizModel->id);
        }

        $attempt = $this->previewSession->buildAttempt($quizModel, $state);
        $answers = $this->previewSession->buildAnswerModels($state, $quizModel)->values();

        $isAdminPreview = true;
        $previewReturnUrl = route('admin.quizzes.show', $quizModel->id);
        $quiz = $quizModel;

        return view('student.pages.quizzes.result', compact(
            'quiz',
            'attempt',
            'answers',
            'isAdminPreview',
            'previewReturnUrl'
        ));
    }

    public function time(string $quiz)
    {
        $quizModel = $this->loadQuizForPreview($quiz);
        $state = $this->previewSession->get($quizModel->id);

        if (! $state) {
            return response()->json([
                'success' => false,
                'timeout' => true,
                'redirect_url' => route('admin.quizzes.show', $quizModel->id),
                'message' => 'جلسة المعاينة غير موجودة',
            ]);
        }

        if (($state['status'] ?? null) !== 'in_progress') {
            return response()->json([
                'success' => false,
                'timeout' => true,
                'redirect_url' => route('admin.quizzes.preview.result', $quizModel->id),
                'message' => 'المعاينة مكتملة',
            ]);
        }

        $attempt = $this->previewSession->buildAttempt($quizModel, $state);

        if (! $quizModel->hasTimeLimit()) {
            return response()->json([
                'success' => true,
                'unlimited' => true,
                'elapsed' => $attempt->elapsed_seconds,
                'formatted_elapsed' => $attempt->formatted_elapsed_time,
            ]);
        }

        $remaining = $attempt->remaining_time;
        if ($remaining !== null && $remaining <= 0) {
            $this->previewSession->submit($quizModel, $state, 'timed_out');

            return response()->json([
                'success' => false,
                'timeout' => true,
                'redirect_url' => route('admin.quizzes.preview.result', $quizModel->id),
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

    public function exit(string $quiz)
    {
        $this->previewSession->forget((int) $quiz);

        return redirect()
            ->route('admin.quizzes.show', $quiz)
            ->with('info', 'تم إنهاء وضع المعاينة.');
    }

    protected function loadQuizForPreview(string $quiz): Quiz
    {
        return Quiz::with(['subject', 'unit', 'questions' => function ($query) {
            $query->orderBy('quiz_questions.order');
        }])->findOrFail($quiz);
    }

    protected function submitResponse(Request $request, string $url, bool $success, string $message)
    {
        $wantsJson = $request->expectsJson() || $request->ajax() || $request->wantsJson();

        if ($wantsJson) {
            return response()->json([
                'success' => $success,
                'redirect_url' => $url,
                'message' => $message,
            ], $success ? 200 : 400);
        }

        return redirect()->to($url)->with($success ? 'success' : 'error', $message);
    }
}
