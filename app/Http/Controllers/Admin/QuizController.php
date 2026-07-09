<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuizRequest;
use App\Http\Requests\Admin\UpdateQuizRequest;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\SystemSetting;
use App\Models\Unit;
use App\Services\QuizExcelQuestionImportService;
use App\Services\QuizPackQuestionImportService;
use App\Services\ReminderService;
use App\Services\StaffNotificationService;
use App\Services\Storage\MediaStorageService;
use App\Services\StudentContentNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class QuizController extends Controller
{
    public function __construct(
        private ReminderService $reminderService,
        private StaffNotificationService $staffNotificationService,
        private QuizExcelQuestionImportService $quizExcelQuestionImportService,
        private QuizPackQuestionImportService $quizPackQuestionImportService,
    ) {
        $this->middleware(['permission:quiz-list'])->only('index');
        $this->middleware(['permission:quiz-create'])->only(['create', 'store', 'storeForSection', 'showImportExcel']);
        $this->middleware(['permission:quiz-edit'])->only(['edit', 'update']);
        $this->middleware(['permission:quiz-delete'])->only('destroy');
        $this->middleware(['permission:quiz-show'])->only('show');
        $this->middleware(['permission:quiz-questions'])->only('questions');
        $this->middleware(['permission:quiz-add-question'])->only('addQuestion');
        $this->middleware(['permission:quiz-remove-question'])->only('removeQuestion');
        $this->middleware(['permission:quiz-reorder-questions'])->only('reorderQuestions');
        $this->middleware(['permission:quiz-update-question-points'])->only('updateQuestionPoints');
        $this->middleware(['permission:quiz-duplicate'])->only('duplicate');
        $this->middleware(['permission:quiz-toggle-publish'])->only('togglePublish');
        $this->middleware(['permission:quiz-preview'])->only('preview');
        $this->middleware(['permission:quiz-results'])->only('results');
        $this->middleware(['permission:quiz-export-results'])->only('exportResults');
        $this->middleware(['permission:quiz-get-subjects-by-class'])->only('getSubjectsByClass');
        $this->middleware(['permission:quiz-get-classes-by-stage'])->only('getClassesByStage');
        $this->middleware(['permission:quiz-get-units'])->only('getUnits');
        $this->middleware(['permission:quiz-approve-review'])->only('approveReview');
        $this->middleware(['permission:quiz-reject-review'])->only('rejectReview');
        $this->middleware(['permission:quiz-submit-for-review'])->only('submitForReview');
        $this->middleware(['permission:question-import'])->only(['importExcel', 'importNerveTest', 'importQuestionPack']);
    }

    /**
     * عرض قائمة الاختبارات
     */
    public function index(Request $request)
    {
        $query = Quiz::with(['subject.schoolClass', 'unit', 'creator'])
            ->withCount(['questions', 'attempts']);

        // إذا كان المستخدم معلم وليس مشرف/مدير
        $user = auth()->user();
        if ($user->usesTeacherAssignmentScope()) {
            $classIds = $user->assignedClasses()->pluck('classes.id');
            $subjectIds = $user->assignedSubjects()->pluck('subjects.id');

            $query->whereHas('subject', function ($q) use ($classIds, $subjectIds) {
                // المواد من الصفوف المخصصة
                if ($classIds->isNotEmpty()) {
                    $q->whereIn('class_id', $classIds);
                }
                // أو المواد المخصصة مباشرة
                if ($subjectIds->isNotEmpty()) {
                    $q->orWhereIn('id', $subjectIds);
                }
            });
        }

        // إذا كان المستخدم مشرف وليس مدير
        if ($user->usesSupervisorAssignmentScope()) {
            $query->forSupervisor($user->id);
        }

        // البحث
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // تصفية حسب الصف
        if ($request->filled('class_id')) {
            $query->whereHas('subject', function ($q) use ($request) {
                $q->where('class_id', $request->input('class_id'));
            });
        }

        // تصفية حسب المادة
        if ($request->filled('subject_id')) {
            $query->forSubject($request->subject_id);
        }

        // تصفية حسب الحالة
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        // تصفية حسب النشر
        if ($request->filled('is_published')) {
            $query->where('is_published', $request->is_published === '1');
        }

        // تصفية حسب حالة المراجعة
        if ($request->filled('review_status')) {
            $query->where('review_status', $request->input('review_status'));
        }

        $quizzes = $query->ordered()->paginate(15)->withQueryString();
        $subjects = Subject::with('schoolClass')->orderBy('name')->get();
        $classes = SchoolClass::with('stage')->orderBy('name')->get();

        // إذا كان طلب Ajax، إرجاع JSON
        if ($request->expectsJson() || $request->ajax()) {
            $html = view('admin.pages.quizzes.partials.table', compact('quizzes'))->render();
            $pagination = view('admin.pages.quizzes.partials.pagination', compact('quizzes'))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination,
                'count' => $quizzes->total(),
            ]);
        }

        return view('admin.pages.quizzes.index', compact('quizzes', 'subjects', 'classes'));
    }

    /**
     * عرض صفحة إنشاء اختبار جديد
     */
    public function create(Request $request)
    {
        $stages = Stage::ordered()->get();
        $units = collect();
        $selectedStageId = null;
        $selectedClassId = null;

        $selectedSubjectId = $request->get('subject_id');
        $selectedUnitId = $request->get('unit_id');
        $selectedLessonId = $request->get('lesson_id');
        $selectedSectionId = $request->filled('section_id') ? (int) $request->input('section_id') : null;

        $selectedSubject = null;
        $selectedUnit = null;
        $selectedClass = null;
        $selectedLesson = null;
        $selectedSection = null;
        $isFromSubjectOrUnit = false;
        $isFromLesson = false;
        $isFromSection = false;

        // في حال تم تمرير درس، نحمّل الدرس والوحدة والمادة المرتبطة به
        if ($selectedLessonId) {
            $selectedLesson = Lesson::with('unit.section.subject.schoolClass')->find($selectedLessonId);
            if ($selectedLesson && $selectedLesson->unit && $selectedLesson->unit->section) {
                $selectedUnit = $selectedLesson->unit;
                $selectedSubject = $selectedUnit->section->subject;
                $selectedClass = $selectedSubject?->schoolClass;
                $selectedSubjectId = $selectedSubject?->id;
                $selectedUnitId = $selectedUnit?->id;
                $isFromSubjectOrUnit = true;
                $isFromLesson = true;

                // تحميل وحدات المادة المرتبطة بالدرس (للاستمرارية إن احتجنا)
                $units = Unit::whereHas('section', function ($q) use ($selectedSubjectId) {
                    $q->where('subject_id', $selectedSubjectId);
                })->orderBy('title')->get();
            }
        }

        // إنشاء من شاشة القسم: ?section_id=...&scope=section (&subject_id اختياري)
        if (! $selectedLessonId && $selectedSectionId) {
            $selectedSection = SubjectSection::with('subject.schoolClass')->find($selectedSectionId);
            if ($selectedSection) {
                if ($selectedSubjectId && (int) $selectedSubjectId !== (int) $selectedSection->subject_id) {
                    $selectedSubjectId = $selectedSection->subject_id;
                }
                if (! $selectedSubjectId) {
                    $selectedSubjectId = $selectedSection->subject_id;
                }
                $selectedSubject = Subject::with('schoolClass')->find($selectedSubjectId);
                $selectedClass = $selectedSubject?->schoolClass;
                $isFromSubjectOrUnit = true;
                $isFromSection = true;
                $units = Unit::where('section_id', $selectedSection->id)->orderBy('title')->get();
            }
        }

        if ($selectedSubjectId) {
            if (! $selectedSubject) {
                $selectedSubject = Subject::with('schoolClass')->find($selectedSubjectId);
            }
            if ($selectedSubject) {
                $selectedClass = $selectedSubject->schoolClass;
                $isFromSubjectOrUnit = true;

                if (! $isFromSection) {
                    $units = Unit::whereHas('section', function ($q) use ($selectedSubjectId) {
                        $q->where('subject_id', $selectedSubjectId);
                    })->orderBy('title')->get();
                }
            }
        }

        if ($selectedUnitId) {
            $selectedUnit = Unit::with('section.subject.schoolClass')->find($selectedUnitId);
            if ($selectedUnit) {
                if (! $selectedSubject) {
                    $selectedSubject = $selectedUnit->section->subject;
                    $selectedClass = $selectedSubject->schoolClass;
                }
                $isFromSubjectOrUnit = true;

                // إذا لم تكن الوحدات محملة بعد، قم بتحميلها
                if ($units->isEmpty() && $selectedSubject) {
                    $subjectId = $selectedSubject->id;
                    $units = Unit::whereHas('section', function ($q) use ($subjectId) {
                        $q->where('subject_id', $subjectId);
                    })->orderBy('title')->get();
                }
            }
        }

        if ($request->filled('section_id') && ! $selectedLessonId && ! $selectedSection) {
            return redirect()
                ->route('admin.quizzes.index')
                ->with('error', 'القسم المحدد في الرابط غير موجود أو غير صالح.');
        }

        if ($selectedClass) {
            $selectedStageId = $selectedClass->stage_id;
            $selectedClassId = $selectedClass->id;
        }

        return view('admin.pages.quizzes.create', compact(
            'stages',
            'units',
            'selectedStageId',
            'selectedClassId',
            'selectedSubjectId',
            'selectedUnitId',
            'selectedLessonId',
            'selectedSectionId',
            'selectedSubject',
            'selectedUnit',
            'selectedClass',
            'selectedLesson',
            'selectedSection',
            'isFromSubjectOrUnit',
            'isFromLesson',
            'isFromSection'
        ));
    }

    /**
     * حفظ اختبار جديد
     */
    public function store(StoreQuizRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['created_by'] = auth()->id();
            $this->syncQuizSubjectFromUnit($data);

            // منطق المراجعة
            $user = auth()->user();
            $submitsForReview = $user->shouldSubmitQuizForReview();

            if ($submitsForReview) {
                $this->applyTeacherQuizReviewOnCreate($data);
            } elseif ($user->isQuizContentUploader() && SystemSetting::quizMandatoryReviewEnabled()) {
                $this->applyTeacherQuizReviewOnCreate($data);
                $submitsForReview = true;
            } else {
                // المشرف والمدير: خيار التفعيل الواحد يحدد is_published أيضاً
                $data['is_published'] = $request->has('is_active');
                if ($data['is_published']) {
                    $data['review_status'] = Quiz::REVIEW_STATUS_APPROVED;
                } else {
                    $data['review_status'] = Quiz::REVIEW_STATUS_DRAFT;
                }
            }

            // نوع الاختبار والتبعية
            $data['lesson_id'] = $request->input('lesson_id');
            $defaultScope = 'unit';
            if (! empty($data['lesson_id'])) {
                $defaultScope = 'lesson';
            } elseif (! empty($data['section_id']) && empty($data['unit_id'])) {
                $defaultScope = 'section';
            }
            $data['scope'] = $request->input('scope', $defaultScope);

            if (! empty($data['unit_id']) && empty($data['section_id'])) {
                $data['section_id'] = Unit::where('id', $data['unit_id'])->value('section_id');
            }

            if (! empty($data['lesson_id']) && empty($data['section_id'])) {
                $lessonSectionId = Lesson::where('id', $data['lesson_id'])->value('section_id');
                if (! $lessonSectionId) {
                    $lessonSectionId = Lesson::query()
                        ->where('id', $data['lesson_id'])
                        ->join('units', 'units.id', '=', 'lessons.unit_id')
                        ->value('units.section_id');
                }
                $data['section_id'] = $lessonSectionId;
            }

            if (! empty($data['unit_id']) && ! empty($data['section_id'])) {
                $unitSectionId = Unit::where('id', $data['unit_id'])->value('section_id');
                if ((int) $unitSectionId !== (int) $data['section_id']) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', 'القسم المحدد لا يطابق الوحدة المحددة.');
                }
            }

            // رفع الصورة
            if ($request->hasFile('image')) {
                $uploadResult = MediaStorageService::uploadImage($request->file('image'), 'quizzes');
                $data['image'] = $uploadResult['path'];
            }

            // إنشاء الاختبار
            $quiz = Quiz::create($data);

            if ($quiz->review_status === Quiz::REVIEW_STATUS_PENDING && $submitsForReview) {
                $this->dispatchNotificationSafely(function () use ($quiz, $user) {
                    $this->staffNotificationService->notifyQuizSubmittedForReview($quiz->fresh(), $user);
                }, 'quiz_review_submitted', (int) $quiz->id);
            }

            DB::commit();

            app(StudentContentNotificationService::class)->notifyIfQuizBecameVisible(
                null,
                $quiz->fresh(),
                auth()->user()
            );

            $submittedForReview = $quiz->review_status === Quiz::REVIEW_STATUS_PENDING && $submitsForReview;
            $successMessage = $submittedForReview
                ? 'تم إنشاء الاختبار وإرساله للمراجعة. يمكنك استيراد الأسئلة (Excel / MD / CSV) أو تخطي هذه الخطوة.'
                : 'تم إنشاء الاختبار. يمكنك استيراد الأسئلة (Excel / MD / CSV) أو تخطي هذه الخطوة.';

            return redirect()
                ->route('admin.quizzes.import-excel.show', $quiz)
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating quiz: '.$e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء الاختبار: '.$e->getMessage());
        }
    }

    /**
     * حفظ اختبار جديد مرتبط مباشرةً بقسم (بدون وحدة).
     */
    public function storeForSection(StoreQuizRequest $request, SubjectSection $section)
    {
        $request->merge([
            'subject_id' => $section->subject_id,
            'section_id' => $section->id,
            'unit_id' => null,
        ]);

        return $this->store($request);
    }

    /**
     * الخطوة 2: استيراد أسئلة وربطها بالاختبار (اختياري).
     */
    public function showImportExcel(string $id)
    {
        $quiz = Quiz::with(['subject.schoolClass', 'unit'])->findOrFail($id);
        $curriculum = $this->quizPackQuestionImportService->quizCurriculumForImport($quiz);
        $lockedSubject = $quiz->subject
            ?? ($curriculum['subject_id'] ? Subject::find($curriculum['subject_id']) : null);

        return view('admin.pages.quizzes.import-excel', [
            'quiz' => $quiz,
            'lockedSubject' => $lockedSubject,
            'canImport' => $curriculum['can_import'],
            'prefillClassId' => $curriculum['class_id'],
            'prefillSubjectId' => $curriculum['subject_id'],
            'prefillUnitId' => $curriculum['unit_id'],
        ]);
    }

    /**
     * تنفيذ استيراد Excel وربط الأسئلة بالاختبار.
     */
    public function importExcel(Request $request, string $id)
    {
        $quiz = Quiz::findOrFail($id);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            'column_mapping' => ['nullable', 'string'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
        ]);

        try {
            $columnMapping = [];
            if ($request->filled('column_mapping')) {
                $columnMapping = json_decode($request->column_mapping, true) ?? [];
            }

            $summary = $this->quizExcelQuestionImportService->importAndAttach(
                $quiz,
                $request->file('file'),
                $columnMapping
            );

            if ($summary['success_count'] === 0) {
                return redirect()
                    ->route('admin.quizzes.import-excel.show', $quiz)
                    ->withInput()
                    ->with('error', 'لم يتم استيراد أي سؤال. راجع الملف وأعد المحاولة.')
                    ->with('import_errors', $summary['errors'])
                    ->with('import_summary', $summary);
            }

            $message = "تم استيراد {$summary['success_count']} سؤال وربط {$summary['attached_count']} بالاختبار";
            if ($summary['error_count'] > 0) {
                $message .= "، وحدثت {$summary['error_count']} أخطاء";
            }

            return redirect()
                ->route('admin.quizzes.questions', $quiz)
                ->with('success', $message)
                ->with('import_errors', $summary['errors'])
                ->with('import_summary', $summary);

        } catch (ValidationException $e) {
            return redirect()
                ->route('admin.quizzes.import-excel.show', $quiz)
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error importing quiz questions from Excel: '.$e->getMessage(), [
                'quiz_id' => $quiz->id,
            ]);

            return redirect()
                ->route('admin.quizzes.import-excel.show', $quiz)
                ->withInput()
                ->with('error', 'حدث خطأ أثناء استيراد الملف: '.$e->getMessage());
        }
    }

    /**
     * استيراد حزمة اختبار الأعصاب (MD/CSV) وربطها بالاختبار.
     */
    public function importNerveTest(Request $request, string $id)
    {
        $quiz = Quiz::findOrFail($id);

        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'format' => ['required', \Illuminate\Validation\Rule::in(['md', 'csv'])],
        ]);

        try {
            $summary = $this->quizPackQuestionImportService->importNerveTestAndAttach(
                $quiz,
                $request->file('file'),
                $request->input('format')
            );

            if ($summary['count'] === 0) {
                return redirect()
                    ->route('admin.quizzes.import-excel.show', $quiz)
                    ->with('error', 'لم يتم استيراد أي سؤال. راجع الملف وأعد المحاولة.');
            }

            return redirect()
                ->route('admin.quizzes.questions', $quiz)
                ->with('success', "تم استيراد {$summary['count']} سؤال وربط {$summary['attached_count']} بالاختبار.")
                ->with('import_summary', [
                    'success' => $summary['count'],
                    'errors' => 0,
                    'total' => $summary['count'],
                ]);
        } catch (ValidationException $e) {
            return redirect()
                ->route('admin.quizzes.import-excel.show', $quiz)
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error importing nerve test for quiz: '.$e->getMessage(), ['quiz_id' => $quiz->id]);

            return redirect()
                ->route('admin.quizzes.import-excel.show', $quiz)
                ->with('error', 'حدث خطأ أثناء الاستيراد: '.$e->getMessage());
        }
    }

    /**
     * استيراد حزمة أسئلة (MD/CSV) وربطها بالاختبار.
     */
    public function importQuestionPack(Request $request, string $id)
    {
        $quiz = Quiz::findOrFail($id);

        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'format' => ['required', \Illuminate\Validation\Rule::in(['md', 'csv'])],
            'target_type' => ['required', \Illuminate\Validation\Rule::in(['single_choice', 'fill_blanks'])],
        ]);

        try {
            $summary = $this->quizPackQuestionImportService->importQuestionPackAndAttach(
                $quiz,
                $request->file('file'),
                $request->input('format'),
                $request->input('target_type')
            );

            if ($summary['count'] === 0) {
                return redirect()
                    ->route('admin.quizzes.import-excel.show', $quiz)
                    ->with('error', 'لم يتم استيراد أي سؤال. راجع الملف وأعد المحاولة.');
            }

            $typeLabel = $request->input('target_type') === 'fill_blanks' ? 'املأ الفراغ' : 'اختيار من متعدد';

            return redirect()
                ->route('admin.quizzes.questions', $quiz)
                ->with('success', "تم استيراد {$summary['count']} سؤال ({$typeLabel}) وربط {$summary['attached_count']} بالاختبار.")
                ->with('import_summary', [
                    'success' => $summary['count'],
                    'errors' => 0,
                    'total' => $summary['count'],
                ]);
        } catch (ValidationException $e) {
            return redirect()
                ->route('admin.quizzes.import-excel.show', $quiz)
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error importing question pack for quiz: '.$e->getMessage(), ['quiz_id' => $quiz->id]);

            return redirect()
                ->route('admin.quizzes.import-excel.show', $quiz)
                ->with('error', 'حدث خطأ أثناء الاستيراد: '.$e->getMessage());
        }
    }

    /**
     * عرض تفاصيل اختبار
     */
    public function show(string $id)
    {
        $quiz = Quiz::with([
            'subject.schoolClass',
            'unit',
            'lesson',
            'section',
            'creator',
            'reviewer',
            'questions.options',
            'attempts' => function ($q) {
                $q->latest()->limit(10);
            },
            'attempts.user',
        ])->withCount(['questions', 'attempts'])->findOrFail($id);

        // إحصائيات
        $stats = [
            'total_attempts' => $quiz->attempts_count,
            'passed_count' => $quiz->attempts()->passed()->count(),
            'failed_count' => $quiz->attempts()->failed()->count(),
            'average_score' => $quiz->attempts()->completed()->avg('percentage') ?? 0,
            'highest_score' => $quiz->attempts()->completed()->max('percentage') ?? 0,
            'lowest_score' => $quiz->attempts()->completed()->min('percentage') ?? 0,
        ];

        return view('admin.pages.quizzes.show', compact('quiz', 'stats'));
    }

    /**
     * عرض صفحة تعديل اختبار
     */
    public function edit(string $id)
    {
        $quiz = Quiz::with(['subject.schoolClass', 'copiedFromQuiz'])->findOrFail($id);

        $this->assertTeacherCanAccessQuiz($quiz);

        $stages = Stage::ordered()->get();
        $selectedStageId = $quiz->subject?->schoolClass?->stage_id;
        $selectedClassId = $quiz->subject?->schoolClass?->id;
        $selectedSubjectId = $quiz->subject_id;
        $selectedUnitId = $quiz->unit_id;
        $selectedSectionId = $quiz->section_id;
        $selectedLessonId = $quiz->lesson_id;
        $needsRelink = $quiz->needsRelink() || request()->boolean('relink');
        $originalWasLessonQuiz = $quiz->copiedFromQuiz?->lesson_id !== null;

        $units = $quiz->subject_id
            ? Unit::whereHas('section', function ($q) use ($quiz) {
                $q->where('subject_id', $quiz->subject_id);
            })->orderBy('title')->get()
            : collect();

        return view('admin.pages.quizzes.edit', compact(
            'quiz',
            'stages',
            'units',
            'selectedStageId',
            'selectedClassId',
            'selectedSubjectId',
            'selectedUnitId',
            'selectedSectionId',
            'selectedLessonId',
            'needsRelink',
            'originalWasLessonQuiz',
        ));
    }

    /**
     * تحديث اختبار
     */
    public function update(UpdateQuizRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $quiz = Quiz::findOrFail($id);
            $quizBeforeUpdate = clone $quiz;
            $data = $request->validated();
            $this->syncQuizSubjectFromUnit($data);

            // منطق المراجعة
            $user = auth()->user();
            $submitsForReview = $user->shouldSubmitQuizForReview();
            $oldReviewStatus = $quiz->review_status;

            if ($submitsForReview) {
                $this->applyTeacherQuizReviewOnUpdate($data, $request, $quiz);
            } elseif ($user->isQuizContentUploader() && SystemSetting::quizMandatoryReviewEnabled()) {
                $this->applyTeacherQuizReviewOnUpdate($data, $request, $quiz);
                $submitsForReview = true;
            } else {
                // المشرف والمدير: خيار التفعيل الواحد يحدد is_published أيضاً
                $data['is_published'] = $request->has('is_active');
                if ($data['is_published'] && $quiz->review_status !== Quiz::REVIEW_STATUS_APPROVED) {
                    $data['review_status'] = Quiz::REVIEW_STATUS_APPROVED;
                } elseif (! $data['is_published']) {
                    $data['review_status'] = Quiz::REVIEW_STATUS_DRAFT;
                }
            }

            // ربط المنهج من نموذج التعديل (مادة / قسم / وحدة / درس)
            $this->applyQuizPlacementFromRequest($data, $request);
            if ($quiz->needsRelink()) {
                $data['copied_from_quiz_id'] = null;
            }

            $placementAdjusted = $this->reconcileQuizLessonPlacement($data);

            if (! empty($data['unit_id']) && ! empty($data['section_id'])) {
                $unitSectionId = Unit::where('id', $data['unit_id'])->value('section_id');
                if ((int) $unitSectionId !== (int) $data['section_id']) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', 'القسم المحدد لا يطابق الوحدة المحددة.');
                }
            }

            // رفع صورة جديدة
            if ($request->hasFile('image')) {
                if ($quiz->image) {
                    MediaStorageService::delete($quiz->image);
                }
                $uploadResult = MediaStorageService::uploadImage($request->file('image'), 'quizzes');
                $data['image'] = $uploadResult['path'];
            } elseif ($request->boolean('remove_image')) {
                if ($quiz->image) {
                    MediaStorageService::delete($quiz->image);
                }
                $data['image'] = null;
            }

            // إزالة كلمة المرور إذا لم يعد مطلوباً
            if (! $data['requires_password']) {
                $data['password'] = null;
            }

            $quiz->update($data);
            $quiz->refresh();

            if (
                $quiz->review_status === Quiz::REVIEW_STATUS_PENDING
                && $oldReviewStatus !== Quiz::REVIEW_STATUS_PENDING
                && $submitsForReview
            ) {
                $this->dispatchNotificationSafely(function () use ($quiz, $user) {
                    $this->staffNotificationService->notifyQuizSubmittedForReview($quiz->fresh(), $user);
                }, 'quiz_review_submitted', (int) $quiz->id);
            }

            DB::commit();

            app(StudentContentNotificationService::class)->notifyIfQuizBecameVisible(
                $quizBeforeUpdate,
                $quiz->fresh(),
                auth()->user()
            );

            $submittedForReview = $quiz->review_status === Quiz::REVIEW_STATUS_PENDING
                && $submitsForReview
                && $oldReviewStatus !== Quiz::REVIEW_STATUS_PENDING;
            $successMessage = $submittedForReview
                ? 'تم تحديث الاختبار وإرساله للمراجعة.'
                : 'تم تحديث الاختبار بنجاح';
            if ($placementAdjusted) {
                $successMessage .= ' تم تحويل الاختبار إلى «اختبار وحدة» لأن الدرس السابق لا ينتمي للمادة/الوحدة المحددة.';
            }

            return redirect()
                ->route('admin.quizzes.show', $quiz->id)
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating quiz: '.$e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث الاختبار: '.$e->getMessage());
        }
    }

    /**
     * إرجاع الوحدات المرتبطة بالاختبار (للمودال).
     */
    public function getLinkedUnits(Quiz $quiz)
    {
        $linkedUnitIds = \Illuminate\Support\Facades\DB::table('quiz_units')
            ->where('quiz_id', $quiz->id)
            ->pluck('unit_id');
        $units = \App\Models\Unit::with('section.subject.schoolClass.stage')
            ->whereIn('id', $linkedUnitIds)
            ->get();
        $data = $units->map(function ($u) {
            return [
                'id' => $u->id,
                'title' => $u->title ?? '',
                'section_title' => optional($u->section)->title ?? '',
                'subject_name' => optional(optional($u->section)->subject)->name ?? '',
                'class_name' => optional(optional(optional($u->section)->subject)->schoolClass)->name ?? '',
                'stage_name' => optional(optional(optional(optional($u->section)->subject)->schoolClass)->stage)->name ?? '',
            ];
        })->values();

        return response()->json($data);
    }

    /**
     * ربط الاختبار بوحدات إضافية (ظهوره في وحدات أخرى).
     */
    public function linkUnits(Request $request, Quiz $quiz)
    {
        $request->validate([
            'linked_unit_ids' => ['nullable', 'array'],
            'linked_unit_ids.*' => ['integer', 'exists:units,id'],
        ]);

        $linkedUnitIds = $request->input('linked_unit_ids', []);
        $primaryUnitId = $quiz->unit_id;
        $linkedUnitIds = array_values(array_unique(array_filter($linkedUnitIds)));

        // دمج مع الوحدات المرتبطة حالياً حتى لا يُستبدل الربط السابق إذا وصلت وحدة جديدة فقط
        $existingLinkedIds = \Illuminate\Support\Facades\DB::table('quiz_units')
            ->where('quiz_id', $quiz->id)
            ->pluck('unit_id')
            ->toArray();
        $linkedUnitIds = array_values(array_unique(array_merge($existingLinkedIds, $linkedUnitIds)));
        $linkedUnitIds = array_values(array_diff($linkedUnitIds, [$primaryUnitId]));

        $quiz->linkedUnits()->sync($linkedUnitIds);

        $linkedUnits = $quiz->linkedUnits()->with('section.subject.schoolClass.stage')->get();
        $count = $linkedUnits->count();
        $labels = $linkedUnits->map(function ($u) {
            return trim(collect([
                data_get($u, 'section.subject.schoolClass.stage.name'),
                data_get($u, 'section.subject.schoolClass.name'),
                data_get($u, 'section.subject.name'),
                data_get($u, 'section.title'),
                $u->title,
            ])->filter()->implode(' — '));
        })->filter()->values()->toArray();

        $message = 'تم تحديث ربط الاختبار بالوحدات بنجاح.';
        if ($count > 0) {
            $message .= ' الاختبار مربوط بـ '.$count.' وحدة';
            if (! empty($labels)) {
                $message .= ': '.implode('، ', array_slice($labels, 0, 5));
                if (count($labels) > 5) {
                    $message .= '...';
                }
            } else {
                $message .= '.';
            }
        } else {
            $message .= ' لا يوجد ربط لوحدات إضافية حالياً.';
        }

        return redirect()
            ->back()
            ->with('success', $message);
    }

    /**
     * حذف اختبار
     */
    public function destroy(string $id)
    {
        try {
            $quiz = Quiz::findOrFail($id);

            $this->assertTeacherCanAccessQuiz($quiz);

            $attemptsCount = $quiz->attempts()->count();

            DB::transaction(function () use ($quiz) {
                foreach ($quiz->attempts()->get() as $attempt) {
                    $attempt->answers()->delete();
                    $attempt->delete();
                }

                if ($quiz->image) {
                    StorageHelper::delete('images', $quiz->image);
                }

                $quiz->delete();
            });

            $message = 'تم حذف الاختبار بنجاح';
            if ($attemptsCount > 0) {
                $message .= " (تم حذف {$attemptsCount} محاولة مرتبطة)";
            }

            return redirect()
                ->route('admin.quizzes.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Error deleting quiz: '.$e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء حذف الاختبار');
        }
    }

    /**
     * صفحة إدارة أسئلة الاختبار
     */
    public function questions(string $id, Request $request)
    {
        $quiz = Quiz::with(['questions.options', 'subject.schoolClass.stage'])->findOrFail($id);

        $quizSubject = $quiz->subject;
        $filterLocked = (bool) $quiz->subject_id;

        $classes = SchoolClass::with('stage')->ordered()->get();
        $subjects = Subject::with('schoolClass')->active()->ordered()->get();

        if (! $filterLocked && $request->filled('class_id')) {
            $subjects = $subjects->where('class_id', $request->input('class_id'));
        }

        $availableQuestions = Question::with(['units', 'options', 'subject'])
            ->active()
            ->whereNotIn('id', $quiz->questions->pluck('id'))
            ->when($quiz->subject_id, function ($q) use ($quiz) {
                $q->forSubject($quiz->subject_id);
            })
            ->when(! $filterLocked && $request->filled('class_id'), function ($q) use ($request) {
                $classId = (int) $request->class_id;
                $q->where(function ($query) use ($classId) {
                    $query->whereHas('subject', fn ($sq) => $sq->where('class_id', $classId))
                        ->orWhereHas('units.section.subject', fn ($sq) => $sq->where('class_id', $classId));
                });
            })
            ->when(! $filterLocked && $request->filled('subject_id'), function ($q) use ($request) {
                $q->forSubject((int) $request->subject_id);
            })
            ->when($request->filled('type'), function ($q) use ($request) {
                $q->ofType($request->type);
            })
            ->when($request->filled('difficulty'), function ($q) use ($request) {
                $q->ofDifficulty($request->difficulty);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->search($request->search);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.pages.quizzes.partials.available-questions-list', compact('availableQuestions', 'quiz'));
        }

        return view('admin.pages.quizzes.questions', compact(
            'quiz',
            'availableQuestions',
            'classes',
            'subjects',
            'quizSubject',
            'filterLocked'
        ));
    }

    /**
     * إضافة سؤال للاختبار
     */
    public function addQuestion(Request $request, string $id)
    {
        try {
            $quiz = Quiz::findOrFail($id);
            $question = Question::with(['options', 'units'])->findOrFail($request->question_id);

            if ($quiz->subject_id) {
                $belongsToSubject = Question::forSubject($quiz->subject_id)
                    ->whereKey($question->id)
                    ->exists();

                if (! $belongsToSubject) {
                    $message = 'السؤال لا ينتمي لمادة هذا الاختبار';

                    if ($request->expectsJson() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => $message,
                        ], 422);
                    }

                    return redirect()->back()->with('error', $message);
                }
            }

            // التحقق من عدم وجود السؤال مسبقاً
            if ($quiz->questions()->where('question_id', $question->id)->exists()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'السؤال موجود مسبقاً في الاختبار',
                    ], 400);
                }

                return redirect()->back()->with('error', 'السؤال موجود مسبقاً في الاختبار');
            }

            $maxOrder = $quiz->quizQuestions()->max('order') ?? 0;

            $quizQuestion = QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question_id' => $question->id,
                'order' => $maxOrder + 1,
                'points' => $request->points ?? $question->default_points,
                'is_required' => $request->has('is_required') ?? true,
            ]);

            $quiz->calculateTotalPoints();
            $quiz->refresh();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم إضافة السؤال للاختبار بنجاح',
                    'question' => array_merge($question->toQuizListPayload(80), [
                        'points' => $quizQuestion->points,
                        'order' => $quizQuestion->order,
                    ]),
                    'statistics' => [
                        'total_questions' => $quiz->questions->count(),
                        'total_points' => $quiz->total_points,
                    ],
                ]);
            }

            return redirect()->back()->with('success', 'تم إضافة السؤال للاختبار بنجاح');

        } catch (\Exception $e) {
            Log::error('Error adding question to quiz: '.$e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء إضافة السؤال: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'حدث خطأ أثناء إضافة السؤال');
        }
    }

    /**
     * ربط مجموعة من الأسئلة بالاختبار
     */
    public static function attachQuestionsToQuiz(int $quizId, array $questionIds): int
    {
        try {
            $quiz = Quiz::findOrFail($quizId);
            $attachedCount = 0;

            // الحصول على آخر order
            $maxOrder = $quiz->quizQuestions()->max('order') ?? 0;

            foreach ($questionIds as $questionId) {
                // التحقق من وجود السؤال
                $question = Question::find($questionId);
                if (! $question) {
                    continue;
                }

                // التحقق من عدم وجود السؤال مسبقاً في الاختبار
                if ($quiz->quizQuestions()->where('question_id', $questionId)->exists()) {
                    continue;
                }

                // إضافة السؤال للاختبار
                QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question_id' => $questionId,
                    'order' => ++$maxOrder,
                    'points' => $question->default_points,
                    'is_required' => true,
                ]);

                $attachedCount++;
            }

            // إعادة حساب إجمالي النقاط
            if ($attachedCount > 0) {
                $quiz->calculateTotalPoints();
            }

            return $attachedCount;
        } catch (\Exception $e) {
            Log::error('Error attaching questions to quiz: '.$e->getMessage(), [
                'quiz_id' => $quizId,
                'question_ids' => $questionIds,
            ]);
            throw $e;
        }
    }

    /**
     * إزالة سؤال من الاختبار
     */
    public function removeQuestion(Request $request, string $id, string $questionId)
    {
        try {
            $quiz = Quiz::findOrFail($id);
            $question = Question::findOrFail($questionId);

            // الحصول على معلومات السؤال قبل الحذف
            $quizQuestion = QuizQuestion::where('quiz_id', $quiz->id)
                ->where('question_id', $questionId)
                ->first();

            $points = $quizQuestion ? $quizQuestion->points : 0;

            $quiz->questions()->detach($questionId);
            $quiz->calculateTotalPoints();
            $quiz->refresh();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم إزالة السؤال من الاختبار',
                    'question' => $question->toQuizListPayload(60),
                    'statistics' => [
                        'total_questions' => $quiz->questions->count(),
                        'total_points' => $quiz->total_points,
                    ],
                ]);
            }

            return redirect()->back()->with('success', 'تم إزالة السؤال من الاختبار');

        } catch (\Exception $e) {
            Log::error('Error removing question from quiz: '.$e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء إزالة السؤال: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'حدث خطأ أثناء إزالة السؤال');
        }
    }

    /**
     * تحديث ترتيب الأسئلة
     */
    public function reorderQuestions(Request $request, string $id)
    {
        try {
            $quiz = Quiz::findOrFail($id);
            $order = $request->order;

            foreach ($order as $index => $questionId) {
                QuizQuestion::where('quiz_id', $quiz->id)
                    ->where('question_id', $questionId)
                    ->update(['order' => $index + 1]);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Error reordering questions: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * تحديث درجة سؤال في الاختبار
     */
    public function updateQuestionPoints(Request $request, string $id, string $questionId)
    {
        try {
            $request->validate([
                'points' => ['required', 'numeric', 'min:0', 'max:1000'],
            ]);

            $quiz = Quiz::findOrFail($id);

            QuizQuestion::where('quiz_id', $quiz->id)
                ->where('question_id', $questionId)
                ->update(['points' => $request->points]);

            $quiz->calculateTotalPoints();
            $quiz->refresh();

            // إرجاع JSON response إذا كان الطلب Ajax
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تحديث درجة السؤال بنجاح',
                    'points' => (float) $request->points,
                    'total_points' => (float) $quiz->total_points,
                ]);
            }

            // إرجاع redirect للطلبات العادية
            return redirect()->back()->with('success', 'تم تحديث درجة السؤال');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'البيانات المدخلة غير صحيحة',
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating question points: '.$e->getMessage());

            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء تحديث الدرجة: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'حدث خطأ أثناء تحديث الدرجة');
        }
    }

    /**
     * AJAX endpoint للحصول على الصفوف حسب المرحلة
     */
    public function getClassesByStage(Request $request)
    {
        $request->validate([
            'stage_id' => ['nullable', 'integer', 'exists:stages,id'],
        ]);

        $query = SchoolClass::query()->active()->ordered();

        if ($request->filled('stage_id')) {
            $query->where('stage_id', $request->input('stage_id'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(['id', 'name', 'stage_id']),
        ]);
    }

    /**
     * AJAX endpoint للحصول على المواد حسب الصف أو المرحلة
     */
    public function getSubjectsByClass(Request $request)
    {
        $request->validate([
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'stage_id' => ['nullable', 'integer', 'exists:stages,id'],
        ]);

        $query = Subject::with('schoolClass.stage')->active()->ordered();

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        } elseif ($request->filled('stage_id')) {
            $query->whereHas('schoolClass', function ($q) use ($request) {
                $q->where('stage_id', $request->input('stage_id'));
            });
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    /**
     * نسخ اختبار
     */
    public function duplicate(string $id)
    {
        try {
            DB::beginTransaction();

            $original = Quiz::with(['questions', 'quizQuestions'])->findOrFail($id);

            $newQuiz = $original->replicate();
            $newQuiz->title = $original->title.' (نسخة)';
            $newQuiz->subject_id = null;
            $newQuiz->unit_id = null;
            $newQuiz->section_id = null;
            $newQuiz->lesson_id = null;
            $newQuiz->scope = 'unit';
            $newQuiz->copied_from_quiz_id = $original->id;
            $newQuiz->is_published = false;
            $newQuiz->is_active = false;
            $newQuiz->review_status = Quiz::REVIEW_STATUS_DRAFT;
            $newQuiz->created_by = auth()->id();
            $newQuiz->save();

            // نسخ الأسئلة
            foreach ($original->quizQuestions as $quizQuestion) {
                QuizQuestion::create([
                    'quiz_id' => $newQuiz->id,
                    'question_id' => $quizQuestion->question_id,
                    'order' => $quizQuestion->order,
                    'points' => $quizQuestion->points,
                    'is_required' => $quizQuestion->is_required,
                    'shuffle_options' => $quizQuestion->shuffle_options,
                ]);
            }

            $newQuiz->calculateTotalPoints();

            DB::commit();

            return redirect()
                ->route('admin.quizzes.edit', ['quiz' => $newQuiz->id, 'relink' => 1])
                ->with('success', 'تم نسخ الاختبار. اختر مكان الربط ثم احفظ التعديلات.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error duplicating quiz: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء نسخ الاختبار');
        }
    }

    /**
     * تبديل حالة النشر
     */
    public function togglePublish(string $id)
    {
        try {
            $quiz = Quiz::findOrFail($id);
            $quizBeforeToggle = clone $quiz;
            $user = auth()->user();
            $submitsForReview = $user->shouldSubmitQuizForReview();

            // إذا كان المستخدم معلماً يُرسل للمراجعة، لا يمكنه النشر مباشرة
            if ($submitsForReview) {
                return redirect()->back()->with('error', 'يجب إرسال الاختبار للمراجعة أولاً. لا يمكنك النشر مباشرة.');
            }

            // التحقق من وجود أسئلة قبل النشر
            if (! $quiz->is_published && $quiz->questions()->count() === 0) {
                return redirect()->back()->with('error', 'لا يمكن نشر اختبار بدون أسئلة');
            }

            $quiz->is_published = ! $quiz->is_published;
            if ($quiz->is_published) {
                $quiz->review_status = Quiz::REVIEW_STATUS_APPROVED;
            }
            $quiz->save();

            app(StudentContentNotificationService::class)->notifyIfQuizBecameVisible(
                $quizBeforeToggle,
                $quiz->fresh(),
                $user
            );

            $status = $quiz->is_published ? 'نشر' : 'إلغاء نشر';

            return redirect()->back()->with('success', "تم {$status} الاختبار بنجاح");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحديث حالة الاختبار');
        }
    }

    /**
     * معاينة الاختبار
     */
    public function preview(string $id)
    {
        $quiz = Quiz::with(['questions.options', 'subject'])->findOrFail($id);

        return view('admin.pages.quizzes.preview', compact('quiz'));
    }

    /**
     * عرض نتائج الاختبار
     */
    public function results(string $id)
    {
        $quiz = Quiz::with(['subject'])->findOrFail($id);

        $attempts = $quiz->attempts()
            ->with(['user'])
            ->completed()
            ->latest('finished_at')
            ->paginate(20);

        return view('admin.pages.quizzes.results', compact('quiz', 'attempts'));
    }

    /**
     * تصدير نتائج الاختبار
     */
    public function exportResults(string $id)
    {
        // يمكن إضافة وظيفة التصدير لاحقاً
        return redirect()->back()->with('info', 'ميزة التصدير قيد التطوير');
    }

    /**
     * الحصول على الوحدات حسب المادة (AJAX)
     */
    public function getUnits(Request $request)
    {
        $request->validate([
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'section_id' => ['nullable', 'integer', 'exists:subject_sections,id'],
        ]);

        $query = Unit::query()
            ->with('section:id,title')
            ->whereHas('section', function ($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            });

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        $units = $query->orderBy('title')->get(['id', 'title', 'section_id']);

        return response()->json($units->map(function (Unit $unit) {
            $sectionTitle = $unit->section?->title;
            $label = $sectionTitle ? $sectionTitle.' — '.$unit->title : $unit->title;

            return [
                'id' => $unit->id,
                'title' => $unit->title,
                'section_id' => $unit->section_id,
                'section_title' => $sectionTitle,
                'label' => $label,
            ];
        })->values());
    }

    /**
     * الحصول على الأقسام حسب المادة (AJAX)
     */
    public function getSectionsBySubject(Request $request)
    {
        $request->validate([
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
        ]);

        $sections = SubjectSection::query()
            ->where('subject_id', $request->subject_id)
            ->orderBy('order')
            ->orderBy('title')
            ->get(['id', 'title', 'parent_id']);

        return response()->json($sections->map(fn (SubjectSection $section) => [
            'id' => $section->id,
            'title' => $section->title,
            'path_title' => $section->path_title,
        ])->values());
    }

    /**
     * الحصول على دروس الوحدة (AJAX)
     */
    public function getLessonsByUnit(Request $request)
    {
        $request->validate([
            'unit_id' => ['required', 'integer', 'exists:units,id'],
        ]);

        $lessons = Lesson::query()
            ->where('unit_id', $request->unit_id)
            ->orderBy('order')
            ->orderBy('title')
            ->get(['id', 'title']);

        return response()->json($lessons);
    }

    /**
     * إرسال الاختبار للمراجعة
     */
    public function submitForReview(string $id)
    {
        try {
            $quiz = Quiz::findOrFail($id);
            $user = auth()->user();

            // التحقق من أن المستخدم يملك صلاحية الإرسال للمراجعة
            if (! $user->shouldSubmitQuizForReview()) {
                abort(403, 'غير مصرح لك بإرسال الاختبار للمراجعة');
            }

            // التحقق من وجود أسئلة
            if ($quiz->questions()->count() === 0) {
                return redirect()->back()->with('error', 'لا يمكن إرسال اختبار بدون أسئلة للمراجعة');
            }

            $this->assertTeacherCanAccessQuiz($quiz);

            $quiz->update([
                'review_status' => Quiz::REVIEW_STATUS_PENDING,
                'submitted_for_review_at' => now(),
                'review_notes' => null, // مسح الملاحظات القديمة
                'is_published' => false,
            ]);

            $this->staffNotificationService->notifyQuizSubmittedForReview($quiz->fresh(), $user);

            return redirect()->back()->with('success', 'تم إرسال الاختبار للمراجعة بنجاح. سيتم مراجعته من قبل المشرف/الأدمن.');

        } catch (\Exception $e) {
            Log::error('Error submitting quiz for review: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء إرسال الاختبار للمراجعة');
        }
    }

    /**
     * الموافقة على نشر الاختبار
     */
    public function approveReview(Request $request, string $id)
    {
        $request->validate([
            'review_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $quiz = Quiz::findOrFail($id);
            $quizBeforeApprove = clone $quiz;

            // التحقق من الصلاحية (admin/supervisor فقط)
            $user = auth()->user();
            if (! $user->canReviewContent()) {
                abort(403, 'غير مصرح لك بالموافقة على نشر الاختبار');
            }

            $this->assertSupervisorCanReviewQuiz($quiz);

            if ($quiz->review_status !== Quiz::REVIEW_STATUS_PENDING) {
                return redirect()->back()->with('error', 'لا يمكن الموافقة على اختبار ليس قيد المراجعة.');
            }

            // التحقق من وجود أسئلة
            if ($quiz->questions()->count() === 0) {
                return redirect()->back()->with('error', 'لا يمكن الموافقة على نشر اختبار بدون أسئلة');
            }

            $quiz->update([
                'review_status' => Quiz::REVIEW_STATUS_APPROVED,
                'is_published' => true,
                'is_active' => true,
                'review_notes' => $request->input('review_notes'),
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            $this->staffNotificationService->notifyQuizReviewOutcome($quiz->fresh(), $user, true);

            app(StudentContentNotificationService::class)->notifyIfQuizBecameVisible(
                $quizBeforeApprove,
                $quiz->fresh(),
                $user
            );

            return redirect()
                ->back()
                ->with('success', 'تم الموافقة على نشر الاختبار بنجاح.');

        } catch (\Exception $e) {
            Log::error('Error approving quiz review: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء الموافقة على نشر الاختبار');
        }
    }

    /**
     * رفض نشر الاختبار مع ملاحظات
     */
    public function rejectReview(Request $request, string $id)
    {
        $request->validate([
            'review_notes' => 'required|string|max:1000',
        ]);

        try {
            $quiz = Quiz::findOrFail($id);

            // التحقق من الصلاحية (admin/supervisor فقط)
            $user = auth()->user();
            if (! $user->canReviewContent()) {
                abort(403, 'غير مصرح لك برفض نشر الاختبار');
            }

            $this->assertSupervisorCanReviewQuiz($quiz);

            if ($quiz->review_status !== Quiz::REVIEW_STATUS_PENDING) {
                return redirect()->back()->with('error', 'لا يمكن رفض اختبار ليس قيد المراجعة.');
            }

            $quiz->update([
                'review_status' => Quiz::REVIEW_STATUS_REJECTED,
                'is_published' => false,
                'review_notes' => $request->input('review_notes'),
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            $this->staffNotificationService->notifyQuizReviewOutcome($quiz->fresh(), $user, false);

            return redirect()
                ->back()
                ->with('success', 'تم رفض نشر الاختبار وتم إرسال الملاحظات للمعلم.');

        } catch (\Exception $e) {
            Log::error('Error rejecting quiz review: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء رفض نشر الاختبار');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function syncQuizSubjectFromUnit(array &$data): void
    {
        if (empty($data['subject_id']) && ! empty($data['unit_id'])) {
            $data['subject_id'] = Unit::query()
                ->whereKey($data['unit_id'])
                ->join('subject_sections', 'subject_sections.id', '=', 'units.section_id')
                ->value('subject_sections.subject_id');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function applyQuizPlacementFromRequest(array &$data, Request $request): void
    {
        if (! $request->filled('subject_id') || ! $request->filled('unit_id')) {
            throw ValidationException::withMessages([
                'unit_id' => 'اختر المادة والقسم والوحدة لربط الاختبار.',
            ]);
        }

        $scope = $request->input('scope', 'unit');
        if (! in_array($scope, ['unit', 'lesson'], true)) {
            throw ValidationException::withMessages([
                'scope' => 'اختر نوع الاختبار: وحدة أو درس.',
            ]);
        }

        $data['scope'] = $scope;
        $data['lesson_id'] = $scope === 'lesson' ? $request->input('lesson_id') : null;

        if ($scope === 'lesson' && empty($data['lesson_id'])) {
            throw ValidationException::withMessages([
                'lesson_id' => 'اختر الدرس المرتبط بالاختبار.',
            ]);
        }

        if ($scope === 'lesson' && ! empty($data['lesson_id'])) {
            $lessonBelongs = Lesson::query()
                ->whereKey($data['lesson_id'])
                ->where('unit_id', $data['unit_id'])
                ->exists();

            if (! $lessonBelongs) {
                throw ValidationException::withMessages([
                    'lesson_id' => 'الدرس المحدد لا ينتمي للوحدة المختارة.',
                ]);
            }
        }

        $this->syncQuizSectionFromPlacement($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function syncQuizSectionFromPlacement(array &$data): void
    {
        if (! empty($data['unit_id']) && empty($data['section_id'])) {
            $data['section_id'] = Unit::where('id', $data['unit_id'])->value('section_id');
        }

        if (! empty($data['lesson_id']) && empty($data['section_id'])) {
            $lessonSectionId = Lesson::where('id', $data['lesson_id'])->value('section_id');
            if (! $lessonSectionId) {
                $lessonSectionId = Lesson::query()
                    ->where('id', $data['lesson_id'])
                    ->join('units', 'units.id', '=', 'lessons.unit_id')
                    ->value('units.section_id');
            }
            $data['section_id'] = $lessonSectionId;
        }
    }

    /**
     * يضمن أن ربط الدرس متسق مع المادة/الوحدة المختارة — وإلا يُحوَّل لاختبار وحدة.
     *
     * @param  array<string, mixed>  $data
     */
    protected function reconcileQuizLessonPlacement(array &$data): bool
    {
        $hadLesson = ! empty($data['lesson_id']);

        if (empty($data['lesson_id'])) {
            if (($data['scope'] ?? null) === 'lesson') {
                $data['scope'] = 'unit';
            }

            return false;
        }

        $lesson = Lesson::query()
            ->with('unit.section')
            ->find($data['lesson_id']);

        if (! $lesson) {
            $data['lesson_id'] = null;
            $data['scope'] = 'unit';

            return $hadLesson;
        }

        $unitId = $data['unit_id'] ?? null;
        if ($unitId && (int) $lesson->unit_id !== (int) $unitId) {
            $data['lesson_id'] = null;
            $data['scope'] = 'unit';

            return true;
        }

        $subjectId = $data['subject_id'] ?? null;
        if ($subjectId) {
            $lessonSubjectId = $lesson->unit?->section?->subject_id ?? $lesson->section?->subject_id;
            if ($lessonSubjectId && (int) $lessonSubjectId !== (int) $subjectId) {
                $data['lesson_id'] = null;
                $data['scope'] = 'unit';

                return true;
            }
        }

        $data['scope'] = 'lesson';

        return false;
    }

    protected function assertTeacherCanAccessQuiz(Quiz $quiz): void
    {
        $user = auth()->user();

        if (! $user->usesTeacherAssignmentScope() || ! $quiz->subject_id) {
            return;
        }

        $classId = $quiz->subject?->class_id;

        if (! $user->isAssignedToSubject($quiz->subject_id) &&
            (! $classId || ! $user->isAssignedToClass($classId))) {
            abort(403, 'غير مصرح لك بالوصول إلى هذا الاختبار');
        }
    }

    protected function assertSupervisorCanReviewQuiz(Quiz $quiz): void
    {
        $user = auth()->user();

        if ($user->isPlatformAdmin() || ! $user->usesSupervisorAssignmentScope()) {
            return;
        }

        if (! $quiz->subject_id) {
            abort(403, 'غير مصرح لك بمراجعة هذا الاختبار');
        }

        $allowed = Quiz::query()
            ->forSupervisor($user->id)
            ->where('id', $quiz->id)
            ->exists();

        if (! $allowed) {
            abort(403, 'غير مصرح لك بمراجعة هذا الاختبار');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function applyTeacherQuizReviewOnCreate(array &$data): void
    {
        if (SystemSetting::quizMandatoryReviewEnabled()) {
            $data['review_status'] = Quiz::REVIEW_STATUS_PENDING;
            $data['is_active'] = false;
            $data['is_published'] = false;
            $data['submitted_for_review_at'] = now();

            return;
        }

        if (request()->has('is_active')) {
            $data['review_status'] = Quiz::REVIEW_STATUS_PENDING;
            $data['submitted_for_review_at'] = now();
            $data['is_published'] = false;
            $data['is_active'] = false;
        } else {
            $data['review_status'] = Quiz::REVIEW_STATUS_DRAFT;
            $data['is_published'] = false;
            $data['is_active'] = false;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function applyTeacherQuizReviewOnUpdate(array &$data, Request $request, Quiz $quiz): void
    {
        if (SystemSetting::quizMandatoryReviewEnabled()) {
            $wasPending = $quiz->review_status === Quiz::REVIEW_STATUS_PENDING;
            $data['review_status'] = Quiz::REVIEW_STATUS_PENDING;
            $data['is_active'] = false;
            $data['is_published'] = false;
            $data['submitted_for_review_at'] = now();
            if (! $wasPending) {
                $data['review_notes'] = null;
            }

            return;
        }

        if ($request->has('is_active') && in_array($quiz->review_status, [Quiz::REVIEW_STATUS_PENDING, Quiz::REVIEW_STATUS_REJECTED], true)) {
            $data['review_status'] = Quiz::REVIEW_STATUS_PENDING;
            $data['submitted_for_review_at'] = now();
            $data['review_notes'] = null;
            $data['is_published'] = false;
            $data['is_active'] = false;
        } elseif ($request->has('is_active')) {
            $data['review_status'] = Quiz::REVIEW_STATUS_PENDING;
            $data['submitted_for_review_at'] = now();
            $data['is_published'] = false;
            $data['is_active'] = false;
        } else {
            $data['review_status'] = Quiz::REVIEW_STATUS_DRAFT;
            $data['is_published'] = false;
            $data['is_active'] = false;
        }
    }

    private function dispatchNotificationSafely(callable $callback, string $context, int $quizId): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            Log::error('Quiz notification dispatch failed: '.$e->getMessage(), [
                'context' => $context,
                'quiz_id' => $quizId,
            ]);
        }
    }
}
