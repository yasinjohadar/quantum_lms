@extends('admin.layouts.master')

@section('page-title')
    تفاصيل تقدم المعلم: {{ $teacher->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تفاصيل تقدم المعلم: {{ $teacher->name }}</h5>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.teachers.progress.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i> تقدم المعلمين
                    </a>
                    <a href="{{ route('admin.teachers.assignments.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-list me-1"></i> قائمة المعلمين
                    </a>
                    <a href="{{ route('admin.teachers.assignments', $teacher->id) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-tie me-1"></i> تخصيص
                    </a>
                </div>
            </div>

            {{-- بطاقات الملخص --}}
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6 class="text-white-50 mb-2">إجمالي الدروس المعتمدة</h6>
                            <h3 class="mb-0">{{ $total_approved_lessons }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="text-white-50 mb-2">نسبة تقدم الصفحات</h6>
                            @if($total_pages_required > 0)
                                <h3 class="mb-1">{{ $total_pages_completed }} / {{ $total_pages_required }}</h3>
                                <span class="badge bg-light text-dark">{{ number_format($total_pages_percentage, 1) }}%</span>
                            @else
                                <p class="mb-0 text-white-50">— لا يوجد هدف صفحات</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <h6 class="mb-2">النسبة الأسبوعية للدروس</h6>
                            @if($weekly_progress['target'] > 0)
                                <h3 class="mb-1">{{ $weekly_progress['completed'] }} / {{ $weekly_progress['target'] }}</h3>
                                @if($weekly_progress['percentage'] !== null)
                                    <span class="badge {{ $weekly_progress['percentage'] >= 100 ? 'bg-success' : ($weekly_progress['percentage'] >= 50 ? 'bg-info' : 'bg-secondary') }}">{{ number_format($weekly_progress['percentage'], 1) }}%</span>
                                @endif
                            @else
                                <p class="mb-0">— لا يوجد هدف أسبوعي</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- جدول تقدم الصفحات حسب المادة --}}
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-journal-bookmark me-2"></i>
                        تقدم الصفحات حسب المادة
                    </h6>
                </div>
                <div class="card-body">
                    @if(!empty($pages_progress))
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>المادة</th>
                                        <th>عدد الدروس المعتمدة</th>
                                        <th>الصفحات المطلوبة</th>
                                        <th>المنجز</th>
                                        <th>المتبقي</th>
                                        <th>النسبة %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pages_progress as $row)
                                        <tr>
                                            <td>{{ $row['subject']->name }}</td>
                                            <td>{{ $row['approved_lessons_count'] ?? 0 }}</td>
                                            <td>{{ $row['required_pages'] }}</td>
                                            <td>{{ $row['completed_pages'] }}</td>
                                            <td>{{ $row['remaining_pages'] }}</td>
                                            <td>
                                                @if($row['percentage'] !== null)
                                                    <span class="badge {{ $row['percentage'] >= 100 ? 'bg-success' : ($row['percentage'] >= 50 ? 'bg-info' : 'bg-warning text-dark') }}">
                                                        {{ number_format($row['percentage'], 1) }}%
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
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
