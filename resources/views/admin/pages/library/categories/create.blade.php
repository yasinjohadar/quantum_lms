@extends('admin.layouts.master')

@section('page-title')
    إضافة تصنيف مكتبة
@stop

@push('styles')
    @include('admin.pages.library.partials.library-styles')
@endpush

@section('content')
    <div class="main-content app-content library-form-page">
        <div class="container-fluid">

            <div class="library-form-hero my-4">
                <div class="library-form-hero__icon">
                    <i class="bi bi-tag-fill"></i>
                </div>
                <div class="library-form-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.library.categories.index') }}">تصنيفات المكتبة</a></li>
                            <li class="breadcrumb-item active" aria-current="page">إضافة تصنيف</li>
                        </ol>
                    </nav>
                    <h4 class="library-form-hero__title">إضافة تصنيف مكتبة جديد</h4>
                    <p class="library-form-hero__subtitle">الاسم، الأيقونة، اللون، وترتيب الظهور</p>
                </div>
                <div class="library-form-hero__actions">
                    <a href="{{ route('admin.library.categories.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i> رجوع للقائمة
                    </a>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>يرجى تصحيح الأخطاء التالية:</strong>
                    <ul class="mb-0 mt-2 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.library.categories.store') }}">
                @csrf

                <div class="library-form-card">
                    <div class="library-form-card__header">
                        <span class="library-form-card__header-icon"><i class="bi bi-info-circle"></i></span>
                        <div>
                            <div class="library-form-card__title">بيانات التصنيف</div>
                            <p class="library-form-card__desc">الاسم، الوصف، الأيقونة، واللون المستخدم في القوائم</p>
                        </div>
                    </div>
                    <div class="library-form-card__body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="library-form-field">
                                    <label class="form-label">اسم التصنيف <span class="text-danger">*</span></label>
                                    <input type="text" name="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           placeholder="مثال: أوراق العمل"
                                           value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="library-form-field">
                                    <label class="form-label">الرابط الدائم</label>
                                    <input type="text" name="slug"
                                           class="form-control @error('slug') is-invalid @enderror"
                                           placeholder="يُولَّد تلقائياً إن تُرك فارغاً"
                                           value="{{ old('slug') }}">
                                    @error('slug')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="library-form-field">
                                    <label class="form-label">الوصف</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                              rows="3" placeholder="وصف مختصر (اختياري)">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="library-form-field">
                                    <label class="form-label">الأيقونة</label>
                                    <input type="text" name="icon"
                                           class="form-control @error('icon') is-invalid @enderror"
                                           placeholder="bi bi-folder"
                                           value="{{ old('icon') }}">
                                    @error('icon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="library-hint">
                                        <i class="bi bi-lightbulb"></i>
                                        <span>اسم صنف أيقونة Bootstrap Icons، مثل bi bi-folder.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="library-form-field">
                                    <label class="form-label">اللون</label>
                                    <input type="color" name="color"
                                           class="form-control form-control-color @error('color') is-invalid @enderror"
                                           value="{{ old('color', '#0d8f7a') }}">
                                    @error('color')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="library-form-field">
                                    <label class="form-label">الترتيب</label>
                                    <input type="number" name="order"
                                           class="form-control @error('order') is-invalid @enderror"
                                           value="{{ old('order', 0) }}">
                                    @error('order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active"
                                           id="is_active" value="1"
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">التصنيف نشط</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ route('admin.library.categories.index') }}" class="btn btn-outline-secondary px-4 me-2">إلغاء</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> حفظ التصنيف
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop
