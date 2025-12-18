@extends('admin.layouts.master')

@section('page-title')
    معاينة الاختبار
@stop

@section('css')
<style>
    .question-preview {
        border-right: 4px solid var(--primary-color);
    }
    .option-preview {
        padding: 10px 15px;
        border: 1px solid var(--default-border);
        border-radius: 8px;
        margin-bottom: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .option-preview:hover {
        background-color: rgba(var(--primary-rgb), 0.05);
        border-color: var(--primary-color);
    }
    .option-preview.correct {
        background-color: rgba(40, 167, 69, 0.1);
        border-color: #28a745;
    }
</style>
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">معاينة: {{ $quiz->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.index') }}">الاختبارات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.show', $quiz->id) }}">{{ Str::limit($quiz->title, 30) }}</a></li>
                            <li class="breadcrumb-item active">معاينة</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-warning text-dark fs-6 d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        وضع المعاينة - لا يتم حفظ الإجابات
                    </span>
                </div>
            </div>
            <!-- Page Header Close -->
    {{-- معلومات الاختبار --}}
    <div class="card custom-card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-1">{{ $quiz->title }}</h5>
                    <p class="text-muted mb-0">{{ $quiz->subject->name ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-md-end gap-3">
                        <div class="text-center">
                            <div class="fs-4 fw-bold text-primary">{{ $quiz->questions->count() }}</div>
                            <small class="text-muted">سؤال</small>
                        </div>
                        <div class="text-center">
                            <div class="fs-4 fw-bold text-success">{{ $quiz->total_points }}</div>
                            <small class="text-muted">درجة</small>
                        </div>
                        <div class="text-center">
                            <div class="fs-4 fw-bold text-info">{{ $quiz->formatted_duration }}</div>
                            <small class="text-muted">المدة</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($quiz->instructions)
        <div class="alert alert-info">
            <h6><i class="bi bi-info-circle me-1"></i> تعليمات الاختبار:</h6>
            {{ $quiz->instructions }}
        </div>
    @endif

    {{-- الأسئلة --}}
    @if($quiz->questions->isEmpty())
        <div class="card custom-card">
            <div class="card-body text-center py-5">
                <i class="bi bi-question-circle display-4 text-muted"></i>
                <p class="text-muted mt-3">لا توجد أسئلة في هذا الاختبار</p>
                <a href="{{ route('admin.quizzes.questions', $quiz->id) }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> إضافة أسئلة
                </a>
            </div>
        </div>
    @else
        @foreach($quiz->questions as $index => $question)
            <div class="card custom-card mb-3 question-preview">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-2 fs-6">{{ $index + 1 }}</span>
                        <span class="badge bg-{{ $question->type_color }}-transparent text-{{ $question->type_color }}">
                            <i class="bi {{ $question->type_icon }} me-1"></i>
                            {{ $question->type_name }}
                        </span>
                    </div>
                    <span class="badge bg-success">{{ $question->pivot->points }} درجة</span>
                </div>
                <div class="card-body">
                    @if($question->image)
                        <div class="mb-3 text-center">
                            <img src="{{ asset('storage/'.$question->image) }}" 
                                 class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                    @endif
                    
                    <h5 class="mb-3">{{ $question->title }}</h5>
                    
                    @if($question->content)
                        <div class="text-muted mb-3">{!! $question->content !!}</div>
                    @endif

                    {{-- عرض الخيارات حسب نوع السؤال --}}
                    @if(in_array($question->type, ['single_choice', 'multiple_choice', 'true_false']))
                        <div class="options-list">
                            @foreach($question->options as $option)
                                <div class="option-preview {{ $option->is_correct ? 'correct' : '' }}">
                                    <div class="d-flex align-items-center">
                                        @if($question->type === 'single_choice' || $question->type === 'true_false')
                                            <i class="bi bi-circle me-2"></i>
                                        @else
                                            <i class="bi bi-square me-2"></i>
                                        @endif
                                        @if($option->image)
                                            <img src="{{ asset('storage/'.$option->image) }}" 
                                                 class="me-2 rounded" style="height: 30px;">
                                        @endif
                                        <span>{{ $option->content }}</span>
                                        @if($option->is_correct)
                                            <span class="badge bg-success ms-auto">
                                                <i class="bi bi-check"></i> صحيح
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif($question->type === 'matching')
                        <div class="row">
                            <div class="col-md-5">
                                <h6 class="text-muted mb-2">العناصر</h6>
                                @foreach($question->options as $option)
                                    <div class="p-2 bg-light rounded mb-2">{{ $option->content }}</div>
                                @endforeach
                            </div>
                            <div class="col-md-2 d-flex align-items-center justify-content-center">
                                <i class="bi bi-arrow-left-right fs-3 text-muted"></i>
                            </div>
                            <div class="col-md-5">
                                <h6 class="text-muted mb-2">المطابقات</h6>
                                @foreach($question->options as $option)
                                    <div class="p-2 bg-success-transparent rounded mb-2">{{ $option->match_target }}</div>
                                @endforeach
                            </div>
                        </div>
                    @elseif($question->type === 'ordering')
                        <div class="alert alert-secondary">
                            <i class="bi bi-info-circle me-1"></i>
                            رتب العناصر التالية بالترتيب الصحيح:
                        </div>
                        @foreach($question->options->sortBy('correct_order') as $option)
                            <div class="p-2 bg-light rounded mb-2 d-flex align-items-center">
                                <span class="badge bg-primary me-2">{{ $option->correct_order }}</span>
                                {{ $option->content }}
                            </div>
                        @endforeach
                    @elseif($question->type === 'fill_blanks')
                        <div class="alert alert-secondary">
                            <i class="bi bi-info-circle me-1"></i>
                            أكمل الفراغات في النص التالي:
                        </div>
                        @if($question->blank_answers)
                            <div class="mt-2">
                                <strong>الإجابات الصحيحة:</strong>
                                @foreach($question->blank_answers as $i => $answer)
                                    <span class="badge bg-success ms-1">فراغ {{ $i + 1 }}: {{ $answer }}</span>
                                @endforeach
                            </div>
                        @endif
                    @elseif($question->type === 'numerical')
                        <div class="alert alert-secondary">
                            <i class="bi bi-info-circle me-1"></i>
                            أدخل الإجابة الرقمية:
                        </div>
                        <input type="number" class="form-control" style="max-width: 200px;" placeholder="الإجابة" disabled>
                        <div class="mt-2">
                            <strong>الإجابة الصحيحة:</strong>
                            <span class="badge bg-success">{{ $question->options->first()->content ?? '-' }}</span>
                            @if($question->tolerance)
                                <span class="text-muted">(± {{ $question->tolerance }})</span>
                            @endif
                        </div>
                    @elseif(in_array($question->type, ['essay', 'short_answer']))
                        <div class="alert alert-secondary">
                            <i class="bi bi-info-circle me-1"></i>
                            {{ $question->type === 'essay' ? 'اكتب إجابتك في الحقل أدناه:' : 'أدخل إجابة قصيرة:' }}
                        </div>
                        <textarea class="form-control" rows="{{ $question->type === 'essay' ? 5 : 2 }}" disabled 
                                  placeholder="إجابة الطالب تظهر هنا..."></textarea>
                    @endif

                    @if($question->explanation)
                        <div class="mt-3 p-3 bg-success-transparent rounded">
                            <strong class="text-success">
                                <i class="bi bi-lightbulb me-1"></i>
                                شرح الإجابة:
                            </strong>
                            <p class="mb-0 mt-1">{{ $question->explanation }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @endif

    <div class="card custom-card">
        <div class="card-body d-flex justify-content-between">
            <a href="{{ route('admin.quizzes.show', $quiz->id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-right me-1"></i> رجوع للاختبار
            </a>
            <a href="{{ route('admin.quizzes.questions', $quiz->id) }}" class="btn btn-primary">
                <i class="bi bi-list-check me-1"></i> إدارة الأسئلة
            </a>
        </div>
    </div>

        </div>
    </div>
    <!-- End::app-content -->
@stop

@section('js')
@stop

