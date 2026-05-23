@extends('admin.layouts.master')

@section('page-title')
    الإعدادات العامة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">الإعدادات العامة</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">الإعدادات</li>
                    </ol>
                </nav>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <!-- Tabs للتنقل بين مجموعات الإعدادات -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            @foreach($groups as $groupKey => $groupName)
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ $group === $groupKey ? 'active' : '' }}" 
                                       href="{{ route('admin.settings.index', ['group' => $groupKey]) }}">
                                        {{ $groupName }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-body">
                        @if($settings->count() > 0)
                            <form action="{{ route('admin.settings.update') }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="group" value="{{ $group }}">
                                
                                <div class="row g-3">
                                    @foreach($settings as $setting)
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                @if($setting->key === 'phone_verification_enabled')
                                                    <i class="fas fa-mobile-alt me-2 text-primary"></i>تفعيل التحقق من رقم الهاتف عند التسجيل
                                                @elseif($setting->key === 'otp_expires_minutes')
                                                    <i class="fas fa-clock me-2 text-warning"></i>مدة صلاحية كود التحقق (دقائق)
                                                @elseif($setting->key === 'otp_message_template')
                                                    <i class="fas fa-envelope me-2 text-info"></i>نص رسالة كود التحقق
                                                    <small class="text-muted d-block mt-1">استخدم <code>{code}</code> للرمز و <code>{expires_in}</code> لوقت الصلاحية بالدقائق</small>
                                                @elseif($setting->key === 'otp_provider')
                                                    <i class="fas fa-paper-plane me-2 text-success"></i>مزود إرسال كود التحقق
                                                @elseif($setting->key === 'whatsapp_contact_number')
                                                    <i class="fab fa-whatsapp me-2 text-success"></i>رقم واتساب للتواصل
                                                @elseif($setting->key === 'student_supervisor_whatsapp_number')
                                                    <i class="fab fa-whatsapp me-2 text-success"></i>رقم واتساب مشرفة الطلاب (متابعة التفعيل)
                                                    <small class="text-muted d-block mt-1">يُستخدم في شريط «مشتريات قيد المراجعة» ومودال تأكيد الدفع (زر واتساب مع أيقونة).</small>
                                                @elseif($setting->key === 'whatsapp_float_button_enabled')
                                                    <i class="fas fa-eye me-2 text-info"></i>إظهار أيقونة واتساب العائمة
                                                @elseif($setting->key === 'contact_address')
                                                    <i class="fas fa-location-dot me-2 text-primary"></i>العنوان (الفوتر)
                                                @elseif($setting->key === 'contact_phone')
                                                    <i class="fas fa-phone me-2 text-success"></i>الهاتف (الفوتر)
                                                @elseif($setting->key === 'contact_email')
                                                    <i class="fas fa-envelope me-2 text-info"></i>البريد الإلكتروني (الفوتر)
                                                @elseif($setting->key === 'payments_iban_receipt_required')
                                                    <i class="fas fa-file-invoice me-2 text-primary"></i>طلب رفع وصل التحويل البنكي (IBAN)
                                                @elseif($setting->key === 'payments_iban_student_instructions')
                                                    <i class="fas fa-align-right me-2 text-secondary"></i>تعليمات التحويل البنكي للطالب
                                                @elseif($setting->key === 'payments_iban_display_name')
                                                    <i class="fas fa-tag me-2 text-primary"></i>اسم طريقة الدفع (التحويل البنكي)
                                                @elseif($setting->key === 'payments_iban_account_iban')
                                                    <i class="fas fa-university me-2 text-info"></i>رقم IBAN للتحويل
                                                @elseif($setting->key === 'payments_iban_account_bank_name')
                                                    <i class="fas fa-building-columns me-2 text-info"></i>اسم البنك
                                                @elseif($setting->key === 'payments_iban_account_holder')
                                                    <i class="fas fa-user me-2 text-secondary"></i>اسم صاحب الحساب
                                                @elseif($setting->key === 'payments_iban_pending_message')
                                                    <i class="fas fa-hourglass-half me-2 text-warning"></i>رسالة «الطلب قيد المعالجة» للطالب
                                                    <small class="text-muted d-block mt-1">تظهر في مودال «تم إرسال طلب الدفع» بعد رفع الإيصال. يُضاف زر واتساب المشرفة تلقائياً أسفلها عند ضبط «رقم واتساب مشرفة الطلاب».</small>
                                                @elseif($setting->key === 'social_facebook_url')
                                                    <i class="fab fa-facebook-f me-2 text-primary"></i>رابط فيسبوك
                                                @elseif($setting->key === 'social_instagram_url')
                                                    <i class="fab fa-instagram me-2 text-danger"></i>رابط انستغرام
                                                @elseif($setting->key === 'social_telegram_url')
                                                    <i class="fab fa-telegram me-2 text-info"></i>رابط تيليجرام
                                                @elseif($setting->key === 'social_youtube_url')
                                                    <i class="fab fa-youtube me-2 text-danger"></i>رابط يوتيوب
                                                @elseif(str_starts_with((string) $setting->key, 'storage_'))
                                                    <i class="fas fa-database me-2 text-secondary"></i>{{ $setting->description ?? $setting->key }}
                                                @else
                                                    {{ $setting->key }}
                                                @endif
                                                @if($setting->description && !str_starts_with((string) $setting->key, 'storage_'))
                                                    <small class="text-muted d-block mt-1">{{ $setting->description }}</small>
                                                @endif
                                            </label>
                                            
                                            @if($setting->key === 'storage_driver_mode')
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
                                            @elseif(str_starts_with((string) $setting->key, 'storage_') && $setting->type === 'boolean')
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                                    <input class="form-check-input" type="checkbox"
                                                           name="settings[{{ $setting->key }}]"
                                                           value="1"
                                                           id="setting_{{ $setting->id }}"
                                                           {{ $setting->value ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="setting_{{ $setting->id }}">مفعّل</label>
                                                </div>
                                            @elseif(str_starts_with((string) $setting->key, 'storage_') && $setting->type === 'integer')
                                                <input type="number" class="form-control" min="0"
                                                       name="settings[{{ $setting->key }}]"
                                                       id="setting_{{ $setting->id }}"
                                                       value="{{ $setting->value }}">
                                            @elseif(str_starts_with((string) $setting->key, 'storage_'))
                                                <input type="text" class="form-control"
                                                       name="settings[{{ $setting->key }}]"
                                                       id="setting_{{ $setting->id }}"
                                                       value="{{ $setting->value }}">
                                            @elseif($setting->key === 'otp_provider')
                                                {{-- معالجة خاصة لحقل otp_provider لعرضه كـ select --}}
                                                <select class="form-select" 
                                                        name="settings[{{ $setting->key }}]" 
                                                        id="setting_{{ $setting->id }}">
                                                    <option value="whatsapp" {{ $setting->value === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                                    <option value="sms" {{ $setting->value === 'sms' ? 'selected' : '' }}>SMS</option>
                                                </select>
                                            @elseif($setting->key === 'whatsapp_contact_number')
                                                <input type="text" 
                                                       class="form-control" 
                                                       name="settings[{{ $setting->key }}]" 
                                                       id="setting_{{ $setting->id }}" 
                                                       value="{{ $setting->value }}" 
                                                       placeholder="963912345678">
                                            @elseif($setting->key === 'student_supervisor_whatsapp_number')
                                                <input type="text" 
                                                       class="form-control" 
                                                       name="settings[{{ $setting->key }}]" 
                                                       id="setting_{{ $setting->id }}" 
                                                       value="{{ $setting->value }}" 
                                                       placeholder="9649xxxxxxxx">
                                            @elseif(in_array($setting->key, ['contact_address', 'contact_phone', 'contact_email']))
                                                <input type="{{ $setting->key === 'contact_email' ? 'email' : 'text' }}" 
                                                       class="form-control" 
                                                       name="settings[{{ $setting->key }}]" 
                                                       id="setting_{{ $setting->id }}" 
                                                       value="{{ $setting->value }}" 
                                                       placeholder="{{ $setting->key === 'contact_email' ? 'info@example.com' : '' }}">
                                            @elseif(in_array($setting->key, ['social_facebook_url', 'social_instagram_url', 'social_telegram_url', 'social_youtube_url']))
                                                <input type="url" 
                                                       class="form-control" 
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
                                                    <label class="form-check-label" for="setting_{{ $setting->id }}">
                                                        مفعّل
                                                    </label>
                                                </div>
                                            @elseif($setting->type === 'integer')
                                                <input type="number" 
                                                       class="form-control" 
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
                                                         rows="{{ in_array($setting->key, ['payments_iban_student_instructions', 'payments_iban_pending_message'], true) ? 5 : 3 }}"
                                                         @if($setting->key === 'otp_message_template') placeholder="مثال: رمز التحقق الخاص بك هو: {code} - صالح لمدة {expires_in} دقائق" @elseif($setting->key === 'payments_iban_student_instructions') placeholder="مثال: أرسل المبلغ من حسابك باسمك الكامل، ثم احفظ رقم العملية..." @elseif($setting->key === 'payments_iban_pending_message') placeholder="مثال: الطلب قيد المعالجة. يجب التواصل مع المشرفة لتأكيد الاشتراك." @endif>{{ $setting->value }}</textarea>
                                            @else
                                                <input type="text" 
                                                      class="form-control" 
                                                      name="settings[{{ $setting->key }}]" 
                                                      id="setting_{{ $setting->id }}" 
                                                      value="{{ $setting->value }}">
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> حفظ الإعدادات
                                    </button>
                                    <form action="{{ route('admin.settings.reset', $group) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من إعادة تعيين جميع إعدادات هذه المجموعة؟');">
                                        @csrf
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-redo me-1"></i> إعادة تعيين
                                        </button>
                                    </form>
                                </div>
                            </form>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                لا توجد إعدادات في هذه المجموعة.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection




