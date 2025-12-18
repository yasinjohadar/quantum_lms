@extends('admin.layouts.master')

@section('page-title')
    تعديل الاختبار
@stop

@section('css')
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تعديل الاختبار</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.index') }}">الاختبارات</a></li>
                            <li class="breadcrumb-item active">تعديل: {{ Str::limit($quiz->title, 30) }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- Page Header Close -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.quizzes.update', $quiz->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-lg-8">
                {{-- المعلومات الأساسية --}}
                <div class="card custom-card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i> المعلومات الأساسية</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">المادة <span class="text-danger">*</span></label>
                                <select name="subject_id" class="form-select" id="subjectSelect" required>
                                    <option value="">اختر المادة</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ old('subject_id', $quiz->subject_id) == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }}
                                            @if($subject->schoolClass)
                                                ({{ $subject->schoolClass->name }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">الوحدة (اختياري)</label>
                                <select name="unit_id" class="form-select" id="unitSelect">
                                    <option value="">كل الوحدات</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ old('unit_id', $quiz->unit_id) == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">عنوان الاختبار <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" 
                                   value="{{ old('title', $quiz->title) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">وصف الاختبار</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $quiz->description) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">تعليمات قبل البدء</label>
                            <textarea name="instructions" class="form-control" rows="3">{{ old('instructions', $quiz->instructions) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">صورة الاختبار</label>
                            @if($quiz->image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/'.$quiz->image) }}" class="rounded" style="max-width: 150px;">
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="removeImage">
                                        <label class="form-check-label text-danger small" for="removeImage">حذف الصورة</label>
                                    </div>
                                </div>
                            @endif
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>

                {{-- إعدادات الوقت --}}
                <div class="card custom-card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-clock me-2"></i> إعدادات الوقت</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">المدة (بالدقائق)</label>
                                <input type="number" name="duration_minutes" class="form-control" 
                                       value="{{ old('duration_minutes', $quiz->duration_minutes) }}" min="1" max="600">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">تاريخ البدء</label>
                                <input type="datetime-local" name="available_from" class="form-control" 
                                       value="{{ old('available_from', $quiz->available_from?->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">تاريخ الانتهاء</label>
                                <input type="datetime-local" name="available_to" class="form-control" 
                                       value="{{ old('available_to', $quiz->available_to?->format('Y-m-d\TH:i')) }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="show_timer" 
                                           id="showTimer" {{ old('show_timer', $quiz->show_timer) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="showTimer">إظهار المؤقت</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="auto_submit" 
                                           id="autoSubmit" {{ old('auto_submit', $quiz->auto_submit) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="autoSubmit">إرسال تلقائي عند انتهاء الوقت</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- إعدادات التقييم --}}
                <div class="card custom-card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-trophy me-2"></i> إعدادات التقييم</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">نسبة النجاح (%)</label>
                                <input type="number" name="pass_percentage" class="form-control" 
                                       value="{{ old('pass_percentage', $quiz->pass_percentage) }}" min="0" max="100" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">طريقة احتساب الدرجة</label>
                                <select name="grading_method" class="form-select" required>
                                    @foreach(\App\Models\Quiz::GRADING_METHODS as $key => $value)
                                        <option value="{{ $key }}" {{ old('grading_method', $quiz->grading_method) == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">عدد المحاولات</label>
                                <input type="number" name="max_attempts" class="form-control" 
                                       value="{{ old('max_attempts', $quiz->max_attempts) }}" min="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">التأخير بين المحاولات (دقائق)</label>
                            <input type="number" name="delay_between_attempts" class="form-control" 
                                   value="{{ old('delay_between_attempts', $quiz->delay_between_attempts) }}" min="0" style="max-width: 200px;">
                        </div>
                    </div>
                </div>

                {{-- إعدادات العرض --}}
                <div class="card custom-card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-display me-2"></i> إعدادات العرض</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="shuffle_questions" 
                                           id="shuffleQuestions" {{ old('shuffle_questions', $quiz->shuffle_questions) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="shuffleQuestions">خلط ترتيب الأسئلة</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="shuffle_options" 
                                           id="shuffleOptions" {{ old('shuffle_options', $quiz->shuffle_options) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="shuffleOptions">خلط ترتيب الخيارات</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="allow_back_navigation" 
                                           id="allowBackNav" {{ old('allow_back_navigation', $quiz->allow_back_navigation) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allowBackNav">السماح بالرجوع للأسئلة السابقة</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">عدد الأسئلة في الصفحة</label>
                                    <input type="number" name="questions_per_page" class="form-control" 
                                           value="{{ old('questions_per_page', $quiz->questions_per_page) }}" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- إعدادات النتائج --}}
                <div class="card custom-card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i> إعدادات النتائج</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="show_result_immediately" 
                                           id="showResult" {{ old('show_result_immediately', $quiz->show_result_immediately) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="showResult">إظهار النتيجة فور الانتهاء</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="show_correct_answers" 
                                           id="showCorrect" {{ old('show_correct_answers', $quiz->show_correct_answers) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="showCorrect">إظهار الإجابات الصحيحة</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="show_explanation" 
                                           id="showExplanation" {{ old('show_explanation', $quiz->show_explanation) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="showExplanation">إظهار شرح الإجابات</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="show_points_per_question" 
                                           id="showPoints" {{ old('show_points_per_question', $quiz->show_points_per_question) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="showPoints">إظهار درجة كل سؤال</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">خيارات المراجعة</label>
                                <select name="review_options" class="form-select">
                                    @foreach(\App\Models\Quiz::REVIEW_OPTIONS as $key => $value)
                                        <option value="{{ $key }}" {{ old('review_options', $quiz->review_options) == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                {{-- الحالة والنشر --}}
                <div class="card custom-card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-toggle-on me-2"></i> الحالة والنشر</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" 
                                   id="isActive" {{ old('is_active', $quiz->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">الاختبار نشط</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_published" 
                                   id="isPublished" {{ old('is_published', $quiz->is_published) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isPublished">نشر للطلاب</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ترتيب العرض</label>
                            <input type="number" name="order" class="form-control" 
                                   value="{{ old('order', $quiz->order) }}" min="0">
                        </div>
                    </div>
                </div>

                {{-- إعدادات الأمان --}}
                <div class="card custom-card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-shield-lock me-2"></i> إعدادات الأمان</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="requires_password" 
                                   id="requiresPassword" {{ old('requires_password', $quiz->requires_password) ? 'checked' : '' }}
                                   onchange="togglePasswordField()">
                            <label class="form-check-label" for="requiresPassword">يتطلب كلمة مرور</label>
                        </div>
                        <div class="mb-3 {{ $quiz->requires_password ? '' : 'd-none' }}" id="passwordField">
                            <input type="text" name="password" class="form-control" 
                                   value="{{ old('password', $quiz->password) }}" placeholder="كلمة مرور الاختبار">
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="prevent_copy_paste" 
                                   id="preventCopy" {{ old('prevent_copy_paste', $quiz->prevent_copy_paste) ? 'checked' : '' }}>
                            <label class="form-check-label" for="preventCopy">منع النسخ واللصق</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="fullscreen_required" 
                                   id="fullscreenRequired" {{ old('fullscreen_required', $quiz->fullscreen_required) ? 'checked' : '' }}>
                            <label class="form-check-label" for="fullscreenRequired">يتطلب ملء الشاشة</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="require_webcam" 
                                   id="requireWebcam" {{ old('require_webcam', $quiz->require_webcam) ? 'checked' : '' }}>
                            <label class="form-check-label" for="requireWebcam">يتطلب كاميرا</label>
                        </div>
                    </div>
                </div>

                {{-- أزرار الحفظ --}}
                <div class="card custom-card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-check-lg me-1"></i> حفظ التعديلات
                        </button>
                        <a href="{{ route('admin.quizzes.show', $quiz->id) }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-lg me-1"></i> إلغاء
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

        </div>
    </div>
    <!-- End::app-content -->
@stop

@section('js')
<script>
function togglePasswordField() {
    const checkbox = document.getElementById('requiresPassword');
    const field = document.getElementById('passwordField');
    
    if (checkbox.checked) {
        field.classList.remove('d-none');
    } else {
        field.classList.add('d-none');
    }
}
</script>
@stop

