@php
    $childUnits = $allUnits->where('parent_id', $unit->id)->sortBy('order');
    $isChildUnit = $unit->parent_id !== null;
@endphp
<div class="accordion-item border rounded mb-2 shadow-sm {{ $isChildUnit ? 'unit-item-child border-start border-info border-3 bg-info-transparent' : '' }}" data-id="{{ $unit->id }}">
    <h2 class="accordion-header d-flex" id="unitHeading{{ $unit->id }}">
        <span class="sortable-handle d-flex align-items-center px-2 cursor-grab text-muted" title="اسحب لإعادة الترتيب"><i class="bi bi-grip-vertical"></i></span>
        <button class="accordion-button collapsed flex-grow-1 py-3" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#unitCollapse{{ $unit->id }}"
                aria-expanded="false"
                aria-controls="unitCollapse{{ $unit->id }}"
                data-bs-parent="#{{ $parentUnitsAccordionId }}">
            <div class="d-flex align-items-center w-100 me-3">
                <div class="me-3">
                    <div class="avatar avatar-md bg-info-transparent text-info rounded">
                        <i class="bi bi-journal-text fs-5"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center">
                        @if($isChildUnit)
                            <span class="badge bg-info-transparent text-info me-2" style="font-size:0.7rem;">وحدة فرعية</span>
                        @endif
                        <span class="fw-semibold">{{ ($unitIndex ?? 0) + 1 }} - {{ $unit->title }}</span>
                        @if($unit->is_active)
                            <span class="badge bg-success-transparent text-success ms-2">نشط</span>
                        @else
                            <span class="badge bg-secondary-transparent text-secondary ms-2">مخفي</span>
                        @endif
                    </div>
                    @if($unit->description)
                        <p class="text-muted small mb-0 mt-1">{{ Str::limit($unit->description, 60) }}</p>
                    @endif
                </div>
                <div class="me-3">
                    <span class="badge bg-info-transparent text-info">
                        <i class="bi bi-play-circle me-1"></i> {{ $unit->allLessons()->count() }} درس
                    </span>
                </div>
            </div>
        </button>
        <div class="d-flex align-items-center gap-1 pe-2 flex-shrink-0" onclick="event.stopPropagation()">
            <button type="button"
                    class="btn btn-sm btn-icon btn-primary-transparent"
                    data-bs-toggle="modal"
                    data-bs-target="#editUnit{{ $unit->id }}"
                    title="تعديل الوحدة">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button"
                    class="btn btn-sm btn-icon btn-danger-transparent"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteUnit{{ $unit->id }}"
                    title="حذف الوحدة">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </h2>
    <div id="unitCollapse{{ $unit->id }}"
         class="accordion-collapse collapse"
         aria-labelledby="unitHeading{{ $unit->id }}"
         data-bs-parent="#{{ $parentUnitsAccordionId }}">
        <div class="accordion-body pt-0">
            @include('admin.pages.subjects.partials.unit-content', ['unit' => $unit, 'subject' => $subject])

            {{-- زر إضافة وحدة فرعية --}}
            <div class="d-flex align-items-center gap-2 mt-3 pt-2 border-top">
                <button type="button"
                        class="btn btn-sm btn-outline-secondary add-child-unit-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#createUnitModal{{ $section->id }}"
                        data-parent-id="{{ $unit->id }}"
                        data-section-id="{{ $section->id }}"
                        title="إضافة وحدة فرعية">
                    <i class="bi bi-layers"></i> إضافة وحدة فرعية
                </button>
            </div>

            {{-- الوحدات الفرعية --}}
            @if($childUnits->isNotEmpty())
                <div class="unit-children mt-4 pt-3 border-top">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-muted small">
                            <i class="bi bi-layers me-1"></i> الوحدات الفرعية ({{ $childUnits->count() }})
                        </span>
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary add-child-unit-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#createUnitModal{{ $section->id }}"
                                data-parent-id="{{ $unit->id }}"
                                data-section-id="{{ $section->id }}"
                                title="إضافة وحدة فرعية">
                            <i class="bi bi-layers"></i> إضافة وحدة فرعية
                        </button>
                    </div>
                    <div class="accordion accordion-secondary" id="childUnitsAccordion{{ $unit->id }}" data-sortable="units" data-section-id="{{ $section->id }}" data-parent-id="{{ $unit->id }}" data-reorder-url="{{ route('admin.sections.units.reorder', $section) }}">
                        @foreach($childUnits->values() as $childIndex => $childUnit)
                            @include('admin.pages.subjects.partials.unit-item', [
                                'unit' => $childUnit,
                                'allUnits' => $allUnits,
                                'section' => $section,
                                'subject' => $subject,
                                'unitIndex' => $childIndex,
                                'parentUnitsAccordionId' => 'childUnitsAccordion' . $unit->id,
                            ])
                        @endforeach
                    </div>
                </div>
            @else
                <div class="unit-children mt-3 pt-2 border-top">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small"><i class="bi bi-layers me-1"></i> الوحدات الفرعية</span>
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary add-child-unit-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#createUnitModal{{ $section->id }}"
                                data-parent-id="{{ $unit->id }}"
                                data-section-id="{{ $section->id }}"
                                title="إضافة وحدة فرعية">
                            <i class="bi bi-layers"></i> إضافة وحدة فرعية
                        </button>
                    </div>
                    <div class="text-center py-2 text-muted small">لا وحدات فرعية</div>
                </div>
            @endif
        </div>
    </div>
</div>
