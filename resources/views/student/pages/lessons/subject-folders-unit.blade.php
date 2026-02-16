@extends('student.layouts.master')

@section('page-title')
    {{ $unit->title }} - {{ $subject->name }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">{{ $unit->title }}</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.subjects') }}">المواد الدراسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.subjects.show', $subject) }}">{{ $subject->name }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.subjects.folders', $subject) }}">عرض المجلدات</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.subjects.folders.section', [$subject, $section]) }}">{{ $section->title }}</a></li>
                        <li class="breadcrumb-item active">{{ $unit->title }}</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('student.subjects.show', $subject) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-card-list me-1"></i> العرض العادي
            </a>
        </div>

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
                    <div id="unitVideoPlayerContainer" class="ratio ratio-16x9 bg-dark rounded overflow-hidden position-relative" style="display: none;" data-progress-url-base="{{ url('student/lessons') }}">
                        <iframe id="unitVideoIframe" title="" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy" class="position-absolute top-0 start-0 w-100 h-100" style="display: none;"></iframe>
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
            <div class="accordion" id="unitContentAccordion">
                @foreach($visibleLessons as $lessonIndex => $lesson)
                    @php
                        $hasVideo = !empty($lesson->embed_url);
                        $actualType = $hasVideo ? $lesson->actual_video_type : '';
                        $embedUrl = $hasVideo ? $lesson->embed_url : '';
                        $iframeUrl = $actualType === 'youtube' ? $embedUrl . (str_contains($embedUrl, '?') ? '&' : '?') . 'rel=0&modestbranding=1' : ($actualType === 'vimeo' ? $embedUrl . (str_contains($embedUrl, '?') ? '&' : '?') . 'title=0&byline=0&portrait=0' : '');
                        $posterUrl = $lesson->thumbnail ? asset('storage/' . $lesson->thumbnail) : '';
                    @endphp
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="lesson-heading-{{ $lesson->id }}">
                            <button class="accordion-button unit-lesson-video-btn {{ $lessonIndex > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#lesson-{{ $lesson->id }}" aria-expanded="{{ $lessonIndex === 0 ? 'true' : 'false' }}"
                                data-lesson-id="{{ $lesson->id }}"
                                data-embed-url="{{ $hasVideo ? e($embedUrl) : '' }}"
                                data-video-type="{{ e($actualType) }}"
                                data-iframe-url="{{ $hasVideo && in_array($actualType, ['youtube', 'vimeo']) ? e($iframeUrl) : '' }}"
                                data-poster="{{ e($posterUrl) }}">
                                <i class="bi bi-play-circle me-2 text-primary"></i>
                                {{ $lesson->title }}
                            </button>
                        </h2>
                        <div id="lesson-{{ $lesson->id }}" class="accordion-collapse collapse {{ $lessonIndex === 0 ? 'show' : '' }}" data-bs-parent="#unitContentAccordion">
                            <div class="accordion-body">
                                @if($lesson->description)
                                    <p class="text-muted small mb-3">{{ \Illuminate\Support\Str::limit($lesson->description, 150) }}</p>
                                @endif
                                <a href="{{ route('student.lessons.show.folders', $lesson) }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-play-fill me-1"></i> عرض الدرس
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
                @if(isset($unit->questions) && $unit->questions->count() > 0)
                    @foreach($unit->questions as $qIndex => $question)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="question-heading-{{ $question->id }}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-{{ $question->id }}" aria-expanded="false">
                                    <i class="bi bi-question-circle me-2 text-info"></i>
                                    {{ $question->title ?? \Illuminate\Support\Str::limit($question->content, 50) }}
                                </button>
                            </h2>
                            <div id="question-{{ $question->id }}" class="accordion-collapse collapse" data-bs-parent="#unitContentAccordion">
                                <div class="accordion-body">
                                    <a href="{{ route('student.questions.start.specific', $question->id) }}" class="btn btn-info btn-sm">
                                        <i class="bi bi-play-fill me-1"></i> بدء السؤال
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
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
<script src="https://player.vimeo.com/api/player.js"></script>
<script>
(function() {
    var placeholder = document.getElementById('unitVideoPlayerPlaceholder');
    var container = document.getElementById('unitVideoPlayerContainer');
    var iframeEl = document.getElementById('unitVideoIframe');
    var videoEl = document.getElementById('unitVideoNative');
    var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
    var progressUrlBase = container ? (container.getAttribute('data-progress-url-base') || '') : '';
    var THROTTLE_MS = 15000;
    var lastSentAt = 0;
    var currentLessonId = null;
    var vimeoPlayer = null;

    function sendProgress(lessonId, timeSpentSeconds, lastPositionSeconds, progressPercentage) {
        if (!lessonId || !progressUrlBase) return;
        if (Date.now() - lastSentAt < THROTTLE_MS && progressPercentage < 100) return;
        lastSentAt = Date.now();
        var url = progressUrlBase + '/' + lessonId + '/progress';
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                time_spent_seconds: timeSpentSeconds,
                last_position_seconds: lastPositionSeconds,
                progress_percentage: progressPercentage
            })
        }).catch(function() {});
    }

    function initVimeoProgressForUnit(lessonId) {
        if (!iframeEl || !lessonId || typeof Vimeo === 'undefined') return;
        if (vimeoPlayer) try { vimeoPlayer.destroy(); } catch (e) {}
        vimeoPlayer = new Vimeo.Player(iframeEl);
        vimeoPlayer.getDuration().then(function(duration) {
            vimeoPlayer.on('timeupdate', function(data) {
                var sec = Math.floor(data.seconds);
                var pct = duration > 0 ? Math.min(100, (data.seconds / duration) * 100) : 0;
                sendProgress(lessonId, sec, sec, pct);
            });
            vimeoPlayer.on('pause', function(data) {
                var sec = Math.floor(data.seconds);
                var pct = duration > 0 ? Math.min(100, (data.seconds / duration) * 100) : 0;
                sendProgress(lessonId, sec, sec, pct);
            });
            vimeoPlayer.on('ended', function() {
                sendProgress(lessonId, Math.floor(duration), Math.floor(duration), 100);
            });
        }).catch(function() {});
    }

    function switchToLesson(btn) {
        if (!btn || !container) return;
        var embedUrl = btn.getAttribute('data-embed-url');
        var videoType = btn.getAttribute('data-video-type') || '';
        var iframeUrl = btn.getAttribute('data-iframe-url') || '';
        var poster = btn.getAttribute('data-poster') || '';
        var lessonId = btn.getAttribute('data-lesson-id') || null;

        if (vimeoPlayer) { try { vimeoPlayer.destroy(); } catch (e) {} vimeoPlayer = null; }
        currentLessonId = null;

        if (!embedUrl || !embedUrl.trim()) {
            if (placeholder) placeholder.style.display = '';
            container.style.display = 'none';
            if (iframeEl) { iframeEl.style.display = 'none'; iframeEl.src = ''; }
            if (videoEl) { videoEl.style.display = 'none'; videoEl.pause(); videoEl.querySelector('source') && (videoEl.querySelector('source').src = ''); }
            return;
        }

        if (placeholder) placeholder.style.display = 'none';
        container.style.display = '';

        if (videoType === 'youtube' || videoType === 'vimeo') {
            if (iframeEl) {
                var src = iframeUrl || embedUrl;
                src += (src.indexOf('?') !== -1 ? '&' : '?') + 'autoplay=1';
                iframeEl.src = src;
                iframeEl.style.display = '';
            }
            if (videoEl) { videoEl.style.display = 'none'; videoEl.pause(); }
            if (videoType === 'vimeo' && lessonId) {
                currentLessonId = lessonId;
                iframeEl.addEventListener('load', function onLoad() {
                    iframeEl.removeEventListener('load', onLoad);
                    setTimeout(function() { initVimeoProgressForUnit(lessonId); }, 500);
                });
            }
        } else {
            if (iframeEl) { iframeEl.style.display = 'none'; iframeEl.src = ''; }
            if (videoEl) {
                videoEl.querySelectorAll('source').forEach(function(s) { s.src = embedUrl; });
                videoEl.poster = poster || '';
                videoEl.style.display = '';
                videoEl.load();
                videoEl.play();
            }
        }

        document.querySelectorAll('.unit-lesson-video-btn').forEach(function(b) {
            b.classList.remove('active');
        });
        btn.classList.add('active');
    }

    document.querySelectorAll('.unit-lesson-video-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            switchToLesson(this);
        }, true);
    });

    document.addEventListener('DOMContentLoaded', function() {
        var firstBtn = document.querySelector('.unit-lesson-video-btn');
        if (firstBtn && firstBtn.getAttribute('data-embed-url')) {
            switchToLesson(firstBtn);
        }
    });
})();
</script>
@endpush
@endif
