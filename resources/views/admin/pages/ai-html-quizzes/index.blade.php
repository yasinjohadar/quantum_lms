@extends('admin.layouts.master')

@section('page-title')
    اختبارات HTML بالذكاء الاصطناعي
@stop

@push('styles')
    @include('admin.pages.learning-experiences.partials.index-styles')
@endpush

@php
    $statusLabels = [
        'draft' => 'مسودة',
        'review' => 'مراجعة',
        'published' => 'منشور',
        'archived' => 'مؤرشف',
    ];
    $statusBadge = [
        'draft' => 'ile-badge--draft',
        'review' => 'ile-badge--review',
        'published' => 'ile-badge--published',
        'archived' => 'ile-badge--archived',
    ];
@endphp

@section('content')
<div class="main-content app-content ile-index-page">
    <div class="container-fluid">

        <div class="ile-index-hero my-4">
            <div class="ile-index-hero__icon">
                <i class="bi bi-filetype-html"></i>
            </div>
            <div class="ile-index-hero__content">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2 small">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">اختبارات HTML بالذكاء الاصطناعي</li>
                    </ol>
                </nav>
                <h4 class="ile-index-hero__title">اختبارات HTML مولَّدة بالذكاء الاصطناعي</h4>
                <p class="ile-index-hero__subtitle">صفحات تفاعلية مستقلة (HTML/CSS/JS) — توليد → مراجعة → نشر داخل sandbox</p>
            </div>
            <div class="ile-index-stats">
                <div class="ile-index-stat-mini">
                    <span class="ile-index-stat-mini__value">{{ number_format($stats['total'] ?? $quizzes->total()) }}</span>
                    <span class="ile-index-stat-mini__label">الكل</span>
                </div>
                <div class="ile-index-stat-mini">
                    <span class="ile-index-stat-mini__value">{{ number_format($stats['published'] ?? 0) }}</span>
                    <span class="ile-index-stat-mini__label">منشور</span>
                </div>
                <div class="ile-index-stat-mini">
                    <span class="ile-index-stat-mini__value">{{ number_format($stats['draft'] ?? 0) }}</span>
                    <span class="ile-index-stat-mini__label">مسودة</span>
                </div>
            </div>
            <div class="ile-index-hero__actions">
                <a href="{{ route('admin.ai-html-quizzes.create') }}" class="btn btn-sm btn-success">
                    <i class="bi bi-plus-circle me-1"></i> اختبار جديد
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="ile-index-card">
            <div class="ile-index-card__header">
                <div class="d-flex align-items-center gap-2">
                    <span class="ile-index-card__header-icon"><i class="bi bi-funnel"></i></span>
                    <span>تصفية وبحث</span>
                </div>
            </div>
            <div class="ile-index-card__body">
                <form method="GET" class="ile-index-filters">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-6 col-lg-5">
                            <label class="form-label">بحث</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="search" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="ابحث بعنوان الاختبار...">
                            </div>
                        </div>
                        <div class="col-8 col-md-4 col-lg-3">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select">
                                <option value="">كل الحالات</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" @selected(request('status') === $status)>
                                        {{ $statusLabels[$status] ?? $status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4 col-md-2 col-lg-2 d-flex gap-2">
                            <button type="submit" class="btn btn-success flex-fill" title="تصفية">
                                <i class="bi bi-search"></i>
                            </button>
                            <a href="{{ route('admin.ai-html-quizzes.index') }}" class="btn btn-outline-secondary" title="مسح">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="ile-index-card ile-index-card--flush">
            <div class="ile-index-card__header">
                <div class="d-flex align-items-center gap-2">
                    <span class="ile-index-card__header-icon"><i class="bi bi-code-slash"></i></span>
                    <span>الاختبارات ({{ number_format($quizzes->total()) }})</span>
                </div>
            </div>
            <div class="ile-index-card__body">
                <div class="ile-index-table-wrap">
                    <table class="table ile-index-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 56px;">#</th>
                                <th>الاختبار</th>
                                <th>الحالة</th>
                                <th>الحزمة</th>
                                <th style="width: 240px;" class="text-end">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($quizzes as $quiz)
                                <tr>
                                    <td class="text-muted fw-semibold">{{ $quiz->id }}</td>
                                    <td>
                                        <div class="ile-exp-cell">
                                            <div class="ile-exp-thumb">
                                                <i class="bi bi-filetype-html"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="ile-exp-title">{{ $quiz->title }}</div>
                                                @if($quiz->description)
                                                    <p class="ile-exp-desc">{{ \Illuminate\Support\Str::limit($quiz->description, 90) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="ile-badge {{ $statusBadge[$quiz->status] ?? 'ile-badge--draft' }}">
                                            {{ $statusLabels[$quiz->status] ?? $quiz->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($quiz->hasBundle())
                                            <span class="ile-meta-pill"><i class="bi bi-check2-circle"></i> جاهزة</span>
                                        @else
                                            <span class="ile-meta-pill text-muted"><i class="bi bi-hourglass"></i> بانتظار التوليد</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="ile-row-actions">
                                            @if($quiz->hasBundle())
                                                <a href="{{ route('ai-html-quizzes.show', $quiz) }}" class="btn btn-sm btn-outline-success" target="_blank" title="تشغيل">
                                                    <i class="bi bi-play-fill"></i> تشغيل
                                                </a>
                                            @endif
                                            <a href="{{ route('admin.ai-html-quizzes.edit', $quiz) }}" class="btn btn-sm btn-outline-primary" title="تحرير">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.ai-html-quizzes.destroy', $quiz) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف الاختبار؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" title="حذف" type="submit">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="ile-empty">
                                            <div class="ile-empty__icon"><i class="bi bi-filetype-html"></i></div>
                                            <div class="fw-bold mb-1">لا توجد اختبارات بعد</div>
                                            <p class="mb-3 small">أنشئ اختباراً وولّد صفحة HTML تفاعلية بالذكاء الاصطناعي.</p>
                                            <a href="{{ route('admin.ai-html-quizzes.create') }}" class="btn btn-sm btn-success">
                                                <i class="bi bi-plus-circle me-1"></i> اختبار جديد
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($quizzes->hasPages())
                    <div class="ile-index-pagination">
                        {{ $quizzes->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
