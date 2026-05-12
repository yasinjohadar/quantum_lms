@extends('admin.layouts.master')

@section('title', 'عمليات التحويل')

@section('breadcrumb-title', 'التحويلات')

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
    <li class="breadcrumb-item active">التحويلات</li>
@endsection

@section('content')
<div class="row">
    {{-- Stats --}}
    <div class="col-12 mb-3">
        <div class="row g-3">
            <div class="col-md-2">
                <div class="card custom-card"><div class="card-body">
                    <div class="text-muted small">الإجمالي</div>
                    <div class="fs-4 fw-bold">{{ number_format($stats['total']) }}</div>
                </div></div>
            </div>
            <div class="col-md-2">
                <div class="card custom-card"><div class="card-body">
                    <div class="text-muted small">قيد الانتظار</div>
                    <div class="fs-4 fw-bold text-warning">{{ number_format($stats['pending']) }}</div>
                </div></div>
            </div>
            <div class="col-md-2">
                <div class="card custom-card"><div class="card-body">
                    <div class="text-muted small">قيد المعالجة</div>
                    <div class="fs-4 fw-bold text-primary">{{ number_format($stats['processing']) }}</div>
                </div></div>
            </div>
            <div class="col-md-2">
                <div class="card custom-card"><div class="card-body">
                    <div class="text-muted small">مكتمل</div>
                    <div class="fs-4 fw-bold text-success">{{ number_format($stats['completed']) }}</div>
                </div></div>
            </div>
            <div class="col-md-2">
                <div class="card custom-card"><div class="card-body">
                    <div class="text-muted small">فاشل</div>
                    <div class="fs-4 fw-bold text-danger">{{ number_format($stats['failed']) }}</div>
                </div></div>
            </div>
        </div>
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
                                <th>Media</th>
                                <th>النوع</th>
                                <th>الحالة</th>
                                <th>المحاولات</th>
                                <th>الخطأ</th>
                                <th>التاريخ</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($conversions as $conv)
                            <tr>
                                <td><code>{{ $conv->id }}</code></td>
                                <td><a href="{{ route('admin.media.show', $conv->media_id) }}" class="text-decoration-none">{{ $conv->media_id }}</a></td>
                                <td><code class="small">{{ $conv->type }}</code></td>
                                <td>
                                    @php
                                        $colors = ['pending' => 'warning', 'processing' => 'primary', 'completed' => 'success', 'failed' => 'danger'];
                                    @endphp
                                    <span class="badge bg-{{ $colors[$conv->status] ?? 'secondary' }}">{{ $conv->status }}</span>
                                </td>
                                <td>{{ $conv->attempts }}</td>
                                <td class="text-truncate" style="max-width:250px">{{ $conv->error }}</td>
                                <td><small>{{ $conv->created_at->diffForHumans() }}</small></td>
                                <td>
                                    @if($conv->canRetry())
                                    <form action="{{ route('admin.media.retry-conversion', $conv) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-repeat"></i></button>
                                    </form>
                                    @endif
                                    <form action="{{ route('admin.media.delete-conversion', $conv) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف؟')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center text-muted py-5">لا توجد تحويلات</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($conversions->hasPages())
            <div class="card-footer">{{ $conversions->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
