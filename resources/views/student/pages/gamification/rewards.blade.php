@extends('student.layouts.master')

@section('page-title')
    المكافآت
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">المكافآت</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">المكافآت</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3>نقاطك الحالية: {{ number_format($totalPoints) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">المكافآت المتاحة</div>
                    </div>
                    <div class="card-body">
                        @if($availableRewards->count() > 0)
                            <div class="row">
                                @foreach($availableRewards as $reward)
                                <div class="col-md-6 mb-4">
                                    <div class="card border-secondary">
                                        <div class="card-body">
                                            <h5>{{ $reward->name }}</h5>
                                            <p class="text-muted">{{ $reward->description }}</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-warning">تكلفة: {{ number_format($reward->points_cost) }} نقطة</span>
                                                @if($totalPoints >= $reward->points_cost)
                                                    <form action="{{ route('student.gamification.rewards.claim', $reward->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-primary">
                                                            <i class="fe fe-shopping-cart"></i> استبدال
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-danger">نقاط غير كافية</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fe fe-info"></i> لا توجد مكافآت متاحة حالياً
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">مكافآتي</div>
                    </div>
                    <div class="card-body">
                        @if($userRewards->count() > 0)
                            <div class="list-group">
                                @foreach($userRewards as $userReward)
                                <div class="list-group-item">
                                    <h6 class="mb-1">{{ $userReward->name }}</h6>
                                    <small class="text-muted">{{ $userReward->pivot->claimed_at->format('Y-m-d') }}</small>
                                    <span class="badge bg-{{ $userReward->pivot->status == 'approved' ? 'success' : 'warning' }} float-end">
                                        {{ $userReward->pivot->status_name }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">لا توجد مكافآت مستبدلة</p>
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

