<?php

namespace App\Http\Controllers\Admin;

use App\Exports\QuestionsTemplateExport;
use App\Helpers\StorageHelper;
use App\Http\Controllers\Admin\Concerns\BuildsQuestionBankIndex;
use App\Http\Controllers\Admin\Concerns\HandlesQuestionWordExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExportQuestionsWordRequest;
use App\Http\Requests\Admin\StoreQuestionRequest;
use App\Http\Requests\Admin\UpdateQuestionRequest;
use App\Imports\QuestionsImport;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuizQuestion;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Unit;
use App\Services\Storage\MediaStorageService;
use App\Support\QuestionMarkupFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class QuestionController extends Controller
{
    use BuildsQuestionBankIndex;
    use HandlesQuestionWordExport;

    public function __construct()
    {
        $this->middleware(['permission:question-list'])->only('index');
        $this->middleware(['permission:question-list|question-create|question-edit|question-show-import|question-import'])->only(['ajaxSubjectsByClass', 'ajaxUnitsBySubject']);
        $this->middleware(['permission:question-create'])->only(['create', 'store']);
        $this->middleware(['permission:question-edit'])->only(['edit', 'update']);
        $this->middleware(['permission:question-delete'])->only(['destroy', 'destroyMultiple']);
        $this->middleware(['permission:question-show'])->only('show');
        $this->middleware(['permission:question-duplicate'])->only('duplicate');
        $this->middleware(['permission:question-toggle-status'])->only('toggleStatus');
        // رفع صور المحرّك: من لديه تعديل/إنشاء أسئلة يكفي (مع الاحتفاظ بصلاحية الرفع الصريحة)
        $this->middleware(['permission:question-upload-image|question-edit|question-create'])->only('uploadImage');
        $this->middleware(['permission:question-export'])->only(['export', 'exportWord']);
        $this->middleware(['permission:question-export|question-delete'])->only('bulkSelectableIds');
        $this->middleware(['permission:question-export-template'])->only('exportTemplate');
        $this->middleware(['permission:question-import'])->only('import');
        $this->middleware(['permission:question-show-import'])->only('showImport');
    }

    /**
     * عرض قائمة الأسئلة (بنك الأسئلة)
     */
    public function index(Request $request)
    {
        $query = $this->buildQuestionIndexQuery($request, null);
        $questions = $query->paginate(20)->withQueryString();
        $filterLists = $this->questionIndexFilterLists(null, $request);

        $bulkDeleteUrl = auth()->user()->can('question-delete')
            ? route('admin.questions.destroy-multiple', $request->query())
            : null;

        $canBulkSelect = auth()->user()->can('question-export') || auth()->user()->can('question-delete');

        $viewData = [
            'questions' => $questions,
            'units' => $filterLists['units'],
            'categories' => $filterLists['categories'],
            'subjects' => $filterLists['subjects'],
            'schoolClasses' => $filterLists['schoolClasses'] ?? collect(),
            'initialSubjects' => $filterLists['initialSubjects'] ?? collect(),
            'subject' => null,
            'createRoute' => route('admin.questions.create'),
            'showGlobalTools' => true,
            'enableAjaxFilters' => true,
            'bulkDeleteUrl' => $bulkDeleteUrl,
            'bulkIdsUrl' => $canBulkSelect ? route('admin.questions.bulk-ids') : null,
        ];

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.pages.questions.partials.bank-index-results', $viewData)->render(),
            ]);
        }

        return view('admin.pages.questions.index', $viewData);
    }

    /**
     * عرض صفحة إنشاء سؤال جديد
     */
    public function create(Request $request)
    {
        $lockedSubject = $this->resolveLockedSubject($request);

        $schoolClasses = SchoolClass::active()->ordered()->get();
        $categories = Question::distinct()->whereNotNull('category')->pluck('category');
        $selectedType = $request->type ?? 'single_choice';

        $preselectedUnitId = $request->unit_id;
        $preselectedUnit = $preselectedUnitId
            ? Unit::with('section.subject.schoolClass')->find($preselectedUnitId)
            : null;

        $initialUnitIds = old('units', $preselectedUnitId ? [(int) $preselectedUnitId] : []);
        $linkedUnits = $this->resolveLinkedUnitsForForm($initialUnitIds);

        $preselectedQuizId = $request->query('quiz_id') ?? $request->input('quiz_id');
        if ($preselectedQuizId !== null && $preselectedQuizId !== '') {
            session(['create_question_return_quiz_id' => $preselectedQuizId]);
        }

        if ($lockedSubject) {
            session(['create_question_return_subject_id' => $lockedSubject->id]);
        }

        return view('admin.pages.questions.create', compact(
            'schoolClasses',
            'categories',
            'selectedType',
            'preselectedUnit',
            'linkedUnits',
            'preselectedQuizId',
            'lockedSubject'
        ));
    }

    /**
     * حفظ سؤال جديد
     */
    public function store(StoreQuestionRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');
            $data['case_sensitive'] = $request->has('case_sensitive');
            $data['created_by'] = auth()->id();

            if (isset($data['title'])) {
                $data['title'] = Question::normalizeHtmlEmbeddedImageUrls($data['title']);
            }
            if (isset($data['content'])) {
                $data['content'] = Question::normalizeHtmlEmbeddedImageUrls($data['content']);
            }
            if (isset($data['explanation'])) {
                $data['explanation'] = Question::normalizeHtmlEmbeddedImageUrls($data['explanation']);
            }

            $this->normalizeQuestionTextFields($data);

            // رفع الصورة
            if ($request->hasFile('image')) {
                $uploadResult = MediaStorageService::uploadImage($request->file('image'), 'questions/images');
                $data['image'] = $uploadResult['path'];
            }

            // معالجة الوسوم
            if (isset($data['tags']) && is_array($data['tags'])) {
                $data['tags'] = array_filter($data['tags']);
            }

            // إنشاء السؤال
            $question = Question::create($data);

            // ربط الوحدات
            $unitIds = $request->input('units', []);
            if ($request->filled('units')) {
                $question->units()->sync($unitIds);
            }

            $this->applyQuestionSubjectId($question, $request->input('subject_id'), $unitIds);

            // إنشاء الخيارات
            if ($request->filled('options') && $question->has_options) {
                $options = $request->options;
                // لسؤال صح/خطأ: تحديد الخيار الصحيح من correct_option
                if ($question->type === 'true_false' && $request->has('correct_option')) {
                    $correctIndex = (int) $request->correct_option;
                    if (isset($options[$correctIndex])) {
                        $options[$correctIndex]['is_correct'] = true;
                    }
                }
                $this->saveOptions($question, $options);
            }

            // للأسئلة الرقمية - إنشاء خيار يحتوي على الإجابة الصحيحة
            if ($question->type === 'numerical' && $request->filled('correct_answer')) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content' => $this->normalizeOptionContent($request->correct_answer),
                    'is_correct' => true,
                    'order' => 1,
                ]);
            }

            DB::commit();

            // إذا تم إنشاء السؤال من داخل صفحة اختبار، نضيفه للاختبار ونرجع لصفحة الاختبار (نستخدم الطلب أو الجلسة)
            $quizId = $request->input('quiz_id') ?? session('create_question_return_quiz_id');
            $quizId = is_numeric($quizId) ? (int) $quizId : $quizId;
            if ($quizId !== null && $quizId !== '') {
                $quiz = \App\Models\Quiz::find($quizId);
                if ($quiz) {
                    if (! $quiz->questions()->where('question_id', $question->id)->exists()) {
                        $maxOrder = $quiz->quizQuestions()->max('order') ?? 0;
                        QuizQuestion::create([
                            'quiz_id' => $quiz->id,
                            'question_id' => $question->id,
                            'order' => $maxOrder + 1,
                            'points' => $question->default_points,
                            'is_required' => true,
                        ]);
                        $quiz->calculateTotalPoints();
                    }
                    session()->forget('create_question_return_quiz_id');
                    // إذا ضغط "حفظ وإنشاء سؤال جديد" نرجع لصفحة إنشاء سؤال مع نفس الاختبار
                    if ($request->has('save_and_new')) {
                        return redirect()
                            ->route('admin.questions.create', ['quiz_id' => $quiz->id])
                            ->with('success', 'تم حفظ السؤال وإضافته للاختبار. أضف سؤالاً جديداً أدناه.');
                    }

                    return redirect()
                        ->route('admin.quizzes.questions', $quiz->id)
                        ->with('success', 'تم إنشاء السؤال وإضافته للاختبار بنجاح')
                        ->with('added_question_id', $question->id);
                }
                session()->forget('create_question_return_quiz_id');
            }

            return $this->redirectAfterQuestionSave($request, 'تم إنشاء السؤال بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating question: '.$e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء السؤال: '.$e->getMessage());
        }
    }

    /**
     * عرض تفاصيل سؤال
     */
    public function show(string $id)
    {
        $question = Question::with(['units.section.subject.schoolClass', 'creator', 'options', 'quizzes'])
            ->findOrFail($id);

        return view('admin.pages.questions.show', compact('question'));
    }

    /**
     * عرض صفحة تعديل سؤال
     */
    public function edit(Request $request, string $id)
    {
        $question = Question::with(['units.section.subject.schoolClass', 'options'])->findOrFail($id);
        $lockedSubject = $this->resolveLockedSubject($request) ?? $question->subject;

        if ($lockedSubject) {
            $this->authorizeManagedSubjectAccess(auth()->user(), $lockedSubject);
            session(['edit_question_return_subject_id' => $lockedSubject->id]);
        }

        $schoolClasses = SchoolClass::active()->ordered()->get();
        $categories = Question::distinct()->whereNotNull('category')->pluck('category');
        $linkedUnits = $this->resolveLinkedUnitsForForm(
            old('units', $question->units->pluck('id')->all())
        );
        $preselectedQuizId = $request->query('quiz_id');
        if ($preselectedQuizId !== null && $preselectedQuizId !== '') {
            session(['edit_question_return_quiz_id' => $preselectedQuizId]);
        }

        return view('admin.pages.questions.edit', compact(
            'question',
            'schoolClasses',
            'categories',
            'linkedUnits',
            'preselectedQuizId',
            'lockedSubject'
        ));
    }

    /**
     * مواد الصف (JSON) لنماذج ربط السؤال بالمنهج.
     */
    public function ajaxSubjectsByClass(SchoolClass $schoolClass)
    {
        $subjects = $this->subjectsForClassFilterQuery($schoolClass)
            ->get(['id', 'name']);

        return response()->json($subjects);
    }

    /**
     * وحدات المادة (JSON) لنماذج ربط السؤال بالمنهج.
     */
    public function ajaxUnitsBySubject(Subject $subject)
    {
        $units = Unit::whereHas('section', function ($q) use ($subject) {
            $q->where('subject_id', $subject->id);
        })->orderBy('title')->get(['id', 'title']);

        return response()->json($units);
    }

    /**
     * @param  array<int|string>  $unitIds
     * @return \Illuminate\Support\Collection<int, Unit>
     */
    protected function resolveLinkedUnitsForForm(array $unitIds): \Illuminate\Support\Collection
    {
        $unitIds = array_values(array_filter(array_map('intval', $unitIds)));
        if ($unitIds === []) {
            return collect();
        }

        return Unit::with('section.subject.schoolClass')
            ->whereIn('id', $unitIds)
            ->get()
            ->sortBy(fn (Unit $unit) => array_search($unit->id, $unitIds, true))
            ->values();
    }

    /**
     * تحديث سؤال
     */
    public function update(UpdateQuestionRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $question = Question::findOrFail($id);
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');
            $data['case_sensitive'] = $request->has('case_sensitive');

            if (isset($data['title'])) {
                $data['title'] = Question::normalizeHtmlEmbeddedImageUrls($data['title']);
            }
            if (isset($data['content'])) {
                $data['content'] = Question::normalizeHtmlEmbeddedImageUrls($data['content']);
            }
            if (isset($data['explanation'])) {
                $data['explanation'] = Question::normalizeHtmlEmbeddedImageUrls($data['explanation']);
            }

            $this->normalizeQuestionTextFields($data);

            // رفع صورة جديدة
            if ($request->hasFile('image')) {
                // حذف الصورة القديمة
                if ($question->image) {
                    MediaStorageService::delete($question->image);
                }
                $uploadResult = MediaStorageService::uploadImage($request->file('image'), 'questions/images');
                $data['image'] = $uploadResult['path'];
            } elseif ($request->boolean('remove_image')) {
                if ($question->image) {
                    MediaStorageService::delete($question->image);
                }
                $data['image'] = null;
            }

            // معالجة الوسوم
            if (isset($data['tags']) && is_array($data['tags'])) {
                $data['tags'] = array_filter($data['tags']);
            }

            // تحديث السؤال
            $question->update($data);

            // تحديث الوحدات
            $unitIds = $request->input('units', []);
            $question->units()->sync($unitIds);

            $this->applyQuestionSubjectId($question, $request->input('subject_id'), $unitIds);

            // تحديث الخيارات
            if ($question->has_options) {
                $options = $request->options ?? [];
                if ($question->type === 'true_false' && $request->has('correct_option')) {
                    $correctIndex = (int) $request->correct_option;
                    if (isset($options[$correctIndex])) {
                        $options[$correctIndex]['is_correct'] = true;
                    }
                }
                $this->updateOptions($question, $options);
            }

            // للأسئلة الرقمية
            if ($question->type === 'numerical' && $request->filled('correct_answer')) {
                $question->options()->delete();
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content' => $this->normalizeOptionContent($request->correct_answer),
                    'is_correct' => true,
                    'order' => 1,
                ]);
            }

            DB::commit();

            // إذا كان هناك quiz_id في الـ request، العودة إلى صفحة إدارة أسئلة الاختبار
            $quizId = $request->input('quiz_id') ?? session('edit_question_return_quiz_id');
            if ($quizId) {
                session()->forget('edit_question_return_quiz_id');

                return redirect()
                    ->route('admin.quizzes.questions', $quizId)
                    ->with('success', 'تم تحديث السؤال بنجاح');
            }

            return $this->redirectAfterQuestionSave($request, 'تم تحديث السؤال بنجاح', 'update');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating question: '.$e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث السؤال: '.$e->getMessage());
        }
    }

    /**
     * حذف سؤال
     */
    public function destroy(string $id)
    {
        try {
            $question = Question::with('options')->findOrFail($id);
            $result = $this->deleteQuestionSafely($question);

            if ($result === 'used_in_quizzes') {
                return redirect()
                    ->back()
                    ->with('error', 'لا يمكن حذف السؤال لأنه مستخدم في اختبارات');
            }

            if ($result !== null) {
                return redirect()
                    ->back()
                    ->with('error', 'حدث خطأ أثناء حذف السؤال');
            }

            return redirect()
                ->route('admin.questions.index')
                ->with('success', 'تم حذف السؤال بنجاح');

        } catch (\Exception $e) {
            Log::error('Error deleting question: '.$e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء حذف السؤال: '.$e->getMessage());
        }
    }

    /**
     * حذف عدة أسئلة من البنك الرئيسي.
     */
    public function destroyMultiple(Request $request)
    {
        $validated = $request->validate([
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['integer', 'exists:questions,id'],
        ], [
            'question_ids.required' => 'يرجى تحديد سؤال واحد على الأقل.',
            'question_ids.min' => 'يرجى تحديد سؤال واحد على الأقل.',
        ]);

        $ids = array_values(array_unique($validated['question_ids']));

        $query = Question::with(['options'])->whereIn('id', $ids);
        $this->applyTeacherQuestionScope($query);
        $questions = $query->get();

        $counts = $this->bulkDeleteQuestions($questions);
        $flash = $this->bulkDeleteFlashMessage(
            $counts['deleted'],
            $counts['skipped_quiz'],
            $counts['failed']
        );

        return redirect()
            ->route('admin.questions.index', $request->query())
            ->with($flash['type'], $flash['message']);
    }

    /**
     * نسخ سؤال
     */
    public function duplicate(string $id)
    {
        try {
            DB::beginTransaction();

            $original = Question::with(['units', 'options'])->findOrFail($id);

            // نسخ السؤال
            $newQuestion = $original->replicate();
            $newQuestion->title = $original->title.' (نسخة)';
            $newQuestion->created_by = auth()->id();
            $newQuestion->save();

            // نسخ الوحدات
            $newQuestion->units()->sync($original->units->pluck('id'));

            // نسخ الخيارات
            foreach ($original->options as $option) {
                $newOption = $option->replicate();
                $newOption->question_id = $newQuestion->id;
                $newOption->save();
            }

            DB::commit();

            return redirect()
                ->route('admin.questions.edit', $newQuestion->id)
                ->with('success', 'تم نسخ السؤال بنجاح، يمكنك تعديله الآن');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error duplicating question: '.$e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء نسخ السؤال');
        }
    }

    /**
     * تبديل حالة السؤال
     */
    public function toggleStatus(string $id)
    {
        try {
            $question = Question::findOrFail($id);
            $question->is_active = ! $question->is_active;
            $question->save();

            $status = $question->is_active ? 'تفعيل' : 'إلغاء تفعيل';

            return redirect()
                ->back()
                ->with('success', "تم {$status} السؤال بنجاح");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحديث حالة السؤال');
        }
    }

    /**
     * حفظ خيارات السؤال
     */
    public function mathPreview(Request $request): JsonResponse
    {
        $request->validate([
            'text' => ['nullable', 'string'],
        ]);

        return response()->json([
            'html' => format_question_markup($request->input('text', '')),
        ]);
    }

    protected function normalizeQuestionTextFields(array &$data): void
    {
        foreach (['title', 'content', 'explanation'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = QuestionMarkupFormatter::normalizeForStorage($data[$field]);
            }
        }

        if (isset($data['blank_answers']) && is_array($data['blank_answers'])) {
            $data['blank_answers'] = array_map(function ($answer) {
                return is_string($answer)
                    ? QuestionMarkupFormatter::normalizeForStorage($answer)
                    : $answer;
            }, $data['blank_answers']);
        }
    }

    protected function normalizeOptionContent(?string $content): ?string
    {
        if ($content === null) {
            return null;
        }

        return QuestionMarkupFormatter::normalizeForStorage($content);
    }

    protected function saveOptions(Question $question, array $options): void
    {
        foreach ($options as $index => $optionData) {
            $option = new QuestionOption([
                'question_id' => $question->id,
                'content' => $this->normalizeOptionContent($optionData['content'] ?? null),
                'is_correct' => isset($optionData['is_correct']),
                'match_target' => $this->normalizeOptionContent($optionData['match_target'] ?? null),
                'correct_order' => $optionData['correct_order'] ?? null,
                'feedback' => isset($optionData['feedback']) && is_string($optionData['feedback'])
                    ? QuestionMarkupFormatter::normalizeForStorage($optionData['feedback'])
                    : ($optionData['feedback'] ?? null),
                'order' => $index + 1,
            ]);

            // رفع صورة الخيار
            if (isset($optionData['image']) && $optionData['image'] instanceof \Illuminate\Http\UploadedFile) {
                $uploadResult = MediaStorageService::uploadImage($optionData['image'], 'question_options');
                $option->image = $uploadResult['path'];
            }

            $option->save();
        }
    }

    /**
     * تحديث خيارات السؤال
     */
    protected function updateOptions(Question $question, array $options): void
    {
        $existingIds = [];

        foreach ($options as $index => $optionData) {
            $data = [
                'content' => $this->normalizeOptionContent($optionData['content'] ?? null),
                'is_correct' => isset($optionData['is_correct']),
                'match_target' => $this->normalizeOptionContent($optionData['match_target'] ?? null),
                'correct_order' => $optionData['correct_order'] ?? null,
                'feedback' => isset($optionData['feedback']) && is_string($optionData['feedback'])
                    ? QuestionMarkupFormatter::normalizeForStorage($optionData['feedback'])
                    : ($optionData['feedback'] ?? null),
                'order' => $index + 1,
            ];

            if (! empty($optionData['id'])) {
                // تحديث خيار موجود
                $option = QuestionOption::find($optionData['id']);
                if ($option) {
                    // رفع صورة جديدة
                    if (isset($optionData['image']) && $optionData['image'] instanceof \Illuminate\Http\UploadedFile) {
                        if ($option->image) {
                            MediaStorageService::delete($option->image);
                        }
                        $imageFile = $optionData['image'];
                        $imageName = time().'_'.$imageFile->getClientOriginalName();
                        $uploadResult = MediaStorageService::uploadImage($imageFile, 'question_options', $imageName);
                        $data['image'] = $uploadResult['path'];
                    } elseif (isset($optionData['remove_image']) && $optionData['remove_image']) {
                        if ($option->image) {
                            MediaStorageService::delete($option->image);
                        }
                        $data['image'] = null;
                    }

                    $option->update($data);
                    $existingIds[] = $option->id;
                }
            } else {
                // إنشاء خيار جديد
                $data['question_id'] = $question->id;

                if (isset($optionData['image']) && $optionData['image'] instanceof \Illuminate\Http\UploadedFile) {
                    $imageFile = $optionData['image'];
                    $imageName = time().'_'.$imageFile->getClientOriginalName();
                    $uploadResult = MediaStorageService::uploadImage($imageFile, 'question_options', $imageName);
                    $data['image'] = $uploadResult['path'];
                }

                $option = QuestionOption::create($data);
                $existingIds[] = $option->id;
            }
        }

        // حذف الخيارات المحذوفة
        $toDelete = $question->options()->whereNotIn('id', $existingIds)->get();
        foreach ($toDelete as $option) {
            if ($option->image) {
                MediaStorageService::delete($option->image);
            }
            $option->delete();
        }
    }

    /**
     * رفع صورة من TinyMCE
     */
    public function uploadImage(Request $request)
    {
        try {
            $request->validate([
                'file' => ['required', 'file', 'max:5120', 'mimes:jpeg,jpg,png,gif,webp'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'الملف غير صالح',
                'errors' => $e->errors(),
            ], 422);
        }

        if (! $request->hasFile('file')) {
            return response()->json(['error' => 'لم يتم رفع الملف'], 400);
        }

        $path = '';

        try {
            $uploadResult = MediaStorageService::uploadImage($request->file('file'), 'questions/images');
            $path = (string) ($uploadResult['path'] ?? '');
        } catch (\Throwable $e) {
            Log::warning('TinyMCE question image: MediaStorageService failed, using public disk fallback', [
                'message' => $e->getMessage(),
            ]);

            try {
                $image = $request->file('file');
                $ext = strtolower((string) ($image->getClientOriginalExtension() ?: $image->guessExtension() ?: 'png'));
                $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'png';
                $imageName = time().'_'.uniqid('', true).'.'.$ext;
                $path = $image->storeAs('questions/images', $imageName, 'public');
            } catch (\Throwable $inner) {
                Log::error('TinyMCE question image: fallback upload failed', [
                    'message' => $inner->getMessage(),
                ]);

                return response()->json([
                    'error' => 'تعذّر رفع الصورة: '.$inner->getMessage(),
                ], 500);
            }
        }

        $path = ltrim(str_replace('\\', '/', trim($path)), '/');
        if ($path === '') {
            return response()->json(['error' => 'تعذّر تحديد مسار الصورة بعد الرفع'], 500);
        }

        if (! MediaStorageService::exists($path)) {
            Log::error('TinyMCE question image: file missing after upload', ['path' => $path]);

            return response()->json([
                'error' => 'الملف غير موجود بعد الرفع. تحقق من إعدادات التخزين أو صلاحيات المجلد storage.',
            ], 500);
        }

        $url = tinymce_public_image_url($path);
        if ($url === '' || $url === '/') {
            return response()->json(['error' => 'تعذّر إنشاء رابط للصورة بعد الرفع'], 500);
        }

        $url = Question::absoluteImageUrlForDisplay($url);

        Log::info('TinyMCE question image uploaded', [
            'path' => $path,
            'location' => $url,
        ]);

        return response()->json(['location' => $url]);
    }

    /**
     * تصدير الأسئلة (Excel — قيد التطوير)
     */
    public function export(Request $request)
    {
        return redirect()->back()->with('info', 'تصدير Excel قيد التطوير. استخدم تصدير Word من بنك الأسئلة.');
    }

    public function exportWord(ExportQuestionsWordRequest $request)
    {
        return $this->downloadQuestionsWord($request);
    }

    public function bulkSelectableIds(Request $request)
    {
        return $this->bulkSelectableIdsResponse($request);
    }

    /**
     * تصدير ملف Template للاستيراد
     */
    public function exportTemplate()
    {
        try {
            $data = [
                [
                    'type' => 'single_choice',
                    'title' => 'ما هي عاصمة مصر؟',
                    'content' => 'اختر الإجابة الصحيحة',
                    'difficulty' => 'easy',
                    'points' => 1,
                    'category' => 'جغرافيا',
                    'option1' => 'القاهرة',
                    'option1_correct' => 'true',
                    'option2' => 'الإسكندرية',
                    'option2_correct' => 'false',
                    'option3' => 'الجيزة',
                    'option3_correct' => 'false',
                    'option4' => 'أسوان',
                    'option4_correct' => 'false',
                ],
                [
                    'type' => 'multiple_choice',
                    'title' => 'ما هي دول الخليج؟',
                    'content' => 'اختر جميع الإجابات الصحيحة',
                    'difficulty' => 'medium',
                    'points' => 2,
                    'category' => 'جغرافيا',
                    'option1' => 'السعودية',
                    'option1_correct' => 'true',
                    'option2' => 'الإمارات',
                    'option2_correct' => 'true',
                    'option3' => 'مصر',
                    'option3_correct' => 'false',
                    'option4' => 'الكويت',
                    'option4_correct' => 'true',
                ],
                [
                    'type' => 'true_false',
                    'title' => 'القاهرة هي عاصمة مصر',
                    'content' => '',
                    'difficulty' => 'easy',
                    'points' => 1,
                    'category' => 'جغرافيا',
                    'option1' => 'صح',
                    'option1_correct' => 'true',
                    'option2' => 'خطأ',
                    'option2_correct' => 'false',
                ],
                [
                    'type' => 'short_answer',
                    'title' => 'ما هي عاصمة السعودية؟',
                    'content' => 'اكتب الإجابة',
                    'difficulty' => 'easy',
                    'points' => 1,
                    'category' => 'جغرافيا',
                    'case_sensitive' => 'false',
                ],
                [
                    'type' => 'numerical',
                    'title' => 'ما هو ناتج 5 × 5؟',
                    'content' => '',
                    'difficulty' => 'easy',
                    'points' => 1,
                    'category' => 'رياضيات',
                    'correct_answer' => 25,
                    'tolerance' => 0,
                ],
            ];

            return Excel::download(new QuestionsTemplateExport($data), 'questions_template.xlsx');

        } catch (\Exception $e) {
            Log::error('Error exporting template: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء تصدير الملف');
        }
    }

    /**
     * عرض صفحة الاستيراد
     */
    public function showImport(Request $request)
    {
        $lockedSubject = $this->resolveLockedSubject($request);

        $schoolClasses = SchoolClass::active()->ordered()->get();
        $prefillSubjectId = $lockedSubject?->id ?? $request->query('subject_id');
        $prefillClassId = $lockedSubject?->class_id
            ?? ($prefillSubjectId ? Subject::whereKey($prefillSubjectId)->value('class_id') : null);
        $prefillUnitId = $request->query('unit_id');

        return view('admin.pages.questions.import', compact(
            'lockedSubject',
            'schoolClasses',
            'prefillClassId',
            'prefillSubjectId',
            'prefillUnitId'
        ));
    }

    /**
     * استيراد الأسئلة
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            'column_mapping' => ['nullable', 'string'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
        ]);

        $lockedSubject = $this->resolveLockedSubject($request);

        $curriculumError = $this->validateImportCurriculum(
            $request->input('class_id'),
            $request->input('subject_id'),
            $request->input('unit_id')
        );
        if ($curriculumError) {
            return redirect()
                ->back()
                ->withErrors(['subject_id' => $curriculumError])
                ->withInput();
        }

        try {
            $columnMapping = [];
            if ($request->filled('column_mapping')) {
                $columnMapping = json_decode($request->column_mapping, true) ?? [];
            }

            $subjectId = $lockedSubject?->id
                ?? ($request->filled('subject_id') ? (int) $request->input('subject_id') : null);

            if (! $subjectId && $request->filled('unit_id')) {
                $unit = Unit::with('section')->find($request->input('unit_id'));
                $subjectId = $unit?->section?->subject_id;
            }

            $defaultUnitId = $request->filled('unit_id') ? (int) $request->input('unit_id') : null;

            $import = new QuestionsImport($columnMapping, $subjectId, $defaultUnitId);

            Excel::import($import, $request->file('file'));

            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();

            $message = "تم استيراد {$successCount} سؤال بنجاح";

            if ($errorCount > 0) {
                $message .= "، وحدثت {$errorCount} أخطاء";
                session()->flash('import_errors', $errors);
            }

            $redirectRoute = $lockedSubject
                ? route('admin.subjects.questions.index', $lockedSubject->id)
                : route('admin.questions.index');

            return redirect()
                ->to($redirectRoute)
                ->with('success', $message)
                ->with('import_summary', [
                    'success' => $successCount,
                    'errors' => $errorCount,
                    'total' => $successCount + $errorCount,
                ]);

        } catch (\Exception $e) {
            Log::error('Error importing questions: '.$e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء استيراد الملف: '.$e->getMessage());
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

    protected function validateImportCurriculum(?string $classId, ?string $subjectId, ?string $unitId): ?string
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

    protected function applyQuestionSubjectId(Question $question, ?int $subjectId, array $unitIds): void
    {
        if ($subjectId) {
            $question->update(['subject_id' => $subjectId]);

            return;
        }

        if ($unitIds !== []) {
            $unit = Unit::with('section')->find($unitIds[0]);
            if ($unit?->section?->subject_id) {
                $question->update(['subject_id' => $unit->section->subject_id]);
            }
        }
    }

    protected function redirectAfterQuestionSave(Request $request, string $message, string $action = 'create')
    {
        $quizId = $request->input('quiz_id')
            ?? ($action === 'create' ? session('create_question_return_quiz_id') : session('edit_question_return_quiz_id'));

        if ($quizId) {
            if ($action === 'create') {
                session()->forget('create_question_return_quiz_id');
            } else {
                session()->forget('edit_question_return_quiz_id');
            }

            return redirect()
                ->route('admin.quizzes.questions', $quizId)
                ->with('success', $message);
        }

        $subjectId = $request->input('subject_id')
            ?? ($action === 'create' ? session('create_question_return_subject_id') : session('edit_question_return_subject_id'));

        if ($subjectId) {
            if ($action === 'create') {
                session()->forget('create_question_return_subject_id');
            } else {
                session()->forget('edit_question_return_subject_id');
            }

            return redirect()
                ->route('admin.subjects.questions.index', $subjectId)
                ->with('success', $message);
        }

        return redirect()
            ->route('admin.questions.index')
            ->with('success', $message);
    }
}
