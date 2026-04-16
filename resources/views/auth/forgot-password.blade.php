@extends('auth.layouts.glass')

@section('title', 'استعادة كلمة المرور - منصة كوانتم التعليمية')
@section('brand-subtitle', 'استعادة الحساب')

@section('content')
    <div class="auth-heading">
        <div class="auth-badge">مساعدة تسجيل الدخول</div>
        <h1>نسيت كلمة المرور؟</h1>
        <p>أدخل رقم هاتفك المسجّل في المنصة وسنرسل لك رمز تحقق لإعادة تعيين كلمة المرور.</p>
    </div>

    @if (session('status'))
        <div class="auth-alert auth-alert-success">{{ session('status') }}</div>
    @endif
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
        <div class="auth-alert auth-alert-danger">تعذر إكمال الطلب. تحقق من الحقول ثم حاول مرة أخرى.</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        @include('auth.partials.phone-country-field', [
            'label' => 'رقم الهاتف',
            'countryCodeName' => 'country_code',
            'manualCodeName' => 'manual_country_code',
            'phoneName' => 'phone',
            'countryCodeId' => 'forgot_country_code',
            'manualCodeId' => 'forgot_manual_country_code',
            'phoneId' => 'forgot_phone',
            'required' => true,
            'liveRegionErrorId' => 'forgot_phone_live_region',
        ])

        <button type="submit" class="auth-btn">إرسال رمز التحقق</button>

        <div class="auth-meta">
            <a href="{{ route('student.login') }}" class="auth-link">العودة إلى تسجيل الدخول</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
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

    (function () {
        const phoneEl = document.getElementById('forgot_phone');
        const countryEl = document.getElementById('forgot_country_code');
        const manualEl = document.getElementById('forgot_manual_country_code');
        const liveEl = document.getElementById('forgot_phone_live_region');
        const url = @json(route('password.request.validate-phone-region'));
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
</script>
@endpush
