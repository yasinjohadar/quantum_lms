# نظام التقارير والتحليلات الشامل

## نظرة عامة

تم إنشاء نظام شامل ومرن للتقارير والتحليلات يوفر:
- تقارير تفصيلية قابلة للتخصيص (طلاب، كورسات، نظام)
- رسوم بيانية متقدمة (جاهزة لـ ApexCharts)
- تصدير التقارير (PDF, Excel, Print - جاهز للتكامل)
- لوحة تحكم متقدمة للادمن مع إعدادات مرنة
- نظام تحليلات متقدم لتتبع السلوك والأداء

## الملفات المُنشأة

### Models
- `app/Models/ReportTemplate.php` - قوالب التقارير
- `app/Models/ReportSchedule.php` - جدولة التقارير
- `app/Models/DashboardWidget.php` - ودجت لوحة التحكم
- `app/Models/AnalyticsEvent.php` - أحداث التحليلات
- `app/Models/SystemSetting.php` - إعدادات النظام
- `app/Models/CustomField.php` - حقول مخصصة

### Migrations
- `database/migrations/2025_12_21_000001_create_report_templates_table.php`
- `database/migrations/2025_12_21_000002_create_report_schedules_table.php`
- `database/migrations/2025_12_21_000003_create_dashboard_widgets_table.php`
- `database/migrations/2025_12_21_000004_create_analytics_events_table.php`
- `database/migrations/2025_12_21_000005_create_system_settings_table.php`
- `database/migrations/2025_12_21_000006_create_custom_fields_table.php`

### Services
- `app/Services/ReportBuilderService.php` - بناء التقارير
- `app/Services/ReportGeneratorService.php` - توليد التقارير (PDF/Excel/Print)
- `app/Services/ChartDataService.php` - بيانات الرسوم البيانية
- `app/Services/AnalyticsService.php` - التحليلات والتتبع
- `app/Services/AdminDashboardService.php` - لوحة تحكم الادمن

### Controllers
- `app/Http/Controllers/Admin/ReportController.php` - إدارة التقارير
- `app/Http/Controllers/Admin/ReportTemplateController.php` - إدارة القوالب
- `app/Http/Controllers/Admin/AnalyticsController.php` - API للتحليلات
- `app/Http/Controllers/Admin/SettingsController.php` - الإعدادات
- `app/Http/Controllers/Admin/AdminDashboardController.php` - لوحة التحكم
- `app/Http/Controllers/Student/StudentReportController.php` - تقارير الطالب

### Views
#### Admin
- `resources/views/admin/pages/reports/index.blade.php` - قائمة التقارير
- `resources/views/admin/pages/reports/create.blade.php` - إنشاء تقرير
- `resources/views/admin/pages/reports/show.blade.php` - عرض التقرير
- `resources/views/admin/pages/reports/print.blade.php` - طباعة
- `resources/views/admin/pages/reports/partials/student-report.blade.php` - تقرير الطالب
- `resources/views/admin/pages/reports/partials/course-report.blade.php` - تقرير الكورس
- `resources/views/admin/pages/reports/partials/system-report.blade.php` - تقرير النظام
- `resources/views/admin/pages/reports/templates/index.blade.php` - قائمة القوالب
- `resources/views/admin/pages/reports/templates/create.blade.php` - إنشاء قالب
- `resources/views/admin/pages/reports/templates/edit.blade.php` - تعديل قالب
- `resources/views/admin/pages/reports/templates/show.blade.php` - عرض قالب
- `resources/views/admin/pages/settings/index.blade.php` - الإعدادات

#### Student
- `resources/views/student/pages/reports/index.blade.php` - قائمة تقارير الطالب
- `resources/views/student/pages/reports/show.blade.php` - عرض تقرير الطالب

### Routes
- تم إضافة Routes في `routes/admin.php`:
  - `/admin/reports` - التقارير
  - `/admin/report-templates` - القوالب
  - `/admin/settings` - الإعدادات
  - `/admin/dashboard/widgets` - الودجت

- تم إضافة Routes في `routes/student.php`:
  - `/student/reports` - تقارير الطالب

- تم إنشاء `routes/api.php`:
  - `/api/analytics/student/{userId}` - تحليلات الطالب
  - `/api/analytics/course/{subjectId}` - تحليلات الكورس
  - `/api/analytics/system` - تحليلات النظام
  - `/api/analytics/track` - تتبع الأحداث

### Sidebar Links
- تم إضافة روابط في `resources/views/admin/layouts/main-sidebar.blade.php`:
  - قسم "التقارير والتحليلات" مع قائمة منسدلة
  - رابط "الإعدادات"

- تم إضافة روابط في `resources/views/student/layouts/main-sidebar.blade.php`:
  - رابط "تقاريري"

## الخطوات التالية (اختيارية)

### 1. تثبيت الحزم المطلوبة

```bash
# ApexCharts للرسوم البيانية
npm install apexcharts

# PDF Export
composer require barryvdh/laravel-dompdf

# Excel Export
composer require maatwebsite/excel
```

### 2. تشغيل Migrations

```bash
php artisan migrate
```

### 3. إضافة بيانات تجريبية (اختياري)

يمكنك إنشاء Seeder لإضافة قوالب تقارير افتراضية وإعدادات أولية.

### 4. تكامل ApexCharts

بعد تثبيت ApexCharts، قم بتحديث ملفات Views لإضافة:
- استيراد مكتبة ApexCharts
- تهيئة الرسوم البيانية في `@push('scripts')`

## الاستخدام

### للادمن:
1. الانتقال إلى "التقارير والتحليلات" > "التقارير"
2. إنشاء قالب تقرير جديد من "قوالب التقارير"
3. استخدام القالب لإنشاء تقرير
4. تصدير التقرير (PDF/Excel/Print)

### للطالب:
1. الانتقال إلى "تقاريري"
2. اختيار تقرير لعرضه
3. عرض التقرير مع الرسوم البيانية والإحصائيات

## ملاحظات

- نظام التصدير (PDF/Excel) جاهز للتكامل بعد تثبيت الحزم
- الرسوم البيانية جاهزة لـ ApexCharts بعد التثبيت
- يمكن تخصيص القوالب والإعدادات حسب الحاجة
- نظام التحليلات يتتبع الأحداث تلقائياً عند استخدام `AnalyticsService::trackEvent()`

