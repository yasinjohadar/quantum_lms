@extends('admin.layouts.master')

@section('page-title')
    إحصائيات الاختبار
@stop

@section('css')
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إحصائيات: {{ $quiz->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.index') }}">الاختبارات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.show', $quiz->id) }}">{{ Str::limit($quiz->title, 30) }}</a></li>
                            <li class="breadcrumb-item active">الإحصائيات</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- Page Header Close -->
    {{-- إحصائيات عامة --}}
    <div class="row mb-3">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card custom-card">
                <div class="card-body text-center">
                    <i class="bi bi-people fs-1 text-primary mb-2"></i>
                    <h3 class="mb-0">{{ $stats['total_attempts'] }}</h3>
                    <p class="text-muted mb-0">إجمالي المحاولات</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card custom-card">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle fs-1 text-success mb-2"></i>
                    <h3 class="mb-0">{{ $stats['passed'] }}</h3>
                    <p class="text-muted mb-0">ناجحون</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card custom-card">
                <div class="card-body text-center">
                    <i class="bi bi-x-circle fs-1 text-danger mb-2"></i>
                    <h3 class="mb-0">{{ $stats['failed'] }}</h3>
                    <p class="text-muted mb-0">راسبون</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card custom-card">
                <div class="card-body text-center">
                    <i class="bi bi-percent fs-1 text-info mb-2"></i>
                    <h3 class="mb-0">{{ $stats['average_score'] }}%</h3>
                    <p class="text-muted mb-0">متوسط الدرجات</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-3">
            {{-- توزيع الدرجات --}}
            <div class="card custom-card h-100">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i> توزيع الدرجات</h6>
                </div>
                <div class="card-body">
                    @foreach($scoreDistribution as $range => $count)
                        @php
                            $percentage = $stats['completed_attempts'] > 0 ? ($count / $stats['completed_attempts']) * 100 : 0;
                            $color = match(true) {
                                str_starts_with($range, '81') => 'success',
                                str_starts_with($range, '61') => 'info',
                                str_starts_with($range, '41') => 'warning',
                                default => 'danger',
                            };
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>{{ $range }}%</span>
                                <span>{{ $count }} طالب ({{ round($percentage) }}%)</span>
                            </div>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-{{ $color }}" 
                                     style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            {{-- معلومات إضافية --}}
            <div class="card custom-card h-100">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i> معلومات إضافية</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">المحاولات المكتملة:</span>
                            <span class="fw-semibold">{{ $stats['completed_attempts'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">المحاولات الجارية:</span>
                            <span class="fw-semibold">{{ $stats['in_progress'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">أعلى درجة:</span>
                            <span class="fw-semibold text-success">{{ $stats['highest_score'] }}%</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">أدنى درجة:</span>
                            <span class="fw-semibold text-danger">{{ $stats['lowest_score'] }}%</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">متوسط الوقت:</span>
                            <span class="fw-semibold">{{ gmdate('H:i:s', $stats['average_time']) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">نسبة النجاح:</span>
                            <span class="fw-semibold">
                                @if($stats['completed_attempts'] > 0)
                                    {{ round(($stats['passed'] / $stats['completed_attempts']) * 100) }}%
                                @else
                                    -
                                @endif
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- أصعب الأسئلة --}}
    @if($hardestQuestions->isNotEmpty())
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i> أصعب الأسئلة (أقل نسبة إجابة صحيحة)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>السؤال</th>
                                <th>النوع</th>
                                <th>عدد الإجابات</th>
                                <th>الإجابات الصحيحة</th>
                                <th>نسبة النجاح</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hardestQuestions as $question)
                                <tr>
                                    <td>{{ Str::limit($question->title, 50) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $question->type_color }}-transparent text-{{ $question->type_color }}">
                                            {{ $question->type_name }}
                                        </span>
                                    </td>
                                    <td>{{ $question->total_answers }}</td>
                                    <td>{{ $question->correct_answers }}</td>
                                    <td>
                                        <span class="badge bg-{{ $question->correct_percentage >= 50 ? 'success' : 'danger' }}">
                                            {{ $question->correct_percentage }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

        </div>
    </div>
    <!-- End::app-content -->
@stop

@section('js')
@stop

