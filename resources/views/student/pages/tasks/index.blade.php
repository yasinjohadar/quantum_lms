@extends('student.layouts.master')

@section('page-title')
    مهامي
@stop

@push('styles')
    @include('student.pages.tasks.partials.tasks-page-styles')
@endpush

@section('content')
@php
    $dailyCompleted = $dailyTasks->filter(function ($task) use ($dailyUserTasks) {
        $ut = $dailyUserTasks->get($task->id);
        return $ut && $ut->status === 'completed';
    })->count();
    $weeklyCompleted = $weeklyTasks->filter(function ($task) use ($weeklyUserTasks) {
        $ut = $weeklyUserTasks->get($task->id);
        return $ut && $ut->status === 'completed';
    })->count();
    $totalTasks = $dailyTasks->count() + $weeklyTasks->count();
    $totalCompleted = $dailyCompleted + $weeklyCompleted;
    $pointsAvailable = $dailyTasks->sum('points_reward') + $weeklyTasks->sum('points_reward');
@endphp
<!-- Start::app-content -->
<div class="main-content app-content stask-page">
    <div class="container-fluid">
        <div class="stask-hero">
            <div class="stask-hero__main">
                <div class="stask-hero__icon" aria-hidden="true">
                    <i class="bi bi-list-check"></i>
                </div>
                <div class="min-w-0">
                    <h1 class="stask-hero__title">مهامي</h1>
                    <p class="stask-hero__meta">أنجز المهام اليومية والأسبوعية واجمع النقاط</p>
                </div>
            </div>
            <div class="stask-stats">
                <div class="stask-stat">
                    <span class="stask-stat__value">{{ $totalCompleted }}/{{ $totalTasks }}</span>
                    <span class="stask-stat__label">مكتمل</span>
                </div>
                <div class="stask-stat">
                    <span class="stask-stat__value">{{ $dailyCompleted }}/{{ $dailyTasks->count() }}</span>
                    <span class="stask-stat__label">يومية</span>
                </div>
                <div class="stask-stat">
                    <span class="stask-stat__value">{{ number_format($pointsAvailable) }}</span>
                    <span class="stask-stat__label">نقاط متاحة</span>
                </div>
            </div>
        </div>

        @include('partials.gamification-help-box', ['helpKey' => 'student.tasks'])

        <section class="stask-section stask-section--daily">
            <div class="stask-section__head">
                <h2 class="stask-section__title">
                    <span class="stask-section__title-icon" aria-hidden="true">
                        <i class="bi bi-sun"></i>
                    </span>
                    المهام اليومية
                </h2>
                <span class="stask-section__count">{{ $dailyCompleted }} / {{ $dailyTasks->count() }} مكتمل</span>
            </div>

            @if($dailyTasks->count() > 0)
                <div class="stask-grid">
                    @foreach($dailyTasks as $task)
                        @include('student.pages.tasks.partials.task-card', [
                            'task' => $task,
                            'userTask' => $dailyUserTasks->get($task->id),
                            'showPeriod' => false,
                        ])
                    @endforeach
                </div>
            @else
                <div class="stask-empty">
                    <div class="stask-empty__icon" aria-hidden="true">
                        <i class="bi bi-sun"></i>
                    </div>
                    <h5 class="fw-bold mb-2">لا توجد مهام يومية حالياً</h5>
                    <p class="text-muted mb-0">ستظهر هنا المهام اليومية عند تفعيلها</p>
                </div>
            @endif
        </section>

        <section class="stask-section stask-section--weekly">
            <div class="stask-section__head">
                <h2 class="stask-section__title">
                    <span class="stask-section__title-icon" aria-hidden="true">
                        <i class="bi bi-calendar-week"></i>
                    </span>
                    المهام الأسبوعية
                </h2>
                <span class="stask-section__count">{{ $weeklyCompleted }} / {{ $weeklyTasks->count() }} مكتمل</span>
            </div>

            @if($weeklyTasks->count() > 0)
                <div class="stask-grid">
                    @foreach($weeklyTasks as $task)
                        @include('student.pages.tasks.partials.task-card', [
                            'task' => $task,
                            'userTask' => $weeklyUserTasks->get($task->id),
                            'showPeriod' => true,
                        ])
                    @endforeach
                </div>
            @else
                <div class="stask-empty">
                    <div class="stask-empty__icon" aria-hidden="true">
                        <i class="bi bi-calendar-week"></i>
                    </div>
                    <h5 class="fw-bold mb-2">لا توجد مهام أسبوعية حالياً</h5>
                    <p class="text-muted mb-0">ستظهر هنا المهام الأسبوعية عند تفعيلها</p>
                </div>
            @endif
        </section>
    </div>
</div>
<!-- End::app-content -->
@stop
