@php
    $sectionsPoolForCount = isset($subject) && $subject->relationLoaded('linkedSections')
        ? $allSections->merge($subject->linkedSections)->unique('id')
        : $allSections;
    $sectionTotalLessonsCount = $section->countAllLessonsForDisplay($sectionsPoolForCount);
    $sectionTotalDurationLabel = auth()->user()?->hasRole('admin')
        ? \App\Support\LessonDurationFormatter::formatHoursMinutes($section->totalLessonsDurationSecondsForDisplay($sectionsPoolForCount))
        : null;
    $directLessons = $section->relationLoaded('directLessons')
        ? $section->directLessons
        : \App\Models\Lesson::query()
            ->where('section_id', $section->id)
            ->whereNull('unit_id')
            ->orderBy('order')
            ->with(['attachments', 'quizzes', 'linkedUnits.section.subject', 'clonedFromLesson.unit.section.subject', 'clonedFromLesson.section.subject'])
            ->get();
    $childSections = $allSections->where('parent_id', $section->id)->sortBy('order');
    $isChildSection = $section->parent_id !== null;
    $isLinkedSection = $section->subject_id != $subject->id;
    $isSyncMirror = $section->isSyncMirror();
    $level = (int) ($sectionLevel ?? 0);
    $levelIcons = ['bi-folder-fill', 'bi-folder2', 'bi-folder-symlink-fill', 'bi-journal-bookmark', 'bi-collection-fill', 'bi-journal-text'];
    $levelIcon = $levelIcons[min($level, 5)] ?? 'bi-folder-fill';
    $levelIconRgb = [
        '37, 99, 235',
        '8, 145, 178',
        '219, 39, 119',
        '22, 163, 74',
        '217, 119, 6',
        '124, 58, 237',
    ];
    $levelIconStyle = 'color: rgb(' . ($levelIconRgb[min($level, 5)] ?? $levelIconRgb[0]) . ')';
    $linkedSubjectsPresenceLines = [];
    $linkedSubjectsPresenceCount = 0;
    if (! $isLinkedSection && ! $isSyncMirror) {
        $syncLinkedSubjects = $section->linkedSubjectsViaSync();
        if ($syncLinkedSubjects->isNotEmpty()) {
            $linkedSubjectsPresenceCount = $syncLinkedSubjects->count();
            foreach ($syncLinkedSubjects as $ls) {
                $lst = optional(optional($ls->schoolClass)->stage)->name ?? '';
                $lcl = $ls->schoolClass->name ?? '';
                $ln = $ls->name ?? '';
                $lprefix = $lst !== '' ? $lst.($lcl !== '' ? ' / '.$lcl : '') : $lcl;
                $linkedSubjectsPresenceLines[] = $lprefix !== '' ? $lprefix.' — '.$ln : $ln;
            }
        } elseif ($section->relationLoaded('linkedSubjects')) {
            $linkedSubjectsPresenceCount = $section->linkedSubjects->count();
            foreach ($section->linkedSubjects as $ls) {
                $lst = optional(optional($ls->schoolClass)->stage)->name ?? '';
                $lcl = $ls->schoolClass->name ?? '';
                $ln = $ls->name ?? '';
                $lprefix = $lst !== '' ? $lst.($lcl !== '' ? ' / '.$lcl : '') : $lcl;
                $linkedSubjectsPresenceLines[] = $lprefix !== '' ? $lprefix.' — '.$ln : $ln;
            }
        }
    }
    $linkedSubjectsPresenceTitle = $linkedSubjectsPresenceCount > 0
        ? 'نسخة متزامنة من هذا القسم في: '.implode(' | ', $linkedSubjectsPresenceLines)
        : '';
    $sectionHomeOriginTitle = '';
    if ($isSyncMirror && $section->relationLoaded('clonedFromSection') && $section->clonedFromSection) {
        $originSection = $section->clonedFromSection;
        $os = $originSection->relationLoaded('subject') ? $originSection->subject : $originSection->subject()->with('schoolClass.stage')->first();
        if ($os) {
            $ost = optional(optional($os->schoolClass)->stage)->name ?? '';
            $ocl = $os->schoolClass->name ?? '';
            $oprefix = $ost !== '' ? $ost.($ocl !== '' ? ' / '.$ocl : '') : $ocl;
            $sectionHomeOriginTitle = 'نسخة مرتبطة من: '.($oprefix !== '' ? $oprefix.' — ' : '').($os->name ?? '').' — '.($originSection->path_title ?? $originSection->title);
        }
    } elseif ($isLinkedSection && $section->relationLoaded('subject') && $section->subject) {
        $os = $section->subject;
        $ost = optional(optional($os->schoolClass)->stage)->name ?? '';
        $ocl = $os->schoolClass->name ?? '';
        $oprefix = $ost !== '' ? $ost.($ocl !== '' ? ' / '.$ocl : '') : $ocl;
        $sectionHomeOriginTitle = 'أصل القسم: '.($oprefix !== '' ? $oprefix.' — ' : '').($os->name ?? '').' — مسار القسم: '.($section->path_title ?? $section->title);
    }
@endphp
<div class="accordion-item mb-3 rounded overflow-hidden section-level-{{ $level }}{{ ($isLinkedSection || $isSyncMirror) ? ' section-item-linked' : '' }}" data-id="{{ $section->id }}" data-section-id="{{ $section->id }}">
    <h2 class="accordion-header d-flex" id="sectionHeading{{ $section->id }}">
        @if(!$isLinkedSection && !$isSyncMirror)
        <span class="sortable-handle d-flex align-items-center px-2 cursor-grab text-muted" title="اسحب لإعادة الترتيب"><i class="bi bi-grip-vertical"></i></span>
        @else
        <span class="d-flex align-items-center px-2 text-muted" style="width: 28px;"><i class="bi bi-link-45deg"></i></span>
        @endif
        <button class="accordion-button flex-grow-1 {{ $sectionIndex > 0 ? 'collapsed' : '' }}" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#sectionCollapse{{ $section->id }}"
                aria-expanded="{{ $sectionIndex === 0 ? 'true' : 'false' }}"
                aria-controls="sectionCollapse{{ $section->id }}"
                data-bs-parent="#{{ $parentAccordionId }}">
            <div class="d-flex align-items-center justify-content-between w-100 me-3">
                <div class="d-flex align-items-center">
                    <i class="bi {{ $levelIcon }} me-2" style="{{ $levelIconStyle }}"></i>
                    @if($isSyncMirror)
                        <span class="badge bg-info-transparent text-info me-2" style="font-size:0.7rem;" @if($sectionHomeOriginTitle !== '') title="{{ e($sectionHomeOriginTitle) }}" @endif>نسخة مرتبطة</span>
                    @elseif($isLinkedSection)
                        <span class="badge bg-info-transparent text-info me-2" style="font-size:0.7rem;" @if($sectionHomeOriginTitle !== '') title="{{ e($sectionHomeOriginTitle) }}" @endif>مرتبط بمادة أخرى</span>
                    @endif
                    @if($isChildSection)
                        <span class="badge bg-primary-transparent text-primary me-2" style="font-size:0.7rem;">قسم فرعي</span>
                    @elseif(!$isLinkedSection && !$isSyncMirror && $section->parent_id === null)
                        <span class="badge bg-secondary-transparent text-secondary me-2" style="font-size:0.7rem;">قسم رئيسي</span>
                    @endif
                    <span class="fw-semibold"><span class="sortable-index">{{ (int)($section->order ?? 0) + 1 }}</span> - {{ $section->title }}</span>
                    @if($section->is_active)
                        <span class="badge bg-success-transparent text-success ms-2">نشط</span>
                    @else
                        <span class="badge bg-secondary-transparent text-secondary ms-2">مخفي</span>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0 me-2">
                    <span class="badge bg-primary-transparent text-primary">
                        ترتيب: {{ $section->order }}
                    </span>
                    <span class="badge bg-info-transparent text-info" title="إجمالي الدروس المباشرة ودروس كل الوحدات في هذا القسم">
                        الدروس: {{ $sectionTotalLessonsCount }}
                    </span>
                    @if($sectionTotalDurationLabel)
                        @include('admin.pages.subjects.partials.admin-lesson-duration-badge', [
                            'duration' => $sectionTotalDurationLabel,
                            'size' => 'section',
                            'title' => 'مجموع مدة دروس القسم',
                        ])
                    @endif
                </div>
            </div>
        </button>
        <div class="d-flex align-items-center gap-1 pe-2 flex-shrink-0" onclick="event.stopPropagation()">
            @if(!$isLinkedSection && !$isSyncMirror && $linkedSubjectsPresenceCount > 0)
            <button type="button"
                    class="btn btn-sm btn-outline-info py-0 px-2 section-linked-presence-btn"
                    title="{{ e($linkedSubjectsPresenceTitle) }}">
                <i class="bi bi-box-arrow-up-right me-1"></i>تواجد {{ $linkedSubjectsPresenceCount }}
            </button>
            @endif
            @can('subject-section-edit')
            @if(!$isSyncMirror && !$isLinkedSection)
            <button type="button"
                    class="btn btn-sm btn-icon btn-info-transparent link-section-subjects-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#linkSectionSubjectsModal"
                    data-section-id="{{ $section->id }}"
                    data-section-title="{{ e($section->title) }}"
                    data-section-primary-subject-id="{{ $section->subject_id }}"
                    title="نسخ القسم في مواد أخرى (متزامن)">
                <i class="bi bi-link-45deg"></i>
            </button>
            @endif
            @endcan
            @if(!$isLinkedSection)
            @can('subject-section-edit')
            <button type="button"
                    class="btn btn-sm btn-icon btn-primary-transparent"
                    data-bs-toggle="modal"
                    data-bs-target="#editSection{{ $section->id }}"
                    title="تعديل القسم">
                <i class="bi bi-pencil"></i>
            </button>
            @endcan
            @can('subject-section-delete')
            <button type="button"
                    class="btn btn-sm btn-icon btn-danger-transparent"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteSection{{ $section->id }}"
                    title="حذف القسم">
                <i class="bi bi-trash"></i>
            </button>
            @endcan
            @endif
        </div>
    </h2>
    <div id="sectionCollapse{{ $section->id }}"
         class="accordion-collapse collapse"
         aria-labelledby="sectionHeading{{ $section->id }}"
         data-bs-parent="#{{ $parentAccordionId }}">
        <div class="accordion-body">
            {{-- وصف القسم --}}
            @if($section->description)
                <p class="text-muted mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    {{ $section->description }}
                </p>
            @endif

            {{-- الدروس المباشرة داخل القسم (بدون وحدة) --}}
            <div class="section-direct-lessons mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-muted small">
                        <i class="bi bi-play-circle me-1"></i>
                        الدروس المباشرة داخل القسم ({{ $directLessons->count() }})
                    </span>
                </div>
                @if($directLessons->isEmpty())
                    <p class="text-muted mb-0 mt-1" style="font-size: 0.75rem;">لا توجد دروس مباشرة في هذا القسم بعد</p>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($directLessons as $lesson)
                            <div class="list-group-item d-flex flex-column px-0 py-2" data-lesson-id="{{ $lesson->id }}">
                                <div class="d-flex align-items-center justify-content-between gap-2 w-100">
                                    <div class="d-flex align-items-center min-w-0 flex-grow-1">
                                        <div class="me-3 position-relative flex-shrink-0">
                                            @if($lesson->thumbnail)
                                                <img src="{{ media_public_url($lesson->thumbnail) }}" alt="{{ $lesson->title }}" class="rounded" style="width:60px;height:40px;object-fit:cover;">
                                            @else
                                                <div class="bg-danger-transparent text-danger rounded d-flex align-items-center justify-content-center" style="width:60px;height:40px;">
                                                    <i class="bi bi-play-circle fs-4"></i>
                                                </div>
                                            @endif
                                            @if($lesson->is_free)
                                                <span class="badge bg-success position-absolute top-0 start-0" style="font-size:0.6rem;">مجاني</span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <h6 class="mb-0 fw-semibold small">
                                                <span class="sortable-index">{{ $loop->iteration }}</span> - {{ $lesson->title }}
                                                @if($lesson->isSyncMirror())
                                                    <span class="badge bg-info-transparent text-info ms-1" style="font-size:0.65rem;">نسخة مرتبطة</span>
                                                @endif
                                                @if(!$lesson->is_active)
                                                    <span class="badge bg-secondary-transparent text-secondary ms-1">مخفي</span>
                                                @endif
                                                @if($lesson->review_status === 'pending_review')
                                                    <span class="badge bg-warning text-dark ms-1"><i class="bi bi-clock-history me-1"></i> قيد المراجعة</span>
                                                @elseif($lesson->review_status === 'rejected')
                                                    <span class="badge bg-danger ms-1"><i class="bi bi-x-circle me-1"></i> مرفوض</span>
                                                @endif
                                            </h6>
                                            <div class="d-flex align-items-center gap-2 mt-1">
                                                <span class="badge bg-{{ $lesson->video_type === 'youtube' ? 'danger' : ($lesson->video_type === 'vimeo' ? 'info' : 'primary') }}-transparent text-{{ $lesson->video_type === 'youtube' ? 'danger' : ($lesson->video_type === 'vimeo' ? 'info' : 'primary') }}" style="font-size:0.65rem;">
                                                    <i class="bi bi-{{ $lesson->video_type === 'youtube' ? 'youtube' : ($lesson->video_type === 'vimeo' ? 'vimeo' : 'film') }} me-1"></i>
                                                    {{ \App\Models\Lesson::VIDEO_TYPES[$lesson->video_type] ?? $lesson->video_type }}
                                                </span>
                                                @if($lesson->duration)
                                                    <span class="text-muted" style="font-size:0.7rem;"><i class="bi bi-clock me-1"></i>{{ $lesson->formatted_duration }}</span>
                                                @endif
                                                @if($lesson->attachments->count() > 0)
                                                    <span class="text-muted" style="font-size:0.7rem;"><i class="bi bi-paperclip me-1"></i>{{ $lesson->attachments->count() }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                        @can('lesson-show')
                                            <a href="{{ route('admin.lessons.show', $lesson->id) }}" class="btn btn-sm btn-icon btn-success-transparent" title="مشاهدة"><i class="bi bi-play-fill"></i></a>
                                        @endcan
                                        @can('lesson-show')
                                            @if($lesson->embed_url || $lesson->video_url)
                                            <button type="button" class="btn btn-sm btn-icon btn-warning-transparent" data-bs-toggle="modal" data-bs-target="#playVideoModal{{ $lesson->id }}" title="تشغيل الفيديو - معاينة سريعة"><i class="bi bi-play-circle"></i></button>
                                            @endif
                                        @endcan
                                        @can('lesson-attachment-create')
                                            <button type="button" class="btn btn-sm btn-icon btn-info-transparent" data-bs-toggle="modal" data-bs-target="#addLessonAttachment{{ $lesson->id }}" title="إضافة مرفقات"><i class="bi bi-paperclip"></i></button>
                                        @endcan
                                        @php
                                            $sectionLessonLinkedUnitsCount = 0;
                                            $sectionLessonLinkedUnitsTitle = '';
                                            if (! $lesson->isSyncMirror()) {
                                                $syncLinkedUnits = $lesson->linkedUnitsViaSync();
                                                if ($syncLinkedUnits->isNotEmpty()) {
                                                    $sectionLessonLinkedUnitsCount = $syncLinkedUnits->count();
                                                    $lines = [];
                                                    foreach ($syncLinkedUnits as $lu) {
                                                        $row = trim(implode(' — ', array_filter([
                                                            optional(optional(optional($lu->section)->subject)->schoolClass)->name,
                                                            optional(optional($lu->section)->subject)->name,
                                                            optional($lu->section)->title,
                                                            $lu->title,
                                                        ])));
                                                        if ($row !== '') {
                                                            $lines[] = $row;
                                                        }
                                                    }
                                                    $sectionLessonLinkedUnitsTitle = 'نسخة متزامنة من هذا الدرس في: '.implode(' | ', $lines);
                                                }
                                            }
                                            $sectionLessonLinkTooltip = '';
                                            if ($lesson->linkedUnits->isNotEmpty()) {
                                                $sectionLessonLinkParts = [];
                                                foreach ($lesson->linkedUnits as $lu) {
                                                    $row = trim(implode(' — ', array_filter([optional(optional($lu->section)->subject)->name, optional($lu->section)->title, $lu->title])));
                                                    if ($row !== '') {
                                                        $sectionLessonLinkParts[] = 'يظهر أيضاً في (ربط قديم): ' . $row;
                                                    }
                                                }
                                                $sectionLessonLinkTooltip = implode(' ', array_filter($sectionLessonLinkParts));
                                            }
                                        @endphp
                                        @if(! $lesson->isSyncMirror() && $sectionLessonLinkedUnitsCount > 0)
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-info py-0 px-2 lesson-linked-presence-btn"
                                                    title="{{ e($sectionLessonLinkedUnitsTitle) }}">
                                                <i class="bi bi-box-arrow-up-right me-1"></i>تواجد {{ $sectionLessonLinkedUnitsCount }}
                                            </button>
                                        @endif
                                        @if($lesson->linkedUnits->isNotEmpty())
                                            <span class="btn btn-sm btn-icon btn-outline-secondary border-secondary-subtle text-secondary lesson-cross-links-indicator"
                                                  role="img"
                                                  tabindex="0"
                                                  style="cursor: help;"
                                                  title="{{ e($sectionLessonLinkTooltip) }}">
                                                <i class="bi bi-diagram-3"></i>
                                            </span>
                                        @endif
                                        @can('lesson-edit')
                                            @if(!$lesson->isSyncMirror())
                                            <button type="button"
                                                    class="btn btn-sm btn-icon btn-info-transparent link-lesson-units-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#linkLessonUnitsModal"
                                                    data-lesson-id="{{ $lesson->id }}"
                                                    data-lesson-title="{{ e($lesson->title) }}"
                                                    data-lesson-primary-unit-id="{{ $lesson->unit_id ?? '' }}"
                                                    title="نسخ الدرس في وحدات أخرى (متزامن)">
                                                <i class="bi bi-link-45deg"></i>
                                            </button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-icon btn-primary-transparent" data-bs-toggle="modal" data-bs-target="#editLesson{{ $lesson->id }}" title="تعديل"><i class="bi bi-pencil"></i></button>
                                        @endcan
                                        @can('lesson-delete')
                                            <button type="button" class="btn btn-sm btn-icon btn-danger-transparent" data-bs-toggle="modal" data-bs-target="#deleteLesson{{ $lesson->id }}" title="حذف"><i class="bi bi-trash"></i></button>
                                        @endcan
                                        @if($lesson->review_status === 'pending_review')
                                            @canany(['lesson-approve-review', 'lesson-reject-review'])
                                            <div class="btn-group btn-group-sm ms-2">
                                                @can('lesson-approve-review')
                                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveLesson{{ $lesson->id }}" title="موافقة"><i class="bi bi-check-circle"></i></button>
                                                @endcan
                                                @can('lesson-reject-review')
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectLesson{{ $lesson->id }}" title="رفض"><i class="bi bi-x-circle"></i></button>
                                                @endcan
                                            </div>
                                            @endcanany
                                        @endif
                                        @can('quiz-create')
                                            <a href="{{ route('admin.quizzes.create', ['subject_id' => $subject->id, 'section_id' => $section->id, 'lesson_id' => $lesson->id, 'scope' => 'lesson']) }}" class="btn btn-sm btn-outline-info" title="اختبار لهذا الدرس"><i class="bi bi-clipboard-check me-1"></i> اختبار الدرس</a>
                                        @endcan
                                        @if($lesson->quizzes && $lesson->quizzes->count() > 0)
                                            @php $firstQuiz = $lesson->quizzes->first(); @endphp
                                            @can('quiz-show')
                                                <a href="{{ route('admin.quizzes.show', $firstQuiz->id) }}" class="btn btn-sm btn-icon btn-info-transparent" title="{{ $firstQuiz->title }}"><i class="bi bi-question-circle"></i></a>
                                            @endcan
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- الوحدات داخل القسم --}}
            <div class="section-units">
                @php
                    $unitsForSectionDisplay = $section->rootUnitsForDisplay();
                    $sectionRootHasMirroredUnit = $unitsForSectionDisplay->contains(fn ($u) => (int) $u->section_id !== (int) $section->id);
                @endphp
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <span class="text-muted small">
                        <i class="bi bi-layers me-1"></i>
                        الوحدات ({{ $unitsForSectionDisplay->count() }})
                    </span>
                    @if(!$isLinkedSection)
                    <div class="d-flex align-items-center gap-2">
                        @can('lesson-create')
                        <button type="button"
                                class="btn btn-sm btn-outline-success"
                                data-bs-toggle="modal"
                                data-bs-target="#createSectionLessonModal{{ $section->id }}">
                            <i class="bi bi-play-circle me-1"></i> إضافة درس مباشر
                        </button>
                        @endcan
                        @can('quiz-create')
                        <a href="{{ route('admin.quizzes.create', ['subject_id' => $subject->id, 'section_id' => $section->id, 'scope' => 'section']) }}"
                                class="btn btn-sm btn-outline-info"
                                title="إضافة اختبار مباشر للقسم">
                            <i class="bi bi-clipboard-check me-1"></i> إضافة اختبار مباشر
                        </a>
                        @endcan
                        @can('unit-create')
                        <button type="button"
                                class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#createUnitModal{{ $section->id }}">
                            <i class="bi bi-plus-lg me-1"></i> إضافة قسم لرفع الدروس
                        </button>
                        @endcan
                        @can('subject-section-create')
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary add-child-section-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#createSectionModal"
                                data-parent-id="{{ $section->id }}"
                                data-parent-title="{{ e($section->title) }}"
                                title="إضافة قسم فرعي داخل هذا القسم">
                            <i class="bi bi-folder-plus me-1"></i> إضافة قسم فرعي
                        </button>
                        @endcan
                    </div>
                    @endif
                </div>

                @if($unitsForSectionDisplay->isEmpty())
                    <p class="text-muted mb-0 mt-1" style="font-size: 0.75rem;">لا توجد وحدات في هذا القسم بعد</p>
                @else
                    {{-- Accordion للوحدات (جذر منزلية + وحدات مرآة من أقسام أخرى) --}}
                    {{-- MVP: إعادة ترتيب السحب تُحدّث units.order للمنزل فقط؛ عند وجود جذور مرآة يُعطّل السحب حتى لا يُرسل ترتيب خاطئ (ترتيب pivot لاحقاً). --}}
                    <div class="accordion accordion-secondary" id="unitsAccordion{{ $section->id }}"
                        @unless($sectionRootHasMirroredUnit)
                        data-sortable="units" data-section-id="{{ $section->id }}" data-parent-id="" data-reorder-url="{{ route('admin.sections.units.reorder', $section) }}"
                        @endunless
                        >
                        @foreach($unitsForSectionDisplay->values() as $unitIndex => $unit)
                            @include('admin.pages.subjects.partials.unit-item', [
                                'unit' => $unit,
                                'allUnits' => $unit->section->units,
                                'section' => $section,
                                'subject' => $subject,
                                'unitIndex' => $unitIndex,
                                'parentUnitsAccordionId' => 'unitsAccordion' . $section->id,
                                'isMirroredInThisSection' => (int) $unit->section_id !== (int) $section->id,
                            ])
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- الأقسام الفرعية --}}
            @if($childSections->isNotEmpty())
                <div class="section-children mt-4 pt-3 border-top">
                    <div class="mb-2 pb-2 border-bottom">
                        <span class="text-muted small">
                            <i class="bi bi-folder2 me-1"></i> الأقسام الفرعية ({{ $childSections->count() }})
                        </span>
                    </div>
                    <div class="accordion accordion-primary" id="childSectionsAccordion{{ $section->id }}" data-sortable="sections" data-subject-id="{{ $subject->id }}" data-parent-id="{{ $section->id }}" data-reorder-url="{{ route('admin.subjects.sections.reorder', $subject) }}">
                        @foreach($childSections->values() as $childIndex => $childSection)
                            @include('admin.pages.subjects.partials.section-item', [
                                'section' => $childSection,
                                'allSections' => $allSections,
                                'subject' => $subject,
                                'sectionIndex' => $childIndex,
                                'parentAccordionId' => 'childSectionsAccordion' . $section->id,
                                'sectionLevel' => min(5, $level + 1),
                            ])
                        @endforeach
                    </div>
                </div>
            @else
                <div class="section-children mt-4 pt-3 border-top">
                    <div class="mb-2 pb-2 border-bottom">
                        <span class="text-muted small">
                            <i class="bi bi-folder2 me-1"></i> الأقسام الفرعية
                        </span>
                    </div>
                    <div class="text-center py-3 text-muted small">
                        لا أقسام فرعية
                        @can('subject-section-create')
                        @if(!$isLinkedSection)
                        <div class="mt-2">
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary add-child-section-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#createSectionModal"
                                    data-parent-id="{{ $section->id }}"
                                    data-parent-title="{{ e($section->title) }}"
                                    title="إضافة قسم فرعي داخل هذا القسم">
                                <i class="bi bi-folder-plus me-1"></i> إضافة قسم فرعي
                            </button>
                        </div>
                        @endif
                        @endcan
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
