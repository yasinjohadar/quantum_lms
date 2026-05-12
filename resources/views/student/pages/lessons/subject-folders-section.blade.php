@extends('student.layouts.master')

@section('page-title')
    {{ $section->title }} - {{ $subject->name }}
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

        @if($section->type === \App\Models\SubjectSection::TYPE_QUIZZES)
            {{-- قسم الاختبارات: أقسام فرعية ثم الوحدات، الاختبارات تظهر داخل صفحة الوحدة --}}
            @if($children->count() > 0)
                <div class="mb-4">
                    <h5 class="mb-3 fw-semibold"><i class="bi bi-folder2 me-2 text-warning"></i> أقسام فرعية</h5>
                    <div class="row g-3">
                        @foreach($children as $child)
                            <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                <a href="{{ route('student.subjects.folders.section', [$subject, $child]) }}" class="text-decoration-none text-reset">
                                    <div class="card custom-card border folder-card">
                                        <div class="card-body text-center py-4">
                                            <i class="bi bi-folder2 text-warning" style="font-size: 2.5rem;"></i>
                                            <h6 class="card-title mt-2 mb-0 fw-semibold">{{ $child->title }}</h6>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($units->count() > 0)
                <div>
                    <h5 class="mb-3 fw-semibold"><i class="bi bi-folder2 me-2 text-secondary"></i> الوحدات</h5>
                    <div class="row g-3">
                        @foreach($units as $unit)
                            <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                <a href="{{ route('student.subjects.folders.unit', [$subject, $section, $unit]) }}" class="text-decoration-none text-reset">
                                    <div class="card custom-card border folder-card">
                                        <div class="card-body text-center py-4">
                                            <i class="bi bi-folder2 text-secondary" style="font-size: 2.5rem;"></i>
                                            <h6 class="card-title mt-2 mb-0 fw-semibold">{{ $unit->title }}</h6>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(($directQuizzes ?? collect())->count() > 0)
                <div class="mt-4">
                    <h5 class="mb-3 fw-semibold"><i class="bi bi-clipboard-check me-2 text-info"></i> اختبارات القسم المباشرة</h5>
                    <div class="list-group">
                        @foreach($directQuizzes as $quiz)
                            <a href="{{ route('student.quizzes.start', $quiz) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span>{{ $quiz->title }}</span>
                                <span class="badge bg-info">بدء</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($children->count() === 0 && $units->count() === 0)
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-folder-x fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="mb-2">لا يوجد محتوى</h5>
                        <p class="text-muted">لا توجد أقسام فرعية أو وحدات في هذا القسم</p>
                    </div>
                </div>
            @endif
        @else
            {{-- قسم الدروس: مشغّل الدروس المباشرة أولاً (مثل صفحة الوحدة) ثم أقسام فرعية ووحدات --}}
            @if(($directLessons ?? collect())->count() > 0)
                <div class="mb-4">
                    <h5 class="mb-3 fw-semibold"><i class="bi bi-play-circle me-2 text-success"></i> دروس القسم المباشرة</h5>

                    <div id="sectionVideoPlayerCard" class="card mb-4">
                        <div class="card-body">
                            <div id="sectionVideoPlayerPlaceholder" class="text-center py-5 text-muted bg-light rounded">
                                <i class="bi bi-collection-play display-5 d-block mb-2"></i>
                                <p class="mb-0">اختر درساً من القائمة لمشاهدة الفيديو</p>
                            </div>
                            <div id="sectionVideoPlayerContainer" class="unit-video-player bg-dark rounded overflow-hidden position-relative" style="display: none;" data-progress-url-base="{{ url('student/lessons') }}">
                                <iframe id="sectionVideoIframe" title="" src="" frameborder="0" referrerpolicy="strict-origin-when-cross-origin" allow="autoplay; fullscreen; picture-in-picture; encrypted-media" allowfullscreen loading="eager" class="position-absolute top-0 start-0 w-100 h-100" style="display: none;"></iframe>
                                <video id="sectionVideoNative" controls class="position-absolute top-0 start-0 w-100 h-100" controlsList="nodownload" style="display: none;">
                                    <source src="" type="video/mp4">
                                    <source src="" type="video/webm">
                                    <source src="" type="video/ogg">
                                    المتصفح لا يدعم تشغيل الفيديو.
                                </video>
                            </div>
                        </div>
                    </div>

                    <div class="section-lessons-list">
                        @foreach($directLessons as $lesson)
                            @php
                                $hasVideo = !empty($lesson->embed_url);
                                $actualType = $hasVideo ? $lesson->actual_video_type : '';
                                $embedUrl = $hasVideo ? $lesson->embed_url : '';
                                $iframeUrl = $actualType === 'youtube' ? $embedUrl . (str_contains($embedUrl, '?') ? '&' : '?') . 'rel=0&modestbranding=1' : ($actualType === 'vimeo' ? $embedUrl . (str_contains($embedUrl, '?') ? '&' : '?') . 'title=0&byline=0&portrait=0' : '');
                                $posterUrl = $lesson->thumbnail ? media_public_url($lesson->thumbnail) : '';
                            @endphp
                            <div class="lesson-row-card accordion-item border-0 mb-3 overflow-hidden" id="section-lesson-heading-{{ $lesson->id }}">
                                <div class="lesson-row-card__inner d-flex align-items-stretch">
                                    <button type="button"
                                        class="lesson-row-card__btn section-lesson-video-btn flex-grow-1 d-flex align-items-center flex-wrap gap-2 py-3 ps-4 pe-3 border-0 bg-transparent text-start text-primary"
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
                                        <a href="{{ route('student.lessons.show.folders', $lesson) }}" class="btn btn-sm btn-link p-1 text-secondary" title="صفحة الدرس" aria-label="صفحة الدرس">
                                            <i class="bi bi-box-arrow-up-left fs-6"></i>
                                        </a>
                                        @if($lesson->quizzes && $lesson->quizzes->count() > 0)
                                            <a href="{{ route('student.quizzes.start', $lesson->quizzes->first()) }}" class="btn btn-sm btn-link p-1 text-info" title="بدء الاختبار" aria-label="بدء الاختبار">
                                                <i class="bi bi-clipboard-check fs-5"></i>
                                            </a>
                                        @endif
                                        @if($lesson->attachments && $lesson->attachments->count() > 0)
                                            @php $firstAtt = $lesson->attachments->first(); @endphp
                                            <a href="{{ $firstAtt->access_url }}" target="_blank" rel="noopener noreferrer" @if($firstAtt->type !== 'link' && $firstAtt->is_downloadable) download @endif class="btn btn-sm btn-link p-1 text-success" title="تحميل/عرض الملحق" aria-label="الملحق">
                                                <i class="bi bi-paperclip fs-5"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($children->count() > 0)
                <div class="mb-4">
                    <h5 class="mb-3 fw-semibold"><i class="bi bi-folder2 me-2 text-warning"></i> أقسام فرعية</h5>
                    <div class="row g-3">
                        @foreach($children as $child)
                            <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                <a href="{{ route('student.subjects.folders.section', [$subject, $child]) }}" class="text-decoration-none text-reset">
                                    <div class="card custom-card border folder-card">
                                        <div class="card-body text-center py-4">
                                            <i class="bi bi-folder2 text-warning" style="font-size: 2.5rem;"></i>
                                            <h6 class="card-title mt-2 mb-0 fw-semibold">{{ $child->title }}</h6>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($units->count() > 0)
                <div class="mb-4">
                    <h5 class="mb-3 fw-semibold"><i class="bi bi-folder2 me-2 text-secondary"></i> الوحدات</h5>
                    <div class="row g-3">
                        @foreach($units as $unit)
                            <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                <a href="{{ route('student.subjects.folders.unit', [$subject, $section, $unit]) }}" class="text-decoration-none text-reset">
                                    <div class="card custom-card border folder-card">
                                        <div class="card-body text-center py-4">
                                            <i class="bi bi-folder2 text-secondary" style="font-size: 2.5rem;"></i>
                                            <h6 class="card-title mt-2 mb-0 fw-semibold">{{ $unit->title }}</h6>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(($directQuizzes ?? collect())->count() > 0)
                <div class="mt-4">
                    <h5 class="mb-3 fw-semibold"><i class="bi bi-clipboard-check me-2 text-info"></i> اختبارات القسم المباشرة</h5>
                    <div class="list-group">
                        @foreach($directQuizzes as $quiz)
                            <a href="{{ route('student.quizzes.start', $quiz) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span>{{ $quiz->title }}</span>
                                <span class="badge bg-info">بدء</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($children->count() === 0 && $units->count() === 0 && ($directLessons ?? collect())->count() === 0 && ($directQuizzes ?? collect())->count() === 0)
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-folder-x fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="mb-2">لا يوجد محتوى</h5>
                        <p class="text-muted">لا توجد أقسام فرعية أو وحدات في هذا القسم</p>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
@stop

@if(($section->type !== \App\Models\SubjectSection::TYPE_QUIZZES) && ($directLessons ?? collect())->count() > 0)
@push('scripts')
<script>
(function() {
    var placeholder = document.getElementById('sectionVideoPlayerPlaceholder');
    var container   = document.getElementById('sectionVideoPlayerContainer');
    var iframeEl    = document.getElementById('sectionVideoIframe');
    var videoEl     = document.getElementById('sectionVideoNative');
    var lessonsList = document.querySelector('.section-lessons-list');

    if (!container || !lessonsList) return;

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

        lessonsList.querySelectorAll('.section-lesson-video-btn').forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');
    }

    lessonsList.addEventListener('click', function(e) {
        var btn = e.target.closest('.section-lesson-video-btn');
        if (!btn) return;
        e.preventDefault();
        try {
            switchToLesson(btn);
        } catch (err) {
            if (window && window.console) console.error('Section lesson switch failed:', err);
        }
    });

    function autoPlayFirst() {
        var first = lessonsList.querySelector('.section-lesson-video-btn.active') || lessonsList.querySelector('.section-lesson-video-btn');
        if (!first) return;
        if ((first.getAttribute('data-embed-url') || '').trim()) {
            try {
                switchToLesson(first);
            } catch (err) {
                if (window && window.console) console.error('Initial section lesson load failed:', err);
            }
        }
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', autoPlayFirst);
    else autoPlayFirst();
})();
</script>
@endpush
@endif
