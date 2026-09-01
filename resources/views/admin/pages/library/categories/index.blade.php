@extends('admin.layouts.master')

@section('page-title')
    تصنيفات المكتبة
@stop

@push('styles')
    @include('admin.pages.library.partials.library-styles')
@endpush

@section('content')
    <div class="main-content app-content library-page">
        <div class="container-fluid">

            <div class="library-hero my-4">
                <div class="library-hero__icon">
                    <i class="bi bi-tags-fill"></i>
                </div>
                <div class="library-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.library.items.index') }}">المكتبة</a></li>
                            <li class="breadcrumb-item active" aria-current="page">التصنيفات</li>
                        </ol>
                    </nav>
                    <h4 class="library-hero__title">تصنيفات المكتبة</h4>
                    <p class="library-hero__subtitle">تنظيم عناصر المكتبة ضمن تصنيفات واضحة للطلاب والإدارة</p>
                </div>
                <div class="library-stat-mini">
                    <span class="library-stat-mini__value">{{ number_format($categories->total()) }}</span>
                    <span class="library-stat-mini__label">تصنيف</span>
                </div>
                <div class="library-hero__actions">
                    <a href="{{ route('admin.library.items.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-collection me-1"></i> عناصر المكتبة
                    </a>
                    @can('library-category-create')
                        <a href="{{ route('admin.library.categories.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg me-1"></i> تصنيف جديد
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
                        <span class="library-card__header-icon"><i class="bi bi-list-ul"></i></span>
                        قائمة تصنيفات المكتبة
                    </div>
                </div>
                <div class="library-card__body">
                    <div class="library-table-wrap">
                        <div class="table-responsive">
                            <table class="table library-table align-middle mb-0 text-center">
                                <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th style="min-width: 180px;">الاسم</th>
                                    <th style="min-width: 100px;">الأيقونة</th>
                                    <th style="min-width: 100px;">اللون</th>
                                    <th style="min-width: 110px;">عدد العناصر</th>
                                    <th style="min-width: 90px;">الترتيب</th>
                                    <th style="min-width: 100px;">الحالة</th>
                                    <th style="min-width: 160px;">العمليات</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($categories as $category)
                                    <tr>
                                        <td>{{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}</td>
                                        <td class="fw-semibold">{{ $category->name }}</td>
                                        <td>
                                            @if ($category->icon)
                                                <i class="{{ $category->icon }}"></i>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if ($category->color)
                                                <span class="badge" style="background-color: {{ $category->color }};">{{ $category->color }}</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $category->items_count }}</td>
                                        <td>{{ $category->order }}</td>
                                        <td>
                                            @if ($category->is_active)
                                                <span class="badge bg-success">نشط</span>
                                            @else
                                                <span class="badge bg-danger">غير نشط</span>
                                            @endif
                                        </td>
                                        <td>
                                            @can('library-category-edit')
                                                <a href="{{ route('admin.library.categories.edit', $category->id) }}"
                                                   class="btn btn-sm btn-warning text-white">
                                                    <i class="fas fa-edit"></i> تعديل
                                                </a>
                                            @endcan
                                            @can('library-category-delete')
                                                <button type="button" class="btn btn-sm btn-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteLibraryCategory{{ $category->id }}">
                                                    <i class="fas fa-trash-alt"></i> حذف
                                                </button>
                                                @include('admin.pages.library.categories.delete', ['category' => $category])
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">
                                            <div class="library-empty">
                                                <i class="bi bi-tags"></i>
                                                لا توجد تصنيفات مسجلة حالياً
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-3">
                        {{ $categories->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
