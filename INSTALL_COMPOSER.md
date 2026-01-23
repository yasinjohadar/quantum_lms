# دليل تثبيت Composer على السيرفر

## المشكلة
`composer: command not found` - Composer غير مثبت على السيرفر

## الحلول

### الحل 1: تثبيت Composer عبر cPanel (الأسهل)

1. افتح **cPanel**
2. ابحث عن **Terminal** أو **SSH Access**
3. شغّل الأوامر التالية:

```bash
cd ~
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

4. تحقق من التثبيت:
```bash
composer --version
```

### الحل 2: تثبيت Composer محلياً في مجلد المشروع

إذا لم تستطع تثبيت Composer بشكل عام، يمكن تثبيته محلياً:

```bash
cd /home/username/public_html  # أو مسار مشروعك
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

ثم استخدمه:
```bash
php composer.phar install --no-dev --optimize-autoloader
```

### الحل 3: رفع مجلد vendor من المشروع المحلي (بديل سريع)

إذا كان لديك مجلد `vendor` في المشروع المحلي:

1. **على جهازك المحلي:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

2. **ارفع مجلد `vendor` كاملاً إلى السيرفر** عبر FTP/cPanel File Manager

3. **تأكد من الصلاحيات:**
   ```bash
   chmod -R 755 vendor
   ```

### الحل 4: استخدام PHP مباشرة (إذا كان Composer غير متاح)

إذا لم تستطع تثبيت Composer، يمكنك استخدام PHP مباشرة:

```bash
cd /path/to/your/project
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php composer.phar install --no-dev --optimize-autoloader
```

## بعد تثبيت Composer

1. **انتقل إلى مجلد المشروع:**
   ```bash
   cd /home/username/public_html  # أو مسار مشروعك
   ```

2. **شغّل Composer:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **تأكد من الصلاحيات:**
   ```bash
   chmod -R 755 vendor
   chmod -R 775 storage bootstrap/cache
   ```

4. **تحقق من النتيجة:**
   افتح `https://quantum-academy.online/public/debug.php` مرة أخرى

## ملاحظات

- إذا كان السيرفر لا يدعم SSH، استخدم **cPanel Terminal**
- بعض الاستضافات المشتركة قد تمنع تثبيت Composer - في هذه الحالة استخدم **الحل 3** (رفع vendor من المحلي)
- تأكد من أن PHP version >= 8.1
