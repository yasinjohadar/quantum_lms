<!DOCTYPE html>
<html lang="ar" dir="rtl" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="light" data-toggled="close">

<head>

    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title> @yield('page-title')</title>
    <meta name="Description" content="منصة كوانتم">
    <meta name="Author" content="claudSoft">
    <meta name="keywords" content=" لوحة التحكم">

    @include('student.layouts.head')
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


</body>

</html>
