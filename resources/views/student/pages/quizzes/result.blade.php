@php
    $isAdminPreview = $isAdminPreview ?? false;
    $quizLayout = $isAdminPreview ? 'student.layouts.quiz-preview-master' : 'student.layouts.master';
@endphp

@extends($quizLayout)

@include('partials.question-math-assets')

@push('styles')
    @include('partials.questions.mcq-options-styles')
    @include('student.pages.quizzes.partials.quiz-result-styles')
@endpush

@section('page-title')
    نتيجة الاختبار - {{ $quiz->title }}
@stop

@section('content')
@php
    $percentage = $attempt->max_score > 0 ? ($attempt->score / $attempt->max_score) * 100 : 0;
    $passed = $percentage >= ($quiz->pass_percentage ?? $quiz->passing_percentage ?? 50);
    $pctCss = max(0, min(100, $percentage));
@endphp
<div class="main-content app-content sqr-result">
    <div class="container-fluid">
        <div class="sqr-hero">
            <div class="d-flex align-items-center gap-3 flex-grow-1 min-w-0">
                <div class="sqr-hero__icon" aria-hidden="true">
                    <i class="bi bi-clipboard2-check"></i>
                </div>
                <div class="min-w-0">
                    <h1 class="sqr-hero__title">{{ $isAdminPreview ? 'نتيجة المعاينة' : 'نتيجة الاختبار' }}</h1>
                    <p class="sqr-hero__meta">{{ $quiz->title }}</p>
                </div>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="sqr-breadcrumb">
                    @if($isAdminPreview)
                        <li><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li aria-hidden="true">»</li>
                        <li><a href="{{ route('admin.quizzes.index') }}">الاختبارات</a></li>
                        <li aria-hidden="true">»</li>
                        <li class="active">معاينة</li>
                    @else
                        <li><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li aria-hidden="true">»</li>
                        <li><a href="{{ route('student.quizzes.results') }}">نتائج الاختبارات</a></li>
                        <li aria-hidden="true">»</li>
                        <li class="active">{{ $quiz->title }}</li>
                    @endif
                </ol>
            </nav>
        </div>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="sqr-card">
                    <div class="sqr-score {{ $passed ? 'is-pass' : 'is-fail' }}" style="--sqr-pct: {{ number_format($pctCss, 2, '.', '') }}%;">
                        <div class="sqr-score__icon" aria-hidden="true">
                            @if($passed)
                                <i class="bi bi-trophy-fill"></i>
                            @else
                                <i class="bi bi-x-circle-fill"></i>
                            @endif
                        </div>

                        <div class="sqr-score__ring" aria-hidden="true">
                            <div class="sqr-score__ring-inner">
                                <span class="sqr-score__pct">{{ number_format($percentage, 1) }}%</span>
                            </div>
                        </div>

                        <h2 class="sqr-score__status">{{ $passed ? 'ناجح!' : 'راسب' }}</h2>
                        <p class="sqr-score__points">{{ $attempt->score }} / {{ $attempt->max_score }} نقطة</p>

                        <div class="sqr-score__bar" role="progressbar" aria-valuenow="{{ number_format($pctCss, 1, '.', '') }}" aria-valuemin="0" aria-valuemax="100">
                            <span style="width: {{ number_format($pctCss, 2, '.', '') }}%"></span>
                        </div>

                        <div class="sqr-actions">
                            @if($isAdminPreview)
                                <a href="{{ route('admin.quizzes.preview', $quiz->id) }}" class="btn btn-primary">
                                    <i class="bi bi-arrow-repeat me-1"></i>
                                    إعادة المعاينة
                                </a>
                                <a href="{{ $previewReturnUrl ?? route('admin.quizzes.show', $quiz->id) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-right me-1"></i>
                                    العودة للاختبار
                                </a>
                            @else
                                @if($quiz->max_attempts == 0 || $attempt->attempt_number < $quiz->max_attempts)
                                    <a href="{{ route('student.quizzes.start', $quiz->id) }}" class="btn btn-primary">
                                        <i class="bi bi-arrow-repeat me-1"></i>
                                        إعادة الاختبار
                                    </a>
                                @endif
                                <a href="{{ route('student.quizzes.results') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-right me-1"></i>
                                    العودة للنتائج
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="sqr-card">
                    <div class="sqr-card__head">
                        <i class="bi bi-info-circle"></i>
                        معلومات الاختبار
                    </div>
                    <div class="sqr-card__body">
                        <ul class="sqr-info-list">
                            <li>
                                <span class="label">المادة</span>
                                <span class="value">{{ $quiz->subject->name ?? 'عام' }}</span>
                            </li>
                            @if($quiz->unit)
                            <li>
                                <span class="label">الوحدة</span>
                                <span class="value">{{ $quiz->unit->title }}</span>
                            </li>
                            @endif
                            <li>
                                <span class="label">عدد الأسئلة</span>
                                <span class="value">{{ $answers->count() }}</span>
                            </li>
                            <li>
                                <span class="label">رقم المحاولة</span>
                                <span class="value">{{ $attempt->attempt_number }}</span>
                            </li>
                            <li>
                                <span class="label">تاريخ البدء</span>
                                <span class="value">{{ $attempt->started_at->format('Y-m-d H:i') }}</span>
                            </li>
                            <li>
                                <span class="label">تاريخ الانتهاء</span>
                                <span class="value">{{ $attempt->finished_at ? $attempt->finished_at->format('Y-m-d H:i') : '-' }}</span>
                            </li>
                            @if($quiz->duration_minutes && $attempt->started_at && $attempt->finished_at)
                            <li>
                                <span class="label">الوقت المستغرق</span>
                                <span class="value">
                                    {{ $attempt->started_at->diff($attempt->finished_at)->format('%i دقيقة %s ثانية') }}
                                </span>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Answers Review -->
            <div class="col-lg-8">
                <div class="sqr-card">
                    <div class="sqr-card__head">
                        <i class="bi bi-list-check"></i>
                        مراجعة الإجابات
                    </div>
                    <div class="sqr-card__body">
                        @foreach($answers as $index => $answer)
                            @php
                                $question = $answer->question;
                                $isCorrect = $answer->is_correct;
                            @endphp
                            <div class="sqr-answer {{ $isCorrect ? 'is-correct' : 'is-wrong' }}">
                                <div class="sqr-answer__top">
                                    <div class="d-flex align-items-start">
                                        <span class="sqr-answer__num">{{ $index + 1 }}</span>
                                        <span class="sqr-answer__title question-stem question-text-body">{!! format_question_markup($question->title ?? 'سؤال ' . ($index + 1)) !!}</span>
                                    </div>
                                    <div>
                                        @if($isCorrect)
                                            <span class="badge bg-success sqr-answer__badge">
                                                <i class="bi bi-check-circle me-1"></i>
                                                صحيح - {{ $answer->points_earned ?? 0 }} نقطة
                                            </span>
                                        @else
                                            <span class="badge bg-danger sqr-answer__badge">
                                                <i class="bi bi-x-circle me-1"></i>
                                                خطأ
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($question->content)
                                    <div class="mb-3 text-muted question-text-body">
                                        {!! format_question_markup($question->content) !!}
                                    </div>
                                @endif

                                @if(in_array($question->type, ['single_choice', 'multiple_choice', 'true_false'], true))
                                    @include('partials.questions.mcq-options-review', [
                                        'options' => $question->options,
                                        'questionType' => $question->type,
                                        'selectedOptionIds' => is_array($answer->selected_options) ? $answer->selected_options : [],
                                        'reviewMode' => true,
                                        'highlightCorrect' => (bool) $quiz->show_correct_answers,
                                    ])
                                @endif
                                
                                <!-- Student Answer -->
                                <div class="mb-2 {{ in_array($question->type, ['single_choice', 'multiple_choice', 'true_false'], true) ? 'd-none' : '' }}">
                                    <strong class="text-dark">إجابتك:</strong>
                                    @if($question->type == 'multiple_choice' || $question->type == 'single_choice')
                                        @php
                                            $selectedOptions = $answer->selected_options ?? [];
                                            $selectedOption = $question->options->whereIn('id', $selectedOptions)->first();
                                        @endphp
                                        <span class="{{ $isCorrect ? 'text-success' : 'text-danger' }} question-text-body">
                                            {!! $selectedOption ? format_question_markup($selectedOption->content) : 'لم يتم الإجابة' !!}
                                        </span>
                                    @elseif($question->type == 'true_false')
                                        @php
                                            $selectedOptions = $answer->selected_options ?? [];
                                            $studentAnswer = in_array('true', $selectedOptions) ? 'صحيح' : (in_array('false', $selectedOptions) ? 'خطأ' : 'لم يتم الإجابة');
                                        @endphp
                                        <span class="{{ $isCorrect ? 'text-success' : 'text-danger' }}">
                                            {{ $studentAnswer }}
                                        </span>
                                    @elseif($question->type == 'short_answer' || $question->type == 'essay')
                                        <span class="{{ $isCorrect ? 'text-success' : 'text-danger' }}">
                                            {{ $answer->answer_text ?? 'لم يتم الإجابة' }}
                                        </span>
                                    @elseif($question->type == 'numerical' || $question->type == 'numeric')
                                        <span class="{{ $isCorrect ? 'text-success' : 'text-danger' }}">
                                            {{ $answer->numeric_answer !== null ? $answer->numeric_answer : 'لم يتم الإجابة' }}
                                        </span>
                                    @elseif($question->type == 'fill_blank' || $question->type == 'fill_blanks')
                                        @php
                                            $blanks = $answer->fill_blanks_answers ?? [];
                                        @endphp
                                        <span class="{{ $isCorrect ? 'text-success' : 'text-danger' }}">
                                            @if(count($blanks) > 0)
                                                @foreach($blanks as $i => $blank)
                                                    ({{ $i + 1 }}) {{ $blank }}{{ !$loop->last ? '، ' : '' }}
                                                @endforeach
                                            @else
                                                لم يتم الإجابة
                                            @endif
                                        </span>
                                    @elseif($question->type == 'multi_select')
                                        @php
                                            $selectedOptions = $answer->selected_options ?? [];
                                            $selectedItems = $question->options->whereIn('id', $selectedOptions)
                                                ->map(fn ($opt) => format_question_markup($opt->content))
                                                ->toArray();
                                        @endphp
                                        <span class="{{ $isCorrect ? 'text-success' : 'text-danger' }} question-text-body">
                                            {!! count($selectedItems) > 0 ? implode('، ', $selectedItems) : 'لم يتم الإجابة' !!}
                                        </span>
                                    @elseif($question->type == 'ordering')
                                        @php
                                            $ordering = $answer->ordering ?? [];
                                            $orderedItems = [];
                                            if (!empty($ordering)) {
                                                foreach ($ordering as $index => $optionId) {
                                                    $option = $question->options->firstWhere('id', $optionId);
                                                    if ($option) {
                                                        $orderedItems[] = ($index + 1) . '. ' . format_question_markup($option->content);
                                                    }
                                                }
                                            }
                                        @endphp
                                        <span class="{{ $isCorrect ? 'text-success' : 'text-danger' }}">
                                            @if(count($orderedItems) > 0)
                                                <br>
                                                @foreach($orderedItems as $item)
                                                    <span class="question-text-body">{!! $item !!}</span><br>
                                                @endforeach
                                            @else
                                                لم يتم الإجابة
                                            @endif
                                        </span>
                                    @elseif($question->type == 'drag_drop')
                                        @php
                                            $assignments = $answer->drag_drop_assignments ?? [];
                                            $zoneItems = [];
                                            foreach ($assignments as $itemId => $zoneLabel) {
                                                $option = $question->options->firstWhere('id', $itemId);
                                                if ($option) {
                                                    $label = $zoneLabel !== null && $zoneLabel !== ''
                                                        ? (string) $zoneLabel
                                                        : 'منطقة';
                                                    if (! isset($zoneItems[$label])) {
                                                        $zoneItems[$label] = [];
                                                    }
                                                    $zoneItems[$label][] = format_question_markup($option->content);
                                                }
                                            }
                                        @endphp
                                        <span class="{{ $isCorrect ? 'text-success' : 'text-danger' }}">
                                            @if(!empty($zoneItems))
                                                <br>
                                                @foreach($zoneItems as $zoneName => $items)
                                                    <strong>{{ $zoneName }}:</strong>
                                                    <span class="question-text-body">{!! implode('، ', $items) !!}</span>
                                                    <br>
                                                @endforeach
                                            @else
                                                لم يتم الإجابة
                                            @endif
                                        </span>
                                    @elseif($question->type == 'matching')
                                        @php
                                            $pairs = $answer->matching_pairs ?? [];
                                            $pairItems = [];
                                            foreach ($pairs as $leftId => $matchTarget) {
                                                $leftOption = $question->options->firstWhere('id', $leftId);
                                                if ($leftOption && $matchTarget !== null && $matchTarget !== '') {
                                                    $pairItems[] = format_question_markup($leftOption->content) . ' ← ' . format_question_markup($matchTarget);
                                                }
                                            }
                                        @endphp
                                        <span class="{{ $isCorrect ? 'text-success' : 'text-danger' }}">
                                            @if(!empty($pairItems))
                                                <br>
                                                @foreach($pairItems as $pair)
                                                    <span class="question-text-body">{!! $pair !!}</span><br>
                                                @endforeach
                                            @else
                                                لم يتم الإجابة
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-muted">{{ $answer->answer_text ?? 'لم يتم الإجابة' }}</span>
                                    @endif
                                </div>
                                
                                <!-- Explanation -->
                                @if($question->explanation)
                                    <div class="sqr-explain {{ $isCorrect ? 'is-correct' : '' }}">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-lightbulb-fill {{ $isCorrect ? 'text-success' : 'text-info' }} me-2 mt-1"></i>
                                            <div>
                                                <strong class="{{ $isCorrect ? 'text-success' : 'text-info' }}">الشرح:</strong>
                                                <p class="mb-0 mt-1 question-text-body">{!! format_question_markup($question->explanation) !!}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Correct Answer (if wrong) -->
                                @if(!$isCorrect && $quiz->show_correct_answers && !in_array($question->type, ['single_choice', 'multiple_choice', 'true_false'], true))
                                    <div class="mt-2">
                                        <strong class="text-success">الإجابة الصحيحة:</strong>
                                        @if($question->type == 'multiple_choice' || $question->type == 'single_choice')
                                            @php
                                                $correctOption = $question->options->where('is_correct', true)->first();
                                            @endphp
                                            <span class="text-success question-text-body">{!! format_question_markup($correctOption->content ?? '-') !!}</span>
                                        @elseif($question->type == 'true_false')
                                            @php
                                                $correctOption = $question->options->where('is_correct', true)->first();
                                            @endphp
                                            <span class="text-success">{{ $correctOption ? ($correctOption->content == 'true' ? 'صحيح' : 'خطأ') : '-' }}</span>
                                        @elseif($question->type == 'multi_select')
                                            @php
                                                $correctItems = $question->options->where('is_correct', true)
                                                    ->map(fn ($opt) => format_question_markup($opt->content))
                                                    ->toArray();
                                            @endphp
                                            <span class="text-success question-text-body">{!! implode('، ', $correctItems) !!}</span>
                                        @elseif($question->type == 'ordering')
                                            @php
                                                $correctOrder = $question->options->sortBy('correct_order')
                                                    ->map(fn ($opt) => format_question_markup($opt->content))
                                                    ->toArray();
                                            @endphp
                                            <span class="text-success question-text-body">
                                                <br>
                                                @foreach($correctOrder as $index => $item)
                                                    {{ ($index + 1) }}. {!! $item !!}<br>
                                                @endforeach
                                            </span>
                                        @elseif($question->type == 'drag_drop')
                                            @php
                                                $correctAssignments = [];
                                                foreach ($question->options as $option) {
                                                    if (! $option->match_target) {
                                                        continue;
                                                    }
                                                    $zone = trim(html_entity_decode(strip_tags((string) $option->match_target), ENT_QUOTES, 'UTF-8'));
                                                    if ($zone === '') {
                                                        continue;
                                                    }
                                                    if (! isset($correctAssignments[$zone])) {
                                                        $correctAssignments[$zone] = [];
                                                    }
                                                    $correctAssignments[$zone][] = format_question_markup($option->content);
                                                }
                                            @endphp
                                            <span class="text-success">
                                                @if(!empty($correctAssignments))
                                                    <br>
                                                    @foreach($correctAssignments as $zone => $items)
                                                        <strong>{{ $zone }}:</strong> <span class="question-text-body">{!! implode('، ', $items) !!}</span><br>
                                                    @endforeach
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        @elseif($question->type == 'matching')
                                            @php
                                                $correctPairs = [];
                                                foreach ($question->options as $option) {
                                                    if ($option->match_target) {
                                                        $correctPairs[] = [
                                                            'left' => $option->content,
                                                            'right' => $option->match_target,
                                                        ];
                                                    }
                                                }
                                            @endphp
                                            <span class="text-success">
                                                @if(!empty($correctPairs))
                                                    <br>
                                                    @foreach($correctPairs as $pair)
                                                        <span class="question-text-body">{!! format_question_markup($pair['left']) !!}</span> ← <span class="question-text-body">{!! format_question_markup($pair['right']) !!}</span><br>
                                                    @endforeach
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        @elseif($question->type == 'fill_blank' || $question->type == 'fill_blanks')
                                            @php
                                                // Get correct answers from question options or config
                                                $correctBlanks = $question->options->pluck('content')->toArray();
                                            @endphp
                                            <span class="text-success">
                                                @if(!empty($correctBlanks))
                                                    @foreach($correctBlanks as $index => $blank)
                                                        ({{ $index + 1 }}) <span class="question-text-body">{!! format_question_markup($blank) !!}</span>{{ !$loop->last ? '، ' : '' }}
                                                    @endforeach
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        @elseif($question->type == 'numerical' || $question->type == 'numeric')
                                            @php
                                                $correctNumeric = $question->options->firstWhere('is_correct', true)
                                                    ?? $question->correctOptions->first()
                                                    ?? $question->options->first();
                                            @endphp
                                            <span class="text-success">
                                                {{ $correctNumeric->content ?? '-' }}
                                                @if($question->tolerance)
                                                    <span class="text-muted">(± {{ $question->tolerance }})</span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-success">-</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                        
                        @if($answers->count() == 0)
                            <div class="sqr-empty">
                                <div class="sqr-empty__icon" aria-hidden="true">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <h5 class="mb-2 fw-bold">لا توجد إجابات</h5>
                                <p class="text-muted mb-0">لم يتم العثور على أي إجابات لهذا الاختبار</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('scripts')
    @include('partials.question-math-scripts')
@endpush
