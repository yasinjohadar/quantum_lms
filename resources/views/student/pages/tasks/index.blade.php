@extends('student.layouts.master')

@section('page-title')
    مهامي
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">مهامي</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">مهامي</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- المهام اليومية -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">المهام اليومية</div>
                    </div>
                    <div class="card-body">
                        @if($dailyTasks->count() > 0)
                            <div class="row">
                                @foreach($dailyTasks as $task)
                                @php
                                    $userTask = $dailyUserTasks->get($task->id);
                                    $progress = $userTask ? $userTask->progress : 0;
                                    $requiredCount = $task->criteria['count'] ?? 1;
                                    $percentage = min(($progress / $requiredCount) * 100, 100);
                                @endphp
                                <div class="col-md-6 mb-4">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0">{{ $task->name }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <p>{{ $task->description }}</p>
                                            <div class="mb-2">
                                                <small class="text-muted">النوع: {{ $task->type_name }}</small>
                                            </div>
                                            <div class="progress mb-2">
                                                <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%">
                                                    {{ $progress }} / {{ $requiredCount }}
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-warning">مكافأة: {{ number_format($task->points_reward) }} نقطة</span>
                                                @if($userTask && $userTask->status === 'completed')
                                                    <span class="badge bg-success">مكتملة</span>
                                                @elseif($userTask && $userTask->status === 'expired')
                                                    <span class="badge bg-danger">منتهية</span>
                                                @else
                                                    <span class="badge bg-info">قيد التنفيذ</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fe fe-info"></i> لا توجد مهام يومية حالياً
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- المهام الأسبوعية -->
        <div class="row">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">المهام الأسبوعية</div>
                    </div>
                    <div class="card-body">
                        @if($weeklyTasks->count() > 0)
                            <div class="row">
                                @foreach($weeklyTasks as $task)
                                @php
                                    $userTask = $weeklyUserTasks->get($task->id);
                                    $progress = $userTask ? $userTask->progress : 0;
                                    $requiredCount = $task->criteria['count'] ?? 1;
                                    $percentage = min(($progress / $requiredCount) * 100, 100);
                                @endphp
                                <div class="col-md-6 mb-4">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white">
                                            <h5 class="mb-0">{{ $task->name }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <p>{{ $task->description }}</p>
                                            <div class="mb-2">
                                                <small class="text-muted">النوع: {{ $task->type_name }}</small>
                                                <br>
                                                <small class="text-muted">الفترة: {{ $task->start_day_name }} - {{ $task->end_day_name }}</small>
                                            </div>
                                            <div class="progress mb-2">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%">
                                                    {{ $progress }} / {{ $requiredCount }}
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-warning">مكافأة: {{ number_format($task->points_reward) }} نقطة</span>
                                                @if($userTask && $userTask->status === 'completed')
                                                    <span class="badge bg-success">مكتملة</span>
                                                @elseif($userTask && $userTask->status === 'expired')
                                                    <span class="badge bg-danger">منتهية</span>
                                                @else
                                                    <span class="badge bg-info">قيد التنفيذ</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fe fe-info"></i> لا توجد مهام أسبوعية حالياً
                            </div>
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

