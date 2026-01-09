@extends('student.layouts.master')

@section('page-title')
    محادثة جديدة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">محادثة جديدة</h5>
            </div>
            <div>
                <a href="{{ route('student.ai.chatbot.index') }}" class="btn btn-secondary btn-sm">
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
            <div class="col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form action="{{ route('student.ai.chatbot.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="subject_id" class="form-label">المادة (اختياري)</label>
                                <select class="form-select" id="subject_id" name="subject_id">
                                    <option value="">محادثة عامة</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="lesson_id" class="form-label">الدرس (اختياري)</label>
                                <select class="form-select" id="lesson_id" name="lesson_id" disabled>
                                    <option value="">اختر المادة أولاً</option>
                                </select>
                            </div>

                            <!-- الخيارات المتقدمة -->
                            <div class="accordion mb-3" id="advancedOptions">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingAdvanced">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdvanced" aria-expanded="false" aria-controls="collapseAdvanced">
                                            <i class="fas fa-cog me-2"></i> خيارات متقدمة
                                        </button>
                                    </h2>
                                    <div id="collapseAdvanced" class="accordion-collapse collapse" aria-labelledby="headingAdvanced" data-bs-parent="#advancedOptions">
                                        <div class="accordion-body">
                                            <div class="mb-3">
                                                <label for="ai_model_id" class="form-label">موديل AI</label>
                                                <select class="form-select" id="ai_model_id" name="ai_model_id">
                                                    <option value="">استخدام الأفضل تلقائياً</option>
                                                    @foreach($models ?? [] as $model)
                                                        <option value="{{ $model->id }}" {{ old('ai_model_id') == $model->id ? 'selected' : '' }}>
                                                            {{ $model->name }} ({{ $model->provider }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted">إذا لم تختر، سيتم استخدام أفضل موديل متاح</small>
                                            </div>

                                            <div class="mb-3">
                                                <label for="temperature" class="form-label">درجة الحرارة (Temperature): <span id="temperatureValue">0.7</span></label>
                                                <input type="range" class="form-range" id="temperature" name="temperature" min="0.1" max="1.0" step="0.1" value="0.7" oninput="document.getElementById('temperatureValue').textContent = this.value">
                                                <div class="d-flex justify-content-between">
                                                    <small class="text-muted">أكثر إبداعاً</small>
                                                    <small class="text-muted">أكثر دقة</small>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="max_tokens" class="form-label">الحد الأقصى للـ Tokens</label>
                                                <input type="number" class="form-control" id="max_tokens" name="max_tokens" min="100" max="10000" step="100" placeholder="اتركه فارغاً للاستخدام الافتراضي">
                                                <small class="text-muted">يحدد طول الرد (100-10000)</small>
                                            </div>

                                            <div class="mb-3">
                                                <label for="mode" class="form-label">نمط المحادثة</label>
                                                <select class="form-select" id="mode" name="mode">
                                                    <option value="educational" {{ old('mode', 'educational') == 'educational' ? 'selected' : '' }}>تعليمي (موصى به)</option>
                                                    <option value="casual" {{ old('mode') == 'casual' ? 'selected' : '' }}>محادثة عادية</option>
                                                    <option value="deep_analysis" {{ old('mode') == 'deep_analysis' ? 'selected' : '' }}>تحليل عميق</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-comments me-1"></i> بدء المحادثة
                                </button>
                                <a href="{{ route('student.ai.chatbot.index') }}" class="btn btn-secondary">
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
    const subjectSelect = document.getElementById('subject_id');
    const lessonSelect = document.getElementById('lesson_id');

    subjectSelect.addEventListener('change', function() {
        const subjectId = this.value;
        
        if (subjectId) {
            lessonSelect.disabled = false;
            fetch(`{{ url('student/subjects') }}/${subjectId}/lessons`)
                .then(response => response.json())
                .then(data => {
                    lessonSelect.innerHTML = '<option value="">اختر الدرس</option>';
                    data.forEach(lesson => {
                        lessonSelect.innerHTML += `<option value="${lesson.id}">${lesson.title}</option>`;
                    });
                })
                .catch(error => {
                    console.error('Error fetching lessons:', error);
                });
        } else {
            lessonSelect.disabled = true;
            lessonSelect.innerHTML = '<option value="">اختر المادة أولاً</option>';
        }
    });
});
</script>
@endpush
@stop

