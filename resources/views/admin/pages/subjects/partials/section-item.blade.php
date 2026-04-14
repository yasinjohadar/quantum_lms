@php
    $directLessons = \App\Models\Lesson::query()
        ->where('section_id', $section->id)
        ->whereNull('unit_id')
        ->orderBy('order')
        ->with(['attachments', 'quizzes'])
        ->get();
    $childSections = $allSections->where('parent_id', $section->id)->sortBy('order');
    $isChildSection = $section->parent_id !== null;
    $isLinkedSection = $section->subject_id != $subject->id;
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
@endphp
<div class="accordion-item mb-3 rounded overflow-hidden section-level-{{ $level }}{{ $isLinkedSection ? ' section-item-linked' : '' }}" data-id="{{ $section->id }}" data-section-id="{{ $section->id }}">
    <h2 class="accordion-header d-flex" id="sectionHeading{{ $section->id }}">
        @if(!$isLinkedSection)
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
                    @if($isLinkedSection)
                        <span class="badge bg-info-transparent text-info me-2" style="font-size:0.7rem;">مرتبط بمادة أخرى</span>
                    @endif
                    @if($isChildSection)
                        <span class="badge bg-primary-transparent text-primary me-2" style="font-size:0.7rem;">قسم فرعي</span>
                    @endif
                    <span class="fw-semibold"><span class="sortable-index">{{ (int)($section->order ?? 0) + 1 }}</span> - {{ $section->title }}</span>
                    @if($section->is_active)
                        <span class="badge bg-success-transparent text-success ms-2">نشط</span>
                    @else
                        <span class="badge bg-secondary-transparent text-secondary ms-2">مخفي</span>
                    @endif
                </div>
                <span class="badge bg-primary-transparent text-primary me-2">
                    ترتيب: {{ $section->order }}
                </span>
            </div>
        </button>
        <div class="d-flex align-items-center gap-1 pe-2 flex-shrink-0" onclick="event.stopPropagation()">
            @can('subject-section-edit')
            <button type="button"
                    class="btn btn-sm btn-icon btn-info-transparent link-section-subjects-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#linkSectionSubjectsModal"
                    data-section-id="{{ $section->id }}"
                    data-section-title="{{ e($section->title) }}"
                    data-section-primary-subject-id="{{ $section->subject_id }}"
                    title="ربط القسم بمواد أخرى">
                <i class="bi bi-link-45deg"></i>
            </button>
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
                                                <img src="{{ asset('storage/'.$lesson->thumbnail) }}" alt="{{ $lesson->title }}" class="rounded" style="width:60px;height:40px;object-fit:cover;">
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
                                        @can('lesson-edit')
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
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <span class="text-muted small">
                        <i class="bi bi-layers me-1"></i>
                        الوحدات ({{ $section->units->count() }})
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
                                title="اضافة قسم تنظيمي">
                            <i class="bi bi-folder-plus me-1"></i> اضافة قسم تنظيمي
                        </button>
                        @endcan
                    </div>
                    @endif
                </div>

                @php
                    $rootUnits = $section->units->whereNull('parent_id')->sortBy('order');
                @endphp
                @if($section->units->count() === 0)
                    <p class="text-muted mb-0 mt-1" style="font-size: 0.75rem;">لا توجد وحدات في هذا القسم بعد</p>
                @else
                    {{-- Accordion للوحدات (جذر فقط، الأبناء داخل unit-item) --}}
                    <div class="accordion accordion-secondary" id="unitsAccordion{{ $section->id }}" data-sortable="units" data-section-id="{{ $section->id }}" data-parent-id="" data-reorder-url="{{ route('admin.sections.units.reorder', $section) }}">
                        @foreach($rootUnits->values() as $unitIndex => $unit)
                            @include('admin.pages.subjects.partials.unit-item', [
                                'unit' => $unit,
                                'allUnits' => $section->units,
                                'section' => $section,
                                'subject' => $subject,
                                'unitIndex' => $unitIndex,
                                'parentUnitsAccordionId' => 'unitsAccordion' . $section->id,
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
                    <div class="text-center py-3 text-muted small">لا أقسام فرعية</div>
                </div>
            @endif
        </div>
    </div>
</div>
