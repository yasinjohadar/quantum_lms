@extends('student.layouts.master')

@section('page-title')
    الصفوف المشترك فيها
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">الصفوف المشترك فيها</h4>
            </div>
        </div>
        <!-- End Page Header -->

        {{-- مشتريات قيد المراجعة --}}
        @if(isset($pendingPurchases) && $pendingPurchases->count() > 0)
            <div class="card custom-card mb-4 border-warning">
                <div class="card-header bg-warning-transparent d-flex align-items-center">
                    <i class="bi bi-hourglass-split fs-5 me-2 text-warning"></i>
                    <h5 class="mb-0 text-warning">مشتريات قيد المراجعة</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($pendingPurchases as $purchase)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="d-flex align-items-center p-3 rounded bg-light">
                                    <i class="bi bi-{{ $purchase->purchase_type === 'class' ? 'building' : 'book' }} fs-4 text-warning me-3"></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $purchase->purchasable->name ?? '—' }}</div>
                                        <small class="text-muted">{{ $purchase->purchase_type === 'class' ? 'صف كامل' : 'مادة' }}</small>
                                    </div>
                                    <span class="badge bg-warning">قيد المراجعة</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if($classes->count() === 0)
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-building fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="mb-2">لا توجد صفوف مسجلة</h5>
                    <p class="text-muted">لم يتم تسجيلك في أي صف دراسي بعد</p>
                    <a href="{{ route('student.enrollments.index') }}" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-circle me-1"></i>
                        طلب الانضمام
                    </a>
                </div>
            </div>
        @else
            {{-- صف واحد أو أكثر: تبويبات --}}
            <div class="card custom-card">
                <div class="card-header border-bottom-0 pb-0">
                    <ul class="nav nav-tabs student-class-tabs card-header-tabs" role="tablist">
                        @foreach($classes as $classData)
                            @php
                                $class = $classData['class'];
                            @endphp
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                                   id="class-{{ $class->id }}-tab"
                                   data-bs-toggle="tab"
                                   data-bs-target="#class-{{ $class->id }}"
                                   href="#class-{{ $class->id }}"
                                   role="tab"
                                   aria-controls="class-{{ $class->id }}"
                                   aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    {{ $class->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="card-body pt-4">
                    <div class="tab-content" id="student-class-tab-content">
                        @foreach($classes as $classData)
                            @php
                                $class = $classData['class'];
                                $subjects = $classData['subjects'];
                            @endphp
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                 id="class-{{ $class->id }}"
                                 role="tabpanel"
                                 aria-labelledby="class-{{ $class->id }}-tab">
                                @include('student.pages.lessons.partials.class-section-content', [
                                    'class' => $class,
                                    'subjects' => $subjects,
                                ])
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
    <!-- Container closed -->
</div>
<!-- main-content closed -->
@stop
