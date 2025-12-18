@extends('admin.layouts.master')

@section('page-title')
    بحاجة للتصحيح
@stop

@section('css')
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">محاولات بحاجة للتصحيح</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.index') }}">الاختبارات</a></li>
                            <li class="breadcrumb-item active" aria-current="page">بحاجة للتصحيح</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.quizzes.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i> العودة للاختبارات
                    </a>
                </div>
            </div>
            <!-- Page Header Close -->

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="card custom-card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-hourglass-split me-2"></i>
                        المحاولات قيد المراجعة ({{ $attempts->total() }})
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($attempts->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle display-4 text-success"></i>
                            <p class="text-muted mt-3">لا توجد محاولات بحاجة للتصحيح</p>
                            <p class="text-muted small">جميع المحاولات تم تصحيحها</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>الاختبار</th>
                                        <th>الطالب</th>
                                        <th>المادة</th>
                                        <th>تاريخ التقديم</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attempts as $attempt)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.quizzes.show', $attempt->quiz_id) }}" class="fw-semibold">
                                                    {{ Str::limit($attempt->quiz->title, 40) }}
                                                </a>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2 bg-primary-transparent text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                        {{ mb_substr($attempt->user->name ?? 'X', 0, 1) }}
                                                    </div>
                                                    {{ $attempt->user->name ?? 'محذوف' }}
                                                </div>
                                            </td>
                                            <td>{{ $attempt->quiz->subject->name ?? '-' }}</td>
                                            <td>{{ $attempt->finished_at?->format('Y/m/d H:i') ?? '-' }}</td>
                                            <td>
                                                <a href="{{ route('admin.quiz-attempts.grade', $attempt->id) }}" 
                                                   class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil-square me-1"></i> تصحيح
                                                </a>
                                                <a href="{{ route('admin.quiz-attempts.show', $attempt->id) }}" 
                                                   class="btn btn-sm btn-info-transparent">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            {{ $attempts->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
    <!-- End::app-content -->
@stop

@section('js')
@stop
