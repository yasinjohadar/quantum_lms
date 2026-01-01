<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>نسيت كلمة المرور - Quantum LMS</title>
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
            --bg-body: #f3f4f6;
            --danger: #ef4444;
            --success: #10b981;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
        }

        body {
            min-height: 100vh;
            font-family: 'Cairo', system-ui, -apple-system, sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .login-shell {
            position: relative;
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 40px 32px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        @media (min-width: 480px) {
            .login-card {
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
            border-radius: 12px;
        }

        .brand-mark-icon {
            color: white;
            font-weight: 700;
            font-size: 28px;
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
            background: #ecfdf5;
            border: 1px solid #10b981;
            border-radius: 20px;
            color: #065f46;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .badge-dot {
            width: 6px;
            height: 6px;
            background: #10b981;
            border-radius: 50%;
        }

        .alert {
            font-size: 13px;
            padding: 12px 16px;
            margin-bottom: 16px;
            border: 1px solid transparent;
            border-radius: 8px;
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
            border-radius: 8px;
            background: var(--bg);
            color: var(--text-main);
            font-size: 14px;
            font-weight: 500;
            outline: none;
            font-family: 'Cairo', sans-serif;
            transition: all 0.2s;
        }

        .field-control input::placeholder {
            color: var(--text-muted);
            font-weight: 400;
        }

        .field-control input:focus {
            border-color: var(--primary);
            outline: 2px solid rgba(79, 70, 229, 0.1);
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

        .field-error {
            margin-top: 6px;
            font-size: 12px;
            color: var(--danger);
            font-weight: 500;
        }

        .field-error ul {
            padding-right: 20px;
            margin: 0;
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
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-family: 'Cairo', sans-serif;
            transition: all 0.2s;
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
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
</head>
<body>
<div class="login-shell">
    <div class="login-card">
        <div class="brand">
            <div class="brand-mark">
                <div class="brand-mark-icon">Q</div>
            </div>
            <div class="brand-title">Quantum LMS</div>
            <div class="brand-subtitle">استعادة كلمة المرور</div>
        </div>

        <div class="badge-pill">
            <span class="badge-dot"></span>
            <span>استعادة آمنة لكلمة المرور</span>
        </div>

        <div class="heading">
            <div class="heading-main">نسيت كلمة المرور؟</div>
            <div class="heading-sub">لا مشكلة. فقط أخبرنا بريدك الإلكتروني وسنرسل لك رابط إعادة تعيين كلمة المرور</div>
        </div>

        {{-- Session Status --}}
        @if (session('status'))
            <div class="alert alert-success">
                <div class="alert-icon">✓</div>
                <div class="alert-body">
                    <div class="alert-title">تم بنجاح</div>
                    <div class="alert-text">{{ session('status') }}</div>
                </div>
            </div>
        @endif

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

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

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
                        autofocus
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

            <div class="actions">
                <button type="submit" class="btn-primary">
                    <span>إرسال رابط إعادة تعيين كلمة المرور</span>
                    <span class="btn-primary-icon">→</span>
                </button>
            </div>

            <div class="meta">
                <a href="{{ route('login') }}">← العودة إلى تسجيل الدخول</a>
            </div>
        </form>

        <div class="footer-note">
            Quantum LMS &copy; {{ date('Y') }} &mdash; جميع الحقوق محفوظة.
        </div>
    </div>
</div>
</body>
</html>
