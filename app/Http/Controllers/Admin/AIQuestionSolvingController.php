<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AIQuestionSolution;
use App\Models\Question;
use App\Models\AIModel;
use App\Services\AI\AIQuestionSolvingService;
use App\Services\AI\AIModelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AIQuestionSolvingController extends Controller
{
    public function __construct(
        private AIQuestionSolvingService $solvingService,
        private AIModelService $modelService
    ) {}

    /**
     * قائمة الحلول
     */
    public function index(Request $request)
    {
        $query = AIQuestionSolution::with(['question', 'model', 'verifier']);

        if ($request->filled('is_verified')) {
            $query->where('is_verified', $request->boolean('is_verified'));
        }

        if ($request->filled('question_id')) {
            $query->where('question_id', $request->input('question_id'));
        }

        $solutions = $query->latest()->paginate(20);

        return view('admin.pages.ai.question-solutions.index', compact('solutions'));
    }

    /**
     * حل سؤال
     */
    public function solve(Request $request, Question $question)
    {
        try {
            $model = $request->filled('ai_model_id')
                ? AIModel::find($request->input('ai_model_id'))
                : null;

            $solution = $this->solvingService->solveQuestion($question, $model);

            return redirect()->route('admin.ai.question-solutions.show', $solution)
                           ->with('success', 'تم حل السؤال بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error solving question: ' . $e->getMessage(), [
                'question_id' => $question->id,
            ]);

            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء حل السؤال: ' . $e->getMessage());
        }
    }

    /**
     * حل عدة أسئلة
     */
    public function solveMultiple(Request $request)
    {
        $validated = $request->validate([
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:questions,id',
            'ai_model_id' => 'nullable|exists:ai_models,id',
        ]);

        try {
            $questions = Question::whereIn('id', $validated['question_ids'])->get();
            $model = $validated['ai_model_id']
                ? AIModel::find($validated['ai_model_id'])
                : null;

            $solutions = $this->solvingService->solveMultipleQuestions($questions, $model);

            return redirect()->route('admin.ai.question-solutions.index')
                           ->with('success', 'تم حل ' . $solutions->count() . ' سؤال بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error solving multiple questions: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء حل الأسئلة: ' . $e->getMessage());
        }
    }

    /**
     * التحقق من حل
     */
    public function verify(AIQuestionSolution $solution)
    {
        try {
            $this->solvingService->verifySolution($solution, Auth::user());

            return redirect()->back()
                           ->with('success', 'تم التحقق من الحل بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error verifying solution: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء التحقق: ' . $e->getMessage());
        }
    }

    /**
     * عرض حل
     */
    public function show(AIQuestionSolution $solution)
    {
        $solution->load(['question', 'model', 'verifier']);
        $accuracy = $this->solvingService->getAccuracy($solution);

        return view('admin.pages.ai.question-solutions.show', compact('solution', 'accuracy'));
    }
}
