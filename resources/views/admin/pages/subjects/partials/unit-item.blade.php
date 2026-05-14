@php
    $childUnits = $allUnits->where('parent_id', $unit->id)->sortBy('order');
    $isChildUnit = $unit->parent_id !== null;
    $isMirroredInThisSection = $isMirroredInThisSection ?? false;
    $homeSectionId = $unit->section_id;
@endphp
<div class="accordion-item border rounded mb-2 shadow-sm unit-item {{ $isChildUnit ? 'unit-item-child' : 'unit-item-root' }}" data-id="{{ $unit->id }}" data-home-section-id="{{ $homeSectionId }}">
    <h2 class="accordion-header d-flex" id="unitHeading{{ $unit->id }}">
        @if($isMirroredInThisSection)
            <span class="d-flex align-items-center px-2 text-muted" style="width: 28px;" title="ظهور مرتبط — القسم المنزل: {{ $unit->section->title ?? '' }}"><i class="bi bi-link-45deg"></i></span>
        @else
            <span class="sortable-handle d-flex align-items-center px-2 cursor-grab text-muted" title="اسحب لإعادة الترتيب"><i class="bi bi-grip-vertical"></i></span>
        @endif
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
                        @if($unit->mirroredInSections->isNotEmpty())
                            <span class="btn btn-sm btn-icon btn-outline-secondary border-secondary-subtle text-secondary ms-1 p-0" style="cursor: help; min-width: 2rem;" role="img"
                                  title="@foreach($unit->mirroredInSections as $mis)يظهر أيضاً في: {{ $mis->subject->name ?? '' }} — {{ $mis->path_title }}@if(!$loop->last) | @endif @endforeach">
                                <i class="bi bi-diagram-3"></i>
                            </span>
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
            @can('unit-edit')
            <button type="button"
                    class="btn btn-sm btn-icon btn-primary-transparent"
                    data-bs-toggle="modal"
                    data-bs-target="#editUnit{{ $unit->id }}"
                    title="تعديل الوحدة">
                <i class="bi bi-pencil"></i>
            </button>
            @endcan
            @can('unit-delete')
            <button type="button"
                    class="btn btn-sm btn-icon btn-danger-transparent"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteUnit{{ $unit->id }}"
                    title="حذف الوحدة">
                <i class="bi bi-trash"></i>
            </button>
            @endcan
        </div>
    </h2>
    <div id="unitCollapse{{ $unit->id }}"
         class="accordion-collapse collapse"
         aria-labelledby="unitHeading{{ $unit->id }}"
         data-bs-parent="#{{ $parentUnitsAccordionId }}">
        <div class="accordion-body pt-0">
            @include('admin.pages.subjects.partials.unit-content', ['unit' => $unit, 'subject' => $subject])

            {{-- زر إضافة قسم لرفع الدروس (فرعي تحت وحدة) — دائماً عبر القسم المنزل للوحدة --}}
            <div class="d-flex align-items-center gap-2 mt-3 pt-2 border-top">
                @can('unit-create')
                <button type="button"
                        class="btn btn-sm btn-outline-secondary add-child-unit-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#createUnitModal{{ $homeSectionId }}"
                        data-parent-id="{{ $unit->id }}"
                        data-section-id="{{ $homeSectionId }}"
                        title="إضافة قسم لرفع الدروس">
                    <i class="bi bi-layers"></i> إضافة قسم لرفع الدروس
                </button>
                @endcan
            </div>

            {{-- الوحدات الفرعية --}}
            @if($childUnits->isNotEmpty())
                <div class="unit-children mt-4 pt-3 border-top">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-muted small">
                            <i class="bi bi-layers me-1"></i> الوحدات الفرعية ({{ $childUnits->count() }})
                        </span>
                        @can('unit-create')
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary add-child-unit-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#createUnitModal{{ $homeSectionId }}"
                                data-parent-id="{{ $unit->id }}"
                                data-section-id="{{ $homeSectionId }}"
                                title="إضافة قسم لرفع الدروس">
                            <i class="bi bi-layers"></i> إضافة قسم لرفع الدروس
                        </button>
                        @endcan
                    </div>
                    @php
                        $childAccordionHasMirror = $childUnits->contains(fn ($cu) => (int) $cu->section_id !== (int) $section->id);
                    @endphp
                    <div class="accordion accordion-secondary" id="childUnitsAccordion{{ $unit->id }}"
                        @unless($childAccordionHasMirror)
                        data-sortable="units" data-section-id="{{ $homeSectionId }}" data-parent-id="{{ $unit->id }}" data-reorder-url="{{ route('admin.sections.units.reorder', $unit->section) }}"
                        @endunless
                        >
                        @foreach($childUnits->values() as $childIndex => $childUnit)
                            @include('admin.pages.subjects.partials.unit-item', [
                                'unit' => $childUnit,
                                'allUnits' => $allUnits,
                                'section' => $section,
                                'subject' => $subject,
                                'unitIndex' => $childIndex,
                                'parentUnitsAccordionId' => 'childUnitsAccordion' . $unit->id,
                                'isMirroredInThisSection' => (int) $childUnit->section_id !== (int) $section->id,
                            ])
                        @endforeach
                    </div>
                </div>
            @else
                <div class="unit-children mt-3 pt-2 border-top">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small"><i class="bi bi-layers me-1"></i> الوحدات الفرعية</span>
                        @can('unit-create')
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary add-child-unit-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#createUnitModal{{ $homeSectionId }}"
                                data-parent-id="{{ $unit->id }}"
                                data-section-id="{{ $homeSectionId }}"
                                title="إضافة قسم لرفع الدروس">
                            <i class="bi bi-layers"></i> إضافة قسم لرفع الدروس
                        </button>
                        @endcan
                    </div>
                    <div class="text-center py-2 text-muted small">لا وحدات فرعية</div>
                </div>
            @endif
        </div>
    </div>
</div>
