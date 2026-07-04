@extends('student.layouts.master')

@section('page-title')
    {{ $unit->title }} - {{ $subject->name }}
@stop

@push('styles')
<style>
    .unit-video-player {
        width: min(100%, calc((100vh - 220px) * 16 / 9));
        aspect-ratio: 16 / 9;
        max-height: calc(100vh - 220px);
        margin-inline: auto;
    }

    .unit-video-player iframe,
    .unit-video-player video {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    /* كروت الدروس — مظهر أوضح وجذاب */
    .lesson-row-card {
        border-radius: 0.75rem;
        border: 1px solid rgba(13, 110, 253, 0.14);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04), 0 4px 20px rgba(13, 110, 253, 0.06);
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 255, 1) 100%);
        transition: box-shadow 0.22s ease, border-color 0.22s ease, transform 0.18s ease;
    }
    .lesson-row-card:hover {
        border-color: rgba(13, 110, 253, 0.28);
        box-shadow: 0 6px 24px rgba(13, 110, 253, 0.12), 0 2px 10px rgba(0, 0, 0, 0.05);
        transform: translateY(-1px);
    }
    .lesson-row-card__inner {
        position: relative;
        min-height: 3.5rem;
    }
    .lesson-row-card__inner::before {
        content: '';
        position: absolute;
        inset-inline-start: 0;
        top: 0.65rem;
        bottom: 0.65rem;
        width: 4px;
        border-radius: 4px;
        background: linear-gradient(180deg, #0d6efd 0%, #86b7fe 100%);
        opacity: 0.95;
    }
    .lesson-row-card__btn {
        transition: background 0.2s ease, color 0.2s ease;
    }
    .lesson-row-card__btn:hover {
        background: rgba(13, 110, 253, 0.07) !important;
    }
    .unit-lesson-video-btn.active,
    .section-lesson-video-btn.active {
        background: linear-gradient(90deg, rgba(13, 110, 253, 0.12) 0%, rgba(13, 110, 253, 0.04) 100%) !important;
        color: #0a58ca !important;
    }
    .unit-lesson-video-btn.active .lesson-row-card__title,
    .section-lesson-video-btn.active .lesson-row-card__title {
        color: #084298;
    }
    .lesson-row-card__num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.85rem;
        height: 1.85rem;
        padding: 0 0.35rem;
        font-size: 0.8rem;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        background: linear-gradient(145deg, #0d6efd 0%, #6ea8fe 100%);
        border-radius: 0.5rem;
        box-shadow: 0 2px 8px rgba(13, 110, 253, 0.35);
        margin-inline-end: 0.6rem;
    }
    .lesson-row-card__play {
        font-size: 1.35rem;
        opacity: 0.92;
        filter: drop-shadow(0 1px 2px rgba(13, 110, 253, 0.25));
    }
    .lesson-row-card__title {
        font-weight: 600;
        letter-spacing: 0.01em;
        color: #1e293b;
    }
    .lesson-row-card__actions {
        background: rgba(255, 255, 255, 0.65) !important;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
    [data-theme-mode="dark"] .lesson-row-card {
        background: linear-gradient(145deg, rgba(28, 31, 40, 0.98) 0%, rgba(18, 20, 26, 1) 100%);
        border-color: rgba(255, 255, 255, 0.1);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
    }
    [data-theme-mode="dark"] .lesson-row-card:hover {
        border-color: rgba(110, 168, 254, 0.35);
    }
    [data-theme-mode="dark"] .lesson-row-card__title {
        color: #f1f5f9;
    }
    [data-theme-mode="dark"] .lesson-row-card__actions {
        background: rgba(22, 25, 32, 0.75) !important;
    }
    [data-theme-mode="dark"] .unit-lesson-video-btn.active .lesson-row-card__title,
    [data-theme-mode="dark"] .section-lesson-video-btn.active .lesson-row-card__title {
        color: #9ec5fe;
    }
</style>
@endpush

@section('content')
<div class="main-content app-content">
    <div class="container-fluid pt-3">

        @include('student.pages.lessons.partials.subject-content-breadcrumb', [
            'subject' => $subject,
            'section' => $section,
            'unit' => $unit,
        ])

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if($unit->description)
            <div class="card mb-4">
                <div class="card-body">
                    <p class="mb-0 text-muted">{{ $unit->description }}</p>
                </div>
            </div>
        @endif

        @if($visibleLessons->count() > 0)
            {{-- مشغّل الفيديو في الأعلى --}}
            <div id="unitVideoPlayerCard" class="card mb-4">
                <div class="card-body">
                    <div id="unitVideoPlayerPlaceholder" class="text-center py-5 text-muted bg-light rounded">
                        <i class="bi bi-collection-play display-5 d-block mb-2"></i>
                        <p class="mb-0">اختر درساً من القائمة لمشاهدة الفيديو</p>
                    </div>
                    <div id="unitVideoPlayerContainer" class="unit-video-player bg-dark rounded overflow-hidden position-relative" style="display: none;" data-progress-url-base="{{ url('student/lessons') }}">
                        <iframe id="unitVideoIframe" title="" src="" frameborder="0" referrerpolicy="strict-origin-when-cross-origin" allow="autoplay; fullscreen; picture-in-picture; encrypted-media" allowfullscreen loading="eager" class="position-absolute top-0 start-0 w-100 h-100" style="display: none;"></iframe>
                        <video id="unitVideoNative" controls class="position-absolute top-0 start-0 w-100 h-100" controlsList="nodownload" style="display: none;">
                            <source src="" type="video/mp4">
                            <source src="" type="video/webm">
                            <source src="" type="video/ogg">
                            المتصفح لا يدعم تشغيل الفيديو.
                        </video>
                    </div>
                </div>
            </div>
        @endif

        @if($unitQuizzes->count() > 0)
            {{-- اختبارات الوحدة (نفس ترتيب الأدمن: أولاً) --}}
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="mb-3 text-info fw-semibold small">
                        <i class="bi bi-clipboard-check me-1"></i>
                        اختبارات الوحدة ({{ $unitQuizzes->count() }})
                    </h6>
                    <div class="list-group list-group-flush">
                        @foreach($unitQuizzes as $quiz)
                        <div class="list-group-item d-flex align-items-center justify-content-between px-0 py-2 border-0 bg-info-transparent rounded mb-2">
                            <div class="d-flex align-items-center flex-grow-1">
                                <div class="bg-info rounded-circle d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;">
                                    <i class="bi bi-clipboard-check text-white small"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0 small fw-medium">{{ $quiz->title }}</p>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="text-muted" style="font-size:0.65rem;">
                                            <i class="bi bi-question-circle me-1"></i>{{ $quiz->questions_count ?? $quiz->questions->count() ?? 0 }} سؤال
                                        </span>
                                        @if($quiz->duration_minutes)
                                        <span class="text-muted" style="font-size:0.65rem;">
                                            <i class="bi bi-clock me-1"></i>{{ $quiz->duration_minutes }} دقيقة
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('student.quizzes.start', $quiz) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-play-fill me-1"></i> بدء الاختبار
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if($visibleLessons->count() > 0 || (isset($unit->questions) && $unit->questions->count() > 0))
            @if($visibleLessons->count() > 0)
                <div class="unit-lessons-list {{ (isset($unit->questions) && $unit->questions->count() > 0) ? 'mb-3' : '' }}">
                    @foreach($visibleLessons as $lesson)
                        @php
                            $hasVideo = !empty($lesson->embed_url);
                            $actualType = $hasVideo ? $lesson->actual_video_type : '';
                            $embedUrl = $hasVideo ? $lesson->embed_url : '';
                            $iframeUrl = $actualType === 'youtube' ? $embedUrl . (str_contains($embedUrl, '?') ? '&' : '?') . 'rel=0&modestbranding=1' : ($actualType === 'vimeo' ? $embedUrl . (str_contains($embedUrl, '?') ? '&' : '?') . 'title=0&byline=0&portrait=0' : '');
                            $posterUrl = $lesson->thumbnail ? media_public_url($lesson->thumbnail) : '';
                        @endphp
                        <div class="lesson-row-card accordion-item border-0 mb-3 overflow-hidden" id="lesson-heading-{{ $lesson->id }}">
                            <div class="lesson-row-card__inner d-flex align-items-stretch">
                                <button type="button"
                                    class="lesson-row-card__btn unit-lesson-video-btn flex-grow-1 d-flex align-items-center flex-wrap gap-2 py-3 ps-4 pe-3 border-0 bg-transparent text-start text-primary"
                                    data-lesson-id="{{ $lesson->id }}"
                                    data-embed-url="{{ $hasVideo ? e($embedUrl) : '' }}"
                                    data-video-type="{{ e($actualType) }}"
                                    data-iframe-url="{{ $hasVideo && in_array($actualType, ['youtube', 'vimeo']) ? e($iframeUrl) : '' }}"
                                    data-poster="{{ e($posterUrl) }}">
                                    <span class="d-inline-flex align-items-center flex-shrink-0">
                                        <span class="lesson-row-card__num">{{ $loop->iteration }}</span>
                                        <i class="bi bi-play-circle-fill lesson-row-card__play me-2 text-primary"></i>
                                        <span class="lesson-row-card__title">{{ $lesson->title }}</span>
                                    </span>
                                    @if($lesson->book_page_from !== null || $lesson->book_page_to !== null)
                                        <span class="text-muted small fw-normal">
                                            <i class="bi bi-journal-bookmark me-1"></i>
                                            @if($lesson->book_page_from !== null && $lesson->book_page_to !== null)
                                                صفحات من {{ $lesson->book_page_from }} إلى {{ $lesson->book_page_to }}
                                            @elseif($lesson->book_page_from !== null)
                                                من صفحة {{ $lesson->book_page_from }}
                                            @else
                                                إلى صفحة {{ $lesson->book_page_to }}
                                            @endif
                                        </span>
                                    @endif
                                </button>
                                <div class="lesson-row-card__actions d-flex align-items-center gap-1 px-2 flex-shrink-0 border-start border-secondary border-opacity-25" onclick="event.stopPropagation()">
                                    @if($lesson->quizzes && $lesson->quizzes->count() > 0)
                                        <a href="{{ route('student.quizzes.start', $lesson->quizzes->first()) }}" class="btn btn-sm btn-link p-1 text-info" title="بدء الاختبار" aria-label="بدء الاختبار">
                                            <i class="bi bi-clipboard-check fs-5"></i>
                                        </a>
                                    @endif
                                    @include('student.pages.lessons.partials.lesson-attachment-quick-link', ['lesson' => $lesson])
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            @if(isset($unit->questions) && $unit->questions->count() > 0)
                <div class="accordion" id="unitQuestionsAccordion">
                    @foreach($unit->questions as $qIndex => $question)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="question-heading-{{ $question->id }}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-{{ $question->id }}" aria-expanded="false">
                                    <i class="bi bi-question-circle me-2 text-info"></i>
                                    {{ \Illuminate\Support\Str::limit(strip_tags($question->title ?? $question->content ?? ''), 50) }}
                                </button>
                            </h2>
                            <div id="question-{{ $question->id }}" class="accordion-collapse collapse" data-bs-parent="#unitQuestionsAccordion">
                                <div class="accordion-body">
                                    <a href="{{ route('student.questions.start.specific', $question->id) }}" class="btn btn-info btn-sm">
                                        <i class="bi bi-play-fill me-1"></i> بدء السؤال
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @else
            @if($unitQuizzes->count() === 0)
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-folder-x fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="mb-2">لا يوجد محتوى</h5>
                    <p class="text-muted">لا توجد دروس أو اختبارات أو أسئلة في هذه الوحدة</p>
                </div>
            </div>
            @endif
        @endif
    </div>
</div>
@stop

@if($visibleLessons->count() > 0)
@push('scripts')
<script>
(function() {
    var placeholder = document.getElementById('unitVideoPlayerPlaceholder');
    var container   = document.getElementById('unitVideoPlayerContainer');
    var iframeEl    = document.getElementById('unitVideoIframe');
    var videoEl     = document.getElementById('unitVideoNative');
    var lessonsList = document.querySelector('.unit-lessons-list');

    if (!container || !lessonsList) return;

    // Reset both iframe and native player before any switch
    function resetPlayer() {
        if (iframeEl) { iframeEl.removeAttribute('src'); iframeEl.style.display = 'none'; }
        if (videoEl)  { videoEl.pause(); videoEl.removeAttribute('src'); videoEl.querySelectorAll('source').forEach(function(s) { s.removeAttribute('src'); }); videoEl.load(); videoEl.style.display = 'none'; }
    }

    function detectType(videoType, embedUrl, iframeUrl) {
        if (videoType === 'youtube' || videoType === 'vimeo' || videoType === 'upload') return videoType;
        var url = (iframeUrl || embedUrl || '').toLowerCase();
        if (url.indexOf('youtube.com') !== -1 || url.indexOf('youtu.be') !== -1) return 'youtube';
        if (url.indexOf('vimeo.com') !== -1 || url.indexOf('player.vimeo.com') !== -1) return 'vimeo';
        return 'upload';
    }

    function extractVimeoId(url) {
        if (!url) return null;
        var m = String(url).match(/(?:player\.)?vimeo\.com\/(?:.*\/)?(\d+)(?:$|[?&#])/i);
        return m && m[1] ? m[1] : null;
    }

    function buildIframeSrc(type, embedUrl, iframeUrl) {
        var base = iframeUrl || embedUrl || '';
        if (type === 'vimeo' && base.indexOf('player.vimeo.com/video/') === -1) {
            var id = extractVimeoId(base);
            if (id) {
                base = 'https://player.vimeo.com/video/' + id;
                var hMatch = String(embedUrl || '').match(/[?&]h=([^&]+)/i);
                if (hMatch) {
                    base += (base.indexOf('?') !== -1 ? '&' : '?') + 'h=' + encodeURIComponent(hMatch[1]);
                }
            }
        }
        if (!base) return '';

        var sep = base.indexOf('?') !== -1 ? '&' : '?';
        if (type === 'vimeo') {
            // muted=1 helps autoplay succeed in Chrome/Safari; user can unmute in player
            base += sep + 'autoplay=1&muted=1';
        } else {
            base += sep + 'autoplay=1';
        }
        return base;
    }

    function assignIframeSrc(src) {
        if (!iframeEl || !src) return;
        iframeEl.src = 'about:blank';
        setTimeout(function() {
            if (!iframeEl) return;
            iframeEl.src = src;
            iframeEl.style.display = '';
        }, 0);
    }

    // Main lesson switching
    function switchToLesson(btn) {
        var embedUrl  = btn.getAttribute('data-embed-url') || '';
        var videoType = btn.getAttribute('data-video-type') || '';
        var iframeUrl = btn.getAttribute('data-iframe-url') || '';
        var poster    = btn.getAttribute('data-poster') || '';
        var resolvedType = detectType(videoType, embedUrl, iframeUrl);

        resetPlayer();

        if (!embedUrl.trim()) {
            if (placeholder) placeholder.style.display = '';
            container.style.display = 'none';
            return;
        }

        if (placeholder) placeholder.style.display = 'none';
        container.style.display = '';

        if ((resolvedType === 'youtube' || resolvedType === 'vimeo') && iframeEl) {
            var src = buildIframeSrc(resolvedType, embedUrl, iframeUrl);
            if (!src) return;
            if (resolvedType === 'vimeo') {
                assignIframeSrc(src);
            } else {
                iframeEl.src = src;
                iframeEl.style.display = '';
            }
        } else if (videoEl) {
            videoEl.querySelectorAll('source').forEach(function(s) { s.src = embedUrl; });
            videoEl.poster = poster;
            videoEl.style.display = '';
            videoEl.load();
            videoEl.play().catch(function() {});
        }

        lessonsList.querySelectorAll('.unit-lesson-video-btn').forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');
    }

    // Click handling on list container
    lessonsList.addEventListener('click', function(e) {
        var btn = e.target.closest('.unit-lesson-video-btn');
        if (!btn) return;
        e.preventDefault();
        try {
            switchToLesson(btn);
        } catch (err) {
            if (window && window.console) console.error('Lesson switch failed:', err);
        }
    });

    // Auto-play first lesson
    function autoPlayFirst() {
        var first = lessonsList.querySelector('.unit-lesson-video-btn.active') || lessonsList.querySelector('.unit-lesson-video-btn');
        if (!first) return;
        if ((first.getAttribute('data-embed-url') || '').trim()) {
            try {
                switchToLesson(first);
            } catch (err) {
                if (window && window.console) console.error('Initial lesson load failed:', err);
            }
        }
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', autoPlayFirst);
    else autoPlayFirst();
})();
</script>
@endpush
@endif
