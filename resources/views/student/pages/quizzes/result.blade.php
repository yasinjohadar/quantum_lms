@extends('student.layouts.master')

@section('page-title')
    نتيجة الاختبار - {{ $quiz->title }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">نتيجة الاختبار</h4>
                <p class="mb-0 text-muted">{{ $quiz->title }}</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.quizzes.results') }}">نتائج الاختبارات</a></li>
                    <li class="breadcrumb-item active">{{ $quiz->title }}</li>
                </ol>
            </nav>
        </div>
        <!-- End Page Header -->

        <!-- Result Summary Card -->
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card custom-card text-center">
                    <div class="card-body py-5">
                        @php
                            $percentage = $attempt->max_score > 0 ? ($attempt->score / $attempt->max_score) * 100 : 0;
                            $passed = $percentage >= ($quiz->passing_percentage ?? 50);
                        @endphp
                        
                        <div class="mb-4">
                            @if($passed)
                                <div class="avatar avatar-xxl bg-success-transparent rounded-circle mx-auto mb-3">
                                    <i class="bi bi-trophy-fill fs-1 text-success"></i>
                                </div>
                                <h3 class="text-success mb-2">ناجح!</h3>
                            @else
                                <div class="avatar avatar-xxl bg-danger-transparent rounded-circle mx-auto mb-3">
                                    <i class="bi bi-x-circle-fill fs-1 text-danger"></i>
                                </div>
                                <h3 class="text-danger mb-2">راسب</h3>
                            @endif
                        </div>
                        
                        <div class="display-4 fw-bold {{ $passed ? 'text-success' : 'text-danger' }} mb-2">
                            {{ number_format($percentage, 1) }}%
                        </div>
                        
                        <h5 class="text-muted mb-4">
                            {{ $attempt->score }} / {{ $attempt->max_score }} نقطة
                        </h5>
                        
                        <div class="progress mb-4" style="height: 12px;">
                            <div class="progress-bar {{ $passed ? 'bg-success' : 'bg-danger' }}" 
                                 role="progressbar" 
                                 style="width: {{ $percentage }}%">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-center gap-2">
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
                        </div>
                    </div>
                </div>
                
                <!-- Quiz Info -->
                <div class="card custom-card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            معلومات الاختبار
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">المادة</span>
                                <span class="fw-semibold">{{ $quiz->subject->name ?? 'عام' }}</span>
                            </li>
                            @if($quiz->unit)
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">الوحدة</span>
                                <span class="fw-semibold">{{ $quiz->unit->title }}</span>
                            </li>
                            @endif
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">عدد الأسئلة</span>
                                <span class="fw-semibold">{{ $answers->count() }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">رقم المحاولة</span>
                                <span class="fw-semibold">{{ $attempt->attempt_number }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">تاريخ البدء</span>
                                <span class="fw-semibold">{{ $attempt->started_at->format('Y-m-d H:i') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">تاريخ الانتهاء</span>
                                <span class="fw-semibold">{{ $attempt->finished_at ? $attempt->finished_at->format('Y-m-d H:i') : '-' }}</span>
                            </li>
                            @if($quiz->duration_minutes && $attempt->started_at && $attempt->finished_at)
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">الوقت المستغرق</span>
                                <span class="fw-semibold">
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
                <div class="card custom-card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-list-check me-2"></i>
                            مراجعة الإجابات
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($answers as $index => $answer)
                            @php
                                $question = $answer->question;
                                $isCorrect = $answer->is_correct;
                            @endphp
                            <div class="border rounded p-3 mb-3 {{ $isCorrect ? 'border-success bg-success-transparent' : 'border-danger bg-danger-transparent' }}">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <span class="badge {{ $isCorrect ? 'bg-success' : 'bg-danger' }} me-2">
                                            {{ $index + 1 }}
                                        </span>
                                        <span class="fw-semibold">{{ $question->title ?? 'سؤال ' . ($index + 1) }}</span>
                                    </div>
                                    <div>
                                        @if($isCorrect)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>
                                                صحيح - {{ $answer->points_earned ?? 0 }} نقطة
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle me-1"></i>
                                                خطأ
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($question->content)
                                    <div class="mb-3 text-muted">
                                        {!! strip_tags($question->content) !!}
                                    </div>
                                @endif
                                
                                <!-- Student Answer -->
                                <div class="mb-2">
                                    <strong class="text-dark">إجابتك:</strong>
                                    @if($question->type == 'multiple_choice' || $question->type == 'single_choice')
                                        @php
                                            $selectedOptions = $answer->selected_options ?? [];
                                            $selectedOption = $question->options->whereIn('id', $selectedOptions)->first();
                                        @endphp
                                        <span class="{{ $isCorrect ? 'text-success' : 'text-danger' }}">
                                            {{ $selectedOption->content ?? 'لم يتم الإجابة' }}
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
                                    @elseif($question->type == 'numeric')
                                        <span class="{{ $isCorrect ? 'text-success' : 'text-danger' }}">
                                            {{ $answer->numeric_answer ?? 'لم يتم الإجابة' }}
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
                                            $selectedItems = $question->options->whereIn('id', $selectedOptions)->pluck('content')->toArray();
                                        @endphp
                                        <span class="{{ $isCorrect ? 'text-success' : 'text-danger' }}">
                                            {{ count($selectedItems) > 0 ? implode('، ', $selectedItems) : 'لم يتم الإجابة' }}
                                        </span>
                                    @elseif($question->type == 'ordering')
                                        @php
                                            $ordering = $answer->ordering ?? [];
                                            $orderedItems = [];
                                            if (!empty($ordering)) {
                                                foreach ($ordering as $index => $optionId) {
                                                    $option = $question->options->firstWhere('id', $optionId);
                                                    if ($option) {
                                                        $orderedItems[] = ($index + 1) . '. ' . $option->content;
                                                    }
                                                }
                                            }
                                        @endphp
                                        <span class="{{ $isCorrect ? 'text-success' : 'text-danger' }}">
                                            @if(count($orderedItems) > 0)
                                                <br>
                                                @foreach($orderedItems as $item)
                                                    {{ $item }}<br>
                                                @endforeach
                                            @else
                                                لم يتم الإجابة
                                            @endif
                                        </span>
                                    @elseif($question->type == 'drag_drop')
                                        @php
                                            $assignments = $answer->drag_drop_assignments ?? [];
                                            // Group items by zone
                                            $zoneItems = [];
                                            foreach ($assignments as $itemId => $zoneId) {
                                                $option = $question->options->firstWhere('id', $itemId);
                                                if ($option) {
                                                    $zoneName = 'مجموعة ' . ((int)$zoneId + 1);
                                                    if (!isset($zoneItems[$zoneName])) {
                                                        $zoneItems[$zoneName] = [];
                                                    }
                                                    $zoneItems[$zoneName][] = $option->content;
                                                }
                                            }
                                        @endphp
                                        <span class="{{ $isCorrect ? 'text-success' : 'text-danger' }}">
                                            @if(!empty($zoneItems))
                                                <br>
                                                @foreach($zoneItems as $zoneName => $items)
                                                    <strong>{{ $zoneName }}:</strong>
                                                    {{ implode('، ', $items) }}
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
                                            foreach ($pairs as $leftId => $rightId) {
                                                $leftOption = $question->options->firstWhere('id', $leftId);
                                                $rightOption = $question->options->firstWhere('id', $rightId);
                                                if ($leftOption && $rightOption) {
                                                    $pairItems[] = $leftOption->content . ' ← ' . $rightOption->content;
                                                }
                                            }
                                        @endphp
                                        <span class="{{ $isCorrect ? 'text-success' : 'text-danger' }}">
                                            @if(!empty($pairItems))
                                                <br>
                                                @foreach($pairItems as $pair)
                                                    {{ $pair }}<br>
                                                @endforeach
                                            @else
                                                لم يتم الإجابة
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-muted">{{ $answer->answer_text ?? 'لم يتم الإجابة' }}</span>
                                    @endif
                                </div>
                                
                                <!-- Correct Answer (if wrong) -->
                                @if(!$isCorrect && $quiz->show_correct_answers)
                                    <div>
                                        <strong class="text-success">الإجابة الصحيحة:</strong>
                                        @if($question->type == 'multiple_choice' || $question->type == 'single_choice')
                                            @php
                                                $correctOption = $question->options->where('is_correct', true)->first();
                                            @endphp
                                            <span class="text-success">{{ $correctOption->content ?? '-' }}</span>
                                        @elseif($question->type == 'true_false')
                                            @php
                                                $correctOption = $question->options->where('is_correct', true)->first();
                                            @endphp
                                            <span class="text-success">{{ $correctOption ? ($correctOption->content == 'true' ? 'صحيح' : 'خطأ') : '-' }}</span>
                                        @elseif($question->type == 'multi_select')
                                            @php
                                                $correctItems = $question->options->where('is_correct', true)->pluck('content')->toArray();
                                            @endphp
                                            <span class="text-success">{{ implode('، ', $correctItems) }}</span>
                                        @elseif($question->type == 'ordering')
                                            @php
                                                $correctOrder = $question->options->sortBy('order')->pluck('content')->toArray();
                                            @endphp
                                            <span class="text-success">
                                                <br>
                                                @foreach($correctOrder as $index => $item)
                                                    {{ ($index + 1) }}. {{ $item }}<br>
                                                @endforeach
                                            </span>
                                        @elseif($question->type == 'drag_drop')
                                            @php
                                                // Get correct assignments from question config or options
                                                $correctAssignments = [];
                                                foreach ($question->options as $option) {
                                                    $zone = $option->zone ?? $option->group ?? 'منطقة';
                                                    if (!isset($correctAssignments[$zone])) {
                                                        $correctAssignments[$zone] = [];
                                                    }
                                                    $correctAssignments[$zone][] = $option->content;
                                                }
                                            @endphp
                                            <span class="text-success">
                                                @if(!empty($correctAssignments))
                                                    <br>
                                                    @foreach($correctAssignments as $zone => $items)
                                                        <strong>{{ $zone }}:</strong> {{ implode('، ', $items) }}<br>
                                                    @endforeach
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        @elseif($question->type == 'matching')
                                            @php
                                                $correctPairs = [];
                                                foreach ($question->options as $option) {
                                                    if ($option->match_pair) {
                                                        $correctPairs[$option->content] = $option->match_pair;
                                                    }
                                                }
                                            @endphp
                                            <span class="text-success">
                                                @if(!empty($correctPairs))
                                                    <br>
                                                    @foreach($correctPairs as $left => $right)
                                                        {{ $left }} ← {{ $right }}<br>
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
                                                        ({{ $index + 1 }}) {{ $blank }}{{ !$loop->last ? '، ' : '' }}
                                                    @endforeach
                                                @else
                                                    -
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
                            <div class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                                <h5 class="mb-2">لا توجد إجابات</h5>
                                <p class="text-muted">لم يتم العثور على أي إجابات لهذا الاختبار</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

