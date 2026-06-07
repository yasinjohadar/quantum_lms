@extends('student.layouts.master')

@include('partials.question-math-assets')

@section('page-title', 'الإجابة على السؤال')

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- عداد الوقت -->
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

            <!-- السؤال -->
            <div class="card" id="student-question-card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="mb-0 flex-grow-1 question-stem question-text-body text-white">
                            <i class="bi bi-question-circle me-2"></i>
                            {!! format_question_markup($question->title) !!}
                        </div>
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
                        <div class="mb-4 question-content-html question-text-body">
                            {!! format_question_markup($question->content) !!}
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

<div id="studentQuestionImageLightbox" class="student-q-img-lightbox" hidden>
    <div class="student-q-img-lightbox__backdrop" role="presentation"></div>
    <button type="button" class="student-q-img-lightbox__close" aria-label="إغلاق">&times;</button>
    <div class="student-q-img-lightbox__inner">
        <img id="studentQuestionImageLightboxImg" src="" alt="">
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
    
    /* Ordering styles */
    .sortable-ghost {
        opacity: 0.4;
    }
    
    .sortable-chosen {
        cursor: move;
    }
    
    .sortable-drag {
        opacity: 0.5;
    }
    
    #ordering-list .list-group-item.dragging {
        opacity: 0.5;
        background-color: #f0f0f0;
    }
    
    #ordering-list .list-group-item {
        cursor: move;
        user-select: none;
        -webkit-user-drag: element;
    }

    #student-question-card .question-stem img,
    #student-question-card .question-content-html img,
    #student-question-card .option-item img {
        max-width: 100% !important;
        width: 100% !important;
        height: auto !important;
        cursor: pointer;
        display: block;
        border-radius: 0.25rem;
        box-sizing: border-box;
    }
    @media (min-width: 768px) {
        #student-question-card .question-stem img,
        #student-question-card .question-content-html img,
        #student-question-card .option-item img {
            max-width: 600px !important;
            width: auto !important;
        }
    }

    .student-q-img-lightbox {
        position: fixed;
        inset: 0;
        z-index: 999999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .student-q-img-lightbox[hidden] {
        display: none !important;
    }
    .student-q-img-lightbox__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.88);
        cursor: pointer;
    }
    .student-q-img-lightbox__close {
        position: fixed;
        z-index: 1000000;
        top: 1rem;
        inset-inline-start: 1rem;
        width: 2.75rem;
        height: 2.75rem;
        border: none;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.95);
        color: #333;
        font-size: 1.75rem;
        line-height: 1;
        cursor: pointer;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.35);
    }
    .student-q-img-lightbox__inner {
        position: relative;
        z-index: 1;
        max-width: min(96vw, 1200px);
        max-height: 90vh;
        pointer-events: none;
    }
    .student-q-img-lightbox__inner img {
        max-width: 100%;
        max-height: 90vh;
        width: auto;
        height: auto;
        object-fit: contain;
        display: block;
        margin: 0 auto;
        border-radius: 0.35rem;
        pointer-events: auto;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="{{ asset('js/quiz-timer.js') }}?v=3"></script>
<script src="{{ asset('js/auto-save-answer.js') }}?v=2"></script>
<script src="{{ asset('js/question-types.js') }}"></script>
<script>
    // تهيئة العداد - دائماً يعرض العداد
    @php
        $timeLimit = $attempt->time_limit ?? 300; // 5 دقائق افتراضية
        $remainingTime = $attempt->remaining_time ?? $timeLimit;
    @endphp
    const timer = new QuizTimer({
        remainingTime: {{ $remainingTime }},
        updateUrl: @if($attempt->time_limit) '{{ route("student.questions.time", $attempt->id) }}' @else null @endif,
        onTimeout: function() {
            alert('انتهى الوقت! سيتم إرسال إجابتك تلقائياً.');
            document.getElementById('answer-form').submit();
        },
        onWarning: function(seconds) {
            const card = document.getElementById('timer-card');
            if (card) {
                if (seconds <= 60) {
                    card.classList.add('danger');
                    card.classList.remove('warning');
                } else if (seconds <= 300) {
                    card.classList.add('warning');
                    card.classList.remove('danger');
                }
            }
        }
    });
    timer.start();

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

    (function setupQuestionImageLightbox() {
        var bound = false;
        function bind() {
            if (bound) return;
            var box = document.getElementById('studentQuestionImageLightbox');
            var bigImg = document.getElementById('studentQuestionImageLightboxImg');
            var root = document.getElementById('question-content') || document.getElementById('student-question-card');
            if (!box || !bigImg || !root) return;
            bound = true;
            if (box.parentNode !== document.body) {
                document.body.appendChild(box);
            }
            var backdrop = box.querySelector('.student-q-img-lightbox__backdrop');
            var closeBtn = box.querySelector('.student-q-img-lightbox__close');
            function openLb(src, alt) {
                bigImg.src = src;
                bigImg.alt = alt || '';
                box.removeAttribute('hidden');
                document.body.style.overflow = 'hidden';
            }
            function closeLb() {
                box.setAttribute('hidden', '');
                bigImg.removeAttribute('src');
                bigImg.alt = '';
                document.body.style.overflow = '';
            }
            document.addEventListener('click', function(e) {
                if (!e.target || !e.target.closest) return;
                var img = e.target.closest('img');
                if (!img || !root.contains(img)) return;
                openLb(img.currentSrc || img.src, img.alt);
            }, true);
            if (backdrop) backdrop.addEventListener('click', closeLb);
            if (closeBtn) closeBtn.addEventListener('click', closeLb);
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !box.hasAttribute('hidden')) {
                    closeLb();
                }
            });
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bind);
        } else {
            bind();
        }
    })();
</script>
@include('partials.question-math-scripts')
@endpush

