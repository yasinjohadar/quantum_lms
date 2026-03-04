@php
    $showFloat = ($whatsappFloatButtonEnabled ?? false) && !empty(trim($whatsappContactNumber ?? ''));
    $waNumber = preg_replace('/\D/', '', $whatsappContactNumber ?? '');
@endphp
@if($showFloat && $waNumber !== '')
    <a href="https://wa.me/{{ $waNumber }}" 
       target="_blank" 
       rel="noopener noreferrer" 
       class="whatsapp-float-btn" 
       aria-label="تواصل معنا عبر واتساب">
        <i class="fa-brands fa-whatsapp"></i>
    </a>
@endif
