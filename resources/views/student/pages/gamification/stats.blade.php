@extends('student.layouts.master')

@section('page-title')
    إحصائياتي
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إحصائياتي</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">الإحصائيات</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">إجمالي النقاط: {{ number_format($stats['total_points']) }}</div>
                    </div>
                    <div class="card-body">
                        <h6>توزيع النقاط حسب النوع:</h6>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h5>حضور</h5>
                                        <h3>{{ number_format($stats['points_by_type']['attendance']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h5>إكمال دروس</h5>
                                        <h3>{{ number_format($stats['points_by_type']['lesson_completion']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h5>اختبارات</h5>
                                        <h3>{{ number_format($stats['points_by_type']['quiz']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h5>أسئلة</h5>
                                        <h3>{{ number_format($stats['points_by_type']['question']) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($stats['level_progress'])
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">تقدم المستوى</div>
                    </div>
                    <div class="card-body">
                        @php
                            $progress = $stats['level_progress'];
                        @endphp
                        <div class="text-center mb-3">
                            <h4>المستوى الحالي: {{ $progress['current_level'] ? $progress['current_level']->name : 'لا يوجد' }}</h4>
                            @if($progress['next_level'])
                                <p>المستوى التالي: {{ $progress['next_level']->name }}</p>
                            @endif
                        </div>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: {{ $progress['progress_percentage'] }}%"
                                 aria-valuenow="{{ $progress['progress_percentage'] }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ number_format($progress['progress_percentage'], 1) }}%
                            </div>
                        </div>
                        <p class="text-center mt-2">
                            {{ number_format($progress['current_points']) }} / {{ number_format($progress['points_required']) }} نقطة
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">تاريخ النقاط (آخر 30 معاملة)</div>
                    </div>
                    <div class="card-body">
                        @if($stats['points_history']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>التاريخ</th>
                                            <th>النوع</th>
                                            <th>النقاط</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stats['points_history'] as $transaction)
                                        <tr>
                                            <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                            <td>{{ $transaction->type_name }}</td>
                                            <td class="{{ $transaction->points > 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $transaction->points > 0 ? '+' : '' }}{{ number_format($transaction->points) }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">لا توجد معاملات نقاط</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<!-- End::app-content -->
@stop

