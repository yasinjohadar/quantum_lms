@php
function formatBytesHelper($bytes) {
    if ($bytes === 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) { $bytes /= 1024; $i++; }
    return round($bytes, 1) . ' ' . $units[$i];
}
@endphp

@extends('admin.layouts.master')

@section('title', 'إدارة الملفات')

@section('breadcrumb-title', 'الوسائط المتعددة')

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
    <li class="breadcrumb-item active">الملفات</li>
@endsection

@section('content')
<div class="row">
    {{-- Stats --}}
    <div class="col-12 mb-3">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="text-muted small">إجمالي الملفات</div>
                        <div class="fs-3 fw-bold">{{ number_format($stats['total']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="text-muted small">تمت مزامنتها</div>
                        <div class="fs-3 fw-bold text-success">{{ number_format($stats['synced']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="text-muted small">قيد المزامنة</div>
                        <div class="fs-3 fw-bold text-warning">{{ number_format($stats['pending']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="text-muted small">الحجم الإجمالي</div>
                        <div class="fs-3 fw-bold">{{ formatBytesHelper($stats['total_size']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="col-12 mb-3">
        <div class="card custom-card">
            <div class="card-body">
                <form method="GET" class="row g-2">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="بحث..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="provider" class="form-select form-select-sm">
                            <option value="">كل المزودين</option>
                            <option value="local" {{ request('provider') === 'local' ? 'selected' : '' }}>محلي</option>
                            <option value="s3" {{ request('provider') === 's3' ? 'selected' : '' }}>S3</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="sync_status" class="form-select form-select-sm">
                            <option value="">كل الحالات</option>
                            <option value="completed" {{ request('sync_status') === 'completed' ? 'selected' : '' }}>مكتمل</option>
                            <option value="pending" {{ request('sync_status') === 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                            <option value="failed" {{ request('sync_status') === 'failed' ? 'selected' : '' }}>فاشل</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="visibility" class="form-select form-select-sm">
                            <option value="">كل المستويات</option>
                            <option value="public" {{ request('visibility') === 'public' ? 'selected' : '' }}>عام</option>
                            <option value="private" {{ request('visibility') === 'private' ? 'selected' : '' }}>خاص</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>تصفية</button>
                        <a href="{{ route('admin.media.index') }}" class="btn btn-sm btn-secondary"><i class="bi bi-x-lg me-1"></i>إعادة تعيين</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Media Table --}}
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>المسار</th>
                                <th>المزود</th>
                                <th>النوع</th>
                                <th>الحجم</th>
                                <th>الرؤية</th>
                                <th>المزامنة</th>
                                <th>المرجع</th>
                                <th>التاريخ</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($media as $file)
                            <tr>
                                <td><code class="small">{{ $file->id }}</code></td>
                                <td>
                                    <div class="text-truncate" style="max-width:250px" title="{{ $file->path }}">
                                        <i class="bi bi-{{ $file->mime_type && str_starts_with($file->mime_type, 'image/') ? 'file-image' : ($file->mime_type && str_starts_with($file->mime_type, 'video/') ? 'file-play' : 'file-earmark') }} me-1"></i>
                                        {{ $file->path }}
                                    </div>
                                </td>
                                <td><span class="badge bg-{{ $file->provider === 'local' ? 'secondary' : 'primary' }}">{{ $file->provider }}</span></td>
                                <td><small>{{ $file->extension }}</small></td>
                                <td><small>{{ $file->size_formatted() }}</small></td>
                                <td>
                                    <span class="badge bg-{{ $file->visibility === 'public' ? 'success' : 'warning' }}">
                                        {{ $file->visibility === 'public' ? 'عام' : 'خاص' }}
                                    </span>
                                </td>
                                <td>
                                    @if($file->is_synced)
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i></span>
                                    @else
                                        <span class="badge bg-warning"><i class="bi bi-hourglass-split"></i></span>
                                    @endif
                                </td>
                                <td><span class="badge bg-info">{{ $file->reference_count }}</span></td>
                                <td><small>{{ $file->created_at->diffForHumans() }}</small></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.media.show', $file) }}" class="btn btn-outline-primary" title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if(!$file->is_synced)
                                        <form action="{{ route('admin.media.sync', $file) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-outline-warning" title="مزامنة الآن">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        </form>
                                        @endif
                                        <form action="{{ route('admin.media.soft-delete', $file) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف مؤقت؟')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger" title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="10" class="text-center text-muted py-5">لا توجد ملفات</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($media->hasPages())
            <div class="card-footer">
                {{ $media->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
