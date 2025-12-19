@extends('student.layouts.master')

@section('page-title', $quiz->title)

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="row">
        <div class="col-lg-9">
            <!-- عداد الوقت -->
            @if($quiz->duration_minutes)
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

            <!-- Progress Bar -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">التقدم</span>
                        <span class="fw-semibold" id="progress-text">0 / {{ $questions->count() }}</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar" role="progressbar" id="progress-bar" style="width: 0%"></div>
                    </div>
                </div>
            </div>

            <!-- السؤال الحالي -->
            <div class="card" id="question-card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-question-circle me-2"></i>
                            سؤال <span id="current-question-number">1</span> من {{ $questions->count() }}
                        </h5>
                        <div>
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-star me-1"></i>
                                <span id="current-question-points">0</span> نقطة
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body" id="question-content">
                    <!-- سيتم تحميل محتوى السؤال هنا -->
                </div>
            </div>

            <!-- Navigation -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <button type="button" class="btn btn-outline-secondary" id="prev-btn" disabled>
                    <i class="bi bi-arrow-right me-1"></i>
                    السابق
                </button>
                <div>
                    <button type="button" class="btn btn-outline-primary" id="save-btn">
                        <i class="bi bi-save me-1"></i>
                        حفظ
                    </button>
                </div>
                <button type="button" class="btn btn-outline-primary" id="next-btn">
                    التالي
                    <i class="bi bi-arrow-left ms-1"></i>
                </button>
            </div>
        </div>

        <!-- Sidebar: قائمة الأسئلة -->
        <div class="col-lg-3">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-list-ul me-2"></i>
                        قائمة الأسئلة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group" id="questions-list">
                        @foreach($questions as $index => $question)
                            @php
                                $answer = $answers[$question->id] ?? null;
                                $isAnswered = $answer && ($answer->answer || $answer->answer_text || $answer->selected_options || $answer->numeric_answer);
                            @endphp
                            <button type="button" 
                                    class="list-group-item list-group-item-action question-nav-btn {{ $index === 0 ? 'active' : '' }} {{ $isAnswered ? 'answered' : '' }}"
                                    data-question-id="{{ $question->id }}"
                                    data-question-index="{{ $index }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="bi bi-{{ $isAnswered ? 'check-circle-fill text-success' : 'circle' }} me-2"></i>
                                        سؤال {{ $index + 1 }}
                                    </span>
                                    @if($isAnswered)
                                        <span class="badge bg-success-transparent text-success">مُجاب</span>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer">
                    <form id="submit-quiz-form" method="POST" action="{{ route('student.quizzes.submit', $attempt->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100" id="submit-quiz-btn">
                            <i class="bi bi-send me-1"></i>
                            إرسال الاختبار
                        </button>
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
    .question-nav-btn.answered {
        background-color: #d1e7dd;
    }
    .question-nav-btn.active {
        background-color: #0d6efd;
        color: white;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/quiz-timer.js') }}"></script>
<script src="{{ asset('js/auto-save-answer.js') }}"></script>
<script src="{{ asset('js/question-types.js') }}"></script>
<script>
    const questions = @json($questions->map(function($q) {
        return [
            'id' => $q->id,
            'title' => $q->title,
            'content' => $q->content,
            'type' => $q->type,
            'default_points' => $q->default_points ?? 0,
            'options' => $q->options->map(function($opt) {
                return [
                    'id' => $opt->id,
                    'content' => $opt->content,
                    'is_correct' => $opt->is_correct,
                ];
            })
        ];
    }));
    
    const answers = @json($answers->mapWithKeys(function($a) {
        return [$a->question_id => [
            'selected_options' => $a->selected_options,
            'answer_text' => $a->answer_text,
            'numeric_answer' => $a->numeric_answer,
            'matching_pairs' => $a->matching_pairs,
            'ordering' => $a->ordering,
            'fill_blanks_answers' => $a->fill_blanks_answers,
        ]];
    }));
    
    let currentQuestionIndex = 0;
    const saveUrl = '{{ route("student.quizzes.save-answer", $attempt->id) }}';
    const csrfToken = '{{ csrf_token() }}';

    // تحميل سؤال
    function loadQuestion(index) {
        if (index < 0 || index >= questions.length) return;
        
        currentQuestionIndex = index;
        const question = questions[index];
        
        // تحديث رقم السؤال
        document.getElementById('current-question-number').textContent = index + 1;
        document.getElementById('current-question-points').textContent = question.default_points;
        
        // تحديث قائمة الأسئلة
        document.querySelectorAll('.question-nav-btn').forEach((btn, i) => {
            btn.classList.remove('active');
            if (i === index) {
                btn.classList.add('active');
            }
        });
        
        // تحديث أزرار التنقل
        document.getElementById('prev-btn').disabled = index === 0;
        document.getElementById('next-btn').disabled = index === questions.length - 1;
        
        // تحديث Progress
        const answeredCount = Object.keys(answers).length;
        const progress = ((answeredCount / questions.length) * 100).toFixed(0);
        document.getElementById('progress-bar').style.width = progress + '%';
        document.getElementById('progress-text').textContent = answeredCount + ' / ' + questions.length;
        
        // تحميل محتوى السؤال (سيتم إضافة هذا لاحقاً)
        loadQuestionContent(question, answers[question.id] || {});
    }
    
    function loadQuestionContent(question, answer) {
        // سيتم إضافة هذا في component
        const contentDiv = document.getElementById('question-content');
        contentDiv.innerHTML = '<p>Loading...</p>'; // Placeholder
    }
    
    // Navigation
    document.getElementById('prev-btn').addEventListener('click', () => {
        if (currentQuestionIndex > 0) {
            loadQuestion(currentQuestionIndex - 1);
        }
    });
    
    document.getElementById('next-btn').addEventListener('click', () => {
        if (currentQuestionIndex < questions.length - 1) {
            loadQuestion(currentQuestionIndex + 1);
        }
    });
    
    // Navigation from list
    document.querySelectorAll('.question-nav-btn').forEach((btn, index) => {
        btn.addEventListener('click', () => {
            loadQuestion(index);
        });
    });
    
    // Auto-save
    const autoSave = new AutoSaveAnswer({
        formId: 'answer-form',
        saveUrl: saveUrl,
        interval: 30000
    });
    
    // Initialize
    loadQuestion(0);
    
    @if($quiz->duration_minutes)
        const timer = new QuizTimer({
            remainingTime: {{ $attempt->remaining_time ?? ($quiz->duration_minutes * 60) }},
            updateUrl: '{{ route("student.quizzes.time", $attempt->id) }}',
            onTimeout: function() {
                document.getElementById('submit-quiz-form').submit();
            }
        });
        timer.start();
    @endif
</script>
@endpush

