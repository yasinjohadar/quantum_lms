@php
    $childSections = $allSections->where('parent_id', $section->id)->sortBy('order');
    $isChildSection = $section->parent_id !== null;
    $isLinkedSection = $section->subject_id != $subject->id;
    $level = (int) ($sectionLevel ?? 0);
    $levelIcons = ['bi-folder-fill', 'bi-folder2', 'bi-folder-symlink-fill', 'bi-journal-bookmark', 'bi-collection-fill', 'bi-journal-text'];
    $levelIcon = $levelIcons[min($level, 5)] ?? 'bi-folder-fill';
    $levelColorClasses = ['text-primary', 'text-info', 'text-danger', 'text-success', 'text-warning', 'text-secondary'];
    $levelColorClass = $levelColorClasses[min($level, 5)] ?? 'text-primary';
@endphp
<div class="accordion-item mb-3 rounded overflow-hidden section-level-{{ $level }}{{ $isLinkedSection ? ' section-item-linked' : '' }}" data-id="{{ $section->id }}">
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
                    <i class="bi {{ $levelIcon }} {{ $levelColorClass }} me-2"></i>
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

            {{-- الوحدات داخل القسم --}}
            <div class="section-units">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <span class="text-muted small">
                        <i class="bi bi-layers me-1"></i>
                        الوحدات ({{ $section->units->count() }})
                    </span>
                    @if(!$isLinkedSection)
                    <div class="d-flex align-items-center gap-2">
                        @can('unit-create')
                        <button type="button"
                                class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#createUnitModal{{ $section->id }}">
                            <i class="bi bi-plus-lg me-1"></i> إضافة وحدة
                        </button>
                        @endcan
                        @can('subject-section-create')
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary add-child-section-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#createSectionModal"
                                data-parent-id="{{ $section->id }}"
                                title="إضافة قسم فرعي">
                            <i class="bi bi-folder-plus me-1"></i> إضافة قسم فرعي
                        </button>
                        @endcan
                    </div>
                    @endif
                </div>

                @php
                    $rootUnits = $section->units->whereNull('parent_id')->sortBy('order');
                @endphp
                @if($section->units->count() === 0)
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-6 d-block mb-2"></i>
                        <span class="small">لا توجد وحدات في هذا القسم بعد</span>
                    </div>
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
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-muted small">
                            <i class="bi bi-folder2 me-1"></i> الأقسام الفرعية ({{ $childSections->count() }})
                        </span>
                        @if(!$isLinkedSection)
                        @can('subject-section-create')
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary add-child-section-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#createSectionModal"
                                data-parent-id="{{ $section->id }}"
                                title="إضافة قسم فرعي">
                            <i class="bi bi-folder-plus me-1"></i> إضافة قسم فرعي
                        </button>
                        @endcan
                        @endif
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
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-muted small">
                            <i class="bi bi-folder2 me-1"></i> الأقسام الفرعية
                        </span>
                        @if(!$isLinkedSection)
                        @can('subject-section-create')
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary add-child-section-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#createSectionModal"
                                data-parent-id="{{ $section->id }}"
                                title="إضافة قسم فرعي">
                            <i class="bi bi-folder-plus me-1"></i> إضافة قسم فرعي
                        </button>
                        @endcan
                        @endif
                    </div>
                    <div class="text-center py-3 text-muted small">لا أقسام فرعية</div>
                </div>
            @endif
        </div>
    </div>
</div>
