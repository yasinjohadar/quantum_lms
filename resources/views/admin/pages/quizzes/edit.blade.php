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
                        @include('admin.pages.quizzes.partials.curriculum-cascade-fields', [
                            'stages' => $stages,
                            'selectedStageId' => $selectedStageId ?? null,
                            'selectedClassId' => $selectedClassId ?? null,
                            'selectedSubjectId' => old('subject_id', $selectedSubjectId ?? ''),
                            'selectedUnitId' => old('unit_id', $selectedUnitId ?? ''),
                        ])

                        {{-- عرض نوع الاختبار (للمعلومة فقط حالياً) --}}
                        <div class="mb-3">
                            <label class="form-label d-block">نوع الاختبار</label>
                            @if($quiz->lesson_id)
                                <div class="alert alert-info py-2 mb-0">
                                    هذا الاختبار من نوع <strong>اختبار درس</strong> ومربوط بدرس معيّن داخل الوحدة.
                                    <br>
                                    يمكن تغيير المادة/الوحدة، لكن ارتباطه بالدرس الحالي يبقى كما هو من الكود حالياً.
                                </div>
                            @else
                                <div class="alert alert-info py-2 mb-0">
                                    هذا الاختبار من نوع <strong>اختبار عام للوحدة</strong> وغير مرتبط بدرس معيّن.
                                </div>
                            @endif
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
                                    <img src="{{ media_public_url($quiz->image) }}" class="rounded" style="max-width: 150px;">
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

                {{-- قيم افتراضية مخفية (إعدادات العرض والنتائج) --}}
                {{-- @TODO: Re-enable display settings section in edit form when ready --}}
                <input type="hidden" name="shuffle_questions" value="{{ old('shuffle_questions', $quiz->shuffle_questions ?? false) ? '1' : '0' }}">
                <input type="hidden" name="shuffle_options" value="{{ old('shuffle_options', $quiz->shuffle_options ?? false) ? '1' : '0' }}">
                <input type="hidden" name="allow_back_navigation" value="{{ old('allow_back_navigation', $quiz->allow_back_navigation ?? true) ? '1' : '0' }}">
                <input type="hidden" name="questions_per_page" value="{{ old('questions_per_page', $quiz->questions_per_page ?? 0) }}">
                <input type="hidden" name="show_result_immediately" value="{{ old('show_result_immediately', $quiz->show_result_immediately ?? true) ? '1' : '0' }}">
                <input type="hidden" name="show_correct_answers" value="{{ old('show_correct_answers', $quiz->show_correct_answers ?? true) ? '1' : '0' }}">
                <input type="hidden" name="show_explanation" value="{{ old('show_explanation', $quiz->show_explanation ?? true) ? '1' : '0' }}">
                <input type="hidden" name="show_points_per_question" value="{{ old('show_points_per_question', $quiz->show_points_per_question ?? true) ? '1' : '0' }}">
                <input type="hidden" name="review_options" value="{{ old('review_options', $quiz->review_options ?? 'immediately') }}">
            </div>

            <div class="col-lg-4">
                {{-- الحالة والنشر --}}
                <div class="card custom-card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-toggle-on me-2"></i> الحالة والنشر</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $user = auth()->user();
                            $isTeacher = $user->shouldSubmitContentForReview();
                        @endphp
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" 
                                   id="isActive" {{ (old('is_active', $quiz->is_active) || $quiz->is_published) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">تفعيل الاختبار</label>
                        </div>
                        {{-- حالة المراجعة --}}
                        <div class="mb-3">
                            <label class="form-label">حالة المراجعة</label>
                            <div>
                                <span class="badge bg-{{ $quiz->review_status_color }}">
                                    {{ $quiz->review_status_name }}
                                </span>
                            </div>
                            @if($quiz->review_notes)
                                <div class="mt-2">
                                    <small class="text-muted"><strong>ملاحظات المراجعة:</strong></small>
                                    <p class="mb-0 small">{{ $quiz->review_notes }}</p>
                                </div>
                            @endif
                            @if($quiz->reviewed_at)
                                <div class="mt-1">
                                    <small class="text-muted">
                                        مراجع من: {{ $quiz->reviewer->name ?? 'غير معروف' }} 
                                        في {{ $quiz->reviewed_at->format('Y-m-d H:i') }}
                                    </small>
                                </div>
                            @endif
                        </div>
                        
                        {{-- أزرار المراجعة للمعلم --}}
                        @if($isTeacher && in_array($quiz->review_status, [\App\Models\Quiz::REVIEW_STATUS_DRAFT, \App\Models\Quiz::REVIEW_STATUS_REJECTED]))
                            <div class="mb-3">
                                <form action="{{ route('admin.quizzes.submit-for-review', $quiz->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm w-100" 
                                            onclick="return confirm('هل أنت متأكد من إرسال الاختبار للمراجعة؟')">
                                        <i class="bi bi-send me-1"></i> إرسال للمراجعة
                                    </button>
                                </form>
                            </div>
                        @endif
                        
                        {{-- أزرار المراجعة للمشرف/الأدمن --}}
                        @if(!$isTeacher && $quiz->review_status === \App\Models\Quiz::REVIEW_STATUS_PENDING)
                            <div class="mb-3">
                                <button type="button" class="btn btn-success btn-sm w-100 mb-2" 
                                        data-bs-toggle="modal" data-bs-target="#approveModal">
                                    <i class="bi bi-check-circle me-1"></i> الموافقة على النشر
                                </button>
                                <button type="button" class="btn btn-danger btn-sm w-100" 
                                        data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i class="bi bi-x-circle me-1"></i> رفض النشر
                                </button>
                            </div>
                        @endif
                        
                        <div class="mb-3">
                            <label class="form-label">ترتيب العرض</label>
                            <input type="number" name="order" class="form-control" 
                                   value="{{ old('order', $quiz->order) }}" min="0">
                        </div>
                    </div>
                </div>
                
                {{-- Modal الموافقة --}}
                @if(!$isTeacher && $quiz->review_status === \App\Models\Quiz::REVIEW_STATUS_PENDING)
                    <div class="modal fade" id="approveModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.quizzes.approve-review', $quiz->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">الموافقة على نشر الاختبار</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">ملاحظات (اختياري)</label>
                                            <textarea name="review_notes" class="form-control" rows="3" 
                                                      placeholder="أضف ملاحظات للمعلم..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                        <button type="submit" class="btn btn-success">الموافقة</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Modal الرفض --}}
                    <div class="modal fade" id="rejectModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.quizzes.reject-review', $quiz->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">رفض نشر الاختبار</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">ملاحظات <span class="text-danger">*</span></label>
                                            <textarea name="review_notes" class="form-control" rows="3" 
                                                      placeholder="أضف ملاحظات للمعلم..." required></textarea>
                                            <small class="text-muted">يجب إضافة ملاحظات توضح سبب الرفض</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                        <button type="submit" class="btn btn-danger">رفض</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- إعدادات الأمان (مخفية) --}}
                <div class="card custom-card mb-3 d-none">
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
@include('admin.pages.quizzes.partials.curriculum-cascade-script', [
    'selectedStageId' => $selectedStageId ?? null,
    'selectedClassId' => $selectedClassId ?? null,
    'selectedSubjectId' => old('subject_id', $selectedSubjectId ?? ''),
    'selectedUnitId' => old('unit_id', $selectedUnitId ?? ''),
])
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

