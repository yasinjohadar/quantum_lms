@extends('admin.layouts.master')

@section('page-title')
    تعديل الدور للمستخدم
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
                {{-- <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1"> المستخدمين</h5>

                </div> --}}


            </div>
            <!-- Page Header Close -->



            <!-- Start::row-1 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card p-3">




                            <form method="POST" action="{{ route('roles.update', 'test') }}">
                                @csrf
                                @method('PUT')
                                <div class="row">

                                    <div class="mb-3 col">
                                        <label class="form-label">اسم الروول</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ $role->name }}">
                                    </div>
                                </div>


                                <div class="mb-4">
                                    <label class="form-label fw-bold d-block mb-3">الصلاحيات:</label>
                                    
                                    @foreach($categorizedPermissions as $categoryName => $categoryPermissions)
                                        @if($categoryPermissions->isNotEmpty())
                                            <div class="card mb-3">
                                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0 fw-bold">{{ $categoryName }}</h6>
                                                    <div>
                                                        <button type="button" class="btn btn-sm btn-link p-0 select-all-category" data-category="{{ $loop->index }}">
                                                            تحديد الكل
                                                        </button>
                                                        <span class="mx-2">|</span>
                                                        <button type="button" class="btn btn-sm btn-link p-0 deselect-all-category" data-category="{{ $loop->index }}">
                                                            إلغاء تحديد الكل
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        @foreach($categoryPermissions as $permission)
                                                            <div class="col-md-6 col-lg-4 mb-3">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                           name="permissions[{{ $permission->name }}]"
                                                                           value="{{ $permission->name }}"
                                                                           id="perm_{{ $permission->id }}"
                                                                           {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                                        <span class="fw-semibold">{{ $permission->name }}</span>
                                                                        @if($permission->description)
                                                                            <br>
                                                                            <small class="text-muted">{{ $permission->description }}</small>
                                                                        @endif
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                <input type="hidden" value="{{ $role->id }}" name="id">

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">إغلاق</button>
                                    <button type="submit" class="btn btn-primary">تعديل بيانات الرول</button>
                                </div>

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
<script>
document.addEventListener('DOMContentLoaded', function() {
    // تحديد جميع الصلاحيات في فئة
    document.querySelectorAll('.select-all-category').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoryCard = this.closest('.card');
            categoryCard.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = true;
            });
        });
    });

    // إلغاء تحديد جميع الصلاحيات في فئة
    document.querySelectorAll('.deselect-all-category').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoryCard = this.closest('.card');
            categoryCard.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
        });
    });
});
</script>
@stop
