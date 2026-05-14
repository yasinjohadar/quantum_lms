<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AIQuestionGeneration;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Lesson;
use App\Models\AIModel;
use App\Services\AI\AIQuestionGenerationService;
use App\Services\AI\AIModelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
     * عرض نموذج توليد أسئلة (متقدم)
     */
    public function createAdvanced(Request $request)
    {
        $schoolClasses = SchoolClass::active()->ordered()->get();
        $subjects = collect();
        $lessons = collect();
        $models = $this->modelService->getAvailableModels('question_generation');
        $difficulties = AIQuestionGeneration::DIFFICULTIES;
        $quiz = null;
        $prefillClassId = old('class_id');
        $prefillSubjectId = old('subject_id');

        if ($request->filled('quiz_id')) {
            $quiz = \App\Models\Quiz::with('subject')->find($request->quiz_id);
            if ($quiz && $quiz->subject) {
                $prefillClassId = $prefillClassId ?: $quiz->subject->class_id;
                $prefillSubjectId = $prefillSubjectId ?: $quiz->subject_id;
            }
        }

        if (! $prefillClassId && $prefillSubjectId) {
            $prefillClassId = Subject::whereKey($prefillSubjectId)->value('class_id');
        }

        if ($prefillClassId) {
            $subjects = Subject::where('class_id', $prefillClassId)->active()->ordered()->get();
        }

        if ($prefillSubjectId) {
            $lessons = Lesson::whereHas('unit.section', function ($q) use ($prefillSubjectId) {
                $q->where('subject_id', $prefillSubjectId);
            })->active()->get();
        }

        return view('admin.pages.ai.question-generations.create-advanced', compact(
            'schoolClasses',
            'subjects',
            'lessons',
            'models',
            'difficulties',
            'quiz',
            'prefillClassId',
            'prefillSubjectId'
        ));
    }

    /**
     * توليد أسئلة من صورة (تحليل بصري عميق).
     */
    public function createFromImage()
    {
        $models = $this->modelService->getAvailableModels('question_generation');
        $difficulties = AIQuestionGeneration::DIFFICULTIES;

        return view('admin.pages.ai.question-generations.create-from-image', compact('models', 'difficulties'));
    }

    /**
     * حفظ طلب توليد من صورة.
     */
    public function storeFromImage(Request $request)
    {
        $validQuestionTypes = array_filter(array_keys(AIQuestionGeneration::QUESTION_TYPES), fn ($k) => $k !== 'mixed');

        $validated = $request->validate([
            'source_image' => 'required|image|mimes:jpeg,jpg,png,webp,gif|max:8192',
            'instructions' => 'nullable|string|max:5000',
            'question_types' => 'required|array|min:1',
            'question_types.*' => 'in:'.implode(',', $validQuestionTypes),
            'number_of_questions' => 'required|integer|min:1|max:50',
            'difficulty_level' => 'required|in:'.implode(',', array_keys(AIQuestionGeneration::DIFFICULTIES)),
            'ai_model_id' => 'nullable|exists:ai_models,id',
        ], [
            'source_image.required' => 'يرجى اختيار صورة',
            'source_image.image' => 'الملف يجب أن يكون صورة',
            'question_types.required' => 'يجب اختيار نوع واحد على الأقل',
        ]);

        try {
            $model = $validated['ai_model_id']
                ? AIModel::find($validated['ai_model_id'])
                : null;

            $generation = $this->generationService->generateFromUploadedImage($request->file('source_image'), [
                'user' => Auth::user(),
                'model' => $model,
                'instructions' => $validated['instructions'] ?? '',
                'question_types' => $validated['question_types'],
                'number_of_questions' => $validated['number_of_questions'],
                'difficulty_level' => $validated['difficulty_level'],
            ]);

            return redirect()->route('admin.ai.question-generations.show', $generation)
                ->with('success', 'تم تحليل الصورة وتوليد الأسئلة بنجاح.');
        } catch (\Exception $e) {
            Log::error('storeFromImage: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return redirect()->back()
                ->with('error', 'حدث خطأ: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * عرض الصورة المصدرية (للطلبات من نوع صورة).
     */
    public function sourceImage(AIQuestionGeneration $generation)
    {
        if ($generation->source_type !== 'image' || ! $generation->source_image_path) {
            abort(404);
        }

        if (! Storage::disk('local')->exists($generation->source_image_path)) {
            abort(404);
        }

        $abs = Storage::disk('local')->path($generation->source_image_path);

        return response()->file($abs);
    }

    /**
     * مواد الصف (JSON) لتوليد الأسئلة — فلترة AJAX.
     */
    public function ajaxSubjectsByClass(SchoolClass $schoolClass)
    {
        $subjects = Subject::where('class_id', $schoolClass->id)
            ->active()
            ->ordered()
            ->get(['id', 'name']);

        return response()->json($subjects);
    }

    /**
     * دروس المادة (JSON) — مع التحقق الاختياري من تطابق الصف.
     */
    public function ajaxLessonsBySubject(Request $request, Subject $subject)
    {
        if ($request->filled('class_id') && (int) $request->query('class_id') !== (int) $subject->class_id) {
            abort(404);
        }

        $lessons = Lesson::whereHas('unit.section', function ($q) use ($subject) {
            $q->where('subject_id', $subject->id);
        })->active()->get(['id', 'title']);

        return response()->json($lessons);
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
        // زيادة وقت التنفيذ إلى 3 دقائق للطلبات الطويلة
        set_time_limit(180);
        
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
     * إنشاء طلب توليد (متقدم)
     */
    public function storeAdvanced(Request $request)
    {
        $validQuestionTypes = array_filter(array_keys(AIQuestionGeneration::QUESTION_TYPES), fn($k) => $k !== 'mixed');
        
        $validated = $request->validate([
            'source_type' => 'required|in:lesson_content,manual_text,topic',
            'lesson_id' => 'nullable|required_if:source_type,lesson_content|exists:lessons,id',
            'source_content' => 'required_if:source_type,manual_text,topic|string',
            'question_types' => 'required|array|min:1',
            'question_types.*' => 'in:' . implode(',', $validQuestionTypes),
            'number_of_questions' => 'required|integer|min:1|max:50',
            'difficulty_level' => 'required|in:' . implode(',', array_keys(AIQuestionGeneration::DIFFICULTIES)),
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'quiz_id' => 'nullable|exists:quizzes,id', // إضافة quiz_id كحقل اختياري
        ], [
            'source_type.required' => 'نوع المصدر مطلوب',
            'source_content.required_if' => 'المحتوى المصدر مطلوب',
            'question_types.required' => 'يجب اختيار نوع واحد على الأقل',
            'question_types.min' => 'يجب اختيار نوع واحد على الأقل',
            'question_types.*.in' => 'نوع سؤال غير صحيح',
            'number_of_questions.required' => 'عدد الأسئلة مطلوب',
        ]);

        try {
            $model = $validated['ai_model_id'] 
                ? AIModel::find($validated['ai_model_id'])
                : null;

            // إنشاء التوليد
            if ($validated['source_type'] === 'lesson_content') {
                $lesson = Lesson::findOrFail($validated['lesson_id']);
                $generation = $this->generationService->generateFromLesson($lesson, [
                    'user' => Auth::user(),
                    'model' => $model,
                    'question_types' => $validated['question_types'],
                    'number_of_questions' => $validated['number_of_questions'],
                    'difficulty_level' => $validated['difficulty_level'],
                ]);
            } elseif ($validated['source_type'] === 'topic') {
                $generation = $this->generationService->generateFromTopic($validated['source_content'], [
                    'user' => Auth::user(),
                    'model' => $model,
                    'question_types' => $validated['question_types'],
                    'number_of_questions' => $validated['number_of_questions'],
                    'difficulty_level' => $validated['difficulty_level'],
                ]);
            } else {
                $generation = $this->generationService->generateFromText($validated['source_content'], [
                    'user' => Auth::user(),
                    'model' => $model,
                    'question_types' => $validated['question_types'],
                    'number_of_questions' => $validated['number_of_questions'],
                    'difficulty_level' => $validated['difficulty_level'],
                ]);
            }

            // إذا كان quiz_id موجوداً، معالجة التوليد وحفظ الأسئلة وربطها بالاختبار
            if ($request->filled('quiz_id')) {
                try {
                    // معالجة التوليد
                    set_time_limit(180);
                    $this->generationService->processGeneration($generation);
                    
                    // حفظ الأسئلة المولدة
                    $savedQuestions = $this->generationService->saveGeneratedQuestions($generation);
                    
                    // ربط الأسئلة بالاختبار
                    if ($savedQuestions->isNotEmpty()) {
                        $questionIds = $savedQuestions->pluck('id')->toArray();
                        $attachedCount = \App\Http\Controllers\Admin\QuizController::attachQuestionsToQuiz(
                            $request->quiz_id,
                            $questionIds
                        );
                        
                        return redirect()->route('admin.quizzes.questions', $request->quiz_id)
                                       ->with('success', "تم توليد {$savedQuestions->count()} سؤال وربط {$attachedCount} سؤال بالاختبار بنجاح.");
                    } else {
                        return redirect()->route('admin.quizzes.questions', $request->quiz_id)
                                       ->with('warning', 'تم إنشاء طلب التوليد ولكن لم يتم توليد أي أسئلة.');
                    }
                } catch (\Exception $e) {
                    Log::error('Error processing generation for quiz: ' . $e->getMessage(), [
                        'generation_id' => $generation->id,
                        'quiz_id' => $request->quiz_id,
                    ]);
                    
                    // إعادة التوجيه إلى صفحة إدارة الأسئلة مع رسالة خطأ
                    return redirect()->route('admin.quizzes.questions', $request->quiz_id)
                                   ->with('error', 'حدث خطأ أثناء معالجة التوليد: ' . $e->getMessage());
                }
            }

            // إذا لم يكن quiz_id موجوداً، السلوك العادي
            return redirect()->route('admin.ai.question-generations.show', $generation)
                           ->with('success', 'تم إنشاء طلب التوليد بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error creating advanced question generation: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء إنشاء طلب التوليد: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * إعادة توليد
     */
    public function regenerate(AIQuestionGeneration $generation)
    {
        // زيادة وقت التنفيذ إلى 3 دقائق للطلبات الطويلة
        set_time_limit(180);
        
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
