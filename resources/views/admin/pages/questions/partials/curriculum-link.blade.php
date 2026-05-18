{{-- ربط السؤال بالمنهج: صف → مادة → وحدة (قوائم مترابطة) --}}
<div class="card custom-card mb-3" id="questionCurriculumLink"
     data-subjects-url="{{ url('admin/questions/ajax/classes') }}"
     data-units-url="{{ url('admin/questions/ajax/subjects') }}">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-diagram-3 me-2"></i> الربط بالمنهج (اختياري)</h6>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            <i class="bi bi-info-circle me-1"></i>
            اختر الصف ثم المادة ثم الوحدة، واضغط «إضافة». اترك القائمة فارغة ليكون السؤال <strong>سؤالاً عاماً</strong>.
        </p>

        @if(isset($preselectedUnit) && $preselectedUnit)
            <div class="alert alert-info small mb-3">
                <i class="bi bi-pin-angle me-1"></i>
                تم تحديد الوحدة مسبقاً من السياق الحالي.
            </div>
        @endif

        <div class="mb-3">
            <label for="link_class_id" class="form-label small text-muted mb-1">الصف</label>
            <select class="form-select form-select-sm" id="link_class_id">
                <option value="">— اختر الصف —</option>
                @foreach($schoolClasses as $schoolClass)
                    <option value="{{ $schoolClass->id }}">{{ $schoolClass->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="link_subject_id" class="form-label small text-muted mb-1">المادة</label>
            <select class="form-select form-select-sm" id="link_subject_id" disabled>
                <option value="">اختر الصف أولاً</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="link_unit_picker" class="form-label small text-muted mb-1">الوحدة</label>
            <select class="form-select form-select-sm" id="link_unit_picker" disabled>
                <option value="">اختر المادة أولاً</option>
            </select>
        </div>

        <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-3" id="addLinkedUnitBtn" disabled>
            <i class="bi bi-plus-lg me-1"></i> إضافة الوحدة
        </button>

        <div id="linkedUnitsContainer">
            <p class="text-muted small mb-2 d-none" id="linkedUnitsEmpty">
                <i class="bi bi-globe me-1"></i> لا توجد وحدات مرتبطة — سؤال عام
            </p>
            <div id="linkedUnitsList">
                @foreach($linkedUnits as $unit)
                    @php
                        $subject = $unit->section?->subject;
                        $schoolClass = $subject?->schoolClass;
                    @endphp
                    <div class="linked-unit-row border rounded p-2 mb-2 bg-light"
                         data-unit-id="{{ $unit->id }}"
                         data-class-name="{{ $schoolClass?->name ?? '' }}"
                         data-subject-name="{{ $subject?->name ?? '' }}"
                         data-unit-title="{{ $unit->title }}">
                        <input type="hidden" name="units[]" value="{{ $unit->id }}">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <table class="table table-sm table-borderless mb-0 flex-grow-1 question-card-curriculum">
                                <thead>
                                    <tr class="text-muted">
                                        <th class="ps-0 py-0 fw-normal">الصف</th>
                                        <th class="py-0 fw-normal">المادة</th>
                                        <th class="pe-0 py-0 fw-normal">الوحدة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="ps-0 py-0"><span class="fw-semibold">{{ $schoolClass?->name ?: '—' }}</span></td>
                                        <td class="py-0"><span class="fw-semibold">{{ $subject?->name ?: '—' }}</span></td>
                                        <td class="pe-0 py-0"><span class="text-primary fw-semibold">{{ $unit->title }}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-linked-unit flex-shrink-0" title="إزالة">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var root = document.getElementById('questionCurriculumLink');
    if (!root) return;

    var classSelect = document.getElementById('link_class_id');
    var subjectSelect = document.getElementById('link_subject_id');
    var unitPicker = document.getElementById('link_unit_picker');
    var addBtn = document.getElementById('addLinkedUnitBtn');
    var listEl = document.getElementById('linkedUnitsList');
    var emptyEl = document.getElementById('linkedUnitsEmpty');
    var subjectsBase = root.dataset.subjectsUrl;
    var unitsBase = root.dataset.unitsUrl;

    function updateEmptyState() {
        var hasRows = listEl.querySelectorAll('.linked-unit-row').length > 0;
        emptyEl.classList.toggle('d-none', hasRows);
    }

    function resetSubjects() {
        subjectSelect.disabled = true;
        subjectSelect.innerHTML = '<option value="">اختر الصف أولاً</option>';
        resetUnits();
    }

    function resetUnits() {
        unitPicker.disabled = true;
        unitPicker.innerHTML = '<option value="">اختر المادة أولاً</option>';
        addBtn.disabled = true;
    }

    function selectedOptionText(selectEl) {
        if (!selectEl || !selectEl.value) return '';
        var opt = selectEl.options[selectEl.selectedIndex];
        return opt ? opt.textContent.trim() : '';
    }

    function unitAlreadyLinked(unitId) {
        return listEl.querySelector('.linked-unit-row[data-unit-id="' + unitId + '"]') !== null;
    }

    function appendLinkedUnit(unitId, className, subjectName, unitTitle) {
        var row = document.createElement('div');
        row.className = 'linked-unit-row border rounded p-2 mb-2 bg-light';
        row.dataset.unitId = unitId;
        row.dataset.className = className;
        row.dataset.subjectName = subjectName;
        row.dataset.unitTitle = unitTitle;
        row.innerHTML =
            '<input type="hidden" name="units[]" value="' + unitId + '">' +
            '<div class="d-flex justify-content-between align-items-start gap-2">' +
            '<table class="table table-sm table-borderless mb-0 flex-grow-1 question-card-curriculum">' +
            '<thead><tr class="text-muted">' +
            '<th class="ps-0 py-0 fw-normal">الصف</th>' +
            '<th class="py-0 fw-normal">المادة</th>' +
            '<th class="pe-0 py-0 fw-normal">الوحدة</th>' +
            '</tr></thead><tbody><tr>' +
            '<td class="ps-0 py-0"><span class="fw-semibold">' + (className || '—') + '</span></td>' +
            '<td class="py-0"><span class="fw-semibold">' + (subjectName || '—') + '</span></td>' +
            '<td class="pe-0 py-0"><span class="text-primary fw-semibold">' + unitTitle + '</span></td>' +
            '</tr></tbody></table>' +
            '<button type="button" class="btn btn-sm btn-outline-danger remove-linked-unit flex-shrink-0" title="إزالة">' +
            '<i class="bi bi-x-lg"></i></button></div>';
        listEl.appendChild(row);
        updateEmptyState();
    }

    function populateSubjects(classId) {
        subjectSelect.disabled = false;
        subjectSelect.innerHTML = '<option value="">جاري التحميل...</option>';
        resetUnits();
        return fetch(subjectsBase + '/' + classId + '/subjects', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (r) { if (!r.ok) throw new Error(); return r.json(); })
            .then(function (data) {
                subjectSelect.innerHTML = '<option value="">اختر المادة</option>';
                data.forEach(function (s) {
                    var opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.name;
                    subjectSelect.appendChild(opt);
                });
            })
            .catch(function () {
                subjectSelect.innerHTML = '<option value="">تعذر تحميل المواد</option>';
            });
    }

    function populateUnits(subjectId) {
        unitPicker.disabled = false;
        unitPicker.innerHTML = '<option value="">جاري التحميل...</option>';
        addBtn.disabled = true;
        return fetch(unitsBase + '/' + subjectId + '/units', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (r) { if (!r.ok) throw new Error(); return r.json(); })
            .then(function (data) {
                unitPicker.innerHTML = '<option value="">اختر الوحدة</option>';
                data.forEach(function (u) {
                    var opt = document.createElement('option');
                    opt.value = u.id;
                    opt.textContent = u.title;
                    unitPicker.appendChild(opt);
                });
            })
            .catch(function () {
                unitPicker.innerHTML = '<option value="">تعذر تحميل الوحدات</option>';
            });
    }

    classSelect.addEventListener('change', function () {
        if (!this.value) {
            resetSubjects();
            return;
        }
        populateSubjects(this.value);
    });

    subjectSelect.addEventListener('change', function () {
        if (!this.value) {
            resetUnits();
            return;
        }
        populateUnits(this.value);
    });

    unitPicker.addEventListener('change', function () {
        addBtn.disabled = !this.value;
    });

    addBtn.addEventListener('click', function () {
        var unitId = unitPicker.value;
        if (!unitId || unitAlreadyLinked(unitId)) {
            if (unitAlreadyLinked(unitId)) {
                unitPicker.value = '';
                addBtn.disabled = true;
            }
            return;
        }
        appendLinkedUnit(
            unitId,
            selectedOptionText(classSelect),
            selectedOptionText(subjectSelect),
            selectedOptionText(unitPicker)
        );
        unitPicker.value = '';
        addBtn.disabled = true;
    });

    listEl.addEventListener('click', function (e) {
        var btn = e.target.closest('.remove-linked-unit');
        if (!btn) return;
        btn.closest('.linked-unit-row').remove();
        updateEmptyState();
    });

    updateEmptyState();
});
</script>
@endpush
@endonce
