@extends('admin.layouts.master')

@section('page-title')
    توليد أسئلة من صورة (تحليل بصري)
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">توليد أسئلة من صورة</h5>
            </div>
            <div>
                <a href="{{ route('admin.ai.question-generations.index') }}" class="btn btn-secondary btn-sm">
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

        <div class="alert alert-info">
            <strong>متطلبات الموديل:</strong> يجب استخدام مزود يدعم <strong>الرؤية (Vision)</strong> مع مفتاح موديل يدعم الصور، مثل:
            OpenAI (gpt-4o)، OpenRouter (موديلات vision)، Anthropic (Claude مع صور)، Google (Gemini Flash/Pro)، أو Z.ai إن كان الموديل يدعم الصور.
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form action="{{ route('admin.ai.question-generations.store-from-image') }}" method="POST" enctype="multipart/form-data" id="imageGenForm">
                            @csrf

                            <div class="mb-3">
                                <label for="source_image" class="form-label">الصورة <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="source_image" name="source_image" accept="image/jpeg,image/png,image/webp,image/gif" required>
                                <small class="text-muted">JPEG أو PNG أو WebP أو GIF — حتى 8 ميجابايت</small>
                                <div class="mt-2 d-none" id="imagePreviewWrap">
                                    <img src="" alt="" id="imagePreview" class="img-fluid rounded border" style="max-height: 280px;">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="instructions" class="form-label">تعليمات إضافية (اختياري)</label>
                                <textarea class="form-control" id="instructions" name="instructions" rows="4" placeholder="مثال: ركّز على المسائل العددية في الصورة، أو صغ الأسئلة للصف السادس...">{{ old('instructions') }}</textarea>
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
                                    <i class="fas fa-image me-1"></i> تحليل الصورة وتوليد الأسئلة
                                </button>
                                <a href="{{ route('admin.ai.question-generations.index') }}" class="btn btn-secondary">إلغاء</a>
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
    var input = document.getElementById('source_image');
    var wrap = document.getElementById('imagePreviewWrap');
    var img = document.getElementById('imagePreview');
    input.addEventListener('change', function() {
        if (!input.files || !input.files[0]) {
            wrap.classList.add('d-none');
            return;
        }
        var url = URL.createObjectURL(input.files[0]);
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
