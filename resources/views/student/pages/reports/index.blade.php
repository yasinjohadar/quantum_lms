@extends('student.layouts.master')

@section('page-title')
    التقارير
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">التقارير</h4>
                <p class="mb-0 text-muted">عرض تقارير التقدم والإنجازات</p>
            </div>
        </div>
        <!-- End Page Header -->

        @if(isset($templates) && $templates->count() > 0)
            <div class="row">
                @foreach($templates as $template)
                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-primary text-white rounded d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                        <i class="bi bi-file-earmark-text fs-3"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1">{{ $template->name }}</h5>
                                        @if($template->is_default)
                                            <span class="badge bg-success">افتراضي</span>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($template->description)
                                    <p class="text-muted mb-3">{{ \Illuminate\Support\Str::limit($template->description, 100) }}</p>
                                @endif

                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        @if($template->type)
                                            <small class="text-muted">
                                                <i class="bi bi-tag me-1"></i>
                                                {{ \App\Models\ReportTemplate::TYPES[$template->type] ?? $template->type }}
                                            </small>
                                        @endif
                                    </div>
                                    <a href="{{ route('student.reports.show', $template->id) }}" 
                                       class="btn btn-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i>
                                        عرض التقرير
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-file-earmark-text fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="mb-2">لا توجد تقارير متاحة</h5>
                    <p class="text-muted">لا توجد قوالب تقارير متاحة للطلاب حالياً</p>
                </div>
            </div>
        @endif
    </div>
</div>
<!-- End::app-content -->
@stop

