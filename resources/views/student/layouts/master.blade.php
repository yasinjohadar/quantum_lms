<!DOCTYPE html>
<html lang="ar" dir="rtl" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="light" data-toggled="close">

<head>
    <script>
        document.documentElement.setAttribute('dir', 'rtl');
        try { localStorage.setItem('valexrtl', true); localStorage.removeItem('valexltr'); } catch (e) {}
    </script>

    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        <script>
            window.__echoReverbConfig = @json(config('echo-client'));
        </script>
        @vite(['resources/js/echo-notifications.js'])
    @endif
    @endauth
    <title> @yield('page-title')</title>
    <meta name="Description" content="أكاديمية كوانتم">
    <meta name="Author" content="claudSoft">
    <meta name="keywords" content=" لوحة التحكم">

    @include('student.layouts.head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css" crossorigin="anonymous">
    <style>
        /* عزل اتجاهي (bidi isolate): المعادلة تُعرض LTR داخلياً دوماً حتى داخل صفحة RTL،
           لمنع المتصفح من إعادة ترتيب/عكس رموز محايدة كالأقواس ( ) المحيطة بالمعادلة. */
        .katex-src { display: inline; white-space: normal; direction: ltr; unicode-bidi: isolate; }
        .katex-src[data-display="1"] { display: block; margin: .35rem 0; text-align: center; }
        /* KaTeX لا يضبط اتجاهه فيرث rtl من الصفحة فتُعكس الأقواس المرآتية [ ] في
           الفترات مثل ]0, +∞[ — فرض ltr على مخرجاته يمنع الانعكاس. */
        .katex, .katex * { direction: ltr; }
    </style>
</head>

<body>


    @include('student.layouts.switcher')


    <!-- Loader -->
    <div id="loader">
        <img src="{{asset('assets/images/media/loader.svg')}}" alt="">
    </div>
    <!-- Loader -->

    <div class="page">

        @include('student.layouts.main-header')



        @include('student.layouts.offcanvas-sidebar')



        @include('student.layouts.main-sidebar')


        @yield('content')


        @include('student.layouts.footer')

    </div>
    @include('student.layouts.footer-scripts')

    {{-- KaTeX: CDN أولاً ثم محرك الرسم الموحد --}}
    @include('partials.question-math-scripts')

</body>

</html>
