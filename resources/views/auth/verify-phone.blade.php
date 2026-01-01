<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>التحقق من رقم الهاتف - Quantum LMS</title>
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

        .field-control input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg);
            color: var(--text-main);
            font-size: 20px;
            font-weight: 600;
            text-align: center;
            letter-spacing: 8px;
            outline: none;
            font-family: 'Cairo', sans-serif;
            transition: all 0.2s;
        }

        .field-control input:focus {
            border-color: var(--primary);
            outline: 2px solid rgba(79, 70, 229, 0.1);
            outline-offset: -2px;
        }

        .field-error {
            margin-top: 6px;
            font-size: 12px;
            color: var(--danger);
            font-weight: 500;
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

        .btn-link {
            background: none;
            border: none;
            color: var(--primary);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            font-family: 'Cairo', sans-serif;
        }

        .btn-link:hover {
            text-decoration: underline;
        }

        .meta {
            margin-top: 20px;
            font-size: 12px;
            color: var(--text-muted);
            text-align: center;
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
        </div>

        <div class="heading">
            <div class="heading-main">التحقق من رقم الهاتف</div>
            <div class="heading-sub">أدخل رمز التحقق الذي تم إرساله إلى رقم هاتفك</div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                <div class="alert-icon">✓</div>
                <div class="alert-body">{{ session('success') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <div class="alert-icon">!</div>
                <div class="alert-body">
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('phone.verify') }}" id="verify-form">
            @csrf

            <div class="field">
                <div class="field-label">
                    <span>رمز التحقق</span>
                </div>
                <div class="field-control">
                    <input
                        id="code"
                        type="text"
                        name="code"
                        required
                        autofocus
                        maxlength="6"
                        pattern="[0-9]{6}"
                        placeholder="000000"
                    >
                </div>
                @if ($errors->has('code'))
                    <div class="field-error">
                        {{ $errors->first('code') }}
                    </div>
                @endif
            </div>

            <div class="actions">
                <button type="submit" class="btn-primary">
                    <span>التحقق</span>
                </button>
                <button type="button" class="btn-link" id="resend-btn">
                    إعادة إرسال الرمز
                </button>
            </div>
        </form>

        <div class="meta">
            Quantum LMS &copy; {{ date('Y') }}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('code');
    const resendBtn = document.getElementById('resend-btn');

    // Only allow numbers
    codeInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Resend OTP
    resendBtn.addEventListener('click', function() {
        const btn = this;
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'جاري الإرسال...';

        fetch('{{ route("phone.send") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('تم إرسال رمز التحقق بنجاح');
            } else {
                alert('فشل إرسال رمز التحقق: ' + data.message);
            }
        })
        .catch(error => {
            alert('حدث خطأ: ' + error.message);
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = originalText;
        });
    });
});
</script>
</body>
</html>



