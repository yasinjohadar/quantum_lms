@extends('admin.layouts.master')

@section('title', 'Dead Letters')

@section('breadcrumb-title', 'الملفات الفاشلة')

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
    <li class="breadcrumb-item active">Dead Letters</li>
@endsection

@section('content')
<div class="row">
    {{-- Stats --}}
    <div class="col-12 mb-3">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="text-muted small">الإجمالي</div>
                        <div class="fs-3 fw-bold">{{ number_format($stats['total']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="text-muted small">غير محلولة</div>
                        <div class="fs-3 fw-bold text-danger">{{ number_format($stats['unresolved']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="text-muted small">محلولة</div>
                        <div class="fs-3 fw-bold text-success">{{ number_format($stats['resolved']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="col-12 mb-3">
        <form action="{{ route('admin.media.dead-letters.resolve-all') }}" method="POST">
            @csrf
            <button class="btn btn-sm btn-outline-success"><i class="bi bi-check-all me-1"></i>تحديد الكل كمحلول</button>
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
                                <th>الـ Disk</th>
                                <th>الخطأ</th>
                                <th>المحاولات</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deadLetters as $dl)
                            <tr>
                                <td><code>{{ $dl->id }}</code></td>
                                <td><code class="small">{{ $dl->file_path }}</code></td>
                                <td><span class="badge bg-info">{{ $dl->target_disk }}</span></td>
                                <td class="text-truncate" style="max-width:300px">{{ $dl->error }}</td>
                                <td>{{ $dl->attempts }}</td>
                                <td>
                                    @if($dl->resolved)
                                        <span class="badge bg-success">محلولة</span>
                                    @else
                                        <span class="badge bg-danger">غير محلولة</span>
                                    @endif
                                </td>
                                <td><small>{{ $dl->created_at->diffForHumans() }}</small></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        @if(!$dl->resolved)
                                        <form action="{{ route('admin.media.dead-letters.retry', $dl) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-outline-primary" title="إعادة محاولة"><i class="bi bi-arrow-repeat"></i></button>
                                        </form>
                                        @endif
                                        <form action="{{ route('admin.media.dead-letters.delete', $dl) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف؟')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger" title="حذف"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center text-muted py-5">لا توجد ملفات فاشلة</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($deadLetters->hasPages())
            <div class="card-footer">{{ $deadLetters->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
