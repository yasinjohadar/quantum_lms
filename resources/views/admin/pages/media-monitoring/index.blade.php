@extends('admin.layouts.master')

@section('title', 'مراقبة نظام الملفات')

@section('breadcrumb-title', 'مراقبة الوسائط المتعددة')

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
    <li class="breadcrumb-item active">مراقبة الوسائط</li>
@endsection

@section('content')
<div class="row">
    {{-- Overview Stats --}}
    <div class="col-12 mb-3">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="text-muted small">إجمالي الملفات</div>
                                <div class="fs-3 fw-bold">{{ number_format($data['overview']['total_files']) }}</div>
                            </div>
                            <div class="fs-1 text-primary"><i class="bi bi-file-earmark"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="text-muted small">الحجم الإجمالي</div>
                                <div class="fs-3 fw-bold">{{ $data['overview']['total_size_formatted'] }}</div>
                            </div>
                            <div class="fs-1 text-info"><i class="bi bi-hdd"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="text-muted small">في انتظار المزامنة</div>
                                <div class="fs-3 fw-bold text-warning">{{ number_format($data['overview']['pending_sync']) }}</div>
                            </div>
                            <div class="fs-1 text-warning"><i class="bi bi-arrow-repeat"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="text-muted small">ملفات يتيمة</div>
                                <div class="fs-3 fw-bold text-danger">{{ number_format($data['overview']['orphaned_files']) }}</div>
                            </div>
                            <div class="fs-1 text-danger"><i class="bi bi-exclamation-triangle"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Stats --}}
    <div class="col-md-6 mb-3">
        <div class="card custom-card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-calendar-event me-2"></i>إحصائيات اليوم</h6></div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="p-3 border rounded">
                            <div class="text-muted small">الرفع اليوم</div>
                            <div class="fs-4 fw-bold">{{ number_format($data['today']['uploads']) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border rounded">
                            <div class="text-muted small">حجم الرفع</div>
                            <div class="fs-4 fw-bold">{{ $data['today']['upload_size_formatted'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sync Health --}}
    <div class="col-md-6 mb-3">
        <div class="card custom-card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-heart-pulse me-2"></i>صحة المزامنة</h6></div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-3">
                        <div class="p-2 border rounded">
                            <div class="text-muted small">نسبة النجاح</div>
                            <div class="fs-5 fw-bold text-success">{{ $data['sync_health']['success_rate'] }}%</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border rounded">
                            <div class="text-muted small">تحويلات فاشلة</div>
                            <div class="fs-5 fw-bold text-danger">{{ $data['sync_health']['failed_conversions'] }}</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border rounded">
                            <div class="text-muted small">قيد الانتظار</div>
                            <div class="fs-5 fw-bold text-warning">{{ $data['sync_health']['pending_conversions'] }}</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border rounded">
                            <div class="text-muted small">Dead Letters</div>
                            <div class="fs-5 fw-bold text-danger">{{ $data['sync_health']['dead_letters'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Storage by Provider --}}
    <div class="col-md-6 mb-3">
        <div class="card custom-card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-cloud me-2"></i>التخزين حسب المزود</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>المزود</th><th>الملفات</th><th>الحجم</th></tr></thead>
                    <tbody>
                        @foreach($data['storage_by_provider'] as $provider)
                        <tr>
                            <td><span class="badge bg-primary">{{ $provider['provider'] }}</span></td>
                            <td>{{ number_format($provider['count']) }}</td>
                            <td>{{ $provider['total_size_formatted'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="col-md-6 mb-3">
        <div class="card custom-card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-gear me-2"></i>إجراءات الصيانة</h6></div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <form action="{{ route('admin.media-monitoring.cleanup-orphans') }}" method="POST" onsubmit="return confirm('حذف جميع الملفات اليتيمة؟')">
                        @csrf
                        <button class="btn btn-outline-danger w-100">
                            <i class="bi bi-trash me-1"></i>حذف الملفات اليتيمة ({{ $data['overview']['orphaned_files'] }})
                        </button>
                    </form>
                    <form action="{{ route('admin.media-monitoring.cleanup-soft-deleted') }}" method="POST" onsubmit="return confirm('حذف نهائي للملفات المحذوفة؟')">
                        @csrf
                        <button class="btn btn-outline-warning w-100">
                            <i class="bi bi-archive me-1"></i>حذف نهائي للمحذوفات ({{ $data['overview']['soft_deleted'] }})
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Failures --}}
    <div class="col-12 mb-3">
        <div class="card custom-card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-x-octagon me-2"></i>آخر الإخفاقات</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Media ID</th><th>النوع</th><th>الخطأ</th><th>المحاولات</th><th>منذ</th><th>إجراء</th></tr></thead>
                    <tbody>
                        @forelse($data['recent_failures'] as $failure)
                        <tr>
                            <td><code>{{ $failure['media_id'] }}</code></td>
                            <td><span class="badge bg-secondary">{{ $failure['type'] }}</span></td>
                            <td class="text-truncate" style="max-width:300px">{{ $failure['error'] }}</td>
                            <td>{{ $failure['attempts'] }}</td>
                            <td>{{ $failure['created_at'] }}</td>
                            <td>
                                <a href="{{ route('admin.media-monitoring.retry-conversion', ['conversion' => $failure['media_id']]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-arrow-repeat"></i> إعادة محاولة
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">لا توجد إخفاقات حديثة</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Migration Batches --}}
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-list-task me-2"></i>دفعات الترحيل</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>#</th><th>الاسم</th><th>الحالة</th><th>التقدم</th><th>منذ</th></tr></thead>
                    <tbody>
                        @forelse($data['migration_batches'] as $batch)
                        <tr>
                            <td>{{ $batch['id'] }}</td>
                            <td>{{ $batch['name'] }}</td>
                            <td><span class="badge bg-{{ $batch['status'] === 'completed' ? 'success' : 'primary' }}">{{ $batch['status'] }}</span></td>
                            <td>
                                <div class="progress" style="height:6px;width:100px">
                                    <div class="progress-bar bg-success" style="width:{{ $batch['progress'] }}%"></div>
                                </div>
                                <small>{{ $batch['progress'] }}%</small>
                            </td>
                            <td>{{ $batch['created_at'] }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">لا توجد دفعات</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
