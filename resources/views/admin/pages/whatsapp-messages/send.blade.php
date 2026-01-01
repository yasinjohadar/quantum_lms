@extends('admin.layouts.master')

@section('page-title')
    إرسال رسالة WhatsApp
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إرسال رسالة WhatsApp</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.whatsapp-messages.index') }}">رسائل WhatsApp</a></li>
                        <li class="breadcrumb-item active">إرسال رسالة</li>
                    </ol>
                </nav>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

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
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header">
                        <h5 class="card-title mb-0">إرسال رسالة جديدة</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.whatsapp-messages.send') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="to" class="form-label">رقم الهاتف <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('to') is-invalid @enderror" id="to" name="to" value="{{ old('to') }}" placeholder="+905519665883" pattern="^\+[1-9]\d{1,14}$" required>
                                <small class="text-muted">يجب أن يبدأ بـ + متبوعاً برمز الدولة</small>
                                @error('to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="type" class="form-label">نوع الرسالة <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="text" {{ old('type') == 'text' ? 'selected' : '' }}>نص</option>
                                    <option value="template" {{ old('type') == 'template' ? 'selected' : '' }}>قالب</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3" id="message-field">
                                <label for="message" class="form-label">الرسالة <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="5" required>{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3" id="template-fields" style="display: none;">
                                <div class="mb-3">
                                    <label for="template_name" class="form-label">اسم القالب <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('template_name') is-invalid @enderror" id="template_name" name="template_name" value="{{ old('template_name') }}" placeholder="اسم القالب المعتمد في Meta">
                                    <small class="text-muted">يجب أن يكون القالب معتمداً في Meta Business Account</small>
                                    @error('template_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="language" class="form-label">رمز اللغة <span class="text-danger">*</span></label>
                                    <select class="form-select @error('language') is-invalid @enderror" id="language" name="language">
                                        <option value="ar" {{ old('language', 'ar') == 'ar' ? 'selected' : '' }}>العربية (ar)</option>
                                        <option value="en" {{ old('language') == 'en' ? 'selected' : '' }}>الإنجليزية (en)</option>
                                        <option value="fr" {{ old('language') == 'fr' ? 'selected' : '' }}>الفرنسية (fr)</option>
                                        <option value="es" {{ old('language') == 'es' ? 'selected' : '' }}>الإسبانية (es)</option>
                                    </select>
                                    @error('language')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> إرسال
                                </button>
                                <a href="{{ route('admin.whatsapp-messages.index') }}" class="btn btn-secondary">إلغاء</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const messageField = document.getElementById('message-field');
    const templateFields = document.getElementById('template-fields');
    const messageInput = document.getElementById('message');
    const templateNameInput = document.getElementById('template_name');
    const languageInput = document.getElementById('language');

    function toggleFields() {
        if (typeSelect.value === 'template') {
            messageField.style.display = 'none';
            templateFields.style.display = 'block';
            messageInput.removeAttribute('required');
            templateNameInput.setAttribute('required', 'required');
            languageInput.setAttribute('required', 'required');
        } else {
            messageField.style.display = 'block';
            templateFields.style.display = 'none';
            messageInput.setAttribute('required', 'required');
            templateNameInput.removeAttribute('required');
            languageInput.removeAttribute('required');
        }
    }

    typeSelect.addEventListener('change', toggleFields);
    toggleFields();
});
</script>
@stop

