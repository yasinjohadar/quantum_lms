<!-- Favicon -->
<link rel="icon" href="{{ asset('assets/images/brand-logos/favicon.ico') }}" type="image/x-icon">

<!-- Choices JS -->
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>

<!-- Main Theme Js -->
<script src="{{ asset('assets/js/main.js') }}{{ file_exists(public_path('assets/js/main.js')) ? '?v=' . filemtime(public_path('assets/js/main.js')) : '' }}"></script>

<!-- Bootstrap Css (RTL افتراضي) -->
<link id="style" href="{{ asset('assets/libs/bootstrap/css/bootstrap.rtl.min.css') }}" rel="stylesheet">

<!-- Style Css -->
<link href="{{ asset('assets/css/styles.min.css') }}" rel="stylesheet">

<!-- Icons Css -->
<link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">

<!-- Node Waves Css -->
<link href="{{ asset('assets/libs/node-waves/waves.min.css') }}" rel="stylesheet">

<!-- Simplebar Css -->
<link href="{{ asset('assets/libs/simplebar/simplebar.min.css') }}" rel="stylesheet">

<!-- Color Picker Css -->
<link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/libs/@simonwep/pickr/themes/nano.min.css') }}">

<!-- Choices Css -->
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">

<!-- Custom Css -->
<link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}{{ file_exists(public_path('assets/css/custom.css')) ? '?v=' . filemtime(public_path('assets/css/custom.css')) : '' }}">

<!-- إخفاء أيقونة إعدادات العرض (المسنن) وأيقونة الرسائل في واجهة الطالب -->
<style>
.switcher-icon { display: none !important; }
.messages-dropdown { display: none !important; }
</style>
<!-- إخفاء أيقونة البحث على الجوال لتوفير مساحة -->
<style>
@media (max-width: 991px) {
    .Search-element { display: none !important; }
}
</style>

{{-- جوال: السايدبار تحت الناف + بوردر أساسي علوي (بعد كل أنماط الثيم) --}}
<style>
@media (max-width: 991.98px) {
    html body.student-panel .app-header {
        z-index: 110 !important;
    }
    html body.student-panel #sidebar.app-sidebar {
        top: 4.15rem !important;
        inset-block-start: 4.15rem !important;
        bottom: 0 !important;
        inset-block-end: 0 !important;
        height: auto !important;
        z-index: 104 !important;
        border-top: 3px solid var(--primary-color, #0162e8) !important;
        border-start-end-radius: 1.15rem !important;
        border-end-end-radius: 1.15rem !important;
        overflow: hidden !important;
    }
}
</style>

@stack('styles')
