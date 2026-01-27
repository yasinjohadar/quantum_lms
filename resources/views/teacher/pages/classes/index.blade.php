@extends('admin.layouts.master')

@section('page-title')
    صفوفي المخصصة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">صفوفي المخصصة</h5>
                    <p class="text-muted mb-0">الصفوف الدراسية المخصصة لك</p>
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
                            <select name="stage_id" class="form-select">
                                <option value="">كل المراحل</option>
                                @foreach($stages as $stage)
                                    <option value="{{ $stage->id }}" {{ request('stage_id') == $stage->id ? 'selected' : '' }}>
                                        {{ $stage->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">بحث</button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('teacher.classes.index') }}" class="btn btn-secondary w-100">إعادة تعيين</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- عرض الصفوف -->
            @if($classes->count() > 0)
                <div class="row">
                    @foreach($classes as $class)
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                @if($class->image)
                                    <img src="{{ asset('storage/' . $class->image) }}" 
                                         class="card-img-top" 
                                         style="height: 200px; object-fit: cover;">
                                @else
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                         style="height: 200px;">
                                        <i class="fas fa-building fa-3x text-muted"></i>
                                    </div>
                                @endif
                                <div class="card-body">
                                    <h5 class="card-title">{{ $class->name }}</h5>
                                    @if($class->stage)
                                        <p class="text-muted mb-2">
                                            <i class="bi bi-bookmark me-1"></i>{{ $class->stage->name }}
                                        </p>
                                    @endif
                                    @if($class->description)
                                        <p class="card-text text-muted small">
                                            {{ Str::limit($class->description, 100) }}
                                        </p>
                                    @endif
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="badge bg-primary">
                                            {{ $class->subjects()->count() }} مادة
                                        </span>
                                        <a href="{{ route('teacher.classes.show', $class->id) }}" 
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
                    {{ $classes->links() }}
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-building fa-3x text-muted mb-3"></i>
                        <p class="text-muted">لا توجد صفوف مخصصة لك</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop
