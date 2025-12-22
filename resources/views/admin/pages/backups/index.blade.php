@extends('admin.layouts.master')

@section('page-title')
    النسخ الاحتياطية
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">النسخ الاحتياطية</h5>
            </div>
            <div>
                <a href="{{ route('admin.backups.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> نسخة احتياطية جديدة
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h4>{{ $stats['total'] ?? 0 }}</h4>
                        <p class="mb-0">إجمالي النسخ</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h4 class="text-success">{{ $stats['completed'] ?? 0 }}</h4>
                        <p class="mb-0">مكتملة</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h4 class="text-danger">{{ $stats['failed'] ?? 0 }}</h4>
                        <p class="mb-0">فاشلة</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h4>{{ number_format(($stats['total_size'] ?? 0) / 1024 / 1024, 2) }} MB</h4>
                        <p class="mb-0">الحجم الإجمالي</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered text-center mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>الاسم</th>
                                        <th>النوع</th>
                                        <th>الحالة</th>
                                        <th>الحجم</th>
                                        <th>التاريخ</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($backups as $backup)
                                        <tr>
                                            <td>{{ $backup->id }}</td>
                                            <td>{{ $backup->name }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ \App\Models\Backup::BACKUP_TYPES[$backup->backup_type] }}</span>
                                            </td>
                                            <td>
                                                @if($backup->status === 'completed')
                                                    <span class="badge bg-success">مكتمل</span>
                                                @elseif($backup->status === 'failed')
                                                    <span class="badge bg-danger">فشل</span>
                                                @elseif($backup->status === 'running')
                                                    <span class="badge bg-warning">قيد التنفيذ</span>
                                                @else
                                                    <span class="badge bg-secondary">معلق</span>
                                                @endif
                                            </td>
                                            <td>{{ $backup->getFileSize() }}</td>
                                            <td>{{ $backup->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <a href="{{ route('admin.backups.show', $backup->id) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($backup->status === 'completed')
                                                        <a href="{{ route('admin.backups.download', $backup->id) }}" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    @endif
                                                    <form action="{{ route('admin.backups.destroy', $backup->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه النسخة؟');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">لا توجد نسخ احتياطية.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $backups->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

