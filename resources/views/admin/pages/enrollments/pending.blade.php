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
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.enrollments.index') }}">الانضمامات</a></li>
                            <li class="breadcrumb-item active" aria-current="page">المعلقة</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.enrollments.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i> جميع الانضمامات
                    </a>
                    <a href="{{ route('admin.enrollments.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> إضافة انضمامات جديدة
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

            <!-- إحصائيات -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted mb-1">الطلبات المعلقة</h6>
                                    <h3 class="mb-0 text-warning">{{ $pendingCount }}</h3>
                                </div>
                                <div class="avatar avatar-lg bg-warning-transparent rounded-circle">
                                    <i class="bi bi-clock-history fs-2 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted mb-1">الانضمامات النشطة</h6>
                                    <h3 class="mb-0 text-success">{{ $activeCount }}</h3>
                                </div>
                                <div class="avatar avatar-lg bg-success-transparent rounded-circle">
                                    <i class="bi bi-check-circle fs-2 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <h5 class="mb-0 fw-bold">طلبات الانضمام المعلقة</h5>

                            <form method="GET" action="{{ route('admin.enrollments.pending') }}"
                                  class="d-flex flex-wrap gap-2 align-items-center">
                                <input type="text" name="search" class="form-control form-control-sm"
                                       placeholder="بحث بالاسم، البريد، أو المادة"
                                       value="{{ request('search') }}" style="min-width: 220px;">

                                <select name="user_id" class="form-select form-select-sm" style="min-width: 160px;">
                                    <option value="">كل الطلاب</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <select name="subject_id" class="form-select form-select-sm" style="min-width: 160px;">
                                    <option value="">كل المواد</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }} - {{ $subject->schoolClass->name ?? '' }}
                                        </option>
                                    @endforeach
                                </select>

                                <button type="submit" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-search me-1"></i> بحث
                                </button>
                                <a href="{{ route('admin.enrollments.pending') }}" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-x-circle me-1"></i> مسح
                                </a>
                            </form>
                        </div>

                        <div class="card-body">
                            @if($enrollments->count() > 0)
                                <form action="{{ route('admin.enrollments.approve-multiple') }}" method="POST" id="bulkActionForm">
                                    @csrf
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-success btn-sm" id="approveSelectedBtn" disabled>
                                                <i class="bi bi-check-circle me-1"></i> قبول المحدد
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" id="rejectSelectedBtn" disabled>
                                                <i class="bi bi-x-circle me-1"></i> رفض المحدد
                                            </button>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAll">
                                            <label class="form-check-label" for="selectAll">
                                                تحديد الكل
                                            </label>
                                        </div>
                                    </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        @if($enrollments->count() > 0)
                                            <th style="width: 50px;">
                                                <input type="checkbox" id="selectAllHeader" class="form-check-input">
                                            </th>
                                        @endif
                                        <th style="width: 50px;">#</th>
                                        <th style="min-width: 180px;">الطالب</th>
                                        <th style="min-width: 200px;">المادة</th>
                                        <th style="min-width: 120px;">الصف</th>
                                        <th style="min-width: 150px;">تاريخ الطلب</th>
                                        <th style="min-width: 150px;">أضيف بواسطة</th>
                                        <th style="min-width: 200px;">العمليات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($enrollments as $enrollment)
                                        <tr>
                                            @if($enrollments->count() > 0)
                                                <td>
                                                    <input type="checkbox" name="enrollment_ids[]" value="{{ $enrollment->id }}" class="form-check-input enrollment-checkbox">
                                                </td>
                                            @endif
                                            <td>{{ $enrollment->id }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if($enrollment->user->photo)
                                                        <img src="{{ asset('storage/' . $enrollment->user->photo) }}" 
                                                             alt="{{ $enrollment->user->name }}" 
                                                             class="rounded-circle" 
                                                             style="width: 40px; height: 40px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                             style="width: 40px; height: 40px;">
                                                            {{ substr($enrollment->user->name, 0, 1) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-semibold">{{ $enrollment->user->name }}</div>
                                                        <small class="text-muted">{{ $enrollment->user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $enrollment->subject->name }}</div>
                                                @if($enrollment->subject->schoolClass)
                                                    <small class="text-muted">{{ $enrollment->subject->schoolClass->name }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($enrollment->subject->schoolClass)
                                                    <span class="badge bg-info-transparent text-info">
                                                        {{ $enrollment->subject->schoolClass->name }}
                                                    </span>
                                                    @if($enrollment->subject->schoolClass->stage)
                                                        <br>
                                                        <small class="text-muted">{{ $enrollment->subject->schoolClass->stage->name }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>{{ $enrollment->enrolled_at->format('Y-m-d') }}</div>
                                                <small class="text-muted">{{ $enrollment->enrolled_at->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                @if($enrollment->enrolledBy)
                                                    {{ $enrollment->enrolledBy->name }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <form action="{{ route('admin.enrollments.approve', $enrollment->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="bi bi-check-circle me-1"></i> قبول
                                                        </button>
                                                    </form>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#rejectEnrollmentModal{{ $enrollment->id }}">
                                                        <i class="bi bi-x-circle me-1"></i> رفض
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $enrollments->count() > 0 ? '8' : '7' }}" class="text-center py-5">
                                                <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                                                <p class="text-muted">لا توجد طلبات انضمام معلقة</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($enrollments->hasPages())
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $enrollments->links() }}
                                </div>
                            @endif

                            @if($enrollments->count() > 0)
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modals for Reject Confirmation -->
    @foreach($enrollments as $enrollment)
    <div class="modal fade" id="rejectEnrollmentModal{{ $enrollment->id }}" tabindex="-1" aria-labelledby="rejectEnrollmentModalLabel{{ $enrollment->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title" id="rejectEnrollmentModalLabel{{ $enrollment->id }}">
                        تأكيد رفض الطلب
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <form action="{{ route('admin.enrollments.reject', $enrollment->id) }}" method="POST">
                    @csrf
                    <div class="modal-body text-center">
                        <div class="mb-4">
                            <i class="bi bi-x-circle-fill text-danger" style="font-size: 80px;"></i>
                        </div>
                        <h6 class="mb-3">هل أنت متأكد من رفض هذا الطلب؟</h6>
                        <p class="text-muted mb-3">
                            سيتم رفض طلب انضمام الطالب <strong>{{ $enrollment->user->name }}</strong> لمادة <strong>{{ $enrollment->subject->name }}</strong>
                        </p>
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            <small>هذه العملية لا يمكن التراجع عنها.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> إلغاء
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-circle me-1"></i> رفض
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
@stop

@section('js')
<script>
    // Select All functionality
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const selectAllHeader = document.getElementById('selectAllHeader');
        const checkboxes = document.querySelectorAll('.enrollment-checkbox');
        const approveBtn = document.getElementById('approveSelectedBtn');
        const rejectBtn = document.getElementById('rejectSelectedBtn');

        function updateButtons() {
            const checked = document.querySelectorAll('.enrollment-checkbox:checked').length;
            if (approveBtn) approveBtn.disabled = checked === 0;
            if (rejectBtn) rejectBtn.disabled = checked === 0;
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                if (selectAllHeader) selectAllHeader.checked = this.checked;
                updateButtons();
            });
        }

        if (selectAllHeader) {
            selectAllHeader.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                if (selectAll) selectAll.checked = this.checked;
                updateButtons();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                updateButtons();
                const allChecked = Array.from(checkboxes).every(c => c.checked);
                if (selectAll) selectAll.checked = allChecked;
                if (selectAllHeader) selectAllHeader.checked = allChecked;
            });
        });

        // Reject multiple
        if (rejectBtn) {
            rejectBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const checked = Array.from(checkboxes).filter(cb => cb.checked);
                if (checked.length === 0) return;

                if (confirm('هل أنت متأكد من رفض ' + checked.length + ' طلب انضمام؟')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("admin.enrollments.reject-multiple") }}';
                    
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);

                    checked.forEach(cb => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'enrollment_ids[]';
                        input.value = cb.value;
                        form.appendChild(input);
                    });

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@stop

