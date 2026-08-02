@extends('student.layouts.master')

@section('page-title')
    {{ $lesson->title }} - عرض المجلدات
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">{{ $lesson->title }}</h4>
                <p class="mb-0 text-muted">
                    {{ $subject->name }} - {{ $lesson->unit->title }}
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('student.lessons.show', $lesson) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-card-list me-1"></i> العرض العادي
                </a>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.subjects') }}">المواد الدراسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.subjects.show', $subject->id) }}">{{ $subject->name }}</a></li>
                        <li class="breadcrumb-item active">{{ $lesson->title }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="col-12">
            <!-- الفيديو في الأعلى -->
            <div class="card mb-4">
                <div class="card-body">
                    @if($lesson->embed_url)
                        @php $actualType = $lesson->actual_video_type; @endphp
                        <div class="ratio ratio-16x9 mb-3 bg-dark rounded overflow-hidden">
                            @if($actualType === 'youtube')
                                <iframe src="{{ $lesson->embed_url }}?rel=0&modestbranding=1" title="{{ $lesson->title }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy"></iframe>
                            @elseif($actualType === 'vimeo')
                                <iframe id="vimeo-player-iframe" src="{{ $lesson->embed_url }}?title=0&byline=0&portrait=0" title="{{ $lesson->title }}" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                            @elseif($actualType === 'upload')
                                <video controls class="w-100 h-100" poster="{{ $lesson->thumbnail ? media_public_url($lesson->thumbnail) : '' }}" controlsList="nodownload">
                                    <source src="{{ $lesson->embed_url }}" type="video/mp4">
                                    <source src="{{ $lesson->embed_url }}" type="video/webm">
                                    <source src="{{ $lesson->embed_url }}" type="video/ogg">
                                    المتصفح لا يدعم تشغيل الفيديو.
                                </video>
                            @else
                                <video controls class="w-100 h-100" poster="{{ $lesson->thumbnail ? media_public_url($lesson->thumbnail) : '' }}">
                                    <source src="{{ $lesson->embed_url }}" type="video/mp4">
                                    المتصفح لا يدعم تشغيل الفيديو.
                                </video>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-5 text-muted bg-light rounded">
                            <i class="bi bi-collection-play display-5 d-block mb-2"></i>
                            <p class="mb-0">لم يتم ضبط فيديو لهذا الدرس بعد.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- أوكورديون تحت الفيديو -->
            <div class="accordion" id="lessonFoldersAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingContent">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContent" aria-expanded="true">
                            <i class="bi bi-folder me-2"></i> محتوى المادة: {{ $subject->name }}
                        </button>
                    </h2>
                    <div id="collapseContent" class="accordion-collapse collapse show" data-bs-parent="#lessonFoldersAccordion">
                        <div class="accordion-body p-0">
                            @include('student.pages.lessons.partials.course-content-card', ['suffix' => 'folders', 'lesson_route' => 'student.lessons.show.folders'])
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingStatus">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStatus" aria-expanded="false">
                            <i class="bi bi-check-circle me-2"></i> حالة الدرس
                        </button>
                    </h2>
                    <div id="collapseStatus" class="accordion-collapse collapse" data-bs-parent="#lessonFoldersAccordion">
                        <div class="accordion-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    @if($lessonCompletion)
                                        <span class="badge bg-{{ $lessonCompletion->status === 'completed' ? 'success' : 'info' }} fs-6">
                                            <i class="bi bi-{{ $lessonCompletion->status === 'completed' ? 'check-circle-fill' : 'calendar-check' }} me-1"></i>
                                            {{ $lessonCompletion->status === 'completed' ? 'تم الإكمال' : 'تم الحضور' }}
                                        </span>
                                        <small class="text-muted d-block mt-1"><i class="bi bi-clock me-1"></i>{{ $lessonCompletion->marked_at->format('Y-m-d H:i') }}</small>
                                        @if($lessonCompletion->progress_percentage !== null || $lessonCompletion->time_spent !== null)
                                            <div class="mt-2">
                                                @if($lessonCompletion->progress_percentage !== null)
                                                    <small class="d-block mb-1">نسبة المشاهدة: <strong>{{ number_format((float) $lessonCompletion->progress_percentage, 1) }}%</strong></small>
                                                    <div class="progress" style="height: 8px;">
                                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min(100, (float) $lessonCompletion->progress_percentage) }}%" aria-valuenow="{{ $lessonCompletion->progress_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                @endif
                                                @if($lessonCompletion->time_spent !== null)
                                                    <small class="text-muted d-block mt-1"><i class="bi bi-clock-history me-1"></i> وقت المشاهدة: {{ \App\Models\LessonCompletion::formatDurationSeconds((int) $lessonCompletion->time_spent) }}</small>
                                                @endif
                                            </div>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary fs-6"><i class="bi bi-circle me-1"></i> لم يتم التحديد</span>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-{{ $lessonCompletion && $lessonCompletion->status === 'completed' ? 'success' : 'outline-success' }} btn-sm" id="mark-completed-btn" data-status="completed">
                                    <i class="bi bi-check-circle me-1"></i> تم الإكمال
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @if($lesson->attachments->count() > 0)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingAttachments">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAttachments" aria-expanded="false">
                            <i class="bi bi-paperclip me-2"></i> مرفقات الدرس
                            @if($lesson->attachments->count() > 1)
                                <span class="badge bg-success-transparent text-success ms-2">{{ $lesson->attachments->count() }}</span>
                            @endif
                        </button>
                    </h2>
                    <div id="collapseAttachments" class="accordion-collapse collapse" data-bs-parent="#lessonFoldersAccordion">
                        <div class="accordion-body">
                            <div class="list-group">
                                @foreach($lesson->attachments as $attachment)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><i class="bi {{ $attachment->type_icon }} me-2"></i>{{ $attachment->title }}</h6>
                                                @if($attachment->description)<p class="text-muted mb-1 small">{{ $attachment->description }}</p>@endif
                                                @if($attachment->file_size)<small class="text-muted"><i class="bi bi-file-earmark me-1"></i>{{ $attachment->formatted_file_size }}</small>@endif
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            @if($attachment->type === 'link')
                                                <a href="{{ $attachment->access_url }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right me-1"></i> فتح الرابط</a>
                                            @elseif($attachment->is_downloadable)
                                                <a href="{{ $attachment->access_url }}" download class="btn btn-sm btn-outline-success"><i class="bi bi-download me-1"></i> تحميل</a>
                                            @else
                                                <a href="{{ $attachment->access_url }}" target="_blank" class="btn btn-sm btn-outline-info"><i class="bi bi-eye me-1"></i> عرض</a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @php $lessonInteractiveExperiences = $lessonInteractiveExperiences ?? collect(); @endphp
                @if($lessonInteractiveExperiences->count() > 0)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingInteractive">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInteractive" aria-expanded="false">
                            <i class="bi bi-joystick me-2"></i> اختبارات تفاعلية لهذا الدرس
                        </button>
                    </h2>
                    <div id="collapseInteractive" class="accordion-collapse collapse" data-bs-parent="#lessonFoldersAccordion">
                        <div class="accordion-body p-0">
                            @include('student.pages.lessons.partials.ile-lesson-cards', [
                                'experiences' => $lessonInteractiveExperiences,
                                'ileAttempts' => $ileAttempts ?? collect(),
                                'bare' => true,
                            ])
                        </div>
                    </div>
                </div>
                @endif
                @if($lessonQuizzes->count() > 0)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingQuizzes">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQuizzes" aria-expanded="false">
                            <i class="bi bi-clipboard-check me-2"></i> اختبارات هذا الدرس
                        </button>
                    </h2>
                    <div id="collapseQuizzes" class="accordion-collapse collapse" data-bs-parent="#lessonFoldersAccordion">
                        <div class="accordion-body">
                            <div class="row">
                                @foreach($lessonQuizzes as $quiz)
                                    @php
                                        $attempt = $quizAttempts[$quiz->id] ?? null;
                                        $hasAttempt = $attempt !== null;
                                        $isInProgress = $attempt && $attempt->status === 'in_progress';
                                        $isCompleted = $attempt && in_array($attempt->status, ['completed', 'timed_out']);
                                    @endphp
                                    <div class="col-md-6 mb-3">
                                        <div class="card border">
                                            <div class="card-body">
                                                <div class="d-flex align-items-start">
                                                    @if($quiz->image)
                                                        <img src="{{ media_public_url($quiz->image) }}" alt="{{ $quiz->title }}" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-primary-transparent rounded d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;"><i class="bi bi-clipboard-check text-primary fs-4"></i></div>
                                                    @endif
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                                            <h6 class="mb-0 fw-semibold">{{ $quiz->title }}</h6>
                                                            @if($hasAttempt)
                                                                @if($isInProgress)<span class="badge bg-warning"><i class="bi bi-clock me-1"></i> جاري</span>
                                                                @elseif($isCompleted)<span class="badge bg-{{ $attempt->passed ? 'success' : 'danger' }}"><i class="bi bi-{{ $attempt->passed ? 'check-circle' : 'x-circle' }} me-1"></i>{{ $attempt->passed ? 'نجح' : 'رسب' }}</span>@endif
                                                            @else<span class="badge bg-secondary"><i class="bi bi-circle me-1"></i> لم يتم البدء</span>@endif
                                                        </div>
                                                        @if($quiz->description)<p class="text-muted small mb-2">{{ \Illuminate\Support\Str::limit($quiz->description, 60) }}</p>@endif
                                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                                            @if($quiz->duration_minutes)<span class="badge bg-info-transparent text-info"><i class="bi bi-clock me-1"></i>{{ $quiz->duration_minutes }} دقيقة</span>@endif
                                                            <span class="badge bg-success-transparent text-success"><i class="bi bi-question-circle me-1"></i>{{ $quiz->questions->count() }} سؤال</span>
                                                            <span class="badge bg-warning-transparent text-warning"><i class="bi bi-star me-1"></i>{{ $quiz->total_points }} نقطة</span>
                                                        </div>
                                                        @if($isInProgress)
                                                            <a href="{{ route('student.quizzes.show', ['quiz' => $quiz->id, 'attempt' => $attempt->id]) }}" class="btn btn-sm btn-warning"><i class="bi bi-arrow-left-circle me-1"></i> متابعة الاختبار</a>
                                                        @elseif($isCompleted)
                                                            <div class="d-flex gap-2">
                                                                <a href="{{ route('student.quizzes.result', ['quiz' => $quiz->id, 'attempt' => $attempt->id]) }}" class="btn btn-sm btn-info"><i class="bi bi-eye me-1"></i> عرض النتيجة</a>
                                                                @if($quiz->max_attempts == 0 || $attempt->attempt_number < $quiz->max_attempts)
                                                                    <a href="{{ route('student.quizzes.start', $quiz->id) }}" class="btn btn-sm btn-primary"><i class="bi bi-arrow-clockwise me-1"></i> محاولة جديدة</a>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <a href="{{ route('student.quizzes.start', $quiz->id) }}" class="btn btn-sm btn-primary"><i class="bi bi-play-circle me-1"></i> بدء الاختبار</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if($questions->count() > 0)
                    @php
                        $allCompleted = true;
                        $hasInProgress = false;
                        $completedCount = 0;
                        foreach($questions as $question) {
                            $attempt = $questionAttempts[$question->id] ?? null;
                            $isCompleted = $attempt && in_array($attempt->status, ['completed', 'timed_out']);
                            $isInProgress = $attempt && $attempt->status === 'in_progress';
                            if ($isInProgress) $hasInProgress = true;
                            if ($isCompleted) $completedCount++; else $allCompleted = false;
                        }
                    @endphp
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingQuestions">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQuestions" aria-expanded="false">
                            <i class="bi bi-question-circle me-2"></i> الأسئلة المرتبطة بهذا الدرس <span class="badge bg-info ms-2">{{ $completedCount }} / {{ $questions->count() }}</span>
                        </button>
                    </h2>
                    <div id="collapseQuestions" class="accordion-collapse collapse" data-bs-parent="#lessonFoldersAccordion">
                        <div class="accordion-body">
                            @if($allCompleted)
                                <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i><strong>تهانينا!</strong> لقد أكملت جميع الأسئلة.</div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('student.questions.report', $lesson->id) }}" class="btn btn-primary"><i class="bi bi-file-text me-2"></i> عرض التقرير النهائي</a>
                                    <a href="{{ route('student.questions.start', ['lesson_id' => $lesson->id]) }}" class="btn btn-outline-primary"><i class="bi bi-arrow-clockwise me-2"></i> إعادة المحاولة</a>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-question-circle display-4 text-info mb-3 d-block"></i>
                                    <h5 class="mb-2">ابدأ الإجابة على الأسئلة</h5>
                                    <p class="text-muted mb-4">سيتم عرض الأسئلة بشكل متسلسل. يجب إكمال كل سؤال قبل الانتقال للسؤال التالي.</p>
                                    @if($hasInProgress)
                                        <a href="{{ route('student.questions.start', ['lesson_id' => $lesson->id]) }}" class="btn btn-warning btn-lg"><i class="bi bi-arrow-left-circle me-2"></i> متابعة الأسئلة</a>
                                    @else
                                        <a href="{{ route('student.questions.start', ['lesson_id' => $lesson->id]) }}" class="btn btn-primary btn-lg"><i class="bi bi-play-circle me-2"></i> بدء الإجابة على الأسئلة</a>
                                    @endif
                                    @if($completedCount > 0)<div class="mt-3"><small class="text-muted">تم إكمال {{ $completedCount }} من {{ $questions->count() }} سؤال</small></div>@endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<style>
.content-tree-flat .list-group-item { background: transparent; }
.content-tree-flat .list-group-item:not(.current-lesson) { border: 1px solid #e9ecef !important; }
.content-tree-flat .list-group-item.current-lesson { background-color: #ccc; color: #212529; }
.content-tree-flat .list-group-item.current-lesson .text-white,
.content-tree-flat .list-group-item.current-lesson i { color: #212529 !important; }
.content-tree-flat .list-group-item.list-group-item-action.current-lesson:hover,
.content-tree-flat .list-group-item.list-group-item-action.current-lesson:focus { background-color: #bbb; color: #212529; }
.content-tree-flat .list-group-item.list-group-item-action.current-lesson:hover .text-white,
.content-tree-flat .list-group-item.list-group-item-action.current-lesson:hover i { color: #212529 !important; }
[data-theme-mode="dark"] .content-tree-flat .list-group-item { background: transparent; }
[data-theme-mode="dark"] .content-tree-flat .list-group-item:not(.current-lesson) { border: 1px solid rgba(255, 255, 255, 0.12) !important; }
[data-theme-mode="dark"] .content-tree-flat .list-group-item.current-lesson { background-color: #1a1d21; color: #fff; }
[data-theme-mode="dark"] .content-tree-flat .list-group-item.current-lesson .text-white,
[data-theme-mode="dark"] .content-tree-flat .list-group-item.current-lesson .text-muted,
[data-theme-mode="dark"] .content-tree-flat .list-group-item.current-lesson i { color: #fff !important; }
[data-theme-mode="dark"] .content-tree-flat .list-group-item.list-group-item-action.current-lesson:hover,
[data-theme-mode="dark"] .content-tree-flat .list-group-item.list-group-item-action.current-lesson:focus { background-color: #25282c; color: #fff; }
[data-theme-mode="dark"] .content-tree-flat .list-group-item.list-group-item-action.current-lesson:hover .text-white,
[data-theme-mode="dark"] .content-tree-flat .list-group-item.list-group-item-action.current-lesson:hover .text-muted,
[data-theme-mode="dark"] .content-tree-flat .list-group-item.list-group-item-action.current-lesson:hover i { color: #fff !important; }
</style>
@stop

@push('scripts')
@if($lesson->embed_url && $lesson->actual_video_type === 'vimeo')
<script src="https://player.vimeo.com/api/player.js"></script>
<script>
(function() {
    const progressUrl = '{{ route("student.lessons.progress", $lesson) }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const THROTTLE_MS = 15000;
    let lastSentAt = 0;
    let vimeoPlayer = null;
    function sendProgress(timeSpentSeconds, lastPositionSeconds, progressPercentage) {
        if (Date.now() - lastSentAt < THROTTLE_MS && progressPercentage < 100) return;
        lastSentAt = Date.now();
        fetch(progressUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ time_spent_seconds: timeSpentSeconds, last_position_seconds: lastPositionSeconds, progress_percentage: progressPercentage })
        }).catch(function() {});
    }
    function initVimeoProgress() {
        var iframe = document.getElementById('vimeo-player-iframe');
        if (!iframe || typeof Vimeo === 'undefined' || !Vimeo || !Vimeo.Player) return;
        var src = iframe.getAttribute('src') || '';
        if (src.indexOf('player.vimeo.com') === -1) return;
        try {
            vimeoPlayer = new Vimeo.Player(iframe);
        } catch (e) { vimeoPlayer = null; return; }
        try {
        vimeoPlayer.getDuration().then(function(duration) {
            vimeoPlayer.on('timeupdate', function(data) {
                var sec = Math.floor(data.seconds);
                var pct = duration > 0 ? Math.min(100, (data.seconds / duration) * 100) : 0;
                sendProgress(sec, sec, pct);
            });
            vimeoPlayer.on('pause', function(data) {
                var sec = Math.floor(data.seconds);
                var pct = duration > 0 ? Math.min(100, (data.seconds / duration) * 100) : 0;
                sendProgress(sec, sec, pct);
            });
            vimeoPlayer.on('ended', function() { sendProgress(Math.floor(duration), Math.floor(duration), 100); });
        }).catch(function() {});
        } catch (e) {}
        window.addEventListener('pagehide', function() {
            if (vimeoPlayer) {
                try {
                    vimeoPlayer.getCurrentTime().then(function(sec) {
                        vimeoPlayer.getDuration().then(function(dur) {
                            var pct = dur > 0 ? Math.min(100, (sec / dur) * 100) : 0;
                            fetch(progressUrl, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                body: JSON.stringify({ time_spent_seconds: Math.floor(sec), last_position_seconds: Math.floor(sec), progress_percentage: pct }),
                                keepalive: true
                            }).catch(function() {});
                        }).catch(function() {});
                    }).catch(function() {});
                } catch (e) {}
            }
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initVimeoProgress);
    else initVimeoProgress();
})();
</script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash === '#collapseAttachments') {
        var attachmentsPanel = document.getElementById('collapseAttachments');
        if (attachmentsPanel && typeof bootstrap !== 'undefined') {
            bootstrap.Collapse.getOrCreateInstance(attachmentsPanel, { toggle: false }).show();
            setTimeout(function() {
                attachmentsPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 150);
        }
    }

    const markCompletedBtn = document.getElementById('mark-completed-btn');
    if (!markCompletedBtn) return;
    const lessonId = {{ $lesson->id }};
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    markCompletedBtn.addEventListener('click', function() {
        const originalText = markCompletedBtn.innerHTML;
        const originalClass = markCompletedBtn.className;
        markCompletedBtn.disabled = true;
        markCompletedBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>جاري الحفظ...';

        fetch(`{{ route('student.lessons.mark-status', $lesson) }}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ status: 'completed' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('حدث خطأ: ' + (data.message || 'فشل في حفظ الحالة'));
                markCompletedBtn.disabled = false;
                markCompletedBtn.innerHTML = originalText;
                markCompletedBtn.className = originalClass;
            }
        })
        .catch(() => {
            alert('حدث خطأ أثناء الاتصال بالخادم');
            markCompletedBtn.disabled = false;
            markCompletedBtn.innerHTML = originalText;
            markCompletedBtn.className = originalClass;
        });
    });
});
</script>
@endpush
