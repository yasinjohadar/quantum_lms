@extends('admin.layouts.master')

@include('partials.question-math-assets')

@section('page-title')
    الأسئلة المولدة #{{ $generation->id }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">
                    <i class="fas fa-robot text-primary me-2"></i>
                    طلب توليد الأسئلة #{{ $generation->id }}
                </h5>
            </div>
            <div class="d-flex gap-2">
                @if($generation->status === 'completed' || $generation->status === 'failed')
                    <form action="{{ route('admin.ai.question-generations.regenerate', $generation->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm">
                            <i class="fas fa-redo me-1"></i> إعادة التوليد
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.ai.question-generations.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                @if(session('saved_to_bank'))
                    <a href="{{ route('admin.questions.index') }}" class="alert-link ms-2">فتح بنك الأسئلة</a>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if($generation->status === 'failed' && $generation->error_message)
            @php
                $displayError = \App\Services\AI\AIQuestionGenerationService::humanizeApiErrorMessage($generation->error_message);
            @endphp
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-times-circle me-2"></i>
                <strong>فشل التوليد:</strong> {{ $displayError }}
                <div class="mt-2">
                    <form action="{{ route('admin.ai.question-generations.regenerate', $generation->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-light">
                            <i class="fas fa-redo me-1"></i> إعادة المحاولة
                        </button>
                    </form>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        {{-- رسالة تحذيرية إذا كان العدد أقل من المطلوب --}}
        @if($generation->status === 'completed' && $generation->error_message && str_contains($generation->error_message, 'سؤال'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>تحذير:</strong> {{ $generation->error_message }}
                <br><small class="text-muted">💡 يمكنك إعادة التوليد أو حفظ الأسئلة المتوفرة.</small>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @php
            $questions = $questions ?? $generation->getResolvedGeneratedQuestions();
            $questionsCount = $questionsCount ?? count($questions);
        @endphp

        <div class="row">
            {{-- معلومات الطلب --}}
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>معلومات الطلب</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td class="text-muted" style="width: 40%;">الحالة:</td>
                                <td>
                                    @if($generation->status === 'completed')
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i>مكتمل</span>
                                    @elseif($generation->status === 'processing')
                                        <span class="badge bg-warning"><i class="fas fa-spinner fa-spin me-1"></i>قيد المعالجة</span>
                                    @elseif($generation->status === 'failed')
                                        <span class="badge bg-danger"><i class="fas fa-times me-1"></i>فشل</span>
                                    @else
                                        <span class="badge bg-secondary"><i class="fas fa-clock me-1"></i>معلق</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">نوع المصدر:</td>
                                <td>{{ \App\Models\AIQuestionGeneration::SOURCE_TYPES[$generation->source_type] ?? $generation->source_type }}</td>
                            </tr>
                            @if($generation->source_type === 'image' && $generation->source_image_path)
                            <tr>
                                <td class="text-muted">الصورة:</td>
                                <td>
                                    <a href="{{ route('admin.ai.question-generations.source-image', $generation) }}" target="_blank" rel="noopener">
                                        <img src="{{ route('admin.ai.question-generations.source-image', $generation) }}" alt="مصدر الصورة" class="img-fluid rounded border" style="max-height: 220px;">
                                    </a>
                                </td>
                            </tr>
                            @endif
                            @if($generation->source_type === 'pdf' && $generation->source_image_path)
                            <tr>
                                <td class="text-muted">ملف PDF:</td>
                                <td>
                                    <a href="{{ route('admin.ai.question-generations.source-image', $generation) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-file-pdf me-1"></i> عرض / تحميل PDF
                                    </a>
                                </td>
                            </tr>
                            @endif
                            @if(in_array($generation->source_type, ['image', 'pdf'], true) && $generation->source_content)
                            <tr>
                                <td class="text-muted">تعليمات إضافية:</td>
                                <td><small>{{ $generation->source_content }}</small></td>
                            </tr>
                            @endif
                            @if($generation->subject_id || $generation->unit_id)
                            @if($generation->subject?->schoolClass)
                            <tr>
                                <td class="text-muted">الصف:</td>
                                <td>{{ $generation->subject->schoolClass->name }}</td>
                            </tr>
                            @endif
                            @if($generation->subject)
                            <tr>
                                <td class="text-muted">المادة:</td>
                                <td>{{ $generation->subject->name }}</td>
                            </tr>
                            @endif
                            @if($generation->unit)
                            <tr>
                                <td class="text-muted">الوحدة:</td>
                                <td>{{ $generation->unit->title }}</td>
                            </tr>
                            @endif
                            @endif
                            <tr>
                                <td class="text-muted">نوع الأسئلة:</td>
                                <td>
                                    @php
                                        $selectedTypes = $generation->getSelectedQuestionTypes();
                                    @endphp
                                    @if(!empty($selectedTypes) && count($selectedTypes) > 0)
                                        @foreach($selectedTypes as $type)
                                            <span class="badge bg-info me-1 mb-1">
                                                {{ \App\Models\Question::TYPES[$type] ?? $type }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="badge bg-info">
                                            {{ \App\Models\AIQuestionGeneration::QUESTION_TYPES[$generation->question_type] ?? $generation->question_type }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">العدد المطلوب:</td>
                                <td><span class="badge bg-info">{{ $generation->number_of_questions }}</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">مستوى الصعوبة:</td>
                                <td>{{ \App\Models\AIQuestionGeneration::DIFFICULTIES[$generation->difficulty_level] ?? $generation->difficulty_level }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">الموديل:</td>
                                <td>
                                    @if($generation->model)
                                        <span class="badge bg-dark">{{ $generation->model->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">المستخدم:</td>
                                <td>{{ $generation->user->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">التاريخ:</td>
                                <td>{{ $generation->created_at ? $generation->created_at->format('Y-m-d H:i') : '-' }}</td>
                            </tr>
                            @if($generation->tokens_used)
                            <tr>
                                <td class="text-muted">Tokens:</td>
                                <td>{{ number_format($generation->tokens_used) }}</td>
                            </tr>
                            @endif
                            @if($generation->cost)
                            <tr>
                                <td class="text-muted">التكلفة:</td>
                                <td>${{ number_format($generation->cost, 6) }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                {{-- المحتوى المصدر --}}
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>المحتوى المصدر</h6>
                    </div>
                    <div class="card-body">
                        <div class="bg-light p-3 rounded" style="max-height: 200px; overflow-y: auto;">
                            {{ $generation->source_content }}
                        </div>
                    </div>
                </div>

                {{-- رسالة الخطأ --}}
                @if($generation->error_message)
                    <div class="card shadow-sm border-danger mb-3">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>رسالة الخطأ</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-danger mb-0">{{ $generation->error_message }}</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- الأسئلة المولدة --}}
            <div class="col-lg-8">
                @if($generation->status === 'completed')
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="fas fa-clipboard-check me-2 text-primary"></i>ملخص المراجعة</h6>
                            <div class="row g-2 small">
                                <div class="col-md-4">
                                    <span class="text-muted d-block">الأسئلة المولّدة</span>
                                    <span class="fw-bold">{{ $questionsCount }} / {{ $generation->number_of_questions }}</span>
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted d-block">الصف / المادة / الوحدة</span>
                                    <span class="fw-bold">
                                        {{ $generation->subject?->schoolClass?->name ?? '—' }}
                                        /
                                        {{ $generation->subject?->name ?? '—' }}
                                        /
                                        {{ $generation->unit?->title ?? '—' }}
                                    </span>
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted d-block">حالة الحفظ</span>
                                    @if($generation->hasSavedQuestions())
                                        <span class="badge bg-success">تم الحفظ في بنك الأسئلة ({{ $generation->questions_saved_at?->format('Y-m-d H:i') }})</span>
                                    @else
                                        <span class="badge bg-secondary">لم يُحفظ بعد — راجع ثم احفظ</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($questionsCount > 0)
                        <div class="card shadow-sm border-0">
                            <div class="card-header {{ $questionsCount < $generation->number_of_questions ? 'bg-warning text-dark' : 'bg-success text-white' }}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">
                                        <i class="fas fa-question-circle me-2"></i>
                                        مراجعة الأسئلة المولدة ({{ $questionsCount }} / {{ $generation->number_of_questions }})
                                        @if($questionsCount < $generation->number_of_questions)
                                            <span class="badge bg-danger ms-2">
                                                ناقص {{ $generation->number_of_questions - $questionsCount }} سؤال
                                            </span>
                                        @endif
                                    </h6>
                                    @if(!$generation->hasSavedQuestions())
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-light btn-sm" onclick="selectAll()">
                                            <i class="fas fa-check-square me-1"></i> تحديد الكل
                                        </button>
                                        <button type="button" class="btn btn-light btn-sm" onclick="deselectAll()">
                                            <i class="fas fa-square me-1"></i> إلغاء التحديد
                                        </button>
                                    </div>
                                    @endif
                                </div>
                                @if(!$generation->hasSavedQuestions())
                                <div class="d-flex gap-2">
                                    <form action="{{ route('admin.ai.question-generations.save-selected', $generation->id) }}" method="POST" id="saveSelectedForm" class="d-inline" onsubmit="return saveSelected()">
                                        @csrf
                                        <input type="hidden" name="selected_questions[]" id="selectedQuestionsInput">
                                        <button type="submit" class="btn btn-warning btn-sm">
                                            <i class="fas fa-save me-1"></i> حفظ المحدد (<span id="selectedCount">0</span>)
                                        </button>
                                    </form>
                                    <button type="button" 
                                            class="btn btn-light btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#saveAllModal">
                                        <i class="fas fa-save me-1"></i> حفظ الكل
                                    </button>
                                </div>
                                @else
                                    <div class="alert alert-success mb-0 py-2">
                                        <i class="fas fa-check-circle me-1"></i>
                                        تم حفظ هذه الأسئلة في بنك الأسئلة.
                                        <a href="{{ route('admin.questions.index') }}" class="alert-link">عرض بنك الأسئلة</a>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body">
                                <form id="questionsForm">
                                    @foreach($questions as $index => $question)
                                        <div class="card mb-3 border-start border-primary border-3 question-item" data-index="{{ $index }}">
                                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center gap-2">
                                                    @if(!$generation->hasSavedQuestions())
                                                    <div class="form-check">
                                                        <input class="form-check-input question-checkbox" type="checkbox" 
                                                               value="{{ $index }}" 
                                                               id="question_{{ $index }}"
                                                               onchange="updateSelectedCount()"
                                                               checked>
                                                        <label class="form-check-label" for="question_{{ $index }}"></label>
                                                    </div>
                                                    @endif
                                                    <h6 class="mb-0">
                                                        <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                                        {{ \App\Models\AIQuestionGeneration::QUESTION_TYPES[$question['type'] ?? 'single_choice'] ?? $question['type'] ?? 'سؤال' }}
                                                    </h6>
                                                </div>
                                                <span class="badge bg-{{ ($question['difficulty'] ?? 'medium') === 'easy' ? 'success' : (($question['difficulty'] ?? 'medium') === 'hard' ? 'danger' : 'warning') }}">
                                                    {{ \App\Models\AIQuestionGeneration::DIFFICULTIES[$question['difficulty'] ?? 'medium'] ?? $question['difficulty'] ?? 'متوسط' }}
                                                </span>
                                            </div>
                                            <div class="card-body">
                                                <div class="question-text-body fw-bold mb-3">
                                                    {!! format_question_markup($question['question'] ?? '-') !!}
                                                </div>
                                                
                                                @if(isset($question['options']) && is_array($question['options']) && count($question['options']) > 0)
                                                    <div class="mb-3">
                                                        <strong class="text-muted d-block mb-2">الخيارات:</strong>
                                                        @foreach($question['options'] as $optIndex => $option)
                                                            @php
                                                                $isCorrect = false;
                                                                $correctAnswer = $question['correct_answer'] ?? '';
                                                                if (is_array($correctAnswer)) {
                                                                    $isCorrect = in_array($option, $correctAnswer);
                                                                } else {
                                                                    $isCorrect = trim($option) === trim($correctAnswer);
                                                                }
                                                            @endphp
                                                            <div class="question-option-row {{ $isCorrect ? 'is-correct' : '' }}">
                                                                <span class="badge bg-secondary me-2">{{ chr(65 + $optIndex) }}</span>
                                                                <span class="question-text-body d-inline flex-grow-1">
                                                                    {!! format_question_markup($option) !!}
                                                                </span>
                                                                @if($isCorrect)
                                                                    <i class="fas fa-check text-success ms-2"></i>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="bg-success bg-opacity-10 p-2 rounded">
                                                            <strong class="text-success"><i class="fas fa-check-circle me-1"></i>الإجابة الصحيحة:</strong>
                                                            <p class="mb-0 mt-1 question-text-body">{!! format_question_markup(is_array($question['correct_answer'] ?? '') ? implode(', ', $question['correct_answer']) : ($question['correct_answer'] ?? '-')) !!}</p>
                                                        </div>
                                                    </div>
                                                    @if(isset($question['explanation']) && !empty($question['explanation']))
                                                    <div class="col-md-6">
                                                        <div class="bg-info bg-opacity-10 p-2 rounded">
                                                            <strong class="text-info"><i class="fas fa-lightbulb me-1"></i>الشرح:</strong>
                                                            <p class="mb-0 mt-1 question-text-body">{!! format_question_markup($question['explanation']) !!}</p>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </form>
                            </div>
                        </div>
                    @else
                        {{-- التوليد اكتمل لكن لم يتم استخراج أسئلة --}}
                        <div class="card shadow-sm border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>التوليد اكتمل لكن لم يتم استخراج أسئلة</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-2">
                                    اكتمل التوليد لكن لم يُستخرج أي سؤال صالح للعرض. راجع الرد الخام أدناه أو أعد التوليد.
                                </p>
                                
                                <div class="d-flex gap-2 mb-3">
                                    <form action="{{ route('admin.ai.question-generations.regenerate', $generation->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-redo me-1"></i> إعادة التوليد
                                        </button>
                                    </form>
                                </div>

                                @if($generation->ai_response_preview)
                                    <details class="mb-3">
                                        <summary class="btn btn-outline-warning btn-sm">
                                            <i class="fas fa-robot me-1"></i> عرض مقتطف رد الذكاء الاصطناعي
                                        </summary>
                                        <pre class="bg-dark text-light p-3 rounded mt-2" style="max-height: 300px; overflow-y: auto; direction: ltr; text-align: left;">{{ $generation->ai_response_preview }}</pre>
                                    </details>
                                @endif

                                @if($generation->prompt)
                                    <details class="mb-3">
                                        <summary class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-code me-1"></i> عرض الـ Prompt المرسل
                                        </summary>
                                        <pre class="bg-dark text-light p-3 rounded mt-2" style="max-height: 300px; overflow-y: auto; direction: ltr; text-align: left;">{{ $generation->prompt }}</pre>
                                    </details>
                                @endif

                                <div class="alert alert-info">
                                    <strong>💡 نصيحة:</strong> جرّب إعادة التوليد أو استخدام موديل آخر. قد يكون الموديل الحالي لا يدعم توليد JSON بشكل جيد.
                                </div>
                            </div>
                        </div>
                    @endif
                @elseif($generation->status === 'pending')
                    <div class="card shadow-sm border-0">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-clock fa-4x text-secondary mb-3"></i>
                            <h5>الطلب في انتظار المعالجة</h5>
                            <p class="text-muted">لم تبدأ معالجة هذا الطلب بعد.</p>
                            <form action="{{ route('admin.ai.question-generations.process', $generation->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-play me-2"></i> بدء المعالجة الآن
                                </button>
                            </form>
                        </div>
                    </div>
                @elseif($generation->status === 'processing')
                    <div class="card shadow-sm border-0">
                        <div class="card-body text-center py-5">
                            <div class="spinner-border text-primary mb-3" style="width: 4rem; height: 4rem;" role="status">
                                <span class="visually-hidden">جاري التحميل...</span>
                            </div>
                            <h5>جاري التوليد...</h5>
                            <p class="text-muted">يرجى الانتظار حتى اكتمال المعالجة.</p>
                            <button class="btn btn-outline-primary" onclick="location.reload()">
                                <i class="fas fa-sync-alt me-1"></i> تحديث الصفحة
                            </button>
                        </div>
                    </div>
                @elseif($generation->status === 'failed')
                    <div class="card shadow-sm border-danger">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
                            <h5>فشل التوليد</h5>
                            <p class="text-danger">{{ \App\Services\AI\AIQuestionGenerationService::humanizeApiErrorMessage((string) ($generation->error_message ?? 'حدث خطأ غير معروف')) }}</p>
                            <form action="{{ route('admin.ai.question-generations.regenerate', $generation->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-redo me-2"></i> إعادة المحاولة
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($generation->status === 'completed' && $questionsCount > 0 && !$generation->hasSavedQuestions())
<!-- Modal for Save All -->
<div class="modal fade" id="saveAllModal" tabindex="-1" aria-labelledby="saveAllModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="saveAllModalLabel">
                    <i class="fas fa-save text-success me-2"></i>
                    تأكيد حفظ الأسئلة
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-question-circle fa-3x text-warning"></i>
                </div>
                <h6 class="mb-2">هل أنت متأكد من حفظ جميع الأسئلة؟</h6>
                <p class="text-muted mb-0">سيتم حفظ جميع الأسئلة المولدة في قاعدة البيانات</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <form action="{{ route('admin.ai.question-generations.save', $generation->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> إلغاء
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> نعم، احفظ الكل
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Modal for Save Selected Questions -->
<div class="modal fade" id="saveSelectedModal" tabindex="-1" aria-labelledby="saveSelectedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="saveSelectedModalLabel">
                    <i class="fas fa-save text-warning me-2"></i>
                    تأكيد حفظ الأسئلة المحددة
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-question-circle fa-3x text-warning"></i>
                </div>
                <h6 class="mb-2">هل أنت متأكد من حفظ الأسئلة المحددة؟</h6>
                <p class="text-muted mb-0">سيتم حفظ الأسئلة المحددة فقط في قاعدة البيانات</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> إلغاء
                </button>
                <button type="button" class="btn btn-warning" id="confirmSaveSelected">
                    <i class="fas fa-save me-1"></i> نعم، احفظ المحدد
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
// تحديث عدد الأسئلة المحددة
function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.question-checkbox:checked');
    const count = checkboxes.length;
    const counter = document.getElementById('selectedCount');
    if (counter) {
        counter.textContent = count;
    }
}

// تحديد الكل
function selectAll() {
    document.querySelectorAll('.question-checkbox').forEach(cb => {
        cb.checked = true;
    });
    updateSelectedCount();
}

// إلغاء تحديد الكل
function deselectAll() {
    document.querySelectorAll('.question-checkbox').forEach(cb => {
        cb.checked = false;
    });
    updateSelectedCount();
}

// حفظ الأسئلة المحددة
function saveSelected() {
    const checkboxes = document.querySelectorAll('.question-checkbox:checked');
    const selected = Array.from(checkboxes).map(cb => parseInt(cb.value));
    
    if (selected.length === 0) {
        alert('يرجى تحديد سؤال واحد على الأقل للحفظ');
        return false;
    }
    
    // إزالة جميع الـ hidden inputs القديمة
    const form = document.getElementById('saveSelectedForm');
    const oldInputs = form.querySelectorAll('input[name="selected_questions[]"]');
    oldInputs.forEach(input => {
        if (input.id !== 'selectedQuestionsInput') {
            input.remove();
        }
    });
    
    // إضافة input لكل سؤال محدد
    selected.forEach(index => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected_questions[]';
        input.value = index;
        form.appendChild(input);
    });
    
    // تحديث نص المودال
    const modalBody = document.querySelector('#saveSelectedModal .modal-body p');
    modalBody.textContent = `هل أنت متأكد من حفظ ${selected.length} سؤال محدد؟ سيتم حفظ الأسئلة المحددة فقط في قاعدة البيانات`;
    
    // إظهار المودال
    const modal = new bootstrap.Modal(document.getElementById('saveSelectedModal'));
    modal.show();
    
    return false; // منع إرسال النموذج، سيتم التعامل معه من المودال
}

// معالج تأكيد حفظ الأسئلة المحددة
document.addEventListener('DOMContentLoaded', function() {
    const confirmBtn = document.getElementById('confirmSaveSelected');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            const form = document.getElementById('saveSelectedForm');
            if (form) {
                // إغلاق المودال
                const modal = bootstrap.Modal.getInstance(document.getElementById('saveSelectedModal'));
                modal.hide();
                
                // إرسال النموذج
                form.submit();
            }
        });
    }
});

// تحديث العدد عند التحميل
document.addEventListener('DOMContentLoaded', function() {
    updateSelectedCount();
});
</script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/contrib/auto-render.min.js" crossorigin="anonymous"></script>
    <script src="{{ asset('assets/js/question-math-katex.js') }}?v=20260518b"></script>
@stop

