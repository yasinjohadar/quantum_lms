@extends('student.layouts.master')

@section('page-title')
    الإنجازات
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">الإنجازات</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">الإنجازات</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">إنجازاتي</h3>
                </div>
                <div class="card-body">
                    @if($achievements->count() > 0)
                        <div class="row">
                            @foreach($achievements as $achievement)
                            @php
                                $userAchievement = $achievement->pivot;
                                $isCompleted = $userAchievement->completed_at !== null;
                                $progress = $userAchievement->progress ?? 0;
                            @endphp
                            <div class="col-md-4 mb-4">
                                <div class="card {{ $isCompleted ? 'border-success' : 'border-secondary' }}">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="fe fe-star {{ $isCompleted ? 'text-warning' : 'text-muted' }}" style="font-size: 32px;"></i>
                                            <div class="ms-3">
                                                <h5 class="mb-0">{{ $achievement->name }}</h5>
                                                <small class="text-muted">{{ $achievement->type_name }}</small>
                                            </div>
                                        </div>
                                        <p class="text-muted">{{ $achievement->description }}</p>
                                        @if($isCompleted)
                                            <div class="alert alert-success mb-0">
                                                <i class="fe fe-check"></i> مكتمل: {{ $userAchievement->completed_at->format('Y-m-d') }}
                                            </div>
                                        @else
                                            <div class="progress mb-2">
                                                <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%">
                                                    {{ $progress }}%
                                                </div>
                                            </div>
                                            <small class="text-muted">التقدم: {{ $progress }}%</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fe fe-info"></i> لا توجد إنجازات بعد
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<!-- End::app-content -->
@stop

