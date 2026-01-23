#!/bin/bash

# سكريبت لإصلاح صلاحيات ملفات Laravel بعد النقل على السيرفر
# 
# كيفية الاستخدام:
# 1. ارفع هذا الملف إلى الجذر الرئيسي للمشروع
# 2. اعطه صلاحيات التنفيذ: chmod +x fix-permissions.sh
# 3. شغله: ./fix-permissions.sh
# 4. أو شغله مع sudo إذا لزم الأمر: sudo ./fix-permissions.sh

echo "🔧 بدء إصلاح صلاحيات Laravel..."
echo ""

# تحديد المسار الأساسي
BASE_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# تحديد المستخدم والمجموعة (عادة www-data أو apache)
WEB_USER="www-data"
WEB_GROUP="www-data"

# إذا كان المستخدم الحالي ليس root، اطلب sudo
if [ "$EUID" -ne 0 ]; then 
    echo "⚠️  تحتاج إلى صلاحيات root. سيتم استخدام sudo..."
    SUDO="sudo"
else
    SUDO=""
fi

# التحقق من وجود المجلدات
echo "📁 التحقق من وجود المجلدات المطلوبة..."

if [ ! -d "$BASE_DIR/storage" ]; then
    echo "   إنشاء مجلد storage..."
    $SUDO mkdir -p "$BASE_DIR/storage"
fi

if [ ! -d "$BASE_DIR/bootstrap/cache" ]; then
    echo "   إنشاء مجلد bootstrap/cache..."
    $SUDO mkdir -p "$BASE_DIR/bootstrap/cache"
fi

# إنشاء المجلدات الفرعية في storage
echo "📁 إنشاء المجلدات الفرعية..."
$SUDO mkdir -p "$BASE_DIR/storage/app/public"
$SUDO mkdir -p "$BASE_DIR/storage/framework/cache"
$SUDO mkdir -p "$BASE_DIR/storage/framework/sessions"
$SUDO mkdir -p "$BASE_DIR/storage/framework/views"
$SUDO mkdir -p "$BASE_DIR/storage/framework/testing"
$SUDO mkdir -p "$BASE_DIR/storage/logs"

# إصلاح الصلاحيات
echo "🔐 إصلاح الصلاحيات..."

# تعيين الصلاحيات للمجلدات
$SUDO chmod -R 775 "$BASE_DIR/storage"
$SUDO chmod -R 775 "$BASE_DIR/bootstrap/cache"

# تعيين المالك
if command -v chown &> /dev/null; then
    echo "   تعيين المالك إلى $WEB_USER:$WEB_GROUP..."
    $SUDO chown -R $WEB_USER:$WEB_GROUP "$BASE_DIR/storage"
    $SUDO chown -R $WEB_USER:$WEB_GROUP "$BASE_DIR/bootstrap/cache"
else
    echo "   ⚠️  أمر chown غير متاح، تأكد من تعيين المالك يدوياً"
fi

# إنشاء symlink storage إذا لم يكن موجوداً
if [ ! -L "$BASE_DIR/public/storage" ] && [ ! -d "$BASE_DIR/public/storage" ]; then
    echo "🔗 إنشاء symlink لـ storage..."
    cd "$BASE_DIR"
    $SUDO php artisan storage:link 2>/dev/null || {
        echo "   ⚠️  فشل إنشاء symlink تلقائياً. قم بإنشائه يدوياً:"
        echo "   ln -s $BASE_DIR/storage/app/public $BASE_DIR/public/storage"
    }
fi

# تنظيف cache
echo "🧹 تنظيف الـ cache..."
cd "$BASE_DIR"
php artisan config:clear 2>/dev/null || echo "   ⚠️  فشل تنظيف config cache"
php artisan cache:clear 2>/dev/null || echo "   ⚠️  فشل تنظيف cache"
php artisan view:clear 2>/dev/null || echo "   ⚠️  فشل تنظيف view cache"
php artisan route:clear 2>/dev/null || echo "   ⚠️  فشل تنظيف route cache"

echo ""
echo "✅ تم إصلاح الصلاحيات بنجاح!"
echo ""
echo "📝 الخطوات التالية:"
echo "   1. تحقق من ملف .env (قاعدة البيانات، APP_KEY، إلخ)"
echo "   2. شغل: php artisan migrate --force"
echo "   3. شغل: php artisan config:cache"
echo "   4. تحقق من أن الموقع يعمل الآن"
echo ""
