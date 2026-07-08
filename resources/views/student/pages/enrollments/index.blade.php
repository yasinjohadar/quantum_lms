@extends('student.layouts.master')

@section('page-title')
    طلب الانضمام للمواد الدراسية
@stop

@section('content')
@include('student.pages.enrollments.partials.enrollment-page-styles')
<!-- Start::app-content -->
<div class="main-content app-content enrollments-page">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex align-items-start gap-3 my-4 page-header-breadcrumb">
            <div class="enrollments-page__header-icon" aria-hidden="true">
                <i class="bi bi-mortarboard-fill fs-5"></i>
            </div>
            <div>
                <h4 class="mb-1">طلب الانضمام للمواد الدراسية</h4>
                <p class="mb-0 text-muted">تصفح الصفوف والمواد المتاحة واطلب الانضمام للبدء في رحلتك التعليمية</p>
            </div>
        </div>
        <!-- End Page Header -->

        @include('student.partials.enrollment-required-alert')

        @include('student.pages.enrollments.partials.stats-summary')
        @include('student.partials.pending-purchases-review-banner')

        @if($stages->count() > 0)
            @php
                $pendingClassIdSet = isset($pendingClassEnrollmentIds) ? array_flip($pendingClassEnrollmentIds) : [];
            @endphp
            <div id="enrollment-classes-list">
                @foreach($stages as $stage)
                    <section class="enrollment-stage-section">
                        <div class="enrollment-stage-section__header">
                            <span class="enrollment-stage-section__header-icon" aria-hidden="true">
                                <i class="bi bi-layers-fill"></i>
                            </span>
                            <h5 class="enrollment-stage-section__title">{{ $stage->name }}</h5>
                        </div>
                        <div class="enrollment-stage-section__body">
                            @if($stage->classes->count() > 0)
                                <div class="row">
                                    @foreach($stage->classes as $class)
                                        @include('student.pages.enrollments.partials.class-card', [
                                            'class' => $class,
                                            'pendingClassIdSet' => $pendingClassIdSet,
                                        ])
                                    @endforeach
                                </div>
                            @else
                                <div class="enrollment-empty-state py-4">
                                    <p class="text-muted mb-0">لا توجد صفوف في هذه المرحلة حالياً</p>
                                </div>
                            @endif
                        </div>
                    </section>
                @endforeach
            </div>
        @else
            <div class="enrollment-empty-state">
                <div class="enrollment-empty-state__icon">
                    <i class="bi bi-book"></i>
                </div>
                <h5 class="mb-2">لا توجد مواد متاحة</h5>
                <p class="text-muted mb-0">لا توجد مواد دراسية متاحة للانضمام حالياً. يرجى مراجعة الإدارة لاحقاً.</p>
            </div>
        @endif
    </div>
    <!-- Container closed -->
</div>
<!-- main-content closed -->

<!-- Modal لتأكيد طلب الانضمام للصف -->
<div class="modal fade enrollment-confirm-modal" id="confirmClassEnrollmentModal" tabindex="-1" aria-labelledby="confirmClassEnrollmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="enrollment-confirm-modal__hero" aria-hidden="true">
                    <i class="bi bi-buildings"></i>
                </div>
                <h5 class="enrollment-confirm-modal__title" id="confirmClassEnrollmentModalLabel">طلب الانضمام للصف</h5>
                <p class="enrollment-confirm-modal__message" id="confirmClassEnrollmentModalMessage">
                    هل تريد طلب الانضمام لجميع المواد في صف <strong id="classNameInModal"></strong>؟
                </p>
                <div class="enrollment-confirm-modal__note">
                    <i class="bi bi-shield-check"></i>
                    سيتم إرسال الطلب مباشرة إلى الإدارة للمراجعة
                </div>
                <div class="enrollment-confirm-modal__actions">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> إلغاء
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmClassEnrollmentBtn">
                        <i class="bi bi-check2-circle me-1"></i> تأكيد الانضمام
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal الدفع (صف مدفوع) -->
<div class="modal fade" id="classEnrollmentPaymentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-scrollable modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إتمام الدفع</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="classEnrollmentPaymentModalBody"></div>
        </div>
    </div>
</div>

@include('student.pages.enrollments.partials.pending-review-modal')
@include('student.pages.purchases.partials.payment-pending-modal')
@stop

@push('scripts')
@include('student.pages.enrollments.partials.inline-purchase-payment-script')
<script>
    let pendingClassId = null;
    let pendingClassName = null;
    let pendingClassButton = null;
    
    function requestClassEnrollment(classId, className, requiresPayment, triggerButton) {
        pendingClassId = classId;
        pendingClassName = className || '';
        pendingClassButton = triggerButton || null;
        document.getElementById('classNameInModal').textContent = className;
        var msgEl = document.getElementById('confirmClassEnrollmentModalMessage');
        if (requiresPayment) {
            msgEl.innerHTML = 'سيتم إرسال طلب انضمامك لـ <strong>' + className + '</strong> إلى الإدارة للمراجعة. ' +
                'بعد الإرسال يمكنك متابعة القبول عبر واتساب قسم الإشراف.';
        } else {
            msgEl.innerHTML = 'هل تريد طلب الانضمام لجميع المواد في صف <strong>' + className + '</strong>؟';
        }
        var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmClassEnrollmentModal'));
        modal.show();
    }
    
    document.getElementById('confirmClassEnrollmentBtn').addEventListener('click', function() {
        if (!pendingClassId) return;
        
        var btn = this;
        var submittedClassName = pendingClassName || '';
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جاري الإرسال...';

        if (pendingClassButton) {
            pendingClassButton.disabled = true;
        }
        
        fetch('/student/enrollments/request-class/' + pendingClassId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            var confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmClassEnrollmentModal'));
            if (confirmModal) {
                confirmModal.hide();
            }

            if (data.success) {
                if (data.under_review) {
                    setTimeout(function () {
                        showEnrollmentPendingReviewModal(data.message, {
                            requiresWhatsappFollowup: !!data.requires_whatsapp_followup,
                            className: submittedClassName
                        });
                    }, 300);
                } else {
                    showAlert('success', data.message || 'تم إرسال طلب الانضمام بنجاح!');
                    setTimeout(function () { window.location.reload(); }, 800);
                }
            } else {
                showAlert('warning', data.message || 'حدث خطأ أثناء إرسال الطلب');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            var confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmClassEnrollmentModal'));
            if (confirmModal) {
                confirmModal.hide();
            }
            showAlert('danger', 'حدث خطأ في الاتصال. حاول مرة أخرى.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check me-1"></i> تأكيد الانضمام';
            if (pendingClassButton) {
                pendingClassButton.disabled = false;
            }
            pendingClassId = null;
            pendingClassName = null;
            pendingClassButton = null;
        });
    });

    var confirmClassEnrollmentModalEl = document.getElementById('confirmClassEnrollmentModal');
    if (confirmClassEnrollmentModalEl) {
        confirmClassEnrollmentModalEl.addEventListener('hidden.bs.modal', function () {
            pendingClassId = null;
            pendingClassName = null;
            pendingClassButton = null;
        });
    }
    
    var payModalEl = document.getElementById('classEnrollmentPaymentModal');
    if (payModalEl) {
        payModalEl.addEventListener('hidden.bs.modal', function () {
            var body = document.getElementById('classEnrollmentPaymentModalBody');
            if (body) {
                body.innerHTML = '';
            }
        });
    }

    function showEnrollmentPendingReviewModal(message, options) {
        options = options || {};
        var classNameEl = document.getElementById('enrollmentPendingReviewClassName');
        if (classNameEl) {
            classNameEl.textContent = options.className || '';
        }
        var msgEl = document.getElementById('enrollmentPendingReviewModalMessage');
        if (msgEl) {
            if (options.className) {
                msgEl.classList.add('d-none');
                msgEl.textContent = '';
            } else {
                msgEl.classList.remove('d-none');
                msgEl.textContent = message || 'تم إرسال طلب انضمامك إلى الإدارة للمراجعة، وهو بانتظار القبول.';
            }
        }
        var whatsappAlertEl = document.getElementById('enrollmentPendingReviewWhatsappAlert');
        if (whatsappAlertEl) {
            whatsappAlertEl.style.display = options.requiresWhatsappFollowup ? '' : 'none';
        }

        var modalEl = document.getElementById('enrollmentPendingReviewModal');
        if (!modalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            showAlert('info', message || 'تم استلام طلبك وهو قيد المراجعة.');
            setTimeout(function () { window.location.reload(); }, 1500);
            return;
        }

        modalEl.addEventListener('hidden.bs.modal', function () {
            window.location.reload();
        }, { once: true });

        var instance = bootstrap.Modal.getInstance(modalEl);
        if (instance) {
            instance.show();
        } else {
            new bootstrap.Modal(modalEl).show();
        }
    }

    function showAlert(type, message) {
        var alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-' + type + ' alert-dismissible fade show position-fixed';
        alertDiv.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
        alertDiv.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        document.body.appendChild(alertDiv);

        setTimeout(function () {
            alertDiv.remove();
        }, 5000);
    }
</script>
@endpush

