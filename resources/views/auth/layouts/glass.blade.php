<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'أكاديمية كوانتم التعليمية')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-1: #090f1f;
            --bg-2: #10172d;
            --bg-3: #1a2240;
            --glass-bg: rgba(15, 23, 42, 0.62);
            --glass-border: rgba(148, 163, 184, 0.22);
            --text-main: #f8fafc;
            --text-muted: #cbd5e1;
            --primary: #6366f1;
            --primary-hover: #7c3aed;
            --gold-1: #caa43a;
            --gold-2: #e5c85c;
            --gold-3: #f4dd86;
            --danger: #fca5a5;
            --danger-bg: rgba(239, 68, 68, 0.16);
            --success: #86efac;
            --success-bg: rgba(34, 197, 94, 0.14);
            --input-bg: rgba(15, 23, 42, 0.55);
            --input-border: rgba(148, 163, 184, 0.35);
            --shadow: 0 35px 80px rgba(2, 6, 23, 0.55);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            min-height: 100%;
        }

        body {
            font-family: 'Cairo', system-ui, -apple-system, sans-serif;
            color: var(--text-main);
            background:
                radial-gradient(900px 480px at 85% -10%, rgba(99, 102, 241, 0.28), transparent 62%),
                radial-gradient(700px 420px at -10% 15%, rgba(14, 165, 233, 0.24), transparent 60%),
                linear-gradient(135deg, var(--bg-1) 0%, var(--bg-2) 45%, var(--bg-3) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            backdrop-filter: blur(2px);
            pointer-events: none;
        }

        .auth-shell {
            width: 100%;
            max-width: 460px;
            position: relative;
            z-index: 1;
        }

        .auth-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow);
            border-radius: 26px;
            padding: 36px 28px;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        @media (min-width: 540px) {
            .auth-card {
                padding: 42px 34px;
            }
        }

        .auth-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            margin-bottom: 22px;
            text-decoration: none;
            color: inherit;
            transition: opacity .2s ease, transform .2s ease;
        }

        .auth-brand:hover {
            opacity: 0.92;
            transform: translateY(-1px);
            color: inherit;
            text-decoration: none;
        }

        .auth-logo {
            width: 76px;
            height: 76px;
            border-radius: 18px;
            padding: 8px;
            background: #000000;
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.28);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .auth-title {
            font-size: 27px;
            font-weight: 800;
            letter-spacing: 0.2px;
        }

        .auth-subtitle {
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 600;
        }

        .auth-heading {
            margin-bottom: 20px;
            text-align: center;
        }

        .auth-heading h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .auth-heading p {
            color: var(--text-muted);
            line-height: 1.8;
            font-size: 13px;
        }

        .auth-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 999px;
            margin-bottom: 12px;
            padding: 7px 14px;
            border: 1px solid rgba(129, 140, 248, 0.55);
            background: rgba(99, 102, 241, 0.18);
            color: #c7d2fe;
            font-size: 12px;
            font-weight: 700;
        }

        .auth-alert {
            border-radius: 14px;
            border: 1px solid transparent;
            padding: 12px 14px;
            margin-bottom: 14px;
            font-size: 13px;
            line-height: 1.7;
        }

        .auth-alert-danger {
            background: var(--danger-bg);
            color: #fecaca;
            border-color: rgba(239, 68, 68, 0.42);
        }

        .auth-alert-success {
            background: var(--success-bg);
            color: #dcfce7;
            border-color: rgba(34, 197, 94, 0.4);
        }

        .auth-field {
            margin-bottom: 14px;
        }

        .auth-label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            color: #e2e8f0;
            font-weight: 600;
        }

        .auth-label-small {
            font-size: 12px;
            margin-bottom: 6px;
            color: #cbd5e1;
        }

        .auth-inline-group {
            display: grid;
            grid-template-columns: 180px 1fr;
            gap: 10px;
            align-items: end;
        }

        .auth-inline-item {
            min-width: 0;
        }

        .native-country-select {
            position: absolute;
            opacity: 0;
            pointer-events: none;
            width: 1px;
            height: 1px;
        }

        .country-select {
            position: relative;
        }

        .country-select-trigger {
            width: 100%;
            border: 1px solid var(--input-border);
            background: var(--input-bg);
            color: var(--text-main);
            border-radius: 12px;
            min-height: 48px;
            padding: 10px 36px 10px 12px;
            font-size: 14px;
            font-family: inherit;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            text-align: right;
        }

        .country-select-trigger::after {
            content: "▾";
            position: absolute;
            left: 12px;
            color: #9fb0ca;
            font-size: 12px;
        }

        .country-select-flag {
            width: 22px;
            height: 16px;
            border-radius: 3px;
            object-fit: cover;
            flex: 0 0 auto;
            box-shadow: 0 0 0 1px rgba(148, 163, 184, 0.35);
        }

        .country-select-menu {
            position: absolute;
            top: calc(100% + 6px);
            right: 0;
            left: 0;
            z-index: 20;
            background: #4b5563;
            border: 1px solid rgba(148, 163, 184, 0.35);
            border-radius: 10px;
            max-height: 220px;
            overflow-y: auto;
            display: none;
        }

        .country-select.open .country-select-menu {
            display: block;
        }

        .country-select-option {
            width: 100%;
            background: transparent;
            border: none;
            color: #fff;
            text-align: right;
            padding: 9px 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 14px;
            font-family: inherit;
        }

        .country-select-option:hover {
            background: #2563eb;
        }

        .auth-control {
            position: relative;
        }

        .auth-input {
            width: 100%;
            border: 1px solid var(--input-border);
            background: var(--input-bg);
            color: var(--text-main);
            border-radius: 12px;
            min-height: 48px;
            padding: 12px 14px 12px 42px;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
        }

        .auth-input-country {
            padding-right: 46px;
        }

        .auth-input::placeholder {
            color: #93a3bc;
        }

        .auth-input:focus {
            border-color: rgba(129, 140, 248, 0.92);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.22);
            background: rgba(15, 23, 42, 0.72);
        }

        .auth-input.invalid {
            border-color: rgba(248, 113, 113, 0.9);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
        }

        .auth-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9fb0ca;
            font-size: 15px;
            pointer-events: none;
        }

        .country-flag-preview {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 22px;
            height: 16px;
            border-radius: 3px;
            object-fit: cover;
            box-shadow: 0 0 0 1px rgba(148, 163, 184, 0.35);
            pointer-events: none;
        }

        .country-flag-fallback {
            width: 22px;
            height: 16px;
            border-radius: 3px;
            font-size: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(148, 163, 184, 0.15);
            color: #cbd5e1;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #9fb0ca;
            cursor: pointer;
            font-size: 16px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            transition: color .2s ease, background-color .2s ease;
        }

        /* زر إظهار/إخفاء كلمة المرور على طرف الحقل (يسار في CSS؛ يُستخدم بدل أيقونة القفل في كل صفحات المصادقة) */
        .auth-control--toggle-start .password-toggle {
            right: auto;
            left: 10px;
        }

        .password-toggle:hover {
            color: #dbeafe;
            background: rgba(148, 163, 184, 0.14);
        }

        .auth-error {
            margin-top: 6px;
            color: #fecaca;
            font-size: 12px;
            line-height: 1.6;
        }

        .auth-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 10px 0 14px;
            flex-wrap: wrap;
            font-size: 13px;
        }

        .auth-checkbox {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            cursor: pointer;
        }

        .auth-checkbox input {
            accent-color: var(--primary);
        }

        .auth-link {
            color: #c7d2fe;
            text-decoration: none;
            font-weight: 700;
            transition: color .2s ease;
        }

        .auth-link:hover {
            color: #ffffff;
            text-decoration: underline;
        }

        .auth-btn {
            width: 100%;
            border: none;
            border-radius: 13px;
            min-height: 50px;
            padding: 12px 18px;
            background: linear-gradient(135deg, var(--gold-1), var(--gold-2) 55%, var(--gold-3));
            color: #1e1b0b;
            font-size: 15px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            box-shadow: 0 14px 30px rgba(202, 164, 58, 0.35);
            transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
        }

        .auth-btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.05);
            box-shadow: 0 18px 34px rgba(229, 200, 92, 0.45);
        }

        .auth-btn:disabled {
            opacity: .7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .auth-meta {
            margin-top: 16px;
            text-align: center;
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.8;
        }

        .auth-footer {
            margin-top: 18px;
            text-align: center;
            color: #94a3b8;
            font-size: 11px;
        }

        @media (max-width: 460px) {
            .auth-card {
                border-radius: 20px;
                padding: 28px 20px;
            }

            .auth-inline-group {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @stack('styles')
    @yield('head')
</head>
<body>
<div class="auth-shell">
    <div class="auth-card">
        <a href="{{ route('home') }}" class="auth-brand" title="العودة إلى الرئيسية">
            <div class="auth-logo">
                <img src="{{ asset('frontend/images/logo.png') }}" alt="أكاديمية كوانتم التعليمية">
            </div>
            <div class="auth-title">أكاديمية كوانتم التعليمية</div>
            <div class="auth-subtitle">@yield('brand-subtitle', 'بوابة الحسابات')</div>
        </a>
        @yield('content')
    </div>
</div>
@stack('scripts')
</body>
</html>
