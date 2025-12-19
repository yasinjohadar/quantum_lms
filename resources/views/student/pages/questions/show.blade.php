@extends('student.layouts.master')

@section('page-title', 'الإجابة على السؤال')

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- عداد الوقت -->
            @if($attempt->time_limit)
                <div class="card mb-3" id="timer-card">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center gap-3">
                            <i class="bi bi-clock-history fs-4 text-primary"></i>
                            <div>
                                <h5 class="mb-0" id="timer-display">--:--</h5>
                                <small class="text-muted">الوقت المتبقي</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- السؤال -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-question-circle me-2"></i>
                            {{ $question->title }}
                        </h5>
                        <div>
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-star me-1"></i>
                                {{ $question->default_points ?? 0 }} نقطة
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- محتوى السؤال -->
                    @if($question->content)
                        <div class="mb-4">
                            {!! $question->content !!}
                        </div>
                    @endif

                    <!-- معلومات السؤال -->
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="badge bg-{{ $questionTypeColors[$question->type] ?? 'primary' }}-transparent text-{{ $questionTypeColors[$question->type] ?? 'primary' }}">
                            <i class="bi {{ $questionTypeIcons[$question->type] ?? 'bi-question' }} me-1"></i>
                            {{ $questionTypes[$question->type] ?? $question->type }}
                        </span>
                        @if($question->difficulty)
                            <span class="badge bg-secondary-transparent text-secondary">
                                {{ $questionDifficulties[$question->difficulty] ?? $question->difficulty }}
                            </span>
                        @endif
                    </div>

                    <!-- نموذج الإجابة -->
                    <form id="answer-form" method="POST" action="{{ route('student.questions.submit', $attempt->id) }}">
                        @csrf
                        
                        @include('student.components.questions.' . str_replace('_', '-', $question->type), [
                            'question' => $question,
                            'answer' => $answer
                        ])

                        <!-- أزرار الإجراء -->
                        <div class="d-flex justify-content-between align-items-center mt-4 pt-4 border-top">
                            <div>
                                @if($lesson)
                                    <a href="{{ route('student.lessons.show', $lesson->id) }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-right me-1"></i>
                                        العودة للدرس
                                    </a>
                                @endif
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary" id="save-btn">
                                    <i class="bi bi-save me-1"></i>
                                    حفظ
                                </button>
                                <button type="submit" class="btn btn-primary" id="submit-btn">
                                    <i class="bi bi-send me-1"></i>
                                    إرسال الإجابة
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<!-- End::app-content -->
@endsection

@push('styles')
<style>
    #timer-card {
        transition: all 0.3s ease;
    }
    #timer-card.warning {
        background-color: #fff3cd;
        border-color: #ffc107;
    }
    #timer-card.danger {
        background-color: #f8d7da;
        border-color: #dc3545;
    }
    
    /* إصلاح مشكلة السحب */
    .matching-draggable {
        pointer-events: auto !important;
        -webkit-user-drag: element !important;
        user-select: none !important;
        touch-action: none !important;
    }
    
    .matching-target {
        pointer-events: auto !important;
    }
    
    #left-items, #right-items {
        pointer-events: auto !important;
    }
    
    /* منع التداخل */
    .question-answer * {
        pointer-events: auto;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/quiz-timer.js') }}"></script>
<script src="{{ asset('js/auto-save-answer.js') }}"></script>
<script>
    @if($attempt->time_limit)
        // تهيئة العداد
        const timer = new QuizTimer({
            remainingTime: {{ $attempt->remaining_time ?? $attempt->time_limit }},
            updateUrl: '{{ route("student.questions.time", $attempt->id) }}',
            onTimeout: function() {
                document.getElementById('answer-form').submit();
            },
            onWarning: function(seconds) {
                const card = document.getElementById('timer-card');
                if (seconds <= 60) {
                    card.classList.add('danger');
                } else if (seconds <= 300) {
                    card.classList.add('warning');
                }
            }
        });
        timer.start();
    @endif

    // حفظ تلقائي
    const autoSave = new AutoSaveAnswer({
        formId: 'answer-form',
        saveUrl: '{{ route("student.questions.save-answer", $attempt->id) }}',
        interval: 30000 // 30 ثانية
    });
    autoSave.start();

    // حفظ يدوي
    document.getElementById('save-btn').addEventListener('click', function() {
        autoSave.save();
    });
    
    // إعادة تهيئة matching بعد تحميل المحتوى
    setTimeout(() => {
        if (typeof QuestionTypesHandler !== 'undefined') {
            new QuestionTypesHandler();
        }
    }, 300);
    
    // إضافة event listener للتأكد من عمل السحب
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            const draggables = document.querySelectorAll('.matching-draggable:not(.d-none)');
            draggables.forEach(item => {
                item.draggable = true;
                item.setAttribute('draggable', 'true');
                console.log('Draggable item:', item, 'draggable:', item.draggable);
            });
        }, 500);
    });
</script>
@endpush

