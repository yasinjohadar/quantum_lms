# دليل تكامل نظام النسخ الاحتياطي والتخزين السحابي - Cursor AI Guide

هذا الملف موجه لبرنامج Cursor AI لمساعدتك في ربط وتشغيل نظام النسخ الاحتياطي والتخزين السحابي في المشروع الجديد.

---

## 📋 نظرة عامة

تم نسخ جميع ملفات نظام النسخ الاحتياطي (Backup System) ونظام التخزين السحابي (Cloud Storage System) من المشروع الأصلي إلى المشروع الجديد في نفس المسارات. المطلوب الآن:

1. التحقق من وجود جميع الملفات
2. ربط الملفات في النظام (Routes, Service Providers, Commands)
3. إعداد المتطلبات (Dependencies, Config)
4. اختبار النظام

---

## 🔍 الخطوة 1: التحقق من وجود الملفات

### أ. Controllers - التحقق من:
```
app/Http/Controllers/Admin/BackupController.php
app/Http/Controllers/Admin/BackupScheduleController.php
app/Http/Controllers/Admin/BackupStorageController.php
app/Http/Controllers/Admin/BackupStorageAnalyticsController.php
app/Http/Controllers/Admin/AppStorageController.php
app/Http/Controllers/Admin/AppStorageAnalyticsController.php
app/Http/Controllers/Admin/StorageDiskMappingController.php
```

### ب. Models - التحقق من:
```
app/Models/Backup.php
app/Models/BackupSchedule.php
app/Models/BackupLog.php
app/Models/BackupStorageConfig.php
app/Models/AppStorageConfig.php
app/Models/AppStorageAnalytic.php
app/Models/StorageAnalytic.php
app/Models/StorageDiskMapping.php
```

### ج. Services - التحقق من:
```
app/Services/Backup/BackupService.php
app/Services/Backup/BackupStorageService.php
app/Services/Backup/BackupScheduleService.php
app/Services/Backup/BackupCompressionService.php
app/Services/Backup/BackupNotificationService.php
app/Services/Backup/StorageManager.php
app/Services/Backup/StorageFactory.php
app/Services/Backup/StorageAnalyticsService.php
app/Services/Storage/AppStorageManager.php
app/Services/Storage/AppStorageFactory.php
app/Services/Storage/AppStorageAnalyticsService.php
```

### د. Storage Drivers - التحقق من:
```
app/Services/Backup/StorageDrivers/LocalStorageDriver.php
app/Services/Backup/StorageDrivers/S3StorageDriver.php
app/Services/Backup/StorageDrivers/GoogleDriveStorageDriver.php
app/Services/Backup/StorageDrivers/DropboxStorageDriver.php
app/Services/Backup/StorageDrivers/FTPStorageDriver.php
app/Services/Backup/StorageDrivers/AzureStorageDriver.php
app/Services/Backup/StorageDrivers/DigitalOceanStorageDriver.php
app/Services/Backup/StorageDrivers/WasabiStorageDriver.php
app/Services/Backup/StorageDrivers/BackblazeStorageDriver.php
app/Services/Backup/StorageDrivers/CloudflareR2StorageDriver.php
```

### هـ. Contracts - التحقق من:
```
app/Contracts/BackupStorageInterface.php
```

### و. Jobs - التحقق من:
```
app/Jobs/CreateBackupJob.php
```

### ز. Console Commands - التحقق من:
```
app/Console/Commands/RunScheduledBackupsCommand.php
app/Console/Commands/CleanupExpiredBackupsCommand.php
app/Console/Commands/TestBackupStorageCommand.php
app/Console/Commands/TestAppStorageCommand.php
```

### ح. Providers - التحقق من:
```
app/Providers/StorageServiceProvider.php
```

### ط. Helpers - التحقق من:
```
app/Helpers/StorageHelper.php
```

### ي. Migrations - التحقق من:
```
database/migrations/2025_12_22_175326_create_backups_table.php
database/migrations/2025_12_22_175343_create_backup_schedules_table.php
database/migrations/2025_12_22_175354_create_backup_storage_configs_table.php
database/migrations/2025_12_22_175405_create_backup_logs_table.php
database/migrations/2025_12_22_175600_add_foreign_key_to_backups_table.php
database/migrations/2025_12_22_152112_add_schedule_id_to_backups_table.php
database/migrations/2025_12_23_051252_add_storage_analytics_to_backup_storage_configs_table.php
database/migrations/2025_12_30_190104_make_storage_path_and_file_path_nullable_in_backups_table.php
database/migrations/2025_12_23_074328_create_app_storage_configs_table.php
database/migrations/2025_12_23_074348_create_app_storage_analytics_table.php
database/migrations/2025_12_23_074403_create_storage_disk_mappings_table.php
database/migrations/2025_12_23_051309_create_storage_analytics_table.php
```

### ك. Views - التحقق من:
```
resources/views/admin/pages/backups/index.blade.php
resources/views/admin/pages/backups/create.blade.php
resources/views/admin/pages/backups/show.blade.php
resources/views/admin/pages/backup-schedules/index.blade.php
resources/views/admin/pages/backup-schedules/create.blade.php
resources/views/admin/pages/backup-storage/index.blade.php
resources/views/admin/pages/backup-storage/create.blade.php
resources/views/admin/pages/backup-storage/analytics.blade.php
resources/views/admin/pages/app-storage/index.blade.php
resources/views/admin/pages/app-storage/create.blade.php
resources/views/admin/pages/app-storage/edit.blade.php
resources/views/admin/pages/app-storage/analytics.blade.php
resources/views/admin/pages/storage-disk-mappings/index.blade.php
```

---

## 🔗 الخطوة 2: ربط الملفات في النظام

### 1. تسجيل Service Provider

**الملف:** `config/app.php`

**الإجراء:** ابحث عن قسم `providers` وأضف:
```php
App\Providers\StorageServiceProvider::class,
```

**الموقع:** يجب أن يكون بعد `AppServiceProvider` أو في نهاية قائمة providers.

### 2. إضافة Routes

**الملف:** `routes/admin.php`

**الإجراء:** ابحث عن نهاية ملف routes أو قسم مناسب وأضف:

```php
// ===============================================
// نظام النسخ الاحتياطي
// ===============================================
Route::resource('backups', \App\Http\Controllers\Admin\BackupController::class);
Route::post('backups/{backup}/restore', [\App\Http\Controllers\Admin\BackupController::class, 'restore'])->name('backups.restore');
Route::get('backups/{backup}/download', [\App\Http\Controllers\Admin\BackupController::class, 'download'])->name('backups.download');
Route::get('backups/stats', [\App\Http\Controllers\Admin\BackupController::class, 'stats'])->name('backups.stats');

Route::resource('backup-schedules', \App\Http\Controllers\Admin\BackupScheduleController::class);
Route::post('backup-schedules/{schedule}/execute', [\App\Http\Controllers\Admin\BackupScheduleController::class, 'execute'])->name('backup-schedules.execute');
Route::post('backup-schedules/{schedule}/toggle-active', [\App\Http\Controllers\Admin\BackupScheduleController::class, 'toggleActive'])->name('backup-schedules.toggle-active');

Route::resource('backup-storage', \App\Http\Controllers\Admin\BackupStorageController::class, ['except' => ['show']])->parameters(['backup-storage' => 'config']);
Route::post('backup-storage/{config}/test', [\App\Http\Controllers\Admin\BackupStorageController::class, 'test'])->name('backup-storage.test');
Route::post('backup-storage/test-connection', [\App\Http\Controllers\Admin\BackupStorageController::class, 'testConnection'])->name('backup-storage.test-connection');
Route::get('backup-storage/analytics', [\App\Http\Controllers\Admin\BackupStorageAnalyticsController::class, 'index'])->name('backup-storage.analytics');

// App Storage
Route::prefix('app-storage')->name('app-storage.')->group(function() {
    Route::resource('configs', \App\Http\Controllers\Admin\AppStorageController::class);
    Route::post('configs/{config}/test', [\App\Http\Controllers\Admin\AppStorageController::class, 'test'])->name('configs.test');
    Route::get('analytics', [\App\Http\Controllers\Admin\AppStorageAnalyticsController::class, 'index'])->name('analytics');
});

Route::resource('storage-disk-mappings', \App\Http\Controllers\Admin\StorageDiskMappingController::class);
```

**ملاحظة:** تأكد من أن هذه Routes موجودة داخل middleware group للـ admin.

### 3. تسجيل Console Commands

**الملف:** `app/Console/Kernel.php`

**الإجراء:** ابحث عن دالة `schedule` أو `commands` وأضف:

```php
protected $commands = [
    \App\Console\Commands\RunScheduledBackupsCommand::class,
    \App\Console\Commands\CleanupExpiredBackupsCommand::class,
    \App\Console\Commands\TestBackupStorageCommand::class,
    \App\Console\Commands\TestAppStorageCommand::class,
];
```

**أو في دالة `schedule` (إذا كانت مجدولة):**
```php
protected function schedule(Schedule $schedule)
{
    // تشغيل النسخ الاحتياطية المجدولة كل دقيقة
    $schedule->command('backups:run-scheduled')->everyMinute();
    
    // تنظيف النسخ المنتهية الصلاحية يومياً
    $schedule->command('backups:cleanup-expired')->daily();
}
```

### 4. إضافة القائمة الجانبية (Sidebar Menu)

**الملف:** `resources/views/admin/layouts/main-sidebar.blade.php`

**الإجراء:** ابحث عن قسم القائمة الجانبية وأضف روابط للنسخ الاحتياطي والتخزين:

```blade
{{-- النسخ الاحتياطية --}}
<li class="slide {{ request()->is('admin/backups*') || request()->is('admin/backup-*') ? 'active' : '' }}">
    <a href="javascript:void(0);" class="side-menu__item">
        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
            <path d="M19 7h-3V6a4 4 0 0 0-8 0v1H5a1 1 0 0 0-1 1v11a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V8a1 1 0 0 0-1-1zM10 6a2 2 0 0 1 4 0v1h-4V6zm8 13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V9h2v1a1 1 0 0 0 2 0V9h4v1a1 1 0 0 0 2 0V9h2v10z"/>
        </svg>
        <span class="side-menu__label">النسخ الاحتياطية</span>
        <i class="fe fe-chevron-left side-menu__angle"></i>
    </a>
    <ul class="slide-menu">
        <li><a href="{{ route('admin.backups.index') }}" class="slide-item">قائمة النسخ</a></li>
        <li><a href="{{ route('admin.backups.create') }}" class="slide-item">نسخة جديدة</a></li>
        <li><a href="{{ route('admin.backup-schedules.index') }}" class="slide-item">الجدولة</a></li>
        <li><a href="{{ route('admin.backup-storage.index') }}" class="slide-item">إعدادات التخزين</a></li>
    </ul>
</li>

{{-- التخزين السحابي --}}
<li class="slide {{ request()->is('admin/app-storage*') || request()->is('admin/storage-disk-mappings*') ? 'active' : '' }}">
    <a href="javascript:void(0);" class="side-menu__item">
        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
            <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
        </svg>
        <span class="side-menu__label">التخزين السحابي</span>
        <i class="fe fe-chevron-left side-menu__angle"></i>
    </a>
    <ul class="slide-menu">
        <li><a href="{{ route('admin.app-storage.configs.index') }}" class="slide-item">إعدادات التخزين</a></li>
        <li><a href="{{ route('admin.app-storage.analytics') }}" class="slide-item">الإحصائيات</a></li>
        <li><a href="{{ route('admin.storage-disk-mappings.index') }}" class="slide-item">ربط الأقراص</a></li>
    </ul>
</li>
```

---

## 📦 الخطوة 3: إعداد المتطلبات

### 1. Dependencies - التحقق من composer.json

**الملف:** `composer.json`

**الإجراء:** تأكد من وجود الحزم التالية (أضفها إذا لم تكن موجودة):

```json
{
    "require": {
        "league/flysystem-aws-s3-v3": "^3.0",
        "league/flysystem-google-cloud-storage": "^3.0",
        "spatie/laravel-backup": "^8.0",
        "league/flysystem-ftp": "^3.0",
        "league/flysystem-sftp-v3": "^3.0"
    }
}
```

**ملاحظة:** قد تحتاج حزم إضافية حسب Storage Drivers المستخدمة. راجع ملفات Storage Drivers للتحقق.

### 2. Environment Variables

**الملف:** `.env`

**الإجراء:** أضف المتغيرات التالية (إذا لم تكن موجودة):

```env
# AWS S3 (إذا كان مستخدماً)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=
AWS_URL=
AWS_ENDPOINT=
AWS_USE_PATH_STYLE_ENDPOINT=false

# Google Drive (إذا كان مستخدماً)
GOOGLE_DRIVE_CLIENT_ID=
GOOGLE_DRIVE_CLIENT_SECRET=
GOOGLE_DRIVE_REFRESH_TOKEN=

# Dropbox (إذا كان مستخدماً)
DROPBOX_ACCESS_TOKEN=

# Azure (إذا كان مستخدماً)
AZURE_ACCOUNT_NAME=
AZURE_ACCOUNT_KEY=
AZURE_CONTAINER=

# DigitalOcean Spaces (إذا كان مستخدماً)
DO_SPACES_KEY=
DO_SPACES_SECRET=
DO_SPACES_ENDPOINT=
DO_SPACES_REGION=
DO_SPACES_BUCKET=

# Wasabi (إذا كان مستخدماً)
WASABI_ACCESS_KEY_ID=
WASABI_SECRET_ACCESS_KEY=
WASABI_REGION=
WASABI_BUCKET=
WASABI_ENDPOINT=

# Backblaze B2 (إذا كان مستخدماً)
B2_ACCOUNT_ID=
B2_APPLICATION_KEY=
B2_BUCKET_NAME=

# Cloudflare R2 (إذا كان مستخدماً)
R2_ACCESS_KEY_ID=
R2_SECRET_ACCESS_KEY=
R2_BUCKET=
R2_ENDPOINT=
R2_ACCOUNT_ID=
```

### 3. إعداد المجلدات

**الإجراء:** تأكد من وجود المجلدات التالية مع الصلاحيات الصحيحة:

```bash
mkdir -p storage/app/backups
mkdir -p storage/app/temp
chmod -R 775 storage/app/backups
chmod -R 775 storage/app/temp
```

---

## 🗄️ الخطوة 4: تشغيل Migrations

**الإجراء:** قم بتشغيل migrations لإنشاء الجداول:

```bash
php artisan migrate
```

**ملاحظة:** تأكد من أن جميع migrations موجودة في `database/migrations/` قبل التشغيل.

---

## ✅ الخطوة 5: التحقق والاختبار

### 1. التحقق من Routes

```bash
php artisan route:list | grep backup
php artisan route:list | grep storage
```

### 2. التحقق من Service Provider

```bash
php artisan config:clear
php artisan cache:clear
```

### 3. اختبار النظام

1. افتح المتصفح وانتقل إلى `/admin/backups`
2. جرب إنشاء نسخة احتياطية
3. جرب إضافة مكان تخزين جديد
4. تحقق من أن القائمة الجانبية تعرض الروابط بشكل صحيح

---

## 🔧 إصلاح المشاكل الشائعة

### مشكلة: Class not found
**الحل:** 
- تأكد من وجود جميع الملفات في المسارات الصحيحة
- قم بتشغيل `composer dump-autoload`
- تحقق من namespaces في الملفات

### مشكلة: Route not found
**الحل:**
- تأكد من إضافة Routes في `routes/admin.php`
- قم بتشغيل `php artisan route:clear`
- تحقق من أن Routes موجودة داخل middleware group

### مشكلة: Service Provider not registered
**الحل:**
- تأكد من إضافة `StorageServiceProvider` في `config/app.php`
- قم بتشغيل `php artisan config:clear`

### مشكلة: Migration errors
**الحل:**
- تحقق من أن جميع migrations موجودة
- تحقق من ترتيب migrations (timestamps)
- تأكد من أن الجداول غير موجودة مسبقاً

### مشكلة: Storage Driver errors
**الحل:**
- تأكد من تثبيت الحزم المطلوبة عبر composer
- تحقق من Environment Variables
- راجع logs في `storage/logs/laravel.log`

---

## 📝 ملاحظات مهمة

1. **User Model**: تأكد من أن `User` model موجود وأن العلاقات مع Backup models صحيحة
2. **Permissions**: قد تحتاج إلى إضافة permissions للـ admin للوصول إلى النسخ الاحتياطي
3. **Queue**: إذا كنت تستخدم Queue للـ Jobs، تأكد من إعداد queue system
4. **Cron Jobs**: إذا كنت تستخدم scheduled backups، أضف cron job:
   ```bash
   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
   ```
5. **Storage Link**: تأكد من تشغيل:
   ```bash
   php artisan storage:link
   ```

---

## 🎯 قائمة التحقق النهائية

- [ ] جميع الملفات موجودة في المسارات الصحيحة
- [ ] Service Provider مسجل في `config/app.php`
- [ ] Routes مضافة في `routes/admin.php`
- [ ] Console Commands مسجلة في `app/Console/Kernel.php`
- [ ] Sidebar Menu محدث
- [ ] Dependencies مثبتة عبر composer
- [ ] Environment Variables مضافة
- [ ] المجلدات موجودة مع الصلاحيات الصحيحة
- [ ] Migrations تم تشغيلها بنجاح
- [ ] Routes تعمل بشكل صحيح
- [ ] النظام يعمل بدون أخطاء

---

## 📞 للمساعدة

إذا واجهت أي مشاكل:
1. راجع logs في `storage/logs/laravel.log`
2. تحقق من أن جميع الملفات موجودة
3. تأكد من أن namespaces صحيحة
4. تحقق من أن dependencies مثبتة

---

**تم إنشاء هذا الملف بواسطة:** Cursor AI Integration Guide
**التاريخ:** 2025
**الغرض:** دليل تكامل نظام النسخ الاحتياطي والتخزين السحابي
