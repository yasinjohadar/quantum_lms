@extends('admin.layouts.master')

@section('page-title')
    إنشاء جلسة حية جديدة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="page-header d-flex justify-content-between align-items-center my-4">
            <h5 class="page-title mb-0">إنشاء جلسة حية جديدة</h5>
            <a href="{{ route('admin.live-sessions.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li class="small">{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.live-sessions.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">البيانات الأساسية</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">نوع الجلسة <span class="text-danger">*</span></label>
                            <select name="sessionable_type" 
                                    class="form-select @error('sessionable_type') is-invalid @enderror" 
                                    id="sessionable_type" required>
                                <option value="">اختر النوع</option>
                                <option value="{{ \App\Models\Subject::class }}" {{ old('sessionable_type') === \App\Models\Subject::class ? 'selected' : '' }}>مادة</option>
                                <option value="{{ \App\Models\Lesson::class }}" {{ old('sessionable_type') === \App\Models\Lesson::class ? 'selected' : '' }}>درس</option>
                            </select>
                            @error('sessionable_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" id="sessionable_label">المادة/الدرس <span class="text-danger">*</span></label>
                            <select name="sessionable_id" 
                                    class="form-select @error('sessionable_id') is-invalid @enderror" 
                                    id="sessionable_id" required>
                                <option value="">اختر المادة/الدرس</option>
                            </select>
                            @error('sessionable_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">عنوان الجلسة <span class="text-danger">*</span></label>
                            <input type="text" name="title" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   placeholder="عنوان الجلسة"
                                   value="{{ old('title') }}" required>
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">الوصف</label>
                            <textarea name="description" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="3" 
                                      placeholder="وصف الجلسة">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">تاريخ ووقت الجلسة <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="scheduled_at" 
                                   class="form-control @error('scheduled_at') is-invalid @enderror" 
                                   value="{{ old('scheduled_at') }}" required>
                            @error('scheduled_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">المدة (بالدقائق) <span class="text-danger">*</span></label>
                            <input type="number" name="duration_minutes" 
                                   class="form-control @error('duration_minutes') is-invalid @enderror" 
                                   placeholder="60"
                                   value="{{ old('duration_minutes') }}" 
                                   min="1" max="480" required>
                            @error('duration_minutes')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">المنطقة الزمنية <span class="text-danger">*</span></label>
                            <input type="text" name="timezone" 
                                   class="form-control @error('timezone') is-invalid @enderror" 
                                   placeholder="UTC"
                                   value="{{ old('timezone', 'UTC') }}" required>
                            @error('timezone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> حفظ الجلسة
                            </button>
                            <a href="{{ route('admin.live-sessions.index') }}" class="btn btn-secondary">
                                إلغاء
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sessionableTypeSelect = document.getElementById('sessionable_type');
    const sessionableIdSelect = document.getElementById('sessionable_id');
    const sessionableLabel = document.getElementById('sessionable_label');
    
    const subjects = @json($subjects);
    const lessons = @json($lessons);

    sessionableTypeSelect.addEventListener('change', function() {
        sessionableIdSelect.innerHTML = '<option value="">اختر المادة/الدرس</option>';
        
        if (this.value === '{{ \App\Models\Subject::class }}') {
            sessionableLabel.textContent = 'المادة *';
            subjects.forEach(function(subject) {
                const option = document.createElement('option');
                option.value = subject.id;
                option.textContent = subject.name;
                if (subject.school_class) {
                    option.textContent += ' - ' + subject.school_class.name;
                }
                sessionableIdSelect.appendChild(option);
            });
        } else if (this.value === '{{ \App\Models\Lesson::class }}') {
            sessionableLabel.textContent = 'الدرس *';
            lessons.forEach(function(lesson) {
                const option = document.createElement('option');
                option.value = lesson.id;
                option.textContent = lesson.title;
                if (lesson.unit && lesson.unit.section && lesson.unit.section.subject) {
                    option.textContent += ' (' + lesson.unit.section.subject.name + ')';
                }
                sessionableIdSelect.appendChild(option);
            });
        }
    });

    // Trigger change on page load if value exists
    if (sessionableTypeSelect.value) {
        sessionableTypeSelect.dispatchEvent(new Event('change'));
        if ('{{ old("sessionable_id") }}') {
            sessionableIdSelect.value = '{{ old("sessionable_id") }}';
        }
    }
});
</script>
@endpush
@endsection


