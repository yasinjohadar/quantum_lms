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
                    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <h5 class="mb-0 fw-bold">قائمة العناصر</h5>

                        <form method="GET" action="{{ route('admin.library.items.index') }}" class="d-flex flex-wrap gap-2 align-items-center">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="بحث بالعنوان أو الوصف" value="{{ request('search') }}" style="min-width: 220px;">

                            <select name="category_id" class="form-select form-select-sm" style="min-width: 150px;">
                                <option value="">كل التصنيفات</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>

                            <select name="type" class="form-select form-select-sm" style="min-width: 150px;">
                                <option value="">كل الأنواع</option>
                                <option value="file" {{ request('type') == 'file' ? 'selected' : '' }}>ملف</option>
                                <option value="link" {{ request('type') == 'link' ? 'selected' : '' }}>رابط</option>
                                <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>فيديو</option>
                                <option value="document" {{ request('type') == 'document' ? 'selected' : '' }}>مستند</option>
                                <option value="book" {{ request('type') == 'book' ? 'selected' : '' }}>كتاب</option>
                                <option value="worksheet" {{ request('type') == 'worksheet' ? 'selected' : '' }}>ورقة عمل</option>
                            </select>

                            <select name="subject_id" class="form-select form-select-sm" style="min-width: 150px;">
                                <option value="">كل المواد</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>

                            <select name="is_public" class="form-select form-select-sm" style="min-width: 150px;">
                                <option value="">كل الحالات</option>
                                <option value="1" {{ request('is_public') === '1' ? 'selected' : '' }}>عام</option>
                                <option value="0" {{ request('is_public') === '0' ? 'selected' : '' }}>خاص</option>
                            </select>

                            <button type="submit" class="btn btn-secondary btn-sm">بحث</button>
                            <a href="{{ route('admin.library.items.index') }}" class="btn btn-outline-danger btn-sm">مسح الفلاتر</a>
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
                                                <form action="{{ route('admin.library.items.destroy', $item->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا العنصر؟');" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger-light" data-bs-toggle="tooltip" title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
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
@stop

