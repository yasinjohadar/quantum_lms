@extends('admin.layouts.master')

@section('page-title')
    عناصر المكتبة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">عناصر المكتبة</h5>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.library.items.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> إضافة عنصر جديد
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
                        <h5 class="mb-0 fw-bold">قائمة العناصر</h5>
                    </div>

                    <!-- الفلاتر بشكل أفقي -->
                    <div class="card-body border-bottom">
                        <form method="GET" action="{{ route('admin.library.items.index') }}">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small mb-1">البحث</label>
                                    <input type="text" name="search" class="form-control form-control-sm" placeholder="بحث بالعنوان أو الوصف" value="{{ request('search') }}">
                                </div>

                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small mb-1">التصنيف</label>
                                    <select name="category_id" class="form-select form-select-sm">
                                        <option value="">كل التصنيفات</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small mb-1">النوع</label>
                                    <select name="type" class="form-select form-select-sm">
                                        <option value="">كل الأنواع</option>
                                        <option value="file" {{ request('type') == 'file' ? 'selected' : '' }}>ملف</option>
                                        <option value="link" {{ request('type') == 'link' ? 'selected' : '' }}>رابط</option>
                                        <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>فيديو</option>
                                        <option value="document" {{ request('type') == 'document' ? 'selected' : '' }}>مستند</option>
                                        <option value="book" {{ request('type') == 'book' ? 'selected' : '' }}>كتاب</option>
                                        <option value="worksheet" {{ request('type') == 'worksheet' ? 'selected' : '' }}>ورقة عمل</option>
                                    </select>
                                </div>

                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small mb-1">المادة</label>
                                    <select name="subject_id" class="form-select form-select-sm">
                                        <option value="">كل المواد</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                                {{ $subject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small mb-1">الحالة</label>
                                    <select name="is_public" class="form-select form-select-sm">
                                        <option value="">كل الحالات</option>
                                        <option value="1" {{ request('is_public') === '1' ? 'selected' : '' }}>عام</option>
                                        <option value="0" {{ request('is_public') === '0' ? 'selected' : '' }}>خاص</option>
                                    </select>
                                </div>

                                <div class="col-md-12 col-lg-2">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-secondary btn-sm w-100">بحث</button>
                                        <a href="{{ route('admin.library.items.index') }}" class="btn btn-outline-danger btn-sm">مسح</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle table-hover table-bordered mb-0 text-center">
                                <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>العنوان</th>
                                    <th>التصنيف</th>
                                    <th>النوع</th>
                                    <th>المادة</th>
                                    <th>التحميلات</th>
                                    <th>المشاهدات</th>
                                    <th>التقييم</th>
                                    <th>الحالة</th>
                                    <th style="width: 200px;">العمليات</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($items as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <a href="{{ route('admin.library.items.show', $item->id) }}" class="text-primary">
                                                {{ $item->title }}
                                            </a>
                                            @if($item->is_featured)
                                                <span class="badge bg-warning ms-1">مميز</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->category->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-info-transparent">
                                                {{ \App\Models\LibraryItem::TYPES[$item->type] ?? $item->type }}
                                            </span>
                                        </td>
                                        <td>{{ $item->subject->name ?? 'عام' }}</td>
                                        <td>{{ $item->download_count }}</td>
                                        <td>{{ $item->view_count }}</td>
                                        <td>
                                            @if($item->total_ratings > 0)
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <span class="text-warning me-1">★</span>
                                                    <span>{{ number_format($item->average_rating, 1) }}</span>
                                                    <span class="text-muted ms-1">({{ $item->total_ratings }})</span>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $item->is_public ? 'success' : 'warning' }}-transparent">
                                                {{ $item->is_public ? 'عام' : 'خاص' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2 justify-content-center">
                                                <a href="{{ route('admin.library.items.show', $item->id) }}" class="btn btn-sm btn-info-light" data-bs-toggle="tooltip" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.library.items.edit', $item->id) }}" class="btn btn-sm btn-primary-light" data-bs-toggle="tooltip" title="تعديل">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
                                                <a href="{{ route('admin.library.items.stats', $item->id) }}" class="btn btn-sm btn-secondary-light" data-bs-toggle="tooltip" title="إحصائيات">
                                                    <i class="fas fa-chart-bar"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger-light" data-bs-toggle="modal" data-bs-target="#deleteItemModal{{ $item->id }}" title="حذف">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">لا توجد عناصر متاحة.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $items->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals for Delete Confirmation -->
@foreach($items as $item)
<div class="modal fade" id="deleteItemModal{{ $item->id }}" tabindex="-1" aria-labelledby="deleteItemModalLabel{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body text-center px-4 pb-4">
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10" style="width: 80px; height: 80px;">
                        <i class="bi bi-trash-fill text-danger" style="font-size: 40px;"></i>
                    </div>
                </div>
                <h5 class="modal-title mb-3 fw-bold" id="deleteItemModalLabel{{ $item->id }}">
                    تأكيد حذف العنصر
                </h5>
                <p class="text-muted mb-3">
                    هل أنت متأكد من حذف العنصر <strong class="text-dark">"{{ $item->title }}"</strong>؟
                </p>
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    <small class="m-0">هذه العملية لا يمكن التراجع عنها. سيتم حذف العنصر وجميع البيانات المرتبطة به نهائياً.</small>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> إلغاء
                </button>
                <form action="{{ route('admin.library.items.destroy', $item->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> حذف
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@stop

@section('js')
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>
@stop


                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">لا توجد عناصر متاحة.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $items->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals for Delete Confirmation -->
@foreach($items as $item)
<div class="modal fade" id="deleteItemModal{{ $item->id }}" tabindex="-1" aria-labelledby="deleteItemModalLabel{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body text-center px-4 pb-4">
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10" style="width: 80px; height: 80px;">
                        <i class="bi bi-trash-fill text-danger" style="font-size: 40px;"></i>
                    </div>
                </div>
                <h5 class="modal-title mb-3 fw-bold" id="deleteItemModalLabel{{ $item->id }}">
                    تأكيد حذف العنصر
                </h5>
                <p class="text-muted mb-3">
                    هل أنت متأكد من حذف العنصر <strong class="text-dark">"{{ $item->title }}"</strong>؟
                </p>
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    <small class="m-0">هذه العملية لا يمكن التراجع عنها. سيتم حذف العنصر وجميع البيانات المرتبطة به نهائياً.</small>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> إلغاء
                </button>
                <form action="{{ route('admin.library.items.destroy', $item->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> حذف
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@stop

@section('js')
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>
@stop

