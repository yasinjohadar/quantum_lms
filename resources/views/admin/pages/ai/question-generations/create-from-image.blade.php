@extends('admin.layouts.master')

@section('page-title')
    توليد أسئلة من صورة أو PDF
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">توليد أسئلة من صورة أو PDF</h5>
            </div>
            <div>
                <a href="{{ !empty($lockedSubject) ? route('admin.subjects.questions.index', $lockedSubject->id) : route('admin.ai.question-generations.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if(!empty($lockedSubject))
            <div class="alert alert-primary">
                <i class="fas fa-link me-1"></i>
                الأسئلة المولدة ستُربط بمادة <strong>{{ $lockedSubject->name }}</strong>
                @if($lockedSubject->schoolClass)
                    — {{ $lockedSubject->schoolClass->name }}
                @endif
            </div>
        @endif

        <div class="alert alert-info">
            <strong>الصور:</strong> يتطلب موديلاً يدعم <strong>الرؤية (Vision)</strong> (مثل gpt-4o، Claude، Gemini).
            <br>
            <strong>PDF نصي:</strong> يعمل مع أي موديل توليد أسئلة (يُستخرج النص تلقائياً).
            <br>
            <strong>PDF ممسوح:</strong> يحتاج موديل رؤية + تفعيل <strong>Imagick</strong> و<strong>Ghostscript</strong> على الخادم.
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form action="{{ route('admin.ai.question-generations.store-from-image') }}" method="POST" enctype="multipart/form-data" id="imageGenForm">
                            @csrf
                            @if(!empty($lockedSubject))
                                <input type="hidden" name="subject_id" value="{{ $lockedSubject->id }}">
                                <input type="hidden" name="class_id" value="{{ $lockedSubject->class_id }}">
                            @endif

                            <div class="mb-3">
                                <label for="source_file" class="form-label">الملف (صورة أو PDF) <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="source_file" name="source_file" accept="image/jpeg,image/png,image/webp,image/gif,application/pdf" required>
                                <small class="text-muted">صور: JPEG, PNG, WebP, GIF (حتى 8 ميجابايت) — PDF: حتى 15 ميجابايت</small>
                                <div class="mt-2 d-none" id="imagePreviewWrap">
                                    <img src="" alt="" id="imagePreview" class="img-fluid rounded border" style="max-height: 280px;">
                                </div>
                                <div class="mt-2 d-none alert alert-secondary py-2 mb-0" id="pdfPreviewWrap">
                                    <i class="fas fa-file-pdf text-danger me-2"></i>
                                    <span id="pdfFileName"></span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="instructions" class="form-label">تعليمات إضافية (اختياري)</label>
                                <textarea class="form-control" id="instructions" name="instructions" rows="4" placeholder="مثال: ركّز على المسائل العددية في الصورة، أو صغ الأسئلة للصف السادس...">{{ old('instructions') }}</textarea>
                            </div>

                            @if(empty($lockedSubject))
                                <div class="mb-3">
                                    <label for="class_id" class="form-label">الصف (اختياري)</label>
                                    <select class="form-select" id="class_id" name="class_id">
                                        <option value="">— بدون تحديد —</option>
                                        @foreach($schoolClasses as $schoolClass)
                                            <option value="{{ $schoolClass->id }}" {{ (string) old('class_id', $prefillClassId ?? '') === (string) $schoolClass->id ? 'selected' : '' }}>{{ $schoolClass->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="subject_id" class="form-label">المادة (اختياري)</label>
                                    <select class="form-select" id="subject_id" name="subject_id" @if(!$prefillClassId) disabled @endif>
                                        <option value="">{{ $prefillClassId ? 'اختر المادة' : 'اختر الصف أولاً' }}</option>
                                    </select>
                                </div>
                            @else
                                <div class="mb-3">
                                    <label class="form-label">المادة</label>
                                    <p class="form-control-plaintext mb-0">
                                        <strong>{{ $lockedSubject->name }}</strong>
                                        @if($lockedSubject->schoolClass)
                                            <span class="text-muted">({{ $lockedSubject->schoolClass->name }})</span>
                                        @endif
                                    </p>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label for="unit_id" class="form-label">الوحدة (اختياري)</label>
                                <select class="form-select" id="unit_id" name="unit_id" @if(!$prefillSubjectId) disabled @endif>
                                    <option value="">{{ $prefillSubjectId ? 'اختر الوحدة' : 'اختر المادة أولاً' }}</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">أنواع الأسئلة المطلوبة <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2 mb-3">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllTypes()">
                                        <i class="fas fa-check-square me-1"></i> تحديد الكل
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllTypes()">
                                        <i class="fas fa-square me-1"></i> إلغاء التحديد
                                    </button>
                                </div>
                                <div class="row g-3" id="question-types-grid">
                                    @php
                                        $questionTypes = \App\Models\Question::TYPES;
                                        $typeIcons = \App\Models\Question::TYPE_ICONS;
                                        $typeColors = \App\Models\Question::TYPE_COLORS;
                                        $oldTypes = old('question_types', []);
                                    @endphp
                                    @foreach($questionTypes as $key => $label)
                                        @php
                                            $color = $typeColors[$key] ?? 'secondary';
                                            $icon = $typeIcons[$key] ?? 'bi-question-circle';
                                            $isChecked = in_array($key, $oldTypes);
                                        @endphp
                                        <div class="col-md-4 col-sm-6">
                                            <div class="card h-100 question-type-card {{ $isChecked ? 'border-primary' : '' }}" style="cursor: pointer; transition: all 0.3s;">
                                                <div class="card-body p-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input question-type-checkbox"
                                                               type="checkbox"
                                                               name="question_types[]"
                                                               value="{{ $key }}"
                                                               id="question_type_{{ $key }}"
                                                               {{ $isChecked ? 'checked' : '' }}
                                                               onchange="updateCardStyle(this)">
                                                        <label class="form-check-label w-100" for="question_type_{{ $key }}" style="cursor: pointer;">
                                                            <div class="d-flex align-items-center">
                                                                <i class="bi {{ $icon }} text-{{ $color }} me-2 fs-5"></i>
                                                                <span class="fw-semibold">{{ $label }}</span>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-danger d-none" id="question-types-error">يجب اختيار نوع واحد على الأقل</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="number_of_questions" class="form-label">عدد الأسئلة <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="number_of_questions" name="number_of_questions" value="{{ old('number_of_questions', 5) }}" min="1" max="50" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="difficulty_level" class="form-label">مستوى الصعوبة <span class="text-danger">*</span></label>
                                    <select class="form-select" id="difficulty_level" name="difficulty_level" required>
                                        @foreach($difficulties as $key => $label)
                                            <option value="{{ $key }}" {{ old('difficulty_level', 'mixed') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="ai_model_id" class="form-label">موديل AI (اختياري)</label>
                                <select class="form-select" id="ai_model_id" name="ai_model_id">
                                    <option value="">استخدام الموديل الافتراضي</option>
                                    @foreach($models as $model)
                                        <option value="{{ $model->id }}" {{ old('ai_model_id') == $model->id ? 'selected' : '' }}>{{ $model->name }} ({{ $model->provider }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-file-upload me-1"></i> تحليل الملف وتوليد الأسئلة
                                </button>
                                <a href="{{ !empty($lockedSubject) ? route('admin.subjects.questions.index', $lockedSubject->id) : route('admin.ai.question-generations.index') }}" class="btn btn-secondary">إلغاء</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var classSelect = document.getElementById('class_id');
    var subjectSelect = document.getElementById('subject_id');
    var unitSelect = document.getElementById('unit_id');
    var ajaxSubjectsBase = @json(url('/admin/ai/question-generations/ajax/classes'));
    var ajaxUnitsBase = @json(url('/admin/ai/question-generations/ajax/subjects'));
    var prefillSubjectId = @json(old('subject_id', $prefillSubjectId ?? ''));
    var prefillUnitId = @json(old('unit_id', $prefillUnitId ?? ''));
    var lockedSubjectId = @json(!empty($lockedSubject) ? $lockedSubject->id : null);

    function resetUnitsPlaceholder() {
        unitSelect.disabled = true;
        unitSelect.innerHTML = '<option value="">اختر المادة أولاً</option>';
    }

    function resetSubjectsPlaceholder() {
        if (!subjectSelect) {
            return;
        }
        subjectSelect.disabled = true;
        subjectSelect.innerHTML = '<option value="">اختر الصف أولاً</option>';
        resetUnitsPlaceholder();
    }

    function populateSubjects(classId, selectedSubjectId) {
        if (!subjectSelect) {
            return Promise.resolve();
        }
        subjectSelect.disabled = false;
        subjectSelect.innerHTML = '<option value="">جاري التحميل...</option>';
        resetUnitsPlaceholder();
        return fetch(ajaxSubjectsBase + '/' + classId + '/subjects', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(function (data) {
                subjectSelect.innerHTML = '<option value="">اختر المادة</option>';
                data.forEach(function (subject) {
                    var opt = document.createElement('option');
                    opt.value = subject.id;
                    opt.textContent = subject.name;
                    if (selectedSubjectId && String(subject.id) === String(selectedSubjectId)) {
                        opt.selected = true;
                    }
                    subjectSelect.appendChild(opt);
                });
                if (selectedSubjectId) {
                    return populateUnits(selectedSubjectId, prefillUnitId);
                }
            })
            .catch(function () {
                subjectSelect.innerHTML = '<option value="">تعذر تحميل المواد</option>';
                resetUnitsPlaceholder();
            });
    }

    function populateUnits(subjectId, selectedUnitId) {
        unitSelect.disabled = false;
        unitSelect.innerHTML = '<option value="">جاري التحميل...</option>';
        return fetch(ajaxUnitsBase + '/' + subjectId + '/units', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(function (data) {
                unitSelect.innerHTML = '<option value="">اختر الوحدة</option>';
                data.forEach(function (unit) {
                    var opt = document.createElement('option');
                    opt.value = unit.id;
                    opt.textContent = unit.title;
                    if (selectedUnitId && String(unit.id) === String(selectedUnitId)) {
                        opt.selected = true;
                    }
                    unitSelect.appendChild(opt);
                });
            })
            .catch(function () {
                unitSelect.innerHTML = '<option value="">تعذر تحميل الوحدات</option>';
            });
    }

    if (classSelect) {
        classSelect.addEventListener('change', function () {
            var classId = this.value;
            if (!classId) {
                resetSubjectsPlaceholder();
                return;
            }
            populateSubjects(classId, null);
        });
    }

    if (subjectSelect) {
        subjectSelect.addEventListener('change', function () {
            var subjectId = this.value;
            if (!subjectId) {
                resetUnitsPlaceholder();
                return;
            }
            populateUnits(subjectId, null);
        });
    }

    if (lockedSubjectId) {
        populateUnits(lockedSubjectId, prefillUnitId || null);
    } else if (classSelect && classSelect.value) {
        populateSubjects(classSelect.value, prefillSubjectId || null);
    }

    var input = document.getElementById('source_file');
    var wrap = document.getElementById('imagePreviewWrap');
    var img = document.getElementById('imagePreview');
    var pdfWrap = document.getElementById('pdfPreviewWrap');
    var pdfFileName = document.getElementById('pdfFileName');
    input.addEventListener('change', function() {
        wrap.classList.add('d-none');
        pdfWrap.classList.add('d-none');
        if (!input.files || !input.files[0]) {
            return;
        }
        var file = input.files[0];
        if (file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')) {
            pdfFileName.textContent = file.name;
            pdfWrap.classList.remove('d-none');
            return;
        }
        var url = URL.createObjectURL(file);
        img.src = url;
        wrap.classList.remove('d-none');
    });

    document.getElementById('imageGenForm').addEventListener('submit', function(e) {
        var checkboxes = document.querySelectorAll('.question-type-checkbox:checked');
        if (checkboxes.length === 0) {
            e.preventDefault();
            document.getElementById('question-types-error').classList.remove('d-none');
            document.getElementById('question-types-grid').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
        document.getElementById('question-types-error').classList.add('d-none');
    });
});

function selectAllTypes() {
    document.querySelectorAll('.question-type-checkbox').forEach(function(cb) {
        cb.checked = true;
        updateCardStyle(cb);
    });
}

function deselectAllTypes() {
    document.querySelectorAll('.question-type-checkbox').forEach(function(cb) {
        cb.checked = false;
        updateCardStyle(cb);
    });
}

function updateCardStyle(checkbox) {
    var card = checkbox.closest('.question-type-card');
    if (checkbox.checked) {
        card.classList.add('border-primary');
        card.style.backgroundColor = 'rgba(13, 110, 253, 0.1)';
    } else {
        card.classList.remove('border-primary');
        card.style.backgroundColor = '';
    }
}
</script>
@endpush
@stop
