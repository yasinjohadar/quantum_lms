@extends('admin.layouts.master')

@section('page-title')
    تعديل مرحلة دراسية
@stop

@section('css')
@stop

@section('content')
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

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header d-flex justify-content-between align-items-center my-4">
                <h5 class="page-title mb-0">تعديل المرحلة: {{ $stage->name }}</h5>
                <a href="{{ route('admin.stages.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.stages.update', $stage->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">البيانات الأساسية</h6>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           placeholder="اسم المرحلة"
                                           value="{{ old('name', $stage->name) }}" required>
                                    <label>اسم المرحلة <span class="text-danger">*</span></label>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="slug"
                                           class="form-control @error('slug') is-invalid @enderror"
                                           placeholder="الرابط الدائم"
                                           value="{{ old('slug', $stage->slug) }}">
                                    <label>الرابط الدائم (اختياري)</label>
                                    @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-floating">
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                              placeholder="وصف المرحلة" style="height: 100px">{{ old('description', $stage->description) }}</textarea>
                                    <label>وصف المرحلة (اختياري)</label>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">صورة المرحلة (اختياري)</label>
                                @if ($stage->image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $stage->image) }}" alt="{{ $stage->name }}"
                                             class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                                    </div>
                                @endif
                                <input type="file" name="image"
                                       class="form-control @error('image') is-invalid @enderror"
                                       accept="image/*">
                                @error('image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">صورة Open Graph (اختياري)</label>
                                @if ($stage->og_image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $stage->og_image) }}" alt="{{ $stage->name }}"
                                             class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                                    </div>
                                @endif
                                <input type="file" name="og_image"
                                       class="form-control @error('og_image') is-invalid @enderror"
                                       accept="image/*">
                                @error('og_image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="number" name="order"
                                           class="form-control @error('order') is-invalid @enderror"
                                           placeholder="الترتيب"
                                           value="{{ old('order', $stage->order) }}">
                                    <label>ترتيب العرض</label>
                                    @error('order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4 d-flex align-items-center">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" name="is_active"
                                           id="is_active" value="1"
                                           {{ old('is_active', $stage->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">المرحلة نشطة</label>
                                </div>
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-3">إعدادات الـ SEO (اختيارية)</h6>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="meta_title"
                                           class="form-control @error('meta_title') is-invalid @enderror"
                                           placeholder="عنوان الميتا"
                                           value="{{ old('meta_title', $stage->meta_title) }}">
                                    <label>Meta Title</label>
                                    @error('meta_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="meta_keywords"
                                           class="form-control @error('meta_keywords') is-invalid @enderror"
                                           placeholder="الكلمات المفتاحية"
                                           value="{{ old('meta_keywords', $stage->meta_keywords) }}">
                                    <label>Meta Keywords</label>
                                    @error('meta_keywords')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-floating">
                                    <textarea name="meta_description" class="form-control @error('meta_description') is-invalid @enderror"
                                              placeholder="وصف الميتا" style="height: 90px">{{ old('meta_description', $stage->meta_description) }}</textarea>
                                    <label>Meta Description</label>
                                    @error('meta_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="{{ route('admin.stages.index') }}" class="btn btn-secondary px-4 me-2">
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> حفظ التعديلات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop


