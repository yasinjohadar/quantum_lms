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

                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-plug me-2 text-primary"></i>اختيار المزود (Provider)
                            </h6>

                            <div class="mb-4">
                                <label for="whatsapp_provider" class="form-label">نوع المزود <span class="text-danger">*</span></label>
                                <select class="form-select @error('whatsapp_provider') is-invalid @enderror" id="whatsapp_provider" name="whatsapp_provider" required>
                                    <option value="meta" {{ old('whatsapp_provider', $settings['whatsapp_provider'] ?? 'meta') === 'meta' ? 'selected' : '' }}>Meta WhatsApp Cloud API</option>
                                    <option value="custom_api" {{ old('whatsapp_provider', $settings['whatsapp_provider'] ?? 'meta') === 'custom_api' ? 'selected' : '' }}>Custom API Provider</option>
                                </select>
                                <small class="text-muted">اختر مزود WhatsApp الذي تريد استخدامه</small>
                                @error('whatsapp_provider')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-4">

                            <h6 class="fw-bold mb-3" id="meta-settings-header">
                                <i class="fas fa-info-circle me-2 text-info"></i>معلومات حساب Meta
                            </h6>

                            <div id="meta-settings">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="api_version" class="form-label">إصدار API <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('api_version') is-invalid @enderror" id="api_version" name="api_version" value="{{ old('api_version', $settings['api_version'] ?? 'v20.0') }}" required>
                                    <small class="text-muted">مثال: v20.0</small>
                                    @error('api_version')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="phone_number_id" class="form-label">Phone Number ID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('phone_number_id') is-invalid @enderror" id="phone_number_id" name="phone_number_id" value="{{ old('phone_number_id', $settings['phone_number_id'] ?? '') }}" required placeholder="123456789012345">
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
                                    <input type="password" class="form-control @error('access_token') is-invalid @enderror" id="access_token" name="access_token" placeholder="اتركه فارغاً للاحتفاظ بالقيمة الحالية">
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
                                <input type="text" class="form-control @error('verify_token') is-invalid @enderror" id="verify_token" name="verify_token" value="{{ old('verify_token', $settings['verify_token'] ?? '') }}" required placeholder="my-secret-verify-token">
                                <small class="text-muted">يستخدم للتحقق من Webhook. يجب أن يكون نفس القيمة في Meta Webhook Settings</small>
                                @error('verify_token')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="app_secret" class="form-label">App Secret</label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('app_secret') is-invalid @enderror" id="app_secret" name="app_secret" placeholder="اتركه فارغاً للاحتفاظ بالقيمة الحالية">
                                    <button class="btn btn-outline-secondary" type="button" id="toggle-app-secret">
                                        <i class="fas fa-eye" id="toggle-app-secret-icon"></i>
                                    </button>
                                </div>
                                <small class="text-muted">يستخدم للتحقق من توقيع Webhook. يمكنك العثور عليه في Meta App Settings</small>
                                @error('app_secret')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            </div>

                            <div id="custom-api-settings" style="display: none;">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-code me-2 text-success"></i>إعدادات Custom API
                                </h6>

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
                                        <input type="password" class="form-control @error('custom_api_key') is-invalid @enderror" id="custom_api_key" name="custom_api_key" placeholder="اتركه فارغاً للاحتفاظ بالقيمة الحالية">
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

                            <div id="test-connection-result" class="alert mt-3" style="display: none;" role="alert">
                                <span id="test-result-icon"></span>
                                <span id="test-result-message"></span>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="button" id="test-connection-btn" class="btn btn-info btn-lg">
                                    <i class="fas fa-plug me-2"></i>اختبار الاتصال
                                </button>
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
    const whatsappProvider = document.getElementById('whatsapp_provider');
    const metaSettings = document.getElementById('meta-settings');
    const metaSettingsHeader = document.getElementById('meta-settings-header');
    const customApiSettings = document.getElementById('custom-api-settings');
    const toggleAccessToken = document.getElementById('toggle-access-token');
    const accessTokenInput = document.getElementById('access_token');
    const toggleAppSecret = document.getElementById('toggle-app-secret');
    const appSecretInput = document.getElementById('app_secret');
    const toggleCustomApiKey = document.getElementById('toggle-custom-api-key');
    const customApiKeyInput = document.getElementById('custom_api_key');
    const autoReply = document.getElementById('auto_reply');
    const autoReplyMessageField = document.getElementById('auto-reply-message-field');
    const testConnectionBtn = document.getElementById('test-connection-btn');
    const testResultDiv = document.getElementById('test-connection-result');
    const testResultIcon = document.getElementById('test-result-icon');
    const testResultMessage = document.getElementById('test-result-message');

    // Toggle provider settings
    function toggleProviderSettings() {
        if (!whatsappProvider) return;
        
        const provider = whatsappProvider.value;
        
        // Get all Meta required fields
        const metaRequiredFields = [
            'api_version',
            'phone_number_id',
            'verify_token'
        ];
        
        if (provider === 'custom_api') {
            if (metaSettings) metaSettings.style.display = 'none';
            if (metaSettingsHeader) metaSettingsHeader.style.display = 'none';
            if (customApiSettings) customApiSettings.style.display = 'block';
            
            // Remove required attribute from Meta fields when Custom API is selected
            metaRequiredFields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                if (field) {
                    field.removeAttribute('required');
                }
            });
            
            // Add required to custom_api_url
            const customApiUrl = document.getElementById('custom_api_url');
            if (customApiUrl) {
                customApiUrl.setAttribute('required', 'required');
            }
        } else {
            if (metaSettings) metaSettings.style.display = 'block';
            if (metaSettingsHeader) metaSettingsHeader.style.display = 'block';
            if (customApiSettings) customApiSettings.style.display = 'none';
            
            // Add required attribute to Meta fields when Meta is selected
            metaRequiredFields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                if (field) {
                    field.setAttribute('required', 'required');
                }
            });
            
            // Remove required from custom_api_url
            const customApiUrl = document.getElementById('custom_api_url');
            if (customApiUrl) {
                customApiUrl.removeAttribute('required');
            }
        }
    }

    // Initial toggle
    toggleProviderSettings();

    // Listen to provider change
    if (whatsappProvider) {
        whatsappProvider.addEventListener('change', toggleProviderSettings);
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

    // Test connection
    testConnectionBtn.addEventListener('click', function() {
        const btn = this;
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>جاري الاختبار...';

        const formData = new FormData();
        const provider = whatsappProvider ? whatsappProvider.value : 'meta';
        formData.append('whatsapp_provider', provider);
        
        if (provider === 'meta') {
            formData.append('phone_number_id', document.getElementById('phone_number_id').value);
            formData.append('access_token', document.getElementById('access_token').value);
            formData.append('api_version', document.getElementById('api_version').value);
        } else {
            formData.append('custom_api_url', document.getElementById('custom_api_url').value);
            formData.append('custom_api_key', document.getElementById('custom_api_key').value);
            formData.append('custom_api_method', document.getElementById('custom_api_method').value);
            formData.append('custom_api_headers', document.getElementById('custom_api_headers').value);
        }
        
        formData.append('_token', '{{ csrf_token() }}');

        testResultDiv.style.display = 'none';

        fetch('{{ route("admin.whatsapp-settings.test-connection") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            testResultDiv.style.display = 'block';
            if (data.success) {
                testResultDiv.className = 'alert alert-success mt-3';
                testResultIcon.innerHTML = '<i class="fas fa-check-circle me-2"></i>';
                testResultMessage.textContent = data.message;
            } else {
                testResultDiv.className = 'alert alert-danger mt-3';
                testResultIcon.innerHTML = '<i class="fas fa-times-circle me-2"></i>';
                testResultMessage.textContent = data.message;
            }
        })
        .catch(error => {
            testResultDiv.style.display = 'block';
            testResultDiv.className = 'alert alert-danger mt-3';
            testResultIcon.innerHTML = '<i class="fas fa-times-circle me-2"></i>';
            testResultMessage.textContent = 'حدث خطأ: ' + error.message;
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    });
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
