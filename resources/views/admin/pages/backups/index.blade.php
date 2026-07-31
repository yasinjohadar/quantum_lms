@extends('admin.layouts.master')

@section('page-title')
    النسخ الاحتياطية
@stop

@push('styles')
<style>
    .backup-stat-card {
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
    .backup-stat-card:hover {
        border-color: rgba(var(--primary-rgb, 132, 90, 223), 0.35);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
    }
    .backup-stat-card__label {
        font-size: 0.82rem;
        color: var(--text-muted);
        margin-bottom: 0.25rem;
    }
    .backup-stat-card__value {
        font-size: 1.55rem;
        font-weight: 700;
        line-height: 1.2;
        color: var(--default-text-color);
    }
    .backup-stat-card__icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.15rem;
    }
    .backup-stat-card--total .backup-stat-card__icon {
        background: rgba(var(--primary-rgb, 132, 90, 223), 0.12);
        color: rgb(var(--primary-rgb, 132, 90, 223));
    }
    .backup-stat-card--success .backup-stat-card__icon {
        background: rgba(38, 191, 148, 0.14);
        color: #26bf94;
    }
    .backup-stat-card--danger .backup-stat-card__icon {
        background: rgba(230, 83, 60, 0.14);
        color: #e6533c;
    }
    .backup-stat-card--info .backup-stat-card__icon {
        background: rgba(73, 182, 245, 0.14);
        color: #49b6f5;
    }
    .backup-empty {
        text-align: center;
        padding: 2.75rem 1.25rem;
    }
    .backup-empty__icon {
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
    .backup-table th {
        white-space: nowrap;
        font-weight: 600;
        font-size: 0.85rem;
    }
    .backup-table td {
        vertical-align: middle;
        font-size: 0.9rem;
    }
    .backup-name {
        font-weight: 600;
        color: var(--default-text-color);
    }
    .backup-meta {
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
                <h5 class="page-title fs-21 mb-1">النسخ الاحتياطية</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">النسخ الاحتياطية</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
                <a href="{{ route('admin.backup-schedules.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-clock me-1"></i> الجدولة
                </a>
                <a href="{{ route('admin.backups.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> نسخة احتياطية جديدة
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

        @if(($stats['overdue_schedules'] ?? 0) > 0 || ($stats['stuck'] ?? 0) > 0)
            <div class="alert alert-warning d-flex align-items-start gap-2 mb-4">
                <i class="fas fa-triangle-exclamation mt-1"></i>
                <div>
                    <strong>تنبيه تشغيلي:</strong>
                    @if(($stats['overdue_schedules'] ?? 0) > 0)
                        <div>{{ $stats['overdue_schedules'] }} جدولة نشطة متأخرة عن موعدها — تحقق من تشغيل queue:work و schedule:run/work على الخادم.</div>
                    @endif
                    @if(($stats['stuck'] ?? 0) > 0)
                        <div>{{ $stats['stuck'] }} نسخة عالقة في حالة معلّق/قيد التنفيذ لفترة أطول من المتوقع — شغّل <code>backup:reconcile-stuck</code> أو انتظر تشغيله التلقائي.</div>
                    @endif
                </div>
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="backup-stat-card backup-stat-card--total">
                    <div>
                        <div class="backup-stat-card__label">إجمالي النسخ</div>
                        <div class="backup-stat-card__value">{{ number_format($stats['total'] ?? 0) }}</div>
                    </div>
                    <span class="backup-stat-card__icon"><i class="fe fe-database"></i></span>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="backup-stat-card backup-stat-card--success">
                    <div>
                        <div class="backup-stat-card__label">مكتملة</div>
                        <div class="backup-stat-card__value text-success">{{ number_format($stats['completed'] ?? 0) }}</div>
                    </div>
                    <span class="backup-stat-card__icon"><i class="fe fe-check-circle"></i></span>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="backup-stat-card backup-stat-card--danger">
                    <div>
                        <div class="backup-stat-card__label">فاشلة</div>
                        <div class="backup-stat-card__value text-danger">{{ number_format($stats['failed'] ?? 0) }}</div>
                    </div>
                    <span class="backup-stat-card__icon"><i class="fe fe-x-circle"></i></span>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="backup-stat-card backup-stat-card--info">
                    <div>
                        <div class="backup-stat-card__label">الحجم الإجمالي</div>
                        <div class="backup-stat-card__value">{{ number_format(($stats['total_size'] ?? 0) / 1024 / 1024, 2) }} <small class="fs-14 fw-semibold text-muted">MB</small></div>
                    </div>
                    <span class="backup-stat-card__icon"><i class="fe fe-hard-drive"></i></span>
                </div>
            </div>
        </div>

        <div class="card custom-card shadow-sm border-0 mb-4">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="card-title mb-0">تصفية النسخ</div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.backups.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="status" class="form-label">الحالة</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">الكل</option>
                            @foreach(\App\Models\Backup::STATUSES as $key => $label)
                                <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="backup_type" class="form-label">نوع النسخ</label>
                        <select name="backup_type" id="backup_type" class="form-select">
                            <option value="">الكل</option>
                            @foreach(\App\Models\Backup::BACKUP_TYPES as $key => $label)
                                <option value="{{ $key }}" {{ request('backup_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="storage_driver" class="form-label">التخزين</label>
                        <select name="storage_driver" id="storage_driver" class="form-select">
                            <option value="">الكل</option>
                            @foreach(\App\Models\AppStorageConfig::DRIVERS as $key => $label)
                                <option value="{{ $key }}" {{ request('storage_driver') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> تطبيق
                        </button>
                        @if(request()->filled('status') || request()->filled('backup_type') || request()->filled('storage_driver'))
                            <a href="{{ route('admin.backups.index') }}" class="btn btn-light">إعادة تعيين</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="card custom-card shadow-sm border-0">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="card-title mb-0">قائمة النسخ</div>
                <span class="badge bg-primary-transparent">{{ $backups->total() }} نسخة</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap mb-0 backup-table">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>الاسم</th>
                                <th>النوع</th>
                                <th>التخزين</th>
                                <th>الحالة</th>
                                <th>الحجم</th>
                                <th>التاريخ</th>
                                <th class="text-center pe-3">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($backups as $backup)
                                <tr>
                                    <td class="ps-3 text-muted">{{ $backup->id }}</td>
                                    <td>
                                        <div class="backup-name">{{ $backup->name }}</div>
                                        <div class="backup-meta">
                                            @if($backup->type === 'scheduled')
                                                جدولة
                                                @if($backup->schedule)
                                                    · {{ $backup->schedule->name }}
                                                @endif
                                            @else
                                                يدوي
                                                @if($backup->creator)
                                                    · {{ $backup->creator->name }}
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-transparent">
                                            {{ \App\Models\Backup::BACKUP_TYPES[$backup->backup_type] ?? $backup->backup_type }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($backup->storageConfig)
                                            <span class="fw-semibold">{{ $backup->storageConfig->name }}</span>
                                            <div class="backup-meta">{{ strtoupper($backup->storageConfig->driver) }}</div>
                                        @elseif($backup->storage_driver)
                                            <span class="text-muted">{{ strtoupper($backup->storage_driver) }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($backup->status === 'completed')
                                            <span class="badge bg-success-transparent">مكتمل</span>
                                        @elseif($backup->status === 'failed')
                                            <span class="badge bg-danger-transparent">فشل</span>
                                        @elseif($backup->status === 'running')
                                            <span class="badge bg-warning-transparent">قيد التنفيذ</span>
                                        @else
                                            <span class="badge bg-secondary-transparent">في الطابور</span>
                                        @endif
                                    </td>
                                    <td>{{ $backup->getFileSize() }}</td>
                                    <td>
                                        <div>{{ $backup->created_at->format('Y-m-d') }}</div>
                                        <div class="backup-meta">{{ $backup->created_at->format('H:i') }}</div>
                                    </td>
                                    <td class="text-center pe-3">
                                        <div class="btn-list d-flex gap-1 justify-content-center">
                                            <a href="{{ route('admin.backups.show', $backup->id) }}"
                                               class="btn btn-sm btn-primary-light"
                                               title="عرض">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($backup->status === 'completed')
                                                <a href="{{ route('admin.backups.download', $backup->id) }}"
                                                   class="btn btn-sm btn-success-light"
                                                   title="تحميل">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            @endif
                                            <button type="button"
                                                    class="btn btn-sm btn-danger-light"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteBackupModal{{ $backup->id }}"
                                                    title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="backup-empty">
                                            <div class="backup-empty__icon">
                                                <i class="fe fe-database"></i>
                                            </div>
                                            <h6 class="mb-1">لا توجد نسخ احتياطية</h6>
                                            <p class="text-muted mb-3">أنشئ نسخة يدوية أو فعّل جدولة لبدء حفظ البيانات.</p>
                                            <a href="{{ route('admin.backups.create') }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-plus me-1"></i> إنشاء نسخة الآن
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($backups->hasPages())
                <div class="card-footer">
                    {{ $backups->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@foreach($backups as $backup)
<div class="modal fade" id="deleteBackupModal{{ $backup->id }}" tabindex="-1" aria-labelledby="deleteBackupModalLabel{{ $backup->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteBackupModalLabel{{ $backup->id }}">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    تأكيد الحذف
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                    <h5>هل أنت متأكد من حذف هذه النسخة الاحتياطية؟</h5>
                </div>
                <div class="alert alert-warning mb-3">
                    <strong>اسم النسخة:</strong> {{ $backup->name }}<br>
                    <strong>النوع:</strong> {{ \App\Models\Backup::BACKUP_TYPES[$backup->backup_type] ?? $backup->backup_type }}<br>
                    <strong>الحجم:</strong> {{ $backup->getFileSize() }}<br>
                    <strong>التاريخ:</strong> {{ $backup->created_at->format('Y-m-d H:i') }}
                </div>
                <p class="text-muted text-center mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    لا يمكن التراجع عن هذا الإجراء بعد التنفيذ.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    إلغاء
                </button>
                <form action="{{ route('admin.backups.destroy', $backup->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>
                        حذف النسخة
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@stop
