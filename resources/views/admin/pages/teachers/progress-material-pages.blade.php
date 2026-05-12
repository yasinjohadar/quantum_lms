@extends('admin.layouts.master')

@section('page-title')
    صفحات المواد الموكّلة — {{ $teacher->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">الصفحات الموكّلة والإنجاز — {{ $teacher->name }}</h5>
                    <p class="text-muted small mb-0">لكل مادة مخصّصة: الهدف (من التخصيص) مقابل صفحات الدروس المعتمدة المحسوبة من نطاق الصفحات في الدرس.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.teachers.progress.show', $teacher) }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i> صفحة التقدم الكاملة
                    </a>
                    <a href="{{ route('admin.teachers.assignments', $teacher->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-user-tie me-1"></i> تعديل التخصيص
                    </a>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-muted small mb-1">إجمالي الصفحات الموكّلة</h6>
                            <h3 class="mb-0">{{ (int) ($total_pages_required ?? 0) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 border-success border-opacity-50">
                        <div class="card-body">
                            <h6 class="text-muted small mb-1">الصفحات المنجزة (معتمدة)</h6>
                            <h3 class="mb-0 text-success">{{ (int) ($total_pages_completed ?? 0) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-muted small mb-1">نسبة الإنجاز</h6>
                            @if(($total_pages_required ?? 0) > 0 && $total_pages_percentage !== null)
                                <h3 class="mb-2">{{ number_format($total_pages_percentage, 1) }}%</h3>
                                <div class="progress" style="height: 12px;">
                                    <div class="progress-bar bg-success" style="width: {{ min(100, $total_pages_percentage) }}%;"></div>
                                </div>
                            @else
                                <p class="text-muted mb-0 small">لا يوجد هدف صفحات موكّل أو لا توجد مواد.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-table me-2"></i>جدول المواد</h6>
                </div>
                <div class="card-body">
                    @if(!empty($pages_progress))
                        <div class="table-responsive teacher-weeks-table-wrap">
                            <table class="table teacher-weeks-targets-table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>المادة</th>
                                        <th>الصف</th>
                                        <th class="text-center">الموكّل<br><span class="small fw-normal text-muted">صفحات</span></th>
                                        <th class="text-center">المنجز<br><span class="small fw-normal text-muted">صفحات</span></th>
                                        <th class="text-center">المتبقي</th>
                                        <th class="text-center">الدروس المعتمدة<br><span class="small fw-normal text-muted">في المادة</span></th>
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
                                        @endphp
                                        <tr @class(['bg-light' => $req <= 0])>
                                            <td class="fw-semibold">{{ $subj->name }}</td>
                                            <td class="small text-muted">{{ $subj->schoolClass?->name ?? '—' }}</td>
                                            <td class="text-center fw-semibold">{{ $req }}</td>
                                            <td class="text-center text-success fw-semibold">{{ $done }}</td>
                                            <td class="text-center">{{ $row['remaining_pages'] }}</td>
                                            <td class="text-center">{{ $row['approved_lessons_count'] ?? 0 }}</td>
                                            <td>
                                                @if($pct !== null)
                                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                        <span class="badge {{ $pct >= 100 ? 'bg-success' : ($pct >= 50 ? 'bg-info text-dark' : 'bg-warning text-dark') }}">{{ number_format($pct, 1) }}%</span>
                                                    </div>
                                                    <div class="progress" style="height: 10px;">
                                                        <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $bar }}%;"></div>
                                                    </div>
                                                @else
                                                    <span class="text-muted small">لا هدف صفحات موكّل</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">لا توجد مواد مخصصة لهذا المعلم.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
@stop

@push('styles')
    <style>
        .teacher-weeks-table-wrap {
            padding: 0.25rem 0.15rem 0.5rem;
            background: rgba(0, 0, 0, 0.02);
            border-radius: 0.5rem;
        }

        .teacher-weeks-table-wrap table.teacher-weeks-targets-table {
            border-collapse: separate;
            border-spacing: 0 0.65rem;
            width: 100%;
        }

        .teacher-weeks-table-wrap table.teacher-weeks-targets-table thead th {
            border: none;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
            background-color: var(--bs-table-bg, #f8f9fa);
            padding-bottom: 0.6rem;
            vertical-align: middle;
        }

        .teacher-weeks-table-wrap table.teacher-weeks-targets-table tbody td {
            border-block: 1px solid rgba(0, 0, 0, 0.1);
            border-inline-start: none;
            border-inline-end: 1px solid rgba(0, 0, 0, 0.07);
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
            vertical-align: middle;
        }

        .teacher-weeks-table-wrap table.teacher-weeks-targets-table tbody tr td:first-child {
            border-inline-start: 1px solid rgba(0, 0, 0, 0.1);
            border-start-start-radius: 0.45rem;
            border-end-start-radius: 0.45rem;
        }

        .teacher-weeks-table-wrap table.teacher-weeks-targets-table tbody tr td:last-child {
            border-start-end-radius: 0.45rem;
            border-end-end-radius: 0.45rem;
        }
    </style>
@endpush
