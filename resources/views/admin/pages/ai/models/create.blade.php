@extends('admin.layouts.master')

@section('page-title')
    إضافة موديل AI جديد
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إضافة موديل AI جديد</h5>
            </div>
            <div>
                <a href="{{ route('admin.ai.models.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form action="{{ route('admin.ai.models.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">اسم الموديل <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="provider" class="form-label">المزود <span class="text-danger">*</span></label>
                                    <select class="form-select" id="provider" name="provider" required>
                                        <option value="">اختر المزود</option>
                                        @foreach($providers as $key => $label)
                                            <option value="{{ $key }}" {{ old('provider') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="model_key" class="form-label">معرف الموديل <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="model_key" name="model_key" value="{{ old('model_key') }}" required>
                                    <small class="text-muted">مثال: gpt-4, claude-3-opus, gemini-pro</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="api_key" class="form-label">مفتاح API</label>
                                <input type="password" class="form-control" id="api_key" name="api_key" value="{{ old('api_key') }}">
                                <small class="text-muted">سيتم تشفيره تلقائياً</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="base_url" class="form-label">Base URL (للموديلات المحلية)</label>
                                    <input type="url" class="form-control" id="base_url" name="base_url" value="{{ old('base_url') }}" placeholder="http://localhost:11434">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="api_endpoint" class="form-label">API Endpoint</label>
                                    <input type="text" class="form-control" id="api_endpoint" name="api_endpoint" value="{{ old('api_endpoint') }}" placeholder="/api/chat">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="max_tokens" class="form-label">الحد الأقصى للـ Tokens <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="max_tokens" name="max_tokens" value="{{ old('max_tokens', 2000) }}" min="1" max="100000" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="temperature" class="form-label">Temperature <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="temperature" name="temperature" value="{{ old('temperature', 0.7) }}" step="0.1" min="0" max="2" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="priority" class="form-label">الأولوية</label>
                                    <input type="number" class="form-control" id="priority" name="priority" value="{{ old('priority', 0) }}" min="0">
                                    <small class="text-muted">كلما زاد الرقم، زادت الأولوية</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="cost_per_1k_tokens" class="form-label">التكلفة لكل 1000 Token</label>
                                    <input type="number" class="form-control" id="cost_per_1k_tokens" name="cost_per_1k_tokens" value="{{ old('cost_per_1k_tokens') }}" step="0.000001" min="0">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">القدرات <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2 flex-wrap">
                                    @foreach($capabilities as $key => $label)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="capabilities[]" value="{{ $key }}" id="cap_{{ $key }}" {{ in_array($key, old('capabilities', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="cap_{{ $key }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">نشط</label>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_default">افتراضي</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> حفظ
                                </button>
                                <a href="{{ route('admin.ai.models.index') }}" class="btn btn-secondary">
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

