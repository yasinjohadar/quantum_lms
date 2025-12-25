@extends('student.layouts.master')

@section('page-title')
    طلب الانضمام للمواد الدراسية
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">طلب الانضمام للمواد الدراسية</h4>
                <p class="mb-0 text-muted">تصفح الصفوف والمواد المتاحة واطلب الانضمام</p>
            </div>
        </div>
        <!-- End Page Header -->

        @if($stages->count() > 0)
            @foreach($stages as $stage)
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-mortarboard me-2"></i>
                            {{ $stage->name }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($stage->classes->count() > 0)
                            <div class="row">
                                @foreach($stage->classes as $class)
                                    <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-4">
                                        <!-- كارد الصف -->
                                        <div class="card custom-card h-100">
                                            <a href="{{ route('student.enrollments.class.show', $class->id) }}" class="text-decoration-none">
                                                @if($class->image)
                                                    <img src="{{ asset('storage/' . $class->image) }}" class="card-img-top" alt="{{ $class->name }}" style="height: 180px; object-fit: cover;">
                                                @else
                                                    <div class="card-img-top bg-primary d-flex align-items-center justify-content-center" style="height: 180px;">
                                                        <i class="bi bi-building text-white" style="font-size: 4rem;"></i>
                                                    </div>
                                                @endif
                                            </a>
                                            <div class="card-body">
                                                <a href="{{ route('student.enrollments.class.show', $class->id) }}" class="text-decoration-none">
                                                    <h6 class="card-title fw-semibold text-dark">{{ $class->name }}</h6>
                                                    @if($class->description)
                                                        <p class="card-text text-muted small">{{ \Illuminate\Support\Str::limit($class->description, 80) }}</p>
                                                    @endif
                                                </a>
                                                <div class="d-flex align-items-center justify-content-between mt-3">
                                                    <span class="text-muted small">
                                                        <i class="bi bi-book me-1"></i>
                                                        {{ $class->subjects()->where('is_active', true)->count() }} مادة
                                                    </span>
                                                    <button class="btn btn-primary btn-sm" onclick="requestClassEnrollment({{ $class->id }}, '{{ addslashes($class->name) }}')" type="button">
                                                        <i class="bi bi-plus-circle me-1"></i>
                                                        انضم للصف
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-footer bg-light">
                                                <a href="{{ route('student.enrollments.class.show', $class->id) }}" class="text-primary text-decoration-none d-flex align-items-center justify-content-center">
                                                    <i class="bi bi-eye me-2"></i>
                                                    عرض المواد والتفاصيل
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0 text-center">لا توجد صفوف في هذه المرحلة</p>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <div class="card custom-card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-book fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="mb-2">لا توجد مواد متاحة</h5>
                    <p class="text-muted mb-0">لا توجد مواد دراسية متاحة للانضمام حالياً</p>
                </div>
            </div>
        @endif
    </div>
    <!-- Container closed -->
</div>
<!-- main-content closed -->

<!-- Modal لتأكيد طلب الانضمام للصف -->
<div class="modal fade" id="confirmClassEnrollmentModal" tabindex="-1" aria-labelledby="confirmClassEnrollmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <div class="avatar avatar-xl bg-primary-transparent rounded-circle mx-auto d-flex align-items-center justify-content-center">
                        <i class="bi bi-building fs-1 text-primary"></i>
                    </div>
                </div>
                <h5 class="modal-title mb-3" id="confirmClassEnrollmentModalLabel">طلب الانضمام للصف</h5>
                <p class="text-muted mb-4">
                    هل تريد طلب الانضمام لجميع المواد في صف <strong id="classNameInModal"></strong>؟
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x me-1"></i> إلغاء
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmClassEnrollmentBtn">
                        <i class="bi bi-check me-1"></i> تأكيد الانضمام
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('scripts')
<script>
    let pendingClassId = null;
    
    function requestClassEnrollment(classId, className) {
        pendingClassId = classId;
        document.getElementById('classNameInModal').textContent = className;
        var modal = new bootstrap.Modal(document.getElementById('confirmClassEnrollmentModal'));
        modal.show();
    }
    
    document.getElementById('confirmClassEnrollmentBtn').addEventListener('click', function() {
        if (!pendingClassId) return;
        
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جاري الإرسال...';
        
        fetch('/student/enrollments/request-class/' + pendingClassId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            var modal = bootstrap.Modal.getInstance(document.getElementById('confirmClassEnrollmentModal'));
            modal.hide();
            
            if (data.success) {
                // عرض رسالة نجاح
                showAlert('success', data.message || 'تم إرسال طلب الانضمام بنجاح!');
            } else {
                showAlert('warning', data.message || 'حدث خطأ أثناء إرسال الطلب');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            var modal = bootstrap.Modal.getInstance(document.getElementById('confirmClassEnrollmentModal'));
            modal.hide();
            showAlert('danger', 'حدث خطأ في الاتصال. حاول مرة أخرى.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check me-1"></i> تأكيد الانضمام';
            pendingClassId = null;
        });
    });
    
    function showAlert(type, message) {
        var alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-' + type + ' alert-dismissible fade show position-fixed';
        alertDiv.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
        alertDiv.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        document.body.appendChild(alertDiv);
        
        setTimeout(function() {
            alertDiv.remove();
        }, 5000);
    }
</script>
@endpush

