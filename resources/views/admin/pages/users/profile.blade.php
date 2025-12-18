@extends('admin.layouts.master')

@section('page-title')
    ملف المستخدم
@stop

@section('css')
@stop

@section('content')
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

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li class="small">{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header d-flex justify-content-between align-items-center my-4">
                <h5 class="page-title mb-0">ملف المستخدم: {{ $user->name }}</h5>
                <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                </a>
            </div>

            <div class="row g-3">
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <img src="{{ $user->photo ? asset('storage/'.$user->photo) : asset('assets/images/faces/default-avatar.jpg') }}"
                                     alt="{{ $user->name }}"
                                     class="rounded-circle"
                                     style="width: 120px; height: 120px; object-fit: cover;">
                            </div>
                            <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                            @if($user->email)
                                <p class="mb-1">
                                    <a href="mailto:{{ $user->email }}" class="text-primary text-decoration-none">
                                        {{ $user->email }}
                                    </a>
                                </p>
                            @endif
                            @if($user->phone)
                                <p class="mb-1 text-muted">
                                    {{ $user->phone }}
                                </p>
                            @endif

                            <p class="mb-2">
                                @foreach($user->getRoleNames() as $role)
                                    <span class="badge bg-primary me-1">{{ $role }}</span>
                                @endforeach
                            </p>

                            <p class="mb-1">
                                @if($user->is_active)
                                    <span class="badge bg-success">حساب نشط</span>
                                @else
                                    <span class="badge bg-danger">حساب غير نشط</span>
                                @endif
                            </p>
                            <p class="text-muted small mb-0">
                                آخر دخول:
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'لا يوجد' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">معلومات الحساب</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted mb-1">الاسم الكامل</label>
                                    <p class="mb-0 fw-semibold">{{ $user->name }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted mb-1">تاريخ الإنشاء</label>
                                    <p class="mb-0 fw-semibold">{{ $user->created_at?->format('Y-m-d H:i') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted mb-1">تاريخ آخر تحديث</label>
                                    <p class="mb-0 fw-semibold">{{ $user->updated_at?->format('Y-m-d H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


