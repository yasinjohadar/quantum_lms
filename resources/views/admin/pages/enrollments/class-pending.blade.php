@extends('admin.layouts.master')

@section('page-title')
    طلبات الانضمام للصف المعلقة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">طلبات الانضمام للصف المعلقة</h5>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.enrollments.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                </a>
                <a href="{{ route('admin.enrollments.pending') }}" class="btn btn-info btn-sm">
                    <i class="fas fa-list me-1"></i> طلبات المواد
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
                        <h6 class="text-muted mb-2">الطلبات المقبولة</h6>
                        <h3 class="mb-0">{{ $approvedCount }}</h3>
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
                        <form method="GET" action="{{ route('admin.enrollments.class-pending') }}">
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
                                    <label class="form-label mb-1">الصف</label>
                                    <select name="class_id" class="form-select form-select-sm">
                                        <option value="">كل الصفوف</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label mb-1 d-block">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="bi bi-search me-1"></i> بحث
                                        </button>
                                        <a href="{{ route('admin.enrollments.class-pending') }}" class="btn btn-secondary btn-sm">
                                            <i class="bi bi-arrow-clockwise me-1"></i> إعادة تعيين
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- الجدول -->
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between align-items-center w-100">
                            <h5 class="mb-0">قائمة الطلبات المعلقة</h5>
                            @if($classEnrollments->count() > 0)
                                <form method="POST" action="{{ route('admin.enrollments.class.approve-multiple') }}" class="d-inline" id="bulk-approve-form">
                                    @csrf
                                    <input type="hidden" name="class_enrollment_ids" id="bulk-approve-ids">
                                    <button type="submit" class="btn btn-success btn-sm" id="bulk-approve-btn" disabled>
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
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @if($classEnrollments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" id="select-all" class="form-check-input">
                                            </th>
                                            <th>الطالب</th>
                                            <th>الصف</th>
                                            <th>تاريخ الطلب</th>
                                            <th>ملاحظات</th>
                                            <th width="200">الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($classEnrollments as $classEnrollment)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" class="form-check-input enrollment-checkbox" 
                                                           value="{{ $classEnrollment->id }}">
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($classEnrollment->user->avatar)
                                                            <img src="{{ asset('storage/' . $classEnrollment->user->avatar) }}" 
                                                                 alt="{{ $classEnrollment->user->name }}" 
                                                                 class="rounded-circle me-2" width="32" height="32">
                                                        @else
                                                            <div class="bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                                 style="width: 32px; height: 32px;">
                                                                {{ substr($classEnrollment->user->name, 0, 1) }}
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <div class="fw-bold">{{ $classEnrollment->user->name }}</div>
                                                            <small class="text-muted">{{ $classEnrollment->user->email }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">{{ $classEnrollment->schoolClass->name }}</span>
                                                    @if($classEnrollment->schoolClass->stage)
                                                        <br><small class="text-muted">{{ $classEnrollment->schoolClass->stage->name }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ $classEnrollment->created_at->format('Y-m-d H:i') }}</td>
                                                <td>
                                                    <small class="text-muted">{{ $classEnrollment->notes ? \Illuminate\Support\Str::limit($classEnrollment->notes, 50) : '-' }}</small>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-success btn-sm approve-btn" 
                                                                data-id="{{ $classEnrollment->id }}"
                                                                data-student="{{ $classEnrollment->user->name }}"
                                                                data-class="{{ $classEnrollment->schoolClass->name }}">
                                                            <i class="bi bi-check-circle me-1"></i> قبول
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm reject-btn"
                                                                data-id="{{ $classEnrollment->id }}"
                                                                data-student="{{ $classEnrollment->user->name }}"
                                                                data-class="{{ $classEnrollment->schoolClass->name }}">
                                                            <i class="bi bi-x-circle me-1"></i> رفض
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-3">
                                {{ $classEnrollments->links() }}
                            </div>
                        @else
                            <div class="alert alert-info text-center">
                                <i class="bi bi-info-circle me-2"></i>
                                لا توجد طلبات انضمام للصف معلقة حالياً.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal قبول الطلب -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveModalLabel">
                    <i class="bi bi-check-circle-fill me-2"></i> قبول طلب الانضمام
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                </div>
                <h5 class="mb-3">هل أنت متأكد من قبول هذا الطلب؟</h5>
                <p class="text-muted mb-2">
                    <strong id="approveStudentName"></strong>
                </p>
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

<!-- Modal رفض الطلب -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="bi bi-x-circle-fill me-2"></i> رفض طلب الانضمام
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i>
                </div>
                <h5 class="mb-3">هل أنت متأكد من رفض هذا الطلب؟</h5>
                <p class="text-muted mb-2">
                    <strong id="rejectStudentName"></strong>
                </p>
                <p class="text-muted mb-0">
                    الصف: <strong id="rejectClassName"></strong>
                </p>
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

        // Select all functionality
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkButtons();
            });
        }

        // Individual checkbox change
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateBulkButtons();
                if (selectAll) {
                    selectAll.checked = Array.from(checkboxes).every(cb => cb.checked);
                }
            });
        });

        function updateBulkButtons() {
            const selectedIds = Array.from(checkboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);

            if (selectedIds.length > 0) {
                bulkApproveBtn.disabled = false;
                bulkRejectBtn.disabled = false;
                bulkApproveIds.value = JSON.stringify(selectedIds);
                bulkRejectIds.value = JSON.stringify(selectedIds);
            } else {
                bulkApproveBtn.disabled = true;
                bulkRejectBtn.disabled = true;
            }
        }

        // Individual approve buttons
        document.querySelectorAll('.approve-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const enrollmentId = this.getAttribute('data-id');
                const studentName = this.getAttribute('data-student');
                const className = this.getAttribute('data-class');
                
                document.getElementById('approveStudentName').textContent = studentName;
                document.getElementById('approveClassName').textContent = className;
                document.getElementById('approveForm').action = '{{ route("admin.enrollments.class.approve", ":id") }}'.replace(':id', enrollmentId);
                
                const approveModal = new bootstrap.Modal(document.getElementById('approveModal'));
                approveModal.show();
            });
        });

        // Individual reject buttons
        document.querySelectorAll('.reject-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const enrollmentId = this.getAttribute('data-id');
                const studentName = this.getAttribute('data-student');
                const className = this.getAttribute('data-class');
                
                document.getElementById('rejectStudentName').textContent = studentName;
                document.getElementById('rejectClassName').textContent = className;
                document.getElementById('rejectForm').action = '{{ route("admin.enrollments.class.reject", ":id") }}'.replace(':id', enrollmentId);
                
                const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
                rejectModal.show();
            });
        });

        // Bulk approve form submission
        if (bulkApproveForm) {
            bulkApproveForm.addEventListener('submit', function(e) {
                const selectedIds = JSON.parse(bulkApproveIds.value);
                if (!confirm(`هل أنت متأكد من قبول ${selectedIds.length} طلب؟ سيتم إنشاء انضمامات لجميع المواد في الصفوف.`)) {
                    e.preventDefault();
                }
            });
        }

        // Bulk reject form submission
        if (bulkRejectForm) {
            bulkRejectForm.addEventListener('submit', function(e) {
                const selectedIds = JSON.parse(bulkRejectIds.value);
                if (!confirm(`هل أنت متأكد من رفض ${selectedIds.length} طلب؟`)) {
                    e.preventDefault();
                }
            });
        }
    });
</script>
@endpush
@endsection

