@php
    $key = (string) $setting->key;
    $iconClass = 'settings-field__icon--muted';
    $icon = 'bi-gear';
    $title = $setting->description ?: $key;
    $hint = null;
    $featured = false;

    $meta = [
        'content_lesson_mandatory_review' => ['bi-clipboard-check', 'warning', 'إلزام مراجعة الدروس قبل النشر', 'عند التفعيل: كل حفظ من المعلم يُرسل تلقائياً للمشرف المسؤول عن الصف والمادة. عند الإلغاء: يعود الإرسال الاختياري.', true],
        'content_quiz_mandatory_review' => ['bi-clipboard-check', 'warning', 'إلزام مراجعة الاختبارات قبل النشر', 'عند التفعيل: كل حفظ من المعلم يُرسل الاختبار تلقائياً للمشرف المسؤول عن الصف والمادة. عند الإلغاء: يعود الإرسال الاختياري.', true],
        'phone_verification_enabled' => ['bi-phone', 'primary', 'تفعيل التحقق من رقم الهاتف عند التسجيل', null, false],
        'otp_expires_minutes' => ['bi-clock-history', 'warning', 'مدة صلاحية كود التحقق (دقائق)', null, false],
        'otp_message_template' => ['bi-chat-left-text', 'info', 'نص رسالة كود التحقق', 'استخدم {code} للرمز و {expires_in} لوقت الصلاحية بالدقائق', false],
        'otp_provider' => ['bi-send', 'success', 'مزود إرسال كود التحقق', null, false],
        'whatsapp_contact_number' => ['bi-whatsapp', 'success', 'رقم واتساب للتواصل', null, false],
        'student_supervisor_whatsapp_number' => ['bi-whatsapp', 'success', 'رقم واتساب قسم الإشراف', 'يظهر للطلاب في مودال طلب الانضمام، شريط الطلبات قيد المراجعة، ونموذج الدفع. اتركه فارغاً لإخفاء الزر.', false],
        'student_supervisor_whatsapp_button_enabled' => ['bi-toggle-on', 'info', 'إظهار زر واتساب قسم الإشراف', 'عند الإيقاف يُخفى الزر حتى لو كان الرقم مضبوطاً.', false],
        'student_supervisor_whatsapp_button_label' => ['bi-fonts', 'muted', 'نص زر واتساب قسم الإشراف', 'مثال: واتساب قسم الإشراف', false],
        'student_supervisor_whatsapp_message' => ['bi-chat-left-text', 'warning', 'رسالة واتساب قسم الإشراف', 'تظهر للطالب في مودال تأكيد إرسال طلب الانضمام.', false],
        'student_pending_purchase_price_visible' => ['bi-cash-coin', 'success', 'إظهار قيمة الاشتراك في الطلبات قيد المراجعة', 'عند الإيقاف تُخفى خانة القيمة من بطاقات طلبات الطالب.', false],
        'whatsapp_float_button_enabled' => ['bi-chat-dots', 'info', 'إظهار أيقونة واتساب العائمة', null, false],
        'contact_address' => ['bi-geo-alt', 'primary', 'العنوان (الفوتر)', null, false],
        'contact_phone' => ['bi-telephone', 'success', 'الهاتف (الفوتر)', null, false],
        'contact_email' => ['bi-envelope', 'info', 'البريد الإلكتروني (الفوتر)', null, false],
        'payments_iban_receipt_required' => ['bi-receipt', 'primary', 'طلب رفع وصل التحويل البنكي (IBAN)', null, false],
        'payments_iban_student_instructions' => ['bi-card-text', 'muted', 'تعليمات التحويل البنكي للطالب', 'تظهر في نموذج إتمام الدفع. زر واتساب المشرفة يُضاف تلقائياً عند ضبط رقم المشرفة.', false],
        'payments_iban_display_name' => ['bi-tag', 'primary', 'اسم طريقة الدفع (التحويل البنكي)', null, false],
        'payments_iban_account_iban' => ['bi-bank', 'info', 'رقم IBAN للتحويل', null, false],
        'payments_iban_account_bank_name' => ['bi-building', 'info', 'اسم البنك', null, false],
        'payments_iban_account_holder' => ['bi-person', 'muted', 'اسم صاحب الحساب', null, false],
        'payments_iban_pending_message' => ['bi-hourglass-split', 'warning', 'رسالة «الطلب قيد المعالجة» للطالب', 'تظهر في نموذج إتمام الدفع وبعد رفع الإيصال في مودال التأكيد.', false],
        'social_facebook_url' => ['bi-facebook', 'primary', 'رابط فيسبوك', null, false],
        'social_instagram_url' => ['bi-instagram', 'danger', 'رابط انستغرام', null, false],
        'social_telegram_url' => ['bi-telegram', 'info', 'رابط تيليجرام', null, false],
        'social_youtube_url' => ['bi-youtube', 'danger', 'رابط يوتيوب', null, false],
    ];

    if (isset($meta[$key])) {
        [$icon, $iconClass, $title, $hint, $featured] = $meta[$key];
        $iconClass = 'settings-field__icon--'.$iconClass;
    } elseif (str_starts_with($key, 'storage_')) {
        $icon = 'bi-hdd-stack';
        $iconClass = 'settings-field__icon--muted';
        $title = $setting->description ?? $key;
    } elseif ($setting->description) {
        $hint = $setting->description;
    }
@endphp

<div class="col-md-6">
    <div class="settings-field {{ $featured ? 'is-featured' : '' }}">
        <div class="settings-field__head">
            <span class="settings-field__icon {{ $iconClass }}">
                <i class="bi {{ $icon }}"></i>
            </span>
            <div class="min-w-0 flex-grow-1">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h6 class="settings-field__title mb-0">{{ $title }}</h6>
                    @if($featured)
                        <span class="settings-field__badge">محتوى</span>
                    @endif
                </div>
                @if($hint)
                    <p class="settings-field__hint mt-1">{{ $hint }}</p>
                @endif
            </div>
        </div>
        <div class="settings-field__control">
            @if($key === 'storage_driver_mode')
                <select class="form-select" name="settings[storage_driver_mode]" id="setting_{{ $setting->id }}">
                    @php
                        $modeLabels = [
                            'local_only' => 'محلي فقط',
                            'cloud_only' => 'سحابة فقط (بدون لوكال)',
                            'cloud_first' => 'السحابة أولاً ثم اللوكال عند الفشل',
                            'local_first' => 'اللوكال أولاً ثم المزامنة للسحابة',
                            'dual_write' => 'كتابة مزدوجة (لوكال + سحابة)',
                        ];
                    @endphp
                    @foreach(\App\Enums\StorageDriverMode::cases() as $mode)
                        <option value="{{ $mode->value }}" @selected((string) $setting->value === $mode->value)>
                            {{ $modeLabels[$mode->value] ?? $mode->value }}
                        </option>
                    @endforeach
                </select>
            @elseif(str_starts_with($key, 'storage_') && $setting->type === 'boolean')
                <div class="form-check form-switch">
                    <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                    <input class="form-check-input" type="checkbox"
                           name="settings[{{ $setting->key }}]"
                           value="1"
                           id="setting_{{ $setting->id }}"
                           {{ $setting->value ? 'checked' : '' }}>
                    <label class="form-check-label" for="setting_{{ $setting->id }}">مفعّل</label>
                </div>
            @elseif(str_starts_with($key, 'storage_') && $setting->type === 'integer')
                <input type="number" class="form-control" min="0"
                       name="settings[{{ $setting->key }}]"
                       id="setting_{{ $setting->id }}"
                       value="{{ $setting->value }}">
            @elseif(str_starts_with($key, 'storage_'))
                <input type="text" class="form-control"
                       name="settings[{{ $setting->key }}]"
                       id="setting_{{ $setting->id }}"
                       value="{{ $setting->value }}">
            @elseif($key === 'otp_provider')
                <select class="form-select" name="settings[{{ $setting->key }}]" id="setting_{{ $setting->id }}">
                    <option value="whatsapp" {{ $setting->value === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                    <option value="sms" {{ $setting->value === 'sms' ? 'selected' : '' }}>SMS</option>
                </select>
            @elseif(in_array($key, ['whatsapp_contact_number', 'student_supervisor_whatsapp_number'], true))
                <input type="text" class="form-control"
                       name="settings[{{ $setting->key }}]"
                       id="setting_{{ $setting->id }}"
                       value="{{ $setting->value }}"
                       placeholder="{{ $key === 'student_supervisor_whatsapp_number' ? '9649xxxxxxxx' : '963912345678' }}">
            @elseif(in_array($key, ['contact_address', 'contact_phone', 'contact_email'], true))
                <input type="{{ $key === 'contact_email' ? 'email' : 'text' }}" class="form-control"
                       name="settings[{{ $setting->key }}]"
                       id="setting_{{ $setting->id }}"
                       value="{{ $setting->value }}"
                       placeholder="{{ $key === 'contact_email' ? 'info@example.com' : '' }}">
            @elseif(in_array($key, ['social_facebook_url', 'social_instagram_url', 'social_telegram_url', 'social_youtube_url'], true))
                <input type="url" class="form-control"
                       name="settings[{{ $setting->key }}]"
                       id="setting_{{ $setting->id }}"
                       value="{{ $setting->value }}"
                       placeholder="https://">
            @elseif($setting->type === 'boolean')
                <div class="form-check form-switch">
                    <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                    <input class="form-check-input" type="checkbox"
                           name="settings[{{ $setting->key }}]"
                           value="1"
                           id="setting_{{ $setting->id }}"
                           {{ $setting->value ? 'checked' : '' }}>
                    <label class="form-check-label" for="setting_{{ $setting->id }}">مفعّل</label>
                </div>
            @elseif($setting->type === 'integer')
                <input type="number" class="form-control"
                       name="settings[{{ $setting->key }}]"
                       id="setting_{{ $setting->id }}"
                       value="{{ $setting->value }}">
            @elseif($setting->type === 'json')
                <textarea class="form-control"
                          name="settings[{{ $setting->key }}]"
                          id="setting_{{ $setting->id }}"
                          rows="3">{{ is_array($setting->value) ? json_encode($setting->value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $setting->value }}</textarea>
            @elseif($setting->type === 'text')
                <textarea class="form-control"
                          name="settings[{{ $setting->key }}]"
                          id="setting_{{ $setting->id }}"
                          rows="{{ in_array($key, ['payments_iban_student_instructions', 'payments_iban_pending_message', 'student_supervisor_whatsapp_message'], true) ? 5 : 3 }}"
                          @if($key === 'otp_message_template') placeholder="مثال: رمز التحقق الخاص بك هو: {code} - صالح لمدة {expires_in} دقائق" @elseif($key === 'payments_iban_student_instructions') placeholder="مثال: أرسل المبلغ من حسابك باسمك الكامل، ثم احفظ رقم العملية..." @elseif($key === 'payments_iban_pending_message') placeholder="مثال: الطلب قيد المعالجة. يجب التواصل مع قسم الإشراف لتأكيد الاشتراك." @elseif($key === 'student_supervisor_whatsapp_message') placeholder="مثال: حتى يتم تفعيل حسابك يرجى التواصل مع قسم الإشراف عبر الواتساب" @endif>{{ $setting->value }}</textarea>
            @else
                <input type="text" class="form-control"
                       name="settings[{{ $setting->key }}]"
                       id="setting_{{ $setting->id }}"
                       value="{{ $setting->value }}">
            @endif
        </div>
    </div>
</div>
