@extends('admin.layouts.master')

@section('page-title')
    عناصر المكتبة
@stop

@push('styles')
    @include('admin.pages.library.partials.library-styles')
@endpush

@section('content')
    <div class="main-content app-content library-page">
        <div class="container-fluid">

            <div class="library-hero my-4">
                <div class="library-hero__icon">
                    <i class="bi bi-collection-fill"></i>
                </div>
                <div class="library-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">المكتبة</li>
                        </ol>
                    </nav>
                    <h4 class="library-hero__title">عناصر المكتبة</h4>
                    <p class="library-hero__subtitle">ملفات وروابط مرتبطة بصفوف ومواد، متاحة للطلاب المسجَّلين فيها</p>
                </div>
                <div class="library-stat-mini">
                    <span class="library-stat-mini__value">{{ number_format($items->total()) }}</span>
                    <span class="library-stat-mini__label">عنصر مطابق</span>
                </div>
                <div class="library-hero__actions">
                    <a href="{{ route('admin.library.categories.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-tags me-1"></i> التصنيفات
                    </a>
                    @can('library-item-create')
                        <a href="{{ route('admin.library.items.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg me-1"></i> عنصر جديد
                        </a>
                    @endcan
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

            <div class="library-card">
                <div class="library-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="library-card__header-icon"><i class="bi bi-funnel"></i></span>
                        تصفية وبحث
                    </div>
                </div>
                <div class="library-card__body">
                    <form method="GET" action="{{ route('admin.library.items.index') }}" class="library-filters">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">بحث</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                                    <input type="text" name="search" class="form-control border-start-0"
                                           placeholder="بالعنوان أو الوصف" value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">التصنيف</label>
                                <select name="category_id" class="form-select">
                                    <option value="">كل التصنيفات</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ (string) request('category_id') === (string) $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-2">
                                <label class="form-label">النوع</label>
                                <select name="type" class="form-select">
                                    <option value="">كل الأنواع</option>
                                    @foreach (\App\Models\LibraryItem::TYPES as $value => $label)
                                        <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-2">
                                <label class="form-label">الحالة</label>
                                <select name="is_public" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="1" {{ request('is_public') === '1' ? 'selected' : '' }}>عام</option>
                                    <option value="0" {{ request('is_public') === '0' ? 'selected' : '' }}>خاص</option>
                                </select>
                            </div>
                            <div class="col-md-12 col-lg-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">بحث</button>
                                <a href="{{ route('admin.library.items.index') }}" class="btn btn-outline-secondary btn-sm">مسح</a>
                            </div>
                        </div>
                    </form>

                    <div class="library-table-wrap mt-4">
                        <div class="table-responsive">
                            <table class="table library-table align-middle mb-0 text-center">
                                <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th style="min-width: 200px;">العنوان</th>
                                    <th style="min-width: 130px;">التصنيف</th>
                                    <th style="min-width: 100px;">النوع</th>
                                    <th style="min-width: 130px;">الصف</th>
                                    <th style="min-width: 130px;">المادة</th>
                                    <th style="min-width: 100px;">الحالة</th>
                                    <th style="min-width: 220px;">العمليات</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($items as $item)
                                    <tr>
                                        <td>{{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}</td>
                                        <td class="text-start">
                                            <span class="fw-semibold">{{ $item->title }}</span>
                                            @if ($item->is_featured)
                                                <span class="badge bg-warning text-dark ms-1">مميز</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->category?->name ?? '—' }}</td>
                                        <td><span class="badge bg-info">{{ \App\Models\LibraryItem::TYPES[$item->type] ?? $item->type }}</span></td>
                                        <td>{{ $item->schoolClass?->name ?? '—' }}</td>
                                        <td>{{ $item->subject?->name ?? '—' }}</td>
                                        <td>
                                            @if ($item->is_public)
                                                <span class="badge bg-success">عام</span>
                                            @else
                                                <span class="badge bg-secondary">خاص</span>
                                            @endif
                                        </td>
                                        <td>
                                            @can('library-item-show')
                                                <a href="{{ route('admin.library.items.show', $item->id) }}" class="btn btn-sm btn-info text-white">
                                                    <i class="fas fa-eye"></i> عرض
                                                </a>
                                            @endcan
                                            @can('library-item-edit')
                                                <a href="{{ route('admin.library.items.edit', $item->id) }}" class="btn btn-sm btn-warning text-white">
                                                    <i class="fas fa-edit"></i> تعديل
                                                </a>
                                            @endcan
                                            @can('library-item-delete')
                                                <button type="button" class="btn btn-sm btn-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteLibraryItem{{ $item->id }}">
                                                    <i class="fas fa-trash-alt"></i> حذف
                                                </button>
                                                @include('admin.pages.library.items.delete', ['item' => $item])
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">
                                            <div class="library-empty">
                                                <i class="bi bi-collection"></i>
                                                لا توجد عناصر مكتبة مسجلة حالياً
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-3">
                        {{ $items->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
