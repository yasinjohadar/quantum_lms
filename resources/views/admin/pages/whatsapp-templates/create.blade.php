@extends('admin.layouts.master')

@section('page-title')
    إضافة قالب WhatsApp
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إضافة قالب WhatsApp جديد</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.whatsapp-templates.index') }}">قوالب WhatsApp</a></li>
                        <li class="breadcrumb-item active" aria-current="page">إضافة جديد</li>
                    </ol>
                </nav>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>حدث خطأ:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">معلومات القالب</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.whatsapp-templates.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">الاسم <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="slug" class="form-label">المعرف (Slug)</label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug') }}" placeholder="سيتم إنشاؤه تلقائياً من الاسم">
                                <small class="text-muted">سيتم إنشاؤه تلقائياً من الاسم إذا تركت فارغاً</small>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label">محتوى الرسالة <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="6" required>{{ old('content') }}</textarea>
                                <small class="text-muted">استخدم متغيرات بصيغة: <code>@{{student_name}}</code></small>
                                @error('content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">نشط</label>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> حفظ
                                </button>
                                <a href="{{ route('admin.whatsapp-templates.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> إلغاء
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header">
                        <h6 class="mb-0">المتغيرات المدعومة</h6>
                    </div>
                    <div class="card-body">
                        @foreach($supportedVariables as $variable)
                            <span class="badge bg-light text-dark border me-1 mb-1">{!! '&#123;&#123;' . e($variable) . '&#125;&#125;' !!}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
