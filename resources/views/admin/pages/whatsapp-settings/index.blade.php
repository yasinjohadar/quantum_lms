@extends('admin.layouts.master')

@section('page-title')
    إعدادات WhatsApp
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إعدادات WhatsApp Cloud API</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">إعدادات WhatsApp</li>
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
            <div class="col-lg-10">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0 text-white">
                            <i class="fab fa-whatsapp me-2"></i>إعدادات WhatsApp Cloud API
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.whatsapp-settings.update') }}" method="POST" id="whatsapp-settings-form">
                            @csrf
                            @method('POST')

                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="whatsapp_enabled" name="whatsapp_enabled" value="1" {{ old('whatsapp_enabled', $settings['whatsapp_enabled'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="whatsapp_enabled">
                                        تفعيل WhatsApp
                                    </label>
                                </div>
                                <small class="text-muted">تفعيل أو تعطيل إرسال الرسائل عبر WhatsApp في النظام</small>
                            </div>

                            <hr class="my-4">

                            @php
                                $savedActiveProvider = $settings['whatsapp_provider'] ?? 'meta';
                                $activeProvider = old('whatsapp_provider', $savedActiveProvider);
                                $providerLabels = [
                                    'meta' => 'Meta WhatsApp Cloud API',
                                    'custom_api' => 'Custom API Provider',
                                    'flaxxa' => 'Flaxxa Wapi',
                                ];
                            @endphp

                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-plug me-2 text-primary"></i>اختيار المزود (Provider)
                            </h6>
                            <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
                                <i class="fas fa-check-circle"></i>
                                <span>
                                    المزوّد النشط حالياً على مستوى التطبيق: <strong>{{ $providerLabels[$savedActiveProvider] ?? $savedActiveProvider }}</strong>
                                    — كل رسائل التحقق ونسيان كلمة المرور والإرسال اليدوي تمر عبره الآن.
                                    هذا المؤشر لا يتغيّر بمجرد تصفح تبويب آخر، بل فقط عند الضغط على زر "تفعيل" داخل تبويب المزوّد المطلوب ثم حفظ الإعدادات.
                                </span>
                            </div>
                            <p class="text-muted small mb-3">
                                يمكنك تصفح إعدادات أي مزوّد من التبويبات أدناه، وتفعيل أحدها كمزوّد الإرسال الرئيسي عبر الزر الموجود داخل تبويبه (لا يتأثر المزوّد النشط بمجرد التصفح).
                            </p>

                            <ul class="nav nav-tabs whatsapp-provider-tabs mb-3" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $activeProvider === 'meta' ? 'active' : '' }}" id="tab-link-meta" data-bs-toggle="tab" data-bs-target="#tab-pane-meta" data-provider="meta" type="button" role="tab" aria-controls="tab-pane-meta">
                                        <i class="fas fa-info-circle me-1"></i>Meta WhatsApp Cloud API
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $activeProvider === 'custom_api' ? 'active' : '' }}" id="tab-link-custom_api" data-bs-toggle="tab" data-bs-target="#tab-pane-custom_api" data-provider="custom_api" type="button" role="tab" aria-controls="tab-pane-custom_api">
                                        <i class="fas fa-code me-1"></i>Custom API Provider
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $activeProvider === 'flaxxa' ? 'active' : '' }}" id="tab-link-flaxxa" data-bs-toggle="tab" data-bs-target="#tab-pane-flaxxa" data-provider="flaxxa" type="button" role="tab" aria-controls="tab-pane-flaxxa">
                                        <i class="fab fa-whatsapp me-1"></i>Flaxxa Wapi
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content whatsapp-provider-tab-content border border-top-0 rounded-bottom p-3 mb-4">
                            <div class="tab-pane fade {{ $activeProvider === 'meta' ? 'show active' : '' }}" id="tab-pane-meta" role="tabpanel">

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="whatsapp_provider" id="whatsapp_provider_meta" value="meta" {{ $activeProvider === 'meta' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="whatsapp_provider_meta">
                                    تفعيل Meta كمزوّد الإرسال الرئيسي
                                </label>
                            </div>

                            <h6 class="fw-bold mb-3" id="meta-settings-header">
                                <i class="fas fa-info-circle me-2 text-info"></i>معلومات حساب Meta
                            </h6>

                            <div id="meta-settings">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="api_version" class="form-label">إصدار API <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('api_version') is-invalid @enderror" id="api_version" name="api_version" value="{{ old('api_version', $settings['api_version'] ?? 'v20.0') }}">
                                    <small class="text-muted">مثال: v20.0</small>
                                    @error('api_version')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="phone_number_id" class="form-label">Phone Number ID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('phone_number_id') is-invalid @enderror" id="phone_number_id" name="phone_number_id" value="{{ old('phone_number_id', $settings['phone_number_id'] ?? '') }}" placeholder="123456789012345">
                                    <small class="text-muted">يمكنك العثور عليه في Meta App Dashboard</small>
                                    @error('phone_number_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="waba_id" class="form-label">WABA ID (اختياري)</label>
                                <input type="text" class="form-control @error('waba_id') is-invalid @enderror" id="waba_id" name="waba_id" value="{{ old('waba_id', $settings['waba_id'] ?? '') }}" placeholder="123456789012345">
                                <small class="text-muted">WhatsApp Business Account ID</small>
                                @error('waba_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-4">

                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-key me-2 text-warning"></i>معلومات المصادقة
                            </h6>

                            <div class="mb-3">
                                <label for="access_token" class="form-label">Access Token</label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('access_token') is-invalid @enderror" id="access_token" name="access_token" autocomplete="new-password" placeholder="اتركه فارغاً للاحتفاظ بالقيمة الحالية">
                                    <button class="btn btn-outline-secondary" type="button" id="toggle-access-token">
                                        <i class="fas fa-eye" id="toggle-access-token-icon"></i>
                                    </button>
                                </div>
                                <small class="text-muted">اتركه فارغاً للاحتفاظ بالـ Token الحالي. يمكنك الحصول عليه من Meta App Dashboard</small>
                                @error('access_token')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="verify_token" class="form-label">Verify Token <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('verify_token') is-invalid @enderror" id="verify_token" name="verify_token" value="{{ old('verify_token', $settings['verify_token'] ?? '') }}" placeholder="my-secret-verify-token">
                                <small class="text-muted">يستخدم للتحقق من Webhook. يجب أن يكون نفس القيمة في Meta Webhook Settings</small>
                                @error('verify_token')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="app_secret" class="form-label">App Secret</label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('app_secret') is-invalid @enderror" id="app_secret" name="app_secret" autocomplete="new-password" placeholder="اتركه فارغاً للاحتفاظ بالقيمة الحالية">
                                    <button class="btn btn-outline-secondary" type="button" id="toggle-app-secret">
                                        <i class="fas fa-eye" id="toggle-app-secret-icon"></i>
                                    </button>
                                </div>
                                <small class="text-muted">يستخدم للتحقق من توقيع Webhook. يمكنك العثور عليه من Meta App Settings</small>
                                @error('app_secret')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            </div>

                            <button type="button" id="test-connection-btn-meta" class="btn btn-info btn-sm">
                                <i class="fas fa-plug me-2"></i>اختبار اتصال Meta
                            </button>
                            <div id="test-connection-result-meta" class="alert mt-3" style="display: none;" role="alert">
                                <span class="test-result-icon"></span>
                                <span class="test-result-message"></span>
                            </div>

                            </div>

                            <div class="tab-pane fade {{ $activeProvider === 'custom_api' ? 'show active' : '' }}" id="tab-pane-custom_api" role="tabpanel">

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="whatsapp_provider" id="whatsapp_provider_custom_api" value="custom_api" {{ $activeProvider === 'custom_api' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="whatsapp_provider_custom_api">
                                        تفعيل Custom API كمزوّد الإرسال الرئيسي
                                    </label>
                                </div>

                                <div class="mb-3">
                                    <label for="custom_api_url" class="form-label">رابط API <span class="text-danger">*</span></label>
                                    <input type="url" class="form-control @error('custom_api_url') is-invalid @enderror" id="custom_api_url" name="custom_api_url" value="{{ old('custom_api_url', $settings['custom_api_url'] ?? '') }}" placeholder="https://api.example.com/whatsapp/send">
                                    <small class="text-muted">رابط API endpoint لإرسال الرسائل</small>
                                    @error('custom_api_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="custom_api_key" class="form-label">API Key</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('custom_api_key') is-invalid @enderror" id="custom_api_key" name="custom_api_key" autocomplete="new-password" placeholder="اتركه فارغاً للاحتفاظ بالقيمة الحالية">
                                        <button class="btn btn-outline-secondary" type="button" id="toggle-custom-api-key">
                                            <i class="fas fa-eye" id="toggle-custom-api-key-icon"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">API Key للمصادقة (يتم تشفيره)</small>
                                    @error('custom_api_key')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="custom_api_method" class="form-label">طريقة الطلب (Method)</label>
                                    <select class="form-select @error('custom_api_method') is-invalid @enderror" id="custom_api_method" name="custom_api_method">
                                        <option value="POST" {{ old('custom_api_method', $settings['custom_api_method'] ?? 'POST') === 'POST' ? 'selected' : '' }}>POST</option>
                                        <option value="GET" {{ old('custom_api_method', $settings['custom_api_method'] ?? 'POST') === 'GET' ? 'selected' : '' }}>GET</option>
                                    </select>
                                    <small class="text-muted">طريقة HTTP request</small>
                                    @error('custom_api_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="custom_api_headers" class="form-label">Headers إضافية (JSON - اختياري)</label>
                                    <textarea class="form-control @error('custom_api_headers') is-invalid @enderror" id="custom_api_headers" name="custom_api_headers" rows="3" placeholder='{"X-Custom-Header": "value"}'>{{ old('custom_api_headers', is_array($settings['custom_api_headers'] ?? []) ? json_encode($settings['custom_api_headers'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : ($settings['custom_api_headers'] ?? '{}')) }}</textarea>
                                    <small class="text-muted">Headers إضافية بصيغة JSON (اختياري)</small>
                                    @error('custom_api_headers')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="button" id="test-connection-btn-custom_api" class="btn btn-info btn-sm">
                                    <i class="fas fa-plug me-2"></i>اختبار اتصال Custom API
                                </button>
                                <div id="test-connection-result-custom_api" class="alert mt-3" style="display: none;" role="alert">
                                    <span class="test-result-icon"></span>
                                    <span class="test-result-message"></span>
                                </div>

                            </div>

                            <div class="tab-pane fade {{ $activeProvider === 'flaxxa' ? 'show active' : '' }}" id="tab-pane-flaxxa" role="tabpanel">

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="whatsapp_provider" id="whatsapp_provider_flaxxa" value="flaxxa" {{ $activeProvider === 'flaxxa' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="whatsapp_provider_flaxxa">
                                        تفعيل Flaxxa Wapi كمزوّد الإرسال الرئيسي
                                    </label>
                                </div>

                                <div class="mb-3">
                                    <label for="flaxxa_base_url" class="form-label">رابط API <span class="text-danger">*</span></label>
                                    <input type="url" class="form-control @error('flaxxa_base_url') is-invalid @enderror" id="flaxxa_base_url" name="flaxxa_base_url" value="{{ old('flaxxa_base_url', $settings['flaxxa_base_url'] ?? 'https://wapi.flaxxa.com') }}" placeholder="https://wapi.flaxxa.com">
                                    <small class="text-muted">رابط Flaxxa Wapi الأساسي (Base URL)</small>
                                    @error('flaxxa_base_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="flaxxa_token" class="form-label">API Token</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('flaxxa_token') is-invalid @enderror" id="flaxxa_token" name="flaxxa_token" autocomplete="new-password" placeholder="اتركه فارغاً للاحتفاظ بالقيمة الحالية">
                                        <button class="btn btn-outline-secondary" type="button" id="toggle-flaxxa-token">
                                            <i class="fas fa-eye" id="toggle-flaxxa-token-icon"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">اتركه فارغاً للاحتفاظ بالـ Token الحالي. يمكنك الحصول عليه من لوحة تحكم Flaxxa Wapi (Brand → API Access)</small>
                                    @error('flaxxa_token')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3 d-flex gap-2">
                                    <button type="button" id="test-connection-btn-flaxxa" class="btn btn-info btn-sm">
                                        <i class="fas fa-plug me-2"></i>اختبار اتصال Flaxxa
                                    </button>
                                    <button type="button" id="fetch-flaxxa-templates-btn" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-list me-2"></i>جلب القوالب المعتمدة من Flaxxa
                                    </button>
                                </div>
                                <div id="test-connection-result-flaxxa" class="alert mt-3" style="display: none;" role="alert">
                                    <span class="test-result-icon"></span>
                                    <span class="test-result-message"></span>
                                </div>
                                <div id="flaxxa-templates-result" class="mt-3" style="display: none;">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead>
                                                <tr>
                                                    <th>اسم القالب</th>
                                                    <th>الحالة</th>
                                                    <th>اللغة</th>
                                                    <th>التصنيف</th>
                                                    <th>إجراء</th>
                                                </tr>
                                            </thead>
                                            <tbody id="flaxxa-templates-tbody"></tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                            </div>

                            <hr class="my-4">

                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-comment-dots me-2 text-primary"></i>إعدادات قالب رسالة التحقق (OTP)
                            </h6>

                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                عند استخدام Flaxxa، الرسائل النصية الحرة تعمل فقط ضمن نافذة 24 ساعة من آخر رد للعميل على واتساب.
                                لضمان وصول رمز التحقق دائماً (خصوصاً لمستخدم جديد لم يراسل الرقم من قبل)، فعّل وضع "قالب معتمد" أدناه
                                وحدد لكل غرض (تسجيل/نسيان كلمة مرور) اسم قالب واتساب معتمد فعلياً.
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>تعليمات المتغيّرات:</strong> كل قالب من القالبين أدناه يجب أن يحتوي على
                                <strong>متغيّر واحد بالضبط</strong> في نص الرسالة (Body) بصيغة رقم بين قوسين متتاليين (المتغيّر الأول)،
                                حيث سيُدرَج فيه رمز التحقق المكوّن من 6 أرقام تلقائياً عند الإرسال.
                                لا تُضِف متغيّرات أخرى في الـ Header أو الأزرار لهذين القالبين تحديداً، فالنظام لا يرسل قيماً لها حالياً —
                                وإلا سيرفض Meta الإرسال بخطأ "عدم تطابق عدد المتغيّرات".
                            </div>

                            <div class="mb-3">
                                <label for="otp_whatsapp_delivery_mode" class="form-label">وضع إرسال رمز التحقق عبر واتساب</label>
                                <select class="form-select @error('otp_whatsapp_delivery_mode') is-invalid @enderror" id="otp_whatsapp_delivery_mode" name="otp_whatsapp_delivery_mode">
                                    <option value="text" {{ old('otp_whatsapp_delivery_mode', $settings['otp_whatsapp_delivery_mode'] ?? 'text') === 'text' ? 'selected' : '' }}>نص مباشر (Free-form Text)</option>
                                    <option value="template" {{ old('otp_whatsapp_delivery_mode', $settings['otp_whatsapp_delivery_mode'] ?? 'text') === 'template' ? 'selected' : '' }}>قالب معتمد (Approved Template)</option>
                                </select>
                                @error('otp_whatsapp_delivery_mode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div id="otp-template-fields" style="display: {{ old('otp_whatsapp_delivery_mode', $settings['otp_whatsapp_delivery_mode'] ?? 'text') === 'template' ? 'block' : 'none' }};">

                                <div class="mb-3">
                                    <button type="button" id="refresh-otp-template-pickers-btn" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-sync me-1"></i>تحديث القوالب المتاحة من Flaxxa
                                    </button>
                                    <small class="text-muted d-block mt-1">يجلب قائمة القوالب المعتمدة الحالية من Flaxxa لتسهيل الاختيار في القائمتين أدناه، بدل كتابة الاسم يدوياً.</small>
                                    <div id="otp-template-pickers-error" class="text-danger small mt-1" style="display: none;"></div>
                                </div>

                                <h6 class="fw-bold mb-2"><i class="fas fa-user-plus me-2 text-success"></i>قالب التسجيل الجديد / تفعيل الحساب</h6>
                                <div class="mb-3">
                                    <label for="otp-verification-template-picker" class="form-label">اختيار سريع من القوالب المعتمدة</label>
                                    <select class="form-select form-select-sm" id="otp-verification-template-picker">
                                        <option value="">-- اضغط "تحديث القوالب المتاحة" أعلاه أولاً --</option>
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label for="otp_whatsapp_template_name" class="form-label">اسم القالب المعتمد</label>
                                        <input type="text" class="form-control @error('otp_whatsapp_template_name') is-invalid @enderror" id="otp_whatsapp_template_name" name="otp_whatsapp_template_name" value="{{ old('otp_whatsapp_template_name', $settings['otp_whatsapp_template_name'] ?? '') }}" placeholder="otp_verification">
                                        <small class="text-muted">يُستخدم عند تسجيل حساب جديد وأي عملية تحقق من رقم الهاتف</small>
                                        @error('otp_whatsapp_template_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="otp_whatsapp_template_language" class="form-label">لغة القالب</label>
                                        <input type="text" class="form-control @error('otp_whatsapp_template_language') is-invalid @enderror" id="otp_whatsapp_template_language" name="otp_whatsapp_template_language" value="{{ old('otp_whatsapp_template_language', $settings['otp_whatsapp_template_language'] ?? 'ar') }}" placeholder="ar">
                                        @error('otp_whatsapp_template_language')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <h6 class="fw-bold mb-2 mt-3"><i class="fas fa-key me-2 text-warning"></i>قالب نسيان كلمة المرور</h6>
                                <div class="mb-3">
                                    <label for="otp-password-reset-template-picker" class="form-label">اختيار سريع من القوالب المعتمدة</label>
                                    <select class="form-select form-select-sm" id="otp-password-reset-template-picker">
                                        <option value="">-- اضغط "تحديث القوالب المتاحة" أعلاه أولاً --</option>
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label for="otp_whatsapp_password_reset_template_name" class="form-label">اسم القالب المعتمد</label>
                                        <input type="text" class="form-control @error('otp_whatsapp_password_reset_template_name') is-invalid @enderror" id="otp_whatsapp_password_reset_template_name" name="otp_whatsapp_password_reset_template_name" value="{{ old('otp_whatsapp_password_reset_template_name', $settings['otp_whatsapp_password_reset_template_name'] ?? '') }}" placeholder="otp_password_reset">
                                        <small class="text-muted">اختياري — اتركه فارغاً لاستخدام قالب "التسجيل الجديد" نفسه لنسيان كلمة المرور أيضاً</small>
                                        @error('otp_whatsapp_password_reset_template_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="otp_whatsapp_password_reset_template_language" class="form-label">لغة القالب</label>
                                        <input type="text" class="form-control @error('otp_whatsapp_password_reset_template_language') is-invalid @enderror" id="otp_whatsapp_password_reset_template_language" name="otp_whatsapp_password_reset_template_language" value="{{ old('otp_whatsapp_password_reset_template_language', $settings['otp_whatsapp_password_reset_template_language'] ?? 'ar') }}" placeholder="ar">
                                        @error('otp_whatsapp_password_reset_template_language')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-cog me-2 text-secondary"></i>الإعدادات المتقدمة
                            </h6>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="strict_signature" name="strict_signature" value="1" {{ old('strict_signature', $settings['strict_signature'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="strict_signature">
                                        التحقق الصارم من التوقيع (Signature Verification)
                                    </label>
                                </div>
                                <small class="text-muted">يوصى بتركه مفعلاً للأمان. يعطل التحقق في بيئة التطوير إذا كان معطلاً</small>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="auto_reply" name="auto_reply" value="1" {{ old('auto_reply', $settings['auto_reply'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="auto_reply">
                                        تفعيل الرد التلقائي
                                    </label>
                                </div>
                                <small class="text-muted">إرسال رد تلقائي على الرسائل الواردة</small>
                            </div>

                            <div class="mb-4" id="auto-reply-message-field" style="display: {{ old('auto_reply', $settings['auto_reply'] ?? false) ? 'block' : 'none' }};">
                                <label for="auto_reply_message" class="form-label">رسالة الرد التلقائي</label>
                                <textarea class="form-control" id="auto_reply_message" name="auto_reply_message" rows="3" placeholder="شكراً لك، تم استلام رسالتك. سنرد عليك قريباً.">{{ old('auto_reply_message', $settings['auto_reply_message'] ?? 'شكراً لك، تم استلام رسالتك. سنرد عليك قريباً.') }}</textarea>
                                <small class="text-muted">الرسالة التي سيتم إرسالها تلقائياً عند استلام رسالة</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="webhook_path" class="form-label">مسار Webhook</label>
                                    <input type="text" class="form-control @error('webhook_path') is-invalid @enderror" id="webhook_path" name="webhook_path" value="{{ old('webhook_path', $settings['webhook_path'] ?? '/api/webhooks/whatsapp') }}" placeholder="/api/webhooks/whatsapp">
                                    <small class="text-muted">المسار الذي سيستخدمه Meta لإرسال Webhooks</small>
                                    @error('webhook_path')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="timeout" class="form-label">المهلة الزمنية (Timeout)</label>
                                    <input type="number" class="form-control @error('timeout') is-invalid @enderror" id="timeout" name="timeout" value="{{ old('timeout', $settings['timeout'] ?? 30) }}" min="1" max="300" placeholder="30">
                                    <small class="text-muted">المهلة الزمنية لطلبات API بالثواني (1-300)</small>
                                    @error('timeout')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="default_from" class="form-label">رقم الهاتف الافتراضي (اختياري)</label>
                                <input type="text" class="form-control @error('default_from') is-invalid @enderror" id="default_from" name="default_from" value="{{ old('default_from', $settings['default_from'] ?? '') }}" placeholder="+1234567890">
                                <small class="text-muted">رقم الهاتف الافتراضي لإرسال الرسائل (يمكن تركه فارغاً)</small>
                                @error('default_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>حفظ الإعدادات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0 text-white">
                            <i class="fas fa-info-circle me-2"></i>معلومات Webhook
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Webhook URL:</strong></p>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" value="{{ url($settings['webhook_path'] ?? config('whatsapp.webhook_path', '/api/webhooks/whatsapp')) }}" readonly id="webhook-url">
                            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('webhook-url')">
                                <i class="fas fa-copy"></i> نسخ
                            </button>
                        </div>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>ملاحظة:</strong> استخدم هذا الرابط عند إعداد Webhook في Meta App Dashboard. 
                            تأكد من أن Verify Token يطابق القيمة المدخلة أعلاه.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleAccessToken = document.getElementById('toggle-access-token');
    const accessTokenInput = document.getElementById('access_token');
    const toggleAppSecret = document.getElementById('toggle-app-secret');
    const appSecretInput = document.getElementById('app_secret');
    const toggleCustomApiKey = document.getElementById('toggle-custom-api-key');
    const customApiKeyInput = document.getElementById('custom_api_key');
    const toggleFlaxxaToken = document.getElementById('toggle-flaxxa-token');
    const flaxxaTokenInput = document.getElementById('flaxxa_token');
    const autoReply = document.getElementById('auto_reply');
    const autoReplyMessageField = document.getElementById('auto-reply-message-field');
    const otpWhatsappDeliveryMode = document.getElementById('otp_whatsapp_delivery_mode');
    const otpTemplateFields = document.getElementById('otp-template-fields');
    const fetchFlaxxaTemplatesBtn = document.getElementById('fetch-flaxxa-templates-btn');
    const flaxxaTemplatesResult = document.getElementById('flaxxa-templates-result');
    const flaxxaTemplatesTbody = document.getElementById('flaxxa-templates-tbody');
    const otpTemplateNameInput = document.getElementById('otp_whatsapp_template_name');
    const otpTemplateLanguageInput = document.getElementById('otp_whatsapp_template_language');
    const otpPasswordResetTemplateNameInput = document.getElementById('otp_whatsapp_password_reset_template_name');
    const otpPasswordResetTemplateLanguageInput = document.getElementById('otp_whatsapp_password_reset_template_language');
    const otpTextOption = otpWhatsappDeliveryMode ? otpWhatsappDeliveryMode.querySelector('option[value="text"]') : null;
    let flaxxaTemplatesByKey = {};

    // Toggle OTP template fields
    function toggleOtpTemplateFields() {
        if (!otpWhatsappDeliveryMode || !otpTemplateFields) return;
        otpTemplateFields.style.display = otpWhatsappDeliveryMode.value === 'template' ? 'block' : 'none';
    }

    if (otpWhatsappDeliveryMode) {
        toggleOtpTemplateFields();
        otpWhatsappDeliveryMode.addEventListener('change', toggleOtpTemplateFields);
    }

    // Flaxxa only reliably delivers OTP via an approved template (free-form text
    // only works within the 24h customer-reply window), so force template mode
    // whenever its "activate" radio is checked, and disable the free-text option
    // so the UI can never disagree with what the server will actually enforce.
    function syncOtpModeWithProvider() {
        const checkedRadio = document.querySelector('input[name="whatsapp_provider"]:checked');
        const isFlaxxa = checkedRadio && checkedRadio.value === 'flaxxa';

        if (otpTextOption) {
            otpTextOption.disabled = isFlaxxa;
            otpTextOption.title = isFlaxxa ? 'غير متاح مع Flaxxa — يتطلب قالباً معتمداً' : '';
        }

        if (isFlaxxa && otpWhatsappDeliveryMode) {
            otpWhatsappDeliveryMode.value = 'template';
            toggleOtpTemplateFields();
        }
    }

    document.querySelectorAll('input[name="whatsapp_provider"]').forEach(function(radio) {
        radio.addEventListener('change', syncOtpModeWithProvider);
    });
    syncOtpModeWithProvider();

    // Count double-curly-brace variables (e.g. number 1, 2, ...) in a template's
    // BODY component text. Built from single-brace literals, not a double-brace
    // pair, so Blade's compiler does not mistake this for one of its own echo tags.
    function countBodyVariables(tpl) {
        if (!tpl || !tpl.components) return null;

        let components;
        try {
            components = typeof tpl.components === 'string' ? JSON.parse(tpl.components) : tpl.components;
        } catch (e) {
            return null;
        }
        if (!Array.isArray(components)) return null;

        const bodyComponent = components.find(c => (c.type || '').toUpperCase() === 'BODY');
        if (!bodyComponent || !bodyComponent.text) return 0;

        const brace = '{';
        const closeBrace = '}';
        const pattern = new RegExp(brace + brace + '(\\d+)' + closeBrace + closeBrace, 'g');
        return [...bodyComponent.text.matchAll(pattern)].length;
    }

    // purpose: 'verification' (new registration / account activation) or
    // 'password_reset' (forgot password) — each has its own template fields.
    function useFlaxxaTemplateForOtp(key, purpose) {
        const tpl = flaxxaTemplatesByKey[key];
        if (!tpl) return;

        const varCount = countBodyVariables(tpl);
        if (varCount !== 1) {
            const proceed = confirm(
                'هذا القالب يحتوي على ' + varCount + ' متغيّر بينما نظام رمز التحقق يرسل متغيّراً واحداً فقط (الرمز) — ' +
                'استخدامه على الأرجح سيؤدي لفشل الإرسال. هل تريد المتابعة رغم ذلك؟'
            );
            if (!proceed) return;
        }

        const nameInput = purpose === 'password_reset' ? otpPasswordResetTemplateNameInput : otpTemplateNameInput;
        const languageInput = purpose === 'password_reset' ? otpPasswordResetTemplateLanguageInput : otpTemplateLanguageInput;

        if (nameInput) nameInput.value = tpl.name || '';
        if (languageInput) languageInput.value = tpl.language || '';
        if (otpWhatsappDeliveryMode) {
            otpWhatsappDeliveryMode.value = 'template';
            toggleOtpTemplateFields();
        }
    }

    if (flaxxaTemplatesTbody) {
        flaxxaTemplatesTbody.addEventListener('click', function(event) {
            const btn = event.target.closest('.use-flaxxa-template-for-otp');
            if (!btn) return;
            useFlaxxaTemplateForOtp(btn.dataset.templateKey, btn.dataset.templatePurpose);
        });
    }

    // Quick-pick dropdowns right next to each OTP template's own fields, so the
    // admin does not have to scroll up to the Flaxxa tab's templates table.
    const refreshOtpTemplatePickersBtn = document.getElementById('refresh-otp-template-pickers-btn');
    const otpTemplatePickersError = document.getElementById('otp-template-pickers-error');
    const otpVerificationTemplatePicker = document.getElementById('otp-verification-template-picker');
    const otpPasswordResetTemplatePicker = document.getElementById('otp-password-reset-template-picker');

    function populateOtpTemplatePicker(select, templates) {
        if (!select) return;
        select.innerHTML = '<option value="">-- اختر قالباً معتمداً (اختياري) --</option>';
        templates
            .filter(tpl => tpl.status === 'APPROVED')
            .forEach(function(tpl) {
                const key = (tpl.name || '') + '|' + (tpl.language || 'ar');
                const option = document.createElement('option');
                option.value = key;
                option.textContent = tpl.name + ' (' + (tpl.language || 'ar') + ')';
                select.appendChild(option);
            });
    }

    if (refreshOtpTemplatePickersBtn) {
        refreshOtpTemplatePickersBtn.addEventListener('click', function() {
            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>جاري الجلب...';
            if (otpTemplatePickersError) otpTemplatePickersError.style.display = 'none';

            const formData = new FormData();
            formData.append('flaxxa_base_url', document.getElementById('flaxxa_base_url').value);
            if (flaxxaTokenInput && flaxxaTokenInput.value) {
                formData.append('flaxxa_token', flaxxaTokenInput.value);
            }
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route("admin.whatsapp-settings.flaxxa-templates") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    if (otpTemplatePickersError) {
                        otpTemplatePickersError.textContent = data.message || 'تعذر جلب القوالب';
                        otpTemplatePickersError.style.display = 'block';
                    }
                    return;
                }

                const templates = data.templates || [];
                templates.forEach(function(tpl) {
                    const key = (tpl.name || '') + '|' + (tpl.language || 'ar');
                    flaxxaTemplatesByKey[key] = tpl;
                });

                populateOtpTemplatePicker(otpVerificationTemplatePicker, templates);
                populateOtpTemplatePicker(otpPasswordResetTemplatePicker, templates);
            })
            .catch(error => {
                if (otpTemplatePickersError) {
                    otpTemplatePickersError.textContent = 'حدث خطأ: ' + error.message;
                    otpTemplatePickersError.style.display = 'block';
                }
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
        });
    }

    if (otpVerificationTemplatePicker) {
        otpVerificationTemplatePicker.addEventListener('change', function() {
            if (!this.value) return;
            useFlaxxaTemplateForOtp(this.value, 'verification');
        });
    }

    if (otpPasswordResetTemplatePicker) {
        otpPasswordResetTemplatePicker.addEventListener('change', function() {
            if (!this.value) return;
            useFlaxxaTemplateForOtp(this.value, 'password_reset');
        });
    }

    if (toggleFlaxxaToken && flaxxaTokenInput) {
        toggleFlaxxaToken.addEventListener('click', function() {
            const type = flaxxaTokenInput.getAttribute('type') === 'password' ? 'text' : 'password';
            flaxxaTokenInput.setAttribute('type', type);
            const icon = document.getElementById('toggle-flaxxa-token-icon');
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
    }

    if (fetchFlaxxaTemplatesBtn) {
        fetchFlaxxaTemplatesBtn.addEventListener('click', function() {
            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>جاري الجلب...';

            const formData = new FormData();
            formData.append('flaxxa_base_url', document.getElementById('flaxxa_base_url').value);
            // Only send the token if the admin actually typed one; an empty field means
            // "keep the currently saved token", and the backend only falls back to it
            // when the key is entirely absent from the request.
            if (flaxxaTokenInput && flaxxaTokenInput.value) {
                formData.append('flaxxa_token', flaxxaTokenInput.value);
            }
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route("admin.whatsapp-settings.flaxxa-templates") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!flaxxaTemplatesTbody) return;
                flaxxaTemplatesTbody.innerHTML = '';

                if (data.success && Array.isArray(data.templates) && data.templates.length > 0) {
                    flaxxaTemplatesByKey = {};
                    data.templates.forEach(function(tpl) {
                        const key = (tpl.name || '') + '|' + (tpl.language || 'ar');
                        flaxxaTemplatesByKey[key] = tpl;

                        const row = document.createElement('tr');
                        const actionCell = tpl.status === 'APPROVED'
                            ? '<div class="d-flex flex-column gap-1">' +
                              '<button type="button" class="btn btn-outline-primary btn-sm use-flaxxa-template-for-otp" data-template-key="' + key + '" data-template-purpose="verification">للتسجيل / تفعيل الحساب</button>' +
                              '<button type="button" class="btn btn-outline-warning btn-sm use-flaxxa-template-for-otp" data-template-key="' + key + '" data-template-purpose="password_reset">لنسيان كلمة المرور</button>' +
                              '</div>'
                            : '';
                        row.innerHTML = '<td>' + (tpl.name ?? '') + '</td>' +
                            '<td>' + (tpl.status ?? '') + '</td>' +
                            '<td>' + (tpl.language ?? '') + '</td>' +
                            '<td>' + (tpl.category ?? '') + '</td>' +
                            '<td>' + actionCell + '</td>';
                        flaxxaTemplatesTbody.appendChild(row);
                    });
                    flaxxaTemplatesResult.style.display = 'block';
                } else if (data.success) {
                    flaxxaTemplatesTbody.innerHTML = '<tr><td colspan="5" class="text-muted">لا توجد قوالب معتمدة حتى الآن</td></tr>';
                    flaxxaTemplatesResult.style.display = 'block';
                } else {
                    flaxxaTemplatesTbody.innerHTML = '<tr><td colspan="5" class="text-danger">' + (data.message ?? 'حدث خطأ') + '</td></tr>';
                    flaxxaTemplatesResult.style.display = 'block';
                }
            })
            .catch(error => {
                if (flaxxaTemplatesTbody) {
                    flaxxaTemplatesTbody.innerHTML = '<tr><td colspan="5" class="text-danger">حدث خطأ: ' + error.message + '</td></tr>';
                    flaxxaTemplatesResult.style.display = 'block';
                }
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
        });
    }

    // Toggle password visibility
    toggleAccessToken.addEventListener('click', function() {
        const type = accessTokenInput.getAttribute('type') === 'password' ? 'text' : 'password';
        accessTokenInput.setAttribute('type', type);
        const icon = document.getElementById('toggle-access-token-icon');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });

    toggleAppSecret.addEventListener('click', function() {
        const type = appSecretInput.getAttribute('type') === 'password' ? 'text' : 'password';
        appSecretInput.setAttribute('type', type);
        const icon = document.getElementById('toggle-app-secret-icon');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });

    if (toggleCustomApiKey && customApiKeyInput) {
        toggleCustomApiKey.addEventListener('click', function() {
            const type = customApiKeyInput.getAttribute('type') === 'password' ? 'text' : 'password';
            customApiKeyInput.setAttribute('type', type);
            const icon = document.getElementById('toggle-custom-api-key-icon');
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
    }

    // Auto reply toggle
    autoReply.addEventListener('change', function() {
        autoReplyMessageField.style.display = this.checked ? 'block' : 'none';
    });

    // Test connection — one independent tester per provider tab, so each tab
    // only ever tests its own fields. No cross-provider ambiguity possible.
    function buildProviderFieldData(provider, formData) {
        if (provider === 'meta') {
            formData.append('phone_number_id', document.getElementById('phone_number_id').value);
            formData.append('access_token', document.getElementById('access_token').value);
            formData.append('api_version', document.getElementById('api_version').value);
        } else if (provider === 'flaxxa') {
            formData.append('flaxxa_base_url', document.getElementById('flaxxa_base_url').value);
            const flaxxaTokenValue = document.getElementById('flaxxa_token').value;
            if (flaxxaTokenValue) {
                formData.append('flaxxa_token', flaxxaTokenValue);
            }
        } else {
            formData.append('custom_api_url', document.getElementById('custom_api_url').value);
            formData.append('custom_api_key', document.getElementById('custom_api_key').value);
            formData.append('custom_api_method', document.getElementById('custom_api_method').value);
            formData.append('custom_api_headers', document.getElementById('custom_api_headers').value);
        }
    }

    function wireProviderTestConnection(provider, btnId, resultId) {
        const btn = document.getElementById(btnId);
        const resultDiv = document.getElementById(resultId);
        if (!btn || !resultDiv) return;

        const resultIcon = resultDiv.querySelector('.test-result-icon');
        const resultMessage = resultDiv.querySelector('.test-result-message');

        btn.addEventListener('click', function() {
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>جاري الاختبار...';

            const formData = new FormData();
            formData.append('whatsapp_provider', provider);
            buildProviderFieldData(provider, formData);
            formData.append('_token', '{{ csrf_token() }}');

            resultDiv.style.display = 'none';

            fetch('{{ route("admin.whatsapp-settings.test-connection") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                resultDiv.style.display = 'block';
                resultDiv.className = 'alert mt-3 ' + (data.success ? 'alert-success' : 'alert-danger');
                resultIcon.innerHTML = data.success
                    ? '<i class="fas fa-check-circle me-2"></i>'
                    : '<i class="fas fa-times-circle me-2"></i>';
                resultMessage.textContent = data.message;
            })
            .catch(error => {
                resultDiv.style.display = 'block';
                resultDiv.className = 'alert alert-danger mt-3';
                resultIcon.innerHTML = '<i class="fas fa-times-circle me-2"></i>';
                resultMessage.textContent = 'حدث خطأ: ' + error.message;
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
        });
    }

    wireProviderTestConnection('meta', 'test-connection-btn-meta', 'test-connection-result-meta');
    wireProviderTestConnection('custom_api', 'test-connection-btn-custom_api', 'test-connection-result-custom_api');
    wireProviderTestConnection('flaxxa', 'test-connection-btn-flaxxa', 'test-connection-result-flaxxa');
});

function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999); // For mobile devices
    document.execCommand('copy');
    
    // Show feedback
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i> تم النسخ';
    btn.classList.remove('btn-outline-secondary');
    btn.classList.add('btn-success');
    setTimeout(() => {
        btn.innerHTML = originalHtml;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-secondary');
    }, 2000);
}
</script>
@stop
