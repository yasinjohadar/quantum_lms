@extends('admin.layouts.master')

@section('page-title')
    إنشاء دور جديد
@stop

@section('css')
@stop

@section('content')
    @if (\Session::has('success'))
        <div class="alert alert-success">
            <ul>
                <li>{!! \Session::get('success') !!}</li>
            </ul>
        </div>
    @endif

    @if (\Session::has('error'))
        <div class="alert alert-danger">
            <ul>
                <li>{!! \Session::get('error') !!}</li>
            </ul>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إنشاء دور جديد</h5>
                </div>
            </div>
            <!-- Page Header Close -->

            <!-- Start::row -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0 ">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">بيانات الدور</h6>
                        </div>
                        <div class="card-body">

                            <form id="role-create-form" method="POST" action="{{ route('roles.store') }}" data-role-permissions-form>
                                @csrf

                                <div class="row mb-3 g-3 align-items-start">
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-bold">اسم الدور</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="مثال: مشرف عام">
                                        @error('name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-bold">نوع الواجهة</label>
                                        <select class="form-select @error('dashboard_type') is-invalid @enderror" name="dashboard_type" required>
                                            <option value="admin" @selected(old('dashboard_type', 'admin') === 'admin')>لوحة تحكم الأدمن</option>
                                            <option value="student" @selected(old('dashboard_type', 'admin') === 'student')>لوحة تحكم الطالب</option>
                                        </select>
                                        @error('dashboard_type')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">حدد نوع الواجهة التي يجب أن يصل إليها المستخدمون بهذا الدور</small>
                                    </div>
                                    @php
                                        $rolesTable = config('permission.table_names.roles', 'roles');
                                    @endphp
                                    @if(\Illuminate\Support\Facades\Schema::hasColumn($rolesTable, 'staff_profile'))
                                        <div class="col-12 col-lg-4">
                                            <label class="form-label fw-bold">تصنيف المشرف / المعلم</label>
                                            <select class="form-select @error('staff_profile') is-invalid @enderror" name="staff_profile" required>
                                                <option value="none" @selected(old('staff_profile', 'none') === 'none')>لا شيء (طالب، أدمن، دور عام)</option>
                                                <option value="supervisor" @selected(old('staff_profile', 'none') === 'supervisor')>مشرف</option>
                                                <option value="teacher" @selected(old('staff_profile', 'none') === 'teacher')>معلم</option>
                                            </select>
                                            @error('staff_profile')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">يحدد ظهور حاملي هذا الدور في صفحات تخصيص المشرفين والمعلمين.</small>
                                        </div>
                                    @endif
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold d-block mb-3">الصلاحيات:</label>

                                    <div class="mb-3">
                                        <input type="text"
                                               id="permissionSearch"
                                               class="form-control"
                                               placeholder="بحث في الصلاحيات (بالاسم أو الوصف)...">
                                    </div>

                                    @include('admin.pages.roles.partials.permission-selection-summary')

                                    @include('admin.pages.roles.partials.permission-categories-tabs', ['permissionTabs' => $permissionTabs])
                                </div>

                            </form>

                        </div>
                    </div>
                </div>
            </div>
            <!--End::row-->

        </div>
    </div>
    <!-- End::app-content -->
@stop

@section('js')
    @include('admin.pages.roles.partials.permission-selection-summary-scripts')
@stop

@push('header-actions')
<div class="header-element">
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-danger" onclick="window.history.back()">
            <i class="fe fe-x me-1"></i> إغلاق
        </button>
        <button type="submit" form="role-create-form" class="btn btn-sm btn-primary">
            <i class="fe fe-save me-1"></i> حفظ الدور
        </button>
    </div>
</div>
@endpush
