@extends('admin.layouts.master')

@section('page-title')
    إحصائيات سابقة للمعلم: {{ $teacher->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إحصائيات سابقة للمعلم: {{ $teacher->name }}</h5>
                    <div class="small text-muted">عرض الأسابيع التي انتهت فقط ضمن السنة الدراسية النشطة.</div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.teachers.progress.show', $teacher) }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i> تفاصيل التقدم (الأسبوع الحالي)
                    </a>
                    <a href="{{ route('admin.teachers.assignments.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-list me-1"></i> قائمة المعلمين
                    </a>
                    <a href="{{ route('admin.teachers.assignments', $teacher->id) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-tie me-1"></i> تخصيص
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

            {{-- بطاقات الملخص التراكمي --}}
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
                            <h6 class="text-white-50 mb-2">نسبة تقدم الصفحات (تراكمي)</h6>
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
                            <h6 class="mb-2">الهدف الأسبوعي (الأسبوع الحالي فقط)</h6>
                            @if(($weekly_progress['target'] ?? 0) > 0)
                                <h3 class="mb-1">{{ $weekly_progress['completed'] ?? 0 }} / {{ $weekly_progress['target'] }}</h3>
                                @if(($weekly_progress['percentage'] ?? null) !== null)
                                    <span class="badge {{ $weekly_progress['percentage'] >= 100 ? 'bg-success' : ($weekly_progress['percentage'] >= 50 ? 'bg-info' : 'bg-secondary') }}">{{ number_format($weekly_progress['percentage'], 1) }}%</span>
                                @endif
                            @else
                                <p class="mb-0">— لا يوجد هدف أسبوعي</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- جدول الأسابيع الماضية فقط --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-calendar-week me-2"></i>
                        الدروس الأسبوعية للأسابيع الماضية
                    </h6>
                    <span class="small text-muted">لا تظهر الأسابيع القادمة، ولا يمكن تعديل أهداف الأسابيع المنتهية.</span>
                </div>
                <div class="card-body">
                    @if(!empty($pastWeeksProgress))
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 90px;">الأسبوع</th>
                                        <th>الفترة</th>
                                        <th style="width: 130px;">المنجز</th>
                                        <th style="width: 130px;">الهدف</th>
                                        <th style="width: 120px;">النسبة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pastWeeksProgress as $row)
                                        @php $w = $row['week']; @endphp
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
                                            <td>{{ $row['completed'] }}</td>
                                            <td>{{ $row['target'] }}</td>
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
                        <div class="text-muted">لا توجد أسابيع منتهية ضمن السنة الدراسية النشطة حتى الآن.</div>
                    @endif
                </div>
            </div>

            {{-- جدول تقدم الصفحات حسب المادة (تراكمي) --}}
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-journal-bookmark me-2"></i>
                        تقدم الصفحات حسب المادة (تراكمي)
                    </h6>
                </div>
                <div class="card-body">
                    @if(!empty($pages_progress))
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>المادة</th>
                                        <th>الصف</th>
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
                                            <td class="fw-semibold">{{ $row['subject']->name }}</td>
                                            <td class="small text-muted">{{ $row['subject']->schoolClass?->name ?? '—' }}</td>
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

