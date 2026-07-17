@extends('admin.layouts.master')

@section('page-title')
    الاختبارات قيد المراجعة
@stop

@push('styles')
    @include('admin.pages.review-queue.partials.index-styles')
@endpush

@section('content')
    <div class="main-content app-content review-queue-page">
        <div class="container-fluid">

            <div class="rq-hero my-4">
                <div class="rq-hero__icon">
                    <i class="bi bi-clipboard-check-fill"></i>
                </div>
                <div class="rq-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.review-queue.index') }}">قائمة المراجعة</a></li>
                            <li class="breadcrumb-item active" aria-current="page">الاختبارات</li>
                        </ol>
                    </nav>
                    <h4 class="rq-hero__title">الاختبارات قيد المراجعة</h4>
                    <p class="rq-hero__subtitle">مراجعة الاختبارات المقدمة من المعلمين والموافقة على نشرها</p>
                </div>
                <div class="rq-hero__actions">
                    <a href="{{ route('admin.review-queue.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-right me-1"></i> العودة
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if(isset($stats))
                @include('admin.pages.review-queue.partials.stats-cards', ['stats' => $stats])
            @endif

            <div class="rq-card mb-4">
                <div class="rq-card__header">
                    <span><span class="rq-card__header-icon"><i class="bi bi-grid-3x3-gap"></i></span> التنقل</span>
                </div>
                <div class="rq-card__body pb-2">
                    @include('admin.pages.review-queue.partials.nav-tabs', [
                        'stats' => $stats ?? ['lessons' => ['pending' => 0], 'quizzes' => ['pending' => $quizzes->total()]],
                        'active' => 'quizzes',
                    ])
                </div>
            </div>

            @include('admin.pages.review-queue.partials.filters', [
                'filterAction' => route('admin.review-queue.quizzes'),
                'resetRoute' => route('admin.review-queue.quizzes'),
            ])

            @if($quizzes->count() > 0)
                <div class="rq-card">
                    <div class="rq-card__header">
                        <span><span class="rq-card__header-icon"><i class="bi bi-clipboard-check"></i></span> النتائج</span>
                        <span class="badge rounded-pill text-bg-light border">{{ $quizzes->total() }} اختبار</span>
                    </div>
                    <div class="rq-card__body">
                        @include('admin.pages.review-queue.partials.quizzes-table', [
                            'quizzes' => $quizzes,
                            'showStatus' => true,
                            'formId' => 'rq-quizzes-page-bulk-form',
                        ])
                        <div class="rq-pagination d-flex justify-content-center">
                            {{ $quizzes->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="rq-card">
                    <div class="rq-card__body">
                        <div class="rq-empty">
                            <i class="bi bi-check-circle-fill"></i>
                            <p class="mb-0 fw-semibold">لا توجد اختبارات قيد المراجعة حالياً</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop

@section('js')
    @include('admin.pages.review-queue.partials.bulk-approve-scripts')
@stop
