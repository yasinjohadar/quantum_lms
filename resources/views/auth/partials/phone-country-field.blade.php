@php
    $countries = config('countries', []);
    $defaultCode = (string) config('app.phone_default_country_code', '963');
    $countryCodeName = $countryCodeName ?? 'country_code';
    $manualCodeName = $manualCodeName ?? 'manual_country_code';
    $phoneName = $phoneName ?? 'phone';
    $countryCodeId = $countryCodeId ?? $countryCodeName;
    $manualCodeId = $manualCodeId ?? $manualCodeName;
    $phoneId = $phoneId ?? $phoneName;
    $selectedCountryCode = old($countryCodeName, $selectedCountryCode ?? $defaultCode);
    $manualCountryCodeValue = old($manualCodeName, $manualCountryCodeValue ?? '');
    $phoneValue = old($phoneName, $phoneValue ?? '');
    $required = isset($required) ? (bool) $required : false;
    $label = $label ?? 'رقم الهاتف';
    $liveRegionErrorId = $liveRegionErrorId ?? null;
@endphp

<div class="auth-field">
    <label class="auth-label">{{ $label }}</label>

    <div class="auth-inline-group">
        <div class="auth-inline-item auth-inline-country">
            <label class="auth-label auth-label-small" for="{{ $countryCodeId }}">رمز الدولة</label>
            <div class="auth-control country-select js-country-select" data-select-id="{{ $countryCodeId }}">
                @php
                    $selectedCountry = collect($countries)->firstWhere('dial_code', (string) $selectedCountryCode);
                    $selectedIso2 = strtolower((string) ($selectedCountry['iso2'] ?? ''));
                    $selectedFlagUrl = $selectedIso2 && $selectedIso2 !== 'other'
                        ? 'https://flagcdn.com/w20/' . $selectedIso2 . '.png'
                        : null;
                @endphp
                <select
                    id="{{ $countryCodeId }}"
                    name="{{ $countryCodeName }}"
                    class="auth-input auth-input-country js-country-code native-country-select @error($countryCodeName) invalid @enderror"
                    data-manual-target="{{ $manualCodeId }}"
                    @if($required) required @endif
                >
                    @foreach($countries as $country)
                        @php($dial = (string) ($country['dial_code'] ?? ''))
                        <option
                            value="{{ $dial }}"
                            data-iso2="{{ strtolower((string) ($country['iso2'] ?? '')) }}"
                            @selected((string) $selectedCountryCode === $dial)
                        >
                            {{ $country['flag_emoji'] ?? '🌍' }} +{{ $dial !== 'other' ? $dial : '' }} {{ $country['name_ar'] ?? '' }}
                        </option>
                    @endforeach
                </select>
                <button type="button" class="country-select-trigger js-country-trigger" aria-haspopup="listbox" aria-expanded="false">
                    @if($selectedFlagUrl)
                        <img class="country-select-flag" src="{{ $selectedFlagUrl }}" alt="flag">
                    @else
                        <span class="country-select-flag country-flag-fallback">🌐</span>
                    @endif
                    <span class="js-country-trigger-label">
                        +{{ (string) $selectedCountryCode !== 'other' ? $selectedCountryCode : '' }}
                        {{ $selectedCountry['name_ar'] ?? 'رمز آخر (إدخال يدوي)' }}
                    </span>
                </button>
                <div class="country-select-menu js-country-menu" role="listbox">
                    @foreach($countries as $country)
                        @php($countryDial = (string) ($country['dial_code'] ?? ''))
                        @php($countryIso2 = strtolower((string) ($country['iso2'] ?? '')))
                        @php($countryFlagUrl = $countryIso2 && $countryIso2 !== 'other' ? 'https://flagcdn.com/w20/' . $countryIso2 . '.png' : null)
                        <button
                            type="button"
                            class="country-select-option js-country-option"
                            data-value="{{ $countryDial }}"
                            data-iso2="{{ $countryIso2 }}"
                            data-label="+{{ $countryDial !== 'other' ? $countryDial : '' }} {{ $country['name_ar'] ?? '' }}"
                        >
                            @if($countryFlagUrl)
                                <img class="country-select-flag" src="{{ $countryFlagUrl }}" alt="flag">
                            @else
                                <span class="country-select-flag country-flag-fallback">🌐</span>
                            @endif
                            <span>+{{ $countryDial !== 'other' ? $countryDial : '' }} {{ $country['name_ar'] ?? '' }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
            @error($countryCodeName) <div class="auth-error">{{ $message }}</div> @enderror
        </div>

        <div class="auth-inline-item auth-inline-phone">
            <label class="auth-label auth-label-small" for="{{ $phoneId }}">رقم الهاتف</label>
            <div class="auth-control">
                <input
                    id="{{ $phoneId }}"
                    class="auth-input @error($phoneName) invalid @enderror"
                    type="text"
                    name="{{ $phoneName }}"
                    value="{{ $phoneValue }}"
                    autocomplete="tel"
                    placeholder="5XXXXXXXX أو 05XXXXXXXX"
                    @if($required) required @endif
                >
                <span class="auth-icon">📱</span>
            </div>
            @error($phoneName) <div class="auth-error js-phone-server-error">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="auth-field js-manual-country-wrap" id="{{ $manualCodeId }}_wrap" @if((string) $selectedCountryCode !== 'other') style="display:none;" @endif>
        <label class="auth-label auth-label-small" for="{{ $manualCodeId }}">رمز الدولة المخصص</label>
        <div class="auth-control">
            <input
                id="{{ $manualCodeId }}"
                class="auth-input @error($manualCodeName) invalid @enderror"
                type="text"
                name="{{ $manualCodeName }}"
                value="{{ $manualCountryCodeValue }}"
                inputmode="numeric"
                placeholder="مثال: 963"
            >
        </div>
        @error($manualCodeName) <div class="auth-error">{{ $message }}</div> @enderror
    </div>

    @if(!empty($liveRegionErrorId))
        <div id="{{ $liveRegionErrorId }}" class="auth-error js-phone-region-live" style="display:none;" aria-live="polite"></div>
    @endif
</div>
