<div class="rq-stats">
    <div class="rq-stat rq-stat--lessons">
        <div class="rq-stat__label"><i class="bi bi-play-btn"></i> الدروس قيد المراجعة</div>
        <div class="rq-stat__value">{{ number_format($stats['lessons']['pending'] ?? 0) }}</div>
        <div class="rq-stat__meta">
            معتمدة: {{ number_format($stats['lessons']['approved'] ?? 0) }}
            · مرفوضة: {{ number_format($stats['lessons']['rejected'] ?? 0) }}
        </div>
    </div>
    <div class="rq-stat rq-stat--quizzes">
        <div class="rq-stat__label"><i class="bi bi-clipboard-check"></i> الاختبارات قيد المراجعة</div>
        <div class="rq-stat__value">{{ number_format($stats['quizzes']['pending'] ?? 0) }}</div>
        <div class="rq-stat__meta">
            معتمدة: {{ number_format($stats['quizzes']['approved'] ?? 0) }}
            · مرفوضة: {{ number_format($stats['quizzes']['rejected'] ?? 0) }}
        </div>
    </div>
    @if(isset($stats['learning_experiences']))
        <div class="rq-stat rq-stat--quizzes">
            <div class="rq-stat__label"><i class="bi bi-joystick"></i> الاختبارات التفاعلية قيد المراجعة</div>
            <div class="rq-stat__value">{{ number_format($stats['learning_experiences']['pending'] ?? 0) }}</div>
            <div class="rq-stat__meta">
                معتمدة: {{ number_format($stats['learning_experiences']['approved'] ?? 0) }}
                · مرفوضة: {{ number_format($stats['learning_experiences']['rejected'] ?? 0) }}
            </div>
        </div>
    @endif
</div>
