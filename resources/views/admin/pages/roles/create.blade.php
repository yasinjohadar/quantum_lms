@extends('admin.layouts.master')

@section('page-title')
    إنشاء دور جديد
@stop

@push('styles')
    @include('admin.pages.roles.partials.role-form-styles')
@endpush

@section('content')
    <div class="main-content app-content role-form-page">
        <div class="container-fluid">

            <div class="role-form-hero my-4">
                <div class="role-form-hero__icon">
                    <i class="bi bi-shield-plus"></i>
                </div>
                <div class="role-form-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">الأدوار</a></li>
                            <li class="breadcrumb-item active" aria-current="page">إنشاء دور</li>
                        </ol>
                    </nav>
                    <h4 class="role-form-hero__title">إنشاء دور جديد</h4>
                    <p class="role-form-hero__subtitle">حدّد بيانات الدور واختر الصلاحيات المناسبة</p>
                </div>
                <div class="role-form-hero__stat">
                    <span class="role-form-hero__stat-value" id="role-form-selected-count">0</span>
                    <span class="role-form-hero__stat-label">صلاحية محددة</span>
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

            <form id="role-create-form" method="POST" action="{{ route('roles.store') }}" data-role-permissions-form>
                @csrf

                <div class="role-form-card">
                    <div class="role-form-card__header">
                        <span class="role-form-card__header-icon"><i class="bi bi-person-badge"></i></span>
                        بيانات الدور
                    </div>
                    <div class="role-form-card__body">
                        @include('admin.pages.roles.partials.role-form-fields')
                    </div>
                </div>

                <div class="role-form-card">
                    <div class="role-form-card__header">
                        <span class="role-form-card__header-icon"><i class="bi bi-key"></i></span>
                        الصلاحيات
                    </div>
                    <div class="role-form-card__body">
                        <div class="role-perm-search-wrap mb-3">
                            <i class="bi bi-search"></i>
                            <input type="text"
                                   id="permissionSearch"
                                   class="form-control"
                                   placeholder="بحث في الصلاحيات (بالاسم أو الوصف)...">
                        </div>

                        @include('admin.pages.roles.partials.permission-selection-summary')

                        @include('admin.pages.roles.partials.permission-categories-tabs', [
                            'permissionTabs' => $permissionTabs,
                        ])
                    </div>
                </div>
            </form>

        </div>
    </div>
@stop

@section('js')
    @include('admin.pages.roles.partials.permission-selection-summary-scripts')
@stop

@push('header-actions')
<div class="header-element">
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.history.back()">
            <i class="bi bi-x-lg me-1"></i> إغلاق
        </button>
        <button type="submit" form="role-create-form" class="btn btn-sm btn-primary">
            <i class="bi bi-check-lg me-1"></i> حفظ الدور
        </button>
    </div>
</div>
@endpush
