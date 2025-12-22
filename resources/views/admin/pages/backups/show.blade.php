@extends('admin.layouts.master')

@section('page-title')
    تفاصيل النسخة الاحتياطية
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تفاصيل النسخة: {{ $backup->name }}</h5>
            </div>
            <div>
                <a href="{{ route('admin.backups.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">معلومات النسخة</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>الاسم:</strong> {{ $backup->name }}</p>
                        <p><strong>النوع:</strong> {{ \App\Models\Backup::BACKUP_TYPES[$backup->backup_type] }}</p>
                        <p><strong>الحالة:</strong> 
                            @if($backup->status === 'completed')
                                <span class="badge bg-success">مكتمل</span>
                            @elseif($backup->status === 'failed')
                                <span class="badge bg-danger">فشل</span>
                            @elseif($backup->status === 'running')
                                <span class="badge bg-warning">قيد التنفيذ</span>
                            @else
                                <span class="badge bg-secondary">معلق</span>
                            @endif
                        </p>
                        <p><strong>الحجم:</strong> {{ $backup->getFileSize() }}</p>
                        <p><strong>تاريخ الإنشاء:</strong> {{ $backup->created_at->format('Y-m-d H:i:s') }}</p>
                        @if($backup->completed_at)
                            <p><strong>تاريخ الاكتمال:</strong> {{ $backup->completed_at->format('Y-m-d H:i:s') }}</p>
                        @endif
                        @if($backup->error_message)
                            <div class="alert alert-danger">
                                <strong>خطأ:</strong> {{ $backup->error_message }}
                            </div>
                        @endif
                    </div>
                </div>

                @if($backup->status === 'completed')
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">الإجراءات</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.backups.download', $backup->id) }}" class="btn btn-primary">
                                    <i class="fas fa-download me-1"></i> تحميل
                                </a>
                                <form action="{{ route('admin.backups.restore', $backup->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من استعادة هذه النسخة؟ سيتم استبدال البيانات الحالية.');">
                                    @csrf
                                    <input type="hidden" name="confirm" value="1">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-undo me-1"></i> استعادة
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

                @if($backup->logs->count() > 0)
                    <div class="card shadow-sm border-0 mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">سجل العمليات</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>الوقت</th>
                                            <th>المستوى</th>
                                            <th>الرسالة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($backup->logs as $log)
                                            <tr>
                                                <td>{{ $log->created_at->format('H:i:s') }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $log->level === 'error' ? 'danger' : ($log->level === 'warning' ? 'warning' : 'info') }}">
                                                        {{ \App\Models\BackupLog::LEVELS[$log->level] }}
                                                    </span>
                                                </td>
                                                <td>{{ $log->message }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

