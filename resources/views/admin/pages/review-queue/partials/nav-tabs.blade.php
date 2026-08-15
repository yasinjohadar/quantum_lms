@php
    $stats = $stats ?? ['lessons' => ['pending' => 0], 'quizzes' => ['pending' => 0], 'learning_experiences' => ['pending' => 0]];
    $active = $active ?? 'all';
@endphp
<div class="rq-nav">
    <a href="{{ route('admin.review-queue.index') }}"
       class="rq-nav__pill {{ $active === 'all' ? 'is-active' : '' }}">
        <i class="bi bi-grid"></i>
        جميع العناصر
    </a>
    <a href="{{ route('admin.review-queue.lessons') }}"
       class="rq-nav__pill {{ $active === 'lessons' ? 'is-active' : '' }}">
        <i class="bi bi-play-btn"></i>
        الدروس
        @if(($stats['lessons']['pending'] ?? 0) > 0)
            <span class="rq-nav__badge">{{ $stats['lessons']['pending'] }}</span>
        @endif
    </a>
    <a href="{{ route('admin.review-queue.quizzes') }}"
       class="rq-nav__pill {{ $active === 'quizzes' ? 'is-active' : '' }}">
        <i class="bi bi-clipboard-check"></i>
        الاختبارات
        @if(($stats['quizzes']['pending'] ?? 0) > 0)
            <span class="rq-nav__badge">{{ $stats['quizzes']['pending'] }}</span>
        @endif
    </a>
    <a href="{{ route('admin.review-queue.learning-experiences') }}"
       class="rq-nav__pill {{ $active === 'learning-experiences' ? 'is-active' : '' }}">
        <i class="bi bi-joystick"></i>
        الاختبارات التفاعلية
        @if(($stats['learning_experiences']['pending'] ?? 0) > 0)
            <span class="rq-nav__badge">{{ $stats['learning_experiences']['pending'] }}</span>
        @endif
    </a>
</div>
