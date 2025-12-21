@extends('student.layouts.master')

@section('page-title')
    لوحة المتصدرين
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">لوحة المتصدرين</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">لوحة المتصدرين</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">لوحة المتصدرين</h3>
                </div>
                <div class="card-body">
                    @if($leaderboard && $entries->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>الترتيب</th>
                                        <th>الطالب</th>
                                        <th>النقاط</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($entries as $entry)
                                    <tr class="{{ $entry->user_id === auth()->id() ? 'table-primary' : '' }}">
                                        <td>
                                            @if($entry->rank <= 3)
                                                <span class="badge bg-{{ $entry->rank == 1 ? 'warning' : ($entry->rank == 2 ? 'secondary' : 'danger') }}">
                                                    {{ $entry->rank }}
                                                </span>
                                            @else
                                                {{ $entry->rank }}
                                            @endif
                                        </td>
                                        <td>{{ $entry->user->name }}</td>
                                        <td>{{ number_format($entry->score) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($userRank)
                            <div class="alert alert-info mt-3">
                                <i class="fe fe-info"></i> ترتيبك: {{ $userRank }}
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            <i class="fe fe-info"></i> لا توجد بيانات في لوحة المتصدرين
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

