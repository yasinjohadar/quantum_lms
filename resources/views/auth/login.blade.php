<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول - Quantum LMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-soft: rgba(79, 70, 229, 0.08);
            --bg: #0f172a;
            --card-bg: #020617;
            --card-border: rgba(148, 163, 184, 0.2);
            --text-main: #e5e7eb;
            --text-muted: #9ca3af;
            --danger: #ef4444;
            --success: #22c55e;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: radial-gradient(circle at top left, #1e293b, #020617 55%, #000 100%);
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

        .login-glow {
            position: absolute;
            inset: -40px;
            background:
                radial-gradient(circle at top right, rgba(79, 70, 229, 0.25), transparent 55%),
                radial-gradient(circle at bottom left, rgba(59, 130, 246, 0.2), transparent 55%);
            filter: blur(18px);
            opacity: 0.9;
            z-index: 0;
        }

        .login-card {
            position: relative;
            z-index: 1;
            background: radial-gradient(circle at top center, rgba(15, 23, 42, 0.98), #020617);
            border-radius: 22px;
            border: 1px solid var(--card-border);
            box-shadow:
                0 18px 45px rgba(15, 23, 42, 0.9),
                0 0 0 1px rgba(15, 23, 42, 0.9);
            padding: 28px 26px 26px;
            backdrop-filter: blur(22px);
        }

        @media (min-width: 480px) {
            .login-card {
                padding: 32px 30px 30px;
            }
        }

        .brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .brand-mark {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            background:
                conic-gradient(from 210deg, #4f46e5, #22d3ee, #22c55e, #4f46e5);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow:
                0 0 0 1px rgba(15, 23, 42, 0.6),
                0 12px 30px rgba(15, 23, 42, 0.9);
            position: relative;
            overflow: hidden;
        }

        .brand-mark::after {
            content: "";
            position: absolute;
            inset: 1px;
            border-radius: 17px;
            background: radial-gradient(circle at 20% 0, rgba(255, 255, 255, 0.35), transparent 55%), #020617;
        }

        .brand-mark-icon {
            position: relative;
            width: 24px;
            height: 24px;
            border-radius: 999px;
            border: 2px solid rgba(148, 163, 184, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e5e7eb;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.8);
        }

        .brand-title {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .brand-subtitle {
            font-size: 13px;
            color: var(--text-muted);
        }

        .heading {
            margin-bottom: 18px;
        }

        .heading-main {
            font-size: 19px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .heading-sub {
            font-size: 13px;
            color: var(--text-muted);
        }

        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(22, 163, 74, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.35);
            color: #bbf7d0;
            font-size: 11px;
            margin-bottom: 10px;
        }

        .badge-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: #22c55e;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.25);
        }

        .alert {
            font-size: 13px;
            border-radius: 12px;
            padding: 9px 11px;
            margin-bottom: 12px;
            border: 1px solid transparent;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .alert-success {
            background: rgba(22, 163, 74, 0.08);
            border-color: rgba(34, 197, 94, 0.45);
            color: #bbf7d0;
        }

        .alert-danger {
            background: rgba(248, 113, 113, 0.08);
            border-color: rgba(248, 113, 113, 0.45);
            color: #fecaca;
        }

        .alert-icon {
            margin-top: 1px;
            font-size: 14px;
        }

        .alert-body {
            flex: 1;
        }

        .alert-title {
            font-weight: 600;
            margin-bottom: 2px;
        }

        .alert-text {
            opacity: 0.9;
        }

        form {
            margin-top: 12px;
        }

        .field {
            margin-bottom: 14px;
        }

        .field-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .field-label span {
            font-weight: 500;
            color: #e5e7eb;
        }

        .field-control {
            position: relative;
        }

        .field-control input {
            width: 100%;
            padding: 10px 11px;
            padding-left: 36px;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.5);
            background-color: rgba(15, 23, 42, 0.9);
            color: var(--text-main);
            font-size: 13px;
            outline: none;
            transition: border-color 0.16s ease, box-shadow 0.16s ease, background-color 0.16s ease;
        }

        .field-control input::placeholder {
            color: rgba(148, 163, 184, 0.8);
        }

        .field-control input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 1px rgba(79, 70, 229, 0.5), 0 0 0 6px rgba(79, 70, 229, 0.15);
            background-color: rgba(15, 23, 42, 0.98);
        }

        .field-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: #9ca3af;
            background: radial-gradient(circle at 0 0, rgba(248, 250, 252, 0.16), transparent 55%);
        }

        .field-error {
            margin-top: 4px;
            font-size: 11px;
            color: #fecaca;
        }

        .field-error ul {
            padding-right: 18px;
        }

        .field-error li {
            list-style: disc;
        }

        .row-inline {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-top: 6px;
            margin-bottom: 4px;
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .remember input[type="checkbox"] {
            width: 14px;
            height: 14px;
            border-radius: 4px;
            border: 1px solid rgba(156, 163, 175, 0.85);
            background-color: transparent;
            accent-color: var(--primary);
        }

        .link-muted {
            font-size: 12px;
            color: rgba(199, 210, 254, 0.85);
            text-decoration: none;
            transition: color 0.15s ease, opacity 0.15s ease;
        }

        .link-muted:hover {
            color: #e5e7eb;
            opacity: 1;
        }

        .actions {
            margin-top: 14px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .btn-primary {
            width: 100%;
            border: none;
            border-radius: 999px;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            color: white;
            background: radial-gradient(circle at 0 0, #6366f1, #4f46e5);
            box-shadow:
                0 0 0 1px rgba(129, 140, 248, 0.5),
                0 14px 35px rgba(15, 23, 42, 0.95);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: transform 0.12s ease, box-shadow 0.12s ease, background 0.16s ease, filter 0.12s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            filter: brightness(1.05);
            box-shadow:
                0 0 0 1px rgba(129, 140, 248, 0.75),
                0 16px 40px rgba(15, 23, 42, 0.98);
        }

        .btn-primary:active {
            transform: translateY(0);
            box-shadow:
                0 0 0 1px rgba(129, 140, 248, 0.7),
                0 10px 28px rgba(15, 23, 42, 0.96);
        }

        .btn-primary-icon {
            width: 18px;
            height: 18px;
            border-radius: 999px;
            border: 1px solid rgba(199, 210, 254, 0.7);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            background: radial-gradient(circle at 0 0, rgba(248, 250, 252, 0.28), transparent 55%);
        }

        .meta {
            margin-top: 10px;
            font-size: 11px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .meta span {
            opacity: 0.9;
        }

        .meta a {
            color: rgba(165, 180, 252, 0.85);
            text-decoration: none;
        }

        .meta a:hover {
            color: #e5e7eb;
        }

        .footer-note {
            margin-top: 16px;
            font-size: 10px;
            color: rgba(148, 163, 184, 0.85);
            text-align: center;
        }
    </style>
</head>
<body>
<div class="login-shell">
    <div class="login-glow"></div>

    <div class="login-card">
        <div class="brand">
            <div class="brand-mark">
                <div class="brand-mark-icon">Q</div>
            </div>
            <div class="brand-title">Quantum LMS</div>
            <div class="brand-subtitle">لوحة تحكم الإدارة</div>
        </div>

        <div class="badge-pill">
            <span class="badge-dot"></span>
            <span>تسجيل دخول آمن إلى حسابك</span>
        </div>

        <div class="heading">
            <div class="heading-main">مرحباً بعودتك</div>
            <div class="heading-sub">قم بإدخال بياناتك للوصول إلى لوحة التحكم</div>
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

        <form method="POST" action="{{ route('login') }}">
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
                    <div class="field-icon">@</div>
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
                        autocomplete="current-password"
                        placeholder="••••••••"
                    >
                    <div class="field-icon">●●</div>
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

            <div class="row-inline">
                <label class="remember" for="remember_me">
                    <input id="remember_me" type="checkbox" name="remember">
                    <span>تذكرني في هذا الجهاز</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="link-muted" href="{{ route('password.request') }}">
                        نسيت كلمة المرور؟
                    </a>
                @endif
            </div>

            <div class="actions">
                <button type="submit" class="btn-primary">
                    <span>تسجيل الدخول</span>
                    <span class="btn-primary-icon">→</span>
                </button>
            </div>

            <div class="meta">
                <span>معلومات الدخول الخاصة بك سرية.</span>
                @if (Route::has('register'))
                    <span>
                        لا تملك حساباً؟
                        <a href="{{ route('register') }}">إنشاء حساب</a>
                    </span>
                @endif
            </div>
        </form>

        <div class="footer-note">
            Quantum LMS &copy; {{ date('Y') }} &mdash; جميع الحقوق محفوظة.
        </div>
    </div>
</div>
</body>
</html>
