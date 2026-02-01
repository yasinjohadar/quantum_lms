<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'أكاديمية كوانتوم')</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
<link rel="stylesheet" href="{{ asset('assets/icon-fonts/fontawesome/css/all.min.css') }}">
<link rel="stylesheet" href="{{ asset('frontend/css/custom.css') }}{{ file_exists(public_path('frontend/css/custom.css')) ? '?v=' . filemtime(public_path('frontend/css/custom.css')) : '' }}">
@stack('styles')