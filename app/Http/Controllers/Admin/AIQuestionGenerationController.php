<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AIQuestionGeneration;
use App\Models\Subject;
use App\Models\Lesson;
use App\Models\AIModel;
use App\Services\AI\AIQuestionGenerationService;
use App\Services\AI\AIModelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AIQuestionGenerationController extends Controller
{
    public function __construct(
        private AIQuestionGenerationService $generationService,
        private AIModelService $modelService
    ) {}

    /**
     * قائمة طلبات التوليد
     */
    public function index()
    {
        $generations = AIQuestionGeneration::with(['user', 'subject', 'lesson', 'model'])
                                           ->latest()
                                           ->paginate(20);

        return view('admin.pages.ai.question-generations.index', compact('generations'));
    }

    /**
     * عرض نموذج توليد أسئلة
     */
    public function create(Request $request)
    {
        $subjects = Subject::active()->ordered()->get();
        $lessons = collect();
        $models = $this->modelService->getAvailableModels('question_generation');
        $questionTypes = AIQuestionGeneration::QUESTION_TYPES;
        $difficulties = AIQuestionGeneration::DIFFICULTIES;

        if ($request->filled('subject_id')) {
            $lessons = Lesson::whereHas('unit.section', function($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            })->active()->get();
        }

        return view('admin.pages.ai.question-generations.create', compact(
            'subjects',
            'lessons',
            'models',
            'questionTypes',
            'difficulties'
        ));
    }

    /**
     * إنشاء طلب توليد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_type' => 'required|in:lesson_content,manual_text,topic',
            'lesson_id' => 'nullable|required_if:source_type,lesson_content|exists:lessons,id',
            'source_content' => 'required_if:source_type,manual_text,topic|string',
            'question_type' => 'required|in:' . implode(',', array_keys(AIQuestionGeneration::QUESTION_TYPES)),
            'number_of_questions' => 'required|integer|min:1|max:50',
            'difficulty_level' => 'required|in:' . implode(',', array_keys(AIQuestionGeneration::DIFFICULTIES)),
            'ai_model_id' => 'nullable|exists:ai_models,id',
        ], [
            'source_type.required' => 'نوع المصدر مطلوب',
            'source_content.required_if' => 'المحتوى المصدر مطلوب',
            'question_type.required' => 'نوع السؤال مطلوب',
            'number_of_questions.required' => 'عدد الأسئلة مطلوب',
        ]);

        try {
            $model = $validated['ai_model_id'] 
                ? AIModel::find($validated['ai_model_id'])
                : null;

            if ($validated['source_type'] === 'lesson_content') {
                $lesson = Lesson::findOrFail($validated['lesson_id']);
                $generation = $this->generationService->generateFromLesson($lesson, [
                    'user' => Auth::user(),
                    'model' => $model,
                    'question_type' => $validated['question_type'],
                    'number_of_questions' => $validated['number_of_questions'],
                    'difficulty_level' => $validated['difficulty_level'],
                ]);
            } elseif ($validated['source_type'] === 'topic') {
                $generation = $this->generationService->generateFromTopic($validated['source_content'], [
                    'user' => Auth::user(),
                    'model' => $model,
                    'question_type' => $validated['question_type'],
                    'number_of_questions' => $validated['number_of_questions'],
                    'difficulty_level' => $validated['difficulty_level'],
                ]);
            } else {
                $generation = $this->generationService->generateFromText($validated['source_content'], [
                    'user' => Auth::user(),
                    'model' => $model,
                    'question_type' => $validated['question_type'],
                    'number_of_questions' => $validated['number_of_questions'],
                    'difficulty_level' => $validated['difficulty_level'],
                ]);
            }

            return redirect()->route('admin.ai.question-generations.show', $generation)
                           ->with('success', 'تم إنشاء طلب التوليد بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error creating question generation: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء إنشاء طلب التوليد: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * عرض الأسئلة المولدة
     */
    public function show(AIQuestionGeneration $generation)
    {
        $generation->load(['user', 'subject', 'lesson', 'model']);
        
        // تحديث البيانات من قاعدة البيانات
        $generation->refresh();
        
        // التأكد من أن generated_questions هو array
        if ($generation->generated_questions && !is_array($generation->generated_questions)) {
            $generation->generated_questions = json_decode($generation->generated_questions, true) ?? [];
        }

        return view('admin.pages.ai.question-generations.show', compact('generation'));
    }

    /**
     * معالجة الطلب (Queue)
     */
    public function process(AIQuestionGeneration $generation)
    {
        try {
            $questions = $this->generationService->processGeneration($generation);

            return redirect()->back()
                           ->with('success', 'تم معالجة التوليد بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error processing generation: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء المعالجة: ' . $e->getMessage());
        }
    }

    /**
     * حفظ الأسئلة المولدة
     */
    public function save(AIQuestionGeneration $generation)
    {
        try {
            $questions = $this->generationService->saveGeneratedQuestions($generation);

            return redirect()->route('admin.questions.index')
                           ->with('success', 'تم حفظ ' . $questions->count() . ' سؤال بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error saving generated questions: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء حفظ الأسئلة: ' . $e->getMessage());
        }
    }

    /**
     * حفظ الأسئلة المحددة فقط
     */
    public function saveSelected(Request $request, AIQuestionGeneration $generation)
    {
        $validated = $request->validate([
            'selected_questions' => 'required|array|min:1',
            'selected_questions.*' => 'integer|min:0',
        ]);

        try {
            $selectedIndices = array_map('intval', $validated['selected_questions']);
            $questions = $this->generationService->saveGeneratedQuestions($generation, $selectedIndices);

            return redirect()->route('admin.questions.index')
                           ->with('success', 'تم حفظ ' . $questions->count() . ' سؤال بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error saving selected questions: ' . $e->getMessage(), [
                'generation_id' => $generation->id,
                'selected_indices' => $validated['selected_questions'] ?? [],
            ]);
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء حفظ الأسئلة: ' . $e->getMessage());
        }
    }

    /**
     * إعادة توليد
     */
    public function regenerate(AIQuestionGeneration $generation)
    {
        try {
            $generation->update(['status' => 'pending']);
            $questions = $this->generationService->processGeneration($generation);

            return redirect()->back()
                           ->with('success', 'تم إعادة التوليد بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error regenerating questions: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء إعادة التوليد: ' . $e->getMessage());
        }
    }
}
