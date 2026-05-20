<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\BuildsQuestionBankIndex;
use App\Http\Controllers\Controller;
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
}
