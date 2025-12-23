@extends('admin.layouts.master')

@section('page-title')
    تعديل إعدادات التخزين
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تعديل إعدادات التخزين: {{ $config->name }}</h5>
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
                        <form action="{{ route('admin.backup-storage.update', $config->id) }}" method="POST" id="storage-form">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="name" class="form-label">اسم الإعداد <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $config->name) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="driver" class="form-label">نوع التخزين <span class="text-danger">*</span></label>
                                <select class="form-select" id="driver" name="driver" required>
                                    @foreach($drivers as $key => $label)
                                        <option value="{{ $key }}" {{ old('driver', $config->driver) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="config-fields">
                                <!-- سيتم ملؤها ديناميكياً -->
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="priority" class="form-label">الأولوية</label>
                                    <input type="number" class="form-control" id="priority" name="priority" value="{{ old('priority', $config->priority) }}" min="0">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="max_backups" class="form-label">الحد الأقصى للنسخ</label>
                                    <input type="number" class="form-control" id="max_backups" name="max_backups" value="{{ old('max_backups', $config->max_backups) }}" min="1">
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $config->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">نشط</label>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> تحديث
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
    const currentConfig = @json($config->getDecryptedConfig() ?? []);

    const configTemplates = {
        'local': '<div class="mb-3"><label class="form-label">المسار (اختياري)</label><input type="text" class="form-control" name="config[path]" value="' + (currentConfig.path || 'backups') + '"></div>',
        's3': `
            <div class="mb-3"><label class="form-label">Access Key ID <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[access_key_id]" value="${currentConfig.access_key_id || ''}" required></div>
            <div class="mb-3"><label class="form-label">Secret Access Key <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[secret_access_key]" placeholder="اتركه فارغاً للحفاظ على القيمة الحالية"></div>
            <div class="mb-3"><label class="form-label">Bucket <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[bucket]" value="${currentConfig.bucket || ''}" required></div>
            <div class="mb-3"><label class="form-label">Region</label><input type="text" class="form-control" name="config[region]" value="${currentConfig.region || 'us-east-1'}"></div>
            <div class="mb-3"><label class="form-label">Endpoint (لـ S3-compatible)</label><input type="text" class="form-control" name="config[endpoint]" value="${currentConfig.endpoint || ''}" placeholder="https://s3.region.amazonaws.com"></div>
            <div class="mb-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="config[use_path_style]" value="1" id="use_path_style" ${currentConfig.use_path_style ? 'checked' : ''}><label class="form-check-label" for="use_path_style">Use Path Style Endpoint</label></div></div>
        `,
        'digitalocean': `
            <div class="mb-3"><label class="form-label">Access Key ID <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[access_key_id]" value="${currentConfig.access_key_id || ''}" required></div>
            <div class="mb-3"><label class="form-label">Secret Access Key <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[secret_access_key]" placeholder="اتركه فارغاً للحفاظ على القيمة الحالية"></div>
            <div class="mb-3"><label class="form-label">Bucket <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[bucket]" value="${currentConfig.bucket || ''}" required></div>
            <div class="mb-3"><label class="form-label">Region</label><select class="form-select" name="config[region]"><option value="nyc3" ${currentConfig.region == 'nyc3' ? 'selected' : ''}>NYC3</option><option value="nyc1" ${currentConfig.region == 'nyc1' ? 'selected' : ''}>NYC1</option><option value="sfo3" ${currentConfig.region == 'sfo3' ? 'selected' : ''}>SFO3</option><option value="sgp1" ${currentConfig.region == 'sgp1' ? 'selected' : ''}>SGP1</option><option value="sfo2" ${currentConfig.region == 'sfo2' ? 'selected' : ''}>SFO2</option><option value="ams3" ${currentConfig.region == 'ams3' ? 'selected' : ''}>AMS3</option></select></div>
        `,
        'wasabi': `
            <div class="mb-3"><label class="form-label">Access Key ID <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[access_key_id]" value="${currentConfig.access_key_id || ''}" required></div>
            <div class="mb-3"><label class="form-label">Secret Access Key <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[secret_access_key]" placeholder="اتركه فارغاً للحفاظ على القيمة الحالية"></div>
            <div class="mb-3"><label class="form-label">Bucket <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[bucket]" value="${currentConfig.bucket || ''}" required></div>
            <div class="mb-3"><label class="form-label">Region</label><select class="form-select" name="config[region]"><option value="us-east-1" ${currentConfig.region == 'us-east-1' ? 'selected' : ''}>US East 1</option><option value="us-west-1" ${currentConfig.region == 'us-west-1' ? 'selected' : ''}>US West 1</option><option value="eu-central-1" ${currentConfig.region == 'eu-central-1' ? 'selected' : ''}>EU Central 1</option><option value="ap-northeast-1" ${currentConfig.region == 'ap-northeast-1' ? 'selected' : ''}>AP Northeast 1</option></select></div>
        `,
        'backblaze': `
            <div class="mb-3"><label class="form-label">Access Key ID <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[access_key_id]" value="${currentConfig.access_key_id || ''}" required></div>
            <div class="mb-3"><label class="form-label">Secret Access Key <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[secret_access_key]" placeholder="اتركه فارغاً للحفاظ على القيمة الحالية"></div>
            <div class="mb-3"><label class="form-label">Bucket <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[bucket]" value="${currentConfig.bucket || ''}" required></div>
            <div class="mb-3"><label class="form-label">Region</label><input type="text" class="form-control" name="config[region]" value="${currentConfig.region || 'us-west-000'}" placeholder="us-west-000"></div>
        `,
        'cloudflare_r2': `
            <div class="mb-3"><label class="form-label">Account ID <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[account_id]" value="${currentConfig.account_id || ''}" required placeholder="Account ID من Cloudflare"></div>
            <div class="mb-3"><label class="form-label">Access Key ID <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[access_key_id]" value="${currentConfig.access_key_id || ''}" required></div>
            <div class="mb-3"><label class="form-label">Secret Access Key <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[secret_access_key]" placeholder="اتركه فارغاً للحفاظ على القيمة الحالية"></div>
            <div class="mb-3"><label class="form-label">Bucket <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[bucket]" value="${currentConfig.bucket || ''}" required></div>
        `,
        'google_drive': `
            <div class="mb-3"><label class="form-label">Client ID <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[client_id]" value="${currentConfig.client_id || ''}" required></div>
            <div class="mb-3"><label class="form-label">Client Secret <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[client_secret]" placeholder="اتركه فارغاً للحفاظ على القيمة الحالية"></div>
            <div class="mb-3"><label class="form-label">Refresh Token <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[refresh_token]" value="${currentConfig.refresh_token || ''}" required></div>
            <div class="mb-3"><label class="form-label">Folder ID (اختياري)</label><input type="text" class="form-control" name="config[folder_id]" value="${currentConfig.folder_id || ''}" placeholder="ID المجلد في Google Drive"></div>
        `,
        'dropbox': `
            <div class="mb-3"><label class="form-label">Access Token <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[access_token]" value="${currentConfig.access_token || ''}" required></div>
        `,
        'ftp': `
            <div class="mb-3"><label class="form-label">Protocol</label><select class="form-select" name="config[protocol]"><option value="ftp" ${currentConfig.protocol == 'ftp' ? 'selected' : ''}>FTP</option><option value="sftp" ${currentConfig.protocol == 'sftp' ? 'selected' : ''}>SFTP</option></select></div>
            <div class="mb-3"><label class="form-label">Host <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[host]" value="${currentConfig.host || ''}" required></div>
            <div class="mb-3"><label class="form-label">Username <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[username]" value="${currentConfig.username || ''}" required></div>
            <div class="mb-3"><label class="form-label">Password <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[password]" placeholder="اتركه فارغاً للحفاظ على القيمة الحالية"></div>
            <div class="mb-3"><label class="form-label">Port</label><input type="number" class="form-control" name="config[port]" value="${currentConfig.port || 21}" id="ftp_port"></div>
            <div class="mb-3"><label class="form-label">Root Path</label><input type="text" class="form-control" name="config[root]" value="${currentConfig.root || '/backups'}"></div>
            <div class="mb-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="config[use_tls]" value="1" id="use_tls" ${currentConfig.use_tls ? 'checked' : ''}><label class="form-check-label" for="use_tls">Use TLS</label></div></div>
            <div class="mb-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="config[passive]" value="1" id="passive" ${currentConfig.passive !== false ? 'checked' : ''}><label class="form-check-label" for="passive">Passive Mode</label></div></div>
        `,
        'sftp': `
            <div class="mb-3"><label class="form-label">Host <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[host]" value="${currentConfig.host || ''}" required></div>
            <div class="mb-3"><label class="form-label">Username <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[username]" value="${currentConfig.username || ''}" required></div>
            <div class="mb-3"><label class="form-label">Password <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[password]" placeholder="اتركه فارغاً للحفاظ على القيمة الحالية"></div>
            <div class="mb-3"><label class="form-label">Port</label><input type="number" class="form-control" name="config[port]" value="${currentConfig.port || 22}"></div>
            <div class="mb-3"><label class="form-label">Root Path</label><input type="text" class="form-control" name="config[root]" value="${currentConfig.root || '/backups'}"></div>
        `,
        'azure': `
            <div class="mb-3"><label class="form-label">Account Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[account_name]" value="${currentConfig.account_name || ''}" required></div>
            <div class="mb-3"><label class="form-label">Account Key <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[account_key]" placeholder="اتركه فارغاً للحفاظ على القيمة الحالية"></div>
            <div class="mb-3"><label class="form-label">Container <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[container]" value="${currentConfig.container || ''}" required></div>
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

    // تشغيل عند التحميل
    driverSelect.dispatchEvent(new Event('change'));
});
</script>
@endpush
@stop

