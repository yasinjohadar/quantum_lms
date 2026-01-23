# 🔧 دليل إصلاح خطأ HTTP 500 بعد النقل على السيرفر

## المشكلة
بعد نقل المشروع على السيرفر، يظهر خطأ **HTTP 500 Internal Server Error** رغم أن الاتصال بقاعدة البيانات صحيح.

## الأسباب المحتملة

1. **مشكلة في ملف `.htaccess`** - يمنع الوصول لمجلد `storage` المطلوب
2. **مشاكل في الصلاحيات** - مجلدات `storage` و `bootstrap/cache` غير قابلة للكتابة
3. **مشاكل في إعدادات Apache** - `mod_rewrite` غير مفعّل أو `AllowOverride` غير صحيح
4. **مشاكل في ملف `.env`** - إعدادات خاطئة أو مفقودة
5. **مشاكل في Cache** - cache قديم يحتاج تنظيف

---

## ✅ الحل السريع

### الخطوة 1: إصلاح ملف `.htaccess`

تم تعديل ملف `.htaccess` في الجذر لإصلاح المشكلة. تأكد من أن التعديلات موجودة.

### الخطوة 2: إصلاح الصلاحيات

#### على Linux/Unix:

```bash
# إعطاء صلاحيات للمجلدات
chmod -R 775 storage bootstrap/cache

# تعيين المالك (استبدل www-data بالمستخدم الصحيح)
chown -R www-data:www-data storage bootstrap/cache

# أو استخدم السكريبت المرفق
chmod +x fix-permissions.sh
./fix-permissions.sh
```

#### على cPanel:

1. افتح **File Manager**
2. اذهب إلى مجلدات `storage` و `bootstrap/cache`
3. اضغط كليك يمين → **Change Permissions**
4. ضع الصلاحيات: `775` للمجلدات و `664` للملفات
5. تأكد من تفعيل **Recursive**

### الخطوة 3: التحقق من الإعدادات

#### أ) التحقق من `.env`

تأكد من وجود ملف `.env` وأنه يحتوي على:

```env
APP_NAME="Quantum LMS"
APP_ENV=production
APP_KEY=base64:YOUR_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

**ملاحظة مهمة:** إذا كان `APP_KEY` مفقوداً، شغّل:
```bash
php artisan key:generate
```

#### ب) التحقق من إعدادات Apache

تأكد من:

1. **تفعيل mod_rewrite:**
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

2. **إعدادات Virtual Host:**
   ```apache
   <Directory /path/to/your/project>
       AllowOverride All
       Require all granted
   </Directory>
   ```

3. **DocumentRoot يجب أن يشير إلى `public`:**
   ```apache
   DocumentRoot /path/to/your/project/public
   ```

### الخطوة 4: تنظيف Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan event:clear
```

### الخطوة 5: إنشاء Cache للإنتاج

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### الخطوة 6: إنشاء symlink لـ storage

```bash
php artisan storage:link
```

---

## 🔍 التحقق من المشاكل

### استخدام سكريبت التحقق

1. ارفع ملف `check-permissions.php` إلى الجذر
2. افتحه من المتصفح: `https://yourdomain.com/check-permissions.php`
3. تحقق من النتائج وأصلح أي مشاكل
4. **احذف الملف بعد الانتهاء** لأسباب أمنية

### فحص Logs

#### Laravel Logs:
```bash
tail -f storage/logs/laravel.log
```

#### Apache Error Log:
```bash
tail -f /var/log/apache2/error.log
# أو
tail -f /var/log/httpd/error_log
```

#### cPanel Error Log:
- اذهب إلى **Error Log** في cPanel
- افحص آخر الأخطاء

---

## 🛠️ حلول للمشاكل الشائعة

### المشكلة 1: "Class not found" أو "Namespace not found"

**الحل:**
```bash
composer install --no-dev --optimize-autoloader
composer dump-autoload
```

### المشكلة 2: "Permission denied" في storage

**الحل:**
```bash
chmod -R 775 storage
chown -R www-data:www-data storage
```

### المشكلة 3: "Symlink storage not found"

**الحل:**
```bash
php artisan storage:link
```

### المشكلة 4: "APP_KEY is not set"

**الحل:**
```bash
php artisan key:generate
```

### المشكلة 5: "Database connection failed"

**الحل:**
- تحقق من إعدادات قاعدة البيانات في `.env`
- تأكد من أن المستخدم لديه صلاحيات كافية
- تحقق من أن قاعدة البيانات موجودة

### المشكلة 6: "mod_rewrite not enabled"

**الحل:**
```bash
# على Ubuntu/Debian
sudo a2enmod rewrite
sudo systemctl restart apache2

# على CentOS/RHEL
# تأكد من وجود LoadModule rewrite_module في httpd.conf
sudo systemctl restart httpd
```

---

## 📋 Checklist بعد النقل

- [ ] رفع جميع الملفات إلى السيرفر
- [ ] نسخ `.env.example` إلى `.env` وتعديل الإعدادات
- [ ] تشغيل `php artisan key:generate`
- [ ] إصلاح صلاحيات `storage` و `bootstrap/cache`
- [ ] تشغيل `php artisan migrate --force`
- [ ] إنشاء symlink: `php artisan storage:link`
- [ ] تنظيف cache: `php artisan config:clear` إلخ
- [ ] إنشاء cache للإنتاج: `php artisan config:cache` إلخ
- [ ] التحقق من أن `APP_DEBUG=false` في `.env`
- [ ] التحقق من أن `APP_ENV=production` في `.env`
- [ ] التحقق من إعدادات Apache (mod_rewrite, AllowOverride)
- [ ] اختبار الموقع والتأكد من عمله

---

## 🔒 الأمان بعد الإصلاح

1. **احذف ملفات الاختبار:**
   ```bash
   rm check-permissions.php
   rm fix-permissions.sh
   ```

2. **تأكد من حماية `.env`:**
   - الصلاحيات: `644` أو `600`
   - محمي في `.htaccess`

3. **تعطيل Debug Mode:**
   ```env
   APP_DEBUG=false
   APP_ENV=production
   ```

---

## 📞 إذا استمرت المشكلة

1. **افحص Laravel Logs:**
   ```bash
   tail -50 storage/logs/laravel.log
   ```

2. **افحص Apache Error Log:**
   ```bash
   tail -50 /var/log/apache2/error.log
   ```

3. **فعّل Debug مؤقتاً (للاستكشاف فقط):**
   ```env
   APP_DEBUG=true
   ```
   **⚠️ لا تتركه مفعلاً في الإنتاج!**

4. **تحقق من PHP Version:**
   ```bash
   php -v
   ```
   يجب أن يكون >= 8.1

5. **تحقق من ملحقات PHP المطلوبة:**
   ```bash
   php -m | grep -E "pdo|mbstring|openssl|tokenizer|json|ctype|fileinfo"
   ```

---

## ✅ بعد الإصلاح

بعد أن يعمل الموقع:

1. احذف ملفات الاختبار (`check-permissions.php`, `fix-permissions.sh`)
2. تأكد من أن `APP_DEBUG=false`
3. أنشئ cache للإنتاج
4. اختبر جميع الوظائف الأساسية

---

**تم إنشاء هذا الدليل لمساعدتك في حل مشكلة HTTP 500 بعد النقل على السيرفر! 🚀**
