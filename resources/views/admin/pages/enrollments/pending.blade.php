@extends('admin.layouts.master')

@section('page-title')
    طلبات الانضمام المعلقة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">طلبات الانضمام المعلقة</h5>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.enrollments.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <!-- إحصائيات -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">الطلبات المعلقة</h6>
                        <h3 class="mb-0">{{ $pendingCount }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">الانضمامات النشطة</h6>
                        <h3 class="mb-0">{{ $activeCount }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <!-- قسم الفلاتر -->
                <div class="card custom-card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-funnel me-2"></i> البحث والفلترة
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.enrollments.pending') }}">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label mb-1">بحث</label>
                                    <input type="text" name="search" class="form-control form-control-sm"
                                           placeholder="بحث بالاسم أو البريد الإلكتروني"
                                           value="{{ request('search') }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label mb-1">الطالب</label>
                                    <select name="user_id" class="form-select form-select-sm">
                                        <option value="">كل الطلاب</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label mb-1">المادة</label>
                                    <select name="subject_id" class="form-select form-select-sm">
                                        <option value="">كل المواد</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                                {{ $subject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label mb-1 d-block">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                            <i class="bi bi-search me-1"></i> بحث
                                        </button>
                                        <a href="{{ route('admin.enrollments.pending') }}" class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-x-circle me-1"></i> مسح
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- جدول الطلبات المعلقة -->
                <div class="card shadow-sm border-0">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold">قائمة طلبات الانضمام المعلقة</h5>
                    </div>

                    <div class="card-body">
                        @if($enrollments->count() > 0)
                            <form id="bulkActionsForm" method="POST">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle table-hover table-bordered mb-0 text-center">
                                        <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">
                                                <input type="checkbox" id="selectAll" class="form-check-input">
                                            </th>
                                            <th style="width: 50px;">#</th>
                                            <th style="min-width: 150px;">الطالب</th>
                                            <th style="min-width: 150px;">المادة</th>
                                            <th style="min-width: 120px;">الصف</th>
                                            <th style="min-width: 150px;">تاريخ الطلب</th>
                                            <th style="min-width: 120px;">الحالة</th>
                                            <th style="min-width: 200px;">العمليات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($enrollments as $enrollment)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="enrollment_ids[]" value="{{ $enrollment->id }}" class="form-check-input enrollment-checkbox">
                                                </td>
                                                <td>{{ $loop->iteration + ($enrollments->currentPage() - 1) * $enrollments->perPage() }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div>
                                                            <strong>{{ $enrollment->user->name ?? 'N/A' }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $enrollment->user->email ?? '' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong>{{ $enrollment->subject->name ?? 'N/A' }}</strong>
                                                </td>
                                                <td>
                                                    {{ $enrollment->subject->schoolClass->name ?? 'N/A' }}
                                                    @if($enrollment->subject->schoolClass && $enrollment->subject->schoolClass->stage)
                                                        <br><small class="text-muted">{{ $enrollment->subject->schoolClass->stage->name }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ $enrollment->enrolled_at ? $enrollment->enrolled_at->format('Y-m-d H:i') : 'N/A' }}</td>
                                                <td>
                                                    <span class="badge bg-warning">معلق</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <form action="{{ route('admin.enrollments.approve', $enrollment) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success btn-sm" title="قبول">
                                                                <i class="fas fa-check"></i> قبول
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin.enrollments.reject', $enrollment) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm" title="رفض" onclick="return confirm('هل أنت متأكد من رفض هذا الطلب؟');">
                                                                <i class="fas fa-times"></i> رفض
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3 d-flex gap-2 align-items-center">
                                    <button type="submit" formaction="{{ route('admin.enrollments.approve-multiple') }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-check-double me-1"></i> قبول المحدد
                                    </button>
                                    <button type="submit" formaction="{{ route('admin.enrollments.reject-multiple') }}" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من رفض الطلبات المحددة؟');">
                                        <i class="fas fa-times-circle me-1"></i> رفض المحدد
                                    </button>
                                </div>
                            </form>

                            <div class="mt-3">
                                {{ $enrollments->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">لا توجد طلبات انضمام معلقة</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.enrollment-checkbox');

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    // Update select all when individual checkboxes change
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            if (selectAll) {
                selectAll.checked = allChecked;
            }
        });
    });
});
</script>
@endpush

