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
                            <li class="breadcrumb-item active" aria-current="page">طلبات معلقة</li>
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
                <div class="col-xl-4 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-md bg-warning-transparent rounded-circle">
                                        <i class="bi bi-clock fs-20 text-warning"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="mb-0 text-muted">طلبات معلقة</p>
                                    <h4 class="mb-0 fw-semibold">{{ $pendingCount }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-md bg-success-transparent rounded-circle">
                                        <i class="bi bi-check-circle fs-20 text-success"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="mb-0 text-muted">انضمامات نشطة</p>
                                    <h4 class="mb-0 fw-semibold">{{ $activeCount }}</h4>
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
                            <h5 class="mb-0 fw-bold">قائمة طلبات الانضمام المعلقة</h5>

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
                                <form id="bulk-action-form" method="POST" action="">
                                    @csrf
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <input type="checkbox" id="select-all" class="form-check-input">
                                            <label for="select-all" class="form-check-label ms-2">تحديد الكل</label>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-success btn-sm" onclick="approveSelected()">
                                                <i class="bi bi-check-circle me-1"></i> قبول المحدد
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="rejectSelected()">
                                                <i class="bi bi-x-circle me-1"></i> رفض المحدد
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">
                                            <input type="checkbox" id="select-all-table" class="form-check-input">
                                        </th>
                                        <th style="width: 50px;">#</th>
                                        <th style="min-width: 180px;">الطالب</th>
                                        <th style="min-width: 200px;">المادة</th>
                                        <th style="min-width: 120px;">الصف</th>
                                        <th style="min-width: 150px;">تاريخ الطلب</th>
                                        <th style="min-width: 200px;">العمليات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($enrollments as $index => $enrollment)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="enrollment_ids[]" value="{{ $enrollment->id }}" class="form-check-input enrollment-checkbox">
                                            </td>
                                            <td>{{ $enrollments->firstItem() + $index }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($enrollment->user->avatar)
                                                        <img src="{{ asset('storage/' . $enrollment->user->avatar) }}" 
                                                             alt="{{ $enrollment->user->name }}" 
                                                             class="avatar avatar-sm rounded-circle me-2">
                                                    @else
                                                        <div class="avatar avatar-sm bg-primary-transparent rounded-circle me-2">
                                                            <span class="text-primary">{{ substr($enrollment->user->name, 0, 1) }}</span>
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
                                                @if($enrollment->subject->description)
                                                    <small class="text-muted">{{ \Illuminate\Support\Str::limit($enrollment->subject->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($enrollment->subject->schoolClass)
                                                    <span class="badge bg-info-transparent text-info">
                                                        {{ $enrollment->subject->schoolClass->name }}
                                                    </span>
                                                    @if($enrollment->subject->schoolClass->stage)
                                                        <br><small class="text-muted">{{ $enrollment->subject->schoolClass->stage->name }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>{{ $enrollment->enrolled_at ? $enrollment->enrolled_at->format('Y-m-d') : '-' }}</div>
                                                <small class="text-muted">{{ $enrollment->enrolled_at ? $enrollment->enrolled_at->format('H:i') : '' }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <form method="POST" action="{{ route('admin.enrollments.approve', $enrollment->id) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('هل أنت متأكد من قبول هذا الطلب؟')">
                                                            <i class="bi bi-check-circle me-1"></i> قبول
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.enrollments.reject', $enrollment->id) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من رفض هذا الطلب؟')">
                                                            <i class="bi bi-x-circle me-1"></i> رفض
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                                <p class="text-muted mb-0">لا توجد طلبات انضمام معلقة</p>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal للتعليقات (اختياري) -->
    <div class="modal fade" id="notesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة ملاحظات</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="notes-form" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="notes" class="form-label">ملاحظات</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" onclick="submitNotesForm()">حفظ</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script>
    // تحديد/إلغاء تحديد الكل
    document.getElementById('select-all')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.enrollment-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        document.getElementById('select-all-table').checked = this.checked;
    });

    document.getElementById('select-all-table')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.enrollment-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        document.getElementById('select-all').checked = this.checked;
    });

    // قبول المحدد
    function approveSelected() {
        const selected = getSelectedIds();
        if (selected.length === 0) {
            alert('يرجى تحديد طلب واحد على الأقل');
            return;
        }

        if (!confirm(`هل أنت متأكد من قبول ${selected.length} طلب؟`)) {
            return;
        }

        const form = document.getElementById('bulk-action-form');
        form.action = '{{ route("admin.enrollments.approve-multiple") }}';
        form.method = 'POST';
        
        // إضافة IDs المحددة
        selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'enrollment_ids[]';
            input.value = id;
            form.appendChild(input);
        });

        form.submit();
    }

    // رفض المحدد
    function rejectSelected() {
        const selected = getSelectedIds();
        if (selected.length === 0) {
            alert('يرجى تحديد طلب واحد على الأقل');
            return;
        }

        if (!confirm(`هل أنت متأكد من رفض ${selected.length} طلب؟`)) {
            return;
        }

        const form = document.getElementById('bulk-action-form');
        form.action = '{{ route("admin.enrollments.reject-multiple") }}';
        form.method = 'POST';
        
        // إضافة IDs المحددة
        selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'enrollment_ids[]';
            input.value = id;
            form.appendChild(input);
        });

        form.submit();
    }

    // الحصول على IDs المحددة
    function getSelectedIds() {
        const checkboxes = document.querySelectorAll('.enrollment-checkbox:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    }
</script>
@stop


