@extends('student.layouts.master')

@section('page-title')
    مواد {{ $class->name }}
@stop

@section('content')
@include('student.pages.enrollments.partials.enrollment-page-styles')
<!-- Start::app-content -->
<div class="main-content app-content enrollments-page">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 my-4 page-header-breadcrumb">
            <div class="d-flex align-items-start gap-3">
                <div class="enrollments-page__header-icon" aria-hidden="true">
                    <i class="bi bi-journal-bookmark-fill fs-5"></i>
                </div>
                <div>
                    <h4 class="mb-1">مواد {{ $class->name }}</h4>
                    <p class="mb-0 text-muted">
                        @if($class->stage)
                            {{ $class->stage->name }} —
                        @endif
                        {{ $class->name }}
                    </p>
                </div>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ ($studentNeedsEnrollment ?? false) ? route('student.enrollments.index') : route('student.dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.enrollments.index') }}">طلب الانضمام</a></li>
                    <li class="breadcrumb-item active">{{ $class->name }}</li>
                </ol>
            </nav>
        </div>
        <!-- End Page Header -->

        @include('student.partials.enrollment-required-alert')

        @include('student.pages.enrollments.partials.stats-summary')
        @include('student.partials.pending-purchases-review-banner')

        <!-- معلومات الصف -->
        <div class="enrollment-class-hero">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h5 class="mb-2 d-flex align-items-center flex-wrap gap-2">
                        <span>{{ $class->name }}</span>
                        @if($class->classJoinRequiresPayment())
                            <span class="enrollment-class-card__badge enrollment-class-card__badge--paid position-static">
                                <i class="bi bi-star-fill" aria-hidden="true"></i>مدفوع
                            </span>
                        @else
                            <span class="enrollment-class-card__badge enrollment-class-card__badge--free position-static">
                                <i class="bi bi-gift-fill" aria-hidden="true"></i>مجاني
                            </span>
                        @endif
                    </h5>
                    @if($class->description)
                        <p class="text-muted mb-0">{{ $class->description }}</p>
                    @endif
                </div>
                <div class="text-end d-flex gap-2">
                    @if($hasFullClassAccess)
                        <span class="btn btn-success btn-sm disabled">
                            <i class="bi bi-check-circle me-1"></i>
                            منضم لجميع المواد
                        </span>
                    @elseif($hasPendingClassRequest)
                        <span class="btn btn-warning btn-sm disabled" title="طلب انضمام الصف قيد المراجعة">
                            <i class="bi bi-clock me-1"></i>
                            طلب الصف قيد المراجعة
                        </span>
                    @elseif($class->subjects->isNotEmpty())
                        <button class="btn btn-primary btn-sm enrollment-class-card__btn-join" onclick="requestClassEnrollment({{ $class->id }}, '{{ addslashes($class->name) }}', {{ $class->classJoinRequiresPayment() ? 'true' : 'false' }}, this)" type="button">
                            <i class="bi bi-plus-circle me-1"></i>
                            انضمام للصف كامل
                        </button>
                    @endif
                </div>
            </div>
        </div>

        @if($class->whatsapp_group_url && $hasFullClassAccess)
        <div class="card custom-card mb-4 border-success">
            <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                        <i class="fa-brands fa-whatsapp text-success" style="font-size: 1.75rem;"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">انضم لمجموعة واتساب الخاصة بهذا الصف</h6>
                        <p class="text-muted mb-0 small">تواصل مع زملائك والمعلمين وكن على اطلاع بآخر المستجدات.</p>
                    </div>
                </div>
                <a href="{{ $class->whatsapp_group_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-success">
                    <i class="fa-brands fa-whatsapp me-2"></i>
                    انضم للمجموعة
                </a>
            </div>
        </div>
        @endif

        <!-- كاردات المواد (المواد ذات التسجيل النشط لا تُعرض هنا) -->
        @if($subjectsToShow->count() > 0)
            <div class="row">
                @foreach($subjectsToShow as $subject)
                    @php
                        $isPending = in_array($subject->id, $pendingEnrollments);
                        $pendingPurchaseId = $pendingSubjectPurchaseIds[$subject->id] ?? null;
                    @endphp

                    <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-3 mb-md-4">
                        <article class="enrollment-subject-card">
                            <div class="enrollment-subject-card__media">
                                @if($subject->image)
                                    <img src="{{ media_public_url($subject->image) }}" alt="{{ $subject->name }}">
                                @else
                                    <div class="enrollment-subject-card__media-placeholder">
                                        <i class="bi bi-book"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="enrollment-subject-card__body">
                                <h6 class="fw-bold mb-2">{{ $subject->name }}</h6>
                                @if($subject->description)
                                    <p class="text-muted small mb-3">{{ \Illuminate\Support\Str::limit($subject->description, 90) }}</p>
                                @endif

                                @if($isPending || $pendingPurchaseId)
                                    <div class="d-flex gap-2" onclick="event.stopPropagation();">
                                        <button class="btn btn-warning btn-sm flex-grow-1 enrollment-class-card__btn-pending" disabled>
                                            <i class="bi bi-clock me-1"></i>
                                            قيد المراجعة
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm"
                                                onclick="{{ $pendingPurchaseId ? 'cancelPendingPurchase('.$pendingPurchaseId.')' : 'cancelRequest('.$subject->id.')' }}"
                                                data-purchase-id="{{ $pendingPurchaseId }}"
                                                title="إلغاء الطلب"
                                                type="button">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                @elseif($hasPendingClassRequest)
                                    <div onclick="event.stopPropagation();">
                                        <button class="btn btn-secondary btn-sm w-100" type="button" disabled title="طلب الانضمام للصف الكامل قيد المراجعة">
                                            <i class="bi bi-clock me-1"></i>
                                            انتظر قبول الصف الكامل
                                        </button>
                                    </div>
                                @else
                                    @php
                                        $access = $subjectAccessById[$subject->id] ?? null;
                                    @endphp
                                    <div onclick="event.stopPropagation();">
                                        @if($access && ($access['can_access'] ?? false))
                                            <a href="{{ route('student.subjects.show', $subject->id) }}" class="btn btn-success btn-sm w-100">
                                                <i class="bi bi-box-arrow-in-right me-1"></i>
                                                دخول المادة
                                            </a>
                                        @elseif($access && ($access['can_purchase'] ?? false))
                                            @if(($access['show_price'] ?? false) && !empty($access['display_price']))
                                                <p class="small text-muted mb-2 text-center">{{ $access['display_price'] }}</p>
                                            @endif
                                            <button class="btn btn-warning btn-sm w-100" onclick="requestEnrollment({{ $subject->id }}, '{{ addslashes($subject->name) }}', this)" type="button">
                                                <i class="bi bi-send-plus me-1"></i>
                                                طلب الانضمام
                                            </button>
                                        @elseif($access && ($access['pricing_mode'] ?? '') === 'free' && !($access['can_access'] ?? false))
                                            <button class="btn btn-primary btn-sm w-100 enrollment-class-card__btn-join" onclick="requestEnrollment({{ $subject->id }}, '{{ addslashes($subject->name) }}', this)" type="button">
                                                <i class="bi bi-plus-circle me-1"></i>
                                                طلب الانضمام
                                            </button>
                                        @elseif($access && !($access['can_purchase_separately'] ?? true))
                                            <button class="btn btn-outline-secondary btn-sm w-100" type="button" disabled title="هذه المادة متاحة فقط عبر شراء الصف الكامل">
                                                <i class="bi bi-building me-1"></i>
                                                عبر الصف فقط
                                            </button>
                                        @else
                                            <button class="btn btn-primary btn-sm w-100 enrollment-class-card__btn-join" onclick="requestEnrollment({{ $subject->id }}, '{{ addslashes($subject->name) }}', this)" type="button">
                                                <i class="bi bi-plus-circle me-1"></i>
                                                طلب الانضمام
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="enrollment-subject-card__footer">
                                <i class="bi bi-building me-1" aria-hidden="true"></i>
                                {{ $class->name }}
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        @elseif($hasFullClassAccess)
            <div class="enrollment-empty-state border-success">
                <div class="enrollment-empty-state__icon bg-success-transparent text-success">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h5 class="mb-2">أنت مسجل في جميع مواد هذا الصف</h5>
                <p class="text-muted mb-3">لا توجد مواد إضافية متاحة للانضمام من هذه الصفحة.</p>
                <a href="{{ route('student.enrollments.index') }}" class="btn btn-outline-primary">العودة إلى قائمة الصفوف</a>
            </div>
        @else
            <div class="enrollment-empty-state">
                <div class="enrollment-empty-state__icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <h5 class="mb-2">لا توجد مواد دراسية</h5>
                <p class="text-muted mb-0">لا توجد مواد دراسية في هذا الصف حالياً</p>
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
<div class="modal fade enrollment-confirm-modal" id="confirmClassEnrollmentModal" tabindex="-1" aria-labelledby="confirmClassEnrollmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="enrollment-confirm-modal__hero" aria-hidden="true">
                    <i class="bi bi-building-add"></i>
                </div>
                <h5 class="enrollment-confirm-modal__title" id="confirmClassEnrollmentModalLabel">تأكيد طلب الانضمام للصف</h5>
                <p class="enrollment-confirm-modal__message" id="confirmClassEnrollmentModalMessage"></p>
                <div class="enrollment-confirm-modal__note">
                    <i class="bi bi-hourglass-split"></i>
                    سيتم إرسال الطلب مباشرة إلى الإدارة للمراجعة
                </div>
                <div class="enrollment-confirm-modal__actions">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> إلغاء
                    </button>
                    <button type="button" class="btn btn-success" id="confirmClassEnrollmentBtn">
                        <i class="bi bi-check2-circle me-1"></i> تأكيد الانضمام
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

<!-- Modal الدفع (صف/مادة مدفوعة) -->
<div class="modal fade" id="enrollmentPaymentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-scrollable modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إتمام الدفع</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="enrollmentPaymentModalBody"></div>
        </div>
    </div>
</div>
@include('student.pages.enrollments.partials.pending-review-modal')
@include('student.pages.purchases.partials.payment-pending-modal')
@stop

@section('script')
@include('student.pages.enrollments.partials.inline-purchase-payment-script')
<script>
    // التأكد من تحميل الصفحة بالكامل
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Enrollment page loaded');
        initializeModals();

        var payModalEl = document.getElementById('enrollmentPaymentModal');
        if (payModalEl) {
            payModalEl.addEventListener('hidden.bs.modal', function () {
                var body = document.getElementById('enrollmentPaymentModalBody');
                if (body) {
                    body.innerHTML = '';
                }
            });
        }
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
    let pendingCancelPurchaseId = null;
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
                if (pendingCancelPurchaseId) {
                    processCancelPendingPurchaseRequest(pendingCancelPurchaseId);
                } else if (pendingCancelSubjectId) {
                    processCancelRequest(pendingCancelSubjectId);
                }
                const modal = bootstrap.Modal.getInstance(document.getElementById('confirmCancelModal'));
                if (modal) modal.hide();
            });
        }
    }

    const confirmEnrollmentModalEl = document.getElementById('confirmEnrollmentModal');
    if (confirmEnrollmentModalEl) {
        confirmEnrollmentModalEl.addEventListener('hidden.bs.modal', function () {
            pendingSubjectId = null;
            pendingSubjectName = null;
            currentButton = null;
        });
    }

    const confirmClassEnrollmentModalEl = document.getElementById('confirmClassEnrollmentModal');
    if (confirmClassEnrollmentModalEl) {
        confirmClassEnrollmentModalEl.addEventListener('hidden.bs.modal', function () {
            pendingClassId = null;
            pendingClassName = null;
            currentButton = null;
        });
    }

    const confirmCancelModalEl = document.getElementById('confirmCancelModal');
    if (confirmCancelModalEl) {
        confirmCancelModalEl.addEventListener('hidden.bs.modal', function () {
            pendingCancelSubjectId = null;
            pendingCancelPurchaseId = null;
        });
    }
    
    function requestEnrollment(subjectId, subjectName, triggerButton) {
        console.log('requestEnrollment called with:', subjectId, subjectName);
        
        pendingSubjectId = subjectId;
        pendingSubjectName = subjectName;
        currentButton = triggerButton || null;
        
        // تحديث رسالة المودال
        const messageEl = document.getElementById('confirmEnrollmentModalMessage');
        if (messageEl) {
            messageEl.textContent = 'هل أنت متأكد من طلب الانضمام إلى مادة "' + subjectName + '"؟';
        }
        
        // إظهار المودال
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmEnrollmentModal'));
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
            if (data.success && data.requires_payment) {
                var payModalEl = document.getElementById('enrollmentPaymentModal');
                var payBody = document.getElementById('enrollmentPaymentModalBody');
                if (typeof window.EnrollmentInlinePurchase !== 'undefined') {
                    window.EnrollmentInlinePurchase.openPaymentModal(payModalEl, payBody, data.purchase_id || null, {
                        return: 'class',
                        purchase_type: data.purchase_type || 'subject',
                        subject_id: data.subject_id || subjectId,
                        class_id: {{ (int) $class->id }},
                    });
                }
                if (button) {
                    button.disabled = false;
                    button.innerHTML = '<i class="bi bi-plus-circle me-1"></i> طلب الانضمام';
                }
                return;
            }
            if (data.success) {
                if (data.under_review) {
                    setTimeout(function () {
                        showEnrollmentPendingReviewModal(data.message, {
                            requiresWhatsappFollowup: !!data.requires_whatsapp_followup,
                            className: @json($class->name)
                        });
                    }, 300);
                } else {
                    showSuccessMessage(data.message);
                    setTimeout(() => location.reload(), 1500);
                }
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
    
    function requestClassEnrollment(classId, className, requiresPayment, triggerButton) {
        console.log('requestClassEnrollment called with:', classId, className);
        
        pendingClassId = classId;
        pendingClassName = className;
        currentButton = triggerButton || null;
        
        const messageEl = document.getElementById('confirmClassEnrollmentModalMessage');
        if (messageEl) {
            if (requiresPayment) {
                messageEl.innerHTML = 'سيتم إرسال طلب انضمامك لـ <strong>' + className + '</strong> إلى الإدارة للمراجعة. ' +
                    'بعد الإرسال يمكنك متابعة القبول عبر واتساب قسم الإشراف.';
            } else {
                messageEl.textContent = 'هل أنت متأكد من طلب الانضمام لجميع مواد صف "' + className + '"؟';
            }
        }
        
        // إظهار المودال
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmClassEnrollmentModal'));
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
            if (data.success && data.requires_payment) {
                var payModalEl = document.getElementById('enrollmentPaymentModal');
                var payBody = document.getElementById('enrollmentPaymentModalBody');
                if (typeof window.EnrollmentInlinePurchase !== 'undefined') {
                    window.EnrollmentInlinePurchase.openPaymentModal(payModalEl, payBody, data.purchase_id || null, {
                        return: 'class',
                        purchase_type: data.purchase_type || 'class',
                        class_id: data.class_id || classId,
                    });
                }
                if (button) {
                    button.disabled = false;
                    button.innerHTML = '<i class="bi bi-plus-circle me-1"></i> انضمام للصف كامل';
                }
                return;
            }
            if (data.success) {
                if (data.under_review) {
                    setTimeout(function () {
                        showEnrollmentPendingReviewModal(data.message, {
                            requiresWhatsappFollowup: !!data.requires_whatsapp_followup,
                            className: className || pendingClassName || @json($class->name)
                        });
                    }, 300);
                } else {
                    showSuccessMessage(data.message);
                    setTimeout(() => location.reload(), 1500);
                }
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
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmCancelModal'));
        modal.show();
    }

    function cancelPendingPurchase(purchaseId) {
        pendingCancelPurchaseId = purchaseId;
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmCancelModal'));
        modal.show();
    }

    function processCancelPendingPurchaseRequest(purchaseId) {
        const csrfToken = getCsrfToken();
        const url = '{{ route("student.purchases.cancel", ":id") }}'.replace(':id', purchaseId);

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
            if (data.success) {
                showSuccessMessage(data.message || 'تم إلغاء الطلب بنجاح');
                setTimeout(() => location.reload(), 1200);
            } else {
                showErrorMessage(data.message || 'تعذر إلغاء الطلب');
            }
        })
        .catch(error => {
            showErrorMessage(error.message || 'حدث خطأ أثناء إلغاء الطلب. يرجى المحاولة مرة أخرى.');
        });
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
            showSuccessMessage(message || 'تم استلام طلبك وهو قيد المراجعة.');
            setTimeout(() => location.reload(), 1500);
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
    window.cancelPendingPurchase = cancelPendingPurchase;
</script>
@stop

