@extends('admin.layouts.master')

@section('page-title')
    إنشاء جدولة جديدة
@stop

@push('styles')
<style>
    .schedule-create-tip__item {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        padding: 0.85rem 0;
        border-bottom: 1px solid var(--default-border);
    }
    .schedule-create-tip__item:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }
    .schedule-create-tip__item:first-child {
        padding-top: 0;
    }
    .schedule-create-tip__icon {
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
    .schedule-option {
        border: 1.5px solid var(--default-border);
        border-radius: 12px;
        padding: 0.9rem 1rem;
        cursor: pointer;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        height: 100%;
        background: var(--custom-card-bg, var(--default-background));
        display: block;
        margin: 0;
    }
    .schedule-option:hover {
        border-color: rgba(var(--primary-rgb, 132, 90, 223), 0.45);
    }
    .schedule-option.is-selected {
        border-color: rgb(var(--primary-rgb, 132, 90, 223));
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 132, 90, 223), 0.14);
        background: rgba(var(--primary-rgb, 132, 90, 223), 0.04);
    }
    .schedule-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .schedule-option__title {
        font-weight: 700;
        margin-bottom: 0.15rem;
    }
    .schedule-option__meta {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin: 0;
    }
    .schedule-chip {
        border: 1.5px solid var(--default-border);
        border-radius: 10px;
        padding: 0.65rem 1rem;
        cursor: pointer;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        background: var(--custom-card-bg, var(--default-background));
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        margin: 0;
        min-width: 5.5rem;
        justify-content: center;
        font-weight: 600;
    }
    .schedule-chip:hover {
        border-color: rgba(var(--primary-rgb, 132, 90, 223), 0.45);
    }
    .schedule-chip.is-selected {
        border-color: rgb(var(--primary-rgb, 132, 90, 223));
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 132, 90, 223), 0.14);
        background: rgba(var(--primary-rgb, 132, 90, 223), 0.04);
        color: rgb(var(--primary-rgb, 132, 90, 223));
    }
    .schedule-chip input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .schedule-day-chip {
        border: 1.5px solid var(--default-border);
        border-radius: 10px;
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        transition: border-color 0.15s ease, background 0.15s ease;
        background: var(--custom-card-bg, var(--default-background));
        display: inline-flex;
        align-items: center;
        margin: 0;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .schedule-day-chip.is-selected {
        border-color: rgb(var(--primary-rgb, 132, 90, 223));
        background: rgba(var(--primary-rgb, 132, 90, 223), 0.08);
        color: rgb(var(--primary-rgb, 132, 90, 223));
    }
    .schedule-day-chip input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .schedule-section-title {
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .schedule-section-title i {
        color: rgb(var(--primary-rgb, 132, 90, 223));
    }
</style>
@endpush

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إنشاء جدولة جديدة</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.backup-schedules.index') }}">جدولة النسخ</a></li>
                        <li class="breadcrumb-item active" aria-current="page">إنشاء</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('admin.backup-schedules.index') }}" class="btn btn-secondary btn-sm">
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
                <strong>تعذر إنشاء الجدولة:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <form action="{{ route('admin.backup-schedules.store') }}" method="POST" id="schedule-create-form">
            @csrf
            <div class="row">
                <div class="col-xl-8">
                    <div class="card custom-card shadow-sm border-0 mb-4">
                        <div class="card-header">
                            <div class="card-title mb-0">بيانات الجدولة</div>
                        </div>
                        <div class="card-body">
                            <div class="schedule-section-title">
                                <i class="fe fe-file-text"></i>
                                المعلومات الأساسية
                            </div>

                            <div class="mb-4">
                                <label for="name" class="form-label">اسم الجدولة <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name') }}"
                                       placeholder="مثال: نسخة يومية لقاعدة البيانات"
                                       required>
                                <div class="form-text">اختر اسماً واضحاً يميّز هذه الجدولة عن غيرها.</div>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="backup_type" class="form-label">نوع النسخ <span class="text-danger">*</span></label>
                                    <select class="form-select @error('backup_type') is-invalid @enderror" id="backup_type" name="backup_type" required>
                                        @foreach($backupTypes as $key => $label)
                                            <option value="{{ $key }}" {{ old('backup_type', 'full') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('backup_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="frequency" class="form-label">التكرار <span class="text-danger">*</span></label>
                                    <select class="form-select @error('frequency') is-invalid @enderror" id="frequency" name="frequency" required>
                                        @foreach($frequencies as $key => $label)
                                            <option value="{{ $key }}" {{ old('frequency', 'daily') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('frequency')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="time" class="form-label">الوقت <span class="text-danger">*</span></label>
                                    <input type="time"
                                           class="form-control @error('time') is-invalid @enderror"
                                           id="time"
                                           name="time"
                                           value="{{ old('time', '02:00') }}"
                                           required>
                                    <div class="form-text">يفضّل وقت انخفاض الاستخدام (مثل منتصف الليل).</div>
                                    @error('time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-4" id="day_of_month_field" style="display: none;">
                                    <label for="day_of_month" class="form-label">يوم الشهر</label>
                                    <input type="number"
                                           class="form-control @error('day_of_month') is-invalid @enderror"
                                           id="day_of_month"
                                           name="day_of_month"
                                           value="{{ old('day_of_month', 1) }}"
                                           min="1"
                                           max="31">
                                    @error('day_of_month')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-4" id="days_of_week_field" style="display: none;">
                                <label class="form-label d-block">أيام الأسبوع</label>
                                <div class="d-flex gap-2 flex-wrap">
                                    @foreach([0 => 'الأحد', 1 => 'الإثنين', 2 => 'الثلاثاء', 3 => 'الأربعاء', 4 => 'الخميس', 5 => 'الجمعة', 6 => 'السبت'] as $day => $label)
                                        @php $isDaySelected = in_array($day, old('days_of_week', [])); @endphp
                                        <label class="schedule-day-chip {{ $isDaySelected ? 'is-selected' : '' }}" data-toggle-chip>
                                            <input type="checkbox"
                                                   name="days_of_week[]"
                                                   value="{{ $day }}"
                                                   id="day_{{ $day }}"
                                                   {{ $isDaySelected ? 'checked' : '' }}>
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <hr class="my-2">

                            <div class="schedule-section-title mt-4">
                                <i class="fe fe-hard-drive"></i>
                                أماكن التخزين
                            </div>

                            @if($storageDrivers->isEmpty())
                                <div class="alert alert-warning mb-0">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="fe fe-alert-triangle mt-1"></i>
                                        <div>
                                            <strong>لا توجد أماكن تخزين عامة نشطة.</strong>
                                            <p class="mb-2 mt-1">أضف مكان تخزين من إعدادات التخزين العامة أولاً، ثم ارجع لإنشاء الجدولة.</p>
                                            <a href="{{ route('admin.app-storage.configs.create') }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-plus me-1"></i> إضافة مكان تخزين
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="row g-3 mb-2">
                                    @foreach($storageDrivers as $config)
                                        @php
                                            $isSelected = in_array($config->id, array_map('intval', old('storage_drivers', [])));
                                            $driverLabel = \App\Models\AppStorageConfig::DRIVERS[$config->driver] ?? $config->driver;
                                        @endphp
                                        <div class="col-md-6">
                                            <label class="schedule-option {{ $isSelected ? 'is-selected' : '' }}" data-toggle-chip>
                                                <input type="checkbox"
                                                       name="storage_drivers[]"
                                                       value="{{ $config->id }}"
                                                       id="storage_{{ $config->id }}"
                                                       {{ $isSelected ? 'checked' : '' }}>
                                                <div class="d-flex justify-content-between align-items-start gap-2">
                                                    <div>
                                                        <div class="schedule-option__title">{{ $config->name }}</div>
                                                        <p class="schedule-option__meta">{{ $driverLabel }} · أولوية {{ $config->priority }}</p>
                                                    </div>
                                                    <span class="badge bg-primary-transparent">{{ strtoupper($config->driver) }}</span>
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('storage_drivers')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <div class="form-text">يمكن اختيار أكثر من مكان. تُحفظ ملفات النسخ تحت المسار <code>backups/</code>.</div>
                            @endif

                            <hr class="my-4">

                            <div class="schedule-section-title">
                                <i class="fe fe-package"></i>
                                الضغط والاحتفاظ
                            </div>

                            <div class="mb-4">
                                <label class="form-label d-block">أنواع الضغط <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2 flex-wrap">
                                    @foreach($compressionTypes as $key => $label)
                                        @php $isCompSelected = in_array($key, old('compression_types', ['zip'])); @endphp
                                        <label class="schedule-chip {{ $isCompSelected ? 'is-selected' : '' }}" data-toggle-chip>
                                            <input type="checkbox"
                                                   name="compression_types[]"
                                                   value="{{ $key }}"
                                                   id="comp_{{ $key }}"
                                                   {{ $isCompSelected ? 'checked' : '' }}>
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                                @error('compression_types')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
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
                                    <div class="form-text">المدة التي تبقى بعدها النسخ قبل التنظيف التلقائي.</div>
                                    @error('retention_days')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex flex-wrap gap-2 justify-content-between">
                            <a href="{{ route('admin.backup-schedules.index') }}" class="btn btn-light">
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary" {{ $storageDrivers->isEmpty() ? 'disabled' : '' }}>
                                <i class="fas fa-save me-1"></i> حفظ الجدولة
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card custom-card shadow-sm border-0 mb-4">
                        <div class="card-header">
                            <div class="card-title mb-0">ملاحظات سريعة</div>
                        </div>
                        <div class="card-body">
                            <div class="schedule-create-tip__item">
                                <span class="schedule-create-tip__icon"><i class="fe fe-clock"></i></span>
                                <div>
                                    <strong class="d-block mb-1">تشغيل تلقائي</strong>
                                    <span class="text-muted fs-13">تنفّذ الجدولة النسخ في الوقت المحدد دون تدخل يدوي.</span>
                                </div>
                            </div>
                            <div class="schedule-create-tip__item">
                                <span class="schedule-create-tip__icon"><i class="fe fe-folder"></i></span>
                                <div>
                                    <strong class="d-block mb-1">مسار منفصل</strong>
                                    <span class="text-muted fs-13">ملفات النسخ تُحفظ تحت <code>backups/</code> ولن تختلط بملفات الوسائط.</span>
                                </div>
                            </div>
                            <div class="schedule-create-tip__item">
                                <span class="schedule-create-tip__icon"><i class="fe fe-settings"></i></span>
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
                            <h6 class="mb-2">التكرار</h6>
                            <ul class="mb-0 ps-3 text-muted fs-13">
                                <li class="mb-1"><strong>يومي:</strong> كل يوم في الوقت المحدد</li>
                                <li class="mb-1"><strong>أسبوعي:</strong> في أيام الأسبوع التي تختارها</li>
                                <li><strong>شهري:</strong> في يوم محدد من كل شهر</li>
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
    const frequencySelect = document.getElementById('frequency');
    const daysOfWeekField = document.getElementById('days_of_week_field');
    const dayOfMonthField = document.getElementById('day_of_month_field');

    function toggleFields() {
        const value = frequencySelect.value;
        daysOfWeekField.style.display = value === 'weekly' ? 'block' : 'none';
        dayOfMonthField.style.display = value === 'monthly' ? 'block' : 'none';
    }

    frequencySelect.addEventListener('change', toggleFields);
    toggleFields();

    document.querySelectorAll('[data-toggle-chip]').forEach(function (chip) {
        chip.addEventListener('click', function (e) {
            e.preventDefault();
            const input = chip.querySelector('input[type="checkbox"]');
            if (!input) return;
            input.checked = !input.checked;
            chip.classList.toggle('is-selected', input.checked);
        });
    });
});
</script>
@endpush
