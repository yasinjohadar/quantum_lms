@extends('auth.layouts.glass')

@section('title', 'صفحة دخول المشرفين والمعلمين - منصة كوانتم التعليمية')
@section('brand-subtitle', 'بوابة الدخول')

@section('content')
    <div class="auth-heading">
        <div class="auth-badge">صفحة دخول المشرفين والمعلمين</div>
        <h1>صفحة دخول المشرفين والمعلمين</h1>
        <p>أدخل بياناتك للوصول إلى لوحة التحكم ومتابعة عملك.</p>
    </div>

    @if (session('status'))
        <div class="auth-alert auth-alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="auth-alert auth-alert-danger">بيانات الدخول غير صحيحة، يرجى التحقق ثم المحاولة مرة أخرى.</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        @php($adminAuthMode = old('admin_auth_mode', 'email'))

        <div class="auth-field">
            <label class="auth-label">طريقة الدخول</label>
            <div class="auth-row" style="margin-top:0;">
                <label class="auth-checkbox" for="admin_auth_mode_email">
                    <input type="radio" id="admin_auth_mode_email" name="admin_auth_mode" value="email" @checked($adminAuthMode === 'email')>
                    <span>البريد الإلكتروني</span>
                </label>
                <label class="auth-checkbox" for="admin_auth_mode_phone">
                    <input type="radio" id="admin_auth_mode_phone" name="admin_auth_mode" value="phone" @checked($adminAuthMode === 'phone')>
                    <span>رقم الهاتف</span>
                </label>
            </div>
        </div>

        <div class="auth-field js-admin-email-wrap">
            <label class="auth-label" for="login">البريد الإلكتروني</label>
            <div class="auth-control">
                <input id="login" class="auth-input @error('login') invalid @enderror" type="text" name="login" value="{{ old('login') }}" autocomplete="username" autofocus placeholder="name@example.com">
                <span class="auth-icon">✉</span>
            </div>
            @error('login') <div class="auth-error">{{ $message }}</div> @enderror
        </div>

        <div class="js-admin-phone-wrap">
            @include('auth.partials.phone-country-field', [
                'label' => 'رقم الهاتف',
                'countryCodeName' => 'country_code',
                'manualCodeName' => 'manual_country_code',
                'phoneName' => 'phone',
                'countryCodeId' => 'admin_country_code',
                'manualCodeId' => 'admin_manual_country_code',
                'phoneId' => 'admin_phone',
                'required' => false,
            ])
        </div>

        <div class="auth-field">
            <label class="auth-label" for="password">كلمة المرور</label>
            <div class="auth-control auth-control--toggle-start">
                <input id="password" class="auth-input @error('password') invalid @enderror" type="password" name="password" required autocomplete="current-password" placeholder="أدخل كلمة المرور">
                <button type="button" class="password-toggle" data-target="password" aria-label="إظهار أو إخفاء كلمة المرور">👁</button>
            </div>
            @error('password') <div class="auth-error">{{ $message }}</div> @enderror
        </div>

        <div class="auth-row">
            <label class="auth-checkbox" for="remember_me">
                <input id="remember_me" type="checkbox" name="remember">
                <span>تذكرني</span>
            </label>
            @if (Route::has('password.request'))
                <a class="auth-link" href="{{ route('password.request') }}">نسيت كلمة المرور؟</a>
            @endif
        </div>

        <button type="submit" class="auth-btn">تسجيل الدخول</button>

        <div class="auth-meta">
            <a class="auth-link" href="{{ route('student.login') }}">دخول الطلاب</a>
            <br>
            @if (Route::has('register'))
                لا تملك حسابًا؟
                <a class="auth-link" href="{{ route('register') }}">إنشاء حساب جديد</a>
            @endif
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

    const syncAdminAuthMode = function () {
        const selected = document.querySelector('input[name="admin_auth_mode"]:checked');
        const mode = selected ? selected.value : 'email';
        const emailWrap = document.querySelector('.js-admin-email-wrap');
        const phoneWrap = document.querySelector('.js-admin-phone-wrap');
        const loginInput = document.getElementById('login');
        const phoneInput = document.getElementById('admin_phone');
        const countrySelect = document.getElementById('admin_country_code');
        const manualInput = document.getElementById('admin_manual_country_code');

        const isPhone = mode === 'phone';
        if (emailWrap) emailWrap.style.display = isPhone ? 'none' : 'block';
        if (phoneWrap) phoneWrap.style.display = isPhone ? 'block' : 'none';

        if (loginInput) loginInput.required = !isPhone;
        if (phoneInput) phoneInput.required = isPhone;
        if (countrySelect) countrySelect.required = isPhone;
        if (!isPhone && manualInput) manualInput.required = false;
    };

    document.querySelectorAll('input[name="admin_auth_mode"]').forEach(function (input) {
        input.addEventListener('change', syncAdminAuthMode);
    });

    document.querySelectorAll('.js-country-select').forEach(function (wrapper) {
        const select = wrapper.querySelector('.js-country-code');
        const trigger = wrapper.querySelector('.js-country-trigger');
        const menu = wrapper.querySelector('.js-country-menu');
        const label = wrapper.querySelector('.js-country-trigger-label');
        if (!select || !trigger || !menu || !label) return;

        const renderTriggerFlag = function (iso2) {
            const oldFlag = trigger.querySelector('.country-select-flag');
            if (oldFlag) oldFlag.remove();

            if (iso2 && iso2 !== 'other') {
                const img = document.createElement('img');
                img.className = 'country-select-flag';
                img.alt = 'flag';
                img.src = 'https://flagcdn.com/w20/' + iso2 + '.png';
                trigger.insertBefore(img, label);
                return;
            }

            const span = document.createElement('span');
            span.className = 'country-select-flag country-flag-fallback';
            span.textContent = '🌐';
            trigger.insertBefore(span, label);
        };

        const syncTriggerFromSelect = function () {
            const selectedOption = select.options[select.selectedIndex];
            if (!selectedOption) return;
            label.textContent = selectedOption.getAttribute('data-label') || selectedOption.textContent || '';
            renderTriggerFlag(selectedOption.getAttribute('data-iso2') || '');
        };

        const closeMenu = function () {
            wrapper.classList.remove('open');
            trigger.setAttribute('aria-expanded', 'false');
        };

        trigger.addEventListener('click', function () {
            const isOpen = wrapper.classList.toggle('open');
            trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        menu.querySelectorAll('.js-country-option').forEach(function (optionBtn) {
            optionBtn.addEventListener('click', function () {
                const value = optionBtn.getAttribute('data-value') || '';
                select.value = value;
                syncTriggerFromSelect();
                select.dispatchEvent(new Event('change', { bubbles: true }));
                closeMenu();
            });
        });

        document.addEventListener('click', function (event) {
            if (!wrapper.contains(event.target)) {
                closeMenu();
            }
        });

        syncTriggerFromSelect();
    });

    document.querySelectorAll('.js-country-code').forEach(function (select) {
        const manualTargetId = select.getAttribute('data-manual-target');
        const manualWrap = document.getElementById(manualTargetId + '_wrap');
        const manualInput = document.getElementById(manualTargetId);

        const syncManualField = function () {
            if (!manualWrap || !manualInput) return;
            const showManual = select.value === 'other';
            manualWrap.style.display = showManual ? 'block' : 'none';
            manualInput.required = showManual && select.required;
            if (!showManual) manualInput.value = '';
        };

        select.addEventListener('change', syncManualField);
        syncManualField();
    });

    syncAdminAuthMode();
</script>
@endpush
