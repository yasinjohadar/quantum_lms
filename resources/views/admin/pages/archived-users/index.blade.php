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

                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-primary btn-sm me-2">
                                        <i class="fas fa-search me-1"></i> بحث
                                    </button>
                                    <a href="{{ route('admin.archived-users.index') }}" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-times me-1"></i> مسح الفلاتر
                                    </a>
                                    <button type="button" class="btn btn-success btn-sm" id="bulk-restore-btn" style="display: none;">
                                        <i class="fas fa-undo me-1"></i> استعادة المحدد
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Archived Users List Card -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header">
                            <h5 class="mb-0 fw-bold">قائمة المستخدمين المؤرشفين ({{ $archivedUsers->total() }})</h5>
                        </div>

                        <div class="card-body">
                            <form id="bulk-restore-form" action="{{ route('admin.archived-users.bulk-restore') }}" method="POST">
                                @csrf
                                <input type="hidden" name="archived_user_ids" id="archived_user_ids_input">

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
                                                        <form action="{{ route('admin.archived-users.restore', $archivedUser->id) }}" 
                                                              method="POST" class="d-inline" 
                                                              onsubmit="return confirm('هل أنت متأكد من استعادة هذا المستخدم؟');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success" title="استعادة">
                                                                <i class="fas fa-undo"></i>
                                                            </button>
                                                        </form>
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
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
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
        if (bulkRestoreBtn) {
            if (checked.length > 0) {
                bulkRestoreBtn.style.display = 'inline-block';
                bulkRestoreBtn.textContent = `استعادة المحدد (${checked.length})`;
            } else {
                bulkRestoreBtn.style.display = 'none';
            }
        }
    }

    function updateSelectAll() {
        if (selectAll) {
            const allChecked = checkboxes.length > 0 && Array.from(checkboxes).every(cb => cb.checked);
            selectAll.checked = allChecked;
        }
    }

    // Bulk restore
    if (bulkRestoreBtn && bulkRestoreForm) {
        bulkRestoreBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const checked = document.querySelectorAll('.row-checkbox:checked');
            const ids = Array.from(checked).map(cb => cb.value);
            
            if (ids.length === 0) {
                alert('يرجى اختيار مستخدم واحد على الأقل للاستعادة');
                return;
            }

            if (confirm('هل أنت متأكد من استعادة ' + ids.length + ' مستخدم محدد؟')) {
                archivedUserIdsInput.value = JSON.stringify(ids);
                
                // Ensure CSRF token is present
                if (!document.querySelector('#bulk-restore-form input[name="_token"]')) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = '{{ csrf_token() }}';
                    bulkRestoreForm.appendChild(csrfInput);
                }
                
                bulkRestoreForm.method = 'POST';
                bulkRestoreForm.submit();
            }
        });
    }
});
</script>
@stop
