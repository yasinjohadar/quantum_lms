@extends('admin.layouts.master')

@section('page-title')
    طلبات الانضمام للصف المعلقة
@stop

@push('styles')
    @include('admin.pages.enrollments.partials.enrollments-index-styles')
@endpush

@section('content')
<div class="main-content app-content enrollments-index-page">
    <div class="container-fluid">

        <div class="enrollments-index-hero my-4">
            <div class="enrollments-index-hero__icon" style="color: #d97706; background: rgba(245, 158, 11, 0.14);">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="enrollments-index-hero__content">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2 small">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.enrollments.index') }}">الانضمامات</a></li>
                        <li class="breadcrumb-item active" aria-current="page">طلبات الصف المعلقة</li>
                    </ol>
                </nav>
                <h4 class="enrollments-index-hero__title">طلبات الانضمام للصف المعلقة</h4>
                <p class="enrollments-index-hero__subtitle">مراجعة وقبول أو رفض طلبات انضمام الطلاب للصفوف</p>
            </div>
            <div class="enrollments-index-stat-mini">
                <span class="enrollments-index-stat-mini__value" style="color: #d97706;">{{ number_format($pendingCount) }}</span>
                <span class="enrollments-index-stat-mini__label">طلب معلق</span>
            </div>
            <div class="enrollments-index-hero__actions">
                <a href="{{ route('admin.enrollments.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-right me-1"></i> رجوع للقائمة
                </a>
                <a href="{{ route('admin.enrollments.pending') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-journal-text me-1"></i> طلبات المواد
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="enrollments-stats-row">
            <div class="enrollments-stat-card enrollments-stat-card--pending">
                <div class="enrollments-stat-card__label">الطلبات المعلقة</div>
                <div class="enrollments-stat-card__value">{{ number_format($pendingCount) }}</div>
            </div>
            <div class="enrollments-stat-card enrollments-stat-card--success">
                <div class="enrollments-stat-card__label">الطلبات المقبولة</div>
                <div class="enrollments-stat-card__value">{{ number_format($approvedCount) }}</div>
            </div>
        </div>

        <div class="enrollments-index-card">
            <div class="enrollments-index-card__header">
                <div class="d-flex align-items-center gap-2">
                    <span class="enrollments-index-card__header-icon"><i class="bi bi-funnel"></i></span>
                    تصفية وبحث
                </div>
            </div>
            <div class="enrollments-index-card__body">
                <form method="GET" action="{{ route('admin.enrollments.class-pending') }}" class="enrollments-index-filters">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-5 col-lg-4">
                            <label class="form-label">بحث</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0"
                                       placeholder="الاسم أو البريد الإلكتروني"
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="form-label">الطالب</label>
                            <select name="user_id" class="form-select">
                                <option value="">كل الطلاب</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="form-label">الصف</label>
                            <select name="class_id" class="form-select">
                                <option value="">كل الصفوف</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-lg-2 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-search me-1"></i> بحث
                            </button>
                            <a href="{{ route('admin.enrollments.class-pending') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-lg me-1"></i> مسح
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="enrollments-index-card">
            <div class="enrollments-index-card__header">
                <div class="d-flex align-items-center gap-2">
                    <span class="enrollments-index-card__header-icon"><i class="bi bi-table"></i></span>
                    طلبات الصفوف المدفوعة المعلقة
                </div>
                @if(!empty($pendingClassPurchaseRequests) && $pendingClassPurchaseRequests->count() > 0)
                    <span class="badge bg-warning-transparent text-warning">
                        {{ $pendingClassPurchaseRequests->count() }} طلب
                    </span>
                @endif
            </div>
            <div class="enrollments-index-card__body">
                @if(!empty($pendingClassPurchaseRequests) && $pendingClassPurchaseRequests->count() > 0)
                    <div class="row g-3">
                        @foreach($pendingClassPurchaseRequests as $purchase)
                            @php
                                $student = $purchase->user;
                                $schoolClass = $purchase->purchasable;
                                $initial = $student ? mb_strtoupper(mb_substr(trim($student->name), 0, 1)) : '—';
                            @endphp
                            <div class="col-12 col-xl-6">
                                <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                    <div class="d-flex flex-wrap gap-3 align-items-start justify-content-between">
                                        <div class="d-flex align-items-center gap-3 min-w-0">
                                            <span class="ui-user-avatar">{{ $initial }}</span>
                                            <div class="min-width-0">
                                                <div class="fw-bold text-truncate">{{ $student->name ?? 'غير محدد' }}</div>
                                                <div class="small text-muted text-truncate">{{ $student->email ?? '—' }}</div>
                                                <div class="small mt-1">
                                                    <span class="ui-class-pill">
                                                        <i class="bi bi-building"></i>
                                                        {{ $schoolClass->name ?? 'صف غير محدد' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-md-end">
                                            <div class="ui-payment-amount mb-1">{{ number_format((float) $purchase->price, 2) }} ر.س</div>
                                            <span class="ui-status-pill ui-status-pill--warning">طلب مدفوع بانتظار القبول</span>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 pt-3 border-top">
                                        <div class="small text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            تم الإرسال {{ $purchase->created_at->format('Y-m-d H:i') }}
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            @include('admin.pages.enrollments.partials.approve-purchase-btn', [
                                                'purchase' => $purchase,
                                                'studentName' => $student->name ?? 'غير محدد',
                                                'itemName' => $schoolClass->name ?? 'صف غير محدد',
                                                'typeLabel' => 'الصف',
                                            ])
                                            <form action="{{ route('admin.payments.pending-purchases.reject', $purchase) }}" method="POST" onsubmit="return confirm('هل تريد رفض طلب الصف المدفوع هذا؟');">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="bi bi-x-lg me-1"></i> رفض
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="enrollments-index-empty py-4">
                        <i class="bi bi-cash-coin"></i>
                        <p class="mb-0 fw-semibold">لا توجد حالياً طلبات صفوف مدفوعة بانتظار القبول</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="enrollments-index-card">
            <div class="enrollments-index-card__header">
                <div class="d-flex align-items-center gap-2">
                    <span class="enrollments-index-card__header-icon"><i class="bi bi-table"></i></span>
                    قائمة الطلبات المعلقة
                </div>
                @if($classEnrollments instanceof \Illuminate\Pagination\LengthAwarePaginator && $classEnrollments->hasPages())
                    <span class="badge bg-primary-transparent text-primary">
                        صفحة {{ $classEnrollments->currentPage() }} من {{ $classEnrollments->lastPage() }}
                    </span>
                @endif
            </div>
            <div class="enrollments-index-card__body p-0">
                @if($classEnrollments->count() > 0)
                    <div class="px-3 pt-3">
                        <div class="enrollments-pending-toolbar">
                            @can('enrollment-reject-multiple-class')
                                <button type="button" class="btn btn-outline-warning btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#cleanStalePendingModal">
                                    <i class="bi bi-trash me-1"></i> تنظيف
                                </button>
                            @endcan
                            <form method="POST" action="{{ route('admin.enrollments.class.approve-all') }}" class="d-inline"
                                  onsubmit="return confirm('سيتم قبول جميع الطلبات المعلقة المطابقة للفلاتر الحالية (بما في ذلك صفحات أخرى غير المعروضة). هل تريد المتابعة؟');">
                                @csrf
                                <input type="hidden" name="search" value="{{ request('search') }}">
                                <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                                <input type="hidden" name="class_id" value="{{ request('class_id') }}">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="bi bi-check2-all me-1"></i> قبول الكل
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.enrollments.class.approve-multiple') }}" class="d-inline" id="bulk-approve-form">
                                @csrf
                                <input type="hidden" name="class_enrollment_ids" id="bulk-approve-ids">
                                <button type="submit" class="btn btn-outline-success btn-sm" id="bulk-approve-btn" disabled>
                                    <i class="bi bi-check-circle me-1"></i> قبول المحدد
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.enrollments.class.reject-multiple') }}" class="d-inline" id="bulk-reject-form">
                                @csrf
                                <input type="hidden" name="class_enrollment_ids" id="bulk-reject-ids">
                                <button type="submit" class="btn btn-danger btn-sm" id="bulk-reject-btn" disabled>
                                    <i class="bi bi-x-circle me-1"></i> رفض المحدد
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="enrollments-index-table-wrap mx-3 mb-0">
                        <div class="table-responsive">
                            <table class="table enrollments-index-table align-middle mb-0">
                                <thead>
                                <tr>
                                    <th scope="col" style="width: 40px;" class="text-center">
                                        <input type="checkbox" id="select-all" class="form-check-input" aria-label="تحديد الكل">
                                    </th>
                                    <th scope="col" style="width: 48px;">#</th>
                                    <th scope="col">الطالب</th>
                                    <th scope="col" class="enrollments-col-class">الصف</th>
                                    <th scope="col" class="enrollments-col-date">تاريخ الطلب</th>
                                    <th scope="col" class="d-none d-md-table-cell">ملاحظات</th>
                                    <th scope="col" style="min-width: 110px;">الإجراءات</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($classEnrollments as $classEnrollment)
                                    @php
                                        $rowNum = $loop->iteration + ($classEnrollments->currentPage() - 1) * $classEnrollments->perPage();
                                        $initial = mb_strtoupper(mb_substr(trim($classEnrollment->user->name), 0, 1));
                                    @endphp
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input enrollment-checkbox"
                                                   value="{{ $classEnrollment->id }}" aria-label="تحديد">
                                        </td>
                                        <th scope="row" class="text-muted small">{{ $rowNum }}</th>
                                        <td>
                                            <div class="ui-user-cell">
                                                <span class="ui-user-avatar">{{ $initial }}</span>
                                                <div class="min-width-0">
                                                    <div class="ui-user-name text-truncate">{{ $classEnrollment->user->name }}</div>
                                                    <small class="text-muted text-truncate d-block">{{ $classEnrollment->user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="enrollments-col-class">
                                            <span class="ui-class-pill">
                                                <i class="bi bi-building"></i>
                                                {{ $classEnrollment->schoolClass->name }}
                                            </span>
                                            @if($classEnrollment->schoolClass->stage)
                                                <div class="ui-enrollment-subject-meta mt-1">{{ $classEnrollment->schoolClass->stage->name }}</div>
                                            @endif
                                        </td>
                                        <td class="enrollments-col-date">
                                            <div class="ui-date-cell">
                                                {{ $classEnrollment->created_at->format('Y-m-d') }}
                                                <small>{{ $classEnrollment->created_at->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <small class="text-muted">{{ $classEnrollment->notes ? \Illuminate\Support\Str::limit($classEnrollment->notes, 50) : '—' }}</small>
                                        </td>
                                        <td>
                                            <div class="row-action-bar">
                                                <button type="button"
                                                        class="row-action-btn row-action-btn--success approve-btn"
                                                        data-id="{{ $classEnrollment->id }}"
                                                        data-student="{{ $classEnrollment->user->name }}"
                                                        data-class="{{ $classEnrollment->schoolClass->name }}"
                                                        title="قبول">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                                <button type="button"
                                                        class="row-action-btn row-action-btn--danger reject-btn"
                                                        data-id="{{ $classEnrollment->id }}"
                                                        data-student="{{ $classEnrollment->user->name }}"
                                                        data-class="{{ $classEnrollment->schoolClass->name }}"
                                                        title="رفض">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="enrollments-index-pagination">
                        {{ $classEnrollments->withQueryString()->links() }}
                    </div>
                @else
                    <div class="enrollments-index-empty py-5">
                        <i class="bi bi-inbox"></i>
                        <p class="mb-0 fw-semibold">لا توجد طلبات انضمام للصف معلقة حالياً</p>
                        <p class="small mb-0 mt-1 text-muted">ستظهر الطلبات الجديدة هنا تلقائياً</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@can('enrollment-reject-multiple-class')
    @include('admin.pages.enrollments.partials.clean-stale-pending-modal', [
        'action' => route('admin.enrollments.class.clean-stale-pending'),
        'context' => 'class',
    ])
@endcan

<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveModalLabel">
                    <i class="bi bi-check-circle-fill me-2"></i> قبول طلب الانضمام
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                </div>
                <h5 class="mb-3">هل أنت متأكد من قبول هذا الطلب؟</h5>
                <p class="text-muted mb-2"><strong id="approveStudentName"></strong></p>
                <p class="text-muted mb-0">
                    سيتم إنشاء انضمامات لجميع المواد في الصف: <strong id="approveClassName"></strong>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> إلغاء
                </button>
                <form id="approveForm" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i> نعم، قبول الطلب
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="bi bi-x-circle-fill me-2"></i> رفض طلب الانضمام
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i>
                </div>
                <h5 class="mb-3">هل أنت متأكد من رفض هذا الطلب؟</h5>
                <p class="text-muted mb-2"><strong id="rejectStudentName"></strong></p>
                <p class="text-muted mb-0">الصف: <strong id="rejectClassName"></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> إلغاء
                </button>
                <form id="rejectForm" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i> نعم، رفض الطلب
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.enrollment-checkbox');
        const bulkApproveBtn = document.getElementById('bulk-approve-btn');
        const bulkRejectBtn = document.getElementById('bulk-reject-btn');
        const bulkApproveIds = document.getElementById('bulk-approve-ids');
        const bulkRejectIds = document.getElementById('bulk-reject-ids');
        const bulkApproveForm = document.getElementById('bulk-approve-form');
        const bulkRejectForm = document.getElementById('bulk-reject-form');

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(checkbox => { checkbox.checked = this.checked; });
                updateBulkButtons();
            });
        }

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateBulkButtons();
                if (selectAll) {
                    selectAll.checked = Array.from(checkboxes).every(cb => cb.checked);
                    selectAll.indeterminate = Array.from(checkboxes).some(cb => cb.checked) && !selectAll.checked;
                }
            });
        });

        function updateBulkButtons() {
            const selectedIds = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
            const hasSelection = selectedIds.length > 0;
            if (bulkApproveBtn) bulkApproveBtn.disabled = !hasSelection;
            if (bulkRejectBtn) bulkRejectBtn.disabled = !hasSelection;
            if (bulkApproveIds) bulkApproveIds.value = JSON.stringify(selectedIds);
            if (bulkRejectIds) bulkRejectIds.value = JSON.stringify(selectedIds);
        }

        document.querySelectorAll('.approve-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('approveStudentName').textContent = this.getAttribute('data-student');
                document.getElementById('approveClassName').textContent = this.getAttribute('data-class');
                document.getElementById('approveForm').action = '{{ route("admin.enrollments.class.approve", ":id") }}'.replace(':id', this.getAttribute('data-id'));
                bootstrap.Modal.getOrCreateInstance(document.getElementById('approveModal')).show();
            });
        });

        document.querySelectorAll('.reject-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('rejectStudentName').textContent = this.getAttribute('data-student');
                document.getElementById('rejectClassName').textContent = this.getAttribute('data-class');
                document.getElementById('rejectForm').action = '{{ route("admin.enrollments.class.reject", ":id") }}'.replace(':id', this.getAttribute('data-id'));
                bootstrap.Modal.getOrCreateInstance(document.getElementById('rejectModal')).show();
            });
        });

        if (bulkApproveForm) {
            bulkApproveForm.addEventListener('submit', function(e) {
                const selectedIds = JSON.parse(bulkApproveIds.value || '[]');
                if (!confirm(`هل أنت متأكد من قبول ${selectedIds.length} طلب؟ سيتم إنشاء انضمامات لجميع المواد في الصفوف.`)) {
                    e.preventDefault();
                }
            });
        }

        if (bulkRejectForm) {
            bulkRejectForm.addEventListener('submit', function(e) {
                const selectedIds = JSON.parse(bulkRejectIds.value || '[]');
                if (!confirm(`هل أنت متأكد من رفض ${selectedIds.length} طلب؟`)) {
                    e.preventDefault();
                }
            });
        }
    });
</script>
@endpush
@include('admin.pages.enrollments.partials.approve-purchase-modal')
@endsection
