<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Quiz;
use App\Support\PreviewQuizAnswer;
use App\Support\QuizPreviewAttempt;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class QuizPreviewSessionService
{
    public function sessionKey(int $quizId): string
    {
        return 'quiz_preview.'.$quizId;
    }

    public function start(Quiz $quiz): array
    {
        $quiz->loadMissing(['questions' => function ($query) {
            $query->orderBy('quiz_questions.order');
        }]);

        $questionIds = $quiz->questions->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        if ($quiz->shuffle_questions) {
            shuffle($questionIds);
        }

        $state = [
            'token' => (string) Str::uuid(),
            'quiz_id' => $quiz->id,
            'user_id' => Auth::id(),
            'attempt_number' => 1,
            'started_at' => now()->toIso8601String(),
            'finished_at' => null,
            'status' => 'in_progress',
            'question_order' => $questionIds,
            'answers' => [],
            'score' => 0,
            'max_score' => (float) ($quiz->total_points ?? 0),
            'percentage' => 0,
            'passed' => false,
            'time_spent' => null,
        ];

        session([$this->sessionKey($quiz->id) => $state]);

        return $state;
    }

    public function get(int $quizId): ?array
    {
        $state = session($this->sessionKey($quizId));

        return is_array($state) ? $state : null;
    }

    public function forget(int $quizId): void
    {
        session()->forget($this->sessionKey($quizId));
    }

    public function put(int $quizId, array $state): void
    {
        session([$this->sessionKey($quizId) => $state]);
    }

    public function requireInProgress(int $quizId): array
    {
        $state = $this->get($quizId);
        if (! $state || ($state['status'] ?? null) !== 'in_progress') {
            abort(404, 'جلسة المعاينة غير موجودة أو منتهية.');
        }

        return $state;
    }

    public function saveAnswer(Quiz $quiz, array $state, Question $question, array $answerData): array
    {
        $questionId = (int) $question->id;
        $existing = $state['answers'][$questionId] ?? [];

        $state['answers'][$questionId] = array_merge($existing, $answerData, [
            'question_id' => $questionId,
            'max_points' => (float) (
                $question->pivot?->points
                ?? \App\Models\QuizQuestion::where('quiz_id', $quiz->id)->where('question_id', $questionId)->value('points')
                ?? $question->default_points
                ?? 0
            ),
            'answered_at' => now()->toIso8601String(),
        ]);

        $this->put($quiz->id, $state);

        return $state;
    }

    public function prepareAnswerData(Request $request, Question $question): array
    {
        $data = [];

        switch ($question->type) {
            case 'single_choice':
            case 'true_false':
                $selectedOptions = $request->input('selected_options', []);
                if (empty($selectedOptions)) {
                    $optionId = $request->input('option_id');
                    $selectedOptions = $optionId ? [$optionId] : [];
                }
                if (! is_array($selectedOptions)) {
                    $selectedOptions = $selectedOptions ? [$selectedOptions] : [];
                }
                $selectedOptions = array_filter($selectedOptions, fn ($v) => $v !== null && $v !== '');
                $data['selected_options'] = array_values($selectedOptions);
                break;

            case 'multiple_choice':
                $selectedOptions = $request->input('selected_options', []);
                if (! is_array($selectedOptions)) {
                    $selectedOptions = $selectedOptions ? [$selectedOptions] : [];
                }
                $selectedOptions = array_filter($selectedOptions, fn ($v) => $v !== null && $v !== '');
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
                    $ordering = array_filter(array_map('trim', explode(',', $ordering)));
                }
                $data['ordering'] = $ordering ?? [];
                break;

            case 'numerical':
                $data['numeric_answer'] = $request->input('numeric_answer');
                break;

            case 'fill_blanks':
                $fillBlanksAnswers = $request->input('fill_blanks_answers', []);
                if (! is_array($fillBlanksAnswers)) {
                    $fillBlanksAnswers = [];
                }
                $result = [];
                foreach ($fillBlanksAnswers as $key => $value) {
                    $result[(int) $key] = $value;
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

        if ($request->has('options_order')) {
            $data['options_order'] = $request->input('options_order');
        }

        return $data;
    }

    public function submit(Quiz $quiz, array $state, ?string $status = 'completed'): array
    {
        $questions = Question::whereIn('id', $state['question_order'] ?? [])
            ->with(['options' => fn ($q) => $q->orderBy('order')])
            ->get()
            ->keyBy('id');

        $score = 0.0;
        $maxScore = 0.0;
        $gradedAnswers = [];

        foreach ($state['question_order'] ?? [] as $questionId) {
            $question = $questions->get($questionId);
            if (! $question) {
                continue;
            }

            $raw = $state['answers'][$questionId] ?? [
                'question_id' => $questionId,
            ];

            $maxPoints = (float) ($raw['max_points'] ?? $question->default_points ?? 0);
            if ($maxPoints <= 0 && method_exists($quiz, 'quizQuestions')) {
                $maxPoints = (float) (\App\Models\QuizQuestion::where('quiz_id', $quiz->id)
                    ->where('question_id', $questionId)
                    ->value('points') ?? $question->default_points ?? 0);
            }
            $maxScore += $maxPoints;

            $previewAnswer = new PreviewQuizAnswer;
            $previewAnswer->fill(array_merge([
                'question_id' => $questionId,
                'max_points' => $maxPoints,
                'selected_options' => null,
                'answer_text' => null,
                'numeric_answer' => null,
                'matching_pairs' => null,
                'ordering' => null,
                'fill_blanks_answers' => null,
                'drag_drop_assignments' => null,
                'options_order' => null,
            ], $raw));
            $previewAnswer->setRelation('question', $question);
            $previewAnswer->autoGrade();

            if ($previewAnswer->needs_manual_grading && ! $previewAnswer->is_graded) {
                // في المعاينة: لا تصحيح يدوي — نعتبرها بدون درجة
                $previewAnswer->is_graded = true;
                $previewAnswer->is_correct = false;
                $previewAnswer->points_earned = 0;
            }

            $score += (float) ($previewAnswer->points_earned ?? 0);

            $gradedAnswers[$questionId] = array_merge($raw, [
                'is_correct' => (bool) $previewAnswer->is_correct,
                'is_partially_correct' => (bool) ($previewAnswer->is_partially_correct ?? false),
                'points_earned' => (float) ($previewAnswer->points_earned ?? 0),
                'max_points' => $maxPoints,
                'is_graded' => true,
                'needs_manual_grading' => (bool) ($previewAnswer->needs_manual_grading ?? false),
                'selected_options' => $previewAnswer->selected_options,
                'answer_text' => $previewAnswer->answer_text,
                'numeric_answer' => $previewAnswer->numeric_answer,
                'matching_pairs' => $previewAnswer->matching_pairs,
                'ordering' => $previewAnswer->ordering,
                'fill_blanks_answers' => $previewAnswer->fill_blanks_answers,
                'drag_drop_assignments' => $previewAnswer->drag_drop_assignments,
            ]);
        }

        if ($maxScore <= 0) {
            $maxScore = (float) ($quiz->total_points ?? 0);
        }

        $percentage = $maxScore > 0 ? ($score / $maxScore) * 100 : 0;
        $passPercentage = (float) ($quiz->pass_percentage ?? 50);

        $startedAt = Carbon::parse($state['started_at']);
        $finishedAt = now();

        $state['answers'] = $gradedAnswers;
        $state['status'] = $status;
        $state['finished_at'] = $finishedAt->toIso8601String();
        $state['time_spent'] = max(0, (int) $startedAt->diffInSeconds($finishedAt));
        $state['score'] = round($score, 2);
        $state['max_score'] = round($maxScore, 2);
        $state['percentage'] = round($percentage, 2);
        $state['passed'] = $percentage >= $passPercentage;

        $this->put($quiz->id, $state);

        return $state;
    }

    public function buildAttempt(Quiz $quiz, array $state): QuizPreviewAttempt
    {
        $answerModels = $this->buildAnswerModels($state, $quiz);

        return new QuizPreviewAttempt($state, $quiz, $answerModels);
    }

    /**
     * @return Collection<int, PreviewQuizAnswer>
     */
    public function buildAnswerModels(array $state, Quiz $quiz): Collection
    {
        $questionIds = array_keys($state['answers'] ?? []);
        $questions = Question::whereIn('id', $questionIds)
            ->with(['options' => fn ($q) => $q->orderBy('order')])
            ->get()
            ->keyBy('id');

        $models = collect();
        foreach ($state['answers'] ?? [] as $questionId => $raw) {
            $question = $questions->get($questionId);
            if (! $question) {
                continue;
            }

            $answer = new PreviewQuizAnswer;
            $answer->fill(array_merge([
                'question_id' => (int) $questionId,
                'max_points' => (float) ($question->default_points ?? 0),
            ], $raw));
            $answer->setRelation('question', $question);
            $models->put((int) $questionId, $answer);
        }

        return $models;
    }

    /**
     * إجابات للقائمة الجانبية أثناء الحل (بدون تصحيح كامل).
     *
     * @return Collection<int, object>
     */
    public function buildShowAnswers(array $state): Collection
    {
        return collect($state['answers'] ?? [])->mapWithKeys(function ($raw, $questionId) {
            $questionId = (int) $questionId;

            return [$questionId => (object) array_merge([
                'question_id' => $questionId,
                'answer' => null,
                'answer_text' => null,
                'selected_options' => null,
                'numeric_answer' => null,
                'matching_pairs' => null,
                'ordering' => null,
                'fill_blanks_answers' => null,
                'drag_drop_assignments' => null,
            ], $raw, ['question_id' => $questionId])];
        });
    }
}
