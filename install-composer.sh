#!/bin/bash

# سكربت تثبيت Composer على السيرفر
# قم بتشغيله عبر SSH: bash install-composer.sh

echo "=========================================="
echo "تثبيت Composer على السيرفر"
echo "=========================================="
echo ""

# التحقق من وجود PHP
if ! command -v php &> /dev/null; then
    echo "❌ PHP غير مثبت!"
    exit 1
fi

echo "✅ PHP موجود: $(php -v | head -n 1)"
echo ""

# تثبيت Composer
echo "📥 جاري تحميل Composer..."
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

echo "🔧 جاري تثبيت Composer..."
php composer-setup.php

echo "🧹 جاري تنظيف الملفات المؤقتة..."
php -r "unlink('composer-setup.php');"

# نقل Composer إلى مسار عام (إذا كان لديك صلاحيات)
if [ -w "/usr/local/bin" ]; then
    echo "📦 نقل Composer إلى /usr/local/bin..."
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
    echo "✅ تم تثبيت Composer بنجاح في /usr/local/bin/composer"
else
    echo "⚠️  لا توجد صلاحيات لكتابة في /usr/local/bin"
    echo "✅ تم تحميل composer.phar في المجلد الحالي"
    echo "💡 استخدم: php composer.phar بدلاً من composer"
fi

echo ""
echo "=========================================="
echo "التحقق من التثبيت..."
echo "=========================================="

if command -v composer &> /dev/null; then
    composer --version
    echo ""
    echo "✅ Composer جاهز للاستخدام!"
    echo "💡 شغّل الآن: composer install --no-dev --optimize-autoloader"
elif [ -f "composer.phar" ]; then
    php composer.phar --version
    echo ""
    echo "✅ composer.phar جاهز للاستخدام!"
    echo "💡 شغّل الآن: php composer.phar install --no-dev --optimize-autoloader"
else
    echo "❌ فشل التثبيت!"
    exit 1
fi
