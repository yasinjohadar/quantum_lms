@extends('student.layouts.master')

@section('page-title')
    الشارات
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">الشارات</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">الشارات</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">شاراتي</h3>
                </div>
                <div class="card-body">
                    @if($userBadges->count() > 0)
                        <div class="row">
                            @foreach($userBadges as $badge)
                            <div class="col-md-3 mb-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <i class="fe fe-award" style="font-size: 48px; color: {{ $badge->color ?? '#007bff' }};"></i>
                                        <h5 class="mt-3">{{ $badge->name }}</h5>
                                        <p class="text-muted">{{ $badge->description }}</p>
                                        <small class="text-muted">حصلت عليها: {{ $badge->pivot->earned_at ? \Carbon\Carbon::parse($badge->pivot->earned_at)->format('Y-m-d') : '-' }}</small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fe fe-info"></i> لا توجد شارات بعد
                        </div>
                    @endif
                </div>
            </div>

            @if($availableBadges->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">الشارات المتاحة</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($availableBadges as $badge)
                        <div class="col-md-3 mb-4">
                            <div class="card text-center border-secondary">
                                <div class="card-body">
                                    <i class="fe fe-award" style="font-size: 48px; color: #ccc;"></i>
                                    <h5 class="mt-3">{{ $badge->name }}</h5>
                                    <p class="text-muted">{{ $badge->description }}</p>
                                    @if($badge->points_required > 0)
                                        <small class="text-muted">مطلوب: {{ $badge->points_required }} نقطة</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
</div>
<!-- End::app-content -->
@stop

