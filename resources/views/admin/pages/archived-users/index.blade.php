@extends('admin.layouts.master')

@section('page-title')
    الأرشيف - المستخدمون المؤرشفون
@stop

@section('css')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">الأرشيف - المستخدمون المؤرشفون</h5>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> العودة للمستخدمين
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <!-- Filters Card -->
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.archived-users.index') }}"
                                  class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label mb-1">البحث</label>
                                    <input type="text" name="query" class="form-control form-control-sm"
                                           placeholder="بحث بالاسم أو الإيميل أو الهاتف أو رقم الطالب"
                                           value="{{ request('query') }}">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label mb-1">الحالة</label>
                                    <select name="is_active" class="form-select form-select-sm">
                                        <option value="">كل الحالات</option>
                                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشط</option>
                                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشط</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label mb-1">من تاريخ</label>
                                    <input type="date" name="archived_from" class="form-control form-control-sm"
                                           value="{{ request('archived_from') }}">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label mb-1">إلى تاريخ</label>
                                    <input type="date" name="archived_to" class="form-control form-control-sm"
                                           value="{{ request('archived_to') }}">
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

                                <div class="col-md-6 d-flex gap-2 flex-wrap align-items-end">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-search me-1"></i> بحث
                                    </button>
                                    <a href="{{ route('admin.archived-users.index') }}" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-times me-1"></i> مسح الفلاتر
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Bulk Actions Bar -->
                    <div class="card shadow-sm border-0 mb-3" id="bulk-actions-bar" style="display: none;">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted" id="selected-count-text">تم تحديد <strong>0</strong> مستخدم</span>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-success btn-sm" id="bulk-restore-btn">
                                        <i class="fas fa-undo me-1"></i> <span id="bulk-restore-text">استعادة المحدد</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Archived Users List Card -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header">
                            <h5 class="mb-0 fw-bold">قائمة المستخدمين المؤرشفين ({{ $archivedUsers->total() }})</h5>
                        </div>

                        <div class="card-body">
                            <!-- Bulk restore form (hidden, only for JavaScript) -->
                            <form id="bulk-restore-form" action="{{ route('admin.archived-users.bulk-restore') }}" method="POST" style="display: none;">
                                @csrf
                                <input type="hidden" name="archived_user_ids" id="archived_user_ids_input">
                            </form>

                            <div class="table-responsive">
                                    <table class="table table-striped align-middle table-hover table-bordered mb-0 text-center">
                                        <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">
                                                <input type="checkbox" id="select-all" class="form-check-input">
                                            </th>
                                            <th style="width: 50px;">#</th>
                                            <th style="min-width: 150px;">الاسم</th>
                                            <th style="min-width: 180px;">البريد الإلكتروني</th>
                                            <th style="min-width: 120px;">الهاتف</th>
                                            <th style="min-width: 100px;">الحالة</th>
                                            <th style="min-width: 150px;">تاريخ الأرشفة</th>
                                            <th style="min-width: 150px;">أرشف بواسطة</th>
                                            <th style="min-width: 200px;">العمليات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($archivedUsers as $archivedUser)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="selected_ids[]" value="{{ $archivedUser->id }}" class="form-check-input row-checkbox">
                                                </td>
                                                <td>{{ $loop->iteration + ($archivedUsers->currentPage() - 1) * $archivedUsers->perPage() }}</td>
                                                <td>
                                                    <a href="{{ route('admin.archived-users.show', $archivedUser->id) }}" class="text-decoration-none fw-semibold">
                                                        {{ $archivedUser->name }}
                                                    </a>
                                                    @if($archivedUser->student_id)
                                                        <br><span class="text-muted small">#{{ $archivedUser->student_id }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $archivedUser->email }}</td>
                                                <td>{{ $archivedUser->phone ?? '-' }}</td>
                                                <td>
                                                    @if($archivedUser->is_active)
                                                        <span class="badge bg-success">نشط</span>
                                                    @else
                                                        <span class="badge bg-danger">غير نشط</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $archivedUser->archived_at?->format('Y-m-d H:i') ?? '-' }}
                                                </td>
                                                <td>
                                                    {{ $archivedUser->archivedByUser?->name ?? '-' }}
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('admin.archived-users.show', $archivedUser->id) }}" 
                                                           class="btn btn-sm btn-info" title="عرض التفاصيل">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-success restore-btn" 
                                                                data-id="{{ $archivedUser->id }}"
                                                                data-name="{{ $archivedUser->name }}"
                                                                title="استعادة">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                        <form action="{{ route('admin.archived-users.destroy', $archivedUser->id) }}" 
                                                              method="POST" class="d-inline" 
                                                              onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل نهائياً؟');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" title="حذف نهائي">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center py-5">
                                                    <p class="text-muted mb-0">لا توجد سجلات مؤرشفة</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <p class="text-muted mb-0">
                                        عرض {{ $archivedUsers->firstItem() ?? 0 }} إلى {{ $archivedUsers->lastItem() ?? 0 }} من {{ $archivedUsers->total() }} سجل
                                    </p>
                                </div>
                                <div>
                                    {{ $archivedUsers->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal استعادة المستخدم -->
<div class="modal fade" id="restoreModal" tabindex="-1" aria-labelledby="restoreModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="restoreModalLabel">
                    <i class="fas fa-undo me-2"></i> استعادة المستخدم
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-undo text-success" style="font-size: 4rem;"></i>
                </div>
                <h5 class="mb-3">هل أنت متأكد من استعادة هذا المستخدم؟</h5>
                <p class="text-muted mb-2">
                    <strong id="restoreUserName"></strong>
                </p>
                <p class="text-muted mb-0">
                    سيتم استعادة المستخدم إلى قائمة المستخدمين النشطين.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> إلغاء
                </button>
                <form id="restoreForm" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-undo me-1"></i> نعم، استعادة المستخدم
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal استعادة المستخدمين المحددين -->
<div class="modal fade" id="bulkRestoreModal" tabindex="-1" aria-labelledby="bulkRestoreModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="bulkRestoreModalLabel">
                    <i class="fas fa-undo me-2"></i> استعادة المستخدمين المحددين
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-undo text-success" style="font-size: 4rem;"></i>
                </div>
                <h5 class="mb-3">هل أنت متأكد من استعادة المستخدمين المحددين؟</h5>
                <p class="text-muted mb-2">
                    سيتم استعادة <strong id="bulkRestoreCount"></strong> مستخدم محدد.
                </p>
                <p class="text-muted mb-0">
                    سيتم استعادة جميع المستخدمين المحددين إلى قائمة المستخدمين النشطين.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> إلغاء
                </button>
                <form id="bulkRestoreForm" method="POST" action="{{ route('admin.archived-users.bulk-restore') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="archived_user_ids" id="bulkRestoreUserIds">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-undo me-1"></i> نعم، استعادة المحدد
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@stop

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    const bulkRestoreBtn = document.getElementById('bulk-restore-btn');
    const bulkRestoreForm = document.getElementById('bulk-restore-form');
    const archivedUserIdsInput = document.getElementById('archived_user_ids_input');

    // Select all functionality
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            toggleBulkRestoreBtn();
        });
    }

    // Individual checkbox change
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            toggleBulkRestoreBtn();
            updateSelectAll();
        });
    });

    function toggleBulkRestoreBtn() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        const bulkActionsBar = document.getElementById('bulk-actions-bar');
        const bulkRestoreText = document.getElementById('bulk-restore-text');
        const selectedCountText = document.getElementById('selected-count-text');
        
        if (checked.length > 0) {
            // Show bulk actions bar
            if (bulkActionsBar) {
                bulkActionsBar.style.display = 'block';
            }
            
            // Update selected count
            if (selectedCountText) {
                selectedCountText.innerHTML = `تم تحديد <strong>${checked.length}</strong> مستخدم`;
            }
            
            // Update restore button text
            if (bulkRestoreText) {
                bulkRestoreText.textContent = `استعادة المحدد (${checked.length})`;
            }
        } else {
            // Hide bulk actions bar
            if (bulkActionsBar) {
                bulkActionsBar.style.display = 'none';
            }
        }
    }
    
    // Initialize button state on page load
    toggleBulkRestoreBtn();

    function updateSelectAll() {
        if (selectAll) {
            const allChecked = checkboxes.length > 0 && Array.from(checkboxes).every(cb => cb.checked);
            selectAll.checked = allChecked;
        }
    }

    // Bulk restore
    if (bulkRestoreBtn) {
        bulkRestoreBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const checked = document.querySelectorAll('.row-checkbox:checked');
            const ids = Array.from(checked).map(cb => cb.value);
            
            if (ids.length === 0) {
                alert('يرجى اختيار مستخدم واحد على الأقل للاستعادة');
                return;
            }

            // Set count and IDs in modal
            document.getElementById('bulkRestoreCount').textContent = ids.length;
            document.getElementById('bulkRestoreUserIds').value = JSON.stringify(ids);
            
            // Show modal
            const bulkRestoreModal = new bootstrap.Modal(document.getElementById('bulkRestoreModal'));
            bulkRestoreModal.show();
        });
    }

    // Individual restore buttons
    document.querySelectorAll('.restore-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const archivedUserId = this.getAttribute('data-id');
            const userName = this.getAttribute('data-name');
            
            document.getElementById('restoreUserName').textContent = userName;
            document.getElementById('restoreForm').action = '{{ route("admin.archived-users.restore", ":id") }}'.replace(':id', archivedUserId);
            
            const restoreModal = new bootstrap.Modal(document.getElementById('restoreModal'));
            restoreModal.show();
        });
    });
});
</script>
@endpush
