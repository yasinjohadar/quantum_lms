@extends('admin.layouts.master')

@section('page-title')
    تعديل عملة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تعديل عملة: {{ $currency->name }}</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.currencies.index') }}">العملات</a></li>
                        <li class="breadcrumb-item active">تعديل</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8">
                <div class="card custom-card">
                    <div class="card-header">
                        <h6 class="mb-0">معلومات العملة</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.currencies.update', $currency->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-3">
                                <label for="code" class="form-label">رمز العملة (ISO) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="code" name="code" value="{{ old('code', $currency->code) }}" maxlength="3" required>
                                @error('code')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">اسم العملة <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $currency->name) }}" required>
                                @error('name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="symbol" class="form-label">رمز العملة <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="symbol" name="symbol" value="{{ old('symbol', $currency->symbol) }}" required>
                                @error('symbol')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default', $currency->is_default) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_default">
                                        العملة الافتراضية
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $currency->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        نشط
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="order" class="form-label">الترتيب</label>
                                <input type="number" class="form-control" id="order" name="order" value="{{ old('order', $currency->order) }}" min="0">
                                @error('order')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>حفظ
                                </button>
                                <a href="{{ route('admin.currencies.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-2"></i>إلغاء
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
