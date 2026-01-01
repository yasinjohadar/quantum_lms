@extends('admin.layouts.master')

@section('page-title')
    إعدادات SMS
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إعدادات SMS</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">إعدادات SMS</li>
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
                    <div class="card-header">
                        <h5 class="card-title mb-0">إعدادات SMS</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.sms-settings.update') }}" method="POST" id="sms-settings-form">
                            @csrf
                            @method('POST')

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="sms_enabled" name="sms_enabled" value="1" {{ old('sms_enabled', $settings['sms_enabled'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sms_enabled">
                                        تفعيل SMS
                                    </label>
                                </div>
                                <small class="text-muted">تفعيل أو تعطيل إرسال الرسائل SMS في النظام</small>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label for="sms_provider" class="form-label">مزود SMS <span class="text-danger">*</span></label>
                                <select class="form-select @error('sms_provider') is-invalid @enderror" id="sms_provider" name="sms_provider" required>
                                    @foreach($providers as $key => $name)
                                        <option value="{{ $key }}" {{ old('sms_provider', $settings['sms_provider'] ?? 'local_syria') == $key ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('sms_provider')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div id="local-syria-fields" style="display: {{ old('sms_provider', $settings['sms_provider'] ?? 'local_syria') == 'local_syria' ? 'block' : 'none' }};">
                                <div class="mb-3">
                                    <label for="local_api_url" class="form-label">عنوان API <span class="text-danger">*</span></label>
                                    <input type="url" class="form-control @error('local_api_url') is-invalid @enderror" id="local_api_url" name="local_api_url" value="{{ old('local_api_url', $settings['local_api_url'] ?? '') }}" placeholder="https://api.example.com/send">
                                    @error('local_api_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="local_api_key" class="form-label">مفتاح API <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('local_api_key') is-invalid @enderror" id="local_api_key" name="local_api_key" placeholder="اتركه فارغاً للاحتفاظ بالقيمة الحالية">
                                        <button class="btn btn-outline-secondary" type="button" id="toggle-api-key">
                                            <i class="fas fa-eye" id="toggle-api-key-icon"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">اتركه فارغاً للاحتفاظ بالمفتاح الحالي</small>
                                    @error('local_api_key')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="local_sender_id" class="form-label">معرف المرسل (Sender ID) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('local_sender_id') is-invalid @enderror" id="local_sender_id" name="local_sender_id" value="{{ old('local_sender_id', $settings['local_sender_id'] ?? '') }}" placeholder="اسم المرسل" maxlength="50">
                                    @error('local_sender_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div id="twilio-fields" style="display: {{ old('sms_provider', $settings['sms_provider'] ?? 'local_syria') == 'twilio' ? 'block' : 'none' }};">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Twilio Trial Account:</strong> يمكنك الحصول على حساب تجريبي مجاني من 
                                    <a href="https://www.twilio.com/try-twilio" target="_blank" class="alert-link">twilio.com</a>
                                    <br>
                                    الحساب التجريبي يتيح إرسال رسائل إلى أرقام معتمدة فقط.
                                </div>

                                <div class="mb-3">
                                    <label for="twilio_account_sid" class="form-label">Account SID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('twilio_account_sid') is-invalid @enderror" id="twilio_account_sid" name="twilio_account_sid" value="{{ old('twilio_account_sid', $settings['twilio_account_sid'] ?? '') }}" placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" pattern="^AC[a-f0-9]{32}$">
                                    <small class="text-muted">يجب أن يبدأ بـ AC متبوعاً بـ 32 حرف (يمكنك العثور عليه في Twilio Console)</small>
                                    @error('twilio_account_sid')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="twilio_auth_token" class="form-label">Auth Token <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('twilio_auth_token') is-invalid @enderror" id="twilio_auth_token" name="twilio_auth_token" placeholder="اتركه فارغاً للاحتفاظ بالقيمة الحالية">
                                        <button class="btn btn-outline-secondary" type="button" id="toggle-twilio-token">
                                            <i class="fas fa-eye" id="toggle-twilio-token-icon"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">اتركه فارغاً للاحتفاظ بالـ Token الحالي</small>
                                    @error('twilio_auth_token')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="twilio_from_number" class="form-label">رقم المرسل (From Number) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('twilio_from_number') is-invalid @enderror" id="twilio_from_number" name="twilio_from_number" value="{{ old('twilio_from_number', $settings['twilio_from_number'] ?? '') }}" placeholder="+1234567890" pattern="^\+[1-9]\d{1,14}$">
                                    <small class="text-muted">يجب أن يبدأ بـ + متبوعاً برمز الدولة (مثال: +905519665883)</small>
                                    @error('twilio_from_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div id="test-connection-result" class="mt-3" style="display: none;"></div>

                            <div class="d-flex gap-2">
                                <button type="button" id="test-connection-btn" class="btn btn-info">
                                    <i class="fas fa-plug me-1"></i> اختبار الاتصال
                                </button>
                                <button type="button" id="send-test-sms-btn" class="btn btn-warning">
                                    <i class="fas fa-paper-plane me-1"></i> إرسال رسالة تجريبية
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> حفظ
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Test SMS -->
<div class="modal fade" id="testSMSModal" tabindex="-1" aria-labelledby="testSMSModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testSMSModalLabel">إرسال رسالة SMS تجريبية</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="test_phone" class="form-label">رقم الهاتف <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="test_phone" placeholder="+905519665883" required>
                    <small class="text-muted">يجب أن يبدأ بـ + متبوعاً برمز الدولة</small>
                </div>
                <div class="mb-3">
                    <label for="test_sms_message" class="form-label">الرسالة (اختياري)</label>
                    <textarea class="form-control" id="test_sms_message" rows="3" placeholder="هذه رسالة تجريبية من نظام Quantum LMS"></textarea>
                </div>
                <div id="test-sms-result" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="send-test-sms-submit">إرسال</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const smsProvider = document.getElementById('sms_provider');
    const localSyriaFields = document.getElementById('local-syria-fields');
    const twilioFields = document.getElementById('twilio-fields');
    const toggleApiKey = document.getElementById('toggle-api-key');
    const apiKeyInput = document.getElementById('local_api_key');
    const toggleTwilioToken = document.getElementById('toggle-twilio-token');
    const twilioTokenInput = document.getElementById('twilio_auth_token');
    const testConnectionBtn = document.getElementById('test-connection-btn');
    const sendTestSMSBtn = document.getElementById('send-test-sms-btn');
    const testSMSModal = new bootstrap.Modal(document.getElementById('testSMSModal'));
    const sendTestSMSSubmit = document.getElementById('send-test-sms-submit');

    // Toggle provider fields
    function updateProviderFields() {
        if (smsProvider.value === 'twilio') {
            localSyriaFields.style.display = 'none';
            twilioFields.style.display = 'block';
            // Remove required from local fields
            localSyriaFields.querySelectorAll('input').forEach(el => el.removeAttribute('required'));
            // Add required to twilio fields
            twilioFields.querySelectorAll('input[type="text"], input[type="password"]').forEach(el => {
                if (el.id !== 'twilio_auth_token' || el.value) {
                    el.setAttribute('required', 'required');
                }
            });
        } else {
            localSyriaFields.style.display = 'block';
            twilioFields.style.display = 'none';
            // Remove required from twilio fields
            twilioFields.querySelectorAll('input').forEach(el => el.removeAttribute('required'));
            // Add required to local fields
            localSyriaFields.querySelectorAll('input[type="url"], input[type="text"]').forEach(el => {
                if (el.id !== 'local_api_key' || el.value) {
                    el.setAttribute('required', 'required');
                }
            });
        }
    }

    smsProvider.addEventListener('change', updateProviderFields);
    updateProviderFields();

    // Toggle API key visibility
    toggleApiKey.addEventListener('click', function() {
        const type = apiKeyInput.getAttribute('type') === 'password' ? 'text' : 'password';
        apiKeyInput.setAttribute('type', type);
        const icon = document.getElementById('toggle-api-key-icon');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });

    // Toggle Twilio token visibility
    toggleTwilioToken.addEventListener('click', function() {
        const type = twilioTokenInput.getAttribute('type') === 'password' ? 'text' : 'password';
        twilioTokenInput.setAttribute('type', type);
        const icon = document.getElementById('toggle-twilio-token-icon');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });

    // Test connection
    testConnectionBtn.addEventListener('click', function() {
        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> جاري الاختبار...';

        const formData = new FormData();
        const provider = document.getElementById('sms_provider').value;
        formData.append('sms_provider', provider);
        
        if (provider === 'twilio') {
            formData.append('twilio_account_sid', document.getElementById('twilio_account_sid').value);
            formData.append('twilio_auth_token', document.getElementById('twilio_auth_token').value);
            formData.append('twilio_from_number', document.getElementById('twilio_from_number').value);
        } else {
            formData.append('local_api_url', document.getElementById('local_api_url').value);
            formData.append('local_api_key', document.getElementById('local_api_key').value);
            formData.append('local_sender_id', document.getElementById('local_sender_id').value);
        }
        
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("admin.sms-settings.test-connection") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const resultDiv = document.getElementById('test-connection-result');
            resultDiv.style.display = 'block';
            if (data.success) {
                resultDiv.className = 'alert alert-success mt-3';
                resultDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + data.message;
            } else {
                resultDiv.className = 'alert alert-danger mt-3';
                resultDiv.innerHTML = '<i class="fas fa-times-circle me-2"></i>' + data.message;
            }
        })
        .catch(error => {
            const resultDiv = document.getElementById('test-connection-result');
            resultDiv.style.display = 'block';
            resultDiv.className = 'alert alert-danger mt-3';
            resultDiv.innerHTML = '<i class="fas fa-times-circle me-2"></i>حدث خطأ: ' + error.message;
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });

    // Open test SMS modal
    sendTestSMSBtn.addEventListener('click', function() {
        testSMSModal.show();
    });

    // Send test SMS
    sendTestSMSSubmit.addEventListener('click', function() {
        const phone = document.getElementById('test_phone').value;
        const message = document.getElementById('test_sms_message').value || 'هذه رسالة تجريبية من نظام Quantum LMS';
        
        if (!phone) {
            alert('يرجى إدخال رقم الهاتف');
            return;
        }

        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> جاري الإرسال...';

        const formData = new FormData();
        formData.append('phone', phone);
        formData.append('message', message);
        
        // Add current form settings
        const provider = document.getElementById('sms_provider').value;
        formData.append('sms_provider', provider);
        
        if (provider === 'twilio') {
            formData.append('twilio_account_sid', document.getElementById('twilio_account_sid').value);
            formData.append('twilio_auth_token', document.getElementById('twilio_auth_token').value);
            formData.append('twilio_from_number', document.getElementById('twilio_from_number').value);
        } else {
            formData.append('local_api_url', document.getElementById('local_api_url').value);
            formData.append('local_api_key', document.getElementById('local_api_key').value);
            formData.append('local_sender_id', document.getElementById('local_sender_id').value);
        }
        
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("admin.sms-settings.send-test") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const resultDiv = document.getElementById('test-sms-result');
            resultDiv.style.display = 'block';
            if (data.success) {
                resultDiv.className = 'alert alert-success';
                resultDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + data.message;
                document.getElementById('test_phone').value = '';
                document.getElementById('test_sms_message').value = '';
                setTimeout(() => {
                    testSMSModal.hide();
                }, 2000);
            } else {
                resultDiv.className = 'alert alert-danger';
                resultDiv.innerHTML = '<i class="fas fa-times-circle me-2"></i>' + data.message;
            }
        })
        .catch(error => {
            const resultDiv = document.getElementById('test-sms-result');
            resultDiv.style.display = 'block';
            resultDiv.className = 'alert alert-danger';
            resultDiv.innerHTML = '<i class="fas fa-times-circle me-2"></i>حدث خطأ: ' + error.message;
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });
});
</script>
@stop

