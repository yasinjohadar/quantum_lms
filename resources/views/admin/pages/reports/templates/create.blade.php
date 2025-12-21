@extends('admin.layouts.master')

@section('page-title')
    إنشاء قالب تقرير جديد
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إنشاء قالب تقرير جديد</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">التقارير</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.report-templates.index') }}">القوالب</a></li>
                        <li class="breadcrumb-item active" aria-current="page">إنشاء قالب</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-xl-8 col-lg-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="mb-0">معلومات القالب</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.report-templates.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">اسم القالب <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">نوع التقرير <span class="text-danger">*</span></label>
                                <select name="type" class="form-select" required>
                                    <option value="">اختر النوع</option>
                                    <option value="student" {{ old('type') == 'student' ? 'selected' : '' }}>تقرير الطالب</option>
                                    <option value="course" {{ old('type') == 'course' ? 'selected' : '' }}>تقرير الكورس</option>
                                    <option value="system" {{ old('type') == 'system' ? 'selected' : '' }}>تقرير النظام</option>
                                </select>
                                @error('type')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">الوصف</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">الإعدادات (JSON)</label>
                                <textarea name="config" class="form-control" rows="5" placeholder='{"required_params": ["user_id"], "charts": ["progress"]}'>{{ old('config', '{}') }}</textarea>
                                <small class="text-muted">يمكنك إضافة إعدادات مخصصة بصيغة JSON</small>
                                @error('config')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">نشط</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_default" id="is_default" {{ old('is_default') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_default">افتراضي</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i>
                                    حفظ
                                </button>
                                <a href="{{ route('admin.report-templates.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-right me-1"></i>
                                    إلغاء
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

