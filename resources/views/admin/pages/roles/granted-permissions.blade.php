@extends('admin.layouts.master')

@section('page-title')
    صلاحيات الدور
@stop

@push('styles')
    @include('admin.pages.roles.partials.role-form-styles')
    <style>
        .role-perm-readonly-item {
            padding: 0.65rem 0.85rem;
            border-radius: 10px;
            border: 1px solid var(--role-form-border, #e9ecef);
            background: var(--role-form-soft, rgba(13, 110, 253, 0.06));
            height: 100%;
        }

        .role-perm-readonly-item__name {
            font-weight: 600;
            font-size: 0.875rem;
            word-break: break-word;
        }

        .role-perm-readonly-item__desc {
            font-size: 0.8rem;
            line-height: 1.45;
        }
    </style>
@endpush

@section('content')
    <div class="main-content app-content role-form-page">
        <div class="container-fluid">

            <div class="role-form-hero my-4">
                <div class="role-form-hero__icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div class="role-form-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">الأدوار</a></li>
                            <li class="breadcrumb-item active" aria-current="page">صلاحيات الدور</li>
                        </ol>
                    </nav>
                    <h4 class="role-form-hero__title">الصلاحيات الممنوحة</h4>
                    <p class="role-form-hero__subtitle">{{ $role->name }}</p>
                </div>
                <div class="role-form-hero__stat">
                    <span class="role-form-hero__stat-value">{{ number_format($grantedPermissions->count()) }}</span>
                    <span class="role-form-hero__stat-label">صلاحية ممنوحة</span>
                </div>
            </div>

            <div class="role-form-card">
                <div class="role-form-card__header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <span class="role-form-card__header-icon"><i class="bi bi-key"></i></span>
                        الصلاحيات الممنوحة
                    </div>
                    @can('role-edit')
                        <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i> تعديل الصلاحيات
                        </a>
                    @endcan
                </div>
                <div class="role-form-card__body">
                    @if ($grantedPermissions->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-shield-x display-6 d-block mb-3"></i>
                            <p class="mb-0 fw-semibold">لا توجد صلاحيات ممنوحة لهذا الدور.</p>
                            @can('role-edit')
                                <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary btn-sm mt-3">
                                    <i class="bi bi-plus-lg me-1"></i> تعيين صلاحيات
                                </a>
                            @endcan
                        </div>
                    @else
                        <div class="role-perm-search-wrap mb-3">
                            <i class="bi bi-search"></i>
                            <input type="text"
                                   id="permissionSearch"
                                   class="form-control"
                                   placeholder="بحث في الصلاحيات الممنوحة (بالاسم أو الوصف)...">
                        </div>

                        @include('admin.pages.roles.partials.permission-categories-tabs-readonly', [
                            'permissionTabs' => $permissionTabs,
                        ])
                    @endif
                </div>
            </div>

        </div>
    </div>
@stop

@section('js')
    @include('admin.pages.roles.partials.granted-permissions-scripts')
@stop

@push('header-actions')
<div class="header-element">
    <div class="d-flex gap-2">
        <a href="{{ route('roles.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-right me-1"></i> العودة للأدوار
        </a>
        @can('role-edit')
            <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-pencil me-1"></i> تعديل الصلاحيات
            </a>
        @endcan
    </div>
</div>
@endpush
