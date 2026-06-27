<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ProvidesLinkableCurriculum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLessonRequest;
use App\Http\Requests\Admin\UpdateLessonRequest;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\SystemSetting;
use App\Models\Unit;
use App\Services\Curriculum\LessonCloneService;
use App\Services\VimeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Helpers\StorageHelper;
use App\Services\Storage\MediaStorageService;
use App\Services\LessonAttachmentService;
use App\Services\StaffNotificationService;
use App\Services\StudentContentNotificationService;

class LessonController extends Controller
{
    use ProvidesLinkableCurriculum;

    public function __construct(
        protected LessonCloneService $cloneService,
        protected LessonAttachmentService $attachmentService
    ) {
        $this->middleware(['permission:lesson-list'])->only('index');
        $this->middleware(['permission:lesson-create'])->only('store');
        $this->middleware(['permission:lesson-edit'])->only(['edit', 'update', 'reorder', 'getLinkedUnits', 'linkUnits']);
        $this->middleware(['permission:lesson-delete'])->only('destroy');
        $this->middleware(['permission:lesson-show'])->only('show');
        $this->middleware(['permission:lesson-approve-review'])->only('approveReview');
        $this->middleware(['permission:lesson-reject-review'])->only('rejectReview');
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Lesson::query()
            ->with([
                'unit.section.subject.schoolClass.stage',
                'section.subject.schoolClass.stage',
                'clonedFromLesson.unit.section.subject.schoolClass',
                'linkedUnits.section.subject.schoolClass.stage',
                'syncMirrors.unit.section.subject.schoolClass.stage',
            ])
            ->withCount([
                'syncMirrors as sync_mirrors_count',
                'linkedUnits as legacy_links_count',
            ]);

        if ($user->usesTeacherAssignmentScope()) {
            $classIds = $user->assignedClasses()->pluck('classes.id');
            $subjectIds = $user->assignedSubjects()->pluck('subjects.id');

            $query->where(function ($q) use ($classIds, $subjectIds) {
                $applySubjectFilter = function ($subjectQuery) use ($classIds, $subjectIds) {
                    if ($classIds->isNotEmpty()) {
                        $subjectQuery->whereIn('class_id', $classIds);
                    }
                    if ($subjectIds->isNotEmpty()) {
                        if ($classIds->isNotEmpty()) {
                            $subjectQuery->orWhereIn('id', $subjectIds);
                        } else {
                            $subjectQuery->whereIn('id', $subjectIds);
                        }
                    }
                    if ($classIds->isEmpty() && $subjectIds->isEmpty()) {
                        $subjectQuery->whereRaw('1 = 0');
                    }
                };

                $q->whereHas('unit.section.subject', $applySubjectFilter)
                    ->orWhereHas('section.subject', $applySubjectFilter);
            });
        }

        if ($user->usesSupervisorAssignmentScope()) {
            $query->forSupervisor($user->id);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('review_status')) {
            $query->where('review_status', $request->input('review_status'));
        }

        if ($request->filled('video_type')) {
            $query->where('video_type', $request->input('video_type'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->input('is_active') === '1');
        }

        if ($request->filled('placement')) {
            if ($request->input('placement') === 'unit') {
                $query->whereNotNull('unit_id');
            } elseif ($request->input('placement') === 'section') {
                $query->whereNull('unit_id')->whereNotNull('section_id');
            }
        }

        if ($request->filled('link_role')) {
            if ($request->input('link_role') === 'original') {
                $query->whereNull('cloned_from_lesson_id');
            } elseif ($request->input('link_role') === 'mirror') {
                $query->whereNotNull('cloned_from_lesson_id');
            }
        }

        if ($request->filled('link_presence') && $request->input('link_presence') !== 'any') {
            $this->applyLessonLinkPresenceFilter($query, $request->input('link_presence'));
        }

        if ($request->filled('class_id')) {
            $classId = (int) $request->input('class_id');
            $this->applyLessonSubjectScope($query, function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        if ($request->filled('subject_id')) {
            $subjectId = (int) $request->input('subject_id');
            $this->applyLessonSubjectScope($query, function ($q) use ($subjectId) {
                $q->where('id', $subjectId);
            });
        }

        if ($request->filled('section_id')) {
            $sectionId = (int) $request->input('section_id');
            $query->where(function ($q) use ($sectionId) {
                $q->where('section_id', $sectionId)
                    ->orWhereHas('unit', fn ($uq) => $uq->where('section_id', $sectionId));
            });
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', (int) $request->input('unit_id'));
        }

        $lessons = $query->orderByDesc('updated_at')->paginate(20)->withQueryString();

        $filterData = $this->lessonIndexFilterOptions($user, $request);
        $linkablePayload = auth()->user()->can('lesson-edit')
            ? $this->buildLinkableCurriculumPayload($user)
            : ['linkableStructure' => collect(), 'linkableClasses' => collect()];

        if ($request->expectsJson() || $request->ajax()) {
            $html = view('admin.pages.lessons.partials.table', compact('lessons'))->render();
            $pagination = view('admin.pages.lessons.partials.pagination', compact('lessons'))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination,
                'count' => $lessons->total(),
            ]);
        }

        return view('admin.pages.lessons.index', array_merge(compact('lessons'), $filterData, $linkablePayload));
    }

    private function applyLessonSubjectScope($query, callable $subjectConstraint): void
    {
        $query->where(function ($q) use ($subjectConstraint) {
            $q->whereHas('unit.section.subject', $subjectConstraint)
                ->orWhereHas('section.subject', $subjectConstraint);
        });
    }

    private function applyLessonLinkPresenceFilter($query, string $presence): void
    {
        $legacyExists = function ($sub) {
            $sub->selectRaw('1')
                ->from('lesson_units')
                ->whereColumn('lesson_units.lesson_id', 'lessons.id')
                ->where(function ($w) {
                    $w->whereNull('lessons.unit_id')
                        ->orWhereColumn('lesson_units.unit_id', '!=', 'lessons.unit_id');
                });
        };

        match ($presence) {
            'has_sync' => $query->whereHas('syncMirrors'),
            'has_legacy' => $query->whereExists($legacyExists),
            'has_any_link' => $query->where(function ($q) use ($legacyExists) {
                $q->whereNotNull('cloned_from_lesson_id')
                    ->orWhereHas('syncMirrors')
                    ->orWhereExists($legacyExists);
            }),
            'none' => $query->whereNull('cloned_from_lesson_id')
                ->whereDoesntHave('syncMirrors')
                ->whereNotExists($legacyExists),
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function lessonIndexFilterOptions($user, Request $request): array
    {
        if ($user->usesSupervisorAssignmentScope()) {
            $classIds = $user->assignedClassesAsSupervisor()->pluck('classes.id');
            $subjectIds = $user->assignedSubjectsAsSupervisor()->pluck('subjects.id');

            $classes = SchoolClass::with('stage')
                ->active()
                ->ordered()
                ->when($classIds->isNotEmpty(), fn ($q) => $q->whereIn('id', $classIds), fn ($q) => $q->whereRaw('1 = 0'))
                ->get();

            $subjects = Subject::with('schoolClass.stage')
                ->active()
                ->ordered()
                ->when($subjectIds->isNotEmpty(), fn ($q) => $q->whereIn('id', $subjectIds), fn ($q) => $q->whereRaw('1 = 0'))
                ->get();
        } elseif ($user->usesTeacherAssignmentScope()) {
            $classIds = $user->assignedClasses()->pluck('classes.id');
            $subjectIds = $user->assignedSubjects()->pluck('subjects.id');

            $classes = SchoolClass::with('stage')
                ->active()
                ->ordered()
                ->when($classIds->isNotEmpty(), fn ($q) => $q->whereIn('id', $classIds), fn ($q) => $q->whereRaw('1 = 0'))
                ->get();

            $subjects = Subject::with('schoolClass.stage')
                ->active()
                ->ordered()
                ->where(function ($q) use ($classIds, $subjectIds) {
                    if ($classIds->isNotEmpty()) {
                        $q->whereIn('class_id', $classIds);
                    }
                    if ($subjectIds->isNotEmpty()) {
                        $classIds->isNotEmpty()
                            ? $q->orWhereIn('id', $subjectIds)
                            : $q->whereIn('id', $subjectIds);
                    }
                    if ($classIds->isEmpty() && $subjectIds->isEmpty()) {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->get();
        } else {
            $classes = SchoolClass::with('stage')->active()->ordered()->get();
            $subjects = Subject::with('schoolClass.stage')->active()->ordered()->get();
        }

        $sections = collect();
        if ($request->filled('subject_id')) {
            $sections = SubjectSection::with('subject')
                ->where('subject_id', (int) $request->input('subject_id'))
                ->orderBy('order')
                ->get();
        }

        $units = collect();
        if ($request->filled('section_id')) {
            $units = Unit::where('section_id', (int) $request->input('section_id'))
                ->orderBy('title')
                ->get();
        }

        return compact('classes', 'subjects', 'sections', 'units');
    }

    private function applyTeacherLessonReviewOnCreate(array &$data): void
    {
        if (SystemSetting::lessonMandatoryReviewEnabled()) {
            $data['review_status'] = Lesson::REVIEW_STATUS_PENDING;
            $data['is_active'] = false;
            $data['submitted_for_review_at'] = now();

            return;
        }

        if (request()->has('is_active')) {
            $data['review_status'] = Lesson::REVIEW_STATUS_PENDING;
            $data['submitted_for_review_at'] = now();
            $data['is_active'] = false;
        } else {
            $data['review_status'] = Lesson::REVIEW_STATUS_DRAFT;
            $data['is_active'] = false;
        }
    }

    private function applyTeacherLessonReviewOnUpdate(array &$data, UpdateLessonRequest $request, Lesson $lesson): void
    {
        if (SystemSetting::lessonMandatoryReviewEnabled()) {
            $wasPending = $lesson->review_status === Lesson::REVIEW_STATUS_PENDING;
            $data['review_status'] = Lesson::REVIEW_STATUS_PENDING;
            $data['is_active'] = false;
            $data['submitted_for_review_at'] = now();
            if (! $wasPending) {
                $data['review_notes'] = null;
            }

            return;
        }

        if ($request->has('is_active') && in_array($lesson->review_status, [Lesson::REVIEW_STATUS_PENDING, Lesson::REVIEW_STATUS_REJECTED])) {
            $data['review_status'] = Lesson::REVIEW_STATUS_PENDING;
            $data['submitted_for_review_at'] = now();
            $data['review_notes'] = null;
            $data['is_active'] = false;
        } elseif ($request->has('is_active')) {
            $data['review_status'] = Lesson::REVIEW_STATUS_PENDING;
            $data['submitted_for_review_at'] = now();
            $data['is_active'] = false;
        } else {
            $data['review_status'] = Lesson::REVIEW_STATUS_DRAFT;
            $data['is_active'] = false;
        }
    }

    /**
     * تخزين درس جديد تابع لوحدة معيّنة.
     */
    public function store(StoreLessonRequest $request, Unit $unit)
    {
        return $this->storeWithContext($request, $unit, null);
    }

    /**
     * تخزين درس جديد مرتبط مباشرة بقسم (بدون وحدة).
     */
    public function storeForSection(StoreLessonRequest $request, SubjectSection $section)
    {
        return $this->storeWithContext($request, null, $section);
    }

    private function storeWithContext(StoreLessonRequest $request, ?Unit $unit, ?SubjectSection $section)
    {
        $contextId = $unit?->id ?? $section?->id;
        Log::info('محاولة إنشاء درس جديد (context): ' . $contextId, $request->all());

        try {
            $uploadedFiles = array_values(array_filter($request->file('attachment_files') ?? []));
            $hasLink = $request->filled('attachment_url');
            $hasLegacyFile = $request->hasFile('attachment_file');
            $hasAttachmentInput = $uploadedFiles !== [] || $hasLink || $hasLegacyFile;

            if ($hasAttachmentInput) {
                $request->validate([
                    'attachment_title' => ['nullable', 'string', 'max:255'],
                    'attachment_description' => ['nullable', 'string'],
                    'attachment_files' => ['nullable', 'array', 'max:20'],
                    'attachment_files.*' => ['file', 'max:51200'],
                    'attachment_url' => ['nullable', 'url', 'max:500'],
                    'attachment_file' => ['nullable', 'file', 'max:51200'],
                    'attachment_type' => ['nullable', 'in:file,link,document,image,audio'],
                ]);

                if ($uploadedFiles === [] && ! $hasLegacyFile && ! $hasLink) {
                    throw ValidationException::withMessages([
                        'attachment_files' => 'يرجى اختيار ملف واحد على الأقل أو إدخال رابط.',
                    ]);
                }
            }

            $subject = $unit?->section?->subject ?? $section?->subject;
            if (!$subject) {
                throw ValidationException::withMessages([
                    'section_id' => 'لا يمكن تحديد المادة المرتبطة بهذا الدرس.',
                ]);
            }

            $user = auth()->user();
            if ($user->usesTeacherAssignmentScope()) {
                if (!$user->isAssignedToSubject($subject->id) &&
                    !$user->isAssignedToClass($subject->class_id)) {
                    abort(403, 'غير مصرح لك بالوصول إلى هذه المادة');
                }
            }

            $data = $request->validated();
            $data['unit_id'] = $unit?->id;
            $data['section_id'] = $section?->id ?? $unit?->section_id;
            $data['is_free'] = $request->has('is_free');
            $data['is_preview'] = $request->has('is_preview');

            if ($data['unit_id'] && $data['section_id']) {
                $unitSectionId = Unit::where('id', $data['unit_id'])->value('section_id');
                if ((int) $unitSectionId !== (int) $data['section_id']) {
                    throw ValidationException::withMessages([
                        'section_id' => 'القسم المحدد لا يطابق الوحدة المحددة.',
                    ]);
                }
            }

            $isTeacher = $user->shouldSubmitContentForReview();
            if ($isTeacher) {
                $this->applyTeacherLessonReviewOnCreate($data);
            } else {
                $data['is_active'] = $request->has('is_active');
                $data['review_status'] = $data['is_active']
                    ? Lesson::REVIEW_STATUS_APPROVED
                    : Lesson::REVIEW_STATUS_DRAFT;
            }

            if ($data['video_type'] === 'youtube' && !empty($data['video_url'])) {
                $data['video_id'] = Lesson::extractYoutubeId($data['video_url']);
            } elseif ($data['video_type'] === 'vimeo' && !empty($data['video_url'])) {
                $data['video_id'] = Lesson::extractVimeoId($data['video_url']);
                $duration = app(VimeoService::class)->getVideoDuration($data['video_url']);
                if ($duration !== null) {
                    $data['duration'] = (int) $duration;
                }
            }

            if ($request->hasFile('video_file')) {
                $videoFile = $request->file('video_file');
                $videoName = time() . '_' . $videoFile->getClientOriginalName();
                $uploadResult = MediaStorageService::uploadVideo($videoFile, 'lessons/videos', $videoName, true);
                $data['video_url'] = $uploadResult['path'];
            }

            if ($request->hasFile('thumbnail')) {
                $thumbnail = $request->file('thumbnail');
                $thumbName = time() . '_thumb_' . $thumbnail->getClientOriginalName();
                $uploadResult = MediaStorageService::uploadImage($thumbnail, 'lessons/thumbnails', $thumbName);
                $data['thumbnail'] = $uploadResult['path'];
            }

            if (!isset($data['order']) || $data['order'] === null) {
                $query = Lesson::query();
                if (!empty($data['unit_id'])) {
                    $query->where('unit_id', $data['unit_id']);
                } else {
                    $query->whereNull('unit_id')->where('section_id', $data['section_id']);
                }
                $maxOrder = $query->max('order') ?? 0;
                $data['order'] = $maxOrder + 1;
            }

            $lesson = Lesson::create($data);

            $attachmentCount = 0;
            $attachmentOptions = [
                'description' => $request->input('attachment_description'),
                'is_downloadable' => $request->has('attachment_is_downloadable'),
            ];

            if ($hasAttachmentInput) {
                if ($uploadedFiles !== []) {
                    $singleFileTitle = count($uploadedFiles) === 1 ? $request->input('attachment_title') : null;
                    foreach ($uploadedFiles as $file) {
                        $this->attachmentService->createFromUploadedFile($lesson, $file, array_merge($attachmentOptions, [
                            'title' => $singleFileTitle,
                        ]));
                        $attachmentCount++;
                    }
                } elseif ($hasLink) {
                    $this->attachmentService->createFromLink($lesson, array_merge($attachmentOptions, [
                        'url' => $request->input('attachment_url'),
                        'title' => $request->input('attachment_title'),
                    ]));
                    $attachmentCount = 1;
                } elseif ($hasLegacyFile) {
                    $legacyFile = $request->file('attachment_file');
                    $legacyType = $request->input('attachment_type');
                    $this->attachmentService->createFromUploadedFile($lesson, $legacyFile, array_merge($attachmentOptions, [
                        'title' => $request->input('attachment_title'),
                        'type' => in_array($legacyType, ['file', 'document', 'image', 'audio'], true) ? $legacyType : null,
                    ]));
                    $attachmentCount = 1;
                }
            }

            if ($lesson->review_status === Lesson::REVIEW_STATUS_PENDING && $user->shouldSubmitContentForReview()) {
                $this->dispatchNotificationSafely(function () use ($lesson, $user) {
                    app(StaffNotificationService::class)->notifyLessonSubmittedForReview($lesson->fresh(), $user);
                }, 'lesson_review_submitted', $lesson->id);
            }

            $this->dispatchNotificationSafely(function () use ($lesson, $user) {
                app(StudentContentNotificationService::class)->notifyIfLessonBecameVisible(null, $lesson->fresh(), $user);
            }, 'student_lesson_visible', $lesson->id);

            $submittedForReview = $lesson->review_status === Lesson::REVIEW_STATUS_PENDING && $isTeacher;
            $successMessage = $submittedForReview
                ? 'تم حفظ الدرس «' . $lesson->title . '» وإرساله للمراجعة.'
                : 'تم إنشاء الدرس «' . $lesson->title . '» بنجاح.';

            if ($attachmentCount > 0) {
                $successMessage .= ' تم إنشاء ' . $attachmentCount . ' مرفق/مرفقات.';
            }

            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'lesson_id' => $lesson->id,
                    'unit_id' => $lesson->unit_id,
                    'section_id' => $lesson->section_id,
                    'subject_id' => $subject->id,
                    'submitted_for_review' => $submittedForReview,
                ]);
            }

            return redirect()
                ->route('admin.subjects.show', $subject->id)
                ->with('success', $successMessage);
        } catch (ValidationException $e) {
            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'تعذر إنشاء الدرس بسبب أخطاء في البيانات المدخلة.',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('خطأ في إنشاء درس: ' . $e->getMessage());
            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء إنشاء الدرس.',
                ], 500);
            }
            return redirect()->back()->with('error', 'حدث خطأ أثناء إنشاء الدرس: ' . $e->getMessage());
        }
    }

    /**
     * عرض صفحة الدرس (للتشغيل والمشاهدة).
     */
    public function show(Lesson $lesson)
    {
        $lesson->load(['unit.section.subject', 'section.subject', 'attachments']);
        
        // التحقق من التخصيص
        $user = auth()->user();
        $subject = $this->resolveSubjectFromLesson($lesson);
        $this->assertTeacherCanAccessLesson($user, $subject);

        $enrolledUserIds = $subject
            ? $subject->students()->wherePivot('status', 'active')->pluck('users.id')->toArray()
            : [];
        $lessonCompletions = empty($enrolledUserIds)
            ? collect()
            : LessonCompletion::where('lesson_id', $lesson->id)
                ->whereIn('user_id', $enrolledUserIds)
                ->with('user')
                ->orderBy('updated_at', 'desc')
                ->get();
        
        return view('admin.pages.lessons.show', compact('lesson', 'lessonCompletions'));
    }

    /**
     * نموذج تعديل الدرس (صفحة مستقلة من معاينة الدرس أو من روابط أخرى).
     */
    public function edit(Lesson $lesson)
    {
        $lesson->load([
            'unit.section.subject',
            'section.subject',
            'linkedUnits.section.subject',
            'attachments',
        ]);

        $user = auth()->user();
        $subject = $this->resolveSubjectFromLesson($lesson);
        $this->assertTeacherCanAccessLesson($user, $subject);

        return view('admin.pages.lessons.edit', compact('lesson', 'subject'));
    }

    /**
     * تحديث درس موجود.
     */
    public function update(UpdateLessonRequest $request, Lesson $lesson)
    {
        try {
            // التحقق من التخصيص
            $user = auth()->user();
            $subject = $this->resolveSubjectFromLesson($lesson);
            $this->assertTeacherCanAccessLesson($user, $subject);

            $lessonBeforeUpdate = clone $lesson;
            
            $data = $request->validated();
            $data['is_free'] = $request->has('is_free');
            $data['is_preview'] = $request->has('is_preview');

            $oldReviewStatus = $lesson->review_status;
            $isTeacher = $user->shouldSubmitContentForReview();

            if ($isTeacher) {
                $this->applyTeacherLessonReviewOnUpdate($data, $request, $lesson);
            } else {
                // المشرف والمدير
                $data['is_active'] = $request->has('is_active');
                if ($data['is_active'] && $lesson->review_status !== Lesson::REVIEW_STATUS_APPROVED) {
                    $data['review_status'] = Lesson::REVIEW_STATUS_APPROVED;
                } elseif (!$data['is_active']) {
                    $data['review_status'] = Lesson::REVIEW_STATUS_DRAFT;
                }
            }

            // معالجة نوع الفيديو واستخراج المعرف
            if ($data['video_type'] === 'youtube' && !empty($data['video_url'])) {
                $data['video_id'] = Lesson::extractYoutubeId($data['video_url']);
            } elseif ($data['video_type'] === 'vimeo' && !empty($data['video_url'])) {
                $data['video_id'] = Lesson::extractVimeoId($data['video_url']);
                $duration = app(VimeoService::class)->getVideoDuration($data['video_url']);
                if ($duration !== null) {
                    $data['duration'] = (int) $duration;
                }
            }

            // رفع ملف الفيديو الجديد
            if ($request->hasFile('video_file')) {
                // حذف الفيديو القديم
                if ($lesson->video_url && $lesson->video_type === 'upload') {
                    MediaStorageService::delete($lesson->video_url);
                }

                $videoFile = $request->file('video_file');
                $videoName = time() . '_' . $videoFile->getClientOriginalName();
                $uploadResult = MediaStorageService::uploadVideo($videoFile, 'lessons/videos', $videoName, true);
                $data['video_url'] = $uploadResult['path'];
            }

            // رفع الصورة المصغرة الجديدة
            if ($request->hasFile('thumbnail')) {
                // حذف الصورة القديمة
                if ($lesson->thumbnail) {
                    MediaStorageService::delete($lesson->thumbnail);
                }

                $thumbnail = $request->file('thumbnail');
                $thumbName = time() . '_thumb_' . $thumbnail->getClientOriginalName();
                $uploadResult = MediaStorageService::uploadImage($thumbnail, 'lessons/thumbnails', $thumbName);
                $data['thumbnail'] = $uploadResult['path'];
            }

            $lesson->update($data);
            $lesson->refresh();

            if (
                $lesson->review_status === Lesson::REVIEW_STATUS_PENDING
                && $oldReviewStatus !== Lesson::REVIEW_STATUS_PENDING
                && $user->shouldSubmitContentForReview()
            ) {
                $this->dispatchNotificationSafely(function () use ($lesson, $user) {
                    app(StaffNotificationService::class)->notifyLessonSubmittedForReview($lesson->fresh(), $user);
                }, 'lesson_review_submitted', $lesson->id);
            }

            $subjectId = $subject?->id;

            $this->dispatchNotificationSafely(function () use ($lessonBeforeUpdate, $lesson, $user) {
                app(StudentContentNotificationService::class)->notifyIfLessonBecameVisible(
                    $lessonBeforeUpdate,
                    $lesson->fresh(),
                    $user
                );
            }, 'student_lesson_visible', $lesson->id);

            $submittedForReview = $lesson->review_status === Lesson::REVIEW_STATUS_PENDING
                && $isTeacher
                && $oldReviewStatus !== Lesson::REVIEW_STATUS_PENDING;
            $updateSuccessMessage = ($isTeacher && SystemSetting::lessonMandatoryReviewEnabled())
                ? 'تم حفظ الدرس وإرساله للمراجعة.'
                : ($submittedForReview
                    ? 'تم حفظ الدرس وإرساله للمراجعة.'
                    : 'تم تحديث الدرس بنجاح.');

            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => true,
                    'message' => $updateSuccessMessage,
                    'lesson_id' => $lesson->id,
                    'unit_id' => $lesson->unit_id,
                    'section_id' => $lesson->section_id,
                    'subject_id' => $subjectId,
                    'submitted_for_review' => $submittedForReview || ($isTeacher && SystemSetting::lessonMandatoryReviewEnabled()),
                ]);
            }

            if ($request->boolean('redirect_to_lesson')) {
                return redirect()
                    ->route('admin.lessons.show', $lesson)
                    ->with('success', $updateSuccessMessage);
            }

            if ($subject) {
                return redirect()
                    ->route('admin.subjects.show', $subject->id)
                    ->with('success', $updateSuccessMessage);
            }

            return redirect()
                ->route('admin.review-queue.index')
                ->with('success', $updateSuccessMessage);
        } catch (\Exception $e) {
            Log::error('خطأ في تحديث درس: ' . $e->getMessage());

            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء تحديث الدرس.',
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحديث الدرس: ' . $e->getMessage());
        }
    }

    /**
     * الوحدات المرتبطة بنسخ متزامنة من هذا الدرس (JSON).
     */
    public function getLinkedUnits(Lesson $lesson)
    {
        if ($lesson->isSyncMirror()) {
            return response()->json([]);
        }

        $mirrors = Lesson::query()
            ->where('cloned_from_lesson_id', $lesson->id)
            ->whereNotNull('unit_id')
            ->with('unit.section.subject.schoolClass.stage')
            ->get();

        $data = $mirrors->map(function (Lesson $mirror) {
            $unit = $mirror->unit;
            $section = $unit?->section;
            $subject = $section?->subject;

            return [
                'id' => $unit?->id,
                'title' => $unit?->title ?? '',
                'section_id' => $section?->id,
                'section_title' => $section?->path_title ?? $section?->title ?? '',
                'subject_id' => $subject?->id,
                'subject_name' => $subject?->name ?? '',
                'class_name' => optional($subject?->schoolClass)->name ?? '',
                'stage_name' => optional(optional($subject?->schoolClass)->stage)->name ?? '',
                'label' => $this->formatLinkedUnitBadgeText($unit),
            ];
        })->filter(fn ($row) => $row['id'])->values();

        return response()->json($data);
    }

    /**
     * ربط الدرس بوحدات إضافية (نسخ متزامنة).
     */
    public function linkUnits(Request $request, Lesson $lesson)
    {
        if ($lesson->isSyncMirror()) {
            return redirect()
                ->back()
                ->with('error', 'لا يمكن ربط نسخة مرتبطة بوحدات أخرى.');
        }

        $request->validate([
            'linked_targets' => ['nullable', 'array'],
            'linked_targets.*.unit_id' => ['required', 'integer', 'exists:units,id'],
            'linked_unit_ids' => ['nullable', 'array'],
            'linked_unit_ids.*' => ['integer', 'exists:units,id'],
        ]);

        $primaryUnitId = $lesson->unit_id ? (int) $lesson->unit_id : null;
        $desiredUnitIds = $this->normalizeLinkedUnitTargets($request, $primaryUnitId);

        $user = auth()->user();
        if ($user->usesTeacherAssignmentScope()) {
            $allowedUnitIds = $this->allowedUnitIdsForTeacher($user);
            $desiredUnitIds = array_values(array_intersect($desiredUnitIds, $allowedUnitIds));
        }

        $currentUnitIds = $lesson->linkedUnitIdsViaSync();

        $toAdd = array_values(array_diff($desiredUnitIds, $currentUnitIds));
        $toRemove = array_values(array_diff($currentUnitIds, $desiredUnitIds));

        try {
            foreach ($toAdd as $unitId) {
                $targetUnit = Unit::find($unitId);
                if ($targetUnit) {
                    $this->cloneService->cloneLessonToUnit($lesson, $targetUnit);
                }
            }

            foreach ($toRemove as $unitId) {
                $targetUnit = Unit::find($unitId);
                if ($targetUnit) {
                    $this->cloneService->removeMirrorForUnit($lesson, $targetUnit);
                }
            }
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }

        $linkedUnits = Unit::with('section.subject.schoolClass.stage')
            ->whereIn('id', $desiredUnitIds)
            ->get();

        $count = $linkedUnits->count();
        $labels = $linkedUnits->map(fn ($u) => $this->formatLinkedUnitBadgeText($u))->filter()->values()->toArray();

        $message = 'تم تحديث ربط الدرس بالوحدات بنجاح.';
        if ($count > 0) {
            $message .= ' تم إنشاء نسخة متزامنة في '.$count.' وحدة';
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
     * @return array<int, int>
     */
    private function normalizeLinkedUnitTargets(Request $request, ?int $primaryUnitId): array
    {
        $unitIds = [];

        if ($request->has('linked_targets')) {
            foreach ((array) $request->input('linked_targets', []) as $row) {
                $unitId = (int) ($row['unit_id'] ?? 0);
                if ($unitId <= 0 || ($primaryUnitId !== null && $unitId === $primaryUnitId)) {
                    continue;
                }
                $unitIds[$unitId] = $unitId;
            }
        } else {
            foreach ((array) $request->input('linked_unit_ids', []) as $unitId) {
                $unitId = (int) $unitId;
                if ($unitId <= 0 || ($primaryUnitId !== null && $unitId === $primaryUnitId)) {
                    continue;
                }
                $unitIds[$unitId] = $unitId;
            }
        }

        return array_values($unitIds);
    }

    /**
     * @return array<int, int>
     */
    private function allowedUnitIdsForTeacher($user): array
    {
        $classIds = $user->assignedClasses()->pluck('classes.id');
        $subjectIds = $user->assignedSubjects()->pluck('subjects.id');

        return Unit::whereHas('section.subject', function ($q) use ($classIds, $subjectIds) {
            if ($classIds->isNotEmpty() || $subjectIds->isNotEmpty()) {
                $q->where(function ($sq) use ($classIds, $subjectIds) {
                    if ($classIds->isNotEmpty()) {
                        $sq->whereIn('class_id', $classIds);
                    }
                    if ($subjectIds->isNotEmpty()) {
                        $sq->orWhereIn('id', $subjectIds);
                    }
                });
            } else {
                $q->whereRaw('1 = 0');
            }
        })->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function formatLinkedUnitBadgeText(?Unit $unit): string
    {
        if (! $unit) {
            return '';
        }

        $subject = $unit->relationLoaded('section') ? $unit->section?->subject : $unit->section()->with('subject.schoolClass.stage')->first()?->subject;
        $stage = (string) (data_get($subject, 'schoolClass.stage.name') ?? '');
        $class = (string) (data_get($subject, 'schoolClass.name') ?? '');
        $subjectName = (string) ($subject->name ?? '');
        $sectionTitle = (string) (data_get($unit, 'section.path_title') ?? data_get($unit, 'section.title') ?? '');
        $unitTitle = (string) ($unit->title ?? '');

        $prefix = $stage !== ''
            ? $stage.($class !== '' ? ' / '.$class : '')
            : $class;

        $parts = array_filter([
            $prefix !== '' ? $prefix.' — '.$subjectName : $subjectName,
            $sectionTitle,
            $unitTitle,
        ]);

        return implode(' — ', $parts);
    }

    /**
     * حذف درس.
     */
    public function destroy(Request $request, Lesson $lesson)
    {
        // التحقق من التخصيص
        $user = auth()->user();
        $subject = $this->resolveSubjectFromLesson($lesson);
        try {
            $this->assertTeacherCanAccessLesson($user, $subject);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بالوصول إلى هذا الدرس.',
                ], 403);
            }

            throw $e;
        }

        $subjectId = $subject?->id;
        $lessonTitle = $lesson->title;

        try {
            // حذف ملفات الدرس
            if ($lesson->video_url && $lesson->video_type === 'upload') {
                StorageHelper::delete('videos', $lesson->video_url);
            }
            if ($lesson->thumbnail) {
                StorageHelper::delete('images', $lesson->thumbnail);
            }

            // حذف مرفقات الدرس
            foreach ($lesson->attachments as $attachment) {
                if ($attachment->file_path) {
                    StorageHelper::delete('attachments', $attachment->file_path);
                }
            }

            if ($lesson->isSyncMirror()) {
                $this->cloneService->deleteMirrorRecord($lesson);
            } else {
                $this->cloneService->deleteCanonicalRecord($lesson);
            }

            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم حذف الدرس "' . $lessonTitle . '" بنجاح.',
                    'lesson_id' => $lesson->id,
                    'unit_id' => $lesson->unit_id,
                    'section_id' => $lesson->section_id,
                    'subject_id' => $subjectId,
                ]);
            }

            return $this->redirectAfterLessonAction($lesson, 'تم حذف الدرس "' . $lessonTitle . '" بنجاح.');
        } catch (\Exception $e) {
            Log::error('خطأ في حذف درس: ' . $e->getMessage());

            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء حذف الدرس.',
                ], 500);
            }

            if ($subjectId) {
                return redirect()
                    ->route('admin.subjects.show', $subjectId)
                    ->with('error', 'حدث خطأ أثناء حذف الدرس: ' . $e->getMessage());
            }

            return redirect()
                ->route('admin.review-queue.index')
                ->with('error', 'حدث خطأ أثناء حذف الدرس: ' . $e->getMessage());
        }
    }

    /**
     * إعادة ترتيب الدروس ضمن الوحدة.
     */
    public function reorder(Request $request, Unit $unit)
    {
        // التحقق من التخصيص
        $user = auth()->user();
        if ($user->usesTeacherAssignmentScope()) {
            $subject = $unit->section->subject;
            if (!$user->isAssignedToSubject($subject->id) &&
                !$user->isAssignedToClass($subject->class_id)) {
                return response()->json(['success' => false, 'message' => 'غير مصرح لك بالوصول إلى هذه الوحدة.'], 403);
            }
        }

        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:lessons,id'],
        ]);

        $order = $request->input('order');
        $unitLessonIds = Lesson::where('unit_id', $unit->id)->whereIn('id', $order)->pluck('id')->toArray();

        $index = 0;
        foreach ($order as $lessonId) {
            if (in_array((int) $lessonId, $unitLessonIds, true)) {
                Lesson::where('id', $lessonId)->update(['order' => $index]);
                $index++;
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * الموافقة على تفعيل الدرس
     */
    public function approveReview(Request $request, Lesson $lesson)
    {
        $request->validate([
            'review_notes' => 'nullable|string|max:1000',
        ]);

        $lessonBeforeApprove = clone $lesson;

        $lesson->update([
            'review_status' => Lesson::REVIEW_STATUS_APPROVED,
            'is_active' => true,
            'review_notes' => $request->input('review_notes'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $this->dispatchNotificationSafely(function () use ($lesson) {
            app(StaffNotificationService::class)->notifyLessonReviewOutcome($lesson->fresh(), auth()->user(), true);
        }, 'lesson_review_approved', $lesson->id);

        app(StudentContentNotificationService::class)->notifyIfLessonBecameVisible(
            $lessonBeforeApprove,
            $lesson->fresh(),
            auth()->user()
        );

        return $this->redirectAfterLessonAction($lesson, 'تم الموافقة على تفعيل الدرس بنجاح.');
    }

    /**
     * رفض تفعيل الدرس مع ملاحظات
     */
    public function rejectReview(Request $request, Lesson $lesson)
    {
        $request->validate([
            'review_notes' => 'required|string|max:1000',
        ]);

        $lesson->update([
            'review_status' => Lesson::REVIEW_STATUS_REJECTED,
            'is_active' => false,
            'review_notes' => $request->input('review_notes'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $this->dispatchNotificationSafely(function () use ($lesson) {
            app(StaffNotificationService::class)->notifyLessonReviewOutcome($lesson->fresh(), auth()->user(), false);
        }, 'lesson_review_rejected', $lesson->id);

        return $this->redirectAfterLessonAction($lesson, 'تم رفض تفعيل الدرس وتم إرسال الملاحظات للمعلم.');
    }

    private function resolveSubjectFromLesson(Lesson $lesson): ?Subject
    {
        $lesson->loadMissing([
            'unit.section.subject',
            'section.subject',
        ]);

        $subject = $lesson->unit?->section?->subject
            ?? $lesson->section?->subject;

        if ($subject) {
            return $subject;
        }

        if ($lesson->section_id) {
            $subject = SubjectSection::with('subject')
                ->find($lesson->section_id)
                ?->subject;
            if ($subject) {
                return $subject;
            }
        }

        if ($lesson->unit_id) {
            $unitSectionId = Unit::where('id', $lesson->unit_id)->value('section_id');
            if ($unitSectionId) {
                $subject = SubjectSection::with('subject')
                    ->find($unitSectionId)
                    ?->subject;
                if ($subject) {
                    return $subject;
                }
            }
        }

        return null;
    }

    private function assertTeacherCanAccessLesson($user, ?Subject $subject): void
    {
        if (! $user->usesTeacherAssignmentScope()) {
            return;
        }

        if ($subject) {
            if (! $user->isAssignedToSubject($subject->id) &&
                ! $user->isAssignedToClass($subject->class_id)) {
                abort(403, 'غير مصرح لك بالوصول إلى هذا الدرس');
            }

            return;
        }

        if (! $user->canReviewContent()) {
            abort(403, 'لا يمكن التحقق من صلاحية الوصول لهذا الدرس.');
        }
    }

    private function redirectAfterLessonAction(Lesson $lesson, string $message): RedirectResponse
    {
        $subject = $this->resolveSubjectFromLesson($lesson);

        if ($subject) {
            return redirect()
                ->route('admin.subjects.show', $subject->id)
                ->with('success', $message);
        }

        return redirect()
            ->route('admin.review-queue.index')
            ->with('success', $message);
    }

    private function wantsJsonResponse(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax();
    }

    private function dispatchNotificationSafely(callable $callback, string $context, int $lessonId): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            Log::error('Lesson notification dispatch failed: ' . $e->getMessage(), [
                'context' => $context,
                'lesson_id' => $lessonId,
            ]);
        }
    }
}

