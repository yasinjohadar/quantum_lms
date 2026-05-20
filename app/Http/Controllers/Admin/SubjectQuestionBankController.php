<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\BuildsQuestionBankIndex;
use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectQuestionBankController extends Controller
{
    use BuildsQuestionBankIndex;

    public function __construct()
    {
        $this->middleware(['permission:question-list'])->only('index');
        $this->middleware(['permission:question-create'])->only(['create', 'aiCreate', 'aiCreateFromImage']);
        $this->middleware(['permission:question-show-import'])->only('import');
        $this->middleware(['permission:quiz-list|quiz-add-question'])->only('quizzesForAdd');
    }

    public function index(Request $request, Subject $subject)
    {
        $user = auth()->user();
        $this->authorizeManagedSubjectAccess($user, $subject);

        $subject->load('schoolClass.stage');

        $query = $this->buildQuestionIndexQuery($request, $subject);
        $questions = $query->paginate(20)->withQueryString();

        $filterLists = $this->questionIndexFilterLists($subject);

        return view('admin.pages.subjects.questions.index', [
            'subject' => $subject,
            'questions' => $questions,
            'units' => $filterLists['units'],
            'categories' => $filterLists['categories'],
            'subjects' => $filterLists['subjects'],
        ]);
    }

    public function create(Subject $subject)
    {
        $this->authorizeManagedSubjectAccess(auth()->user(), $subject);

        return redirect()->route('admin.questions.create', ['subject_id' => $subject->id]);
    }

    public function import(Subject $subject)
    {
        $this->authorizeManagedSubjectAccess(auth()->user(), $subject);

        return redirect()->route('admin.questions.import.show', ['subject_id' => $subject->id]);
    }

    public function aiCreate(Subject $subject)
    {
        $this->authorizeManagedSubjectAccess(auth()->user(), $subject);

        return redirect()->route('admin.ai.question-generations.create', ['subject_id' => $subject->id]);
    }

    public function aiCreateFromImage(Subject $subject)
    {
        $this->authorizeManagedSubjectAccess(auth()->user(), $subject);

        return redirect()->route('admin.ai.question-generations.create-from-image', ['subject_id' => $subject->id]);
    }

    public function quizzesForAdd(Request $request, Subject $subject)
    {
        $this->authorizeManagedSubjectAccess(auth()->user(), $subject);

        $questionId = $request->query('question_id') ? (int) $request->query('question_id') : null;

        $quizIds = Quiz::where('subject_id', $subject->id)->pluck('id');

        $alreadyInQuizIds = [];
        if ($questionId && $quizIds->isNotEmpty()) {
            $alreadyInQuizIds = QuizQuestion::query()
                ->where('question_id', $questionId)
                ->whereIn('quiz_id', $quizIds)
                ->pluck('quiz_id')
                ->all();
        }

        $quizzes = Quiz::query()
            ->where('subject_id', $subject->id)
            ->withCount('questions')
            ->orderByDesc('updated_at')
            ->get(['id', 'title', 'is_published', 'is_active'])
            ->map(fn (Quiz $quiz) => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'is_published' => (bool) $quiz->is_published,
                'is_active' => (bool) $quiz->is_active,
                'questions_count' => $quiz->questions_count,
                'already_added' => in_array($quiz->id, $alreadyInQuizIds, true),
            ]);

        return response()->json([
            'success' => true,
            'quizzes' => $quizzes,
        ]);
    }
}
