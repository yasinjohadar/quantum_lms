<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إنشاء حساب - Quantum LMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --bg: #ffffff;
            --bg-light: #f9fafb;
            --danger: #ef4444;
            --success: #10b981;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: 'Cairo', system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .register-shell {
            position: relative;
            width: 100%;
            max-width: 420px;
        }

        .register-card {
            background: var(--bg);
            border: 1px solid var(--border);
            padding: 40px 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        @media (min-width: 480px) {
            .register-card {
                padding: 48px 40px;
            }
        }

        .brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            margin-bottom: 32px;
        }

        .brand-mark {
            width: 64px;
            height: 64px;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--primary-dark);
        }

        .brand-mark-icon {
            color: white;
            font-weight: 700;
            font-size: 24px;
        }

        .brand-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-main);
        }

        .brand-subtitle {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .heading {
            margin-bottom: 24px;
        }

        .heading-main {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--text-main);
        }

        .heading-sub {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 400;
            line-height: 1.6;
        }

        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: #eff6ff;
            border: 1px solid var(--primary);
            color: #1e40af;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .badge-dot {
            width: 6px;
            height: 6px;
            background: var(--primary);
        }

        .alert {
            font-size: 13px;
            padding: 12px 16px;
            margin-bottom: 16px;
            border: 1px solid transparent;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .alert-success {
            background: #ecfdf5;
            border-color: #10b981;
            color: #065f46;
        }

        .alert-danger {
            background: #fef2f2;
            border-color: var(--danger);
            color: #991b1b;
        }

        .alert-icon {
            margin-top: 2px;
            font-size: 16px;
            font-weight: 700;
        }

        .alert-body {
            flex: 1;
        }

        .alert-title {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .alert-text {
            opacity: 0.9;
            line-height: 1.5;
        }

        form {
            margin-top: 20px;
        }

        .field {
            margin-bottom: 20px;
        }

        .field-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .field-label span {
            font-weight: 600;
            color: var(--text-main);
        }

        .field-control {
            position: relative;
        }

        .field-control input {
            width: 100%;
            padding: 12px 16px;
            padding-left: 44px;
            padding-right: 16px;
            border: 1px solid var(--border);
            background: var(--bg);
            color: var(--text-main);
            font-size: 14px;
            font-weight: 500;
            outline: none;
            font-family: 'Cairo', sans-serif;
        }

        .field-control input[type="password"] {
            padding-right: 48px;
        }

        .field-control input::placeholder {
            color: var(--text-muted);
            font-weight: 400;
        }

        .field-control input:focus {
            border-color: var(--primary);
            outline: 2px solid var(--primary);
            outline-offset: -2px;
        }

        .field-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: var(--text-muted);
            pointer-events: none;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: var(--text-muted);
            background: transparent;
            border: none;
            cursor: pointer;
            z-index: 10;
        }

        .password-toggle:hover {
            color: var(--primary);
        }

        .field-error {
            margin-top: 6px;
            font-size: 12px;
            color: var(--danger);
            font-weight: 500;
        }

        .field-error ul {
            padding-right: 20px;
        }

        .field-error li {
            list-style: disc;
            line-height: 1.6;
        }

        .actions {
            margin-top: 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn-primary {
            width: 100%;
            border: none;
            padding: 14px 24px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            color: white;
            background: var(--primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-family: 'Cairo', sans-serif;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-primary:active {
            background: var(--primary-dark);
        }

        .btn-primary-icon {
            font-size: 16px;
        }

        .meta {
            margin-top: 20px;
            font-size: 12px;
            color: var(--text-muted);
            text-align: center;
        }

        .meta a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .meta a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .footer-note {
            margin-top: 24px;
            font-size: 11px;
            color: var(--text-muted);
            text-align: center;
            font-weight: 500;
        }
    </style>
    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.textContent = '👁️‍🗨️';
            } else {
                passwordInput.type = 'password';
                toggleIcon.textContent = '👁';
            }
        }

        // Phone number validation
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('phone');
            const phoneError = document.getElementById('phone-error');
            
            if (phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    let value = e.target.value;
                    
                    // Remove any non-digit characters except +
                    if (value.length > 0 && value[0] !== '+') {
                        value = '+' + value.replace(/[^0-9]/g, '');
                    } else {
                        value = value[0] + value.slice(1).replace(/[^0-9]/g, '');
                    }
                    
                    e.target.value = value;
                    
                    // Validate format
                    const pattern = /^\+[1-9]\d{1,14}$/;
                    if (value.length > 0 && !pattern.test(value)) {
                        if (phoneError) {
                            phoneError.textContent = 'يجب أن يبدأ الرقم بـ + متبوعاً برمز الدولة (مثال: +966501234567)';
                            phoneError.style.display = 'block';
                        }
                    } else {
                        if (phoneError) {
                            phoneError.style.display = 'none';
                        }
                    }
                });

                phoneInput.addEventListener('blur', function(e) {
                    const pattern = /^\+[1-9]\d{1,14}$/;
                    if (e.target.value.length > 0 && !pattern.test(e.target.value)) {
                        e.target.setCustomValidity('يجب أن يبدأ الرقم بـ + متبوعاً برمز الدولة');
                    } else {
                        e.target.setCustomValidity('');
                    }
                });
            }
        });
    </script>
</head>
<body>
<div class="register-shell">
    <div class="register-card">
        <div class="brand">
            <div class="brand-mark">
                <div class="brand-mark-icon">Q</div>
            </div>
            <div class="brand-title">Quantum LMS</div>
            <div class="brand-subtitle">لوحة تحكم الإدارة</div>
        </div>

        <div class="badge-pill">
            <span class="badge-dot"></span>
            <span>إنشاء حساب جديد</span>
        </div>

        <div class="heading">
            <div class="heading-main">مرحباً بك</div>
            <div class="heading-sub">قم بإنشاء حساب جديد للوصول إلى لوحة التحكم</div>
        </div>

        {{-- Global Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <div class="alert-icon">!</div>
                <div class="alert-body">
                    <div class="alert-title">حدثت بعض الأخطاء</div>
                    <div class="alert-text">
                        يرجى مراجعة الحقول أدناه والتأكد من صحة البيانات.
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            {{-- Name --}}
            <div class="field">
                <div class="field-label">
                    <span>الاسم الكامل</span>
                </div>
                <div class="field-control">
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="أدخل اسمك الكامل"
                    >
                    <div class="field-icon">👤</div>
                </div>
                @if ($errors->has('name'))
                    <div class="field-error">
                        <ul>
                            @foreach ($errors->get('name') as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- Email --}}
            <div class="field">
                <div class="field-label">
                    <span>البريد الإلكتروني</span>
                </div>
                <div class="field-control">
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="username"
                        placeholder="name@example.com"
                    >
                    <div class="field-icon">✉</div>
                </div>
                @if ($errors->has('email'))
                    <div class="field-error">
                        <ul>
                            @foreach ($errors->get('email') as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- Phone --}}
            <div class="field" id="phone-field">
                <div class="field-label">
                    <span>رقم الهاتف @if(isset($phoneVerificationEnabled) && $phoneVerificationEnabled)<span class="text-danger">*</span>@endif</span>
                </div>
                <div class="field-control">
                    <input
                        id="phone"
                        type="tel"
                        name="phone"
                        value="{{ old('phone') }}"
                        @if(isset($phoneVerificationEnabled) && $phoneVerificationEnabled)required @endif
                        autocomplete="tel"
                        placeholder="+966501234567"
                        pattern="^\+[1-9]\d{1,14}$"
                    >
                    <div class="field-icon">📱</div>
                </div>
                <div class="field-error" style="display: none;" id="phone-error"></div>
                @if ($errors->has('phone'))
                    <div class="field-error">
                        <ul>
                            @foreach ($errors->get('phone') as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <small style="display: block; margin-top: 6px; font-size: 12px; color: var(--text-muted);">
                    يجب أن يبدأ الرقم بـ + متبوعاً برمز الدولة (مثال: +966501234567)
                    @if(isset($phoneVerificationEnabled) && $phoneVerificationEnabled)
                        <span class="text-danger">* مطلوب للتحقق من الحساب</span>
                    @endif
                </small>
            </div>

            {{-- Password --}}
            <div class="field">
                <div class="field-label">
                    <span>كلمة المرور</span>
                </div>
                <div class="field-control">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    >
                    <div class="field-icon">🔒</div>
                    <button type="button" class="password-toggle" onclick="togglePassword('password', 'password-toggle-icon')" aria-label="إظهار/إخفاء كلمة المرور">
                        <span id="password-toggle-icon">👁</span>
                    </button>
                </div>
                @if ($errors->has('password'))
                    <div class="field-error">
                        <ul>
                            @foreach ($errors->get('password') as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- Confirm Password --}}
            <div class="field">
                <div class="field-label">
                    <span>تأكيد كلمة المرور</span>
                </div>
                <div class="field-control">
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    >
                    <div class="field-icon">🔒</div>
                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation', 'password-confirm-toggle-icon')" aria-label="إظهار/إخفاء كلمة المرور">
                        <span id="password-confirm-toggle-icon">👁</span>
                    </button>
                </div>
                @if ($errors->has('password_confirmation'))
                    <div class="field-error">
                        <ul>
                            @foreach ($errors->get('password_confirmation') as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="actions">
                <button type="submit" class="btn-primary">
                    <span>إنشاء حساب</span>
                    <span class="btn-primary-icon">→</span>
                </button>
            </div>

            <div class="meta">
                <span>لديك حساب بالفعل؟</span>
                <a href="{{ route('login') }}">تسجيل الدخول</a>
            </div>
        </form>

        <div class="footer-note">
            Quantum LMS &copy; {{ date('Y') }} &mdash; جميع الحقوق محفوظة.
        </div>
    </div>
</div>
</body>
</html>