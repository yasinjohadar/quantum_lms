@extends('admin.layouts.master')

@section('page-title')
    إحصائيات سابقة للمعلم: {{ $teacher->name }}
@stop

@push('styles')
    @include('admin.pages.teachers.partials.progress-styles')
@endpush

@section('content')
    <div class="main-content app-content teachers-progress-page">
        <div class="container-fluid">

            <div class="tp-hero my-4">
                <div class="tp-hero__icon">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="tp-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.teachers.progress.index') }}">تقدم المعلمين</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.teachers.progress.show', $teacher) }}">{{ $teacher->name }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">إحصائيات سابقة</li>
                        </ol>
                    </nav>
                    <h4 class="tp-hero__title">إحصائيات سابقة — {{ $teacher->name }}</h4>
                    <p class="tp-hero__subtitle">الأسابيع المنتهية ضمن السنة الدراسية النشطة</p>
                </div>
                <div class="tp-hero__actions">
                    <a href="{{ route('admin.teachers.progress.show', $teacher) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i> التقدم الحالي
                    </a>
                    <a href="{{ route('admin.teachers.assignments', $teacher->id) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-sliders me-1"></i> تخصيص
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="tp-metric tp-metric--info">
                        <div class="tp-metric__head">
                            <div class="tp-metric__title">إجمالي الدروس المعتمدة</div>
                            <span class="tp-metric__icon"><i class="bi bi-check2-circle"></i></span>
                        </div>
                        <div class="tp-metric__value">{{ $total_approved_lessons }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="tp-metric tp-metric--primary">
                        <div class="tp-metric__head">
                            <div class="tp-metric__title">نسبة تقدم الصفحات (تراكمي)</div>
                            <span class="tp-metric__icon"><i class="bi bi-journal-text"></i></span>
                        </div>
                        @if($total_pages_required > 0)
                            <div class="d-flex align-items-baseline gap-2 flex-wrap">
                                <span class="tp-metric__value">{{ $total_pages_completed }}</span>
                                <span class="text-muted small">/ {{ $total_pages_required }}</span>
                            </div>
                            <div class="tp-progress mt-2">
                                <div class="tp-progress__bar tp-progress__bar--success" style="width: {{ min(100, $total_pages_percentage) }}%;"></div>
                            </div>
                            <div class="mt-2"><span class="tp-pct tp-pct--info">{{ number_format($total_pages_percentage, 1) }}%</span></div>
                        @else
                            <p class="mb-0 text-muted small">لا يوجد هدف صفحات</p>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="tp-metric tp-metric--warning">
                        <div class="tp-metric__head">
                            <div class="tp-metric__title">الهدف الأسبوعي (الحالي)</div>
                            <span class="tp-metric__icon"><i class="bi bi-calendar-week"></i></span>
                        </div>
                        @if(($weekly_progress['target'] ?? 0) > 0)
                            <div class="d-flex align-items-baseline gap-2 flex-wrap">
                                <span class="tp-metric__value">{{ $weekly_progress['completed'] ?? 0 }}</span>
                                <span class="text-muted small">/ {{ $weekly_progress['target'] }}</span>
                            </div>
                            @if(($weekly_progress['percentage'] ?? null) !== null)
                                @php $wp = $weekly_progress['percentage']; $wpc = $wp >= 100 ? 'success' : ($wp >= 50 ? 'info' : 'warning'); @endphp
                                <div class="mt-2"><span class="tp-pct tp-pct--{{ $wpc }}">{{ number_format($wp, 1) }}%</span></div>
                            @endif
                        @else
                            <p class="mb-0 text-muted small">لا يوجد هدف أسبوعي</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="tp-card mb-4">
                <div class="tp-card__header">
                    <div>
                        <span class="tp-card__header-icon"><i class="bi bi-calendar-week"></i></span>
                        الدروس الأسبوعية للأسابيع الماضية
                    </div>
                    <span class="small text-muted fw-normal">لا تظهر الأسابيع القادمة</span>
                </div>
                <div class="tp-card__body">
                    @if(!empty($pastWeeksProgress))
                        <div class="tp-table-wrap">
                            <table class="table tp-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 90px;">الأسبوع</th>
                                        <th>الفترة</th>
                                        <th class="text-center">المنجز</th>
                                        <th class="text-center">الهدف</th>
                                        <th class="text-center">النسبة</th>
                                        <th style="min-width: 120px;">التقدم</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pastWeeksProgress as $row)
                                        @php
                                            $w = $row['week'];
                                            $pct = $row['percentage'];
                                            $pctClass = $pct === null ? 'muted' : ($pct >= 100 ? 'success' : ($pct >= 50 ? 'info' : 'warning'));
                                            $barW = $pct !== null ? min(100, $pct) : 0;
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold">
                                                {{ $w->week_number }}
                                                @if($w->title)
                                                    <div class="small text-muted">{{ $w->title }}</div>
                                                @endif
                                            </td>
                                            <td class="text-muted small">
                                                {{ $w->start_date->format('Y-m-d') }} → {{ $w->end_date->format('Y-m-d') }}
                                            </td>
                                            <td class="text-center fw-semibold text-success">{{ $row['completed'] }}</td>
                                            <td class="text-center">{{ $row['target'] }}</td>
                                            <td class="text-center">
                                                @if($pct !== null)
                                                    <span class="tp-pct tp-pct--{{ $pctClass }}">{{ number_format($pct, 1) }}%</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($pct !== null)
                                                    <div class="tp-progress">
                                                        <div class="tp-progress__bar tp-progress__bar--{{ $pctClass === 'success' ? 'success' : ($pctClass === 'info' ? 'info' : 'warning') }}" style="width: {{ $barW }}%;"></div>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="tp-empty py-4">
                            <i class="bi bi-calendar-x"></i>
                            <p class="mb-0">لا توجد أسابيع منتهية ضمن السنة الدراسية النشطة حتى الآن.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="tp-card">
                <div class="tp-card__header">
                    <span class="tp-card__header-icon"><i class="bi bi-journal-bookmark"></i></span>
                    تقدم الصفحات حسب المادة (تراكمي)
                </div>
                <div class="tp-card__body">
                    @include('admin.pages.teachers.partials.progress-pages-table', [
                        'pagesProgress' => $pages_progress ?? [],
                        'showApprovedCol' => true,
                    ])
                </div>
            </div>

        </div>
    </div>
@stop
