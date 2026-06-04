# قائمة التحقق من إعدادات السيرفر

## 1. التحقق من ملف .env على السيرفر

تأكد من أن الملف `.env` على السيرفر يحتوي على الإعدادات التالية:

```env
APP_URL=https://quantum-academy.online
# أو
APP_URL=https://www.quantum-academy.online
```

**ملاحظات مهمة:**
- يجب أن يكون `APP_URL` مطابقاً لرابط الموقع الفعلي
- لا يجب أن ينتهي بـ `/` (مثلاً: `https://quantum-academy.online` وليس `https://quantum-academy.online/`)
- إذا كان `ASSET_URL` موجوداً في `.env`، يجب حذفه أو تركه فارغاً

## 2. تنظيف Cache على السيرفر

بعد رفع الملفات، قم بتنفيذ الأوامر التالية على السيرفر:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear
```

أو يمكنك تنفيذ أمر واحد لتنظيف كل شيء:

```bash
php artisan optimize:clear
```

## 3. التخزين وصور TinyMCE (أسئلة / محرر)

بعد رفع صورة في **إنشاء سؤال** (`/admin/questions/create`)، إن ظهرت أيقونة صورة مكسورة:

### أوامر على السيرفر

```bash
cd /path/to/project
php artisan storage:link
ls -la public/storage
ls -la storage/app/public/questions/images/ | tail -5
chmod -R 775 storage bootstrap/cache
php artisan config:clear
php artisan optimize:clear
```

### DocumentRoot

- **الأفضل:** جعل جذر الدومين يشير إلى مجلد `public` داخل المشروع.
- إن كان الجذر مجلد المشروع كاملاً (`public_html`)، تأكد أن قواعد `.htaccess` في الجذر تعيد توجيه `/storage/*` إلى `public/storage/*` (موجودة في المستودع).

### تشخيص سريع من المتصفح

1. DevTools → **Network** → ارفع صورة من TinyMCE.
2. افتح طلب `POST .../admin/questions/upload-image`.
3. انسخ قيمة **`location`** من JSON وافتحها في تبويب جديد:
   - **404** على `/storage/...` → نفّذ `storage:link` أو صحّح DocumentRoot.
   - **403** على رابط S3/R2 → فعّل **روابط موقّعة** في الإعدادات → التخزين (`media_use_presigned_urls`) أو اجعل الـ bucket/CDN عاماً للقراءة.
   - **`localhost` في الرابط** → صحّح `APP_URL` في `.env` ثم `php artisan config:clear`.

### إعدادات التخزين (لوحة الإدارة)

- **الإعدادات → التخزين:** تحقق من `storage_driver_mode` ومفاتيح السحابة.
- للـ bucket الخاص: يجب تفعيل الروابط الموقّعة (presigned) وإلا تظهر الصور مكسورة في المحرر.

## 4. التحقق من الصلاحيات

تأكد من أن المجلدات التالية لديها صلاحيات الكتابة:

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## 5. التحقق من تحميل الملفات الثابتة

بعد الرفع، افتح الموقع وتحقق من:
- تحميل ملف CSS: `frontend/css/custom.css`
- تحميل الصور: `frontend/images/logo-footer.webp`, `frontend/images/hero-img.png`
- تحميل الخطوط من Google Fonts (CDN)

## 6. مشاكل شائعة وحلولها

### المشكلة: التنسيق لا يظهر بشكل صحيح
**الحل:**
1. تحقق من `APP_URL` في `.env`
2. قم بتنظيف الـ cache
3. امسح cache المتصفح (Ctrl+Shift+Delete)
4. تحقق من أن ملف `custom.css` يتم تحميله بشكل صحيح من Developer Tools

### المشكلة: الصور لا تظهر (واجهة الموقع أو TinyMCE)
**الحل:**
1. راجع القسم **3. التخزين وصور TinyMCE** أعلاه
2. تحقق من أن مجلد `public/frontend/images/` موجود ويحتوي على الصور
3. تحقق من صلاحيات الملفات و`public/storage` (symlink)
4. تحقق من أن المسارات في الكود صحيحة

### المشكلة: الخطوط لا تظهر
**الحل:**
- الخطوط تُحمّل من Google Fonts CDN، تأكد من اتصال الإنترنت
- تحقق من أن `@import` في `custom.css` يعمل بشكل صحيح

## 7. بعد التعديلات

بعد إصلاح مسار الصورة في `custom.css`:
- تم تغيير `url('/frontend/images/banner-two-shape-bg.png')` إلى `url('../images/banner-two-shape-bg.png')`
- هذا المسار النسبي يعمل بشكل أفضل لأنه لا يعتمد على `APP_URL`
