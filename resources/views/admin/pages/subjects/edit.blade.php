@extends('admin.layouts.master')

@section('page-title')
    تعديل مادة دراسية
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
                <h5 class="page-title mb-0">تعديل المادة: {{ $subject->name }}</h5>
                <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.subjects.update', $subject->id) }}" enctype="multipart/form-data">
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
                                           placeholder="اسم المادة"
                                           value="{{ old('name', $subject->name) }}" required>
                                    <label>اسم المادة <span class="text-danger">*</span></label>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select name="class_id"
                                            class="form-select @error('class_id') is-invalid @enderror"
                                            aria-label="الصف الدراسي" required>
                                        <option value="">اختر الصف</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}"
                                                {{ old('class_id', $subject->class_id) == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }} - {{ $class->stage?->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label>الصف الدراسي <span class="text-danger">*</span></label>
                                    @error('class_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="slug"
                                           class="form-control @error('slug') is-invalid @enderror"
                                           placeholder="الرابط الدائم"
                                           value="{{ old('slug', $subject->slug) }}">
                                    <label>الرابط الدائم (اختياري)</label>
                                    @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="number" name="order"
                                           class="form-control @error('order') is-invalid @enderror"
                                           placeholder="الترتيب"
                                           value="{{ old('order', $subject->order) }}">
                                    <label>ترتيب العرض</label>
                                    @error('order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-floating">
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                              placeholder="وصف المادة" style="height: 100px">{{ old('description', $subject->description) }}</textarea>
                                    <label>وصف المادة (اختياري)</label>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">صورة المادة (اختياري)</label>
                                @if ($subject->image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/'.$subject->image) }}" alt="{{ $subject->name }}"
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
                                @if ($subject->og_image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/'.$subject->og_image) }}" alt="{{ $subject->name }}"
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

                            <div class="col-md-4 d-flex align-items-center">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" name="is_active"
                                           id="is_active" value="1"
                                        {{ old('is_active', $subject->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">المادة نشطة</label>
                                </div>
                            </div>

                            <div class="col-md-4 d-flex align-items-center">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" name="display_in_class"
                                           id="display_in_class" value="1"
                                        {{ old('display_in_class', $subject->display_in_class) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="display_in_class">عرض في صفحة الصف</label>
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
                                           value="{{ old('meta_title', $subject->meta_title) }}">
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
                                           value="{{ old('meta_keywords', $subject->meta_keywords) }}">
                                    <label>Meta Keywords</label>
                                    @error('meta_keywords')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-floating">
                                    <textarea name="meta_description" class="form-control @error('meta_description') is-invalid @enderror"
                                              placeholder="وصف الميتا" style="height: 90px">{{ old('meta_description', $subject->meta_description) }}</textarea>
                                    <label>Meta Description</label>
                                    @error('meta_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary px-4 me-2">
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

