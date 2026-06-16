@extends('admin.layouts.master')

@section('page-title')
    توليد أسئلة من صورة أو PDF
@stop

@push('styles')
    @include('admin.pages.ai.question-generations.partials.question-generations-index-styles')
@endpush

@section('content')
<div class="main-content app-content ai-gen-index-page">
    <div class="container-fluid">

        <div class="ai-gen-index-hero my-4">
            <div class="ai-gen-index-hero__icon">
                <i class="bi bi-image"></i>
            </div>
            <div class="ai-gen-index-hero__content">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2 small">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.ai.question-generations.index') }}">طلبات التوليد</a></li>
                        <li class="breadcrumb-item active" aria-current="page">من صورة</li>
                    </ol>
                </nav>
                <h4 class="ai-gen-index-hero__title">توليد أسئلة من صورة أو PDF</h4>
                <p class="ai-gen-index-hero__subtitle">ارفع ملفاً وحدد أنواع الأسئلة لتحليله وتوليد الأسئلة</p>
            </div>
            <div class="ai-gen-index-hero__actions">
                <a href="{{ !empty($lockedSubject) ? route('admin.subjects.questions.index', $lockedSubject->id) : route('admin.ai.question-generations.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-right me-1"></i> رجوع
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
            <div class="ai-gen-linked-alert">
                <i class="bi bi-link-45deg"></i>
                <div>
                    الأسئلة المولدة ستُربط بمادة <strong>{{ $lockedSubject->name }}</strong>
                    @if($lockedSubject->schoolClass)
                        — {{ $lockedSubject->schoolClass->name }}
                    @endif
                </div>
            </div>
        @endif

        <div class="ai-gen-hint-box">
            <strong>الصور:</strong> يتطلب موديلاً يدعم <strong>الرؤية (Vision)</strong> (مثل gpt-4o، Claude، Gemini).
            <br>
            <strong>PDF نصي:</strong> يعمل مع أي موديل توليد أسئلة (يُستخرج النص تلقائياً).
            <br>
            <strong>PDF ممسوح:</strong> يحتاج موديل رؤية + تفعيل <strong>Imagick</strong> و<strong>Ghostscript</strong> على الخادم.
        </div>

        <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-10">
                <div class="ai-gen-index-card ai-gen-form-card">
                    <div class="ai-gen-index-card__header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="ai-gen-index-card__header-icon"><i class="bi bi-upload"></i></span>
                            <span>رفع الملف وإعدادات التوليد</span>
                        </div>
                    </div>
                    <div class="ai-gen-index-card__body">
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
                                <div class="mt-2 ai-gen-file-preview d-none" id="imagePreviewWrap">
                                    <img src="" alt="" id="imagePreview" class="img-fluid rounded" style="max-height: 280px;">
                                </div>
                                <div class="mt-2 ai-gen-file-preview d-none" id="pdfPreviewWrap">
                                    <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                    <span id="pdfFileName"></span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="instructions" class="form-label">تعليمات إضافية (اختياري)</label>
                                <textarea class="form-control" id="instructions" name="instructions" rows="4" placeholder="مثال: ركّز على المسائل العددية في الصورة، أو صغ الأسئلة للصف السادس...">{{ old('instructions') }}</textarea>
                            </div>

                            @if(empty($lockedSubject))
                                @include('admin.pages.ai.question-generations.partials.optional-curriculum-fields', [
                                    'fieldPrefix' => '',
                                    'schoolClasses' => $schoolClasses,
                                    'prefillClassId' => $prefillClassId,
                                    'prefillSubjectId' => $prefillSubjectId,
                                    'prefillUnitId' => $prefillUnitId,
                                ])
                            @else
                                @include('admin.pages.ai.question-generations.partials.optional-curriculum-fields', [
                                    'fieldPrefix' => '',
                                    'schoolClasses' => $schoolClasses,
                                    'lockedSubject' => $lockedSubject,
                                    'prefillSubjectId' => $prefillSubjectId,
                                    'prefillUnitId' => $prefillUnitId,
                                ])
                            @endif

                            <div class="ai-gen-form-section">
                                <div class="ai-gen-form-section__title">
                                    <span class="ai-gen-form-section__title-icon"><i class="bi bi-ui-checks-grid"></i></span>
                                    أنواع الأسئلة المطلوبة
                                </div>
                                <div class="ai-gen-type-select-toolbar">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllTypes()">
                                        <i class="bi bi-check-all me-1"></i> تحديد الكل
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllTypes()">
                                        <i class="bi bi-x-lg me-1"></i> إلغاء التحديد
                                    </button>
                                </div>
                                <div class="ai-gen-question-types-grid" id="question-types-grid">
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
                                        <div class="ai-gen-question-type-card card question-type-card {{ $isChecked ? 'ai-gen-question-type-card--selected' : '' }}">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input question-type-checkbox"
                                                               type="checkbox"
                                                               name="question_types[]"
                                                               value="{{ $key }}"
                                                               id="question_type_{{ $key }}"
                                                               {{ $isChecked ? 'checked' : '' }}
                                                               onchange="updateCardStyle(this)">
                                                        <label class="form-check-label" for="question_type_{{ $key }}">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="ai-gen-question-type-card__icon">
                                                                    <i class="bi {{ $icon }} text-{{ $color }}"></i>
                                                                </span>
                                                                <span class="fw-semibold">{{ $label }}</span>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                    @endforeach
                                </div>
                                <small class="text-danger d-none" id="question-types-error">يجب اختيار نوع واحد على الأقل</small>
                            </div>

                            <div class="ai-gen-form-section">
                                <div class="ai-gen-form-section__title">
                                    <span class="ai-gen-form-section__title-icon"><i class="bi bi-toggles"></i></span>
                                    خيارات التوليد
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
                            </div>

                            <div class="ai-gen-form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload me-1"></i> تحليل الملف وتوليد الأسئلة
                                </button>
                                <a href="{{ !empty($lockedSubject) ? route('admin.subjects.questions.index', $lockedSubject->id) : route('admin.ai.question-generations.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
@include('admin.pages.ai.question-generations.partials.optional-curriculum-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.initOptionalCurriculumCascade === 'function') {
        window.initOptionalCurriculumCascade({
            classSelectId: 'class_id',
            subjectSelectId: 'subject_id',
            unitSelectId: 'unit_id',
            prefillSubjectId: @json(old('subject_id', $prefillSubjectId ?? '')),
            prefillUnitId: @json(old('unit_id', $prefillUnitId ?? '')),
            lockedSubjectId: @json(!empty($lockedSubject) ? $lockedSubject->id : null),
        });
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
        card.classList.add('ai-gen-question-type-card--selected');
    } else {
        card.classList.remove('ai-gen-question-type-card--selected');
    }
}
</script>
@endpush
@stop
