@extends('student.layouts.master')

@section('page-title')
    تقاريري
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">تقاريري</h4>
                <p class="mb-0 text-muted">عرض تقارير تقدمك الدراسي</p>
            </div>
        </div>
        <!-- End Page Header -->

        @if($templates->count() > 0)
            <div class="row">
                @foreach($templates as $template)
                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-3">
                        <div class="card custom-card h-100">
                            <div class="card-body">
                                <h6 class="card-title fw-semibold">{{ $template->name }}</h6>
                                @if($template->description)
                                    <p class="card-text text-muted small mb-3">{{ \Illuminate\Support\Str::limit($template->description, 100) }}</p>
                                @endif
                                <a href="{{ route('student.reports.show', $template->id) }}" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-eye me-1"></i>
                                    عرض التقرير
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card custom-card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-file-earmark-text fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="mb-2">لا توجد تقارير متاحة</h5>
                    <p class="text-muted">لم يتم إنشاء أي تقارير للطلاب بعد</p>
                </div>
            </div>
        @endif
    </div>
    <!-- Container closed -->
</div>
<!-- main-content closed -->
@stop

