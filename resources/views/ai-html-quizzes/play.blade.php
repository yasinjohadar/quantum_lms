<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $quiz->title }} — اختبار تفاعلي</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ahq-ink: #0b3d36;
            --ahq-accent: #0f766e;
            --ahq-glow: #2dd4bf;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Alexandria", "Segoe UI", Tahoma, sans-serif;
            background:
                radial-gradient(900px 420px at 100% -10%, rgba(45, 212, 191, 0.35), transparent 55%),
                radial-gradient(700px 380px at 0% 0%, rgba(56, 189, 248, 0.28), transparent 50%),
                linear-gradient(165deg, #ecfdf5 0%, #e0f2fe 48%, #fef9c3 100%);
            color: var(--ahq-ink);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .ahq-preview-banner {
            background: linear-gradient(90deg, #92400e, #b45309);
            color: #fff;
            text-align: center;
            padding: .55rem 1rem;
            font-size: .85rem;
            font-weight: 600;
            letter-spacing: .01em;
        }
        .ahq-host-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .85rem 1.25rem;
            background: rgba(255, 255, 255, 0.78);
            border-bottom: 1px solid rgba(15, 118, 110, 0.16);
            backdrop-filter: blur(14px);
            box-shadow: 0 8px 28px rgba(15, 118, 110, 0.08);
        }
        .ahq-host-bar h1 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            background: linear-gradient(120deg, #0f766e, #0369a1);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .ahq-status {
            font-size: .82rem;
            font-weight: 600;
            color: #475569;
            padding: .35rem .75rem;
            border-radius: 999px;
            background: rgba(240, 253, 250, 0.9);
            border: 1px solid rgba(45, 212, 191, 0.35);
        }
        .ahq-status.is-ok {
            color: #047857;
            background: #ecfdf5;
            border-color: #6ee7b7;
        }
        .ahq-status.is-err {
            color: #b91c1c;
            background: #fef2f2;
            border-color: #fecaca;
        }
        .ahq-frame-wrap {
            flex: 1;
            padding: .9rem 1rem 1.1rem;
            min-height: 0;
        }
        #ahq-frame {
            width: 100%;
            height: calc(100vh - 96px);
            border: 0;
            border-radius: 22px;
            background: #fff;
            box-shadow:
                0 0 0 1px rgba(15, 118, 110, 0.1),
                0 22px 60px rgba(15, 118, 110, 0.16);
        }
        @media (max-width: 640px) {
            .ahq-host-bar { padding: .7rem .85rem; }
            .ahq-host-bar h1 { font-size: .92rem; }
            #ahq-frame { height: calc(100vh - 118px); border-radius: 14px; }
        }
    </style>
</head>
<body>
@if($isPreview)
    <div class="ahq-preview-banner">وضع معاينة — الاختبار غير منشور</div>
@endif
<header class="ahq-host-bar">
    <h1>{{ $quiz->title }}</h1>
    <div class="ahq-status" id="ahq-status">جاهز للعب</div>
</header>
<div class="ahq-frame-wrap">
    <iframe
        id="ahq-frame"
        title="{{ $quiz->title }}"
        src="{{ $bundleUrl }}"
        sandbox="allow-scripts allow-same-origin"
        referrerpolicy="no-referrer"
    ></iframe>
</div>
<script>
(function () {
    const attemptUrl = @json($attemptUrl);
    const csrf = @json(csrf_token());
    const statusEl = document.getElementById('ahq-status');
    let submitted = false;

    function setStatus(msg, kind) {
        statusEl.textContent = msg;
        statusEl.className = 'ahq-status' + (kind ? ' is-' + kind : '');
    }

    window.addEventListener('message', async function (event) {
        if (!event.data || event.data.type !== 'ile-html-quiz-result') return;
        if (submitted) return;
        const payload = event.data.payload || {};
        submitted = true;
        setStatus('جاري حفظ النتيجة…');

        try {
            const res = await fetch(attemptUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({
                    score: Number(payload.score) || 0,
                    total: Number(payload.total) || 0,
                    percentage: Number(payload.percentage) || 0,
                    durationSeconds: Number(payload.durationSeconds || payload.duration) || 0,
                    answers: Array.isArray(payload.answers) ? payload.answers : [],
                }),
            });
            const data = await res.json();
            if (!res.ok || !data.ok) {
                throw new Error(data.message || 'تعذر حفظ المحاولة');
            }
            setStatus('تم الحفظ — النتيجة: ' + (payload.score || 0) + ' / ' + (payload.total || 0), 'ok');
        } catch (e) {
            submitted = false;
            setStatus(e.message || 'خطأ في الحفظ', 'err');
        }
    });
})();
</script>
</body>
</html>
