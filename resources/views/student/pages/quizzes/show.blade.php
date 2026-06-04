@extends('student.layouts.master')

@section('page-title', $quiz->title)

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header: العنوان الرئيسي فقط -->
        <div class="my-2 my-md-3">
            <h5 class="mb-0 fw-semibold text-body">{{ $quiz->title }}</h5>
        </div>
        <!-- End Page Header -->

        <div class="row">
        <!-- Sidebar: قائمة الأسئلة - يظهر أولاً في RTL -->
        <div class="col-lg-3 order-lg-1 mb-3 mb-lg-0">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-list-ul me-2"></i>
                        قائمة الأسئلة
                    </h6>
                </div>
                <div class="card-body p-2">
                    <div class="d-flex flex-wrap gap-2" id="questions-list">
                        @foreach($questions as $index => $question)
                            @php
                                $answer = $answers[$question->id] ?? null;
                                $isAnswered = $answer && ($answer->answer || $answer->answer_text || $answer->selected_options || $answer->numeric_answer);
                            @endphp
                            <button type="button" 
                                    class="btn {{ $index === 0 ? 'btn-primary' : ($isAnswered ? 'btn-success' : 'btn-outline-secondary') }} question-nav-btn question-nav-btn-compact"
                                    data-question-id="{{ $question->id }}"
                                    data-question-index="{{ $index }}">
                                {{ $index + 1 }}
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer p-2">
                    <form id="submit-quiz-form" method="POST" action="{{ route('student.quizzes.submit', $attempt->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100" id="submit-quiz-btn">
                            <i class="bi bi-send me-1"></i>
                            إرسال الاختبار
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- المحتوى الرئيسي -->
        <div class="col-lg-9 order-lg-2">
            <!-- عداد الوقت -->
            @if($quiz->show_timer)
                @php
                    $hasTimeLimit = $quiz->hasTimeLimit();
                    $initialRemaining = $hasTimeLimit
                        ? ($attempt->remaining_time ?? ($quiz->duration_minutes * 60))
                        : null;
                    $initialElapsed = $hasTimeLimit ? null : $attempt->elapsed_seconds;
                @endphp
                <div class="card mb-3" id="timer-card">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center gap-3">
                            <i class="bi bi-clock-history fs-4 text-primary"></i>
                            <div>
                                <h5 class="mb-0" id="timer-display"
                                    data-timer-mode="{{ $hasTimeLimit ? 'countdown' : 'elapsed' }}"
                                    data-started-at-ms="{{ $attempt->started_at ? $attempt->started_at->getTimestamp() * 1000 : '' }}"
                                    @if($hasTimeLimit)
                                        data-remaining-seconds="{{ (int) $initialRemaining }}"
                                    @else
                                        data-elapsed-seconds="{{ (int) $initialElapsed }}"
                                    @endif
                                >{{ $hasTimeLimit ? ($attempt->formatted_remaining_time ?? '--:--') : $attempt->formatted_elapsed_time }}</h5>
                                <small class="text-muted">{{ $hasTimeLimit ? 'الوقت المتبقي' : 'الوقت المنقضي' }}</small>
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
                <button type="button" class="btn btn-outline-primary" id="next-btn">
                    التالي
                    <i class="bi bi-arrow-left ms-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>
</div>

<!-- معاينة الصورة (بدون Bootstrap Modal — يعمل دائماً) -->
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
        animation: pulse 1s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    #questions-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .question-nav-btn-compact {
        width: 2.5rem;
        height: 2.5rem;
        min-width: 2.5rem;
        min-height: 2.5rem;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
    }
    .question-nav-btn {
        transition: all 0.2s ease;
    }
    .question-nav-btn:hover {
        transform: translateX(-3px);
    }
    .option-item {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .option-item:hover {
        border-color: #0d6efd !important;
        background-color: rgba(13, 110, 253, 0.05);
    }
    .cursor-pointer {
        cursor: pointer;
    }
    .cursor-move {
        cursor: move;
    }
    
    .draggable-item {
        cursor: move;
        user-select: none;
        transition: opacity 0.2s;
    }
    
    .draggable-item:hover {
        opacity: 0.8;
    }
    
    .draggable-item[draggable="true"]:active {
        cursor: grabbing;
    }
    
    .drop-zone {
        min-height: 150px;
        transition: all 0.2s;
    }
    
    .drop-zone.drag-over {
        border-color: #0d6efd !important;
        background-color: rgba(13, 110, 253, 0.1) !important;
    }
    
    .dropped-item {
        display: inline-block;
    }
    
    .min-h-150 {
        cursor: move;
    }
    #question-content {
        min-height: 200px;
    }
    /* جوال: عرض كامل | شاشات ≥768px: 600px كحد أقصى */
    #question-content .question-stem img,
    #question-content .question-content-html img,
    #question-content .option-item img {
        max-width: 100% !important;
        width: 100% !important;
        height: auto !important;
        cursor: pointer;
        display: block;
        border-radius: 0.25rem;
        box-sizing: border-box;
    }
    @media (min-width: 768px) {
        #question-content .question-stem img,
        #question-content .question-content-html img,
        #question-content .option-item img {
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
    .form-check.p-4 {
        transition: all 0.2s ease;
    }
    .form-check.p-4:hover {
        transform: scale(1.02);
    }
    .draggable-item {
        user-select: none;
        transition: opacity 0.2s, transform 0.2s;
    }
    .draggable-item:hover {
        opacity: 0.8;
        transform: scale(1.05);
    }
    .draggable-item[draggable="true"]:active {
        cursor: grabbing;
    }
    .drop-zone {
        min-height: 150px;
        transition: all 0.2s;
    }
    .drop-zone.border-primary {
        border-color: #0d6efd !important;
        background-color: rgba(13, 110, 253, 0.1) !important;
    }
    .dropped-item {
        display: inline-block;
        margin: 0.25rem;
    }
    .min-h-150 {
        min-height: 150px;
    }
    .ordering-item {
        transition: background-color 0.2s, transform 0.2s;
        cursor: move;
    }
    .ordering-item:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }
    .ordering-item.drag-over {
        border-top: 3px solid #0d6efd;
        background-color: rgba(13, 110, 253, 0.1);
    }
    .ordering-item[draggable="true"]:active {
        cursor: grabbing;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/quiz-timer.js') }}?v=5"></script>
<script src="{{ asset('js/auto-save-answer.js') }}?v=2"></script>
<script src="{{ asset('js/question-types.js') }}"></script>
<script>
    @php
        $questionsJson = $questions->map(function($q) {
            return [
                'id' => $q->id,
                'title' => format_question_markup($q->title),
                'content' => format_question_markup($q->content),
                'type' => $q->type,
                'default_points' => $q->default_points ?? 0,
                'options' => $q->options->map(function($opt) {
                    return [
                        'id' => $opt->id,
                        'content' => format_question_markup($opt->content),
                        'is_correct' => $opt->is_correct,
                    ];
                })->values()->toArray()
            ];
        })->values()->toArray();
        
        $answersJson = $answers->mapWithKeys(function($a) {
            return [$a->question_id => [
                'selected_options' => $a->selected_options,
                'answer_text' => $a->answer_text,
                'numeric_answer' => $a->numeric_answer,
                'matching_pairs' => $a->matching_pairs,
                'ordering' => $a->ordering,
                'fill_blanks_answers' => $a->fill_blanks_answers,
                'drag_drop_assignments' => $a->drag_drop_assignments,
            ]];
        })->toArray();
    @endphp
    
    let questions = @json($questionsJson);
    let answers = @json($answersJson);
    
    // Ensure questions is an array
    if (!Array.isArray(questions)) {
        console.error('Questions is not an array:', questions);
        questions = Object.values(questions);
    }
    
    // Ensure answers is an object (it should be)
    if (Array.isArray(answers)) {
        console.error('Answers should be an object, converting...');
        answers = {};
    }
    
    console.log('Quiz initialized with', questions.length, 'questions');

    function countAnsweredQuestions() {
        return Object.keys(answers).filter((k) => {
            const ans = answers[k];
            if (!ans) return false;
            return (
                (Array.isArray(ans.selected_options) && ans.selected_options.length > 0) ||
                !!ans.answer_text ||
                (ans.numeric_answer !== null && ans.numeric_answer !== '') ||
                (ans.matching_pairs && Object.keys(ans.matching_pairs).length > 0) ||
                (Array.isArray(ans.ordering) && ans.ordering.length > 0) ||
                (ans.fill_blanks_answers && Object.keys(ans.fill_blanks_answers).length > 0) ||
                (ans.drag_drop_assignments && Object.keys(ans.drag_drop_assignments).length > 0)
            );
        }).length;
    }
    
    let currentQuestionIndex = 0;
    const saveUrl = '{{ route("student.quizzes.save-answer", $attempt->id) }}';
    const submitUrl = '{{ route("student.quizzes.submit", $attempt->id) }}';
    const resultUrl = '{{ route("student.quizzes.result", ["quiz" => $quiz->id, "attempt" => $attempt->id]) }}';
    const csrfToken = '{{ csrf_token() }}';
    let isSubmittingQuiz = false;

    // تحميل سؤال
    function loadQuestion(index) {
        if (!Array.isArray(questions)) {
            console.error('Questions is not an array');
            return;
        }
        
        if (index < 0 || index >= questions.length) {
            console.error('Invalid question index:', index);
            return;
        }
        
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
        const nextBtn = document.getElementById('next-btn');
        nextBtn.disabled = false;
        if (index === questions.length - 1) {
            nextBtn.className = 'btn btn-danger';
            nextBtn.innerHTML = '<i class="bi bi-send me-1"></i> إرسال الاختبار';
        } else {
            nextBtn.className = 'btn btn-outline-primary';
            nextBtn.innerHTML = 'التالي <i class="bi bi-arrow-left ms-1"></i>';
        }
        
        // تحديث Progress
        const answeredCount = countAnsweredQuestions();
        const progress = questions.length > 0 ? ((answeredCount / questions.length) * 100).toFixed(0) : 0;
        document.getElementById('progress-bar').style.width = progress + '%';
        document.getElementById('progress-text').textContent = answeredCount + ' / ' + questions.length;
        
        // تحميل محتوى السؤال (سيتم إضافة هذا لاحقاً)
        loadQuestionContent(question, answers[question.id] || {});
    }
    
    function loadQuestionContent(question, answer) {
        const contentDiv = document.getElementById('question-content');
        let html = '';
        
        // عنوان السؤال (يُخزَّن كـ HTML من TinyMCE — لا نستخدم escapeHtml)
        if (question.title) {
            html += `<div class="question-stem question-text-body mb-3">${question.title}</div>`;
        }
        
        // محتوى السؤال (لا نعرضه لـ drag_drop و fill_blanks لأنها تحتاج معالجة خاصة)
        if (question.content && question.type !== 'drag_drop' && question.type !== 'fill_blank' && question.type !== 'fill_blanks') {
            html += `<div class="mb-4 p-3 bg-light rounded question-content-html question-text-body">${question.content}</div>`;
        } else if (question.content && question.type === 'drag_drop') {
            // Extract text content without drop-zones div for drag_drop
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = question.content;
            const dropZonesDiv = tempDiv.querySelector('.drop-zones');
            if (dropZonesDiv) {
                const textContent = tempDiv.textContent.trim();
                if (textContent) {
                    html += `<div class="mb-4 p-3 bg-light rounded">${escapeHtml(textContent)}</div>`;
                }
            } else {
                html += `<div class="mb-4 p-3 bg-light rounded question-content-html question-text-body">${question.content}</div>`;
            }
        }
        // Note: fill_blank/fill_blanks content is handled inside renderFillBlank
        
        // خيارات الإجابة حسب نوع السؤال
        html += '<div class="answer-section">';
        
        switch(question.type) {
            case 'single_choice':
                html += renderSingleChoice(question, answer);
                break;
            case 'multiple_choice':
                html += renderMultipleChoice(question, answer);
                break;
            case 'true_false':
                html += renderTrueFalse(question, answer);
                break;
            case 'short_answer':
                html += renderShortAnswer(question, answer);
                break;
            case 'essay':
                html += renderEssay(question, answer);
                break;
            case 'matching':
                html += renderMatching(question, answer);
                break;
            case 'ordering':
                html += renderOrdering(question, answer);
                break;
            case 'fill_blank':
            case 'fill_blanks':
                html += renderFillBlank(question, answer);
                break;
            case 'numeric':
                html += renderNumeric(question, answer);
                break;
            case 'multi_select':
                html += renderMultiSelect(question, answer);
                break;
            case 'drag_drop':
                html += renderDragDrop(question, answer);
                break;
            default:
                html += `<p class="text-muted">نوع السؤال غير مدعوم: ${question.type}</p>`;
        }
        
        html += '</div>';
        contentDiv.innerHTML = html;
        
        // إضافة event listeners للإجابات
        setupAnswerListeners(question);
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /** نص عادي من HTML (لعناصر مثل &lt;option&gt; لا تدعم وسوماً) */
    function plainTextFromHtml(html) {
        if (!html) return '';
        const div = document.createElement('div');
        div.innerHTML = html;
        return div.textContent || div.innerText || '';
    }

    function isTrueFalseTrueLabel(content) {
        const text = plainTextFromHtml(content || '').trim().toLowerCase();
        return ['صح', 'صحيح', 'true', 'نعم'].includes(text);
    }

    function isTrueFalseFalseLabel(content) {
        const text = plainTextFromHtml(content || '').trim().toLowerCase();
        return ['خطأ', 'خطا', 'false', 'لا'].includes(text);
    }

    function trueFalseIconHtml(content) {
        if (isTrueFalseTrueLabel(content)) {
            return '<i class="bi bi-check-circle text-success me-2"></i>';
        }
        if (isTrueFalseFalseLabel(content)) {
            return '<i class="bi bi-x-circle text-danger me-2"></i>';
        }
        return '';
    }
    
    function renderSingleChoice(question, answer) {
        // Handle both array and string formats for selected_options
        let selectedOptions = answer.selected_options || [];
        if (typeof selectedOptions === 'string') {
            selectedOptions = [selectedOptions];
        } else if (!Array.isArray(selectedOptions)) {
            selectedOptions = [];
        }
        // Convert all IDs to strings for comparison
        selectedOptions = selectedOptions.map(id => String(id));
        
        let html = '<div class="options-list">';
        
        question.options.forEach((opt, idx) => {
            // Convert opt.id to string for comparison
            const optIdStr = String(opt.id);
            const isChecked = selectedOptions.includes(optIdStr) || selectedOptions.includes(opt.id) ? 'checked' : '';
            html += `
                <div class="form-check p-3 mb-2 border rounded option-item ${isChecked ? 'border-primary bg-primary-transparent' : ''}">
                    <input class="form-check-input" type="radio" name="answer_${question.id}" 
                           id="opt_${opt.id}" value="${opt.id}" ${isChecked}>
                    <label class="form-check-label question-text-body w-100 cursor-pointer" for="opt_${opt.id}">
                        ${opt.content || ''}
                    </label>
                </div>
            `;
        });
        
        html += '</div>';
        return html;
    }
    
    function renderMultipleChoice(question, answer) {
        // Handle both array and string formats for selected_options
        let selectedOptions = answer.selected_options || [];
        if (typeof selectedOptions === 'string') {
            selectedOptions = [selectedOptions];
        } else if (!Array.isArray(selectedOptions)) {
            selectedOptions = [];
        }
        // Convert all IDs to strings for comparison
        selectedOptions = selectedOptions.map(id => String(id));
        
        let html = '<p class="text-muted mb-3"><i class="bi bi-info-circle me-1"></i> يمكنك اختيار أكثر من إجابة</p>';
        html += '<div class="options-list">';
        
        question.options.forEach((opt, idx) => {
            // Convert opt.id to string for comparison
            const optIdStr = String(opt.id);
            const isChecked = selectedOptions.includes(optIdStr) || selectedOptions.includes(opt.id) ? 'checked' : '';
            html += `
                <div class="form-check p-3 mb-2 border rounded option-item ${isChecked ? 'border-primary bg-primary-transparent' : ''}">
                    <input class="form-check-input" type="checkbox" name="answer_${question.id}[]" 
                           id="opt_${opt.id}" value="${opt.id}" ${isChecked}>
                    <label class="form-check-label question-text-body w-100 cursor-pointer" for="opt_${opt.id}">
                        ${opt.content || ''}
                    </label>
                </div>
            `;
        });
        
        html += '</div>';
        return html;
    }
    
    function renderTrueFalse(question, answer) {
        // Handle both array and string formats
        let selectedOptions = answer.selected_options || [];
        if (typeof selectedOptions === 'string') {
            selectedOptions = [selectedOptions];
        } else if (!Array.isArray(selectedOptions)) {
            selectedOptions = [];
        }
        
        let html = '<div class="d-flex gap-3">';
        
        const sahOption = question.options?.find(opt => isTrueFalseTrueLabel(opt.content));
        const khataOption = question.options?.find(opt => isTrueFalseFalseLabel(opt.content));

        if (!sahOption || !khataOption) {
            const trueChecked = selectedOptions.includes('true') || selectedOptions.includes(true) ? 'checked' : '';
            const falseChecked = selectedOptions.includes('false') || selectedOptions.includes(false) ? 'checked' : '';
            
            html += `
                <div class="form-check p-4 border rounded flex-fill text-center ${trueChecked ? 'border-success bg-success-transparent' : ''}">
                    <input class="form-check-input true-false-radio" type="radio" name="answer_${question.id}" 
                           id="true_${question.id}" value="true" data-tf-choice="true" ${trueChecked}>
                    <label class="form-check-label w-100 cursor-pointer fs-5" for="true_${question.id}">
                        <i class="bi bi-check-circle text-success me-2"></i> صحيح
                    </label>
                </div>
                <div class="form-check p-4 border rounded flex-fill text-center ${falseChecked ? 'border-danger bg-danger-transparent' : ''}">
                    <input class="form-check-input true-false-radio" type="radio" name="answer_${question.id}" 
                           id="false_${question.id}" value="false" data-tf-choice="false" ${falseChecked}>
                    <label class="form-check-label w-100 cursor-pointer fs-5" for="false_${question.id}">
                        <i class="bi bi-x-circle text-danger me-2"></i> خطأ
                    </label>
                </div>
            `;
        } else {
            // Convert string values to option IDs for comparison (backward compatibility)
            const selectedIds = selectedOptions.map(val => {
                // If it's "true" or "false", convert to option ID
                if (val === 'true' || val === true) {
                    return parseInt(sahOption.id, 10);
                } else if (val === 'false' || val === false) {
                    return parseInt(khataOption.id, 10);
                }
                // Otherwise, try to parse as integer (should be option ID)
                return parseInt(val) || val;
            }).map(id => parseInt(id)); // Ensure all are integers for comparison
            
            const sahChecked = selectedIds.includes(parseInt(sahOption.id, 10)) ? 'checked' : '';
            const khataChecked = selectedIds.includes(parseInt(khataOption.id, 10)) ? 'checked' : '';

            html += `
                <div class="form-check p-4 border rounded flex-fill text-center ${sahChecked ? 'border-success bg-success-transparent' : ''}">
                    <input class="form-check-input true-false-radio" type="radio" name="answer_${question.id}"
                           id="option_${sahOption.id}" value="${sahOption.id}" data-tf-choice="true" ${sahChecked}>
                    <label class="form-check-label w-100 cursor-pointer fs-5" for="option_${sahOption.id}">
                        ${trueFalseIconHtml(sahOption.content)} ${sahOption.content || 'صح'}
                    </label>
                </div>
                <div class="form-check p-4 border rounded flex-fill text-center ${khataChecked ? 'border-danger bg-danger-transparent' : ''}">
                    <input class="form-check-input true-false-radio" type="radio" name="answer_${question.id}"
                           id="option_${khataOption.id}" value="${khataOption.id}" data-tf-choice="false" ${khataChecked}>
                    <label class="form-check-label w-100 cursor-pointer fs-5" for="option_${khataOption.id}">
                        ${trueFalseIconHtml(khataOption.content)} ${khataOption.content || 'خطأ'}
                    </label>
                </div>
            `;
        }
        
        html += '</div>';
        return html;
    }
    
    function renderShortAnswer(question, answer) {
        const value = answer.answer_text || '';
        return `
            <div class="mb-3">
                <label class="form-label">إجابتك:</label>
                <input type="text" class="form-control form-control-lg" 
                       name="answer_${question.id}" value="${escapeHtml(value)}"
                       placeholder="اكتب إجابتك هنا...">
            </div>
        `;
    }
    
    function renderEssay(question, answer) {
        const value = answer.answer_text || '';
        return `
            <div class="mb-3">
                <label class="form-label">إجابتك:</label>
                <textarea class="form-control" name="answer_${question.id}" 
                          rows="6" placeholder="اكتب إجابتك المفصلة هنا...">${escapeHtml(value)}</textarea>
            </div>
        `;
    }
    
    function renderNumeric(question, answer) {
        const value = answer.numeric_answer || '';
        return `
            <div class="mb-3">
                <label class="form-label">الإجابة الرقمية:</label>
                <input type="number" step="any" class="form-control form-control-lg" 
                       name="answer_${question.id}" value="${value}"
                       placeholder="أدخل الرقم...">
            </div>
        `;
    }
    
    function renderMultiSelect(question, answer) {
        let html = '<p class="text-muted mb-3"><i class="bi bi-info-circle me-1"></i> يمكنك اختيار أكثر من إجابة</p>';
        html += '<div class="options-list">';
        const selectedOptions = answer.selected_options || [];
        
        question.options.forEach((opt, idx) => {
            const isChecked = selectedOptions.includes(opt.id) ? 'checked' : '';
            html += `
                <div class="form-check p-3 mb-2 border rounded option-item ${isChecked ? 'border-primary bg-primary-transparent' : ''}">
                    <input class="form-check-input" type="checkbox" name="answer_${question.id}[]" 
                           id="opt_${opt.id}" value="${opt.id}" ${isChecked}>
                    <label class="form-check-label question-text-body w-100 cursor-pointer" for="opt_${opt.id}">
                        ${opt.content || ''}
                    </label>
                </div>
            `;
        });
        
        html += '</div>';
        return html;
    }
    
    function renderMatching(question, answer) {
        const pairs = answer.matching_pairs || {};
        let html = '<div class="matching-container">';
        html += '<p class="text-muted mb-3"><i class="bi bi-info-circle me-1"></i> قم بمطابقة العناصر من العمود الأيمن مع الأيسر</p>';
        
        // Split options into left and right columns (assuming even number)
        const leftOptions = question.options.filter((_, i) => i % 2 === 0);
        const rightOptions = question.options.filter((_, i) => i % 2 === 1);
        
        html += '<div class="row">';
        leftOptions.forEach((leftOpt, idx) => {
            const rightOpt = rightOptions[idx] || {};
            const selectedValue = pairs[leftOpt.id] || '';
            
            html += `
                <div class="col-12 mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-fill p-3 border rounded bg-light">
                            ${leftOpt.content || ''}
                        </div>
                        <i class="bi bi-arrow-left-right text-primary"></i>
                        <select class="form-select flex-fill" name="matching_${question.id}[${leftOpt.id}]">
                            <option value="">-- اختر --</option>
                            ${rightOptions.map(r => `<option value="${r.id}" ${selectedValue == r.id ? 'selected' : ''}>${escapeHtml(plainTextFromHtml(r.content))}</option>`).join('')}
                        </select>
                    </div>
                </div>
            `;
        });
        html += '</div></div>';
        return html;
    }
    
    function renderOrdering(question, answer) {
        let ordering = answer.ordering;
        if (!ordering || !Array.isArray(ordering)) {
            // Convert string to array if needed
            if (typeof ordering === 'string') {
                ordering = ordering.split(',').filter(id => id.trim());
            } else {
                ordering = question.options.map(o => o.id);
            }
        }
        
        let html = '<p class="text-muted mb-3"><i class="bi bi-info-circle me-1"></i> اسحب العناصر لترتيبها بالترتيب الصحيح</p>';
        html += `<ul class="list-group ordering-list" id="ordering_${question.id}">`;
        
        // Sort options by ordering
        const sortedOptions = [...question.options].sort((a, b) => {
            const indexA = ordering.indexOf(a.id);
            const indexB = ordering.indexOf(b.id);
            if (indexA === -1 && indexB === -1) return 0;
            if (indexA === -1) return 1;
            if (indexB === -1) return -1;
            return indexA - indexB;
        });
        
        sortedOptions.forEach((opt, idx) => {
            html += `
                <li class="list-group-item d-flex align-items-center gap-3 ordering-item" 
                    data-id="${opt.id}" 
                    draggable="true">
                    <span class="badge bg-primary rounded-pill order-number">${idx + 1}</span>
                    <i class="bi bi-grip-vertical text-muted cursor-move"></i>
                    <span class="flex-grow-1">${opt.content || ''}</span>
                </li>
            `;
        });
        
        html += '</ul>';
        html += `<input type="hidden" name="ordering_${question.id}" id="ordering_input_${question.id}" value="${ordering.join(',')}">`;
        return html;
    }
    
    function renderFillBlank(question, answer) {
        // Handle fill_blanks_answers - can be object with numeric keys or array
        let blanks = answer.fill_blanks_answers || {};
        if (Array.isArray(blanks)) {
            // Convert array to object with numeric keys
            const blanksObj = {};
            blanks.forEach((val, idx) => {
                if (val !== undefined && val !== null) {
                    blanksObj[idx] = val;
                }
            });
            blanks = blanksObj;
        }
        
        let content = question.content || '';
        let blankIndex = 0;
        
        // Extract text from HTML safely
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = content;
        content = tempDiv.textContent || tempDiv.innerText || '';
        content = content.trim();
        
        // Replace {n} pattern with input fields (e.g., {1}, {2}, {3})
        content = content.replace(/\{(\d+)\}/g, function(match, num) {
            const idx = parseInt(num) - 1; // Convert to 0-based index (1->0, 2->1, 3->2)
            // Try both string and numeric key access
            let value = blanks[idx] !== undefined ? blanks[idx] : (blanks[String(idx)] !== undefined ? blanks[String(idx)] : '');
            value = (value !== undefined && value !== null) ? String(value) : '';
            const escapedValue = escapeHtml(value);
            const input = '<input type="text" class="form-control d-inline-block mx-1" ' +
                         'style="width: 120px;" name="blank_' + question.id + '[' + idx + ']" ' +
                         'value="' + escapedValue + '" placeholder="' + num + '">';
            return input;
        });
        
        // Also support ___ pattern
        content = content.replace(/_{3,}/g, function() {
            // Try both string and numeric key access
            let value = blanks[blankIndex] !== undefined ? blanks[blankIndex] : (blanks[String(blankIndex)] !== undefined ? blanks[String(blankIndex)] : '');
            value = (value !== undefined && value !== null) ? String(value) : '';
            const escapedValue = escapeHtml(value);
            const input = '<input type="text" class="form-control d-inline-block mx-1" ' +
                         'style="width: 120px;" name="blank_' + question.id + '[' + blankIndex + ']" ' +
                         'value="' + escapedValue + '" placeholder="...">';
            blankIndex++;
            return input;
        });
        
        return '<div class="fill-blank-container p-4 border rounded bg-light fs-5 lh-lg">' + content + '</div>';
    }
    
    function renderDragDrop(question, answer) {
        const dragDropData = answer.drag_drop_assignments || {};
        let html = '<p class="text-muted mb-3"><i class="bi bi-info-circle me-1"></i> اسحب العناصر إلى المجموعات المناسبة</p>';
        
        // Parse drop zones from content if available
        let dropZones = [];
        try {
            if (question.content) {
                // Try to extract drop-zones from HTML content using regex
                const zonesMatch = question.content.match(/data-zones='([^']+)'/);
                if (zonesMatch) {
                    dropZones = JSON.parse(zonesMatch[1].replace(/\\u([0-9a-f]{4})/gi, (match, code) => {
                        return String.fromCharCode(parseInt(code, 16));
                    }));
                }
            }
        } catch(e) {
            console.error('Error parsing drop zones:', e);
        }
        
        // If no zones in content, create default zones (every 2 options = one zone)
        if (dropZones.length === 0 && question.options.length > 0) {
            const zonesCount = Math.ceil(question.options.length / 2);
            for (let i = 0; i < zonesCount; i++) {
                dropZones.push({ label: `مجموعة ${i + 1}` });
            }
        }
        
        html += '<div class="drag-drop-container">';
        
        // Render draggable items
        html += '<div class="draggable-items mb-4">';
        html += '<h6 class="mb-3">العناصر القابلة للسحب:</h6>';
        html += '<div class="d-flex flex-wrap gap-2" id="draggable-items-' + question.id + '">';
        
        question.options.forEach((opt, idx) => {
            const zoneId = dragDropData[opt.id] || null;
            html += `
                <div class="draggable-item badge bg-primary p-3 cursor-move" 
                     draggable="true" 
                     data-item-id="${opt.id}"
                     data-zone-id="${zoneId || ''}"
                     id="drag-item-${question.id}-${opt.id}">
                    <i class="bi bi-grip-vertical me-2"></i>
                    ${opt.content || ''}
                </div>
            `;
        });
        html += '</div></div>';
        
        // Render drop zones
        html += '<div class="drop-zones-container">';
        html += '<h6 class="mb-3">مناطق الإفلات:</h6>';
        html += '<div class="row">';
        
        dropZones.forEach((zone, zoneIdx) => {
            const zoneId = zone.id || zoneIdx;
            const itemsInZone = Object.keys(dragDropData).filter(itemId => dragDropData[itemId] == zoneId);
            
            html += `
                <div class="col-md-6 mb-4">
                    <div class="drop-zone border border-2 border-dashed rounded p-4 text-center min-h-150" 
                         data-zone-id="${zoneId}"
                         id="drop-zone-${question.id}-${zoneId}"
                         ondrop="handleDrop(event, ${question.id})" 
                         ondragover="handleDragOver(event)">
                        <h6 class="mb-3">${escapeHtml(zone.label || zone.name || `مجموعة ${zoneIdx + 1}`)}</h6>
                        <div class="dropped-items" id="dropped-items-${question.id}-${zoneId}">
                            ${itemsInZone.map(itemId => {
                                const opt = question.options.find(o => o.id == itemId);
                                if (!opt) return '';
                                return `
                                    <div class="dropped-item badge bg-success p-2 mb-2 me-1" data-item-id="${itemId}">
                                        ${opt.content || ''}
                                        <button type="button" class="btn-close btn-close-white ms-2" onclick="removeFromZone(${question.id}, ${itemId})"></button>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                        <p class="text-muted mt-3 mb-0"><small>أسقط العناصر هنا</small></p>
                    </div>
                </div>
            `;
        });
        
        html += '</div></div>';
        html += '</div>';
        
        // Hidden input to store the answer
        const assignmentsJson = JSON.stringify(dragDropData);
        html += `<input type="hidden" name="drag_drop_${question.id}" id="drag-drop-input-${question.id}" value="${escapeHtml(assignmentsJson)}">`;
        
        return html;
    }
    
    // Drag and Drop handlers
    function handleDragOver(event) {
        event.preventDefault();
        event.currentTarget.classList.add('border-primary', 'bg-primary-transparent');
    }
    
    function handleDrop(event, questionId) {
        event.preventDefault();
        const zoneEl = event.currentTarget;
        const zoneId = zoneEl.dataset.zoneId;
        const itemId = event.dataTransfer.getData('text/plain');
        
        // Remove from previous zone
        removeFromZone(questionId, itemId, false);
        
        // Add to new zone
        const itemEl = document.getElementById('drag-item-' + questionId + '-' + itemId);
        const question = questions.find(q => q.id == questionId);
        const opt = question?.options.find(o => o.id == itemId);
        
        if (itemEl && opt) {
            const droppedItemsEl = document.getElementById('dropped-items-' + questionId + '-' + zoneId);
            if (droppedItemsEl) {
                const droppedItem = document.createElement('div');
                droppedItem.className = 'dropped-item badge bg-success p-2 mb-2 me-1';
                droppedItem.dataset.itemId = itemId;
                droppedItem.innerHTML = `
                    ${opt.content || ''}
                    <button type="button" class="btn-close btn-close-white ms-2" onclick="removeFromZone(${questionId}, ${itemId})"></button>
                `;
                droppedItemsEl.appendChild(droppedItem);
            }
            
            itemEl.dataset.zoneId = zoneId;
            itemEl.style.display = 'none';
        }
        
        zoneEl.classList.remove('border-primary', 'bg-primary-transparent');
        updateDragDropAnswer(questionId);
        saveCurrentAnswer(questions.find(q => String(q.id) === String(questionId)));
    }
    
    function removeFromZone(questionId, itemId, updateAnswer = true) {
        const itemEl = document.getElementById('drag-item-' + questionId + '-' + itemId);
        if (itemEl) {
            const zoneId = itemEl.dataset.zoneId;
            if (zoneId) {
                const droppedItemEl = document.querySelector(`#dropped-items-${questionId}-${zoneId} [data-item-id="${itemId}"]`);
                if (droppedItemEl) {
                    droppedItemEl.remove();
                }
            }
            itemEl.dataset.zoneId = '';
            itemEl.style.display = '';
        }
        
        if (updateAnswer) {
            updateDragDropAnswer(questionId);
            saveCurrentAnswer(questions.find(q => String(q.id) === String(questionId)));
        }
    }
    
    function updateDragDropAnswer(questionId) {
        const question = questions.find(q => q.id == questionId);
        if (!question) return;
        
        const assignments = {};
        question.options.forEach(opt => {
            const itemEl = document.getElementById('drag-item-' + questionId + '-' + opt.id);
            if (itemEl && itemEl.dataset.zoneId) {
                assignments[opt.id] = itemEl.dataset.zoneId;
            }
        });
        
        const inputEl = document.getElementById('drag-drop-input-' + questionId);
        if (inputEl) {
            inputEl.value = JSON.stringify(assignments);
        }
    }
    
    function setupDragDropListeners(question) {
        // Setup drag start for all draggable items
        setTimeout(() => {
            document.querySelectorAll(`#draggable-items-${question.id} .draggable-item`).forEach(item => {
                item.addEventListener('dragstart', function(e) {
                    e.dataTransfer.setData('text/plain', this.dataset.itemId);
                    this.style.opacity = '0.5';
                });
                
                item.addEventListener('dragend', function(e) {
                    this.style.opacity = '1';
                    // Remove highlight from all zones
                    document.querySelectorAll('.drop-zone').forEach(zone => {
                        zone.classList.remove('border-primary', 'bg-primary-transparent');
                    });
                });
            });
            
            // Setup drag leave for drop zones
            document.querySelectorAll('.drop-zone').forEach(zone => {
                zone.addEventListener('dragleave', function(e) {
                    if (!this.contains(e.relatedTarget)) {
                        this.classList.remove('border-primary', 'bg-primary-transparent');
                    }
                });
            });
        }, 100);
    }
    
    function setupOrderingListeners(question) {
        setTimeout(() => {
            const listEl = document.getElementById(`ordering_${question.id}`);
            if (!listEl) return;
            
            let draggedElement = null;
            
            listEl.querySelectorAll('.ordering-item').forEach(item => {
                item.addEventListener('dragstart', function(e) {
                    draggedElement = this;
                    this.style.opacity = '0.5';
                    e.dataTransfer.effectAllowed = 'move';
                });
                
                item.addEventListener('dragend', function(e) {
                    this.style.opacity = '1';
                    listEl.querySelectorAll('.ordering-item').forEach(el => {
                        el.classList.remove('drag-over');
                    });
                });
                
                item.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    if (this !== draggedElement) {
                        this.classList.add('drag-over');
                    }
                });
                
                item.addEventListener('dragleave', function(e) {
                    this.classList.remove('drag-over');
                });
                
                item.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('drag-over');
                    
                    if (draggedElement && draggedElement !== this) {
                        const allItems = Array.from(listEl.querySelectorAll('.ordering-item'));
                        const draggedIndex = allItems.indexOf(draggedElement);
                        const targetIndex = allItems.indexOf(this);
                        
                        if (draggedIndex < targetIndex) {
                            listEl.insertBefore(draggedElement, this.nextSibling);
                        } else {
                            listEl.insertBefore(draggedElement, this);
                        }
                        
                        // Update order numbers and hidden input
                        updateOrderingAnswer(question.id);
                        saveCurrentAnswer(question);
                    }
                });
            });
        }, 100);
    }
    
    function updateOrderingAnswer(questionId) {
        const listEl = document.getElementById(`ordering_${questionId}`);
        if (!listEl) return;
        
        const items = listEl.querySelectorAll('.ordering-item');
        const order = Array.from(items).map(item => item.dataset.id);
        
        // Update order numbers
        items.forEach((item, index) => {
            const badge = item.querySelector('.order-number');
            if (badge) {
                badge.textContent = index + 1;
            }
        });
        
        // Update hidden input
        const inputEl = document.getElementById(`ordering_input_${questionId}`);
        if (inputEl) {
            inputEl.value = order.join(',');
        }
    }
    
    function setupAnswerListeners(question) {
        // Drag and Drop setup
        if (question.type === 'drag_drop') {
            setupDragDropListeners(question);
        }
        
        // Ordering setup
        if (question.type === 'ordering') {
            setupOrderingListeners(question);
        }
        
        // Radio buttons and checkboxes highlighting
        document.querySelectorAll('.option-item input').forEach(input => {
            input.addEventListener('change', function() {
                const parent = this.closest('.options-list');
                if (this.type === 'radio') {
                    // For radio buttons, remove highlight from all and add to selected
                    parent.querySelectorAll('.option-item').forEach(item => {
                        item.classList.remove('border-primary', 'bg-primary-transparent');
                    });
                    if (this.checked) {
                        this.closest('.option-item').classList.add('border-primary', 'bg-primary-transparent');
                    }
                } else if (this.type === 'checkbox') {
                    // For checkboxes, toggle highlight for each item
                    if (this.checked) {
                        this.closest('.option-item').classList.add('border-primary', 'bg-primary-transparent');
                    } else {
                        this.closest('.option-item').classList.remove('border-primary', 'bg-primary-transparent');
                    }
                }
                
                // Save answer
                saveCurrentAnswer(question);
            });
        });
        
        // True/False highlighting
        document.querySelectorAll('.true-false-radio').forEach(input => {
            input.addEventListener('change', function() {
                document.querySelectorAll(`input[name="${this.name}"]`).forEach(radio => {
                    const parent = radio.closest('.form-check');
                    parent.classList.remove('border-success', 'border-danger', 'bg-success-transparent', 'bg-danger-transparent');
                });

                const parent = this.closest('.form-check');
                if (this.dataset.tfChoice === 'true') {
                    parent.classList.add('border-success', 'bg-success-transparent');
                } else if (this.dataset.tfChoice === 'false') {
                    parent.classList.add('border-danger', 'bg-danger-transparent');
                }

                saveCurrentAnswer(question);
            });
        });
        
        // Text inputs
        document.querySelectorAll('input[type="text"], textarea, input[type="number"]').forEach(input => {
            input.addEventListener('blur', () => saveCurrentAnswer(question));
        });
        
        // Selects
        document.querySelectorAll('select').forEach(select => {
            select.addEventListener('change', () => saveCurrentAnswer(question));
        });
    }
    
    function saveCurrentAnswer(question) {
        return new Promise((resolve) => {
            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('question_id', question.id);
            
            // Collect answer based on type
            switch(question.type) {
                case 'single_choice':
                case 'true_false':
                    const radio = document.querySelector(`input[name="answer_${question.id}"]:checked`);
                    if (radio) {
                        formData.append('selected_options[]', radio.value);
                    }
                    break;
                case 'multiple_choice':
                    document.querySelectorAll(`input[name="answer_${question.id}[]"]:checked`).forEach(checkbox => {
                        formData.append('selected_options[]', checkbox.value);
                    });
                    break;
                case 'multi_select':
                    document.querySelectorAll(`input[name="answer_${question.id}[]"]:checked`).forEach(cb => {
                        formData.append('selected_options[]', cb.value);
                    });
                    break;
                case 'short_answer':
                case 'essay':
                    const textInput = document.querySelector(`[name="answer_${question.id}"]`);
                    if (textInput) {
                        formData.append('answer_text', textInput.value);
                    }
                    break;
                case 'numeric':
                    const numInput = document.querySelector(`[name="answer_${question.id}"]`);
                    if (numInput) {
                        formData.append('numeric_answer', numInput.value);
                    }
                    break;
                case 'matching':
                    const matchingSelects = document.querySelectorAll(`select[name^="matching_${question.id}"]`);
                    matchingSelects.forEach(select => {
                        // Extract key from name like "matching_123[456]" -> "456"
                        const matchResult = select.name.match(/\[([^\]]+)\]/);
                        if (matchResult && select.value) {
                            const key = matchResult[1];
                            formData.append(`matching_pairs[${key}]`, select.value);
                        }
                    });
                    break;
                case 'ordering':
                    const orderInput = document.getElementById(`ordering_input_${question.id}`);
                    if (orderInput) {
                        formData.append('ordering', orderInput.value);
                    }
                    break;
                case 'fill_blank':
                case 'fill_blanks':
                    document.querySelectorAll(`input[name^="blank_${question.id}"]`).forEach(input => {
                        const key = input.name.match(/\[(\d+)\]/)[1];
                        formData.append(`fill_blanks_answers[${key}]`, input.value);
                    });
                    break;
                case 'drag_drop':
                    const dragDropInput = document.getElementById(`drag-drop-input-${question.id}`);
                    if (dragDropInput) {
                        formData.append('drag_drop_assignments', dragDropInput.value);
                    }
                    break;
            }
            
            fetch(saveUrl, {
                method: 'POST',
                body: formData
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      // Update answers cache with proper structure
                      if (data.answer) {
                          answers[question.id] = {
                              'selected_options': data.answer.selected_options || null,
                              'answer_text': data.answer.answer_text || null,
                              'numeric_answer': data.answer.numeric_answer || null,
                              'matching_pairs': data.answer.matching_pairs || null,
                              'ordering': data.answer.ordering || null,
                              'fill_blanks_answers': data.answer.fill_blanks_answers || null,
                              'drag_drop_assignments': data.answer.drag_drop_assignments || null,
                          };
                      } else {
                          // If no answer data, at least ensure the key exists
                          answers[question.id] = answers[question.id] || {};
                      }
                      
                      // Update progress
                      const answeredCount = countAnsweredQuestions();
                  const progress = questions.length > 0 ? ((answeredCount / questions.length) * 100).toFixed(0) : 0;
                  document.getElementById('progress-bar').style.width = progress + '%';
                  document.getElementById('progress-text').textContent = answeredCount + ' / ' + questions.length;
                  
                  // Mark question as answered in nav
                  const navBtn = document.querySelectorAll('.question-nav-btn')[currentQuestionIndex];
                  if (navBtn) {
                      navBtn.classList.remove('btn-outline-secondary');
                      navBtn.classList.add('btn-success', 'answered');
                      const icon = navBtn.querySelector('i');
                      if (icon) {
                          icon.className = 'bi bi-check-circle-fill me-2';
                      }
                  }
              }
              resolve();
          }).catch(err => {
              console.error('Save error:', err);
              alert('حدث خطأ أثناء حفظ الإجابة');
              resolve(); // Resolve anyway to not block submission
          });
        });
    }
    
    // Navigation
    document.getElementById('prev-btn').addEventListener('click', () => {
        if (currentQuestionIndex > 0) {
            loadQuestion(currentQuestionIndex - 1);
        }
    });
    
    document.getElementById('next-btn').addEventListener('click', () => {
        if (currentQuestionIndex === questions.length - 1) {
            document.getElementById('submit-quiz-btn').click();
        } else {
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

    function submitQuizAttempt(options) {
        options = options || {};
        if (isSubmittingQuiz) {
            return Promise.resolve();
        }

        isSubmittingQuiz = true;
        const submitBtn = document.getElementById('submit-quiz-btn');
        const nextBtn = document.getElementById('next-btn');
        if (submitBtn) {
            submitBtn.disabled = true;
        }
        if (nextBtn) {
            nextBtn.disabled = true;
        }

        const savePromise = (Array.isArray(questions) && questions.length > 0 && questions[currentQuestionIndex])
            ? saveCurrentAnswer(questions[currentQuestionIndex])
            : Promise.resolve();

        return savePromise.then(function() {
            const formData = new FormData();
            formData.append('_token', csrfToken);

            return fetch(submitUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });
        }).then(function(response) {
            return response.json().then(function(data) {
                return { response: response, data: data };
            }).catch(function() {
                return { response: response, data: null };
            });
        }).then(function(result) {
            const response = result.response;
            const data = result.data;

            if (data && data.redirect_url) {
                window.location.href = data.redirect_url;
                return;
            }

            if (data && data.success) {
                window.location.href = resultUrl;
                return;
            }

            if (response.ok) {
                window.location.href = resultUrl;
                return;
            }

            const message = (data && data.message)
                ? data.message
                : 'حدث خطأ أثناء إرسال الاختبار. حاول مرة أخرى.';
            alert(message);
            isSubmittingQuiz = false;
            if (submitBtn) {
                submitBtn.disabled = false;
            }
            if (nextBtn) {
                nextBtn.disabled = false;
            }
        }).catch(function(err) {
            console.error('Submit quiz error:', err);
            alert('حدث خطأ أثناء إرسال الاختبار. تحقق من الاتصال وحاول مرة أخرى.');
            isSubmittingQuiz = false;
            if (submitBtn) {
                submitBtn.disabled = false;
            }
            if (nextBtn) {
                nextBtn.disabled = false;
            }
        });
    }
    
    function isQuizTimerTicking() {
        if (window.__quizAttemptTimer && window.__quizAttemptTimer.tickIntervalId !== null) {
            return true;
        }
        return !!window.__quizAttemptTimerInterval;
    }

    function formatElapsedSeconds(totalSeconds) {
        const seconds = Math.max(0, parseInt(totalSeconds, 10) || 0);
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;
        if (h > 0) {
            return h + ':' + (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
        }
        return m + ':' + (s < 10 ? '0' : '') + s;
    }

    function startQuizCountdown() {
        @if($quiz->show_timer)
        if (window.__quizAttemptTimerStarted && isQuizTimerTicking()) {
            return;
        }

        const displayEl = document.getElementById('timer-display');
        if (!displayEl) {
            return;
        }

        const timerMode = displayEl.getAttribute('data-timer-mode') || 'countdown';
        const hasTimeLimit = timerMode === 'countdown';
        const startedAtMs = parseInt(displayEl.getAttribute('data-started-at-ms') || '', 10);
        const updateUrl = '{{ route("student.quizzes.time", $attempt->id) }}';

        const onTimeout = hasTimeLimit && {{ $quiz->auto_submit ? 'true' : 'false' }}
            ? function() { submitQuizAttempt({ reason: 'timeout' }); }
            : null;

        const onWarning = hasTimeLimit ? function(seconds) {
            const card = document.getElementById('timer-card');
            if (!card) {
                return;
            }
            if (seconds <= 60) {
                card.classList.add('danger');
                card.classList.remove('warning');
            } else if (seconds <= 300) {
                card.classList.add('warning');
                card.classList.remove('danger');
            }
        } : null;

        let timerOptions;
        if (hasTimeLimit) {
            const remainingFromData = parseInt(displayEl.getAttribute('data-remaining-seconds') || '', 10);
            const remainingTime = Number.isFinite(remainingFromData) && remainingFromData > 0
                ? remainingFromData
                : {{ (int) ($initialRemaining ?? 0) }};
            timerOptions = {
                mode: 'countdown',
                remainingTime: remainingTime,
                updateUrl: updateUrl,
                onTimeout: onTimeout,
                onWarning: onWarning,
            };
        } else {
            const elapsedFromData = parseInt(displayEl.getAttribute('data-elapsed-seconds') || '', 10);
            const elapsedSeconds = Number.isFinite(elapsedFromData) && elapsedFromData >= 0
                ? elapsedFromData
                : {{ (int) ($initialElapsed ?? 0) }};
            timerOptions = {
                mode: 'elapsed',
                elapsedSeconds: elapsedSeconds,
                startedAtMs: Number.isFinite(startedAtMs) ? startedAtMs : undefined,
                updateUrl: updateUrl,
            };
        }

        if (window.__quizAttemptTimerInterval) {
            clearInterval(window.__quizAttemptTimerInterval);
            window.__quizAttemptTimerInterval = null;
        }

        if (typeof window.QuizTimer !== 'undefined') {
            if (window.__quizAttemptTimer && typeof window.__quizAttemptTimer.stop === 'function') {
                window.__quizAttemptTimer.stop();
            }
            window.__quizAttemptTimer = new window.QuizTimer(timerOptions);
            window.__quizAttemptTimer.start();
        } else if (hasTimeLimit && onTimeout) {
            let remaining = timerOptions.remainingTime;
            const tick = function() {
                if (remaining <= 0) {
                    displayEl.textContent = '0:00';
                    if (window.__quizAttemptTimerInterval) {
                        clearInterval(window.__quizAttemptTimerInterval);
                        window.__quizAttemptTimerInterval = null;
                    }
                    onTimeout();
                    return;
                }
                const m = Math.floor(remaining / 60);
                const s = remaining % 60;
                displayEl.textContent = m + ':' + (s < 10 ? '0' : '') + s;
                remaining -= 1;
            };
            tick();
            window.__quizAttemptTimerInterval = setInterval(tick, 1000);
        } else if (!hasTimeLimit) {
            const anchorMs = Number.isFinite(startedAtMs)
                ? startedAtMs
                : Date.now() - (timerOptions.elapsedSeconds * 1000);
            const tick = function() {
                const elapsed = Math.max(0, Math.floor((Date.now() - anchorMs) / 1000));
                displayEl.textContent = formatElapsedSeconds(elapsed);
                displayEl.setAttribute('data-elapsed-seconds', String(elapsed));
            };
            tick();
            window.__quizAttemptTimerInterval = setInterval(tick, 1000);
        }

        window.__quizAttemptTimerStarted = true;
        @endif
    }

    // Initialize when DOM is ready so first question and timer render correctly
    function initQuiz() {
        loadQuestion(0);
        startQuizCountdown();
    }

    (function () {
        const submitForm = document.getElementById('submit-quiz-form');
        if (submitForm) {
            submitForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitQuizAttempt({ reason: 'manual' });
            });
        }
    })();

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

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initQuiz);
    } else {
        initQuiz();
    }
    // Fallback: ensure first question shows after paint (e.g. if loader delayed DOM visibility)
    setTimeout(function() {
        var contentEl = document.getElementById('question-content');
        if (contentEl && !contentEl.innerHTML.trim()) {
            loadQuestion(0);
        }
    }, 100);
    window.addEventListener('load', function() {
        var contentEl = document.getElementById('question-content');
        if (contentEl && !contentEl.innerHTML.trim()) {
            loadQuestion(0);
        }
        startQuizCountdown();
    });

    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.__quizAttemptTimerStarted = false;
            startQuizCountdown();
        }
    });
</script>
@endpush
