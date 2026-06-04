#!/bin/bash
# تشغيل من جذر Laravel على السيرفر: bash scripts/server-check-extension-api.sh

set -e
cd "$(dirname "$0")/.." || exit 1

echo "=== 1) ملفات المسارات ==="
test -f routes/extension-api.php && echo "OK routes/extension-api.php" || echo "MISSING routes/extension-api.php"
grep -n "extension-api" routes/admin.php routes/web.php 2>/dev/null || true

echo ""
echo "=== 2) Controllers ==="
test -f app/Http/Controllers/Api/Extension/ExtensionAuthController.php && echo "OK ExtensionAuthController" || echo "MISSING controller"

echo ""
echo "=== 3) Middleware alias ==="
grep -n "extension.api" bootstrap/app.php || echo "WARN: extension.api not in bootstrap/app.php"

echo ""
echo "=== 4) مسح الكاش ==="
php artisan route:clear 2>/dev/null || true
php artisan config:clear 2>/dev/null || true
rm -f bootstrap/cache/routes-v7.php bootstrap/cache/routes.php 2>/dev/null || true

echo ""
echo "=== 5) قائمة المسارات ==="
php artisan route:list --path=extension 2>&1 || echo "FAIL route:list"

echo ""
echo "=== 6) اختبار HTTP (عدّل البريد/كلمة المرور) ==="
echo 'curl -s -X POST "$APP_URL/api/v1/extension/auth/login" -H "Accept: application/json" -H "Content-Type: application/json" -d "{\"email\":\"...\",\"password\":\"...\"}"'
