@extends('admin.layouts.master')

@section('page-title')
    تعديل مكافأة
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تعديل مكافأة</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.rewards.index') }}">المكافآت</a></li>
                        <li class="breadcrumb-item active" aria-current="page">تعديل</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">معلومات المكافأة</div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.rewards.update', $reward) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الاسم <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $reward->name) }}" required>
                                    @error('name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">النوع <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required>
                                        <option value="certificate" {{ old('type', $reward->type) == 'certificate' ? 'selected' : '' }}>شهادة</option>
                                        <option value="discount" {{ old('type', $reward->type) == 'discount' ? 'selected' : '' }}>خصم</option>
                                        <option value="badge" {{ old('type', $reward->type) == 'badge' ? 'selected' : '' }}>شارة</option>
                                        <option value="points" {{ old('type', $reward->type) == 'points' ? 'selected' : '' }}>نقاط</option>
                                        <option value="access" {{ old('type', $reward->type) == 'access' ? 'selected' : '' }}>وصول</option>
                                    </select>
                                    @error('type')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">الوصف</label>
                                    <textarea name="description" class="form-control" rows="3">{{ old('description', $reward->description) }}</textarea>
                                    @error('description')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">تكلفة النقاط <span class="text-danger">*</span></label>
                                    <input type="number" name="points_cost" class="form-control" value="{{ old('points_cost', $reward->points_cost) }}" min="0" required>
                                    @error('points_cost')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الكمية المتاحة</label>
                                    <input type="number" name="quantity_available" class="form-control" value="{{ old('quantity_available', $reward->quantity_available) }}" min="0" placeholder="اتركه فارغاً للكمية غير المحدودة">
                                    @error('quantity_available')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $reward->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">نشط</label>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">حفظ</button>
                                <a href="{{ route('admin.rewards.index') }}" class="btn btn-secondary">إلغاء</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->
@stop

