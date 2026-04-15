@extends('auth.layouts.glass')

@section('title', 'التحقق من الهاتف - Quantum LMS')
@section('brand-subtitle', 'توثيق الحساب')

@section('content')
    <div class="auth-heading">
        <div class="auth-badge">تحقق بخطوتين</div>
        <h1>التحقق من رقم الهاتف</h1>
        <p>
            @if(isset($phone))
                أدخل الرمز المرسل إلى {{ substr($phone, 0, 4) }}****{{ substr($phone, -4) }}
            @else
                أدخل رمز التحقق المرسل إلى رقم هاتفك.
            @endif
        </p>
    </div>

    @if (session('success'))
        <div class="auth-alert auth-alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="auth-alert auth-alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('phone.verify') }}">
        @csrf
        <div class="auth-field">
            <label class="auth-label" for="code">رمز التحقق</label>
            <div class="auth-control">
                <input id="code" class="auth-input @error('code') invalid @enderror" type="text" name="code" required autofocus maxlength="6" pattern="[0-9]{6}" placeholder="000000" style="text-align:center;letter-spacing:6px;padding-left:14px;">
            </div>
            @error('code') <div class="auth-error">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="auth-btn">تأكيد الرمز</button>

        <div class="auth-meta" style="display:flex;flex-direction:column;gap:8px;">
            <a class="auth-link" href="#" id="resend-btn">إعادة إرسال الرمز</a>
            <div style="display:flex;gap:14px;justify-content:center;">
                <a class="auth-link" href="#" id="resend-sms-btn">إرسال SMS</a>
                <a class="auth-link" href="#" id="resend-whatsapp-btn">إرسال WhatsApp</a>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const codeInput = document.getElementById('code');
        const resendBtn = document.getElementById('resend-btn');
        const resendSmsBtn = document.getElementById('resend-sms-btn');
        const resendWhatsappBtn = document.getElementById('resend-whatsapp-btn');

        codeInput.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        let cooldown = 0;
        let timer = null;
        function syncButtons() {
            [resendBtn, resendSmsBtn, resendWhatsappBtn].forEach(function (btn) {
                if (!btn) return;
                if (cooldown > 0) {
                    btn.style.pointerEvents = 'none';
                    btn.style.opacity = '0.5';
                } else {
                    btn.style.pointerEvents = 'auto';
                    btn.style.opacity = '1';
                }
            });
            if (resendBtn) {
                resendBtn.textContent = cooldown > 0 ? `إعادة إرسال (${cooldown}ث)` : 'إعادة إرسال الرمز';
            }
            if (cooldown > 0) cooldown--;
            if (cooldown <= 0 && timer) {
                clearInterval(timer);
                timer = null;
            }
        }

        function sendOtp(provider) {
            fetch('{{ route("phone.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ provider: provider })
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!data.success) {
                    alert(data.message || 'تعذر إرسال الرمز');
                    return;
                }
                cooldown = 60;
                syncButtons();
                if (!timer) {
                    timer = setInterval(syncButtons, 1000);
                }
            })
            .catch(function () {
                alert('حدث خطأ أثناء إرسال الرمز');
            });
        }

        if (resendBtn) resendBtn.addEventListener('click', function (e) { e.preventDefault(); sendOtp('sms'); });
        if (resendSmsBtn) resendSmsBtn.addEventListener('click', function (e) { e.preventDefault(); sendOtp('sms'); });
        if (resendWhatsappBtn) resendWhatsappBtn.addEventListener('click', function (e) { e.preventDefault(); sendOtp('whatsapp'); });
    });
</script>
@endpush




