@extends('auth.layouts.glass')

@section('title', 'صفحة تسجيل الطالب - منصة كوانتم التعليمية')
@section('brand-subtitle', 'بوابة الطلاب')

@section('content')
    <div class="auth-heading">
        <div class="auth-badge">صفحة تسجيل الطالب</div>
        <h1>صفحة تسجيل الطالب</h1>
        <p>سجّل الدخول برقم الهاتف وكلمة المرور لمتابعة الدراسة.</p>
    </div>

    @if (session('status'))
        <div class="auth-alert auth-alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="auth-alert auth-alert-danger">تعذر تسجيل الدخول، يرجى التحقق من البيانات ثم المحاولة مرة أخرى.</div>
    @endif

    <form method="POST" action="{{ route('student.login.store') }}">
        @csrf

        @include('auth.partials.phone-country-field', [
            'label' => 'رقم الهاتف',
            'countryCodeName' => 'country_code',
            'manualCodeName' => 'manual_country_code',
            'phoneName' => 'phone',
            'countryCodeId' => 'student_country_code',
            'manualCodeId' => 'student_manual_country_code',
            'phoneId' => 'student_phone',
            'required' => true,
        ])

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

        <button type="submit" class="auth-btn">دخول الطالب</button>

        <div class="auth-meta">
            التحقق عبر واتساب يتم مرة واحدة فقط بعد التسجيل الأول.
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
            manualInput.required = showManual;
            if (!showManual) manualInput.value = '';
        };

        select.addEventListener('change', syncManualField);
        syncManualField();
    });
</script>
@endpush
