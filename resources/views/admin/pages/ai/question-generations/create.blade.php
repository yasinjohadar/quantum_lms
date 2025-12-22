@extends('admin.layouts.master')

@section('page-title')
    توليد أسئلة تلقائياً
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">توليد أسئلة تلقائياً</h5>
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

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form action="{{ route('admin.ai.question-generations.store') }}" method="POST">
                            @csrf

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
                                <textarea class="form-control" id="source_content" name="source_content" rows="10" placeholder="أدخل النص أو الموضوع الذي تريد توليد أسئلة منه...">{{ old('source_content') }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="question_type" class="form-label">نوع السؤال <span class="text-danger">*</span></label>
                                    <select class="form-select" id="question_type" name="question_type" required>
                                        @foreach($questionTypes as $key => $label)
                                            <option value="{{ $key }}" {{ old('question_type', 'mixed') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="number_of_questions" class="form-label">عدد الأسئلة <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="number_of_questions" name="number_of_questions" value="{{ old('number_of_questions', 5) }}" min="1" max="50" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="difficulty_level" class="form-label">مستوى الصعوبة <span class="text-danger">*</span></label>
                                    <select class="form-select" id="difficulty_level" name="difficulty_level" required>
                                        @foreach($difficulties as $key => $label)
                                            <option value="{{ $key }}" {{ old('difficulty_level', 'mixed') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="ai_model_id" class="form-label">موديل AI (اختياري)</label>
                                    <select class="form-select" id="ai_model_id" name="ai_model_id">
                                        <option value="">استخدام الموديل الافتراضي</option>
                                        @foreach($models as $model)
                                            <option value="{{ $model->id }}" {{ old('ai_model_id') == $model->id ? 'selected' : '' }}>{{ $model->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-magic me-1"></i> توليد الأسئلة
                                </button>
                                <a href="{{ route('admin.ai.question-generations.index') }}" class="btn btn-secondary">
                                    إلغاء
                                </a>
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
    const sourceType = document.getElementById('source_type');
    const lessonSource = document.getElementById('lesson_source');
    const lessonSelect = document.getElementById('lesson_select');
    const textSource = document.getElementById('text_source');
    const subjectSelect = document.getElementById('subject_id');
    const lessonIdSelect = document.getElementById('lesson_id');
    const sourceContent = document.getElementById('source_content');

    function toggleSourceFields() {
        if (sourceType.value === 'lesson_content') {
            lessonSource.style.display = 'block';
            lessonSelect.style.display = 'block';
            textSource.style.display = 'none';
            sourceContent.removeAttribute('required');
        } else {
            lessonSource.style.display = 'none';
            lessonSelect.style.display = 'none';
            textSource.style.display = 'block';
            sourceContent.setAttribute('required', 'required');
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
});
</script>
@endpush
@stop

