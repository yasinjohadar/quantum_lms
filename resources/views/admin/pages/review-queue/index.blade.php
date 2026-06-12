@extends('admin.layouts.master')

@section('page-title')
    قائمة المراجعة
@stop

@push('styles')
    @include('admin.pages.review-queue.partials.index-styles')
@endpush

@section('content')
    <div class="main-content app-content review-queue-page">
        <div class="container-fluid">

            <div class="rq-hero my-4">
                <div class="rq-hero__icon">
                    <i class="bi bi-clipboard2-check-fill"></i>
                </div>
                <div class="rq-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">قائمة المراجعة</li>
                        </ol>
                    </nav>
                    <h4 class="rq-hero__title">قائمة المراجعة</h4>
                    <p class="rq-hero__subtitle">مراجعة الدروس والاختبارات المقدمة من المعلمين والموافقة عليها</p>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @include('admin.pages.review-queue.partials.stats-cards', ['stats' => $stats])

            <div class="rq-card mb-4">
                <div class="rq-card__header">
                    <span><span class="rq-card__header-icon"><i class="bi bi-grid-3x3-gap"></i></span> التنقل</span>
                </div>
                <div class="rq-card__body pb-2">
                    @include('admin.pages.review-queue.partials.nav-tabs', ['stats' => $stats, 'active' => 'all'])
                </div>
            </div>

            <div class="rq-card">
                <div class="rq-card__body">
                    @if($lessons->count() > 0)
                        <div class="mb-4">
                            <div class="rq-section-title">
                                <i class="bi bi-play-btn"></i>
                                الدروس قيد المراجعة
                                <span class="badge rounded-pill text-bg-warning">{{ $lessons->total() }}</span>
                            </div>
                            @include('admin.pages.review-queue.partials.lessons-table', ['lessons' => $lessons])
                            <div class="rq-pagination d-flex justify-content-center">
                                {{ $lessons->links() }}
                            </div>
                        </div>
                    @endif

                    @if($quizzes->count() > 0)
                        <div class="mb-2">
                            <div class="rq-section-title">
                                <i class="bi bi-clipboard-check"></i>
                                الاختبارات قيد المراجعة
                                <span class="badge rounded-pill text-bg-info">{{ $quizzes->total() }}</span>
                            </div>
                            @include('admin.pages.review-queue.partials.quizzes-table', ['quizzes' => $quizzes])
                            <div class="rq-pagination d-flex justify-content-center">
                                {{ $quizzes->links() }}
                            </div>
                        </div>
                    @endif

                    @if($lessons->count() === 0 && $quizzes->count() === 0)
                        <div class="rq-empty">
                            <i class="bi bi-check-circle-fill"></i>
                            <p class="mb-0 fw-semibold">لا توجد عناصر قيد المراجعة حالياً</p>
                            <p class="small mb-0 mt-1">ستظهر هنا الدروس والاختبارات عند إرسالها من المعلمين.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
