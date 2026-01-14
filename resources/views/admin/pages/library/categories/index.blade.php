@extends('admin.layouts.master')

@section('page-title')
    تصنيفات المكتبة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تصنيفات المكتبة</h5>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.library.categories.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> إضافة تصنيف جديد
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

        <div class="row">
            <div class="col-xl-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold">قائمة التصنيفات</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle table-hover table-bordered mb-0 text-center">
                                <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>الاسم</th>
                                    <th>الأيقونة</th>
                                    <th>اللون</th>
                                    <th>عدد العناصر</th>
                                    <th>الترتيب</th>
                                    <th>الحالة</th>
                                    <th style="width: 180px;">العمليات</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($categories as $category)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $category->name }}</td>
                                        <td>
                                            @if($category->icon)
                                                <i class="{{ $category->icon }}" style="color: {{ $category->color ?? '#007bff' }}; font-size: 20px;"></i>
                                            @else
                                                <i class="fe fe-folder" style="color: {{ $category->color ?? '#007bff' }}; font-size: 20px;"></i>
                                            @endif
                                        </td>
                                        <td>
                                            @if($category->color)
                                                <span class="badge" style="background-color: {{ $category->color }};">{{ $category->color }}</span>
                                            @else
                                                <span class="badge bg-secondary">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $category->items_count ?? 0 }}</td>
                                        <td>{{ $category->order }}</td>
                                        <td>
                                            <span class="badge bg-{{ $category->is_active ? 'success' : 'danger' }}-transparent">
                                                {{ $category->is_active ? 'نشط' : 'غير نشط' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2 justify-content-center">
                                                <a href="{{ route('admin.library.categories.edit', $category->id) }}" class="btn btn-sm btn-primary-light" data-bs-toggle="tooltip" title="تعديل">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger-light delete-category-btn" 
                                                        data-bs-toggle="tooltip" 
                                                        title="حذف"
                                                        data-category-id="{{ $category->id }}"
                                                        data-category-name="{{ $category->name }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">لا توجد تصنيفات متاحة.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $categories->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal حذف التصنيف -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteCategoryModalLabel">
                    <i class="fas fa-trash me-2"></i> حذف التصنيف
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-trash text-danger" style="font-size: 4rem;"></i>
                </div>
                <h5 class="mb-3">هل أنت متأكد من حذف هذا التصنيف؟</h5>
                <p class="text-muted mb-2">
                    <strong id="deleteCategoryName"></strong>
                </p>
                <p class="text-danger small mb-0">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    لا يمكن التراجع عن هذا الإجراء
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> إلغاء
                </button>
                <form id="deleteCategoryForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> نعم، حذف التصنيف
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
    // Delete category buttons
    document.querySelectorAll('.delete-category-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoryId = this.getAttribute('data-category-id');
            const categoryName = this.getAttribute('data-category-name');
            
            document.getElementById('deleteCategoryName').textContent = categoryName;
            document.getElementById('deleteCategoryForm').action = '{{ route("admin.library.categories.destroy", ":id") }}'.replace(':id', categoryId);
            
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteCategoryModal'));
            deleteModal.show();
        });
    });
});
</script>
@endpush

