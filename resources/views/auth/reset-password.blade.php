@extends('auth.layouts.glass')

@section('title', 'إعادة تعيين كلمة المرور - أكاديمية كوانتم التعليمية')
@section('brand-subtitle', 'أمان الحساب')

@push('styles')
<style>
    .otp-countdown-box {
        border: 1px solid rgba(148, 163, 184, 0.35);
        border-radius: 12px;
        padding: 10px 12px;
        background: rgba(15, 23, 42, 0.35);
    }

    .otp-countdown-label {
        display: block;
        margin-bottom: 4px;
    }

    .otp-countdown-value {
        font-size: 1.05rem;
        font-weight: 700;
        color: #86efac;
        transition: color .2s ease;
    }

    .otp-countdown-value.is-warning {
        color: #fbbf24;
    }

    .otp-progress {
        height: 6px;
        border-radius: 999px;
        margin-top: 8px;
        background: rgba(148, 163, 184, 0.22);
        overflow: hidden;
    }

    .otp-progress > span {
        display: block;
        height: 100%;
        width: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #22c55e 0%, #86efac 100%);
        transition: width .95s linear, background .2s ease;
    }

    .otp-progress > span.is-warning {
        background: linear-gradient(90deg, #f59e0b 0%, #fbbf24 100%);
    }
</style>
@endpush

@section('content')
    <div class="auth-heading">
        <div class="auth-badge">إعادة تعيين آمنة</div>
        <h1>تعيين كلمة مرور جديدة</h1>
        <p>
            @if(isset($phone))
                أدخل الرمز المرسل إلى {{ $phone }} ثم اختر كلمة مرور جديدة.
            @else
                أدخل رمز التحقق وكلمة المرور الجديدة.
            @endif
        </p>
    </div>

    @if (session('success'))
        <div class="auth-alert auth-alert-success">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div class="auth-alert auth-alert-danger">{{ session('warning') }}</div>
    @endif
    @if (session('error'))
        <div class="auth-alert auth-alert-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="auth-alert auth-alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <div class="auth-field">
            <label class="auth-label" for="code">رمز التحقق</label>
            <div class="auth-control">
                <input id="code" class="auth-input @error('code') invalid @enderror" type="text" name="code" value="{{ old('code') }}" required autofocus maxlength="6" pattern="[0-9]{6}" placeholder="000000" style="text-align:center;letter-spacing:6px;padding-left:14px;">
            </div>
            @error('code') <div class="auth-error">{{ $message }}</div> @enderror
        </div>

        <div class="auth-field">
            <label class="auth-label" for="password">كلمة المرور الجديدة</label>
            <div class="auth-control auth-control--toggle-start">
                <input id="password" class="auth-input @error('password') invalid @enderror" type="password" name="password" required autocomplete="new-password" placeholder="كلمة مرور جديدة">
                <button type="button" class="password-toggle" data-target="password" aria-label="إظهار أو إخفاء كلمة المرور">👁</button>
            </div>
            @error('password') <div class="auth-error">{{ $message }}</div> @enderror
        </div>

        <div class="auth-field">
            <label class="auth-label" for="password_confirmation">تأكيد كلمة المرور</label>
            <div class="auth-control auth-control--toggle-start">
                <input id="password_confirmation" class="auth-input @error('password_confirmation') invalid @enderror" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="أعد كتابة كلمة المرور">
                <button type="button" class="password-toggle" data-target="password_confirmation" aria-label="إظهار أو إخفاء كلمة المرور">👁</button>
            </div>
            @error('password_confirmation') <div class="auth-error">{{ $message }}</div> @enderror
        </div>

        <button class="auth-btn" type="submit">حفظ كلمة المرور</button>

        <div class="auth-meta" style="display:flex;flex-direction:column;gap:8px;margin-top:1rem;">
            <div id="otp-countdown-wrap" class="otp-countdown-box">
                <span class="otp-countdown-label">ينتهي الكود خلال:</span>
                <strong id="otp-countdown" class="otp-countdown-value">00:00</strong>
                <div class="otp-progress">
                    <span id="otp-progress-bar"></span>
                </div>
            </div>

            <div id="resend-wrap" style="display:none;">
                <a class="auth-link" href="#" id="resend-btn">إعادة إرسال الرمز</a>
                <div style="display:flex;gap:14px;justify-content:center;">
                    <a class="auth-link" href="#" id="resend-sms-btn">إرسال SMS</a>
                    <a class="auth-link" href="#" id="resend-whatsapp-btn">إرسال WhatsApp</a>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.password-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const target = document.getElementById(btn.getAttribute('data-target'));
            if (!target) return;
            const isPassword = target.type === 'password';
            target.type = isPassword ? 'text' : 'password';
            btn.textContent = isPassword ? '🙈' : '👁';
        });
    });

    document.getElementById('code')?.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    document.addEventListener('DOMContentLoaded', function () {
        const initialRemainingSeconds = Number(@json((int) ($otpRemainingSeconds ?? 0)));
        const hasActiveOtp = Boolean(@json((bool) ($hasActiveOtp ?? false)));
        const resendBtn = document.getElementById('resend-btn');
        const resendSmsBtn = document.getElementById('resend-sms-btn');
        const resendWhatsappBtn = document.getElementById('resend-whatsapp-btn');
        const countdownWrap = document.getElementById('otp-countdown-wrap');
        const countdownEl = document.getElementById('otp-countdown');
        const progressEl = document.getElementById('otp-progress-bar');
        const resendWrap = document.getElementById('resend-wrap');

        let remainingSeconds = initialRemainingSeconds;
        let initialDuration = initialRemainingSeconds;
        let timer = null;

        function formatTime(totalSeconds) {
            const sec = Math.max(0, Number(totalSeconds) || 0);
            const minutes = Math.floor(sec / 60);
            const seconds = sec % 60;
            return String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
        }

        function syncVisibility() {
            const active = remainingSeconds > 0;
            if (countdownWrap) countdownWrap.style.display = active ? 'block' : 'none';
            if (resendWrap) resendWrap.style.display = active ? 'none' : 'block';
            if (countdownEl) {
                countdownEl.textContent = formatTime(remainingSeconds);
                countdownEl.classList.toggle('is-warning', active && remainingSeconds <= 30);
            }
            if (progressEl) {
                const percentage = initialDuration > 0 ? Math.max(0, (remainingSeconds / initialDuration) * 100) : 0;
                progressEl.style.width = percentage + '%';
                progressEl.classList.toggle('is-warning', active && remainingSeconds <= 30);
            }
        }

        function stopTimer() {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        }

        function startCountdown(seconds) {
            remainingSeconds = Math.max(0, Number(seconds) || 0);
            initialDuration = remainingSeconds;
            syncVisibility();
            stopTimer();
            if (remainingSeconds <= 0) return;
            timer = setInterval(function () {
                remainingSeconds = Math.max(0, remainingSeconds - 1);
                syncVisibility();
                if (remainingSeconds <= 0) {
                    stopTimer();
                }
            }, 1000);
        }

        function sendOtp(provider) {
            fetch(@json(route('password.reset.send-otp')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ provider: provider })
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!data.success) {
                    alert(data.message || 'تعذر إرسال الرمز');
                    return;
                }
                startCountdown(data.remaining_seconds || 0);
            })
            .catch(function () {
                alert('حدث خطأ أثناء إرسال الرمز');
            });
        }

        if (resendBtn) resendBtn.addEventListener('click', function (e) { e.preventDefault(); sendOtp('sms'); });
        if (resendSmsBtn) resendSmsBtn.addEventListener('click', function (e) { e.preventDefault(); sendOtp('sms'); });
        if (resendWhatsappBtn) resendWhatsappBtn.addEventListener('click', function (e) { e.preventDefault(); sendOtp('whatsapp'); });

        if (hasActiveOtp && initialRemainingSeconds > 0) {
            startCountdown(initialRemainingSeconds);
        } else {
            startCountdown(0);
        }
    });
</script>
@endpush
