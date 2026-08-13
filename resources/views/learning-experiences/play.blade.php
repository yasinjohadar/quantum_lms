<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $experience->title }} — اختبار تفاعلي</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/icon-fonts/bootstrap-icons/icons/font/bootstrap-icons.min.css') }}">
    @vite(['resources/js/interactive-engine/index.js'])
    <style>
        body {
            margin: 0;
            font-family: "Alexandria", "Segoe UI", Tahoma, "Noto Sans Arabic", sans-serif;
            background: #ecfdf5;
            color: #1f2937;
        }
        #ile-root { min-height: 100vh; }
        .ile-preview-banner {
            background: #854d0e; color: #fff; text-align: center; padding: .5rem; font-size: .875rem;
            font-family: "Alexandria", sans-serif;
        }
    </style>
</head>
<body>
@if($isPreview)
    <div class="ile-preview-banner">وضع معاينة — التجربة غير منشورة</div>
@endif
<div id="ile-root" aria-live="polite"></div>
<script>
    window.__interactiveSchema = @json($experience->schema_json);
    window.__interactiveConfig = {
        experienceId: {{ $experience->id }},
        submitUrl: @json(route('learning-experiences.attempts.store', $experience)),
        ttsUrl: @json(route('learning-experiences.tts')),
        csrfToken: @json(csrf_token()),
        schemaVersion: @json($schemaVersion),
        engineVersion: @json($engineVersion),
        isPreview: @json($isPreview),
        feedbackPhrases: @json($feedbackPhrases),
    };
</script>
</body>
</html>
