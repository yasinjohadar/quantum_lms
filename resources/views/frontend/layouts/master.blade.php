<!DOCTYPE html>
<html lang="en" dir="rtl" data-bs-theme="light">

<head>
    @include('frontend.layouts.head')
</head>

<body class="custom-cursor">

    @include('frontend.layouts.navbar')

    @yield('content')

    @include('frontend.layouts.footer')

    @include('frontend.layouts.script')
    
    @stack('scripts')

</body>

</html>
