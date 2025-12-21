@extends('admin.layouts.master')

@section('page-title')
    إدارة الشهادات
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إدارة الشهادات</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
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
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الطالب</th>
                                        <th>المادة</th>
                                        <th>النوع</th>
                                        <th>رقم الشهادة</th>
                                        <th>تاريخ الإصدار</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($certificates as $certificate)
                                    <tr>
                                        <td>{{ $certificate->id }}</td>
                                        <td>{{ $certificate->user->name }}</td>
                                        <td>{{ $certificate->subject->name ?? 'N/A' }}</td>
                                        <td><span class="badge bg-info">{{ $certificate->type_name }}</span></td>
                                        <td>{{ $certificate->certificate_number }}</td>
                                        <td>{{ $certificate->issued_at->format('Y-m-d') }}</td>
                                        <td>
                                            @if($certificate->pdf_path)
                                                <a href="{{ route('admin.certificates.download', $certificate) }}" class="btn btn-sm btn-primary">
                                                    <i class="fe fe-download"></i> تحميل
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">لا توجد شهادات</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $certificates->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->
@stop

