@extends('admin.layouts.master')

@section('page-title')
    إعدادات تصحيح الأسئلة المقالية بالAI
@stop

@section('css')
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إعدادات تصحيح الأسئلة المقالية بالAI</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.ai.settings.index') }}">إعدادات AI</a></li>
                        <li class="breadcrumb-item active">إعدادات التصحيح</li>
                    </ol>
                </nav>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('admin.ai.settings.grading.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card custom-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-robot me-2"></i>إعدادات التصحيح التلقائي</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="ai_essay_grading_enabled" 
                                       name="ai_essay_grading_enabled" value="1" 
                                       {{ $settings['ai_essay_grading_enabled'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="ai_essay_grading_enabled">
                                    <strong>تفعيل التصحيح التلقائي للأسئلة المقالية</strong>
                                </label>
                                <small class="form-text text-muted d-block">
                                    عند التفعيل، يمكن استخدام AI لتصحيح الأسئلة المقالية تلقائياً
                                </small>
                            </div>
                        </div>

                        <div class="col-md-12 mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="ai_essay_auto_grade" 
                                       name="ai_essay_auto_grade" value="1" 
                                       {{ $settings['ai_essay_auto_grade'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="ai_essay_auto_grade">
                                    <strong>تصحيح تلقائي عند الإجابة</strong>
                                </label>
                                <small class="form-text text-muted d-block">
                                    عند التفعيل، سيتم تصحيح الأسئلة المقالية تلقائياً فور إجابة الطالب
                                </small>
                            </div>
                        </div>

                        <div class="col-md-12 mb-4">
                            <label class="form-label">الموديل الافتراضي للتصحيح</label>
                            <select class="form-select" name="ai_essay_grading_model_id" id="ai_essay_grading_model_id">
                                <option value="">اختر الموديل...</option>
                                @foreach($models as $model)
                                    <option value="{{ $model->id }}" 
                                        {{ $settings['ai_essay_grading_model_id'] == $model->id ? 'selected' : '' }}>
                                        {{ $model->name }} ({{ $model->provider }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                الموديل الذي سيُستخدم للتصحيح التلقائي (إذا لم يتم تحديده، سيتم اختيار أفضل موديل متاح)
                            </small>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> حفظ الإعدادات
                    </button>
                    <a href="{{ route('admin.ai.settings.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-right me-1"></i> رجوع
                    </a>
                </div>
            </div>
        </form>

        <div class="card custom-card mt-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>معلومات إضافية</h6>
            </div>
            <div class="card-body">
                <h6>كيف يعمل التصحيح التلقائي؟</h6>
                <ul>
                    <li>عند تفعيل التصحيح التلقائي، يمكن للمعلمين استخدام AI لتصحيح الأسئلة المقالية</li>
                    <li>يمكن التصحيح من صفحة تصحيح المحاولة أو تلقائياً عند الإجابة (إذا كان مفعلاً)</li>
                    <li>AI يقيم الإجابة بناءً على معايير محددة: المحتوى، التنظيم، اللغة، الإبداع، التفصيل</li>
                    <li>يمكن للمعلم مراجعة وتعديل الدرجة المقترحة من AI</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
@stop




