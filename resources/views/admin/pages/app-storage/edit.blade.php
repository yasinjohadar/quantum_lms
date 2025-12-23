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
                <a href="{{ route('admin.app-storage.configs.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form action="{{ route('admin.app-storage.configs.update', $config->id) }}" method="POST" id="storage-form">
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
                                    <label for="cdn_url" class="form-label">رابط CDN (اختياري)</label>
                                    <input type="url" class="form-control" id="cdn_url" name="cdn_url" value="{{ old('cdn_url', $config->cdn_url) }}" placeholder="https://cdn.example.com">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">أنواع الملفات المدعومة (اختياري)</label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="file_types[]" value="image" id="file_type_image" {{ in_array('image', $config->file_types ?? []) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="file_type_image">صور</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="file_types[]" value="document" id="file_type_document" {{ in_array('document', $config->file_types ?? []) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="file_type_document">وثائق</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="file_types[]" value="video" id="file_type_video" {{ in_array('video', $config->file_types ?? []) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="file_type_video">فيديو</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $config->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">نشط</label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="redundancy" name="redundancy" value="1" {{ old('redundancy', $config->redundancy) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="redundancy">تفعيل التخزين المتعدد (Redundancy)</label>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6>إعدادات التسعير (اختياري)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">تكلفة التخزين لكل GB</label>
                                            <input type="number" step="0.01" class="form-control" name="pricing_config[storage_cost_per_gb]" value="{{ old('pricing_config.storage_cost_per_gb', $config->pricing_config['storage_cost_per_gb'] ?? '') }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">تكلفة الرفع لكل GB</label>
                                            <input type="number" step="0.01" class="form-control" name="pricing_config[upload_cost_per_gb]" value="{{ old('pricing_config.upload_cost_per_gb', $config->pricing_config['upload_cost_per_gb'] ?? '') }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">تكلفة التحميل لكل GB</label>
                                            <input type="number" step="0.01" class="form-control" name="pricing_config[download_cost_per_gb]" value="{{ old('pricing_config.download_cost_per_gb', $config->pricing_config['download_cost_per_gb'] ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">الميزانية الشهرية</label>
                                            <input type="number" step="0.01" class="form-control" name="monthly_budget" value="{{ old('monthly_budget', $config->monthly_budget) }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">حد تنبيه التكلفة</label>
                                            <input type="number" step="0.01" class="form-control" name="cost_alert_threshold" value="{{ old('cost_alert_threshold', $config->cost_alert_threshold) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> تحديث
                                </button>
                                <a href="{{ route('admin.app-storage.configs.index') }}" class="btn btn-secondary">
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
        'local': '<div class="mb-3"><label class="form-label">المسار (اختياري)</label><input type="text" class="form-control" name="config[path]" value="' + (currentConfig.path || 'public') + '"></div>',
        's3': `
            <div class="mb-3"><label class="form-label">Access Key ID <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[access_key_id]" value="${currentConfig.access_key_id || ''}" required></div>
            <div class="mb-3"><label class="form-label">Secret Access Key <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[secret_access_key]" placeholder="اتركه فارغاً للحفاظ على القيمة الحالية"></div>
            <div class="mb-3"><label class="form-label">Bucket <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[bucket]" value="${currentConfig.bucket || ''}" required></div>
            <div class="mb-3"><label class="form-label">Region</label><input type="text" class="form-control" name="config[region]" value="${currentConfig.region || 'us-east-1'}"></div>
            <div class="mb-3"><label class="form-label">Endpoint (لـ S3-compatible)</label><input type="text" class="form-control" name="config[endpoint]" value="${currentConfig.endpoint || ''}" placeholder="https://s3.region.amazonaws.com"></div>
            <div class="mb-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="config[use_path_style]" value="1" id="use_path_style" ${currentConfig.use_path_style ? 'checked' : ''}><label class="form-check-label" for="use_path_style">Use Path Style Endpoint</label></div></div>
        `,
        'cloudflare_r2': `
            <div class="mb-3"><label class="form-label">Account ID <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[account_id]" value="${currentConfig.account_id || ''}" required placeholder="Account ID من Cloudflare"></div>
            <div class="mb-3"><label class="form-label">Access Key ID <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[access_key_id]" value="${currentConfig.access_key_id || ''}" required></div>
            <div class="mb-3"><label class="form-label">Secret Access Key <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[secret_access_key]" placeholder="اتركه فارغاً للحفاظ على القيمة الحالية"></div>
            <div class="mb-3"><label class="form-label">Bucket <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[bucket]" value="${currentConfig.bucket || ''}" required></div>
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

    driverSelect.dispatchEvent(new Event('change'));
});
</script>
@endpush
@stop

