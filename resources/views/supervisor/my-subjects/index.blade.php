@extends('admin.layouts.master')

@section('page-title')
    موادي المخصصة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">موادي المخصصة</h5>
                    <p class="text-muted mb-0">المواد الدراسية المخصصة لك</p>
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

            <!-- فلترة وبحث -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="بحث..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="class_id" class="form-select">
                                <option value="">كل الصفوف</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }} @if($class->stage) - {{ $class->stage->name }} @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">بحث</button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('admin.my-subjects') }}" class="btn btn-secondary w-100">إعادة تعيين</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- عرض المواد -->
            @if($subjects->count() > 0)
                <div class="row">
                    @foreach($subjects as $subject)
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                @if($subject->image)
                                    <img src="{{ media_public_url($subject->image) }}" 
                                         class="card-img-top" 
                                         style="height: 200px; object-fit: cover;">
                                @else
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                         style="height: 200px;">
                                        <i class="fas fa-book fa-3x text-muted"></i>
                                    </div>
                                @endif
                                <div class="card-body">
                                    <h5 class="card-title">{{ $subject->name }}</h5>
                                    @if($subject->schoolClass)
                                        <p class="text-muted mb-2">
                                            <i class="bi bi-building me-1"></i>{{ $subject->schoolClass->name }}
                                            @if($subject->schoolClass->stage)
                                                - {{ $subject->schoolClass->stage->name }}
                                            @endif
                                        </p>
                                    @endif
                                    @if($subject->description)
                                        <p class="card-text text-muted small">
                                            {{ Str::limit($subject->description, 100) }}
                                        </p>
                                    @endif
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="badge bg-info">
                                            {{ $subject->sections()->count() }} قسم
                                        </span>
                                        <a href="{{ route('admin.my-subjects.show', $subject->id) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye me-1"></i> عرض التفاصيل
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $subjects->links() }}
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-book fa-3x text-muted mb-3"></i>
                        <p class="text-muted">لا توجد مواد مخصصة لك</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop
