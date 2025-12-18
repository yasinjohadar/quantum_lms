@extends('admin.layouts.master')

@section('page-title')
    أنشطة الجلسة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">أنشطة الجلسة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.user-sessions.index') }}">جلسات المستخدمين</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.user-sessions.show', $session->id) }}">تفاصيل الجلسة</a></li>
                            <li class="breadcrumb-item active" aria-current="page">الأنشطة</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.user-sessions.show', $session->id) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> العودة
                    </a>
                </div>
            </div>

            <!-- معلومات الجلسة -->
            <div class="row mb-4">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="mb-1">جلسة: {{ $session->session_name ?? 'جلسة عادية' }}</h5>
                                    <p class="text-muted mb-0">
                                        المستخدم: <strong>{{ $session->user->name ?? '-' }}</strong> | 
                                        الحالة: {!! $session->status_badge !!} |
                                        بدأت: {{ $session->started_at->format('Y-m-d H:i:s') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- إحصائيات -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-1">إجمالي الأنشطة</p>
                                    <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                                </div>
                                <div class="avatar avatar-md bg-primary-transparent">
                                    <i class="bi bi-list-check fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-1">عرض صفحات</p>
                                    <h4 class="mb-0 text-primary">{{ number_format($stats['page_views']) }}</h4>
                                </div>
                                <div class="avatar avatar-md bg-primary-transparent">
                                    <i class="bi bi-eye fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-1">إجراءات</p>
                                    <h4 class="mb-0 text-info">{{ number_format($stats['actions']) }}</h4>
                                </div>
                                <div class="avatar avatar-md bg-info-transparent">
                                    <i class="bi bi-cursor fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-1">صفحات فريدة</p>
                                    <h4 class="mb-0 text-success">{{ number_format($stats['unique_pages']) }}</h4>
                                </div>
                                <div class="avatar avatar-md bg-success-transparent">
                                    <i class="bi bi-file-earmark fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- قائمة الأنشطة -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <h5 class="mb-0 fw-bold">قائمة الأنشطة</h5>

                            <form method="GET" action="{{ route('admin.user-sessions.activities', $session->id) }}"
                                  class="d-flex flex-wrap gap-2 align-items-center">
                                <select name="activity_type" class="form-select form-select-sm" style="min-width: 180px;">
                                    <option value="">كل الأنواع</option>
                                    @foreach($activityTypes as $type => $name)
                                        <option value="{{ $type }}" {{ request('activity_type') === $type ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>

                                <input type="date" name="date_from" class="form-control form-control-sm"
                                       value="{{ request('date_from') }}" style="min-width: 150px;"
                                       placeholder="من تاريخ">

                                <input type="date" name="date_to" class="form-control form-control-sm"
                                       value="{{ request('date_to') }}" style="min-width: 150px;"
                                       placeholder="إلى تاريخ">

                                <button type="submit" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-search me-1"></i> بحث
                                </button>
                                <a href="{{ route('admin.user-sessions.activities', $session->id) }}" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-x-circle me-1"></i> مسح
                                </a>
                            </form>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th style="min-width: 150px;">نوع النشاط</th>
                                        <th style="min-width: 300px;">الصفحة/الإجراء</th>
                                        <th style="min-width: 200px;">التفاصيل</th>
                                        <th style="min-width: 150px;">الوقت</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($activities as $activity)
                                        <tr>
                                            <td>{{ $loop->iteration + ($activities->currentPage() - 1) * $activities->perPage() }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi {{ $activity->activity_icon }} fs-5"></i>
                                                    {!! $activity->activity_badge !!}
                                                </div>
                                            </td>
                                            <td>
                                                @if($activity->page_url)
                                                    <a href="{{ $activity->page_url }}" target="_blank" class="text-decoration-none">
                                                        {{ \Illuminate\Support\Str::limit($activity->page_url, 60) }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($activity->activity_details)
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#detailsModal{{ $activity->id }}">
                                                        <i class="bi bi-info-circle me-1"></i> عرض التفاصيل
                                                    </button>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $activity->occurred_at->format('Y-m-d') }}</div>
                                                <small class="text-muted">{{ $activity->occurred_at->format('H:i:s') }}</small>
                                                <div class="text-muted small">{{ $activity->occurred_at->diffForHumans() }}</div>
                                            </td>
                                        </tr>

                                        <!-- Modal for Activity Details -->
                                        @if($activity->activity_details)
                                        <div class="modal fade" id="detailsModal{{ $activity->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">تفاصيل النشاط</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <pre class="bg-light p-3 rounded">{{ json_encode($activity->activity_details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                                                <p class="text-muted">لا توجد أنشطة مسجلة</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($activities->hasPages())
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $activities->appends(request()->query())->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('js')
@stop

