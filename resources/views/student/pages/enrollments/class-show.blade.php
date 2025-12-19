@extends('student.layouts.master')

@section('page-title')
    مواد {{ $class->name }}
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">مواد {{ $class->name }}</h4>
                <p class="mb-0 text-muted">
                    @if($class->stage)
                        {{ $class->stage->name }} - 
                    @endif
                    {{ $class->name }}
                </p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.enrollments.index') }}">طلب الانضمام</a></li>
                    <li class="breadcrumb-item active">{{ $class->name }}</li>
                </ol>
            </nav>
        </div>
        <!-- End Page Header -->

        <!-- معلومات الصف -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-1">{{ $class->name }}</h5>
                        @if($class->description)
                            <p class="text-muted mb-0">{{ $class->description }}</p>
                        @endif
                    </div>
                    <div class="text-end">
                        <button class="btn btn-primary btn-sm" onclick="requestClassEnrollment({{ $class->id }}, '{{ addslashes($class->name) }}')" type="button">
                            <i class="bi bi-plus-circle me-1"></i>
                            انضمام للصف كامل
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- كاردات المواد -->
        @if($class->subjects->count() > 0)
            <div class="row">
                @foreach($class->subjects as $subject)
                    @php
                        $isEnrolled = in_array($subject->id, $enrolledSubjectIds);
                        $isPending = in_array($subject->id, $pendingEnrollments);
                    @endphp
                    
                    <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-4">
                        <div class="card custom-card {{ $isEnrolled ? 'subject-enrolled-card' : '' }}" 
                             @if($isEnrolled) onclick="window.location.href='{{ route('student.subjects.show', $subject->id) }}'" style="cursor: pointer; transition: all 0.3s ease;" @endif>
                            @if($subject->image)
                                <img src="{{ asset('storage/' . $subject->image) }}" class="card-img-top" alt="{{ $subject->name }}">
                            @else
                                <div class="card-img-top bg-primary d-flex align-items-center justify-content-center" style="height: 150px;">
                                    <i class="bi bi-book text-white" style="font-size: 3rem;"></i>
                                </div>
                            @endif
                            <div class="card-body">
                                <h6 class="card-title fw-semibold">{{ $subject->name }}</h6>
                                @if($subject->description)
                                    <p class="card-text text-muted">{{ \Illuminate\Support\Str::limit($subject->description, 80) }}</p>
                                @endif
                                
                                @if($isEnrolled)
                                    <div onclick="event.stopPropagation();">
                                        <button class="btn btn-success btn-sm w-100" disabled>
                                            <i class="bi bi-check-circle me-1"></i>
                                            مسجل
                                        </button>
                                    </div>
                                @elseif($isPending)
                                    <div class="d-flex gap-2" onclick="event.stopPropagation();">
                                        <button class="btn btn-warning btn-sm flex-grow-1" disabled>
                                            <i class="bi bi-clock me-1"></i>
                                            قيد المراجعة
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="cancelRequest({{ $subject->id }})" title="إلغاء الطلب" type="button">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                @else
                                    <div onclick="event.stopPropagation();">
                                        <button class="btn btn-primary btn-sm w-100" onclick="requestEnrollment({{ $subject->id }}, '{{ addslashes($subject->name) }}')" type="button">
                                            <i class="bi bi-plus-circle me-1"></i>
                                            طلب الانضمام
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer">
                                @if($isEnrolled)
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="bi bi-building me-1"></i>
                                            {{ $class->name }}
                                        </small>
                                        <span class="badge bg-primary-transparent text-primary">
                                            <i class="bi bi-arrow-left me-1"></i>
                                            اضغط لعرض الدروس
                                        </span>
                                    </div>
                                @else
                                    <small class="text-muted">
                                        <i class="bi bi-building me-1"></i>
                                        {{ $class->name }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card custom-card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="mb-2">لا توجد مواد دراسية</h5>
                    <p class="text-muted mb-0">لا توجد مواد دراسية في هذا الصف</p>
                </div>
            </div>
        @endif
    </div>
    <!-- Container closed -->
</div>
<!-- main-content closed -->

<!-- Modal لتأكيد طلب الانضمام لمادة -->
<div class="modal fade" id="confirmEnrollmentModal" tabindex="-1" aria-labelledby="confirmEnrollmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <div class="avatar avatar-xl bg-primary-transparent rounded-circle mx-auto d-flex align-items-center justify-content-center">
                        <i class="bi bi-bookmark-plus fs-1 text-primary"></i>
                    </div>
                </div>
                <h5 class="modal-title mb-3" id="confirmEnrollmentModalLabel">تأكيد طلب الانضمام</h5>
                <p class="text-muted mb-4" id="confirmEnrollmentModalMessage"></p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> إلغاء
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmEnrollmentBtn">
                        <i class="bi bi-check-circle me-1"></i> تأكيد
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal لتأكيد طلب الانضمام للصف كامل -->
<div class="modal fade" id="confirmClassEnrollmentModal" tabindex="-1" aria-labelledby="confirmClassEnrollmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <div class="avatar avatar-xl bg-success-transparent rounded-circle mx-auto d-flex align-items-center justify-content-center">
                        <i class="bi bi-building-add fs-1 text-success"></i>
                    </div>
                </div>
                <h5 class="modal-title mb-3" id="confirmClassEnrollmentModalLabel">تأكيد طلب الانضمام للصف</h5>
                <p class="text-muted mb-4" id="confirmClassEnrollmentModalMessage"></p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> إلغاء
                    </button>
                    <button type="button" class="btn btn-success" id="confirmClassEnrollmentBtn">
                        <i class="bi bi-check-circle me-1"></i> تأكيد
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal لتأكيد إلغاء الطلب -->
<div class="modal fade" id="confirmCancelModal" tabindex="-1" aria-labelledby="confirmCancelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <div class="avatar avatar-xl bg-danger-transparent rounded-circle mx-auto d-flex align-items-center justify-content-center">
                        <i class="bi bi-x-octagon fs-1 text-danger"></i>
                    </div>
                </div>
                <h5 class="modal-title mb-3" id="confirmCancelModalLabel">تأكيد إلغاء الطلب</h5>
                <p class="text-muted mb-4">هل أنت متأكد من إلغاء طلب الانضمام؟</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-arrow-right me-1"></i> تراجع
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmCancelBtn">
                        <i class="bi bi-trash me-1"></i> إلغاء الطلب
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .subject-enrolled-card {
        transition: all 0.3s ease;
    }
    .subject-enrolled-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
    }
</style>
@stop

@section('script')
<script>
    // التأكد من تحميل الصفحة بالكامل
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Enrollment page loaded');
        initializeModals();
    });
    
    // الحصول على CSRF token من meta tag
    function getCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            return metaTag.getAttribute('content');
        }
        return '{{ csrf_token() }}';
    }
    
    // متغيرات لتخزين البيانات المؤقتة
    let pendingSubjectId = null;
    let pendingSubjectName = null;
    let pendingClassId = null;
    let pendingClassName = null;
    let pendingCancelSubjectId = null;
    let currentButton = null;
    
    // تهيئة المودالات
    function initializeModals() {
        // مودال طلب الانضمام لمادة
        const confirmEnrollmentBtn = document.getElementById('confirmEnrollmentBtn');
        if (confirmEnrollmentBtn) {
            confirmEnrollmentBtn.addEventListener('click', function() {
                if (pendingSubjectId) {
                    processEnrollmentRequest(pendingSubjectId, pendingSubjectName, currentButton);
                }
                const modal = bootstrap.Modal.getInstance(document.getElementById('confirmEnrollmentModal'));
                if (modal) modal.hide();
            });
        }
        
        // مودال طلب الانضمام للصف كامل
        const confirmClassEnrollmentBtn = document.getElementById('confirmClassEnrollmentBtn');
        if (confirmClassEnrollmentBtn) {
            confirmClassEnrollmentBtn.addEventListener('click', function() {
                if (pendingClassId) {
                    processClassEnrollmentRequest(pendingClassId, pendingClassName, currentButton);
                }
                const modal = bootstrap.Modal.getInstance(document.getElementById('confirmClassEnrollmentModal'));
                if (modal) modal.hide();
            });
        }
        
        // مودال إلغاء الطلب
        const confirmCancelBtn = document.getElementById('confirmCancelBtn');
        if (confirmCancelBtn) {
            confirmCancelBtn.addEventListener('click', function() {
                if (pendingCancelSubjectId) {
                    processCancelRequest(pendingCancelSubjectId);
                }
                const modal = bootstrap.Modal.getInstance(document.getElementById('confirmCancelModal'));
                if (modal) modal.hide();
            });
        }
    }
    
    function requestEnrollment(subjectId, subjectName) {
        console.log('requestEnrollment called with:', subjectId, subjectName);
        
        pendingSubjectId = subjectId;
        pendingSubjectName = subjectName;
        currentButton = event.target;
        
        // تحديث رسالة المودال
        const messageEl = document.getElementById('confirmEnrollmentModalMessage');
        if (messageEl) {
            messageEl.textContent = 'هل أنت متأكد من طلب الانضمام إلى مادة "' + subjectName + '"؟';
        }
        
        // إظهار المودال
        const modal = new bootstrap.Modal(document.getElementById('confirmEnrollmentModal'));
        modal.show();
    }
    
    function processEnrollmentRequest(subjectId, subjectName, button) {
        const url = '{{ route("student.enrollments.request", ":id") }}'.replace(':id', subjectId);
        const csrfToken = getCsrfToken();
        
        console.log('Requesting enrollment for subject:', subjectId, 'URL:', url, 'CSRF:', csrfToken);
        
        // تعطيل الزر أثناء المعالجة
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> جاري الإرسال...';
        }
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({})
        })
        .then(response => {
            console.log('Response status:', response.status, response.statusText);
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'حدث خطأ في الطلب');
                }).catch(err => {
                    if (err.message) throw err;
                    throw new Error('حدث خطأ في الاتصال بالخادم');
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                showSuccessMessage(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showErrorMessage(data.message || 'حدث خطأ أثناء إرسال الطلب');
                if (button) {
                    button.disabled = false;
                    button.innerHTML = '<i class="bi bi-plus-circle me-1"></i> طلب الانضمام';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorMessage(error.message || 'حدث خطأ أثناء إرسال الطلب. يرجى المحاولة مرة أخرى.');
            if (button) {
                button.disabled = false;
                button.innerHTML = '<i class="bi bi-plus-circle me-1"></i> طلب الانضمام';
            }
        });
    }
    
    function requestClassEnrollment(classId, className) {
        console.log('requestClassEnrollment called with:', classId, className);
        
        pendingClassId = classId;
        pendingClassName = className;
        currentButton = event.target;
        
        // تحديث رسالة المودال
        const messageEl = document.getElementById('confirmClassEnrollmentModalMessage');
        if (messageEl) {
            messageEl.textContent = 'هل أنت متأكد من طلب الانضمام لجميع مواد صف "' + className + '"؟';
        }
        
        // إظهار المودال
        const modal = new bootstrap.Modal(document.getElementById('confirmClassEnrollmentModal'));
        modal.show();
    }
    
    function processClassEnrollmentRequest(classId, className, button) {
        const url = '{{ route("student.enrollments.request-class", ":id") }}'.replace(':id', classId);
        const csrfToken = getCsrfToken();
        
        console.log('Requesting enrollment for class:', classId, 'URL:', url, 'CSRF:', csrfToken);
        
        // تعطيل الزر أثناء المعالجة
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> جاري الإرسال...';
        }
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({})
        })
        .then(response => {
            console.log('Response status:', response.status, response.statusText);
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'حدث خطأ في الطلب');
                }).catch(err => {
                    if (err.message) throw err;
                    throw new Error('حدث خطأ في الاتصال بالخادم');
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                showSuccessMessage(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showErrorMessage(data.message || 'حدث خطأ أثناء إرسال الطلب');
                if (button) {
                    button.disabled = false;
                    button.innerHTML = '<i class="bi bi-plus-circle me-1"></i> انضمام للصف كامل';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorMessage(error.message || 'حدث خطأ أثناء إرسال الطلب. يرجى المحاولة مرة أخرى.');
            if (button) {
                button.disabled = false;
                button.innerHTML = '<i class="bi bi-plus-circle me-1"></i> انضمام للصف كامل';
            }
        });
    }
    
    function cancelRequest(subjectId) {
        console.log('cancelRequest called with:', subjectId);
        
        pendingCancelSubjectId = subjectId;
        
        // إظهار المودال
        const modal = new bootstrap.Modal(document.getElementById('confirmCancelModal'));
        modal.show();
    }
    
    function processCancelRequest(subjectId) {
        const url = '{{ route("student.enrollments.cancel", ":id") }}'.replace(':id', subjectId);
        const csrfToken = getCsrfToken();
        
        console.log('Canceling enrollment for subject:', subjectId, 'URL:', url, 'CSRF:', csrfToken);
        
        fetch(url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Response status:', response.status, response.statusText);
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'حدث خطأ في الطلب');
                }).catch(err => {
                    if (err.message) throw err;
                    throw new Error('حدث خطأ في الاتصال بالخادم');
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                showSuccessMessage(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showErrorMessage(data.message || 'حدث خطأ أثناء إلغاء الطلب');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorMessage(error.message || 'حدث خطأ أثناء إلغاء الطلب. يرجى المحاولة مرة أخرى.');
        });
    }
    
    // دوال لعرض الرسائل
    function showSuccessMessage(message) {
        // يمكن استبدالها بـ toast notification لاحقاً
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `
            <i class="bi bi-check-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 5000);
    }
    
    function showErrorMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `
            <i class="bi bi-exclamation-triangle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 5000);
    }
    
    // جعل الدوال متاحة عالمياً
    window.requestEnrollment = requestEnrollment;
    window.requestClassEnrollment = requestClassEnrollment;
    window.cancelRequest = cancelRequest;
</script>
@stop

