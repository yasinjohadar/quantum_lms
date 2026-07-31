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
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.backups.index') }}">النسخ الاحتياطية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">تفاصيل</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admin.backups.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
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

        @if (in_array($backup->status, ['pending', 'running'], true))
            <div class="alert alert-info d-flex align-items-center justify-content-between gap-2" id="backup-processing-alert">
                <div>
                    <i class="fas fa-spinner fa-spin me-2"></i>
                    @if($backup->status === 'pending')
                        النسخة في الطابور بانتظار المعالجة...
                    @else
                        جاري إنشاء النسخة الآن...
                    @endif
                    <span class="text-muted fs-13 d-block mt-1">ستُحدَّث هذه الصفحة تلقائياً كل 5 ثوانٍ.</span>
                </div>
                <a href="{{ route('admin.backups.show', $backup) }}" class="btn btn-sm btn-outline-primary">تحديث الآن</a>
            </div>
        @endif

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
                                <span class="badge bg-secondary">في الطابور</span>
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
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('admin.backups.download', $backup->id) }}" class="btn btn-primary">
                                    <i class="fas fa-download me-1"></i> تحميل
                                </a>
                                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#restoreBackupModal">
                                    <i class="fas fa-undo me-1"></i> استعادة
                                </button>
                            </div>
                            <p class="text-muted fs-13 mb-0 mt-3">
                                الاستعادة تستبدل البيانات/الملفات/الإعدادات حسب نوع النسخة. يُنصح بأخذ نسخة حديثة قبل المتابعة.
                            </p>
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

@if($backup->status === 'completed')
<div class="modal fade" id="restoreBackupModal" tabindex="-1" aria-labelledby="restoreBackupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.backups.restore', $backup) }}" method="POST" id="restore-backup-form">
                @csrf
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="restoreBackupModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        تأكيد استعادة النسخة
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <strong>تحذير:</strong> ستُستبدل البيانات الحالية حسب نوع النسخة
                        (<span class="badge bg-dark">{{ \App\Models\Backup::BACKUP_TYPES[$backup->backup_type] ?? $backup->backup_type }}</span>.
                        لا يمكن التراجع بسهولة بعد التنفيذ.
                    </div>

                    <p class="mb-2"><strong>النسخة:</strong> {{ $backup->name }}</p>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" id="restore_confirm" name="confirm" required>
                        <label class="form-check-label" for="restore_confirm">
                            أؤكد أنني أفهم المخاطر وأرغب في المتابعة
                        </label>
                    </div>

                    <div class="mb-0">
                        <label for="confirm_phrase" class="form-label">اكتب <code>RESTORE</code> للتأكيد النهائي</label>
                        <input type="text"
                               class="form-control @error('confirm_phrase') is-invalid @enderror"
                               id="confirm_phrase"
                               name="confirm_phrase"
                               autocomplete="off"
                               required>
                        @error('confirm_phrase')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning" id="restore-submit-btn" disabled>
                        <i class="fas fa-undo me-1"></i> تنفيذ الاستعادة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@stop

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if(in_array($backup->status, ['pending', 'running'], true))
    setTimeout(function () {
        window.location.reload();
    }, 5000);
    @endif

    const checkbox = document.getElementById('restore_confirm');
    const phrase = document.getElementById('confirm_phrase');
    const submitBtn = document.getElementById('restore-submit-btn');

    function syncRestoreButton() {
        if (!checkbox || !phrase || !submitBtn) return;
        const ok = checkbox.checked && phrase.value.trim() === 'RESTORE';
        submitBtn.disabled = !ok;
    }

    if (checkbox && phrase) {
        checkbox.addEventListener('change', syncRestoreButton);
        phrase.addEventListener('input', syncRestoreButton);
        syncRestoreButton();
    }

    @if($errors->has('confirm_phrase') || $errors->has('confirm'))
    const modalEl = document.getElementById('restoreBackupModal');
    if (modalEl && window.bootstrap) {
        new bootstrap.Modal(modalEl).show();
    }
    @endif
});
</script>
@endpush

