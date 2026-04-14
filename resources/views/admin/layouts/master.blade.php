<!DOCTYPE html>
<html lang="en" dir="rtl" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="light" data-toggled="close">

<head>

    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    {{-- يتطلب npm run build على السيرفر أو رفع public/build؛ وإلا يُتخطى (بدون إشعارات Echo فقط) --}}
    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['resources/js/echo-notifications.js'])
    @endif
    @endauth
    @if(isset($userSessionId))
        <meta name="session-id" content="{{ $userSessionId }}">
        <meta name="session-activity-api" content="{{ $sessionActivityApiUrl }}">
    @endif
    <title> @yield('page-title')</title>
    <meta name="Description" content="أفضل موقع للاعلانات المبوبة">
    <meta name="Author" content="claudSoft">
    <meta name="keywords" content="إعلانات , لوحة التحكم">

    @include('admin.layouts.head')
    @stack('styles')
</head>

<body>


    @include('admin.layouts.switcher')


    <!-- Loader -->
    <div id="loader">
        <img src="{{asset('assets/images/media/loader.svg')}}" alt="">
    </div>
    <!-- Loader -->

    <div class="page">

        @include('partials.server-realtime-hints')

        @include('admin.layouts.main-header')



        @include('admin.layouts.offcanvas-sidebar')



        @include('admin.layouts.main-sidebar')


        @yield('content')


        @include('admin.layouts.footer')

    </div>
    @include('admin.layouts.footer-scripts')
    @stack('scripts')

</body>

</html>