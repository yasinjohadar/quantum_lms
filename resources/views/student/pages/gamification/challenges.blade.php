@extends('student.layouts.master')

@section('page-title')
    التحديات
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">التحديات</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">التحديات</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">التحديات النشطة</div>
                    </div>
                    <div class="card-body">
                        @if($activeChallenges->count() > 0)
                            <div class="row">
                                @foreach($activeChallenges as $challenge)
                                @php
                                    $userChallenge = $challenge->userChallenges->first();
                                    $progress = $userChallenge ? $userChallenge->progress : 0;
                                @endphp
                                <div class="col-md-6 mb-4">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0">{{ $challenge->name }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <p>{{ $challenge->description }}</p>
                                            <div class="mb-3">
                                                <small class="text-muted">
                                                    من {{ $challenge->start_date->format('Y-m-d') }} إلى {{ $challenge->end_date->format('Y-m-d') }}
                                                </small>
                                            </div>
                                            <div class="progress mb-2">
                                                <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%">
                                                    {{ $progress }}%
                                                </div>
                                            </div>
                                            @if($userChallenge && $userChallenge->is_completed)
                                                <div class="alert alert-success mb-0">
                                                    <i class="fe fe-check"></i> مكتمل!
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fe fe-info"></i> لا توجد تحديات نشطة حالياً
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

