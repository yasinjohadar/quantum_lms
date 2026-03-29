@extends('admin.layouts.master')

@section('page-title')
    تعديل الدور للمستخدم
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
                {{-- <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1"> المستخدمين</h5>

                </div> --}}


            </div>
            <!-- Page Header Close -->



            <!-- Start::row-1 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card p-3">




                            <form id="role-edit-form" method="POST" action="{{ route('roles.update', 'test') }}" data-role-permissions-form>
                                @csrf
                                @method('PUT')
                                <div class="row g-3 align-items-start">

                                    <div class="mb-3 col-12 col-lg-4">
                                        <label class="form-label">اسم الروول</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ $role->name }}">
                                    </div>
                                    <div class="mb-3 col-12 col-lg-4">
                                        <label class="form-label">نوع الواجهة</label>
                                        <select class="form-select" name="dashboard_type" required>
                                            <option value="admin" {{ ($role->dashboard_type ?? 'student') === 'admin' ? 'selected' : '' }}>لوحة تحكم الأدمن</option>
                                            <option value="student" {{ ($role->dashboard_type ?? 'student') === 'student' ? 'selected' : '' }}>لوحة تحكم الطالب</option>
                                        </select>
                                        <small class="text-muted">حدد نوع الواجهة التي يجب أن يصل إليها المستخدمون بهذا الدور</small>
                                    </div>
                                    @php
                                        $rolesTable = config('permission.table_names.roles', 'roles');
                                    @endphp
                                    @if(\Illuminate\Support\Facades\Schema::hasColumn($rolesTable, 'staff_profile'))
                                        <div class="mb-3 col-12 col-lg-4">
                                            <label class="form-label">تصنيف المشرف / المعلم</label>
                                            <select class="form-select" name="staff_profile" required>
                                                <option value="none" {{ ($role->staff_profile ?? 'none') === 'none' ? 'selected' : '' }}>لا شيء (طالب، أدمن، دور عام)</option>
                                                <option value="supervisor" {{ ($role->staff_profile ?? 'none') === 'supervisor' ? 'selected' : '' }}>مشرف</option>
                                                <option value="teacher" {{ ($role->staff_profile ?? 'none') === 'teacher' ? 'selected' : '' }}>معلم</option>
                                            </select>
                                            <small class="text-muted">يحدد ظهور حاملي هذا الدور في صفحات تخصيص المشرفين والمعلمين حتى لو غيّرت اسم الدور.</small>
                                        </div>
                                    @endif
                                </div>


                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <label class="form-label fw-bold mb-0">الصلاحيات:</label>
                                    </div>
                                    
                                    <!-- حقل البحث -->
                                    <div class="mb-3">
                                        <input type="text" 
                                               id="permissionSearch" 
                                               class="form-control" 
                                               placeholder="بحث في الصلاحيات (بالاسم أو الوصف)...">
                                    </div>

                                    @include('admin.pages.roles.partials.permission-selection-summary')

                                    @include('admin.pages.roles.partials.permission-categories-tabs', ['permissionTabs' => $permissionTabs, 'role' => $role])
                                </div>

                                <input type="hidden" value="{{ $role->id }}" name="id">

                            </form>




                    </div><!-- end card -->
                </div>
            </div>
            <!--End::row-1 -->

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
        <button type="submit" form="role-edit-form" class="btn btn-sm btn-primary">
            <i class="fe fe-save me-1"></i> تعديل بيانات الرول
        </button>
    </div>
</div>
@endpush
