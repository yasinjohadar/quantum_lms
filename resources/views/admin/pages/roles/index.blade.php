@extends('admin.layouts.master')

@section('page-title')
    الأدوار
@stop

@push('styles')
    @include('admin.pages.roles.partials.index-styles')
@endpush

@section('content')
    <div class="main-content app-content roles-page">
        <div class="container-fluid">

            <div class="roles-hero my-4">
                <div class="roles-hero__icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div class="roles-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">الأدوار</li>
                        </ol>
                    </nav>
                    <h4 class="roles-hero__title">إدارة الأدوار</h4>
                    <p class="roles-hero__subtitle">تعريف الأدوار، نوع الواجهة، وربط الصلاحيات</p>
                </div>
                <div class="roles-hero__stat">
                    <span class="roles-hero__stat-value">{{ number_format($roles->count()) }}</span>
                    <span class="roles-hero__stat-label">دور</span>
                </div>
                <div class="roles-hero__actions">
                    @can('role-create')
                        <a class="btn btn-sm btn-primary" href="{{ route('roles.create') }}">
                            <i class="bi bi-plus-lg me-1"></i> إضافة دور جديد
                        </a>
                    @endcan
                </div>
            </div>

            @if (\Session::has('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>{!! \Session::get('success') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (\Session::has('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i>{!! \Session::get('error') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="roles-card">
                <div class="roles-card__header">
                    <div class="roles-card__header-left">
                        <span class="roles-card__header-icon"><i class="bi bi-table"></i></span>
                        جدول الأدوار
                    </div>
                </div>
                <div class="roles-card__body">
                    <div class="roles-table-wrap">
                        <div class="table-responsive">
                            <table class="table roles-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 48px;">#</th>
                                        <th scope="col">اسم الدور</th>
                                        <th scope="col" style="width: 160px;">نوع الواجهة</th>
                                        <th scope="col" style="width: 120px;">العمليات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @include('admin.pages.roles.partials.table-rows', ['roles' => $roles])
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop
