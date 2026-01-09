@extends('admin.layouts.master')

@section('page-title')
    توليد أسئلة تلقائياً (متقدم)
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">توليد أسئلة تلقائياً (متقدم)</h5>
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

        @if(isset($quiz) && $quiz)
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <strong>ملاحظة:</strong> الأسئلة المولدة ستُضاف تلقائياً للاختبار: <strong>{{ $quiz->title }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form action="{{ route('admin.ai.question-generations.store-advanced') }}" method="POST" id="advancedForm">
                            @csrf
                            @if(isset($quiz) && $quiz)
                                <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">
                            @endif

                            <div class="mb-3">
                                <label for="source_type" class="form-label">نوع المصدر <span class="text-danger">*</span></label>
                                <select class="form-select" id="source_type" name="source_type" required>
                                    @foreach(\App\Models\AIQuestionGeneration::SOURCE_TYPES as $key => $label)
                                        <option value="{{ $key }}" {{ old('source_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="lesson_source" class="mb-3" style="display: none;">
                                <label for="subject_id" class="form-label">المادة <span class="text-danger">*</span></label>
                                <select class="form-select" id="subject_id" name="subject_id">
                                    <option value="">اختر المادة</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="lesson_select" class="mb-3" style="display: none;">
                                <label for="lesson_id" class="form-label">الدرس <span class="text-danger">*</span></label>
                                <select class="form-select" id="lesson_id" name="lesson_id" disabled>
                                    <option value="">اختر المادة أولاً</option>
                                </select>
                            </div>

                            <div id="text_source" class="mb-3">
                                <label for="source_content" class="form-label">المحتوى المصدر <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="source_content" name="source_content" rows="12" placeholder="أدخل النص أو الموضوع الذي تريد توليد أسئلة منه...">{{ old('source_content') }}</textarea>
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
                                    <small class="text-muted">سيتم توزيع العدد على الأنواع المحددة</small>
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
                                        <option value="{{ $model->id }}" {{ old('ai_model_id') == $model->id ? 'selected' : '' }}>{{ $model->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" onclick="saveTinyMCE()">
                                    <i class="fas fa-magic me-1"></i> توليد الأسئلة
                                </button>
                                @if(isset($quiz) && $quiz)
                                    <a href="{{ route('admin.quizzes.questions', $quiz->id) }}" class="btn btn-secondary">
                                        إلغاء
                                    </a>
                                @else
                                    <a href="{{ route('admin.ai.question-generations.index') }}" class="btn btn-secondary">
                                        إلغاء
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- TinyMCE Self-Hosted (Free & Open Source) -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@7.3.0/tinymce.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // تهيئة TinyMCE للمحتوى المصدر
    tinymce.init({
        selector: '#source_content',
        language: 'ar',
        language_url: 'https://cdn.jsdelivr.net/npm/tinymce-i18n@24/langs5/ar.js',
        directionality: 'rtl',
        height: 400,
        menubar: 'file edit view insert format tools table help',
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount',
            'directionality', 'visualchars', 'emoticons', 'paste'
        ],
        toolbar: 'undo redo | blocks | fontfamily fontsize | ' +
            'bold italic underline strikethrough | forecolor backcolor | ' +
            'alignleft aligncenter alignright alignjustify | ' +
            'bullist numlist outdent indent | ' +
            'link image media | table charmap emoticons | ' +
            'code preview fullscreen | ' +
            'ltr rtl | searchreplace visualblocks visualchars | ' +
            'help',
        content_style: 'body { font-family: Arial, "Helvetica Neue", Helvetica, sans-serif; font-size: 14px; direction: rtl; text-align: right; }',
        image_advtab: true,
        file_picker_types: 'image',
        automatic_uploads: true,
        images_upload_handler: function (blobInfo, progress) {
            return new Promise(function (resolve, reject) {
                var xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                xhr.open('POST', '{{ route("admin.questions.upload-image") }}');
                
                var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                xhr.setRequestHeader('X-CSRF-TOKEN', token);
                
                xhr.upload.onprogress = function (e) {
                    if (e.lengthComputable) {
                        progress(e.loaded / e.total * 100);
                    }
                };
                
                xhr.onload = function () {
                    if (xhr.status === 403) {
                        reject({ message: 'HTTP Error: ' + xhr.status, remove: true });
                        return;
                    }
                    
                    if (xhr.status < 200 || xhr.status >= 300) {
                        reject('HTTP Error: ' + xhr.status);
                        return;
                    }
                    
                    var json = JSON.parse(xhr.responseText);
                    
                    if (!json || typeof json.location != 'string') {
                        reject('Invalid JSON: ' + xhr.responseText);
                        return;
                    }
                    
                    resolve(json.location);
                };
                
                xhr.onerror = function () {
                    reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
                };
                
                var formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                
                xhr.send(formData);
            });
        },
        paste_data_images: true,
        convert_urls: false,
        relative_urls: false,
        remove_script_host: false,
    });

    const sourceType = document.getElementById('source_type');
    const lessonSource = document.getElementById('lesson_source');
    const lessonSelect = document.getElementById('lesson_select');
    const textSource = document.getElementById('text_source');
    const subjectSelect = document.getElementById('subject_id');
    const lessonIdSelect = document.getElementById('lesson_id');
    const sourceContent = document.getElementById('source_content');
    const form = document.getElementById('advancedForm');

    function toggleSourceFields() {
        if (sourceType.value === 'lesson_content') {
            lessonSource.style.display = 'block';
            lessonSelect.style.display = 'block';
            textSource.style.display = 'none';
            sourceContent.removeAttribute('required');
            // إخفاء TinyMCE عند اختيار lesson_content
            if (tinymce.get('source_content')) {
                tinymce.get('source_content').hide();
            }
        } else {
            lessonSource.style.display = 'none';
            lessonSelect.style.display = 'none';
            textSource.style.display = 'block';
            sourceContent.setAttribute('required', 'required');
            // إظهار TinyMCE عند اختيار text_content
            if (tinymce.get('source_content')) {
                tinymce.get('source_content').show();
            }
        }
    }

    sourceType.addEventListener('change', toggleSourceFields);
    toggleSourceFields();

    subjectSelect.addEventListener('change', function() {
        const subjectId = this.value;
        if (subjectId) {
            lessonIdSelect.disabled = false;
            fetch(`{{ url('student/subjects') }}/${subjectId}/lessons`)
                .then(response => response.json())
                .then(data => {
                    lessonIdSelect.innerHTML = '<option value="">اختر الدرس</option>';
                    data.forEach(lesson => {
                        lessonIdSelect.innerHTML += `<option value="${lesson.id}">${lesson.title}</option>`;
                    });
                });
        } else {
            lessonIdSelect.disabled = true;
            lessonIdSelect.innerHTML = '<option value="">اختر المادة أولاً</option>';
        }
    });

    // Validation قبل الإرسال
    form.addEventListener('submit', function(e) {
        // حفظ محتوى TinyMCE قبل الإرسال
        if (tinymce.get('source_content')) {
            tinymce.triggerSave();
        }

        const checkboxes = document.querySelectorAll('.question-type-checkbox:checked');
        if (checkboxes.length === 0) {
            e.preventDefault();
            document.getElementById('question-types-error').classList.remove('d-none');
            document.getElementById('question-types-grid').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
        document.getElementById('question-types-error').classList.add('d-none');
    });
});

// دالة لحفظ TinyMCE (يمكن استدعاؤها من الأزرار)
function saveTinyMCE() {
    if (tinymce.get('source_content')) {
        tinymce.triggerSave();
    }
}

function selectAllTypes() {
    document.querySelectorAll('.question-type-checkbox').forEach(cb => {
        cb.checked = true;
        updateCardStyle(cb);
    });
}

function deselectAllTypes() {
    document.querySelectorAll('.question-type-checkbox').forEach(cb => {
        cb.checked = false;
        updateCardStyle(cb);
    });
}

function updateCardStyle(checkbox) {
    const card = checkbox.closest('.question-type-card');
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

