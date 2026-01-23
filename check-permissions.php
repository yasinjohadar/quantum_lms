<?php
/**
 * سكريبت للتحقق من صلاحيات الملفات والمجلدات المطلوبة لـ Laravel
 * 
 * استخدم هذا الملف للتحقق من أن جميع الصلاحيات صحيحة بعد النقل على السيرفر
 * 
 * كيفية الاستخدام:
 * 1. ارفع هذا الملف إلى الجذر الرئيسي للمشروع
 * 2. افتحه من المتصفح: https://yourdomain.com/check-permissions.php
 * 3. تحقق من النتائج وأصلح أي مشاكل
 * 4. احذف الملف بعد الانتهاء لأسباب أمنية
 */

// منع الوصول المباشر في الإنتاج (فقط للاختبار)
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    if (isset($env['APP_ENV']) && $env['APP_ENV'] === 'production') {
        // في الإنتاج، يمكنك تعطيل هذا السكريبت أو حمايته بكلمة مرور
        // die('This script is disabled in production');
    }
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التحقق من صلاحيات Laravel</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        .check-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-right: 4px solid #ddd;
        }
        .success {
            background: #d4edda;
            border-color: #28a745;
        }
        .error {
            background: #f8d7da;
            border-color: #dc3545;
        }
        .warning {
            background: #fff3cd;
            border-color: #ffc107;
        }
        .info {
            background: #d1ecf1;
            border-color: #17a2b8;
        }
        .status {
            font-weight: bold;
            margin-left: 10px;
        }
        .command {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            margin-top: 10px;
            direction: ltr;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 التحقق من صلاحيات Laravel</h1>
        
        <?php
        $checks = [];
        $hasErrors = false;
        
        // 1. التحقق من مجلد storage
        $storagePath = __DIR__ . '/storage';
        if (!is_dir($storagePath)) {
            $checks[] = ['type' => 'error', 'message' => 'مجلد storage غير موجود', 'fix' => 'إنشاء المجلد: mkdir -p storage'];
            $hasErrors = true;
        } else {
            $storageWritable = is_writable($storagePath);
            $storagePerms = substr(sprintf('%o', fileperms($storagePath)), -4);
            if (!$storageWritable) {
                $checks[] = ['type' => 'error', 'message' => "مجلد storage غير قابل للكتابة (الصلاحيات: $storagePerms)", 'fix' => 'chmod -R 775 storage'];
                $hasErrors = true;
            } else {
                $checks[] = ['type' => 'success', 'message' => "مجلد storage قابل للكتابة (الصلاحيات: $storagePerms)"];
            }
        }
        
        // 2. التحقق من مجلد bootstrap/cache
        $bootstrapCachePath = __DIR__ . '/bootstrap/cache';
        if (!is_dir($bootstrapCachePath)) {
            $checks[] = ['type' => 'error', 'message' => 'مجلد bootstrap/cache غير موجود', 'fix' => 'mkdir -p bootstrap/cache'];
            $hasErrors = true;
        } else {
            $cacheWritable = is_writable($bootstrapCachePath);
            $cachePerms = substr(sprintf('%o', fileperms($bootstrapCachePath)), -4);
            if (!$cacheWritable) {
                $checks[] = ['type' => 'error', 'message' => "مجلد bootstrap/cache غير قابل للكتابة (الصلاحيات: $cachePerms)", 'fix' => 'chmod -R 775 bootstrap/cache'];
                $hasErrors = true;
            } else {
                $checks[] = ['type' => 'success', 'message' => "مجلد bootstrap/cache قابل للكتابة (الصلاحيات: $cachePerms)"];
            }
        }
        
        // 3. التحقق من ملف .env
        $envPath = __DIR__ . '/.env';
        if (!file_exists($envPath)) {
            $checks[] = ['type' => 'error', 'message' => 'ملف .env غير موجود', 'fix' => 'نسخ .env.example إلى .env وتعديل الإعدادات'];
            $hasErrors = true;
        } else {
            $envWritable = is_writable($envPath);
            if (!$envWritable) {
                $checks[] = ['type' => 'warning', 'message' => 'ملف .env غير قابل للكتابة (قد يكون هذا مقصوداً للأمان)'];
            } else {
                $checks[] = ['type' => 'success', 'message' => 'ملف .env موجود'];
            }
        }
        
        // 4. التحقق من مجلدات storage الفرعية
        $storageSubdirs = ['app', 'framework', 'logs'];
        foreach ($storageSubdirs as $subdir) {
            $subdirPath = $storagePath . '/' . $subdir;
            if (!is_dir($subdirPath)) {
                $checks[] = ['type' => 'error', 'message' => "مجلد storage/$subdir غير موجود", 'fix' => "mkdir -p storage/$subdir"];
                $hasErrors = true;
            } else {
                $subdirWritable = is_writable($subdirPath);
                if (!$subdirWritable) {
                    $checks[] = ['type' => 'error', 'message' => "مجلد storage/$subdir غير قابل للكتابة", 'fix' => "chmod -R 775 storage/$subdir"];
                    $hasErrors = true;
                }
            }
        }
        
        // 5. التحقق من مجلدات framework
        $frameworkDirs = ['cache', 'sessions', 'views', 'testing'];
        foreach ($frameworkDirs as $dir) {
            $dirPath = $storagePath . '/framework/' . $dir;
            if (!is_dir($dirPath)) {
                $checks[] = ['type' => 'error', 'message' => "مجلد storage/framework/$dir غير موجود", 'fix' => "mkdir -p storage/framework/$dir"];
                $hasErrors = true;
            } else {
                $dirWritable = is_writable($dirPath);
                if (!$dirWritable) {
                    $checks[] = ['type' => 'error', 'message' => "مجلد storage/framework/$dir غير قابل للكتابة", 'fix' => "chmod -R 775 storage/framework/$dir"];
                    $hasErrors = true;
                }
            }
        }
        
        // 6. التحقق من symlink storage
        $publicStoragePath = __DIR__ . '/public/storage';
        if (!is_link($publicStoragePath) && !is_dir($publicStoragePath)) {
            $checks[] = ['type' => 'warning', 'message' => 'symlink storage غير موجود في public/storage', 'fix' => 'php artisan storage:link'];
        } else {
            $checks[] = ['type' => 'success', 'message' => 'symlink storage موجود'];
        }
        
        // 7. التحقق من PHP version
        $phpVersion = PHP_VERSION;
        if (version_compare($phpVersion, '8.1.0', '<')) {
            $checks[] = ['type' => 'error', 'message' => "إصدار PHP قديم: $phpVersion (المطلوب: >= 8.1)", 'fix' => 'تحديث PHP إلى الإصدار 8.1 أو أحدث'];
            $hasErrors = true;
        } else {
            $checks[] = ['type' => 'success', 'message' => "إصدار PHP: $phpVersion ✓"];
        }
        
        // 8. التحقق من ملحقات PHP المطلوبة
        $requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'json', 'ctype', 'fileinfo'];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $checks[] = ['type' => 'error', 'message' => "ملحق PHP مفقود: $ext", 'fix' => "تثبيت ملحق PHP: $ext"];
                $hasErrors = true;
            }
        }
        if (count($requiredExtensions) === count(array_filter($requiredExtensions, function($ext) { return extension_loaded($ext); }))) {
            $checks[] = ['type' => 'success', 'message' => 'جميع ملحقات PHP المطلوبة موجودة ✓'];
        }
        
        // عرض النتائج
        foreach ($checks as $check) {
            $icon = $check['type'] === 'success' ? '✅' : ($check['type'] === 'error' ? '❌' : '⚠️');
            echo "<div class='check-item {$check['type']}'>";
            echo "<span class='status'>{$icon}</span> {$check['message']}";
            if (isset($check['fix'])) {
                echo "<div class='command'>{$check['fix']}</div>";
            }
            echo "</div>";
        }
        
        // ملخص
        echo "<div class='check-item " . ($hasErrors ? 'error' : 'success') . "'>";
        echo "<h2>" . ($hasErrors ? '❌ يوجد مشاكل تحتاج إلى إصلاح' : '✅ جميع الفحوصات نجحت') . "</h2>";
        if ($hasErrors) {
            echo "<p>يرجى إصلاح المشاكل المذكورة أعلاه ثم إعادة تحميل هذه الصفحة.</p>";
        } else {
            echo "<p>جميع الصلاحيات والإعدادات صحيحة. يمكنك حذف هذا الملف الآن.</p>";
        }
        echo "</div>";
        ?>
        
        <div class="check-item info">
            <h3>📝 ملاحظات مهمة:</h3>
            <ul>
                <li>بعد إصلاح جميع المشاكل، احذف هذا الملف لأسباب أمنية</li>
                <li>تأكد من أن DocumentRoot في Apache يشير إلى مجلد <code>public</code></li>
                <li>تأكد من تفعيل <code>mod_rewrite</code> في Apache</li>
                <li>تأكد من أن <code>AllowOverride All</code> في إعدادات Apache</li>
            </ul>
        </div>
    </div>
</body>
</html>
