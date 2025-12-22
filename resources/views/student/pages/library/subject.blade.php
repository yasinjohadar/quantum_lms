@extends('student.layouts.master')

@section('page-title')
    مكتبة: {{ $subject->name }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">مكتبة: {{ $subject->name }}</h5>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form method="GET" action="{{ route('student.library.subject', $subject->id) }}" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="بحث..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="category_id" class="form-select">
                                    <option value="">كل التصنيفات</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="type" class="form-select">
                                    <option value="">كل الأنواع</option>
                                    @foreach(\App\Models\LibraryItem::TYPES as $key => $label)
                                        <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">بحث</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            @forelse($items as $item)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        @if($item->thumbnail)
                            <img src="{{ Storage::disk('public')->url($item->thumbnail) }}" class="card-img-top" alt="{{ $item->title }}" style="height: 200px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fe fe-file" style="font-size: 48px; color: #ccc;"></i>
                            </div>
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">{{ $item->title }}</h5>
                            <p class="card-text text-muted small">{{ Str::limit($item->description, 100) }}</p>
                            
                            <div class="mb-2">
                                <span class="badge bg-info">{{ $item->category->name ?? '-' }}</span>
                                <span class="badge bg-secondary">{{ \App\Models\LibraryItem::TYPES[$item->type] ?? $item->type }}</span>
                            </div>

                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-download me-1"></i> {{ $item->download_count }}
                                    <i class="fas fa-eye ms-2 me-1"></i> {{ $item->view_count }}
                                </small>
                            </div>

                            <a href="{{ route('student.library.show', $item->id) }}" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-eye me-1"></i> عرض
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>
                        لا توجد عناصر متاحة في مكتبة هذه المادة حالياً.
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $items->links() }}
        </div>
    </div>
</div>
@stop

