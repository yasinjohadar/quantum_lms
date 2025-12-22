@extends('admin.layouts.master')

@section('page-title')
    إضافة تصنيف جديد
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="page-header d-flex justify-content-between align-items-center my-4">
            <h5 class="page-title mb-0">إضافة تصنيف جديد</h5>
            <a href="{{ route('admin.library.categories.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li class="small">{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.library.categories.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="اسم التصنيف" value="{{ old('name') }}" required>
                                <label>اسم التصنيف <span class="text-danger">*</span></label>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" placeholder="الرابط" value="{{ old('slug') }}">
                                <label>الرابط (سيتم توليده تلقائياً إذا تركت فارغاً)</label>
                                @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" placeholder="الوصف" style="height: 100px;">{{ old('description') }}</textarea>
                                <label>الوصف</label>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror" placeholder="الأيقونة (مثل: fe fe-folder)" value="{{ old('icon') }}">
                                <label>الأيقونة</label>
                                <small class="form-text text-muted">مثال: fe fe-folder, fas fa-book</small>
                                @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="color" name="color" class="form-control form-control-color @error('color') is-invalid @enderror" value="{{ old('color', '#007bff') }}">
                                <label>اللون</label>
                                @error('color')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="number" name="order" class="form-control @error('order') is-invalid @enderror" placeholder="الترتيب" value="{{ old('order', 0) }}" min="0">
                                <label>الترتيب</label>
                                @error('order')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">نشط</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> حفظ
                            </button>
                            <a href="{{ route('admin.library.categories.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> إلغاء
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

