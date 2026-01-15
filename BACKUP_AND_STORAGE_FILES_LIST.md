# قائمة ملفات نظام النسخ الاحتياطي والتخزين السحابي

هذا الملف يحتوي على جميع الملفات المتعلقة بنظام النسخ الاحتياطي (Backup) ونظام التخزين السحابي (Cloud Storage) لنقلها لمشروع آخر.

---

## 📦 نظام النسخ الاحتياطي (Backup System)

### 🎯 Controllers
- `app/Http/Controllers/Admin/BackupController.php`
- `app/Http/Controllers/Admin/BackupScheduleController.php`
- `app/Http/Controllers/Admin/BackupStorageController.php`
- `app/Http/Controllers/Admin/BackupStorageAnalyticsController.php`

### 📊 Models
- `app/Models/Backup.php`
- `app/Models/BackupSchedule.php`
- `app/Models/BackupLog.php`
- `app/Models/BackupStorageConfig.php`

### ⚙️ Services
- `app/Services/Backup/BackupService.php`
- `app/Services/Backup/BackupStorageService.php`
- `app/Services/Backup/BackupScheduleService.php`
- `app/Services/Backup/BackupCompressionService.php`
- `app/Services/Backup/BackupNotificationService.php`
- `app/Services/Backup/StorageManager.php`
- `app/Services/Backup/StorageFactory.php`
- `app/Services/Backup/StorageAnalyticsService.php`

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

### 🗄️ Database Migrations
- `database/migrations/2025_12_22_175326_create_backups_table.php`
- `database/migrations/2025_12_22_175343_create_backup_schedules_table.php`
- `database/migrations/2025_12_22_175354_create_backup_storage_configs_table.php`
- `database/migrations/2025_12_22_175405_create_backup_logs_table.php`
- `database/migrations/2025_12_22_175600_add_foreign_key_to_backups_table.php`
- `database/migrations/2025_12_22_152112_add_schedule_id_to_backups_table.php`
- `database/migrations/2025_12_23_051252_add_storage_analytics_to_backup_storage_configs_table.php`
- `database/migrations/2025_12_30_190104_make_storage_path_and_file_path_nullable_in_backups_table.php`

### 🎨 Views
- `resources/views/admin/pages/backups/index.blade.php`
- `resources/views/admin/pages/backups/create.blade.php`
- `resources/views/admin/pages/backups/show.blade.php`
- `resources/views/admin/pages/backups/edit.blade.php` (إن وجد)
- `resources/views/admin/pages/backup-schedules/index.blade.php`
- `resources/views/admin/pages/backup-schedules/create.blade.php`
- `resources/views/admin/pages/backup-schedules/edit.blade.php` (إن وجد)
- `resources/views/admin/pages/backup-storage/index.blade.php`
- `resources/views/admin/pages/backup-storage/create.blade.php`
- `resources/views/admin/pages/backup-storage/edit.blade.php` (إن وجد)
- `resources/views/admin/pages/backup-storage/analytics.blade.php`

### 🛣️ Routes
في `routes/admin.php`:
- السطور 271-286 (جميع routes النسخ الاحتياطي)

---

## ☁️ نظام التخزين السحابي (Cloud Storage System)

### 🎯 Controllers
- `app/Http/Controllers/Admin/AppStorageController.php`
- `app/Http/Controllers/Admin/AppStorageAnalyticsController.php`
- `app/Http/Controllers/Admin/StorageDiskMappingController.php`

### 📊 Models
- `app/Models/AppStorageConfig.php`
- `app/Models/AppStorageAnalytic.php`
- `app/Models/StorageAnalytic.php`
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
- `database/migrations/2025_12_23_051309_create_storage_analytics_table.php`

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
- السطور 292-299 (جميع routes التخزين السحابي)

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

4. **Console Commands**: تأكد من تسجيل الأوامر في `app/Console/Kernel.php` إذا كانت مجدولة

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
- [ ] تسجيل Console Commands
- [ ] إعداد Queue (إن لزم الأمر)
