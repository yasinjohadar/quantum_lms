@extends('admin.layouts.master')

@section('page-title')
    إضافة عملة جديدة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إضافة عملة جديدة</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.currencies.index') }}">العملات</a></li>
                        <li class="breadcrumb-item active">إضافة جديدة</li>
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
                        <form action="{{ route('admin.currencies.store') }}" method="POST">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="code" class="form-label">رمز العملة (ISO) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}" maxlength="3" required placeholder="مثال: SYP, USD, TRY">
                                <small class="text-muted">رمز العملة الدولي (3 أحرف)</small>
                                @error('code')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">اسم العملة <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required placeholder="مثال: ليرة سورية">
                                @error('name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="symbol" class="form-label">رمز العملة <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="symbol" name="symbol" value="{{ old('symbol') }}" required placeholder="مثال: ل.س, $, ₺">
                                @error('symbol')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_default">
                                        العملة الافتراضية
                                    </label>
                                    <small class="text-muted d-block">سيتم إلغاء الافتراضية من العملات الأخرى تلقائياً</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        نشط
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="order" class="form-label">الترتيب</label>
                                <input type="number" class="form-control" id="order" name="order" value="{{ old('order', 0) }}" min="0">
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
