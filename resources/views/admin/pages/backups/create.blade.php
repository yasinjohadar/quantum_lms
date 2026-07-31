@extends('admin.layouts.master')

@section('page-title')
    إنشاء نسخة احتياطية
@stop

@push('styles')
<style>
    .backup-create-tip {
        border: 1px solid var(--default-border);
        border-radius: 12px;
        background: var(--custom-card-bg, var(--default-background));
    }
    .backup-create-tip__item {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        padding: 0.85rem 0;
        border-bottom: 1px solid var(--default-border);
    }
    .backup-create-tip__item:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }
    .backup-create-tip__item:first-child {
        padding-top: 0;
    }
    .backup-create-tip__icon {
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
    .backup-storage-option {
        border: 1.5px solid var(--default-border);
        border-radius: 12px;
        padding: 0.9rem 1rem;
        cursor: pointer;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        height: 100%;
        background: var(--custom-card-bg, var(--default-background));
    }
    .backup-storage-option:hover {
        border-color: rgba(var(--primary-rgb, 132, 90, 223), 0.45);
    }
    .backup-storage-option.is-selected {
        border-color: rgb(var(--primary-rgb, 132, 90, 223));
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 132, 90, 223), 0.14);
        background: rgba(var(--primary-rgb, 132, 90, 223), 0.04);
    }
    .backup-storage-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .backup-storage-option__title {
        font-weight: 700;
        margin-bottom: 0.15rem;
    }
    .backup-storage-option__meta {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin: 0;
    }
    .backup-section-title {
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .backup-section-title i {
        color: rgb(var(--primary-rgb, 132, 90, 223));
    }
</style>
@endpush

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إنشاء نسخة احتياطية</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.backups.index') }}">النسخ الاحتياطية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">إنشاء</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
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

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>تعذر إنشاء النسخة:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <form action="{{ route('admin.backups.store') }}" method="POST" id="backup-create-form">
            @csrf
            <div class="row">
                <div class="col-xl-8">
                    <div class="card custom-card shadow-sm border-0 mb-4">
                        <div class="card-header">
                            <div class="card-title mb-0">بيانات النسخة</div>
                        </div>
                        <div class="card-body">
                            <div class="backup-section-title">
                                <i class="fe fe-file-text"></i>
                                المعلومات الأساسية
                            </div>

                            <div class="mb-4">
                                <label for="name" class="form-label">اسم النسخة <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', 'backup_' . now()->format('Y-m-d_H-i-s')) }}"
                                       required>
                                <div class="form-text">اسم واضح يساعدك على تمييز النسخة لاحقاً.</div>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="backup_type" class="form-label">نوع النسخ <span class="text-danger">*</span></label>
                                    <select class="form-select @error('backup_type') is-invalid @enderror" id="backup_type" name="backup_type" required>
                                        @foreach($backupTypes as $key => $label)
                                            <option value="{{ $key }}" {{ old('backup_type', 'database') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('backup_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="compression_type" class="form-label">نوع الضغط <span class="text-danger">*</span></label>
                                    <select class="form-select @error('compression_type') is-invalid @enderror" id="compression_type" name="compression_type" required>
                                        @foreach($compressionTypes as $key => $label)
                                            <option value="{{ $key }}" {{ old('compression_type', 'zip') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('compression_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-2">

                            <div class="backup-section-title mt-4">
                                <i class="fe fe-hard-drive"></i>
                                مكان التخزين
                            </div>

                            @if($storageDrivers->isEmpty())
                                <div class="alert alert-warning mb-0">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="fe fe-alert-triangle mt-1"></i>
                                        <div>
                                            <strong>لا توجد أماكن تخزين عامة نشطة.</strong>
                                            <p class="mb-2 mt-1">أضف مكان تخزين من إعدادات التخزين العامة أولاً، ثم ارجع لإنشاء النسخة.</p>
                                            <a href="{{ route('admin.app-storage.configs.create') }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-plus me-1"></i> إضافة مكان تخزين
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <input type="hidden" name="storage_config_id" id="storage_config_id" value="{{ old('storage_config_id') }}" required>
                                <div class="row g-3 mb-2">
                                    @foreach($storageDrivers as $config)
                                        @php
                                            $isSelected = (string) old('storage_config_id', $storageDrivers->first()->id) === (string) $config->id;
                                            $driverLabel = \App\Models\AppStorageConfig::DRIVERS[$config->driver] ?? $config->driver;
                                        @endphp
                                        <div class="col-md-6">
                                            <label class="backup-storage-option {{ $isSelected ? 'is-selected' : '' }}" data-storage-option data-id="{{ $config->id }}">
                                                <input type="radio"
                                                       name="storage_config_radio"
                                                       value="{{ $config->id }}"
                                                       {{ $isSelected ? 'checked' : '' }}>
                                                <div class="d-flex justify-content-between align-items-start gap-2">
                                                    <div>
                                                        <div class="backup-storage-option__title">{{ $config->name }}</div>
                                                        <p class="backup-storage-option__meta">{{ $driverLabel }} · أولوية {{ $config->priority }}</p>
                                                    </div>
                                                    <span class="badge bg-primary-transparent">{{ strtoupper($config->driver) }}</span>
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('storage_config_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <div class="form-text">تُستخدم أماكن التخزين العامة، وتُحفظ ملفات النسخ تحت المسار <code>backups/</code>.</div>
                            @endif

                            <hr class="my-4">

                            <div class="backup-section-title">
                                <i class="fe fe-clock"></i>
                                الاحتفاظ
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-1">
                                    <label for="retention_days" class="form-label">أيام الاحتفاظ <span class="text-danger">*</span></label>
                                    <input type="number"
                                           class="form-control @error('retention_days') is-invalid @enderror"
                                           id="retention_days"
                                           name="retention_days"
                                           value="{{ old('retention_days', 30) }}"
                                           min="1"
                                           max="365"
                                           required>
                                    <div class="form-text">بعد انتهاء المدة يمكن تنظيف النسخة تلقائياً حسب إعدادات النظام.</div>
                                    @error('retention_days')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex flex-wrap gap-2 justify-content-between">
                            <a href="{{ route('admin.backups.index') }}" class="btn btn-light">
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary" {{ $storageDrivers->isEmpty() ? 'disabled' : '' }}>
                                <i class="fas fa-database me-1"></i> إنشاء النسخة
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card custom-card backup-create-tip shadow-sm border-0 mb-4">
                        <div class="card-header">
                            <div class="card-title mb-0">ملاحظات سريعة</div>
                        </div>
                        <div class="card-body">
                            <div class="backup-create-tip__item">
                                <span class="backup-create-tip__icon"><i class="fe fe-shield"></i></span>
                                <div>
                                    <strong class="d-block mb-1">نسخ آمن</strong>
                                    <span class="text-muted fs-13">تُنشأ النسخة ثم تُضغط وتُرفع إلى مكان التخزين الذي تختاره.</span>
                                </div>
                            </div>
                            <div class="backup-create-tip__item">
                                <span class="backup-create-tip__icon"><i class="fe fe-folder"></i></span>
                                <div>
                                    <strong class="d-block mb-1">مسار منفصل</strong>
                                    <span class="text-muted fs-13">ملفات النسخ تُحفظ تحت <code>backups/</code> ولن تختلط بملفات الوسائط.</span>
                                </div>
                            </div>
                            <div class="backup-create-tip__item">
                                <span class="backup-create-tip__icon"><i class="fe fe-settings"></i></span>
                                <div>
                                    <strong class="d-block mb-1">إدارة الأماكن</strong>
                                    <span class="text-muted fs-13">لإضافة أو تعديل مكان تخزين استخدم
                                        <a href="{{ route('admin.app-storage.configs.index') }}">إعدادات التخزين العامة</a>.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="mb-2">أنواع النسخ</h6>
                            <ul class="mb-0 ps-3 text-muted fs-13">
                                <li class="mb-1"><strong>كامل:</strong> قاعدة البيانات والملفات والإعدادات</li>
                                <li class="mb-1"><strong>قاعدة البيانات:</strong> الجداول والبيانات فقط</li>
                                <li class="mb-1"><strong>الملفات:</strong> الملفات المخزّنة محلياً</li>
                                <li><strong>الإعدادات:</strong> ملفات الإعدادات الأساسية</li>
                            </ul>
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
    const hiddenInput = document.getElementById('storage_config_id');
    const options = document.querySelectorAll('[data-storage-option]');

    function selectOption(option) {
        options.forEach((el) => el.classList.remove('is-selected'));
        option.classList.add('is-selected');
        const radio = option.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
        if (hiddenInput) hiddenInput.value = option.dataset.id;
    }

    options.forEach((option) => {
        option.addEventListener('click', function () {
            selectOption(option);
        });
    });

    const initiallySelected = document.querySelector('[data-storage-option].is-selected');
    if (initiallySelected) {
        selectOption(initiallySelected);
    }
});
</script>
@endpush
