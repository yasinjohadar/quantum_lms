# قائمة ملفات نظام النسخ الاحتياطي والتخزين السحابي

هذا الملف يحتوي على جميع الملفات المتعلقة بنظام النسخ الاحتياطي (Backup) ونظام التخزين السحابي (Cloud Storage) لنقلها لمشروع آخر.

---

## 📦 نظام النسخ الاحتياطي (Backup System)

> ⚠️ **تحديث 2026-08**: نظام `BackupStorageConfig` القديم (تخزين خاص بالنسخ الاحتياطي فقط)
> أُزيل بالكامل واستُبدل بنظام التخزين العام الموحّد `AppStorageConfig` (قسم "نظام
> التخزين السحابي" أدناه). الملفات التالية **حُذفت فعلياً من المشروع** ولم تعد موجودة:
> `BackupStorageController.php`, `BackupStorageAnalyticsController.php`,
> `BackupStorageConfig.php`, `StorageAnalyticsService.php`,
> `resources/views/admin/pages/backup-storage/*`, وجدول `backup_storage_configs`
> (وجدول `storage_analytics` التابع له حصراً). أُزيلت من هذه القائمة أدناه.

### 🎯 Controllers
- `app/Http/Controllers/Admin/BackupController.php`
- `app/Http/Controllers/Admin/BackupScheduleController.php`

### 📊 Models
- `app/Models/Backup.php`
- `app/Models/BackupSchedule.php`
- `app/Models/BackupLog.php`

### ⚙️ Services
- `app/Services/Backup/BackupService.php`
- `app/Services/Backup/BackupStorageService.php`
- `app/Services/Backup/BackupScheduleService.php`
- `app/Services/Backup/BackupCompressionService.php`
- `app/Services/Backup/BackupNotificationService.php`
- `app/Services/Backup/StorageManager.php`
- `app/Services/Backup/StorageFactory.php`

### 🚗 Storage Drivers
- `app/Services/Backup/StorageDrivers/LocalStorageDriver.php`
- `app/Services/Backup/StorageDrivers/S3StorageDriver.php`
- `app/Services/Backup/StorageDrivers/GoogleDriveStorageDriver.php`
- `app/Services/Backup/StorageDrivers/DropboxStorageDriver.php`
- `app/Services/Backup/StorageDrivers/FTPStorageDriver.php`
- `app/Services/Backup/StorageDrivers/AzureStorageDriver.php`
- `app/Services/Backup/StorageDrivers/DigitalOceanStorageDriver.php`
- `app/Services/Backup/StorageDrivers/WasabiStorageDriver.php`
- `app/Services/Backup/StorageDrivers/BackblazeStorageDriver.php`
- `app/Services/Backup/StorageDrivers/CloudflareR2StorageDriver.php`

### 📝 Contracts/Interfaces
- `app/Contracts/BackupStorageInterface.php`

### 🔧 Jobs
- `app/Jobs/CreateBackupJob.php`

### 💻 Console Commands
- `app/Console/Commands/RunScheduledBackupsCommand.php`
- `app/Console/Commands/CleanupExpiredBackupsCommand.php`
- `app/Console/Commands/TestBackupStorageCommand.php`
- `app/Console/Commands/ReconcileStuckBackupsCommand.php`

### ⚙️ Config
- `config/backup.php` (webhook_url, job_timeout, sql_dump_chunk_size, schedule_lock_seconds, stuck_job_timeout_minutes, stuck_pending_timeout_minutes)

### 🗄️ Database Migrations
- `database/migrations/2025_12_22_175326_create_backups_table.php`
- `database/migrations/2025_12_22_175343_create_backup_schedules_table.php`
- `database/migrations/2025_12_22_175405_create_backup_logs_table.php`
- `database/migrations/2025_12_22_175600_add_foreign_key_to_backups_table.php`
- `database/migrations/2025_12_22_152112_add_schedule_id_to_backups_table.php`
- `database/migrations/2025_12_30_190104_make_storage_path_and_file_path_nullable_in_backups_table.php`
- `database/migrations/2026_07_31_000001_add_storage_config_id_to_backups_table.php`
- `database/migrations/2026_07_31_010001_unify_backup_schedule_id_on_backups_table.php`
- `database/migrations/2026_08_01_000001_backfill_backup_storage_config_id.php`
- `database/migrations/2026_08_01_000002_backfill_backup_schedule_storage_targets.php`
- `database/migrations/2026_08_02_000001_drop_legacy_backup_storage_columns.php`
- `database/migrations/2026_08_02_000002_drop_backup_storage_configs_table.php`

> ملاحظتان: (1) `2025_12_22_175354_create_backup_storage_configs_table.php` و
> `2025_12_23_051252_add_storage_analytics_to_backup_storage_configs_table.php`
> ما زالتا موجودتين تاريخياً (سجل الهجرات append-only ولا يُعدَّل) لكن الجدول
> الذي تنشئانه أُسقط لاحقاً — لا تنقلهما إلى مشروع جديد. (2) الترتيب أعلاه إلزامي:
> هجرتا backfill يجب أن تُشغَّلا وتُتحقَّق نتيجتهما قبل هجرتي drop.

### 🎨 Views
- `resources/views/admin/pages/backups/index.blade.php`
- `resources/views/admin/pages/backups/create.blade.php`
- `resources/views/admin/pages/backups/show.blade.php`
- `resources/views/admin/pages/backups/edit.blade.php` (إن وجد)
- `resources/views/admin/pages/backup-schedules/index.blade.php`
- `resources/views/admin/pages/backup-schedules/create.blade.php`
- `resources/views/admin/pages/backup-schedules/edit.blade.php` (إن وجد)

### 🛣️ Routes
في `routes/admin.php`:
- ابحث عن التعليق `// نظام النسخ الاحتياطي` (جميع routes النسخ الاحتياطي تليه مباشرة).
  لا تعتمد على أرقام أسطر ثابتة — تنزاح مع كل تعديل لاحق في الملف.

---

## ☁️ نظام التخزين السحابي (Cloud Storage System)

### 🎯 Controllers
- `app/Http/Controllers/Admin/AppStorageController.php`
- `app/Http/Controllers/Admin/AppStorageAnalyticsController.php`
- `app/Http/Controllers/Admin/StorageDiskMappingController.php`

### 📊 Models
- `app/Models/AppStorageConfig.php`
- `app/Models/AppStorageAnalytic.php`
- `app/Models/StorageDiskMapping.php`

### ⚙️ Services
- `app/Services/Storage/AppStorageManager.php`
- `app/Services/Storage/AppStorageFactory.php`
- `app/Services/Storage/AppStorageAnalyticsService.php`

### 🔧 Providers
- `app/Providers/StorageServiceProvider.php`

### 🛠️ Helpers
- `app/Helpers/StorageHelper.php`

### 🗄️ Database Migrations
- `database/migrations/2025_12_23_074328_create_app_storage_configs_table.php`
- `database/migrations/2025_12_23_074348_create_app_storage_analytics_table.php`
- `database/migrations/2025_12_23_074403_create_storage_disk_mappings_table.php`

### 🎨 Views
- `resources/views/admin/pages/app-storage/index.blade.php`
- `resources/views/admin/pages/app-storage/create.blade.php`
- `resources/views/admin/pages/app-storage/edit.blade.php`
- `resources/views/admin/pages/app-storage/analytics.blade.php`
- `resources/views/admin/pages/storage-disk-mappings/index.blade.php`
- `resources/views/admin/pages/storage-disk-mappings/create.blade.php` (إن وجد)
- `resources/views/admin/pages/storage-disk-mappings/edit.blade.php` (إن وجد)

### 🛣️ Routes
في `routes/admin.php`:
- ابحث عن التعليق `// App Storage` (ويليه `// Storage Migration` لمسارات ترحيل التخزين).
  لا تعتمد على أرقام أسطر ثابتة — تنزاح مع كل تعديل لاحق في الملف.

---

## 📋 ملفات إضافية قد تحتاجها

### ⚙️ Config Files
- `config/filesystems.php` (قد يحتوي على إعدادات التخزين)

### 📝 Sidebar Menu
في `resources/views/admin/layouts/main-sidebar.blade.php`:
- البحث عن روابط النسخ الاحتياطي والتخزين السحابي في القائمة الجانبية

### 📊 Dashboard
في `resources/views/admin/dashboard.blade.php`:
- أي widgets أو إحصائيات متعلقة بالنسخ الاحتياطي

---

## 📦 ملاحظات مهمة

1. **Dependencies**: تأكد من تثبيت جميع الحزم المطلوبة:
   - Laravel Storage Drivers
   - أي packages إضافية مستخدمة في Storage Drivers

2. **Environment Variables**: قد تحتاج إلى إضافة متغيرات البيئة في `.env`:
   - AWS credentials
   - Google Drive credentials
   - وغيرها من credentials للتخزين السحابي

3. **Service Provider**: تأكد من تسجيل `StorageServiceProvider` في `config/app.php`

4. **Console Commands**: تأكد من تسجيل الأوامر في `routes/console.php` إذا كانت مجدولة —
   **لا يوجد** `app/Console/Kernel.php` في هذا المشروع (Laravel 11 يستخدم أسلوب
   `Schedule::command(...)` مباشرة داخل `routes/console.php`)

5. **Jobs**: تأكد من إعداد queue system إذا كانت Jobs تستخدم queue

6. **Permissions**: تأكد من صلاحيات المجلدات:
   - `storage/app/backups`
   - `storage/app/temp`

---

## 🔍 للتحقق من الملفات

استخدم الأوامر التالية للتحقق من وجود جميع الملفات:

```bash
# للتحقق من Controllers
ls -la app/Http/Controllers/Admin/Backup*.php
ls -la app/Http/Controllers/Admin/*Storage*.php

# للتحقق من Models
ls -la app/Models/Backup*.php
ls -la app/Models/*Storage*.php

# للتحقق من Services
ls -la app/Services/Backup/
ls -la app/Services/Storage/

# للتحقق من Migrations
ls -la database/migrations/*backup*.php
ls -la database/migrations/*storage*.php
```

---

## ✅ قائمة التحقق (Checklist)

- [ ] جميع Controllers
- [ ] جميع Models
- [ ] جميع Services
- [ ] جميع Storage Drivers
- [ ] جميع Migrations
- [ ] جميع Views
- [ ] جميع Routes
- [ ] جميع Jobs
- [ ] جميع Console Commands
- [ ] Contracts/Interfaces
- [ ] Service Providers
- [ ] Helpers
- [ ] تحديث Sidebar Menu
- [ ] تحديث Dashboard (إن وجد)
- [ ] إعداد Environment Variables
- [ ] تسجيل Service Providers
- [ ] تسجيل Console Commands (في `routes/console.php`، وليس `app/Console/Kernel.php`)
- [ ] إعداد Queue (إن لزم الأمر)
