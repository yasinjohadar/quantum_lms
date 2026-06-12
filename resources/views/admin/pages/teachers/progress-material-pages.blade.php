@extends('admin.layouts.master')

@section('page-title')
    صفحات المواد الموكّلة — {{ $teacher->name }}
@stop

@push('styles')
    @include('admin.pages.teachers.partials.progress-styles')
@endpush

@section('content')
    <div class="main-content app-content teachers-progress-page">
        <div class="container-fluid">

            <div class="tp-hero my-4">
                <div class="tp-hero__icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div class="tp-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.teachers.progress.index') }}">تقدم المعلمين</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.teachers.progress.show', $teacher) }}">{{ $teacher->name }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">صفحات المواد</li>
                        </ol>
                    </nav>
                    <h4 class="tp-hero__title">الصفحات الموكّلة والإنجاز</h4>
                    <p class="tp-hero__subtitle">{{ $teacher->name }} — الهدف مقابل صفحات الدروس المعتمدة</p>
                </div>
                <div class="tp-hero__actions">
                    <a href="{{ route('admin.teachers.progress.show', $teacher) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i> صفحة التقدم الكاملة
                    </a>
                    <a href="{{ route('admin.teachers.assignments', $teacher->id) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-sliders me-1"></i> تعديل التخصيص
                    </a>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="tp-metric tp-metric--info">
                        <div class="tp-metric__head">
                            <div class="tp-metric__title">إجمالي الصفحات الموكّلة</div>
                            <span class="tp-metric__icon"><i class="bi bi-book"></i></span>
                        </div>
                        <div class="tp-metric__value" style="color: var(--tp-accent-2);">{{ (int) ($total_pages_required ?? 0) }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="tp-metric tp-metric--primary">
                        <div class="tp-metric__head">
                            <div class="tp-metric__title">الصفحات المنجزة</div>
                            <span class="tp-metric__icon"><i class="bi bi-check2-all"></i></span>
                        </div>
                        <div class="tp-metric__value">{{ (int) ($total_pages_completed ?? 0) }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="tp-metric tp-metric--warning">
                        <div class="tp-metric__head">
                            <div class="tp-metric__title">نسبة الإنجاز</div>
                            <span class="tp-metric__icon"><i class="bi bi-percent"></i></span>
                        </div>
                        @if(($total_pages_required ?? 0) > 0 && $total_pages_percentage !== null)
                            <div class="tp-metric__value" style="color: var(--tp-warning);">{{ number_format($total_pages_percentage, 1) }}%</div>
                            <div class="tp-progress mt-2">
                                <div class="tp-progress__bar tp-progress__bar--success" style="width: {{ min(100, $total_pages_percentage) }}%;"></div>
                            </div>
                        @else
                            <p class="mb-0 text-muted small">لا يوجد هدف صفحات موكّل.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="tp-card">
                <div class="tp-card__header">
                    <span class="tp-card__header-icon"><i class="bi bi-table"></i></span>
                    جدول المواد
                </div>
                <div class="tp-card__body">
                    @if(!empty($pages_progress))
                        <div class="teacher-weeks-table-wrap">
                            <table class="table teacher-weeks-targets-table tp-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>المادة</th>
                                        <th>الصف</th>
                                        <th class="text-center">الموكّل</th>
                                        <th class="text-center">المنجز</th>
                                        <th class="text-center">المتبقي</th>
                                        <th class="text-center">دروس معتمدة</th>
                                        <th style="min-width: 160px;">التقدم</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pages_progress as $row)
                                        @php
                                            $subj = $row['subject'];
                                            $req = (int) ($row['required_pages'] ?? 0);
                                            $done = (int) ($row['completed_pages'] ?? 0);
                                            $pct = $row['percentage'];
                                            $bar = $pct !== null ? min(100, $pct) : 0;
                                            $pctClass = $pct === null ? 'muted' : ($pct >= 100 ? 'success' : ($pct >= 50 ? 'info' : 'warning'));
                                        @endphp
                                        <tr @class(['opacity-75' => $req <= 0])>
                                            <td class="fw-semibold">{{ $subj->name }}</td>
                                            <td>
                                                @if($subj->schoolClass)
                                                    <span class="tp-chip tp-chip--class">{{ $subj->schoolClass->name }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center fw-semibold">{{ $req }}</td>
                                            <td class="text-center fw-semibold text-success">{{ $done }}</td>
                                            <td class="text-center">{{ $row['remaining_pages'] }}</td>
                                            <td class="text-center">{{ $row['approved_lessons_count'] ?? 0 }}</td>
                                            <td>
                                                @if($pct !== null)
                                                    <span class="tp-pct tp-pct--{{ $pctClass }}">{{ number_format($pct, 1) }}%</span>
                                                    <div class="tp-progress mt-1">
                                                        <div class="tp-progress__bar tp-progress__bar--{{ $pctClass === 'success' ? 'success' : ($pctClass === 'info' ? 'info' : 'warning') }}" style="width: {{ $bar }}%;"></div>
                                                    </div>
                                                @else
                                                    <span class="text-muted small">لا هدف موكّل</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="tp-empty py-4">
                            <i class="bi bi-journal-x"></i>
                            <p class="mb-0">لا توجد مواد مخصصة لهذا المعلم.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@stop
