@extends('student.layouts.master')

@section('page-title')
    الشهادات
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">شهاداتي</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">الشهادات</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">قائمة الشهادات</div>
                    </div>
                    <div class="card-body">
                        @if($certificates->count() > 0)
                            <div class="row">
                                @foreach($certificates as $certificate)
                                <div class="col-md-4 mb-4">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <i class="fe fe-award" style="font-size: 64px; color: #28a745;"></i>
                                            <h5 class="mt-3">{{ $certificate->type_name }}</h5>
                                            @if($certificate->subject)
                                                <p class="text-muted">{{ $certificate->subject->name }}</p>
                                            @endif
                                            <p class="text-muted small">رقم الشهادة: {{ $certificate->certificate_number }}</p>
                                            <p class="text-muted small">تاريخ الإصدار: {{ $certificate->issued_at->format('Y-m-d') }}</p>
                                            @if($certificate->pdf_path)
                                                <a href="{{ route('student.gamification.certificates.download', $certificate->id) }}" class="btn btn-primary btn-sm mt-2">
                                                    <i class="fe fe-download"></i> تحميل PDF
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fe fe-info"></i> لا توجد شهادات بعد
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

