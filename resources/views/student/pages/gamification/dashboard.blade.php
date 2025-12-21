@extends('student.layouts.master')

@section('page-title')
    لوحة التحفيز
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">لوحة التحفيز</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">لوحة التحفيز</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">لوحة التحفيز</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5>إجمالي النقاط</h5>
                                    <h2>{{ number_format($stats['total_points']) }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5>الشارات</h5>
                                    <h2>{{ $stats['badges_count'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5>الإنجازات</h5>
                                    <h2>{{ $stats['achievements_count'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5>المستوى الحالي</h5>
                                    <h2>{{ $stats['current_level'] ? $stats['current_level']->name : 'لا يوجد' }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($stats['current_level'])
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>تقدم المستوى</h5>
                                </div>
                                <div class="card-body">
                                    @php
                                        $progress = $stats['level_progress'];
                                    @endphp
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
                                    <p class="mt-2">
                                        {{ number_format($progress['current_points']) }} / {{ number_format($progress['points_required']) }} نقطة
                                        @if($progress['next_level'])
                                            للوصول إلى المستوى: {{ $progress['next_level']->name }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>آخر الشارات</h5>
                                </div>
                                <div class="card-body">
                                    @if($recentBadges->count() > 0)
                                        <div class="list-group">
                                            @foreach($recentBadges as $badge)
                                            <div class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <i class="fe fe-award text-primary me-3" style="font-size: 24px;"></i>
                                                    <div>
                                                        <h6 class="mb-0">{{ $badge->name }}</h6>
                                                        <small class="text-muted">{{ $badge->pivot->earned_at->diffForHumans() }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted">لا توجد شارات بعد</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>آخر الإنجازات</h5>
                                </div>
                                <div class="card-body">
                                    @if($recentAchievements->count() > 0)
                                        <div class="list-group">
                                            @foreach($recentAchievements as $achievement)
                                            <div class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <i class="fe fe-star text-warning me-3" style="font-size: 24px;"></i>
                                                    <div>
                                                        <h6 class="mb-0">{{ $achievement->name }}</h6>
                                                        <small class="text-muted">{{ $achievement->pivot->completed_at->diffForHumans() }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted">لا توجد إنجازات بعد</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<!-- End::app-content -->
@stop

