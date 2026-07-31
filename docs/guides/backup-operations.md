# تشغيل نظام النسخ الاحتياطي

هذا الدليل يغطي متطلبات التشغيل بعد إصلاحات النسخ/الجدولة/الاستعادة.

## المتطلبات الأساسية

1. **عامل الطابور (Queue Worker)**  
   المشروع يستخدم `QUEUE_CONNECTION=database` عادةً. بدون عامل طابور تبقى النسخ في حالة «في الطابور»
   للأبد (وستُكتشف تلقائياً وتُحوَّل إلى «فشل» بعد مهلة عبر `backup:reconcile-stuck` — انظر أدناه — لكن
   هذا كشف للمشكلة وليس حلاً لها).

```bash
php artisan queue:work --sleep=1 --tries=1
```

2. **مجدول Laravel (Cron / Task Scheduler)**  
   الأوامر مسجّلة في `routes/console.php`:

- `backup:run-scheduled` كل دقيقة (`withoutOverlapping`)
- `backup:cleanup-expired` يومياً
- `backup:reconcile-stuck` كل 5 دقائق (يكشف النسخ العالقة ويحوّلها إلى «فشل» تلقائياً)

### الطريقة الموصى بها: Supervisor

بما أن المشروع يعتمد Supervisor فعلياً لتشغيل Reverb (`deploy/supervisor/reverb.conf`)،
الأسهل والأكثر متانة هو تشغيل عامل الطابور والمجدول بنفس الطريقة بدل الاعتماد على
crontab خارجي لا يمكن التحقق من وجوده فعلياً من داخل المستودع — Supervisor يعيد
تشغيل أي عملية تتعطّل تلقائياً (`autorestart=true`)، بعكس سطر cron منسي لا يُبلّغ عن غيابه.

ملفا الإعداد جاهزان في `deploy/supervisor/`:

```bash
sudo cp deploy/supervisor/queue-worker.conf /etc/supervisor/conf.d/quantum-lms-queue-worker.conf
sudo cp deploy/supervisor/scheduler.conf /etc/supervisor/conf.d/quantum-lms-scheduler.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start quantum-lms-queue-worker:* quantum-lms-scheduler:*
```

⚠️ بعد أي نشر يغيّر كود الـ Jobs (مثل `CreateBackupJob`) يجب إعادة تشغيل عامل الطابور
ليقرأ الكود الجديد — الأمر الشائع المنسي في Laravel:

```bash
sudo supervisorctl restart quantum-lms-queue-worker:*
```

### بديل: cron / Task Scheduler النظامي

إن لم يتوفر Supervisor:

على Linux:

```bash
* * * * * cd /path/to/quantum_lms && php artisan schedule:run >> /dev/null 2>&1
```

على Windows (Task Scheduler) شغّل كل دقيقة:

```bat
php artisan schedule:run
```

في كلتا الحالتين يبقى عامل الطابور (البند 1 أعلاه) مطلوباً كخدمة دائمة منفصلة
(Supervisor أو Windows Service) — الجدولة وحدها لا تُشغّل الـ Jobs المُرسَلة للطابور.

للتطوير المحلي يمكن استخدام:

```bash
php artisan schedule:work
```

3. **`max_allowed_packet` في MySQL**  
   نسخ/استعادة قاعدة البيانات تنفّذ عبارات `INSERT` قد تضم عشرات الصفوف دفعة واحدة. الكود يحدّ
   حجم كل عبارة تلقائياً بحد أقصى (`config('backup.sql_dump_max_statement_bytes')`، الافتراضي 512
   كيلوبايت) — لكن هذا الحد يجب أن يبقى **أصغر** من `max_allowed_packet` الفعلي على خادم MySQL، وإلا
   فشلت الاستعادة بخطأ `SQLSTATE[HY000]: 2006 MySQL server has gone away` (انقطاع الاتصال بعد تجاوز
   حجم الحزمة المسموح). تحقّق من القيمة الحالية:
   ```sql
   SHOW VARIABLES LIKE 'max_allowed_packet';
   ```
   القيمة الافتراضية في بعض توزيعات MySQL/MariaDB المحلية (XAMPP/Laragon) قد تكون منخفضة جداً (1
   ميجابايت أو أقل). يُوصى برفعها إلى 64 ميجابايت على الأقل في `my.ini`/`my.cnf`:
   ```ini
   [mysqld]
   max_allowed_packet=64M
   ```
   ثم إعادة تشغيل خادم MySQL. **ملاحظة**: هذا الحد الجديد لا يحمي نسخاً احتياطية قديمة أُنشئت قبل هذا
   الإصلاح وقد تحتوي بالفعل على عبارة إدراج أكبر من `max_allowed_packet` الحالي — استعادتها تتطلب رفع
   القيمة على الخادم لتتسع لها، أو إنشاء نسخة جديدة بدلاً منها.

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
php artisan backup:cleanup-expired --dry-run   # معاينة ما سيُحذف بدون حذف فعلي
php artisan backup:reconcile-stuck             # كشف وتصحيح النسخ العالقة يدوياً
php artisan backup:test-storage                # اختبار كل أماكن التخزين النشطة (AppStorageConfig)
php artisan backup:test-storage {id}           # اختبار مكان تخزين محدد
php artisan queue:work --once
php artisan schedule:list
```

## ملاحظات تشغيلية

- النسخ اليدوية والمجدولة تُعالَج عبر `CreateBackupJob`.
- الاستعادة متاحة للنسخ **المكتملة** فقط وتتطلب تأكيداً مزدوجاً (`RESTORE`).
- قبل استعادة أي من ملفات الإعدادات الأربعة (`.env`, `config/app.php`, `config/database.php`,
  `config/mail.php`) يُنشأ ملف احتياطي بنفس الاسم مع لاحقة `.pre-restore-YYYYMMDDHHMMSS`.
- استعادة نوع "كامل" (`full`) تأخذ لقطة من قاعدة البيانات قبل البدء؛ إذا فشلت استعادة
  الملفات أو الإعدادات بعد نجاح استعادة قاعدة البيانات، يُعاد تطبيق هذه اللقطة تلقائياً
  كتراجع تعويضي (compensating rollback) قبل رفع الخطأ الأصلي — راجع سجلات Laravel
  (`storage/logs/laravel.log`) لمعرفة إن نجح التراجع أم يلزم تدخّل يدوي.
- تأكد أن أماكن التخزين العامة نشطة وقابلة للاتصال، وإلا تفشل مهمة الرفع بعد إنشاء المحتوى.
- إذا فشلت عبارة SQL أثناء استعادة قاعدة البيانات (مثلاً بسبب `max_allowed_packet` — انظر أعلاه)،
  يعيد الكود الاتصال بقاعدة البيانات تلقائياً (`DB::reconnect()`) قبل رفع رسالة خطأ واضحة، حتى لا
  يفشل حفظ الخطأ نفسه أو جلسة الطلب بخطأ "gone away" مُضلِّل يُخفي السبب الحقيقي.
- نسخ/استعادة قاعدة البيانات تُخفّف `sql_mode` مؤقتاً (تعطيل `NO_ZERO_DATE` وغيرها) أثناء تنفيذ
  عبارات الاستعادة فقط، لأن مخطط الجداول القديم قد يحوي قيماً افتراضية (مثل تاريخ صفري) كانت
  مسموحة وقت الإنشاء لكن يرفضها `sql_mode` الصارم الحالي للاتصال (`strict => true`).
- **جداول مُستبعَدة كلياً من أي نسخة قاعدة بيانات** (`config('backup.excluded_tables')`):
  `backups`, `backup_logs`, `backup_schedules`, `sessions`, `jobs`, `job_batches`, `failed_jobs`,
  `cache`, `cache_locks`. السبب: تضمين جداول تتبّع النسخ نفسها يسبّب تناقضاً ذاتياً عند الاستعادة —
  استعادة نسخة تستبدل جدول `backups` بصورته وقت إنشاء تلك النسخة نفسها (أي *قبل* اكتمالها)، فتظهر
  النسخة التي استعدتها للتو وكأنها فشلت رغم نجاح الاستعادة فعلياً. هذه الجداول تبقى بحالتها الحية
  دون أي تأثير من أي استعادة — وهذا مقصود ومطلوب.
- صفحة `/admin/backups` تعرض تنبيهاً تلقائياً عند وجود جدولات متأخرة عن موعدها أو نسخ
  عالقة — إن ظهر، تحقق أولاً من أن عامل الطابور والمجدول (القسم أعلاه) يعملان فعلياً.
- ⚠️ **أرشيفات النسخ غير مشفّرة حالياً على مستوى الملف** (فقط بيانات اعتماد التخزين
  نفسها مشفّرة عبر `AppStorageConfig`). أرشيف نوع `full`/`config` يتضمن `.env` الحقيقي
  بأسراره. التحسين الموصى به لاحقاً: تشفير AES-256 لاحق للضغط بمفتاح مخصص
  `BACKUP_ENCRYPTION_KEY` منفصل عن `APP_KEY` — مؤجَّل حالياً بقرار من فريق المشروع.
