<?php

namespace App\InteractiveLearning\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\InteractiveLearning\Models\LearningExperience;
use App\InteractiveLearning\Models\LearningExperienceAttempt;
use App\InteractiveLearning\Services\AiPatchService;
use App\InteractiveLearning\Services\AiSessionGenerationService;
use App\InteractiveLearning\Services\ExperienceQuestionImportException;
use App\InteractiveLearning\Services\ExperienceQuestionImportService;
use App\InteractiveLearning\Services\ExperienceSourceExtractionService;
use App\InteractiveLearning\Services\SchemaValidator;
use App\InteractiveLearning\Support\FeedbackPhrases;
use App\InteractiveLearning\Support\QuestionTypeRegistry;
use App\Models\AIModel;
use App\Models\Lesson;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\SystemSetting;
use App\Models\Unit;
use App\Services\StaffNotificationService;
use App\Services\StudentContentNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LearningExperienceController extends Controller
{
    public function __construct(
        protected SchemaValidator $schemaValidator,
        protected AiPatchService $aiPatchService,
        protected AiSessionGenerationService $aiSessionGenerationService,
        protected ExperienceQuestionImportService $experienceQuestionImportService,
        protected ExperienceSourceExtractionService $sourceExtractionService,
        protected StaffNotificationService $staffNotificationService
    ) {
        $this->middleware(['permission:learning-experience-approve-review'])->only('approveReview');
        $this->middleware(['permission:learning-experience-reject-review'])->only('rejectReview');
        $this->middleware(['permission:learning-experience-submit-for-review'])->only('submitForReview');
    }

    public function index(Request $request): View
    {
        $query = LearningExperience::query()
            ->with(['subject.schoolClass', 'unit'])
            ->withCount('attempts')
            ->latest();

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($search = trim($request->string('q')->toString())) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($subjectId = $request->integer('subject_id')) {
            $query->where('subject_id', $subjectId);
        }

        $stats = [
            'total' => LearningExperience::query()->count(),
            'published' => LearningExperience::query()->where('status', LearningExperience::STATUS_PUBLISHED)->count(),
            'draft' => LearningExperience::query()->where('status', LearningExperience::STATUS_DRAFT)->count(),
            'review' => LearningExperience::query()->where('status', LearningExperience::STATUS_REVIEW)->count(),
        ];

        return view('admin.pages.learning-experiences.index', [
            'experiences' => $query->paginate(20)->withQueryString(),
            'statuses' => LearningExperience::STATUSES,
            'stats' => $stats,
        ]);
    }

    public function create(Request $request): View
    {
        $context = $this->resolveCurriculumContext($request);

        return view('admin.pages.learning-experiences.create', array_merge($context, [
            'types' => QuestionTypeRegistry::all(),
            'stages' => Stage::ordered()->get(),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'experience_mode' => ['nullable', 'string', 'in:classic,dynamic'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
        ]);

        $mode = ($data['experience_mode'] ?? 'classic') === 'dynamic' ? 'dynamic' : 'classic';
        $schema = $this->schemaValidator->emptySchema($data['title'], $mode);

        if ($mode === 'dynamic') {
            $q = $this->schemaValidator->makeBlankDynamicQuestion('single_choice');
            $q['stem'] = 'كم عدد التفاحات؟';
            $q['stemBlocks'] = [
                ['type' => 'text', 'text' => 'كم عدد التفاحات الحمراء؟'],
                ['type' => 'scene', 'item' => 'apple', 'count' => 3, 'layout' => 'row'],
            ];
            $q['interaction']['payload']['options'] = [
                ['id' => 'a', 'label' => '2', 'icon' => '2️⃣'],
                ['id' => 'b', 'label' => '3', 'icon' => '3️⃣'],
                ['id' => 'c', 'label' => '5', 'icon' => '5️⃣'],
            ];
            $q['interaction']['payload']['correctId'] = 'b';
            $schema['questions'][] = $q;
        } else {
            $schema['questions'][] = $this->schemaValidator->makeBlankQuestion('true_false');
            $schema['questions'][0]['stem'] = 'مثال: هل هذه العبارة صحيحة؟';
        }

        $links = $this->normalizeCurriculumLinks(
            $data['subject_id'] ?? null,
            $data['unit_id'] ?? null,
            $data['lesson_id'] ?? null
        );

        $user = $request->user();
        $requiresReview = $user->shouldSubmitContentForReview();
        $mandatoryReview = SystemSetting::learningExperienceMandatoryReviewEnabled();

        $attributes = [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => LearningExperience::STATUS_DRAFT,
            'schema_json' => $schema,
            'schema_version' => $mode === 'dynamic'
                ? SchemaValidator::SCHEMA_VERSION_DYNAMIC
                : SchemaValidator::SCHEMA_VERSION,
            'engine_version' => SchemaValidator::ENGINE_VERSION,
            'created_by' => $user->id,
            'subject_id' => $links['subject_id'],
            'unit_id' => $links['unit_id'],
            'lesson_id' => $links['lesson_id'],
        ];

        $submitForReview = $requiresReview && ($mandatoryReview || $request->boolean('submit_for_review'));

        if ($submitForReview) {
            $attributes['status'] = LearningExperience::STATUS_REVIEW;
            $attributes['submitted_for_review_at'] = now();
        }

        $experience = LearningExperience::create($attributes);

        if ($submitForReview) {
            $this->dispatchNotificationSafely(
                fn () => $this->staffNotificationService->notifyLearningExperienceSubmittedForReview($experience->fresh(), $user),
                'learning_experience_submitted_for_review',
                $experience->id
            );

            return redirect()
                ->route('admin.learning-experiences.edit', $experience)
                ->with('success', 'تم إنشاء الاختبار التفاعلي وإرساله للمراجعة. لن يظهر للطلاب حتى تتم الموافقة.');
        }

        if ($requiresReview) {
            // معلم/مشرف بمراجعة اختيارية اختار عدم الإرسال الآن — يبقى مسودة.
            return redirect()
                ->route('admin.learning-experiences.edit', $experience)
                ->with('success', 'تم إنشاء الاختبار التفاعلي كمسودة. أكمل الأسئلة ثم أرسله للمراجعة.');
        }

        // أدمن كامل اختار عدم التفعيل — يبقى مسودة (مطابقة لسلوك الاختبار العادي عند إلغاء تحديد "تفعيل الاختبار").
        if (! $request->has('is_active')) {
            return redirect()
                ->route('admin.learning-experiences.edit', $experience)
                ->with('success', 'تم إنشاء الاختبار التفاعلي كمسودة. أكمل الأسئلة ثم انشر.');
        }

        // أدمن كامل: محاولة نشر فوري (مطابقة لسلوك الاختبار العادي).
        $validation = $this->schemaValidator->validate($schema);

        if ($validation['valid']) {
            $experienceBeforePublish = clone $experience;

            $experience->update(['status' => LearningExperience::STATUS_PUBLISHED]);

            $this->dispatchNotificationSafely(
                fn () => app(StudentContentNotificationService::class)->notifyIfLearningExperienceBecameVisible(
                    $experienceBeforePublish,
                    $experience->fresh(),
                    $user
                ),
                'learning_experience_became_visible',
                $experience->id
            );

            return redirect()
                ->route('admin.learning-experiences.edit', $experience)
                ->with('success', 'تم إنشاء الاختبار التفاعلي ونشره تلقائياً. يمكنك تعديل الأسئلة في أي وقت.');
        }

        return redirect()
            ->route('admin.learning-experiences.edit', $experience)
            ->with('success', 'تم إنشاء الاختبار التفاعلي. أكمل الأسئلة ثم انشر.');
    }

    /**
     * إرسال الاختبار التفاعلي للمراجعة.
     */
    public function submitForReview(LearningExperience $learningExperience): RedirectResponse
    {
        try {
            $user = auth()->user();

            if (! $user->shouldSubmitContentForReview()) {
                abort(403, 'غير مصرح لك بإرسال الاختبار التفاعلي للمراجعة');
            }

            if ($learningExperience->status === LearningExperience::STATUS_PUBLISHED) {
                return redirect()->back()->with('error', 'الاختبار التفاعلي منشور بالفعل.');
            }

            if ($learningExperience->questionsCount() === 0) {
                return redirect()->back()->with('error', 'لا يمكن إرسال اختبار تفاعلي بدون أسئلة للمراجعة');
            }

            $this->assertTeacherCanAccessExperience($learningExperience);

            $learningExperience->update([
                'status' => LearningExperience::STATUS_REVIEW,
                'submitted_for_review_at' => now(),
                'review_notes' => null,
            ]);

            $this->dispatchNotificationSafely(
                fn () => $this->staffNotificationService->notifyLearningExperienceSubmittedForReview($learningExperience->fresh(), $user),
                'learning_experience_submitted_for_review',
                $learningExperience->id
            );

            return redirect()->back()->with('success', 'تم إرسال الاختبار التفاعلي للمراجعة بنجاح. سيتم مراجعته من قبل المشرف/الأدمن.');
        } catch (\Exception $e) {
            Log::error('Error submitting learning experience for review: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء إرسال الاختبار التفاعلي للمراجعة');
        }
    }

    /**
     * الموافقة على نشر الاختبار التفاعلي.
     */
    public function approveReview(Request $request, LearningExperience $learningExperience): RedirectResponse
    {
        $request->validate([
            'review_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $experienceBeforeApprove = clone $learningExperience;

            $user = auth()->user();
            if (! $user->canReviewContent()) {
                abort(403, 'غير مصرح لك بالموافقة على نشر الاختبار التفاعلي');
            }

            $this->assertSupervisorCanReviewExperience($learningExperience);

            if ($learningExperience->status !== LearningExperience::STATUS_REVIEW) {
                return redirect()->back()->with('error', 'لا يمكن الموافقة على اختبار تفاعلي ليس قيد المراجعة.');
            }

            if ($learningExperience->questionsCount() === 0) {
                return redirect()->back()->with('error', 'لا يمكن الموافقة على نشر اختبار تفاعلي بدون أسئلة');
            }

            $validation = $this->schemaValidator->validate($learningExperience->schema_json ?? []);
            if (! $validation['valid']) {
                return redirect()->back()->with('error', 'لا يمكن نشر الاختبار التفاعلي قبل تصحيح أخطاء البنية: '.implode(' - ', $validation['errors']));
            }

            $learningExperience->update([
                'status' => LearningExperience::STATUS_PUBLISHED,
                'review_notes' => $request->input('review_notes'),
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            $this->dispatchNotificationSafely(
                fn () => $this->staffNotificationService->notifyLearningExperienceReviewOutcome($learningExperience->fresh(), $user, true),
                'learning_experience_review_outcome',
                $learningExperience->id
            );

            $this->dispatchNotificationSafely(
                fn () => app(StudentContentNotificationService::class)->notifyIfLearningExperienceBecameVisible(
                    $experienceBeforeApprove,
                    $learningExperience->fresh(),
                    $user
                ),
                'learning_experience_became_visible',
                $learningExperience->id
            );

            return redirect()
                ->route('admin.review-queue.index')
                ->with('success', 'تم الموافقة على نشر الاختبار التفاعلي بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error approving learning experience review: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء الموافقة على نشر الاختبار التفاعلي');
        }
    }

    /**
     * رفض نشر الاختبار التفاعلي مع ملاحظات.
     */
    public function rejectReview(Request $request, LearningExperience $learningExperience): RedirectResponse
    {
        $request->validate([
            'review_notes' => 'required|string|max:1000',
        ]);

        try {
            $user = auth()->user();
            if (! $user->canReviewContent()) {
                abort(403, 'غير مصرح لك برفض نشر الاختبار التفاعلي');
            }

            $this->assertSupervisorCanReviewExperience($learningExperience);

            if ($learningExperience->status !== LearningExperience::STATUS_REVIEW) {
                return redirect()->back()->with('error', 'لا يمكن رفض اختبار تفاعلي ليس قيد المراجعة.');
            }

            $learningExperience->update([
                'status' => LearningExperience::STATUS_DRAFT,
                'review_notes' => $request->input('review_notes'),
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            $this->dispatchNotificationSafely(
                fn () => $this->staffNotificationService->notifyLearningExperienceReviewOutcome($learningExperience->fresh(), $user, false),
                'learning_experience_review_outcome',
                $learningExperience->id
            );

            return redirect()
                ->route('admin.review-queue.index')
                ->with('success', 'تم رفض نشر الاختبار التفاعلي وتم إرسال الملاحظات للمعلم.');
        } catch (\Exception $e) {
            Log::error('Error rejecting learning experience review: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء رفض نشر الاختبار التفاعلي');
        }
    }

    protected function assertTeacherCanAccessExperience(LearningExperience $learningExperience): void
    {
        $user = auth()->user();

        if (! $user->usesTeacherAssignmentScope() || ! $learningExperience->subject_id) {
            return;
        }

        $classId = $learningExperience->subject?->class_id;

        if (! $user->isAssignedToSubject($learningExperience->subject_id) &&
            (! $classId || ! $user->isAssignedToClass($classId))) {
            abort(403, 'غير مصرح لك بالوصول إلى هذا الاختبار التفاعلي');
        }
    }

    protected function assertSupervisorCanReviewExperience(LearningExperience $learningExperience): void
    {
        $user = auth()->user();

        if ($user->isPlatformAdmin() || ! $user->usesSupervisorAssignmentScope()) {
            return;
        }

        if (! $learningExperience->subject_id) {
            abort(403, 'غير مصرح لك بمراجعة هذا الاختبار التفاعلي');
        }

        $allowed = LearningExperience::query()
            ->forSupervisor($user->id)
            ->where('id', $learningExperience->id)
            ->exists();

        if (! $allowed) {
            abort(403, 'غير مصرح لك بمراجعة هذا الاختبار التفاعلي');
        }
    }

    private function dispatchNotificationSafely(callable $callback, string $context, int $experienceId): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            Log::error('Learning experience notification dispatch failed: '.$e->getMessage(), [
                'context' => $context,
                'learning_experience_id' => $experienceId,
            ]);
        }
    }

    public function edit(LearningExperience $learningExperience): View
    {
        $learningExperience->loadMissing(['subject.schoolClass', 'unit', 'lesson']);

        $blankTemplates = [];
        foreach (QuestionTypeRegistry::types() as $type) {
            $blankTemplates[$type] = $this->schemaValidator->makeBlankQuestion($type);
        }

        $blankDynamicTemplates = [];
        foreach (QuestionTypeRegistry::types() as $type) {
            $blankDynamicTemplates[$type] = $this->schemaValidator->makeBlankDynamicQuestion($type);
        }

        $aiModels = AIModel::query()
            ->active()
            ->orderByDesc('is_default')
            ->orderByDesc('priority')
            ->get(['id', 'name', 'provider', 'is_default']);

        $lockedFromCurriculum = (bool) ($learningExperience->subject_id || $learningExperience->unit_id || $learningExperience->lesson_id);

        $recentAttempts = LearningExperienceAttempt::query()
            ->with('user:id,name,email')
            ->where('learning_experience_id', $learningExperience->id)
            ->latest('finished_at')
            ->latest('id')
            ->limit(20)
            ->get();

        $attemptsCount = LearningExperienceAttempt::query()
            ->where('learning_experience_id', $learningExperience->id)
            ->count();

        $attemptsAvg = (float) (LearningExperienceAttempt::query()
            ->where('learning_experience_id', $learningExperience->id)
            ->avg('percentage') ?? 0);

        return view('admin.pages.learning-experiences.edit', [
            'experience' => $learningExperience,
            'types' => QuestionTypeRegistry::all(),
            'typesKeyed' => QuestionTypeRegistry::keyed(),
            'blankTemplates' => $blankTemplates,
            'blankDynamicTemplates' => $blankDynamicTemplates,
            'dynamicInteractionTypes' => QuestionTypeRegistry::types(),
            'aiModels' => $aiModels,
            'stages' => Stage::ordered()->get(),
            'selectedStageId' => $learningExperience->subject?->schoolClass?->stage_id,
            'selectedClassId' => $learningExperience->subject?->schoolClass?->id,
            'selectedSubjectId' => old('subject_id', $learningExperience->subject_id),
            'selectedUnitId' => old('unit_id', $learningExperience->unit_id),
            'selectedLessonId' => old('lesson_id', $learningExperience->lesson_id),
            'selectedSubject' => $learningExperience->subject,
            'selectedUnit' => $learningExperience->unit,
            'selectedLesson' => $learningExperience->lesson,
            'selectedClass' => $learningExperience->subject?->schoolClass,
            'isFromSubjectOrUnit' => $lockedFromCurriculum,
            'isFromLesson' => (bool) $learningExperience->lesson_id,
            'recentAttempts' => $recentAttempts,
            'attemptsCount' => $attemptsCount,
            'attemptsAvg' => $attemptsAvg,
            'feedbackPhrases' => FeedbackPhrases::forPlayer(),
        ]);
    }

    public function update(Request $request, LearningExperience $learningExperience): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'schema_json' => ['required', 'string'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
            'passing_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'max_attempts' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $schema = json_decode($data['schema_json'], true);
        if (! is_array($schema)) {
            return back()->withInput()->withErrors(['schema_json' => 'JSON غير صالح.']);
        }

        $schema['meta']['title'] = $data['title'];
        // ضمان أن كل رسالة محفوظة لها تسجيل صوتي مطابق حتى لو تجاوز الطلب واجهة القوائم
        $schema = FeedbackPhrases::snapSchema($schema)['schema'];
        $result = $this->schemaValidator->validate($schema);
        if (! $result['valid']) {
            return back()->withInput()->withErrors(['schema_json' => implode(' ', $result['errors'])]);
        }

        $links = $this->normalizeCurriculumLinks(
            $data['subject_id'] ?? null,
            $data['unit_id'] ?? null,
            $data['lesson_id'] ?? null
        );

        $learningExperience->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'schema_json' => $schema,
            'schema_version' => $this->schemaValidator->resolveMode($schema) === 'dynamic'
                ? SchemaValidator::SCHEMA_VERSION_DYNAMIC
                : SchemaValidator::SCHEMA_VERSION,
            'engine_version' => SchemaValidator::ENGINE_VERSION,
            'subject_id' => $links['subject_id'],
            'unit_id' => $links['unit_id'],
            'lesson_id' => $links['lesson_id'],
            'passing_score' => isset($data['passing_score']) ? (float) $data['passing_score'] : 50,
            'max_attempts' => isset($data['max_attempts']) ? (int) $data['max_attempts'] : 0,
        ]);

        // التجربة بلا مادة لا يمكن أن تظهر لأي طالب — نُنبّه بدل الصمت
        if (empty($links['subject_id'])) {
            return back()->with('warning', 'تم الحفظ، لكن التجربة غير مرتبطة بمادة فلن تظهر لأي طالب. اربطها بمادة ثم انشرها.');
        }

        return back()->with('success', 'تم حفظ الاختبار التفاعلي.');
    }

    public function destroy(LearningExperience $learningExperience): RedirectResponse
    {
        $learningExperience->delete();

        if (url()->previous() && url()->previous() !== url()->current()) {
            return redirect()
                ->back()
                ->with('success', 'تم حذف الاختبار التفاعلي.');
        }

        return redirect()
            ->route('admin.learning-experiences.index')
            ->with('success', 'تم حذف الاختبار التفاعلي.');
    }

    /**
     * @return array{
     *     selectedStageId: mixed,
     *     selectedClassId: mixed,
     *     selectedSubjectId: mixed,
     *     selectedUnitId: mixed,
     *     selectedLessonId: mixed,
     *     selectedSubject: ?Subject,
     *     selectedUnit: ?Unit,
     *     selectedLesson: ?Lesson,
     *     selectedClass: mixed,
     *     isFromSubjectOrUnit: bool,
     *     isFromLesson: bool
     * }
     */
    protected function resolveCurriculumContext(Request $request): array
    {
        $selectedStageId = null;
        $selectedClassId = null;
        $selectedSubjectId = $request->get('subject_id');
        $selectedUnitId = $request->get('unit_id');
        $selectedLessonId = $request->get('lesson_id');

        $selectedSubject = null;
        $selectedUnit = null;
        $selectedLesson = null;
        $selectedClass = null;
        $isFromSubjectOrUnit = false;
        $isFromLesson = false;

        if ($selectedLessonId) {
            $selectedLesson = Lesson::with([
                'unit.section.subject.schoolClass',
                'section.subject.schoolClass',
            ])->find($selectedLessonId);
            if ($selectedLesson) {
                $selectedUnit = $selectedLesson->unit;
                $selectedSubject = $selectedUnit?->section?->subject
                    ?? $selectedLesson->section?->subject;
                $selectedClass = $selectedSubject?->schoolClass;
                $selectedSubjectId = $selectedSubject?->id;
                $selectedUnitId = $selectedUnit?->id ?? $selectedUnitId;
                $isFromSubjectOrUnit = true;
                $isFromLesson = true;
            }
        }

        if (! $selectedLesson && $selectedUnitId) {
            $selectedUnit = Unit::with('section.subject.schoolClass')->find($selectedUnitId);
            if ($selectedUnit) {
                $selectedSubject = $selectedUnit->section?->subject;
                $selectedClass = $selectedSubject?->schoolClass;
                $selectedSubjectId = $selectedSubject?->id ?? $selectedSubjectId;
                $isFromSubjectOrUnit = true;
            }
        }

        if (! $selectedSubject && $selectedSubjectId) {
            $selectedSubject = Subject::with('schoolClass')->find($selectedSubjectId);
            if ($selectedSubject) {
                $selectedClass = $selectedSubject->schoolClass;
                $isFromSubjectOrUnit = true;
            }
        }

        if ($selectedClass) {
            $selectedStageId = $selectedClass->stage_id;
            $selectedClassId = $selectedClass->id;
        }

        return [
            'selectedStageId' => $selectedStageId,
            'selectedClassId' => $selectedClassId,
            'selectedSubjectId' => old('subject_id', $selectedSubjectId),
            'selectedUnitId' => old('unit_id', $selectedUnitId),
            'selectedLessonId' => old('lesson_id', $selectedLessonId),
            'selectedSubject' => $selectedSubject,
            'selectedUnit' => $selectedUnit,
            'selectedLesson' => $selectedLesson,
            'selectedClass' => $selectedClass,
            'isFromSubjectOrUnit' => $isFromSubjectOrUnit,
            'isFromLesson' => $isFromLesson,
        ];
    }

    /**
     * @return array{subject_id: ?int, unit_id: ?int, lesson_id: ?int}
     */
    protected function normalizeCurriculumLinks(?int $subjectId, ?int $unitId, ?int $lessonId): array
    {
        if ($lessonId) {
            $lesson = Lesson::with(['unit.section.subject', 'section.subject'])->find($lessonId);
            if ($lesson) {
                $unit = $lesson->unit;
                $subject = $unit?->section?->subject ?? $lesson->section?->subject;

                return [
                    'subject_id' => $subject?->id ?? $subjectId,
                    'unit_id' => $unit?->id ?? $unitId,
                    'lesson_id' => $lesson->id,
                ];
            }
        }

        if (! $subjectId) {
            return [
                'subject_id' => null,
                'unit_id' => null,
                'lesson_id' => null,
            ];
        }

        if ($unitId) {
            $unit = Unit::with('section')->find($unitId);
            if ($unit && (int) optional($unit->section)->subject_id !== (int) $subjectId) {
                $unitId = null;
            }
        }

        return [
            'subject_id' => $subjectId,
            'unit_id' => $unitId,
            'lesson_id' => null,
        ];
    }

    public function transition(Request $request, LearningExperience $learningExperience): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', LearningExperience::STATUSES)],
        ]);

        if (! $learningExperience->canTransitionTo($data['status'])) {
            return back()->withErrors(['status' => 'انتقال الحالة غير مسموح.']);
        }

        if ($data['status'] === LearningExperience::STATUS_PUBLISHED) {
            $user = $request->user();

            // منشئ محتوى يخضع لمسار المراجعة لا يمكنه النشر مباشرة — يجب إرساله للمراجعة أولاً.
            if ($user->shouldSubmitContentForReview() && ! $user->canReviewContent()) {
                return back()->withErrors(['status' => 'لا يمكنك نشر الاختبار التفاعلي مباشرة. أرسله للمراجعة أولاً.']);
            }

            $result = $this->schemaValidator->validate($learningExperience->schema_json ?? []);
            if (! $result['valid']) {
                return back()->withErrors(['status' => 'لا يمكن النشر: '.implode(' ', $result['errors'])]);
            }
        }

        $learningExperience->update(['status' => $data['status']]);

        return back()->with('success', 'تم تحديث الحالة.');
    }

    public function addQuestion(Request $request, LearningExperience $learningExperience): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'in:'.implode(',', QuestionTypeRegistry::types())],
        ]);

        $schema = $learningExperience->schema_json ?? $this->schemaValidator->emptySchema($learningExperience->title);
        $mode = $this->schemaValidator->resolveMode($schema);
        if ($mode === 'dynamic') {
            $schema['questions'][] = $this->schemaValidator->makeBlankDynamicQuestion($data['type']);
        } else {
            $schema['questions'][] = $this->schemaValidator->makeBlankQuestion($data['type']);
        }
        $learningExperience->update(['schema_json' => $schema]);

        return back()->with('success', 'تمت إضافة سؤال جديد.');
    }

    public function aiPatch(Request $request, LearningExperience $learningExperience): JsonResponse
    {
        $data = $request->validate([
            'intent' => ['nullable', 'string', 'max:1000'],
            'schema_json' => ['nullable', 'array'],
        ]);

        $schema = $data['schema_json'] ?? $learningExperience->schema_json ?? [];
        if (! is_array($schema) || $schema === []) {
            return response()->json(['ok' => false, 'message' => 'لا يوجد Schema.'], 422);
        }

        try {
            $patch = $this->aiPatchService->proposePatch(
                $schema,
                $data['intent'] ?? 'حسّن صياغة الأسئلة والشرح والرسائل دون تغيير الإجابات الصحيحة'
            );

            return response()->json([
                'ok' => true,
                'summary' => $patch['summary'],
                'operations' => $patch['operations'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function aiApply(Request $request, LearningExperience $learningExperience): JsonResponse
    {
        $data = $request->validate([
            'operations' => ['required', 'array', 'min:1'],
            'schema_json' => ['nullable', 'array'],
            'persist' => ['nullable', 'boolean'],
        ]);

        $schema = $data['schema_json'] ?? $learningExperience->schema_json ?? [];
        $result = $this->aiPatchService->applyPatch($schema, $data['operations']);

        if (($data['persist'] ?? true) && $result['validation']['valid']) {
            $title = $result['schema']['meta']['title'] ?? $learningExperience->title;
            $learningExperience->update([
                'title' => $title,
                'schema_json' => $result['schema'],
                'schema_version' => SchemaValidator::SCHEMA_VERSION,
                'engine_version' => SchemaValidator::ENGINE_VERSION,
            ]);
        }

        return response()->json([
            'ok' => $result['validation']['valid'],
            'schema' => $result['schema'],
            'applied' => $result['applied'],
            'errors' => array_merge($result['errors'], $result['validation']['errors']),
            'persisted' => ($data['persist'] ?? true) && $result['validation']['valid'],
        ], $result['validation']['valid'] ? 200 : 422);
    }

    public function aiGenerate(Request $request, LearningExperience $learningExperience): JsonResponse
    {
        $data = $request->validate([
            'topic' => ['required', 'string', 'max:500'],
            'objectives' => ['nullable', 'string', 'max:1000'],
            'count' => ['nullable', 'integer', 'min:1', 'max:15'],
            'difficulty' => ['nullable', 'string', 'in:easy,medium,hard'],
            'types' => ['nullable', 'array'],
            'types.*' => ['string'],
            'model_id' => ['nullable', 'integer', 'exists:ai_models,id'],
            'mode' => ['nullable', 'string', 'in:replace,append'],
        ]);

        try {
            $generated = $this->aiSessionGenerationService->generate(
                topic: $data['topic'],
                types: $data['types'] ?? QuestionTypeRegistry::types(),
                count: (int) ($data['count'] ?? 5),
                difficulty: $data['difficulty'] ?? 'medium',
                objectives: (string) ($data['objectives'] ?? ''),
                modelId: isset($data['model_id']) ? (int) $data['model_id'] : null,
                experienceMode: $this->schemaValidator->resolveMode(
                    is_array($learningExperience->schema_json) ? $learningExperience->schema_json : []
                ),
            );

            return response()->json([
                'ok' => true,
                'summary' => $generated['summary'],
                'model' => $generated['model'],
                'questions' => $generated['questions'],
                'mode' => $data['mode'] ?? 'replace',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * الخطوة 1: تحليل ملف مرفوع (PDF أو صورة) وإرجاع النص المستخرج للمعاينة.
     *
     * النص يعود للمتصفح ولا يُخزَّن شيء على الخادم. أما الصور (صورة مرفوعة أو
     * PDF ممسوح ضوئياً) فلا نص لها، فيُحفظ الملف مؤقتاً ويُعاد رمز مرتبط بالجلسة.
     */
    public function aiSourceExtract(Request $request, LearningExperience $learningExperience): JsonResponse
    {
        $pdfMaxKb = (int) config('ai.question_generation_pdf.max_size_kb', 15360);
        $imageMaxKb = (int) config('ai.question_generation_pdf.image_max_size_kb', 8192);

        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:pdf,jpeg,jpg,png,webp,gif',
                'max:'.max($pdfMaxKb, $imageMaxKb),
            ],
        ], [
            'file.required' => 'يجب اختيار ملف.',
            'file.mimes' => 'يُقبل ملف PDF أو صورة (JPEG, PNG, WebP, GIF) فقط.',
            'file.max' => 'حجم الملف أكبر من المسموح.',
        ]);

        $file = $request->file('file');

        try {
            $extracted = $this->sourceExtractionService->extract($file);

            if ($extracted['kind'] === ExperienceSourceExtractionService::KIND_TEXT) {
                return response()->json([
                    'ok' => true,
                    'kind' => 'text',
                    'text' => $extracted['text'],
                    'pageCount' => $extracted['pageCount'],
                    'charCount' => $extracted['charCount'],
                    'imagesCount' => 0,
                    'notes' => $extracted['notes'],
                ]);
            }

            // مسار الصور: نحتاج بقاء الملف حتى طلب التوليد
            $this->pruneStaleSourceFiles();
            $path = $file->store('ile_ai_sources', 'local');
            $token = (string) Str::uuid();

            $request->session()->put($this->sourceSessionKey($token), [
                'path' => $path,
                'experience_id' => $learningExperience->id,
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'ok' => true,
                'kind' => 'images',
                'text' => '',
                'pageCount' => $extracted['pageCount'],
                'charCount' => 0,
                'imagesCount' => count($extracted['images']),
                'notes' => $extracted['notes'],
                'token' => $token,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * الخطوة 2: توليد الأسئلة من النص المُعاين (أو من صور الملف المؤقّت).
     * تُرجع نفس شكل aiGenerate() ليعمل نفس مسار المعاينة والإضافة بلا تعديل.
     */
    public function aiGenerateFromSource(Request $request, LearningExperience $learningExperience): JsonResponse
    {
        $data = $request->validate([
            'text' => ['nullable', 'string'],
            'token' => ['nullable', 'string', 'max:64'],
            'objectives' => ['nullable', 'string', 'max:1000'],
            'count' => ['nullable', 'integer', 'min:1', 'max:15'],
            'difficulty' => ['nullable', 'string', 'in:easy,medium,hard'],
            'types' => ['nullable', 'array'],
            'types.*' => ['string'],
            'model_id' => ['nullable', 'integer', 'exists:ai_models,id'],
            'mode' => ['nullable', 'string', 'in:replace,append'],
        ]);

        $token = (string) ($data['token'] ?? '');
        $sessionKey = $token !== '' ? $this->sourceSessionKey($token) : null;
        $storedPath = null;

        try {
            if ($token !== '') {
                $entry = $request->session()->get($sessionKey);

                // المسار يُقرأ من الجلسة فقط — لا يُقبل مسار من الطلب
                if (! is_array($entry)
                    || (int) ($entry['experience_id'] ?? 0) !== (int) $learningExperience->id
                    || (int) ($entry['user_id'] ?? 0) !== (int) $request->user()?->id) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'انتهت صلاحية الملف المرفوع. أعد تحليل الملف من جديد.',
                    ], 422);
                }

                $storedPath = (string) $entry['path'];
                if (! Storage::disk('local')->exists($storedPath)) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'الملف المؤقّت غير موجود. أعد تحليل الملف من جديد.',
                    ], 422);
                }

                $source = $this->sourceExtractionService->extractFromStoredPath(
                    Storage::disk('local')->path($storedPath)
                );
            } else {
                $text = trim((string) ($data['text'] ?? ''));
                if ($text === '') {
                    return response()->json([
                        'ok' => false,
                        'message' => 'لا يوجد نص لتوليد الأسئلة منه. حلّل ملفاً أولاً.',
                    ], 422);
                }

                $source = [
                    'kind' => ExperienceSourceExtractionService::KIND_TEXT,
                    'text' => $text,
                    'images' => [],
                ];
            }

            $generated = $this->aiSessionGenerationService->generateFromSource(
                source: $source,
                types: $data['types'] ?? QuestionTypeRegistry::types(),
                count: (int) ($data['count'] ?? 5),
                difficulty: $data['difficulty'] ?? 'medium',
                objectives: (string) ($data['objectives'] ?? ''),
                modelId: isset($data['model_id']) ? (int) $data['model_id'] : null,
                experienceMode: $this->schemaValidator->resolveMode(
                    is_array($learningExperience->schema_json) ? $learningExperience->schema_json : []
                ),
            );

            return response()->json([
                'ok' => true,
                'summary' => $generated['summary'],
                'model' => $generated['model'],
                'questions' => $generated['questions'],
                'mode' => $data['mode'] ?? 'replace',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        } finally {
            // الملف المؤقّت يُحذف نجاحاً أو فشلاً
            if ($storedPath !== null) {
                Storage::disk('local')->delete($storedPath);
            }
            if ($sessionKey !== null) {
                $request->session()->forget($sessionKey);
            }
        }
    }

    protected function sourceSessionKey(string $token): string
    {
        return 'ile_ai_source.'.$token;
    }

    /**
     * حذف ملفات المصدر المؤقّتة المهجورة (رُفعت ثم لم يُطلب التوليد منها).
     * تنظيف انتهازي عند كل رفع جديد — بلا اعتماد على المُجدول.
     */
    protected function pruneStaleSourceFiles(int $maxAgeMinutes = 120): void
    {
        try {
            $disk = Storage::disk('local');
            if (! $disk->exists('ile_ai_sources')) {
                return;
            }

            $cutoff = now()->subMinutes($maxAgeMinutes)->getTimestamp();
            foreach ($disk->files('ile_ai_sources') as $file) {
                if ($disk->lastModified($file) < $cutoff) {
                    $disk->delete($file);
                }
            }
        } catch (\Throwable $e) {
            // التنظيف مساعد لا حرج في فشله — لا يجوز أن يُفشل رفع الملف
            report($e);
        }
    }

    public function aiGenerateApply(Request $request, LearningExperience $learningExperience): JsonResponse
    {
        $data = $request->validate([
            'questions' => ['required', 'array', 'min:1'],
            'schema_json' => ['nullable', 'array'],
            'mode' => ['nullable', 'string', 'in:replace,append'],
            'persist' => ['nullable', 'boolean'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $schema = $data['schema_json'] ?? $learningExperience->schema_json ?? $this->schemaValidator->emptySchema($learningExperience->title);
        if (! is_array($schema)) {
            $schema = $this->schemaValidator->emptySchema($learningExperience->title);
        }

        $mode = $data['mode'] ?? 'replace';
        $questions = array_values(array_filter($data['questions'], 'is_array'));

        if ($mode === 'append') {
            $schema['questions'] = array_values(array_merge($schema['questions'] ?? [], $questions));
        } else {
            $schema['questions'] = $questions;
        }

        if (! empty($data['title'])) {
            $schema['meta']['title'] = $data['title'];
        }

        $validation = $this->schemaValidator->validate($schema);
        if (! $validation['valid']) {
            return response()->json([
                'ok' => false,
                'errors' => $validation['errors'],
                'schema' => $schema,
            ], 422);
        }

        if ($data['persist'] ?? true) {
            $learningExperience->update([
                'title' => $schema['meta']['title'] ?? $learningExperience->title,
                'schema_json' => $schema,
                'schema_version' => $this->schemaValidator->resolveMode($schema) === 'dynamic'
                    ? SchemaValidator::SCHEMA_VERSION_DYNAMIC
                    : SchemaValidator::SCHEMA_VERSION,
                'engine_version' => SchemaValidator::ENGINE_VERSION,
            ]);
        }

        return response()->json([
            'ok' => true,
            'schema' => $schema,
            'count' => count($schema['questions']),
            'persisted' => (bool) ($data['persist'] ?? true),
        ]);
    }

    public function importParse(Request $request, LearningExperience $learningExperience): JsonResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'format' => ['required', 'string', 'in:csv,md,json'],
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $allowed = match ($data['format']) {
            'csv' => ['csv'],
            'md' => ['md', 'txt'],
            'json' => ['json'],
        };
        if (! in_array($ext, $allowed, true)) {
            return response()->json([
                'ok' => false,
                'message' => 'امتداد الملف لا يطابق الصيغة المختارة.',
            ], 422);
        }

        $mode = $this->schemaValidator->resolveMode(
            is_array($learningExperience->schema_json) ? $learningExperience->schema_json : []
        );

        try {
            $parsed = $this->experienceQuestionImportService->parseUploadedFile(
                $file,
                $data['format'],
                $mode
            );

            return response()->json([
                'ok' => true,
                'count' => count($parsed['questions']),
                'questions' => $parsed['questions'],
                'previews' => $parsed['previews'],
                'suspicious_count' => $parsed['suspicious_count'],
                'parse_warnings' => $parsed['parse_warnings'] ?? [],
                'mode' => $mode,
            ]);
        } catch (ExperienceQuestionImportException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['ok' => false, 'message' => 'فشل تحليل الملف: '.$e->getMessage()], 422);
        }
    }

    public function importApply(Request $request, LearningExperience $learningExperience): JsonResponse
    {
        $data = $request->validate([
            'questions' => ['required', 'array', 'min:1'],
            'schema_json' => ['nullable', 'array'],
            'mode' => ['nullable', 'string', 'in:replace,append'],
            'persist' => ['nullable', 'boolean'],
        ]);

        // Reuse AI apply pipeline for merge + validation + persist
        $request->merge([
            'persist' => $data['persist'] ?? true,
            'mode' => $data['mode'] ?? 'append',
        ]);

        return $this->aiGenerateApply($request, $learningExperience);
    }

    public function importTemplate(Request $request): StreamedResponse
    {
        $format = $request->string('format')->toString() ?: 'csv';

        if ($format === 'json') {
            $payload = [
                'questions' => [
                    [
                        'type' => 'single_choice',
                        'stem' => 'ما ناتج \$2+2\$؟',
                        'difficulty' => 'easy',
                        'points' => 1,
                        'hints' => ['اجمع العددين'],
                        'explanation' => '2+2=4',
                        'payload' => [
                            'options' => [
                                ['id' => 'a', 'label' => '3', 'icon' => '⭐'],
                                ['id' => 'b', 'label' => '4', 'icon' => '⭐'],
                                ['id' => 'c', 'label' => '5', 'icon' => '⭐'],
                            ],
                            'correctId' => 'b',
                        ],
                    ],
                    [
                        'type' => 'numerical',
                        'stem' => 'احسب \$\\\\frac{1}{2} + \\\\frac{1}{2}\$',
                        'difficulty' => 'medium',
                        'points' => 1,
                        'payload' => [
                            'correct' => '1',
                            'tolerance' => 0,
                            'unit' => '',
                        ],
                    ],
                ],
            ];

            return response()->streamDownload(function () use ($payload) {
                echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }, 'ile-questions-template.json', ['Content-Type' => 'application/json; charset=UTF-8']);
        }

        if ($format === 'md') {
            $md = <<<'MD'
## 1. ما ناتج \(2+2\)؟
type: single_choice
difficulty: easy
points: 1
- **A.** 3
- **B.** 4
- **C.** 5
> **Answer:** B
> **Hint:** اجمع العددين
> **Rationale:** 2+2=4

## 2. احسب \(\frac{1}{2}+\frac{1}{2}\)
type: numerical
correct: 1
tolerance: 0
difficulty: medium
points: 1
MD;

            return response()->streamDownload(function () use ($md) {
                echo $md;
            }, 'ile-questions-template.md', ['Content-Type' => 'text/markdown; charset=UTF-8']);
        }

        // Default CSV (typed columns — supports math LaTeX in stem/options)
        $csv = "type,stem,difficulty,points,hint,explanation,option_a,option_b,option_c,option_d,correct,tolerance,unit\n"
            ."single_choice,\"ما ناتج \$2+2\$؟\",easy,1,\"اجمع\",\"2+2=4\",3,4,5,6,B,,\n"
            ."numerical,\"احسب \$\\\\frac{1}{2}+\\\\frac{1}{2}\$\",medium,1,\"\",\"\",,,,,1,0,\n"
            ."true_false,\"\$3+1=4\$\",easy,1,\"\",\"\",,,,,true,,\n"
            ."short_answer,\"ما عاصمة سوريا؟\",easy,1,\"\",\"\",,,,,دمشق,,\n";

        return response()->streamDownload(function () use ($csv) {
            echo "\xEF\xBB\xBF".$csv;
        }, 'ile-questions-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
