<!DOCTYPE html>
<html lang="ar" dir="rtl" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="light" data-toggled="close">

<head>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page-title') — معاينة</title>
    <meta name="Description" content="معاينة اختبار">
    @include('student.layouts.head')
    <style>
        .quiz-preview-banner {
            position: sticky;
            top: 0;
            z-index: 1080;
            background: linear-gradient(90deg, #f59e0b, #fbbf24);
            color: #1f2937;
            padding: .65rem 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,.12);
        }
        .quiz-preview-banner .btn {
            white-space: nowrap;
        }
        .page.quiz-preview-mode .app-sidebar,
        body.quiz-preview-mode .app-sidebar {
            display: none !important;
        }
        .page.quiz-preview-mode .main-content.app-content {
            margin-inline-start: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
    </style>
</head>

<body class="quiz-preview-mode">
    <div id="loader">
        <img src="{{ asset('assets/images/media/loader.svg') }}" alt="">
    </div>

    <div class="quiz-preview-banner">
        <div class="container-fluid d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2 fw-semibold">
                <i class="bi bi-eye"></i>
                <span>وضع معاينة تجريبية — لا تُحفظ الإجابات ولا تُحتسب في الإحصائيات</span>
            </div>
            <div class="d-flex gap-2">
                @isset($previewReturnUrl)
                    <a href="{{ route('admin.quizzes.preview.exit', $quiz->id ?? request()->route('quiz')) }}"
                       class="btn btn-sm btn-dark">
                        <i class="bi bi-x-lg me-1"></i> إنهاء المعاينة
                    </a>
                    <a href="{{ $previewReturnUrl }}" class="btn btn-sm btn-outline-dark bg-white">
                        <i class="bi bi-arrow-right me-1"></i> العودة للاختبار
                    </a>
                @endisset
            </div>
        </div>
    </div>

    <div class="page quiz-preview-mode">
        @yield('content')
    </div>

    @include('student.layouts.footer-scripts')
</body>

</html>
