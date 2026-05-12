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

@section('title', 'الملفات اليتيمة')

@section('breadcrumb-title', 'الملفات اليتيمة')

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
    <li class="breadcrumb-item active">الملفات اليتيمة</li>
@endsection

@section('content')
<div class="row">
    {{-- Stats --}}
    <div class="col-12 mb-3">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="text-muted small">ملفات يتيمة</div>
                        <div class="fs-3 fw-bold text-danger">{{ number_format($stats['total']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="text-muted small">الحجم الإجمالي</div>
                        <div class="fs-3 fw-bold">{{ formatBytesHelper($stats['total_size']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="text-muted small">حسب المزود</div>
                        <div class="small">
                            @foreach($stats['by_provider'] as $provider => $count)
                                <span class="badge bg-secondary me-1">{{ $provider }}: {{ $count }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete All --}}
    <div class="col-12 mb-3">
        <form action="{{ route('admin.media.delete-orphans') }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف جميع الملفات اليتيمة؟ لا يمكن التراجع.')">
            @csrf
            <button class="btn btn-danger"><i class="bi bi-trash me-1"></i>حذف جميع الملفات اليتيمة</button>
        </form>
    </div>

    {{-- Table --}}
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
                                <th>الرافع</th>
                                <th>التاريخ</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orphans as $file)
                            <tr>
                                <td><code>{{ $file->id }}</code></td>
                                <td><code class="small">{{ $file->path }}</code></td>
                                <td><span class="badge bg-{{ $file->provider === 'local' ? 'secondary' : 'primary' }}">{{ $file->provider }}</span></td>
                                <td>{{ $file->extension }}</td>
                                <td><small>{{ $file->size_formatted() }}</small></td>
                                <td>{{ $file->uploader?->name ?? '-' }}</td>
                                <td><small>{{ $file->created_at->diffForHumans() }}</small></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.media.show', $file) }}" class="btn btn-outline-primary"><i class="bi bi-eye"></i></a>
                                        <form action="{{ route('admin.media.destroy', $file) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف نهائي؟')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center text-muted py-5">لا توجد ملفات يتيمة 🎉</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($orphans->hasPages())
            <div class="card-footer">{{ $orphans->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
