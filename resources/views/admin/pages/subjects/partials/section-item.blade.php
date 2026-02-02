@php
    $childSections = $allSections->where('parent_id', $section->id)->sortBy('order');
    $isChildSection = $section->parent_id !== null;
@endphp
<div class="accordion-item mb-3 rounded overflow-hidden {{ $isChildSection ? 'section-item-child border-start border-primary border-3 bg-primary-transparent' : '' }}" data-id="{{ $section->id }}">
    <h2 class="accordion-header d-flex" id="sectionHeading{{ $section->id }}">
        <span class="sortable-handle d-flex align-items-center px-2 cursor-grab text-muted" title="اسحب لإعادة الترتيب"><i class="bi bi-grip-vertical"></i></span>
        <button class="accordion-button flex-grow-1 {{ $sectionIndex > 0 ? 'collapsed' : '' }}" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#sectionCollapse{{ $section->id }}"
                aria-expanded="{{ $sectionIndex === 0 ? 'true' : 'false' }}"
                aria-controls="sectionCollapse{{ $section->id }}"
                data-bs-parent="#{{ $parentAccordionId }}">
            <div class="d-flex align-items-center justify-content-between w-100 me-3">
                <div class="d-flex align-items-center">
                    <i class="bi bi-folder-fill text-primary me-2"></i>
                    @if($isChildSection)
                        <span class="badge bg-primary-transparent text-primary me-2" style="font-size:0.7rem;">قسم فرعي</span>
                    @endif
                    <span class="fw-semibold"><span class="sortable-index">{{ ($sectionIndex ?? 0) + 1 }}</span> - {{ $section->title }}</span>
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
            <button type="button"
                    class="btn btn-sm btn-icon btn-primary-transparent"
                    data-bs-toggle="modal"
                    data-bs-target="#editSection{{ $section->id }}"
                    title="تعديل القسم">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button"
                    class="btn btn-sm btn-icon btn-danger-transparent"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteSection{{ $section->id }}"
                    title="حذف القسم">
                <i class="bi bi-trash"></i>
            </button>
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
                    <div class="d-flex align-items-center gap-2">
                        <button type="button"
                                class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#createUnitModal{{ $section->id }}">
                            <i class="bi bi-plus-lg me-1"></i> إضافة وحدة
                        </button>
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary add-child-section-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#createSectionModal"
                                data-parent-id="{{ $section->id }}"
                                title="إضافة قسم فرعي">
                            <i class="bi bi-folder-plus me-1"></i> إضافة قسم فرعي
                        </button>
                    </div>
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
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary add-child-section-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#createSectionModal"
                                data-parent-id="{{ $section->id }}"
                                title="إضافة قسم فرعي">
                            <i class="bi bi-folder-plus me-1"></i> إضافة قسم فرعي
                        </button>
                    </div>
                    <div class="accordion accordion-primary" id="childSectionsAccordion{{ $section->id }}" data-sortable="sections" data-subject-id="{{ $subject->id }}" data-parent-id="{{ $section->id }}" data-reorder-url="{{ route('admin.subjects.sections.reorder', $subject) }}">
                        @foreach($childSections->values() as $childIndex => $childSection)
                            @include('admin.pages.subjects.partials.section-item', [
                                'section' => $childSection,
                                'allSections' => $allSections,
                                'subject' => $subject,
                                'sectionIndex' => $childIndex,
                                'parentAccordionId' => 'childSectionsAccordion' . $section->id,
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
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary add-child-section-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#createSectionModal"
                                data-parent-id="{{ $section->id }}"
                                title="إضافة قسم فرعي">
                            <i class="bi bi-folder-plus me-1"></i> إضافة قسم فرعي
                        </button>
                    </div>
                    <div class="text-center py-3 text-muted small">لا أقسام فرعية</div>
                </div>
            @endif
        </div>
    </div>
</div>
