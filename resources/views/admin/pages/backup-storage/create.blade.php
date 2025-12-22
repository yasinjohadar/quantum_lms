@extends('admin.layouts.master')

@section('page-title')
    إضافة مكان تخزين
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إضافة مكان تخزين</h5>
            </div>
            <div>
                <a href="{{ route('admin.backup-storage.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form action="{{ route('admin.backup-storage.store') }}" method="POST" id="storage-form">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">اسم الإعداد <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="driver" class="form-label">نوع التخزين <span class="text-danger">*</span></label>
                                <select class="form-select" id="driver" name="driver" required>
                                    <option value="">اختر نوع التخزين</option>
                                    @foreach($drivers as $key => $label)
                                        <option value="{{ $key }}" {{ old('driver') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="config-fields">
                                <!-- سيتم ملؤها ديناميكياً حسب نوع التخزين -->
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="priority" class="form-label">الأولوية</label>
                                    <input type="number" class="form-control" id="priority" name="priority" value="{{ old('priority', 0) }}" min="0">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="max_backups" class="form-label">الحد الأقصى للنسخ</label>
                                    <input type="number" class="form-control" id="max_backups" name="max_backups" value="{{ old('max_backups') }}" min="1">
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">نشط</label>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> حفظ
                                </button>
                                <a href="{{ route('admin.backup-storage.index') }}" class="btn btn-secondary">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const driverSelect = document.getElementById('driver');
    const configFields = document.getElementById('config-fields');

    const configTemplates = {
        'local': '<div class="mb-3"><label class="form-label">المسار (اختياري)</label><input type="text" class="form-control" name="config[path]" value="backups"></div>',
        's3': `
            <div class="mb-3"><label class="form-label">Access Key ID <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[access_key_id]" required></div>
            <div class="mb-3"><label class="form-label">Secret Access Key <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[secret_access_key]" required></div>
            <div class="mb-3"><label class="form-label">Bucket <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[bucket]" required></div>
            <div class="mb-3"><label class="form-label">Region</label><input type="text" class="form-control" name="config[region]" value="us-east-1"></div>
        `,
        'google_drive': `
            <div class="mb-3"><label class="form-label">Client ID <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[client_id]" required></div>
            <div class="mb-3"><label class="form-label">Client Secret <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[client_secret]" required></div>
            <div class="mb-3"><label class="form-label">Refresh Token <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[refresh_token]" required></div>
        `,
        'dropbox': `
            <div class="mb-3"><label class="form-label">Access Token <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[access_token]" required></div>
        `,
        'ftp': `
            <div class="mb-3"><label class="form-label">Host <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[host]" required></div>
            <div class="mb-3"><label class="form-label">Username <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[username]" required></div>
            <div class="mb-3"><label class="form-label">Password <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[password]" required></div>
            <div class="mb-3"><label class="form-label">Port</label><input type="number" class="form-control" name="config[port]" value="21"></div>
            <div class="mb-3"><label class="form-label">Path</label><input type="text" class="form-control" name="config[path]" value="/backups"></div>
        `,
        'azure': `
            <div class="mb-3"><label class="form-label">Account Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[account_name]" required></div>
            <div class="mb-3"><label class="form-label">Account Key <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[account_key]" required></div>
            <div class="mb-3"><label class="form-label">Container <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[container]" required></div>
        `,
    };

    driverSelect.addEventListener('change', function() {
        const driver = this.value;
        if (configTemplates[driver]) {
            configFields.innerHTML = configTemplates[driver];
        } else {
            configFields.innerHTML = '';
        }
    });

    // تشغيل عند التحميل إذا كان هناك قيمة قديمة
    if (driverSelect.value) {
        driverSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@stop

