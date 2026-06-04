# نشر API إضافة Chrome على السيرفر

## المشكلة

رسالة: `The route api/v1/extension/auth/login could not be found`

يعني أن Laravel على السيرفر **لا يملك** مسارات الإضافة (كود قديم أو كاش مسارات قديم).

**الحل:** المسارات تُسجَّل عبر `routes/admin.php` → `routes/extension-api.php`.

**تشخيص على السيرفر:**

```bash
cd /home/rootquantum1/public_html
bash scripts/server-check-extension-api.sh
```

أو يدوياً:

```bash
grep extension-api routes/admin.php
ls -la routes/extension-api.php
php artisan route:clear
rm -f bootstrap/cache/routes-v7.php
php artisan route:list --path=extension
```

## الملفات الواجب رفعها (الحد الأدنى)

- `routes/extension-api.php` **جديد — الأهم**
- `routes/admin.php` (آخر الملف: `require extension-api.php`)
- `bootstrap/app.php` (استثناء CSRF + alias `extension.api`)
- مجلد `app/Http/Controllers/Api/Extension/`
- `config/cors.php`
- `config/extension.php`
- `composer.json` + `composer.lock` (لـ `laravel/sanctum`)
- `app/Http/Controllers/Api/Extension/*`
- `app/Http/Middleware/EnsureExtensionApiAccess.php`
- `app/Services/ExtensionImport/*`
- `app/Models/User.php` (مع `HasApiTokens`)
- `database/migrations/*personal_access_tokens*`

## أوامر السيرفر (بعد الرفع)

```bash
cd /path/to/quantum_lms

composer install --no-dev --optimize-autoloader

php artisan migrate --force

php artisan optimize:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear

php artisan route:list --path=extension
```

يجب أن تظهر 7 مسارات منها:

`POST api/v1/extension/auth/login`

ثم:

```bash
php artisan config:cache
php artisan route:cache
```

## اختبار من المتصفح

`POST https://quantum-academy.online/api/v1/extension/auth/login`

(بـ Postman أو curl — GET قد يعطي 405 وهذا طبيعي)

```bash
curl -X POST https://quantum-academy.online/api/v1/extension/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"YOUR_EMAIL","password":"YOUR_PASSWORD"}'
```

نجاح: JSON فيه `token`.

## الإضافة

`chrome-extension/config/environments.json`:

```json
"apiBase": "https://quantum-academy.online/api/v1/extension"
```

Reload الإضافة في Chrome.
