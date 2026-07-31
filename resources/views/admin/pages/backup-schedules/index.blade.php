@extends('admin.layouts.master')

@section('page-title')
    جدولة النسخ الاحتياطية
@stop

@push('styles')
<style>
    .schedule-stat-card {
        border: 1px solid var(--default-border);
        border-radius: 12px;
        background: var(--custom-card-bg, var(--default-background));
        padding: 1.1rem 1.15rem;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        transition: box-shadow 0.15s ease, border-color 0.15s ease;
    }
    .schedule-stat-card:hover {
        border-color: rgba(var(--primary-rgb, 132, 90, 223), 0.35);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
    }
    .schedule-stat-card__label {
        font-size: 0.82rem;
        color: var(--text-muted);
        margin-bottom: 0.25rem;
    }
    .schedule-stat-card__value {
        font-size: 1.55rem;
        font-weight: 700;
        line-height: 1.2;
        color: var(--default-text-color);
    }
    .schedule-stat-card__icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.15rem;
    }
    .schedule-stat-card--total .schedule-stat-card__icon {
        background: rgba(var(--primary-rgb, 132, 90, 223), 0.12);
        color: rgb(var(--primary-rgb, 132, 90, 223));
    }
    .schedule-stat-card--success .schedule-stat-card__icon {
        background: rgba(38, 191, 148, 0.14);
        color: #26bf94;
    }
    .schedule-stat-card--muted .schedule-stat-card__icon {
        background: rgba(130, 134, 150, 0.14);
        color: #828696;
    }
    .schedule-stat-card--info .schedule-stat-card__icon {
        background: rgba(73, 182, 245, 0.14);
        color: #49b6f5;
    }
    .schedule-empty {
        text-align: center;
        padding: 2.75rem 1.25rem;
    }
    .schedule-empty__icon {
        width: 4rem;
        height: 4rem;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        background: rgba(var(--primary-rgb, 132, 90, 223), 0.1);
        color: rgb(var(--primary-rgb, 132, 90, 223));
        font-size: 1.6rem;
    }
    .schedule-table th {
        white-space: nowrap;
        font-weight: 600;
        font-size: 0.85rem;
    }
    .schedule-table td {
        vertical-align: middle;
        font-size: 0.9rem;
    }
    .schedule-name {
        font-weight: 600;
        color: var(--default-text-color);
    }
    .schedule-meta {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: 0.15rem;
    }
</style>
@endpush

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">جدولة النسخ الاحتياطية</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.backups.index') }}">النسخ الاحتياطية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">الجدولة</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
                <a href="{{ route('admin.backups.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-database me-1"></i> النسخ
                </a>
                <a href="{{ route('admin.backup-schedules.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> جدولة جديدة
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="schedule-stat-card schedule-stat-card--total">
                    <div>
                        <div class="schedule-stat-card__label">إجمالي الجدولات</div>
                        <div class="schedule-stat-card__value">{{ number_format($stats['total'] ?? 0) }}</div>
                    </div>
                    <span class="schedule-stat-card__icon"><i class="fe fe-calendar"></i></span>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="schedule-stat-card schedule-stat-card--success">
                    <div>
                        <div class="schedule-stat-card__label">نشطة</div>
                        <div class="schedule-stat-card__value text-success">{{ number_format($stats['active'] ?? 0) }}</div>
                    </div>
                    <span class="schedule-stat-card__icon"><i class="fe fe-check-circle"></i></span>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="schedule-stat-card schedule-stat-card--muted">
                    <div>
                        <div class="schedule-stat-card__label">غير نشطة</div>
                        <div class="schedule-stat-card__value">{{ number_format($stats['inactive'] ?? 0) }}</div>
                    </div>
                    <span class="schedule-stat-card__icon"><i class="fe fe-pause-circle"></i></span>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="schedule-stat-card schedule-stat-card--info">
                    <div>
                        <div class="schedule-stat-card__label">قادمة للتشغيل</div>
                        <div class="schedule-stat-card__value text-info">{{ number_format($stats['upcoming'] ?? 0) }}</div>
                    </div>
                    <span class="schedule-stat-card__icon"><i class="fe fe-clock"></i></span>
                </div>
            </div>
        </div>

        <div class="card custom-card shadow-sm border-0">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="card-title mb-0">قائمة الجدولات</div>
                <span class="badge bg-primary-transparent">{{ $schedules->total() }} جدولة</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap mb-0 schedule-table">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>الاسم</th>
                                <th>النوع</th>
                                <th>التكرار</th>
                                <th>الوقت</th>
                                <th>الحالة</th>
                                <th>التشغيل التالي</th>
                                <th class="text-center pe-3">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schedules as $schedule)
                                <tr>
                                    <td class="ps-3 text-muted">{{ $schedule->id }}</td>
                                    <td>
                                        <div class="schedule-name">{{ $schedule->name }}</div>
                                        <div class="schedule-meta">
                                            {{ $schedule->backups_count }} نسخة منفّذة
                                            @if($schedule->creator)
                                                · {{ $schedule->creator->name }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-transparent">
                                            {{ \App\Models\BackupSchedule::BACKUP_TYPES[$schedule->backup_type] ?? $schedule->backup_type }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ \App\Models\BackupSchedule::FREQUENCIES[$schedule->frequency] ?? $schedule->frequency }}
                                    </td>
                                    <td>{{ \Illuminate\Support\Str::of($schedule->time)->substr(0, 5) }}</td>
                                    <td>
                                        @if($schedule->is_active)
                                            <span class="badge bg-success-transparent">نشط</span>
                                        @else
                                            <span class="badge bg-secondary-transparent">غير نشط</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($schedule->next_run_at)
                                            <div>{{ $schedule->next_run_at->format('Y-m-d') }}</div>
                                            <div class="schedule-meta">{{ $schedule->next_run_at->format('H:i') }}</div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-3">
                                        <div class="btn-list d-flex gap-1 justify-content-center flex-wrap">
                                            <a href="{{ route('admin.backup-schedules.edit', $schedule->id) }}"
                                               class="btn btn-sm btn-primary-light"
                                               title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.backup-schedules.execute', $schedule->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success-light" title="تشغيل الآن">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.backup-schedules.toggle-active', $schedule->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm {{ $schedule->is_active ? 'btn-warning-light' : 'btn-success-light' }}"
                                                        title="{{ $schedule->is_active ? 'إيقاف' : 'تفعيل' }}">
                                                    <i class="fas fa-{{ $schedule->is_active ? 'pause' : 'check' }}"></i>
                                                </button>
                                            </form>
                                            <button type="button"
                                                    class="btn btn-sm btn-danger-light"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteScheduleModal{{ $schedule->id }}"
                                                    title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="schedule-empty">
                                            <div class="schedule-empty__icon">
                                                <i class="fe fe-calendar"></i>
                                            </div>
                                            <h6 class="mb-1">لا توجد جدولات</h6>
                                            <p class="text-muted mb-3">أنشئ جدولة لتشغيل النسخ الاحتياطية تلقائياً في الأوقات المناسبة.</p>
                                            <a href="{{ route('admin.backup-schedules.create') }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-plus me-1"></i> إنشاء جدولة الآن
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($schedules->hasPages())
                <div class="card-footer">
                    {{ $schedules->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@foreach($schedules as $schedule)
<div class="modal fade" id="deleteScheduleModal{{ $schedule->id }}" tabindex="-1" aria-labelledby="deleteScheduleModalLabel{{ $schedule->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteScheduleModalLabel{{ $schedule->id }}">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    تأكيد الحذف
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                    <h5>هل أنت متأكد من حذف هذه الجدولة؟</h5>
                </div>
                <div class="alert alert-warning mb-3">
                    <strong>الاسم:</strong> {{ $schedule->name }}<br>
                    <strong>النوع:</strong> {{ \App\Models\BackupSchedule::BACKUP_TYPES[$schedule->backup_type] ?? $schedule->backup_type }}<br>
                    <strong>التكرار:</strong> {{ \App\Models\BackupSchedule::FREQUENCIES[$schedule->frequency] ?? $schedule->frequency }}
                </div>
                <p class="text-muted text-center mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    لن تُحذف النسخ السابقة، لكن لن تُنشأ نسخ جديدة من هذه الجدولة.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    إلغاء
                </button>
                <form action="{{ route('admin.backup-schedules.destroy', $schedule->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>
                        حذف الجدولة
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@stop
