@extends('admin.layouts.master')

@section('page-title')
    لوحة التحكم - نظام التحفيز
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">لوحة التحكم - نظام التحفيز</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">نظام التحفيز</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">لوحة التحكم - نظام التحفيز</h3>
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
                                    <h5>الشارات النشطة</h5>
                                    <h2>{{ $stats['total_badges'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5>الإنجازات النشطة</h5>
                                    <h2>{{ $stats['total_achievements'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5>المستويات</h5>
                                    <h2>{{ $stats['total_levels'] }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>روابط سريعة</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <a href="{{ route('admin.gamification.settings') }}" class="btn btn-primary btn-block">
                                                <i class="fe fe-settings"></i> الإعدادات
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="{{ route('admin.badges.index') }}" class="btn btn-success btn-block">
                                                <i class="fe fe-award"></i> إدارة الشارات
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="{{ route('admin.achievements.index') }}" class="btn btn-info btn-block">
                                                <i class="fe fe-star"></i> إدارة الإنجازات
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="{{ route('admin.leaderboards.index') }}" class="btn btn-warning btn-block">
                                                <i class="fe fe-trending-up"></i> لوحة المتصدرين
                                            </a>
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
</div>
</div>
@stop

