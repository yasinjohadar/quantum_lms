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
                                    <small class="text-muted mt-1 d-block" id="provider_hint">
                                        💡 <strong>OpenRouter (موصى به)</strong>: يوفر موديلات مجانية متعددة
                                    </small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="model_key_select" class="form-label">معرف الموديل <span class="text-danger">*</span></label>
                                    <div id="model_key_container">
                                        <!-- الافتراضي: حقل نصي -->
                                        <input type="text" class="form-control" id="model_key_input" name="model_key" value="{{ old('model_key') }}" required placeholder="اختر المزود أولاً">
                                        <small class="text-muted" id="model_key_hint">اختر المزود أولاً لعرض الموديلات المتاحة</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="api_key" class="form-label">
                                    مفتاح API <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="api_key" name="api_key" value="{{ old('api_key') }}" placeholder="@if(old('provider') == 'google') AlzaSyBo-... (من Google AI Studio) @elseif(old('provider') == 'openrouter') sk-or-... (من OpenRouter) @elseif(old('provider') == 'openai') sk-... (من OpenAI Platform) @else أدخل مفتاح API @endif">
                                    <button type="button" class="btn btn-outline-primary" id="testApiKeyBtn" onclick="testApiKey()">
                                        <i class="fas fa-vial me-1"></i> اختبار الاتصال
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-1" id="api_key_hint">
                                    @if(old('provider') == 'google')
                                        <strong>📍 للحصول على API Key:</strong> اذهب إلى <a href="https://aistudio.google.com/app/api-keys" target="_blank">Google AI Studio</a> → API Keys → Copy Key
                                    @elseif(old('provider') == 'openai')
                                        <strong>📍 للحصول على API Key:</strong> اذهب إلى <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a> → API Keys → Create new secret key
                                    @elseif(old('provider') == 'openrouter')
                                        <strong>📍 للحصول على API Key مجاني:</strong> اذهب إلى <a href="https://openrouter.ai/keys" target="_blank">openrouter.ai/keys</a> → Create Key<br>
                                        <span class="text-success">✅ لا يحتاج بطاقة ائتمان | ✅ الموديلات المجانية متاحة فوراً</span>
                                    @else
                                        أدخل مفتاح API الخاص بالمزود
                                    @endif
                                </small>
                                <div id="testResult" class="mt-2"></div>
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

@section('js')
<script>
const supportedModels = @json($supportedModels);

document.addEventListener('DOMContentLoaded', function() {
    const providerSelect = document.getElementById('provider');
    const modelKeyContainer = document.getElementById('model_key_container');
    const providerHint = document.getElementById('provider_hint');
    
    const hints = {
        'openrouter': '🆓 الموديلات المجانية متاحة فوراً! | <a href="https://openrouter.ai/keys" target="_blank">الحصول على API Key مجاني</a>',
        'google': '📌 يحتاج API Key من <a href="https://aistudio.google.com/apikey" target="_blank">Google AI Studio</a>',
        'openai': '📌 يحتاج API Key من <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>',
        'anthropic': '📌 يحتاج API Key من <a href="https://console.anthropic.com/settings/keys" target="_blank">Anthropic Console</a>',
        'local': '🏠 للموديلات المحلية (Ollama, LM Studio) - لا يحتاج API Key'
    };
    
    providerSelect.addEventListener('change', function() {
        const provider = this.value;
        const models = supportedModels[provider] || {};
        
        // تحديث hint المزود
        providerHint.innerHTML = hints[provider] || '💡 <strong>OpenRouter (موصى به)</strong>: يوفر موديلات مجانية متعددة';
        
        if (Object.keys(models).length > 0) {
            // إنشاء قائمة منسدلة
            let html = `<select class="form-select" id="model_key_select" name="model_key" required>
                <option value="">-- اختر موديل --</option>`;
            
            for (const [key, name] of Object.entries(models)) {
                html += `<option value="${key}">${name}</option>`;
            }
            
            html += `<option value="__custom__">✏️ موديل مخصص</option></select>`;
            html += `<input type="text" class="form-control mt-2" id="model_key_custom_input" 
                    placeholder="أدخل معرف الموديل المخصص" style="display: none;">`;
            
            // إضافة hint
            if (provider === 'openrouter') {
                html += `<small class="text-muted d-block mt-1">🆓 الموديلات المجانية لا تحتاج رصيد! | <a href="https://openrouter.ai/models" target="_blank">عرض كل الموديلات</a></small>`;
            } else {
                html += `<small class="text-muted d-block mt-1">اختر من القائمة أو أدخل موديل مخصص</small>`;
            }
            
            modelKeyContainer.innerHTML = html;
            
            // إضافة event listener للتبديل بين القائمة والحقل المخصص
            const newSelect = document.getElementById('model_key_select');
            const customInput = document.getElementById('model_key_custom_input');
            
            newSelect.addEventListener('change', function() {
                if (this.value === '__custom__') {
                    customInput.style.display = 'block';
                    customInput.required = true;
                    customInput.name = 'model_key';
                    this.name = '';
                } else {
                    customInput.style.display = 'none';
                    customInput.required = false;
                    customInput.name = '';
                    this.name = 'model_key';
                }
            });
        } else {
            // حقل نصي فقط
            modelKeyContainer.innerHTML = `
                <input type="text" class="form-control" id="model_key_input" name="model_key" required placeholder="أدخل معرف الموديل">
                <small class="text-muted d-block mt-1">مثال: gpt-4, claude-3-opus, gemini-2.0-flash</small>
            `;
        }
    });
    
    // تفعيل الحالة الأولية إذا كان هناك provider مختار
    if (providerSelect.value) {
        providerSelect.dispatchEvent(new Event('change'));
    }
});

function testApiKey() {
    const btn = document.getElementById('testApiKeyBtn');
    const resultDiv = document.getElementById('testResult');
    const originalText = btn.innerHTML;
    const apiKey = document.getElementById('api_key').value;
    const provider = document.getElementById('provider').value;
    const modelKeySelect = document.getElementById('model_key_select');
    const modelKeyInput = document.getElementById('model_key_input');
    const modelKey = (modelKeySelect?.value && modelKeySelect.value !== '__custom__') 
        ? modelKeySelect.value 
        : (modelKeySelect?.value === '__custom__' ? document.getElementById('model_key_custom_input')?.value : (modelKeyInput?.value || ''));
    
    if (!apiKey || apiKey.trim() === '') {
        resultDiv.innerHTML = `
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>⚠️ تحذير:</strong> يرجى إدخال API Key أولاً
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        `;
        return;
    }
    
    if (!provider) {
        resultDiv.innerHTML = `
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>⚠️ تحذير:</strong> يرجى اختيار المزود أولاً
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        `;
        return;
    }
    
    if (!modelKey || modelKey.trim() === '') {
        resultDiv.innerHTML = `
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>⚠️ تحذير:</strong> يرجى إدخال Model Key أولاً
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        `;
        return;
    }
    
    // تعطيل الزر وإظهار حالة التحميل
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> جاري الاختبار...';
    resultDiv.innerHTML = '';
    
    // إرسال طلب AJAX لاختبار API Key
    fetch('{{ route("admin.ai.models.test-temp") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            provider: provider,
            model_key: modelKey,
            api_key: apiKey,
            base_url: document.getElementById('base_url')?.value || '',
            api_endpoint: document.getElementById('api_endpoint')?.value || ''
        })
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        
        if (data.success) {
            resultDiv.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>✓ نجح الاختبار!</strong><br>
                    ${data.message}<br>
                    ${data.response_time_ms ? `وقت الاستجابة: ${data.response_time_ms} مللي ثانية` : ''}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            `;
        } else {
            // عرض رسالة الخطأ مع تنسيق أفضل
            let errorHtml = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>✗ فشل الاختبار!</strong><br>`;
            
            // تقسيم الرسالة إلى أسطر
            if (data.message) {
                const lines = data.message.split('\n');
                lines.forEach(line => {
                    if (line.trim()) {
                        if (line.includes('معلومات التكوين:') || line.includes('نصائح:')) {
                            errorHtml += `<br><strong>${line}</strong>`;
                        } else if (line.startsWith('-')) {
                            errorHtml += `<br>${line}`;
                        } else {
                            errorHtml += `<br>${line}`;
                        }
                    }
                });
            } else {
                errorHtml += 'حدث خطأ غير معروف.';
            }
            
            errorHtml += `<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>`;
            
            resultDiv.innerHTML = errorHtml;
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        resultDiv.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>✗ خطأ!</strong><br>
                حدث خطأ أثناء الاختبار: ${error.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        `;
    });
}

// تحديث hint عند تغيير Provider
const providerSelect = document.getElementById('provider');
const apiKeyHint = document.getElementById('api_key_hint');

if (providerSelect && apiKeyHint) {
    providerSelect.addEventListener('change', function() {
        const provider = this.value;
        let hint = '';
        
        if (provider === 'google') {
            hint = '<strong>📍 للحصول على API Key:</strong> اذهب إلى <a href="https://aistudio.google.com/app/api-keys" target="_blank">Google AI Studio</a> → API Keys → Copy Key';
        } else if (provider === 'openai') {
            hint = '<strong>📍 للحصول على API Key:</strong> اذهب إلى <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a> → API Keys → Create new secret key';
        } else if (provider === 'openrouter') {
            hint = '<strong>📍 للحصول على API Key مجاني:</strong> اذهب إلى <a href="https://openrouter.ai/keys" target="_blank">openrouter.ai/keys</a> → Create Key<br><span class="text-success">✅ لا يحتاج بطاقة ائتمان | ✅ الموديلات المجانية متاحة فوراً</span>';
        } else {
            hint = 'أدخل مفتاح API الخاص بالمزود';
        }
        
        apiKeyHint.innerHTML = hint;
    });
}
</script>
@stop
