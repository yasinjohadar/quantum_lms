@extends('admin.layouts.master')

@section('page-title')
    تعديل إعدادات التخزين
@stop

@push('styles')
<style>
    .storage-section-title {
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .storage-section-title i {
        color: rgb(var(--primary-rgb, 132, 90, 223));
    }
    .storage-tip__item {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        padding: 0.85rem 0;
        border-bottom: 1px solid var(--default-border);
    }
    .storage-tip__item:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }
    .storage-tip__item:first-child {
        padding-top: 0;
    }
    .storage-tip__icon {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: rgba(var(--primary-rgb, 132, 90, 223), 0.12);
        color: rgb(var(--primary-rgb, 132, 90, 223));
    }
    .storage-test-result {
        display: none;
        margin-top: 0.75rem;
    }
    .storage-file-chip {
        border: 1.5px solid var(--default-border);
        border-radius: 10px;
        padding: 0.65rem 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        height: 100%;
        background: var(--custom-card-bg, var(--default-background));
    }
</style>
@endpush

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تعديل إعدادات التخزين</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.app-storage.configs.index') }}">التخزين العام</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $config->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
                <button type="button" class="btn btn-outline-warning btn-sm" id="test-connection-btn">
                    <i class="fas fa-vial me-1"></i> اختبار الاتصال
                </button>
                <a href="{{ route('admin.app-storage.configs.index') }}" class="btn btn-secondary btn-sm">
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

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>تعذر حفظ الإعدادات:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div id="storage-test-alert" class="alert storage-test-result" role="alert"></div>

        <form action="{{ route('admin.app-storage.configs.update', $config) }}" method="POST" id="storage-form">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-xl-8">
                    <div class="card custom-card shadow-sm border-0 mb-4">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div class="card-title mb-0">بيانات مكان التخزين</div>
                            <span class="badge bg-primary-transparent">{{ strtoupper($config->driver) }}</span>
                        </div>
                        <div class="card-body">
                            <div class="storage-section-title">
                                <i class="fe fe-info"></i>
                                المعلومات الأساسية
                            </div>

                            <div class="row">
                                <div class="col-md-7 mb-4">
                                    <label for="name" class="form-label">اسم الإعداد <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name', $config->name) }}"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-5 mb-4">
                                    <label for="driver" class="form-label">نوع التخزين <span class="text-danger">*</span></label>
                                    <select class="form-select @error('driver') is-invalid @enderror" id="driver" name="driver" required>
                                        @foreach($drivers as $key => $label)
                                            <option value="{{ $key }}" {{ old('driver', $config->driver) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('driver')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-2">

                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-4 mb-3">
                                <div class="storage-section-title mb-0">
                                    <i class="fe fe-lock"></i>
                                    بيانات الاتصال
                                </div>
                                <button type="button" class="btn btn-warning btn-sm" id="test-connection-btn-inline">
                                    <i class="fas fa-vial me-1"></i> اختبار الاتصال
                                </button>
                            </div>
                            <p class="text-muted fs-13 mb-3">
                                اختبار الاتصال يعتمد على القيم <strong>المحفوظة حالياً</strong>. احفظ التعديلات أولاً قبل الاختبار إن غيّرت المفاتيح.
                            </p>

                            <div id="config-fields" class="mb-2"></div>

                            <hr class="my-4">

                            <div class="storage-section-title">
                                <i class="fe fe-settings"></i>
                                الخيارات العامة
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <label for="priority" class="form-label">الأولوية</label>
                                    <input type="number"
                                           class="form-control"
                                           id="priority"
                                           name="priority"
                                           value="{{ old('priority', $config->priority) }}"
                                           min="0">
                                    <div class="form-text">الأعلى أولوية يُفضَّل عند التحويل التلقائي.</div>
                                </div>
                                <div class="col-md-8 mb-4">
                                    <label for="cdn_url" class="form-label">رابط CDN (اختياري)</label>
                                    <input type="url"
                                           class="form-control"
                                           id="cdn_url"
                                           name="cdn_url"
                                           value="{{ old('cdn_url', $config->cdn_url) }}"
                                           placeholder="https://cdn.example.com">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label d-block">أنواع الملفات المدعومة</label>
                                <div class="row g-2">
                                    @foreach([
                                        'image' => 'صور',
                                        'document' => 'وثائق',
                                        'video' => 'فيديو',
                                    ] as $type => $label)
                                        <div class="col-md-4">
                                            <label class="storage-file-chip">
                                                <input class="form-check-input m-0"
                                                       type="checkbox"
                                                       name="file_types[]"
                                                       value="{{ $type }}"
                                                       id="file_type_{{ $type }}"
                                                       {{ in_array($type, old('file_types', $config->file_types ?? []), true) ? 'checked' : '' }}>
                                                <span>{{ $label }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               id="is_active"
                                               name="is_active"
                                               value="1"
                                               {{ old('is_active', $config->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">نشط</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               id="redundancy"
                                               name="redundancy"
                                               value="1"
                                               {{ old('redundancy', $config->redundancy) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="redundancy">تفعيل التخزين المتعدد (Redundancy)</label>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="storage-section-title">
                                <i class="fe fe-dollar-sign"></i>
                                إعدادات التسعير (اختياري)
                            </div>

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
                        <div class="card-footer d-flex flex-wrap gap-2 justify-content-between">
                            <a href="{{ route('admin.app-storage.configs.index') }}" class="btn btn-light">إلغاء</a>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-outline-warning" id="test-connection-btn-footer">
                                    <i class="fas fa-vial me-1"></i> اختبار الاتصال
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> حفظ التعديلات
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card custom-card shadow-sm border-0 mb-4">
                        <div class="card-header">
                            <div class="card-title mb-0">ملخص سريع</div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="text-muted fs-13">الاسم</div>
                                <strong>{{ $config->name }}</strong>
                            </div>
                            <div class="mb-3">
                                <div class="text-muted fs-13">النوع</div>
                                <strong>{{ $drivers[$config->driver] ?? $config->driver }}</strong>
                            </div>
                            <div class="mb-3">
                                <div class="text-muted fs-13">الحالة</div>
                                @if($config->is_active)
                                    <span class="badge bg-success-transparent">نشط</span>
                                @else
                                    <span class="badge bg-secondary-transparent">غير نشط</span>
                                @endif
                            </div>
                            <div>
                                <div class="text-muted fs-13">الأولوية</div>
                                <strong>{{ $config->priority }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card shadow-sm border-0">
                        <div class="card-header">
                            <div class="card-title mb-0">ملاحظات</div>
                        </div>
                        <div class="card-body">
                            <div class="storage-tip__item">
                                <span class="storage-tip__icon"><i class="fe fe-shield"></i></span>
                                <div>
                                    <strong class="d-block mb-1">المفاتيح السرية</strong>
                                    <span class="text-muted fs-13">اترك الحقول السرية فارغة للإبقاء على القيمة المحفوظة.</span>
                                </div>
                            </div>
                            <div class="storage-tip__item">
                                <span class="storage-tip__icon"><i class="fe fe-activity"></i></span>
                                <div>
                                    <strong class="d-block mb-1">اختبار الاتصال</strong>
                                    <span class="text-muted fs-13">يتحقق من إمكانية الوصول للحاوية/المسار بالإعدادات المحفوظة.</span>
                                </div>
                            </div>
                            <div class="storage-tip__item">
                                <span class="storage-tip__icon"><i class="fe fe-folder"></i></span>
                                <div>
                                    <strong class="d-block mb-1">النسخ الاحتياطية</strong>
                                    <span class="text-muted fs-13">تُحفظ ملفات النسخ تحت المسار <code>backups/</code> داخل هذا المكان.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const driverSelect = document.getElementById('driver');
    const configFields = document.getElementById('config-fields');
    const currentConfig = @json($config->getDecryptedConfig() ?? []);
    const testUrl = @json(route('admin.app-storage.configs.test', $config));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
        || @json(csrf_token());

    function esc(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function secretField(name, label) {
        return `
            <div class="mb-3">
                <label class="form-label">${label}</label>
                <input type="password" class="form-control" name="config[${name}]" value="" autocomplete="new-password" placeholder="اتركه فارغاً للإبقاء على القيمة الحالية">
                <div class="form-text">للحفاظ على المفتاح الحالي اترك الحقل فارغاً.</div>
            </div>
        `;
    }

    const configTemplates = {
        local: `
            <div class="mb-3">
                <label class="form-label">المسار (اختياري)</label>
                <input type="text" class="form-control" name="config[path]" value="${esc(currentConfig.path || 'public')}">
            </div>
        `,
        s3: `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Access Key ID <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="config[access_key_id]" value="${esc(currentConfig.access_key_id || '')}" required>
                </div>
                <div class="col-md-6">${secretField('secret_access_key', 'Secret Access Key')}</div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bucket <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="config[bucket]" value="${esc(currentConfig.bucket || '')}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Region</label>
                    <input type="text" class="form-control" name="config[region]" value="${esc(currentConfig.region || 'us-east-1')}">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Endpoint (لـ S3-compatible)</label>
                    <input type="text" class="form-control" name="config[endpoint]" value="${esc(currentConfig.endpoint || '')}" placeholder="https://s3.region.amazonaws.com">
                </div>
                <div class="col-12 mb-1">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="config[use_path_style]" value="1" id="use_path_style" ${currentConfig.use_path_style ? 'checked' : ''}>
                        <label class="form-check-label" for="use_path_style">Use Path Style Endpoint</label>
                    </div>
                </div>
            </div>
        `,
        digitalocean: `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Access Key ID <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="config[access_key_id]" value="${esc(currentConfig.access_key_id || '')}" required>
                </div>
                <div class="col-md-6">${secretField('secret_access_key', 'Secret Access Key')}</div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bucket <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="config[bucket]" value="${esc(currentConfig.bucket || '')}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Region</label>
                    <input type="text" class="form-control" name="config[region]" value="${esc(currentConfig.region || 'nyc3')}">
                </div>
            </div>
        `,
        wasabi: `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Access Key ID <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="config[access_key_id]" value="${esc(currentConfig.access_key_id || '')}" required>
                </div>
                <div class="col-md-6">${secretField('secret_access_key', 'Secret Access Key')}</div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bucket <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="config[bucket]" value="${esc(currentConfig.bucket || '')}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Region</label>
                    <input type="text" class="form-control" name="config[region]" value="${esc(currentConfig.region || 'us-east-1')}">
                </div>
            </div>
        `,
        backblaze: `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Access Key ID <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="config[access_key_id]" value="${esc(currentConfig.access_key_id || '')}" required>
                </div>
                <div class="col-md-6">${secretField('secret_access_key', 'Secret Access Key')}</div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bucket <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="config[bucket]" value="${esc(currentConfig.bucket || '')}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Region</label>
                    <input type="text" class="form-control" name="config[region]" value="${esc(currentConfig.region || 'us-west-000')}">
                </div>
            </div>
        `,
        cloudflare_r2: `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Account ID <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="config[account_id]" value="${esc(currentConfig.account_id || '')}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Access Key ID <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="config[access_key_id]" value="${esc(currentConfig.access_key_id || '')}" required>
                </div>
                <div class="col-md-6">${secretField('secret_access_key', 'Secret Access Key')}</div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bucket <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="config[bucket]" value="${esc(currentConfig.bucket || '')}" required>
                </div>
            </div>
        `,
        google_drive: `
            <div class="mb-3">
                <label class="form-label">Client ID <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="config[client_id]" value="${esc(currentConfig.client_id || '')}" required>
            </div>
            ${secretField('client_secret', 'Client Secret')}
            <div class="mb-3">
                <label class="form-label">Refresh Token <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="config[refresh_token]" value="${esc(currentConfig.refresh_token || '')}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Folder ID (اختياري)</label>
                <input type="text" class="form-control" name="config[folder_id]" value="${esc(currentConfig.folder_id || '')}">
            </div>
        `,
        dropbox: `${secretField('access_token', 'Access Token')}`,
        ftp: `
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Protocol</label>
                    <select class="form-select" name="config[protocol]">
                        <option value="ftp" ${currentConfig.protocol === 'ftp' ? 'selected' : ''}>FTP</option>
                        <option value="sftp" ${currentConfig.protocol === 'sftp' ? 'selected' : ''}>SFTP</option>
                    </select>
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Host <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="config[host]" value="${esc(currentConfig.host || '')}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="config[username]" value="${esc(currentConfig.username || '')}" required>
                </div>
                <div class="col-md-6">${secretField('password', 'Password')}</div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Port</label>
                    <input type="number" class="form-control" name="config[port]" value="${esc(currentConfig.port || 21)}">
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Root Path</label>
                    <input type="text" class="form-control" name="config[root]" value="${esc(currentConfig.root || '/')}">
                </div>
            </div>
        `,
        sftp: `
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">Host <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="config[host]" value="${esc(currentConfig.host || '')}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Port</label>
                    <input type="number" class="form-control" name="config[port]" value="${esc(currentConfig.port || 22)}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="config[username]" value="${esc(currentConfig.username || '')}" required>
                </div>
                <div class="col-md-6">${secretField('password', 'Password')}</div>
                <div class="col-12 mb-3">
                    <label class="form-label">Root Path</label>
                    <input type="text" class="form-control" name="config[root]" value="${esc(currentConfig.root || '/')}">
                </div>
            </div>
        `,
        azure: `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Account Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="config[account_name]" value="${esc(currentConfig.account_name || '')}" required>
                </div>
                <div class="col-md-6">${secretField('account_key', 'Account Key')}</div>
                <div class="col-12 mb-3">
                    <label class="form-label">Container <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="config[container]" value="${esc(currentConfig.container || '')}" required>
                </div>
            </div>
        `,
    };

    function renderFields() {
        const driver = driverSelect.value;
        configFields.innerHTML = configTemplates[driver] || '<div class="alert alert-light mb-0">لا توجد حقول إضافية لهذا النوع.</div>';
    }

    driverSelect.addEventListener('change', renderFields);
    renderFields();

    const testAlert = document.getElementById('storage-test-alert');

    async function runConnectionTest(button) {
        const buttons = [
            document.getElementById('test-connection-btn'),
            document.getElementById('test-connection-btn-inline'),
            document.getElementById('test-connection-btn-footer'),
        ].filter(Boolean);

        buttons.forEach((btn) => {
            btn.disabled = true;
        });

        const original = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> جاري الاختبار...';

        testAlert.style.display = 'none';
        testAlert.className = 'alert storage-test-result';

        try {
            const response = await fetch(testUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({}),
            });

            const data = await response.json();
            testAlert.style.display = 'block';

            if (data.success) {
                testAlert.classList.add('alert-success');
                testAlert.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + (data.message || 'الاتصال ناجح');
            } else {
                testAlert.classList.add('alert-danger');
                testAlert.innerHTML = '<i class="fas fa-times-circle me-2"></i>' + (data.message || 'فشل الاتصال');
            }

            testAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } catch (error) {
            testAlert.style.display = 'block';
            testAlert.classList.add('alert-danger');
            testAlert.innerHTML = '<i class="fas fa-times-circle me-2"></i>حدث خطأ أثناء الاختبار: ' + error.message;
            testAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } finally {
            buttons.forEach((btn) => {
                btn.disabled = false;
            });
            button.innerHTML = original;
        }
    }

    [
        document.getElementById('test-connection-btn'),
        document.getElementById('test-connection-btn-inline'),
        document.getElementById('test-connection-btn-footer'),
    ].forEach((btn) => {
        if (!btn) return;
        btn.addEventListener('click', function () {
            runConnectionTest(btn);
        });
    });
});
</script>
@endpush
