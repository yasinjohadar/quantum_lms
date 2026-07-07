@extends('admin.layouts.master')

@section('page-title')
    طلبات الانضمام المعلقة
@stop

@push('styles')
    @include('admin.pages.enrollments.partials.enrollments-index-styles')
@endpush

@section('content')
<div class="main-content app-content enrollments-index-page">
    <div class="container-fluid">

        <div class="enrollments-index-hero my-4">
            <div class="enrollments-index-hero__icon" style="color: #d97706; background: rgba(245, 158, 11, 0.14);">
                <i class="bi bi-journal-arrow-up"></i>
            </div>
            <div class="enrollments-index-hero__content">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2 small">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.enrollments.index') }}">الانضمامات</a></li>
                        <li class="breadcrumb-item active" aria-current="page">طلبات المواد المعلقة</li>
                    </ol>
                </nav>
                <h4 class="enrollments-index-hero__title">طلبات الانضمام المعلقة</h4>
                <p class="enrollments-index-hero__subtitle">مراجعة طلبات انضمام الطلاب للمواد الدراسية</p>
            </div>
            <div class="enrollments-index-stat-mini">
                <span class="enrollments-index-stat-mini__value" style="color: #d97706;">{{ number_format($pendingCount) }}</span>
                <span class="enrollments-index-stat-mini__label">طلب معلق</span>
            </div>
            <div class="enrollments-index-hero__actions">
                <a href="{{ route('admin.enrollments.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-right me-1"></i> رجوع للقائمة
                </a>
                <a href="{{ route('admin.enrollments.class-pending') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-building me-1"></i> طلبات الصف
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
                <div class="enrollments-stat-card__label">الانضمامات النشطة</div>
                <div class="enrollments-stat-card__value">{{ number_format($activeCount) }}</div>
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
                <form method="GET" action="{{ route('admin.enrollments.pending') }}" class="enrollments-index-filters">
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
                            <label class="form-label">المادة</label>
                            <select name="subject_id" class="form-select">
                                <option value="">كل المواد</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-lg-2 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-search me-1"></i> بحث
                            </button>
                            <a href="{{ route('admin.enrollments.pending') }}" class="btn btn-outline-secondary btn-sm">
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
                    طلبات المواد المدفوعة المعلقة
                </div>
                @if(!empty($pendingSubjectPurchaseRequests) && $pendingSubjectPurchaseRequests->count() > 0)
                    <span class="badge bg-warning-transparent text-warning">
                        {{ $pendingSubjectPurchaseRequests->count() }} طلب
                    </span>
                @endif
            </div>
            <div class="enrollments-index-card__body">
                @if(!empty($pendingSubjectPurchaseRequests) && $pendingSubjectPurchaseRequests->count() > 0)
                    <div class="row g-3">
                        @foreach($pendingSubjectPurchaseRequests as $purchase)
                            @php
                                $student = $purchase->user;
                                $subject = $purchase->purchasable;
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
                                                    <span class="ui-enrollment-subject">{{ $subject->name ?? 'مادة غير محددة' }}</span>
                                                    @if($subject?->schoolClass)
                                                        <span class="ui-class-pill ms-1">
                                                            <i class="bi bi-building"></i>
                                                            {{ $subject->schoolClass->name }}
                                                        </span>
                                                    @endif
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
                                                'itemName' => $subject->name ?? 'مادة غير محددة',
                                                'typeLabel' => 'المادة',
                                            ])
                                            <form action="{{ route('admin.payments.pending-purchases.reject', $purchase) }}" method="POST" onsubmit="return confirm('هل تريد رفض طلب المادة المدفوع هذا؟');">
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
                        <p class="mb-0 fw-semibold">لا توجد حالياً طلبات مواد مدفوعة بانتظار القبول</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="enrollments-index-card">
            <div class="enrollments-index-card__header">
                <div class="d-flex align-items-center gap-2">
                    <span class="enrollments-index-card__header-icon"><i class="bi bi-table"></i></span>
                    قائمة طلبات الانضمام المعلقة
                </div>
                @can('enrollment-reject-multiple')
                    <button type="button" class="btn btn-outline-warning btn-sm"
                            data-bs-toggle="modal" data-bs-target="#cleanStalePendingModal">
                        <i class="bi bi-trash me-1"></i> تنظيف
                    </button>
                @endcan
            </div>
            <div class="enrollments-index-card__body p-0">
                @if($enrollments->count() > 0)
                    <form id="bulkActionsForm" method="POST" class="px-3 pt-3">
                        @csrf
                        <div class="enrollments-pending-toolbar mb-3">
                            <span class="small text-muted"><i class="bi bi-check2-square me-1"></i> إجراءات على المحدد:</span>
                            <button type="submit" formaction="{{ route('admin.enrollments.approve-multiple') }}" class="btn btn-success btn-sm">
                                <i class="bi bi-check2-all me-1"></i> قبول المحدد
                            </button>
                            <button type="submit" formaction="{{ route('admin.enrollments.reject-multiple') }}" class="btn btn-danger btn-sm"
                                    onclick="return confirm('هل أنت متأكد من رفض الطلبات المحددة؟');">
                                <i class="bi bi-x-circle me-1"></i> رفض المحدد
                            </button>
                        </div>

                        <div class="enrollments-index-table-wrap mb-0">
                            <div class="table-responsive">
                                <table class="table enrollments-index-table align-middle mb-0">
                                    <thead>
                                    <tr>
                                        <th scope="col" style="width: 40px;" class="text-center">
                                            <input type="checkbox" id="selectAll" class="form-check-input" aria-label="تحديد الكل">
                                        </th>
                                        <th scope="col" style="width: 48px;">#</th>
                                        <th scope="col">الطالب</th>
                                        <th scope="col">المادة</th>
                                        <th scope="col" class="enrollments-col-class">الصف</th>
                                        <th scope="col" class="enrollments-col-date">تاريخ الطلب</th>
                                        <th scope="col">الحالة</th>
                                        <th scope="col" style="min-width: 100px;">العمليات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($enrollments as $enrollment)
                                        @php
                                            $rowNum = $loop->iteration + ($enrollments->currentPage() - 1) * $enrollments->perPage();
                                            $initial = $enrollment->user ? mb_strtoupper(mb_substr(trim($enrollment->user->name), 0, 1)) : '—';
                                        @endphp
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" name="enrollment_ids[]" value="{{ $enrollment->id }}" class="form-check-input enrollment-checkbox">
                                            </td>
                                            <th scope="row" class="text-muted small">{{ $rowNum }}</th>
                                            <td>
                                                @if($enrollment->user)
                                                    <div class="ui-user-cell">
                                                        <span class="ui-user-avatar">{{ $initial }}</span>
                                                        <div class="min-width-0">
                                                            <div class="ui-user-name text-truncate">{{ $enrollment->user->name }}</div>
                                                            <small class="text-muted text-truncate d-block">{{ $enrollment->user->email }}</small>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="ui-enrollment-subject">{{ $enrollment->subject->name ?? '—' }}</div>
                                            </td>
                                            <td class="enrollments-col-class">
                                                @if($enrollment->subject?->schoolClass)
                                                    <span class="ui-class-pill">
                                                        <i class="bi bi-building"></i>
                                                        {{ $enrollment->subject->schoolClass->name }}
                                                    </span>
                                                    @if($enrollment->subject->schoolClass->stage)
                                                        <div class="ui-enrollment-subject-meta mt-1">{{ $enrollment->subject->schoolClass->stage->name }}</div>
                                                    @endif
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="enrollments-col-date">
                                                <div class="ui-date-cell">
                                                    {{ $enrollment->enrolled_at ? $enrollment->enrolled_at->format('Y-m-d') : '—' }}
                                                    @if($enrollment->enrolled_at)
                                                        <small>{{ $enrollment->enrolled_at->format('H:i') }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="ui-enrollment-status ui-enrollment-status--pending">
                                                    <i class="bi bi-circle-fill" style="font-size: 0.45rem;"></i>
                                                    معلق
                                                </span>
                                            </td>
                                            <td>
                                                <div class="row-action-bar">
                                                    <form action="{{ route('admin.enrollments.approve', $enrollment) }}" method="POST" class="row-action-form">
                                                        @csrf
                                                        <button type="submit" class="row-action-btn row-action-btn--success" title="قبول">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.enrollments.reject', $enrollment) }}" method="POST" class="row-action-form"
                                                          onsubmit="return confirm('هل أنت متأكد من رفض هذا الطلب؟');">
                                                        @csrf
                                                        <button type="submit" class="row-action-btn row-action-btn--danger" title="رفض">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </form>

                    <div class="enrollments-index-pagination">
                        {{ $enrollments->withQueryString()->links() }}
                    </div>
                @else
                    <div class="enrollments-index-empty py-5">
                        <i class="bi bi-inbox"></i>
                        <p class="mb-0 fw-semibold">لا توجد طلبات انضمام معلقة</p>
                        <p class="small mb-0 mt-1 text-muted">ستظهر الطلبات الجديدة هنا تلقائياً</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@can('enrollment-reject-multiple')
    @include('admin.pages.enrollments.partials.clean-stale-pending-modal', [
        'action' => route('admin.enrollments.clean-stale-pending'),
        'context' => 'subject',
    ])
@endcan

@include('admin.pages.enrollments.partials.approve-purchase-modal')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.enrollment-checkbox');

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => { checkbox.checked = this.checked; });
        });
    }

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (selectAll) {
                selectAll.checked = Array.from(checkboxes).every(cb => cb.checked);
                selectAll.indeterminate = Array.from(checkboxes).some(cb => cb.checked) && !selectAll.checked;
            }
        });
    });
});
</script>
@endpush
@endsection
