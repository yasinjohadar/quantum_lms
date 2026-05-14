@extends('admin.layouts.master')

@section('page-title')
    تقدم المعلمين / أهداف المعلمين
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تقدم المعلمين (أهداف الصفحات والدروس الأسبوعية)</h5>
                </div>
                <div>
                    <a href="{{ route('admin.teachers.assignments.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i> تخصيص المعلمين
                    </a>
                </div>
            </div>

            @if(isset($activeWeeks) && $activeWeeks->isNotEmpty())
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body py-2">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <label class="form-label mb-0">عرض إحصائيات الأسبوع:</label>
                            <form method="GET" action="{{ route('admin.teachers.progress.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
                                <select name="week_id" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                    <option value="">الأسبوع الحالي</option>
                                    @foreach($activeWeeks as $w)
                                        <option value="{{ $w->id }}" {{ request('week_id') == $w->id ? 'selected' : '' }}>{{ $w->title ?? 'الأسبوع ' . $w->week_number }} ({{ $w->start_date->format('Y-m-d') }} → {{ $w->end_date->format('Y-m-d') }})</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-sm btn-secondary">عرض</button>
                            </form>
                            @if(isset($currentWeek) && $currentWeek)
                                <span class="small text-muted">الأسبوع المعروض: {{ $currentWeek->title ?? 'الأسبوع ' . $currentWeek->week_number }} ({{ $currentWeek->start_date->format('Y-m-d') }} - {{ $currentWeek->end_date->format('Y-m-d') }})</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if(empty($progress))
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">لا يوجد معلمون لعرض التقدم.</p>
                    </div>
                </div>
            @else
                <div class="row">
                    @foreach($progress as $item)
                        @php
                            $teacher = $item['teacher'];
                            $pagesProgress = $item['pages_progress'];
                            $weekly = $item['weekly_progress'];
                        @endphp
                        <div class="col-xl-12 mb-4">
                            <div class="card shadow-sm border-0">
                                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                                    <h6 class="mb-0 fw-bold">
                                        <i class="bi bi-person-badge me-2"></i>
                                        <a href="{{ route('admin.teachers.progress.show', $teacher->id) }}" class="text-decoration-none">{{ $teacher->name }}</a>
                                    </h6>
                                    <a href="{{ route('admin.teachers.assignments', $teacher->id) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-user-tie me-1"></i> تخصيص
                                    </a>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        {{-- الصفحات (لكل مادة) --}}
                                        <div class="col-lg-7 mb-3 mb-lg-0">
                                            <h6 class="text-muted mb-2">
                                                <i class="bi bi-journal-bookmark me-1"></i>
                                                تقدم الصفحات حسب المادة
                                            </h6>
                                            @if(!empty($pagesProgress))
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>المادة</th>
                                                                <th>الصف</th>
                                                                <th>المطلوب</th>
                                                                <th>المنجز</th>
                                                                <th>المتبقي</th>
                                                                <th>النسبة %</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($pagesProgress as $row)
                                                                <tr>
                                                                    <td class="fw-semibold">{{ $row['subject']->name }}</td>
                                                                    <td class="small text-muted">{{ $row['subject']->schoolClass?->name ?? '—' }}</td>
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
                                                <p class="text-muted small mb-0">لا توجد مواد مخصصة أو أهداف صفحات.</p>
                                            @endif
                                        </div>
                                        {{-- الدروس الأسبوعية --}}
                                        <div class="col-lg-5">
                                            <h6 class="text-muted mb-2">
                                                <i class="bi bi-calendar-week me-1"></i>
                                                الدروس الأسبوعية
                                                @if(isset($weekly['current_week']) && $weekly['current_week'])
                                                    <span class="small fw-normal">({{ $weekly['current_week']->title ?? 'أسبوع ' . $weekly['current_week']->week_number }})</span>
                                                @endif
                                            </h6>
                                            <div class="border rounded p-3 bg-light">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span>الهدف الأسبوعي:</span>
                                                    <strong>{{ $weekly['target'] ?: '—' }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span>المنجز في الفترة:</span>
                                                    <strong>{{ $weekly['completed'] }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span>النسبة:</span>
                                                    @if($weekly['percentage'] !== null)
                                                        <span class="badge {{ $weekly['percentage'] >= 100 ? 'bg-success' : ($weekly['percentage'] >= 50 ? 'bg-info' : 'bg-warning text-dark') }}">
                                                            {{ number_format($weekly['percentage'], 1) }}%
                                                        </span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
@stop
