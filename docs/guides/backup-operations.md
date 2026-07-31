# تشغيل نظام النسخ الاحتياطي

هذا الدليل يغطي متطلبات التشغيل بعد إصلاحات النسخ/الجدولة/الاستعادة.

## المتطلبات الأساسية

1. **عامل الطابور (Queue Worker)**  
   المشروع يستخدم `QUEUE_CONNECTION=database` عادةً. بدون عامل طابور تبقى النسخ في حالة «في الطابور».

```bash
php artisan queue:work --sleep=1 --tries=1
```

في الإنتاج يُفضّل تشغيله كخدمة دائمة (Supervisor / Windows Service).

2. **مجدول Laravel (Cron / Task Scheduler)**  
   الأوامر مسجّلة في `routes/console.php`:

- `backup:run-scheduled` كل دقيقة (`withoutOverlapping`)
- `backup:cleanup-expired` يومياً

على Linux:

```bash
* * * * * cd /path/to/quantum_lms && php artisan schedule:run >> /dev/null 2>&1
```

على Windows (Task Scheduler) شغّل كل دقيقة:

```bat
php artisan schedule:run
```

للتطوير المحلي يمكن استخدام:

```bash
php artisan schedule:work
```

## المسارات الإدارية

| الصفحة | المسار |
|--------|--------|
| قائمة النسخ | `/admin/backups` |
| إنشاء نسخة | `/admin/backups/create` |
| الجدولة | `/admin/backup-schedules` |
| التخزين العام | `/admin/app-storage/configs` |

ملفات النسخ تُحفظ تحت المسار المنطقي `backups/{id}/...` داخل مكان التخزين العام المختار.

## أوامر مفيدة

```bash
php artisan backup:run-scheduled
php artisan backup:cleanup-expired
php artisan queue:work --once
php artisan schedule:list
```

## ملاحظات تشغيلية

- النسخ اليدوية والمجدولة تُعالَج عبر `CreateBackupJob`.
- الاستعادة متاحة للنسخ **المكتملة** فقط وتتطلب تأكيداً مزدوجاً (`RESTORE`).
- قبل استعادة `.env` يُنشأ ملف احتياطي: `.env.pre-restore-YYYYMMDDHHMMSS`.
- تأكد أن أماكن التخزين العامة نشطة وقابلة للاتصال، وإلا تفشل مهمة الرفع بعد إنشاء المحتوى.
