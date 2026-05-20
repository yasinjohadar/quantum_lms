<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\QuestionGenerationProcessException;
use App\Http\Controllers\Admin\Concerns\BuildsQuestionBankIndex;
use App\Http\Controllers\Controller;
use App\Models\AIModel;
use App\Models\AIQuestionGeneration;
use App\Models\Lesson;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Unit;
use App\Services\AI\AIModelService;
use App\Services\AI\AIQuestionGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AIQuestionGenerationController extends Controller
{
    use BuildsQuestionBankIndex;

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
            $lessons = Lesson::whereHas('unit.section', function ($q) use ($request) {
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
    public function createFromImage(Request $request)
    {
        $lockedSubject = $this->resolveLockedSubject($request);

        $schoolClasses = SchoolClass::active()->ordered()->get();
        $models = $this->modelService->getAvailableModels('question_generation');
        $difficulties = AIQuestionGeneration::DIFFICULTIES;
        $prefillClassId = old('class_id');
        $prefillSubjectId = old('subject_id') ?: $request->query('subject_id');
        $prefillUnitId = old('unit_id');

        if ($lockedSubject) {
            $prefillSubjectId = $lockedSubject->id;
            $prefillClassId = $lockedSubject->class_id;
            session(['ai_generation_return_subject_id' => $lockedSubject->id]);
        } elseif (! $prefillClassId && $prefillSubjectId) {
            $prefillClassId = Subject::whereKey($prefillSubjectId)->value('class_id');
        }

        return view('admin.pages.ai.question-generations.create-from-image', compact(
            'schoolClasses',
            'models',
            'difficulties',
            'prefillClassId',
            'prefillSubjectId',
            'prefillUnitId',
            'lockedSubject'
        ));
    }

    /**
     * حفظ طلب توليد من صورة.
     */
    public function storeFromImage(Request $request)
    {
        $validQuestionTypes = array_filter(array_keys(AIQuestionGeneration::QUESTION_TYPES), fn ($k) => $k !== 'mixed');

        $imageMaxKb = (int) config('ai.question_generation_pdf.image_max_size_kb', 8192);
        $pdfMaxKb = (int) config('ai.question_generation_pdf.max_size_kb', 15360);

        $uploadedFile = $request->file('source_file') ?? $request->file('source_image');

        $validated = $request->validate([
            'source_file' => [
                'nullable',
                'file',
                'mimes:jpeg,jpg,png,webp,gif,pdf',
                'max:'.max($imageMaxKb, $pdfMaxKb),
            ],
            'source_image' => [
                'nullable',
                'file',
                'mimes:jpeg,jpg,png,webp,gif,pdf',
                'max:'.max($imageMaxKb, $pdfMaxKb),
            ],
            'instructions' => 'nullable|string|max:5000',
            'class_id' => 'nullable|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'unit_id' => 'nullable|exists:units,id',
            'question_types' => 'required|array|min:1',
            'question_types.*' => 'in:'.implode(',', $validQuestionTypes),
            'number_of_questions' => 'required|integer|min:1|max:50',
            'difficulty_level' => 'required|in:'.implode(',', array_keys(AIQuestionGeneration::DIFFICULTIES)),
            'ai_model_id' => 'nullable|exists:ai_models,id',
        ], [
            'source_file.mimes' => 'يُقبل فقط: JPEG, PNG, WebP, GIF أو PDF',
            'source_image.mimes' => 'يُقبل فقط: JPEG, PNG, WebP, GIF أو PDF',
            'question_types.required' => 'يجب اختيار نوع واحد على الأقل',
        ]);

        if (! $uploadedFile) {
            return redirect()->back()
                ->withErrors(['source_file' => 'يرجى اختيار ملف (صورة أو PDF)'])
                ->withInput();
        }

        $curriculumError = $this->validateImageGenerationCurriculum(
            $validated['class_id'] ?? null,
            $validated['subject_id'] ?? null,
            $validated['unit_id'] ?? null
        );
        if ($curriculumError) {
            return redirect()->back()
                ->withErrors(['subject_id' => $curriculumError])
                ->withInput();
        }

        try {
            $model = $validated['ai_model_id']
                ? AIModel::find($validated['ai_model_id'])
                : null;

            $generation = $this->generationService->generateFromUploadedFile($uploadedFile, [
                'user' => Auth::user(),
                'model' => $model,
                'instructions' => $validated['instructions'] ?? '',
                'subject_id' => $validated['subject_id'] ?? null,
                'unit_id' => $validated['unit_id'] ?? null,
                'question_types' => $validated['question_types'],
                'number_of_questions' => $validated['number_of_questions'],
                'difficulty_level' => $validated['difficulty_level'],
            ]);

            $generation->refresh();

            if ($generation->status === 'completed') {
                return redirect()->route('admin.ai.question-generations.show', $generation)
                    ->with('success', 'تم تحليل الملف وتوليد الأسئلة بنجاح.');
            }

            if ($generation->status === 'failed') {
                $errorMessage = $generation->error_message
                    ?? AIQuestionGenerationService::humanizeApiErrorMessage('فشل توليد الأسئلة.');

                return redirect()->route('admin.ai.question-generations.show', $generation)
                    ->with('error', $errorMessage);
            }

            return redirect()->route('admin.ai.question-generations.show', $generation)
                ->with('warning', 'تم إنشاء الطلب وهو قيد المعالجة.');
        } catch (QuestionGenerationProcessException $e) {
            Log::error('storeFromImage: '.$e->getMessage(), [
                'generation_id' => $e->generation->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('admin.ai.question-generations.show', $e->generation)
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('storeFromImage: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return redirect()->back()
                ->with('error', AIQuestionGenerationService::humanizeApiErrorMessage($e->getMessage()))
                ->withInput();
        }
    }

    /**
     * عرض الملف المصدر (صورة أو PDF).
     */
    public function sourceImage(AIQuestionGeneration $generation)
    {
        if (! in_array($generation->source_type, ['image', 'pdf'], true) || ! $generation->source_image_path) {
            abort(404);
        }

        if (! Storage::disk('local')->exists($generation->source_image_path)) {
            abort(404);
        }

        $abs = Storage::disk('local')->path($generation->source_image_path);

        if ($generation->source_type === 'pdf') {
            return response()->file($abs, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="source.pdf"',
            ]);
        }

        return response()->file($abs);
    }

    /**
     * التحقق من تطابق الصف والمادة والوحدة (اختياري).
     */
    private function validateImageGenerationCurriculum(?string $classId, ?string $subjectId, ?string $unitId): ?string
    {
        if ($unitId && ! $subjectId) {
            return 'يجب اختيار المادة عند تحديد الوحدة.';
        }

        if ($subjectId) {
            $subject = Subject::find($subjectId);
            if (! $subject) {
                return 'المادة المحددة غير صالحة.';
            }
            if ($classId && (int) $subject->class_id !== (int) $classId) {
                return 'المادة لا تنتمي للصف المحدد.';
            }
        }

        if ($unitId) {
            $belongsToSubject = Unit::whereKey($unitId)
                ->whereHas('section', fn ($q) => $q->where('subject_id', $subjectId))
                ->exists();
            if (! $belongsToSubject) {
                return 'الوحدة لا تنتمي للمادة المحددة.';
            }
        }

        return null;
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
     * وحدات المادة (JSON) — فلترة AJAX.
     */
    public function ajaxUnitsBySubject(Subject $subject)
    {
        $units = Unit::whereHas('section', function ($q) use ($subject) {
            $q->where('subject_id', $subject->id);
        })->orderBy('title')->get(['id', 'title']);

        return response()->json($units);
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
            'question_type' => 'required|in:'.implode(',', array_keys(AIQuestionGeneration::QUESTION_TYPES)),
            'number_of_questions' => 'required|integer|min:1|max:50',
            'difficulty_level' => 'required|in:'.implode(',', array_keys(AIQuestionGeneration::DIFFICULTIES)),
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
            Log::error('Error creating question generation: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إنشاء طلب التوليد: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * عرض الأسئلة المولدة
     */
    public function show(AIQuestionGeneration $generation)
    {
        $generation->load(['user', 'subject.schoolClass', 'unit', 'lesson', 'model']);
        $generation->refresh();

        $questions = $generation->getResolvedGeneratedQuestions();
        $questionsCount = count($questions);

        // إصلاح سجلات قديمة: JSON بصيغة غلاف أو حقول بديلة تُخزَّن فارغة رغم وجود رد AI
        if ($generation->status === 'completed' && $questionsCount > 0) {
            $stored = $generation->generated_questions;
            $needsRepair = ! is_array($stored)
                || $stored === []
                || ! array_is_list($stored)
                || count($stored) !== $questionsCount;

            if ($needsRepair) {
                $validated = $this->generationService->validateGeneratedQuestions($questions);
                if (count($validated) > 0) {
                    $generation->update(['generated_questions' => $validated]);
                    $generation->refresh();
                    $questions = $generation->getResolvedGeneratedQuestions();
                    $questionsCount = count($questions);
                }
            }
        }

        $returnUrl = $this->resolveAiGenerationReturnUrl($generation);

        return view('admin.pages.ai.question-generations.show', compact(
            'generation',
            'questions',
            'questionsCount',
            'returnUrl'
        ));
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
            Log::error('Error processing generation: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء المعالجة: '.$e->getMessage());
        }
    }

    /**
     * حفظ الأسئلة المولدة
     */
    public function save(AIQuestionGeneration $generation)
    {
        try {
            $questions = $this->generationService->saveGeneratedQuestions($generation);
            $generation->refresh();

            $returnUrl = $this->resolveAiGenerationReturnUrl($generation);
            session()->forget('ai_generation_return_subject_id');

            return redirect()->route('admin.ai.question-generations.show', $generation)
                ->with('success', 'تم حفظ '.$questions->count().' سؤال في بنك الأسئلة بنجاح.')
                ->with('saved_to_bank', true)
                ->with('return_url', $returnUrl);
        } catch (\Exception $e) {
            Log::error('Error saving generated questions: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حفظ الأسئلة: '.$e->getMessage());
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
            $generation->refresh();

            $returnUrl = $this->resolveAiGenerationReturnUrl($generation);
            session()->forget('ai_generation_return_subject_id');

            return redirect()->route('admin.ai.question-generations.show', $generation)
                ->with('success', 'تم حفظ '.$questions->count().' سؤال في بنك الأسئلة بنجاح.')
                ->with('saved_to_bank', true)
                ->with('return_url', $returnUrl);
        } catch (\Exception $e) {
            Log::error('Error saving selected questions: '.$e->getMessage(), [
                'generation_id' => $generation->id,
                'selected_indices' => $validated['selected_questions'] ?? [],
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حفظ الأسئلة: '.$e->getMessage());
        }
    }

    /**
     * إنشاء طلب توليد (متقدم)
     */
    public function storeAdvanced(Request $request)
    {
        $validQuestionTypes = array_filter(array_keys(AIQuestionGeneration::QUESTION_TYPES), fn ($k) => $k !== 'mixed');

        $validated = $request->validate([
            'source_type' => 'required|in:lesson_content,manual_text,topic',
            'lesson_id' => 'nullable|required_if:source_type,lesson_content|exists:lessons,id',
            'source_content' => 'required_if:source_type,manual_text,topic|string',
            'question_types' => 'required|array|min:1',
            'question_types.*' => 'in:'.implode(',', $validQuestionTypes),
            'number_of_questions' => 'required|integer|min:1|max:50',
            'difficulty_level' => 'required|in:'.implode(',', array_keys(AIQuestionGeneration::DIFFICULTIES)),
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
                    Log::error('Error processing generation for quiz: '.$e->getMessage(), [
                        'generation_id' => $generation->id,
                        'quiz_id' => $request->quiz_id,
                    ]);

                    // إعادة التوجيه إلى صفحة إدارة الأسئلة مع رسالة خطأ
                    return redirect()->route('admin.quizzes.questions', $request->quiz_id)
                        ->with('error', 'حدث خطأ أثناء معالجة التوليد: '.$e->getMessage());
                }
            }

            // إذا لم يكن quiz_id موجوداً، السلوك العادي
            return redirect()->route('admin.ai.question-generations.show', $generation)
                ->with('success', 'تم إنشاء طلب التوليد بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error creating advanced question generation: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إنشاء طلب التوليد: '.$e->getMessage())
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
            Log::error('Error regenerating questions: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إعادة التوليد: '.$e->getMessage());
        }
    }

    protected function resolveLockedSubject(Request $request): ?Subject
    {
        $subjectId = $request->input('subject_id') ?? $request->query('subject_id');
        if (! $subjectId) {
            return null;
        }

        $subject = Subject::with('schoolClass.stage')->find($subjectId);
        if (! $subject) {
            return null;
        }

        $this->authorizeManagedSubjectAccess(auth()->user(), $subject);

        return $subject;
    }

    protected function resolveAiGenerationReturnUrl(AIQuestionGeneration $generation): string
    {
        $sessionSubjectId = session('ai_generation_return_subject_id');

        if ($sessionSubjectId && (int) $sessionSubjectId === (int) $generation->subject_id) {
            return route('admin.subjects.questions.index', $sessionSubjectId);
        }

        if ($generation->subject_id) {
            return route('admin.subjects.questions.index', $generation->subject_id);
        }

        return route('admin.questions.index');
    }
}
