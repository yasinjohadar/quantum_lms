@extends('admin.layouts.master')

@section('page-title')
    إعدادات البريد الإلكتروني
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إعدادات البريد الإلكتروني</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">إعدادات البريد</li>
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
                        <h5 class="card-title mb-0">إعدادات SMTP</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.email-settings.update') }}" method="POST" id="email-settings-form">
                            @csrf
                            @method('POST')

                            <div class="mb-3">
                                <label for="mail_driver" class="form-label">نوع البريد <span class="text-danger">*</span></label>
                                <select class="form-select @error('mail_driver') is-invalid @enderror" id="mail_driver" name="mail_driver" required>
                                    <option value="smtp" {{ old('mail_driver', $settings['mail_driver'] ?? 'smtp') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                    <option value="sendmail" {{ old('mail_driver', $settings['mail_driver'] ?? 'smtp') == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                    <option value="log" {{ old('mail_driver', $settings['mail_driver'] ?? 'smtp') == 'log' ? 'selected' : '' }}>Log (للتطوير)</option>
                                </select>
                                @error('mail_driver')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div id="smtp-fields">
                                <div class="mb-3">
                                    <label for="smtp_host" class="form-label">خادم SMTP <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('smtp_host') is-invalid @enderror" id="smtp_host" name="smtp_host" value="{{ old('smtp_host', $settings['smtp_host'] ?? '') }}" placeholder="smtp.example.com">
                                    @error('smtp_host')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="smtp_port" class="form-label">منفذ SMTP <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('smtp_port') is-invalid @enderror" id="smtp_port" name="smtp_port" value="{{ old('smtp_port', $settings['smtp_port'] ?? '587') }}" min="1" max="65535">
                                        @error('smtp_port')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="smtp_encryption" class="form-label">التشفير <span class="text-danger">*</span></label>
                                        <select class="form-select @error('smtp_encryption') is-invalid @enderror" id="smtp_encryption" name="smtp_encryption">
                                            <option value="none" {{ old('smtp_encryption', $settings['smtp_encryption'] ?? 'tls') == 'none' ? 'selected' : '' }}>بدون</option>
                                            <option value="tls" {{ old('smtp_encryption', $settings['smtp_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS</option>
                                            <option value="ssl" {{ old('smtp_encryption', $settings['smtp_encryption'] ?? 'tls') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                        </select>
                                        @error('smtp_encryption')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="smtp_username" class="form-label">اسم المستخدم</label>
                                    <input type="text" class="form-control @error('smtp_username') is-invalid @enderror" id="smtp_username" name="smtp_username" value="{{ old('smtp_username', $settings['smtp_username'] ?? '') }}">
                                    @error('smtp_username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="smtp_password" class="form-label">كلمة المرور</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('smtp_password') is-invalid @enderror" id="smtp_password" name="smtp_password" placeholder="اتركه فارغاً للاحتفاظ بالقيمة الحالية">
                                        <button class="btn btn-outline-secondary" type="button" id="toggle-password">
                                            <i class="fas fa-eye" id="toggle-icon"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">اتركه فارغاً للاحتفاظ بكلمة المرور الحالية</small>
                                    @error('smtp_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label for="mail_from_address" class="form-label">عنوان المرسل <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('mail_from_address') is-invalid @enderror" id="mail_from_address" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? '') }}" required>
                                @error('mail_from_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="mail_from_name" class="form-label">اسم المرسل <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('mail_from_name') is-invalid @enderror" id="mail_from_name" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? '') }}" required>
                                @error('mail_from_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="mail_reply_to" class="form-label">عنوان الرد (Reply To)</label>
                                <input type="email" class="form-control @error('mail_reply_to') is-invalid @enderror" id="mail_reply_to" name="mail_reply_to" value="{{ old('mail_reply_to', $settings['mail_reply_to'] ?? '') }}">
                                @error('mail_reply_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div id="test-connection-result" class="mt-3" style="display: none;"></div>

                            <div class="d-flex gap-2">
                                <button type="button" id="test-connection-btn" class="btn btn-info">
                                    <i class="fas fa-plug me-1"></i> اختبار الاتصال
                                </button>
                                <button type="button" id="send-test-email-btn" class="btn btn-warning">
                                    <i class="fas fa-paper-plane me-1"></i> إرسال إيميل تجريبي
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

<!-- Modal for Test Email -->
<div class="modal fade" id="testEmailModal" tabindex="-1" aria-labelledby="testEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testEmailModalLabel">إرسال إيميل تجريبي</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="test_email" class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="test_email" placeholder="example@example.com" required>
                </div>
                <div class="mb-3">
                    <label for="test_message" class="form-label">الرسالة (اختياري)</label>
                    <textarea class="form-control" id="test_message" rows="3" placeholder="هذا إيميل اختبار من نظام Quantum LMS"></textarea>
                </div>
                <div id="test-email-result" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="send-test-email-submit">إرسال</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mailDriver = document.getElementById('mail_driver');
    const smtpFields = document.getElementById('smtp-fields');
    const togglePassword = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('smtp_password');
    const testConnectionBtn = document.getElementById('test-connection-btn');
    const sendTestEmailBtn = document.getElementById('send-test-email-btn');
    const testEmailModal = new bootstrap.Modal(document.getElementById('testEmailModal'));
    const sendTestEmailSubmit = document.getElementById('send-test-email-submit');

    // Toggle SMTP fields
    function updateSMTPFields() {
        if (mailDriver.value === 'smtp') {
            smtpFields.style.display = 'block';
            smtpFields.querySelectorAll('input, select').forEach(el => {
                if (el.hasAttribute('required')) {
                    el.setAttribute('required', 'required');
                }
            });
        } else {
            smtpFields.style.display = 'none';
            smtpFields.querySelectorAll('input, select').forEach(el => {
                el.removeAttribute('required');
            });
        }
    }

    mailDriver.addEventListener('change', updateSMTPFields);
    updateSMTPFields();

    // Toggle password visibility
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        const icon = document.getElementById('toggle-icon');
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
        formData.append('smtp_host', document.getElementById('smtp_host').value);
        formData.append('smtp_port', document.getElementById('smtp_port').value);
        formData.append('smtp_encryption', document.getElementById('smtp_encryption').value);
        formData.append('smtp_username', document.getElementById('smtp_username').value);
        formData.append('smtp_password', document.getElementById('smtp_password').value);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("admin.email-settings.test-connection") }}', {
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

    // Open test email modal
    sendTestEmailBtn.addEventListener('click', function() {
        testEmailModal.show();
    });

    // Send test email
    sendTestEmailSubmit.addEventListener('click', function() {
        const email = document.getElementById('test_email').value;
        const message = document.getElementById('test_message').value || 'هذا إيميل اختبار من نظام Quantum LMS';
        
        if (!email) {
            alert('يرجى إدخال البريد الإلكتروني');
            return;
        }

        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> جاري الإرسال...';

        const formData = new FormData();
        formData.append('email', email);
        formData.append('message', message);
        
        // Add current form settings
        formData.append('mail_driver', document.getElementById('mail_driver').value);
        formData.append('smtp_host', document.getElementById('smtp_host').value);
        formData.append('smtp_port', document.getElementById('smtp_port').value);
        formData.append('smtp_encryption', document.getElementById('smtp_encryption').value);
        formData.append('smtp_username', document.getElementById('smtp_username').value);
        formData.append('smtp_password', document.getElementById('smtp_password').value);
        formData.append('mail_from_address', document.getElementById('mail_from_address').value);
        formData.append('mail_from_name', document.getElementById('mail_from_name').value);
        formData.append('mail_reply_to', document.getElementById('mail_reply_to').value);
        
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("admin.email-settings.send-test") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const resultDiv = document.getElementById('test-email-result');
            resultDiv.style.display = 'block';
            if (data.success) {
                resultDiv.className = 'alert alert-success';
                resultDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + data.message;
                document.getElementById('test_email').value = '';
                document.getElementById('test_message').value = '';
                setTimeout(() => {
                    testEmailModal.hide();
                }, 2000);
            } else {
                resultDiv.className = 'alert alert-danger';
                resultDiv.innerHTML = '<i class="fas fa-times-circle me-2"></i>' + data.message;
            }
        })
        .catch(error => {
            const resultDiv = document.getElementById('test-email-result');
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

