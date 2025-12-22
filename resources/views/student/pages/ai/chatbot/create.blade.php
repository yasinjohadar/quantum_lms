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

