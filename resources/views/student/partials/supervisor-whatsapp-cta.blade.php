@php
    $supervisorWhatsappDigits = $supervisorWhatsappDigits ?? \App\Models\SystemSetting::supervisorWhatsappDigits();
    $hasSupervisorWa = $supervisorWhatsappDigits !== '';
    $btnSize = $btnSize ?? 'sm';
    $align = $align ?? 'start';
    $showMissingHint = $showMissingHint ?? false;
    $wrapperClass = $wrapperClass ?? 'mb-2';
    $btnClasses = $btnSize === 'lg'
        ? 'btn btn-success btn-lg px-4 rounded-pill d-inline-flex align-items-center gap-2'
        : 'btn btn-success btn-sm d-inline-flex align-items-center gap-1';
    $alignClass = $align === 'center' ? 'justify-content-center' : '';
@endphp
@if($hasSupervisorWa)
    <div class="{{ trim($wrapperClass.' d-flex '.$alignClass) }}">
        <a href="https://wa.me/{{ $supervisorWhatsappDigits }}" target="_blank" rel="noopener noreferrer"
           class="{{ $btnClasses }}">
            <i class="fab fa-whatsapp" aria-hidden="true"></i>
            واتساب المشرفة
        </a>
    </div>
@elseif($showMissingHint)
    <p class="text-muted small {{ $wrapperClass }} px-md-2">
        سيتم تفعيل رابط التواصل عبر واتساب عند ضبط رقم المشرفة من إعدادات النظام.
    </p>
@endif
