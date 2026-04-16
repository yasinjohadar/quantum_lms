@extends('auth.layouts.glass')

@section('title', 'صفحة تسجيل طالب جديد - منصة كوانتم التعليمية')
@section('brand-subtitle', 'بوابة التسجيل')

@section('content')
    <div class="auth-heading">
        <div class="auth-badge">صفحة تسجيل طالب جديد</div>
        <h1>صفحة تسجيل طالب جديد</h1>
        <p>سجل بياناتك للوصول إلى لوحة الإدارة بتجربة حديثة وآمنة.</p>
    </div>

    @if ($errors->any())
        <div class="auth-alert auth-alert-danger">
            يرجى مراجعة الحقول وتصحيح الأخطاء قبل المتابعة.
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="auth-field">
            <label class="auth-label" for="name">الاسم الكامل</label>
            <div class="auth-control">
                <input id="name" class="auth-input @error('name') invalid @enderror" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="اكتب اسمك الكامل">
                <span class="auth-icon">👤</span>
            </div>
            @error('name') <div class="auth-error">{{ $message }}</div> @enderror
        </div>

        @include('auth.partials.phone-country-field', [
            'label' => 'رقم الهاتف' . ((isset($phoneVerificationEnabled) && $phoneVerificationEnabled) ? ' *' : ''),
            'countryCodeName' => 'country_code',
            'manualCodeName' => 'manual_country_code',
            'phoneName' => 'phone',
            'countryCodeId' => 'register_country_code',
            'manualCodeId' => 'register_manual_country_code',
            'phoneId' => 'register_phone',
            'required' => isset($phoneVerificationEnabled) && $phoneVerificationEnabled,
            'liveRegionErrorId' => (isset($phoneVerificationEnabled) && $phoneVerificationEnabled) ? 'register_phone_live_region' : null,
        ])

        <div class="auth-meta" style="margin-top:6px;">
            يتم حفظ الرقم بصيغة دولية كاملة مع رمز الدولة.
            @if(isset($phoneVerificationEnabled) && $phoneVerificationEnabled)
                - مطلوب للتحقق من الحساب
            @endif
        </div>

        <div class="auth-field">
            <label class="auth-label" for="password">كلمة المرور</label>
            <div class="auth-control auth-control--toggle-start">
                <input id="password" class="auth-input @error('password') invalid @enderror" type="password" name="password" required autocomplete="new-password" placeholder="أدخل كلمة مرور قوية">
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

        <button type="submit" class="auth-btn">إنشاء حساب</button>

        <div class="auth-meta">
            لديك حساب بالفعل؟
            <a href="{{ route('student.login') }}" class="auth-link">تسجيل دخول الطلاب</a>
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

    @if(isset($phoneVerificationEnabled) && $phoneVerificationEnabled)
    (function () {
        const phoneEl = document.getElementById('register_phone');
        const countryEl = document.getElementById('register_country_code');
        const manualEl = document.getElementById('register_manual_country_code');
        const liveEl = document.getElementById('register_phone_live_region');
        const url = @json(route('register.validate-phone-region'));
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        let debounceTimer = null;
        let abortController = null;

        function hideServerPhoneError() {
            document.querySelectorAll('.js-phone-server-error').forEach(function (el) {
                el.style.display = 'none';
            });
        }

        function setLiveState(valid, message) {
            if (!liveEl || !phoneEl) return;
            if (valid) {
                liveEl.style.display = 'none';
                liveEl.textContent = '';
                phoneEl.classList.remove('invalid');
                return;
            }
            liveEl.textContent = message || '';
            liveEl.style.display = message ? 'block' : 'none';
            phoneEl.classList.toggle('invalid', !!message);
        }

        function runValidate() {
            if (!phoneEl || !countryEl) return;
            if (abortController) {
                abortController.abort();
            }
            abortController = new AbortController();

            const body = {
                phone: phoneEl.value,
                country_code: countryEl.value,
                manual_country_code: manualEl ? manualEl.value : '',
            };

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(body),
                signal: abortController.signal,
            })
                .then(function (r) {
                    return r.json().then(function (data) {
                        return { ok: r.ok, data: data };
                    });
                })
                .then(function (result) {
                    if (!result.ok) {
                        return;
                    }
                    var data = result.data;
                    if (data.valid) {
                        hideServerPhoneError();
                        setLiveState(true);
                        return;
                    }
                    hideServerPhoneError();
                    setLiveState(false, data.message || 'تحقق من رقم الهاتف ورمز الدولة.');
                })
                .catch(function (err) {
                    if (err.name === 'AbortError') return;
                });
        }

        function scheduleValidate() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(runValidate, 380);
        }

        if (phoneEl) {
            phoneEl.addEventListener('input', scheduleValidate);
            phoneEl.addEventListener('blur', function () {
                clearTimeout(debounceTimer);
                runValidate();
            });
        }
        if (countryEl) {
            countryEl.addEventListener('change', function () {
                clearTimeout(debounceTimer);
                runValidate();
            });
        }
        if (manualEl) {
            manualEl.addEventListener('input', scheduleValidate);
        }

        if (phoneEl && phoneEl.value.trim()) {
            runValidate();
        }
    })();
    @endif
</script>
@endpush