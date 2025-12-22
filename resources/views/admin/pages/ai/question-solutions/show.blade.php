@extends('admin.layouts.master')

@section('page-title')
    عرض حل AI
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">عرض حل AI</h5>
            </div>
            <div>
                <a href="{{ route('admin.ai.question-solutions.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">السؤال</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>نوع السؤال:</strong> {{ \App\Models\Question::TYPES[$solution->question->type] ?? $solution->question->type }}</p>
                        <p><strong>السؤال:</strong> {{ $solution->question->title ?? $solution->question->content }}</p>
                                        @if($solution->question->options && $solution->question->options->count() > 0)
                                            <p><strong>الخيارات:</strong></p>
                                            <ul>
                                                @foreach($solution->question->options as $option)
                                                    <li>{{ $option->content }} @if($option->is_correct) <span class="badge bg-success">صحيح</span> @endif</li>
                                                @endforeach
                                            </ul>
                                        @endif
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">حل AI</h6>
                        @if(!$solution->is_verified)
                            <form action="{{ route('admin.ai.question-solutions.verify', $solution->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-check me-1"></i> التحقق من الحل
                                </button>
                            </form>
                        @endif
                    </div>
                    <div class="card-body">
                        <p><strong>الموديل:</strong> {{ $solution->model->name ?? '-' }}</p>
                        <p><strong>درجة الثقة:</strong> 
                            @if($solution->confidence_score)
                                <span class="badge bg-{{ $solution->confidence_score >= 0.8 ? 'success' : ($solution->confidence_score >= 0.5 ? 'warning' : 'danger') }}">
                                    {{ number_format($solution->confidence_score * 100, 1) }}%
                                </span>
                            @else
                                -
                            @endif
                        </p>
                        <p><strong>الدقة:</strong> {{ number_format($accuracy * 100, 1) }}%</p>
                        <hr>
                        <h6>الحل:</h6>
                        <div class="p-3 bg-light rounded">
                            {!! nl2br(e($solution->solution)) !!}
                        </div>
                        @if($solution->explanation)
                            <hr>
                            <h6>الشرح:</h6>
                            <div class="p-3 bg-light rounded">
                                {!! nl2br(e($solution->explanation)) !!}
                            </div>
                        @endif
                        @if($solution->is_verified)
                            <hr>
                            <p><strong>تم التحقق بواسطة:</strong> {{ $solution->verifier->name ?? '-' }}</p>
                            <p><strong>تاريخ التحقق:</strong> {{ $solution->verified_at?->format('Y-m-d H:i') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

