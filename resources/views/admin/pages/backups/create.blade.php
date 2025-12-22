@extends('admin.layouts.master')

@section('page-title')
    إنشاء نسخة احتياطية
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إنشاء نسخة احتياطية</h5>
            </div>
            <div>
                <a href="{{ route('admin.backups.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form action="{{ route('admin.backups.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">اسم النسخة <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', 'backup_' . now()->format('Y-m-d_H-i-s')) }}" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="backup_type" class="form-label">نوع النسخ <span class="text-danger">*</span></label>
                                    <select class="form-select" id="backup_type" name="backup_type" required>
                                        @foreach($backupTypes as $key => $label)
                                            <option value="{{ $key }}" {{ old('backup_type', 'full') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="compression_type" class="form-label">نوع الضغط <span class="text-danger">*</span></label>
                                    <select class="form-select" id="compression_type" name="compression_type" required>
                                        @foreach($compressionTypes as $key => $label)
                                            <option value="{{ $key }}" {{ old('compression_type', 'zip') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="storage_driver" class="form-label">مكان التخزين <span class="text-danger">*</span></label>
                                    <select class="form-select" id="storage_driver" name="storage_driver" required>
                                        @foreach($storageDrivers as $config)
                                            <option value="{{ $config->driver }}" {{ old('storage_driver') == $config->driver ? 'selected' : '' }}>{{ $config->name }} ({{ \App\Models\BackupStorageConfig::DRIVERS[$config->driver] ?? $config->driver }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="retention_days" class="form-label">أيام الاحتفاظ <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="retention_days" name="retention_days" value="{{ old('retention_days', 30) }}" min="1" max="365" required>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> إنشاء النسخة
                                </button>
                                <a href="{{ route('admin.backups.index') }}" class="btn btn-secondary">
                                    إلغاء
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

