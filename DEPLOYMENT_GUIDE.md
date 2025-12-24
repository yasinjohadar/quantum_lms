# دليل رفع التطبيق على السيرفر

## 📋 متطلبات السيرفر

- PHP >= 8.1
- Composer
- MySQL/MariaDB أو PostgreSQL
- Apache مع mod_rewrite أو Nginx
- SSL Certificate (موصى به)

---

## 🚀 خطوات الرفع

### 1. رفع الملفات

ارفع جميع ملفات المشروع إلى المجلد الرئيسي للسيرفر (عادة `public_html` أو `www`).

### 2. إعداد ملف `.htaccess`

تم إنشاء ملف `.htaccess` في المجلد الرئيسي الذي يقوم بـ:
- ✅ إعادة توجيه جميع الطلبات إلى مجلد `public`
- ✅ حماية الملفات الحساسة
- ✅ منع الوصول المباشر لمجلدات Laravel
- ✅ تحسين الأداء (ضغط الملفات، Cache)

**ملاحظة:** تأكد من أن ملف `.htaccess` موجود في المجلد الرئيسي وليس فقط في `public`.

### 3. إعداد قاعدة البيانات

```bash
# تحديث ملف .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

### 4. تشغيل Migrations

```bash
php artisan migrate --force
```

### 5. تشغيل Seeders (اختياري)

```bash
php artisan db:seed --force
```

### 6. إعداد الصلاحيات

```bash
# على Linux/Unix
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 7. تنظيف Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 8. تحسين الأداء

```bash
# تحسين التطبيق للإنتاج
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## 🔧 إعدادات Apache

### تأكد من تفعيل mod_rewrite

```bash
# على Ubuntu/Debian
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### إعدادات Virtual Host (مثال)

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /path/to/your/project/public
    
    <Directory /path/to/your/project/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

---

## 🔧 إعدادات Nginx

إذا كنت تستخدم Nginx، استخدم هذا الإعداد:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /path/to/your/project/public;
    index index.php;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 🔒 الأمان

### 1. حماية ملف `.env`

تأكد من أن ملف `.env` محمي ولا يمكن الوصول إليه من المتصفح.

### 2. تحديث `APP_KEY`

```bash
php artisan key:generate
```

### 3. تعطيل Debug Mode

في ملف `.env`:
```
APP_DEBUG=false
APP_ENV=production
```

### 4. تفعيل HTTPS

استخدم SSL Certificate وعدّل `.htaccess` لإعادة توجيه HTTP إلى HTTPS:

```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 📝 ملاحظات مهمة

1. **مسار التطبيق:** بعد الرفع، التطبيق سيعمل من المسار الأساسي `domain.com` وليس `domain.com/public`

2. **الملفات الحساسة:** تم حماية الملفات التالية من الوصول المباشر:
   - `.env`
   - `composer.json`, `composer.lock`
   - `package.json`, `package.lock`
   - `artisan`
   - ملفات قاعدة البيانات (`.sql`, `.sqlite`)

3. **مجلدات Laravel:** تم حماية المجلدات التالية:
   - `app/`
   - `bootstrap/`
   - `config/`
   - `database/`
   - `resources/`
   - `routes/`
   - `storage/`
   - `tests/`
   - `vendor/`

4. **الأداء:** تم تفعيل:
   - ضغط الملفات (Gzip)
   - Cache للملفات الثابتة

---

## ✅ التحقق من العمل

بعد الرفع، تحقق من:

1. ✅ الوصول للتطبيق من `domain.com` (بدون `/public`)
2. ✅ تسجيل الدخول يعمل
3. ✅ جميع الصفحات تعمل بشكل صحيح
4. ✅ الملفات الثابتة (CSS, JS, Images) تُحمّل بشكل صحيح
5. ✅ لا توجد أخطاء في Console المتصفح

---

## 🆘 حل المشاكل الشائعة

### المشكلة: 500 Internal Server Error
**الحل:**
- تحقق من صلاحيات الملفات
- تحقق من ملف `.htaccess`
- تحقق من logs في `storage/logs/laravel.log`

### المشكلة: الصفحات لا تعمل
**الحل:**
- تأكد من تفعيل `mod_rewrite` في Apache
- تحقق من إعدادات Virtual Host

### المشكلة: الملفات الثابتة لا تُحمّل
**الحل:**
- تحقق من مسار الملفات في `config/filesystems.php`
- تأكد من وجود ملف `.htaccess` في `public`

---

## 📞 الدعم

إذا واجهت أي مشاكل، تحقق من:
- Laravel Logs: `storage/logs/laravel.log`
- Apache/Nginx Error Logs
- PHP Error Logs

---

**تم إنشاء هذا الدليل لمساعدتك في رفع التطبيق بنجاح! 🚀**

