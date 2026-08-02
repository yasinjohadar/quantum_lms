<?php

namespace App\InteractiveLearning\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\InteractiveLearning\Models\LearningExperience;
use App\InteractiveLearning\Services\AiPatchService;
use App\InteractiveLearning\Services\AiSessionGenerationService;
use App\InteractiveLearning\Services\ExperienceQuestionImportException;
use App\InteractiveLearning\Services\ExperienceQuestionImportService;
use App\InteractiveLearning\Services\SchemaValidator;
use App\InteractiveLearning\Support\QuestionTypeRegistry;
use App\Models\AIModel;
use App\Models\Lesson;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LearningExperienceController extends Controller
{
    public function __construct(
        protected SchemaValidator $schemaValidator,
        protected AiPatchService $aiPatchService,
        protected AiSessionGenerationService $aiSessionGenerationService,
        protected ExperienceQuestionImportService $experienceQuestionImportService
    ) {}

    public function index(Request $request): View
    {
        $query = LearningExperience::query()
            ->with(['subject.schoolClass', 'unit'])
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

        $experience = LearningExperience::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => LearningExperience::STATUS_DRAFT,
            'schema_json' => $schema,
            'schema_version' => $mode === 'dynamic'
                ? SchemaValidator::SCHEMA_VERSION_DYNAMIC
                : SchemaValidator::SCHEMA_VERSION,
            'engine_version' => SchemaValidator::ENGINE_VERSION,
            'created_by' => $request->user()->id,
            'subject_id' => $links['subject_id'],
            'unit_id' => $links['unit_id'],
            'lesson_id' => $links['lesson_id'],
        ]);

        return redirect()
            ->route('admin.learning-experiences.edit', $experience)
            ->with('success', 'تم إنشاء الاختبار التفاعلي. أكمل الأسئلة ثم انشر.');
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
        ]);

        $schema = json_decode($data['schema_json'], true);
        if (! is_array($schema)) {
            return back()->withInput()->withErrors(['schema_json' => 'JSON غير صالح.']);
        }

        $schema['meta']['title'] = $data['title'];
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
        ]);

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
