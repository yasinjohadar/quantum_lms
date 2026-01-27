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




                            <form id="role-edit-form" method="POST" action="{{ route('roles.update', 'test') }}">
                                @csrf
                                @method('PUT')
                                <div class="row">

                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">اسم الروول</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ $role->name }}">
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">نوع الواجهة</label>
                                        <select class="form-select" name="dashboard_type" required>
                                            <option value="admin" {{ ($role->dashboard_type ?? 'student') === 'admin' ? 'selected' : '' }}>لوحة تحكم الأدمن</option>
                                            <option value="student" {{ ($role->dashboard_type ?? 'student') === 'student' ? 'selected' : '' }}>لوحة تحكم الطالب</option>
                                        </select>
                                        <small class="text-muted">حدد نوع الواجهة التي يجب أن يصل إليها المستخدمون بهذا الدور</small>
                                    </div>
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

    // البحث في الصلاحيات
    const searchInput = document.getElementById('permissionSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const categoryCards = document.querySelectorAll('.card.mb-3');
            
            categoryCards.forEach(card => {
                const cardText = card.textContent.toLowerCase();
                const hasMatch = cardText.includes(searchTerm);
                
                if (searchTerm === '') {
                    // إذا كان البحث فارغاً، إظهار جميع البطاقات
                    card.style.display = '';
                } else if (hasMatch) {
                    // إظهار البطاقة إذا كانت تحتوي على النص
                    card.style.display = '';
                    
                    // إخفاء الصلاحيات التي لا تطابق البحث داخل البطاقة
                    const permissionItems = card.querySelectorAll('.col-md-6.col-lg-4.mb-3');
                    permissionItems.forEach(item => {
                        const itemText = item.textContent.toLowerCase();
                        if (itemText.includes(searchTerm)) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                } else {
                    // إخفاء البطاقة بالكامل إذا لم تطابق البحث
                    card.style.display = 'none';
                }
            });
        });
    }
});
</script>
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
