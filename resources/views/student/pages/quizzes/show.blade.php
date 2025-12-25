@extends('student.layouts.master')

@section('page-title', $quiz->title)

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">{{ $quiz->title }}</h4>
                <p class="mb-0 text-muted">
                    @if($quiz->subject)
                        {{ $quiz->subject->name }}
                        @if($quiz->unit) - {{ $quiz->unit->title }} @endif
                    @endif
                </p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.subjects') }}">المواد الدراسية</a></li>
                    @if($quiz->subject)
                        <li class="breadcrumb-item"><a href="{{ route('student.subjects.show', $quiz->subject->id) }}">{{ $quiz->subject->name }}</a></li>
                    @endif
                    <li class="breadcrumb-item active">{{ $quiz->title }}</li>
                </ol>
            </nav>
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
                    <div class="d-grid gap-2" id="questions-list">
                        @foreach($questions as $index => $question)
                            @php
                                $answer = $answers[$question->id] ?? null;
                                $isAnswered = $answer && ($answer->answer || $answer->answer_text || $answer->selected_options || $answer->numeric_answer);
                            @endphp
                            <button type="button" 
                                    class="btn {{ $index === 0 ? 'btn-primary' : ($isAnswered ? 'btn-success' : 'btn-outline-secondary') }} question-nav-btn text-start"
                                    data-question-id="{{ $question->id }}"
                                    data-question-index="{{ $index }}">
                                <i class="bi bi-{{ $isAnswered ? 'check-circle-fill' : 'circle' }} me-2"></i>
                                سؤال {{ $index + 1 }}
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
        animation: pulse 1s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
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
    #question-content {
        min-height: 200px;
    }
    .form-check.p-4 {
        transition: all 0.2s ease;
    }
    .form-check.p-4:hover {
        transform: scale(1.02);
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/quiz-timer.js') }}"></script>
<script src="{{ asset('js/auto-save-answer.js') }}"></script>
<script src="{{ asset('js/question-types.js') }}"></script>
<script>
    @php
        $questionsJson = $questions->map(function($q) {
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
        });
        
        $answersJson = $answers->mapWithKeys(function($a) {
            return [$a->question_id => [
                'selected_options' => $a->selected_options,
                'answer_text' => $a->answer_text,
                'numeric_answer' => $a->numeric_answer,
                'matching_pairs' => $a->matching_pairs,
                'ordering' => $a->ordering,
                'fill_blanks_answers' => $a->fill_blanks_answers,
            ]];
        });
    @endphp
    
    const questions = {!! json_encode($questionsJson) !!};
    const answers = {!! json_encode($answersJson) !!};
    
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
        const contentDiv = document.getElementById('question-content');
        let html = '';
        
        // عنوان السؤال
        if (question.title) {
            html += `<h5 class="mb-3">${escapeHtml(question.title)}</h5>`;
        }
        
        // محتوى السؤال
        if (question.content) {
            html += `<div class="mb-4 p-3 bg-light rounded">${escapeHtml(question.content)}</div>`;
        }
        
        // خيارات الإجابة حسب نوع السؤال
        html += '<div class="answer-section">';
        
        switch(question.type) {
            case 'multiple_choice':
            case 'single_choice':
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
    
    function renderMultipleChoice(question, answer) {
        let html = '<div class="options-list">';
        const selectedOptions = answer.selected_options || [];
        
        question.options.forEach((opt, idx) => {
            const isChecked = selectedOptions.includes(opt.id) ? 'checked' : '';
            html += `
                <div class="form-check p-3 mb-2 border rounded option-item ${isChecked ? 'border-primary bg-primary-transparent' : ''}">
                    <input class="form-check-input" type="radio" name="answer_${question.id}" 
                           id="opt_${opt.id}" value="${opt.id}" ${isChecked}>
                    <label class="form-check-label w-100 cursor-pointer" for="opt_${opt.id}">
                        ${escapeHtml(opt.content)}
                    </label>
                </div>
            `;
        });
        
        html += '</div>';
        return html;
    }
    
    function renderTrueFalse(question, answer) {
        const selectedOptions = answer.selected_options || [];
        let html = '<div class="d-flex gap-3">';
        
        const trueChecked = selectedOptions.includes('true') ? 'checked' : '';
        const falseChecked = selectedOptions.includes('false') ? 'checked' : '';
        
        html += `
            <div class="form-check p-4 border rounded flex-fill text-center ${trueChecked ? 'border-success bg-success-transparent' : ''}">
                <input class="form-check-input" type="radio" name="answer_${question.id}" 
                       id="true_${question.id}" value="true" ${trueChecked}>
                <label class="form-check-label w-100 cursor-pointer fs-5" for="true_${question.id}">
                    <i class="bi bi-check-circle text-success me-2"></i> صحيح
                </label>
            </div>
            <div class="form-check p-4 border rounded flex-fill text-center ${falseChecked ? 'border-danger bg-danger-transparent' : ''}">
                <input class="form-check-input" type="radio" name="answer_${question.id}" 
                       id="false_${question.id}" value="false" ${falseChecked}>
                <label class="form-check-label w-100 cursor-pointer fs-5" for="false_${question.id}">
                    <i class="bi bi-x-circle text-danger me-2"></i> خطأ
                </label>
            </div>
        `;
        
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
                    <label class="form-check-label w-100 cursor-pointer" for="opt_${opt.id}">
                        ${escapeHtml(opt.content)}
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
                            ${escapeHtml(leftOpt.content)}
                        </div>
                        <i class="bi bi-arrow-left-right text-primary"></i>
                        <select class="form-select flex-fill" name="matching_${question.id}[${leftOpt.id}]">
                            <option value="">-- اختر --</option>
                            ${rightOptions.map(r => `<option value="${r.id}" ${selectedValue == r.id ? 'selected' : ''}>${escapeHtml(r.content)}</option>`).join('')}
                        </select>
                    </div>
                </div>
            `;
        });
        html += '</div></div>';
        return html;
    }
    
    function renderOrdering(question, answer) {
        const ordering = answer.ordering || question.options.map(o => o.id);
        let html = '<p class="text-muted mb-3"><i class="bi bi-info-circle me-1"></i> اسحب العناصر لترتيبها بالترتيب الصحيح</p>';
        html += '<ul class="list-group ordering-list" id="ordering_${question.id}">';
        
        // Sort options by ordering
        const sortedOptions = [...question.options].sort((a, b) => {
            return ordering.indexOf(a.id) - ordering.indexOf(b.id);
        });
        
        sortedOptions.forEach((opt, idx) => {
            html += `
                <li class="list-group-item d-flex align-items-center gap-3" data-id="${opt.id}">
                    <span class="badge bg-primary rounded-pill">${idx + 1}</span>
                    <i class="bi bi-grip-vertical text-muted cursor-move"></i>
                    <span class="flex-grow-1">${escapeHtml(opt.content)}</span>
                </li>
            `;
        });
        
        html += '</ul>';
        html += `<input type="hidden" name="ordering_${question.id}" id="ordering_input_${question.id}" value="${ordering.join(',')}">`;
        return html;
    }
    
    function renderFillBlank(question, answer) {
        const blanks = answer.fill_blanks_answers || {};
        let content = question.content || '';
        let blankIndex = 0;
        
        // Remove HTML tags for clean display
        content = content.replace(/<\/?[^>]+(>|$)/g, '');
        
        // Replace {n} pattern with input fields (e.g., {1}, {2}, {3})
        content = content.replace(/\{(\d+)\}/g, (match, num) => {
            const idx = parseInt(num) - 1; // Convert to 0-based index
            const value = blanks[idx] || '';
            const input = `<input type="text" class="form-control d-inline-block mx-1" 
                                  style="width: 120px;" name="blank_${question.id}[${idx}]" 
                                  value="${escapeHtml(value)}" placeholder="${num}">`;
            return input;
        });
        
        // Also support ___ pattern
        content = content.replace(/_{3,}/g, () => {
            const value = blanks[blankIndex] || '';
            const input = `<input type="text" class="form-control d-inline-block mx-1" 
                                  style="width: 120px;" name="blank_${question.id}[${blankIndex}]" 
                                  value="${escapeHtml(value)}" placeholder="...">`;
            blankIndex++;
            return input;
        });
        
        return `
            <div class="fill-blank-container p-4 border rounded bg-light fs-5 lh-lg text-center">
                ${content}
            </div>
        `;
    }
    
    function setupAnswerListeners(question) {
        // Radio buttons and checkboxes highlighting
        document.querySelectorAll('.option-item input').forEach(input => {
            input.addEventListener('change', function() {
                const parent = this.closest('.options-list');
                if (this.type === 'radio') {
                    parent.querySelectorAll('.option-item').forEach(item => {
                        item.classList.remove('border-primary', 'bg-primary-transparent');
                    });
                }
                if (this.checked) {
                    this.closest('.option-item').classList.add('border-primary', 'bg-primary-transparent');
                } else {
                    this.closest('.option-item').classList.remove('border-primary', 'bg-primary-transparent');
                }
                
                // Save answer
                saveCurrentAnswer(question);
            });
        });
        
        // True/False highlighting
        document.querySelectorAll('.form-check input[type="radio"]').forEach(input => {
            input.addEventListener('change', function() {
                document.querySelectorAll(`input[name="${this.name}"]`).forEach(radio => {
                    const parent = radio.closest('.form-check');
                    parent.classList.remove('border-success', 'border-danger', 'bg-success-transparent', 'bg-danger-transparent');
                });
                
                const parent = this.closest('.form-check');
                if (this.value === 'true') {
                    parent.classList.add('border-success', 'bg-success-transparent');
                } else {
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
        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('question_id', question.id);
        
        // Collect answer based on type
        switch(question.type) {
            case 'multiple_choice':
            case 'single_choice':
            case 'true_false':
                const radio = document.querySelector(`input[name="answer_${question.id}"]:checked`);
                if (radio) {
                    formData.append('selected_options[]', radio.value);
                }
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
                document.querySelectorAll(`select[name^="matching_${question.id}"]`).forEach(select => {
                    const key = select.name.match(/\[(\d+)\]/)[1];
                    formData.append(`matching_pairs[${key}]`, select.value);
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
        }
        
        fetch(saveUrl, {
            method: 'POST',
            body: formData
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  // Update answers cache
                  answers[question.id] = data.answer || {};
                  
                  // Update progress
                  const answeredCount = Object.keys(answers).filter(k => answers[k] && Object.keys(answers[k]).length > 0).length;
                  const progress = ((answeredCount / questions.length) * 100).toFixed(0);
                  document.getElementById('progress-bar').style.width = progress + '%';
                  document.getElementById('progress-text').textContent = answeredCount + ' / ' + questions.length;
                  
                  // Mark question as answered in nav
                  const navBtn = document.querySelectorAll('.question-nav-btn')[currentQuestionIndex];
                  if (navBtn) {
                      navBtn.classList.add('answered');
                  }
              }
          }).catch(err => console.error('Save error:', err));
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

