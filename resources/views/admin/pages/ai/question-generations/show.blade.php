@extends('admin.layouts.master')

@section('page-title')
    الأسئلة المولدة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">الأسئلة المولدة</h5>
            </div>
            <div>
                <a href="{{ route('admin.ai.question-generations.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">
                        <h6>معلومات الطلب</h6>
                        <p><strong>الحالة:</strong> 
                            @if($generation->status === 'completed')
                                <span class="badge bg-success">مكتمل</span>
                            @elseif($generation->status === 'processing')
                                <span class="badge bg-warning">قيد المعالجة</span>
                            @elseif($generation->status === 'failed')
                                <span class="badge bg-danger">فشل</span>
                            @else
                                <span class="badge bg-secondary">معلق</span>
                            @endif
                        </p>
                        <p><strong>الموديل:</strong> {{ $generation->model->name ?? '-' }}</p>
                        <p><strong>التكلفة:</strong> {{ $generation->cost ? number_format($generation->cost, 6) : '-' }}</p>
                        @if($generation->error_message)
                            <div class="alert alert-danger">
                                <strong>خطأ:</strong> {{ $generation->error_message }}
                            </div>
                        @endif
                    </div>
                </div>

                @if($generation->status === 'completed' && $generation->generated_questions)
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">الأسئلة المولدة ({{ count($generation->generated_questions) }})</h6>
                            <form action="{{ route('admin.ai.question-generations.save', $generation->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-save me-1"></i> حفظ جميع الأسئلة
                                </button>
                            </form>
                        </div>
                        <div class="card-body">
                            @foreach($generation->generated_questions as $index => $question)
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6>سؤال {{ $index + 1 }}</h6>
                                        <p><strong>النوع:</strong> {{ \App\Models\AIQuestionGeneration::QUESTION_TYPES[$question['type'] ?? 'single_choice'] ?? $question['type'] }}</p>
                                        <p><strong>السؤال:</strong> {{ $question['question'] ?? '-' }}</p>
                                        @if(isset($question['options']) && count($question['options']) > 0)
                                            <p><strong>الخيارات:</strong></p>
                                            <ul>
                                                @foreach($question['options'] as $option)
                                                    <li>{{ $option }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                        <p><strong>الإجابة الصحيحة:</strong> {{ $question['correct_answer'] ?? '-' }}</p>
                                        @if(isset($question['explanation']))
                                            <p><strong>الشرح:</strong> {{ $question['explanation'] }}</p>
                                        @endif
                                        <p><strong>الصعوبة:</strong> {{ \App\Models\AIQuestionGeneration::DIFFICULTIES[$question['difficulty'] ?? 'medium'] ?? $question['difficulty'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif($generation->status === 'pending')
                    <div class="alert alert-info">
                        <p>الطلب في انتظار المعالجة. <a href="{{ route('admin.ai.question-generations.process', $generation->id) }}" class="btn btn-sm btn-primary">بدء المعالجة</a></p>
                    </div>
                @elseif($generation->status === 'processing')
                    <div class="alert alert-warning">
                        <p>الطلب قيد المعالجة...</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

