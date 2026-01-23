<?php
/**
 * Laravel Debug Script
 * قم بزيارة: quantum-academy.online/public/debug.php
 */

// عرض جميع الأخطاء
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Debug - تشخيص المشاكل</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .check-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border-right: 4px solid #ddd;
            background: #f9f9f9;
        }
        .check-item.success {
            border-right-color: #28a745;
            background: #d4edda;
        }
        .check-item.error {
            border-right-color: #dc3545;
            background: #f8d7da;
        }
        .check-item.warning {
            border-right-color: #ffc107;
            background: #fff3cd;
        }
        .check-title {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
        }
        .check-message {
            color: #666;
            font-size: 14px;
        }
        .icon {
            display: inline-block;
            margin-left: 10px;
            font-size: 20px;
        }
        .summary {
            margin-top: 30px;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }
        .summary.success {
            background: #d4edda;
            color: #155724;
        }
        .summary.error {
            background: #f8d7da;
            color: #721c24;
        }
        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
            margin-top: 10px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Laravel Debug Tool</h1>
            <p>أداة تشخيص مشاكل Laravel</p>
        </div>
        <div class="content">
            <?php
            $checks = [];
            $hasErrors = false;

            // 1. فحص مجلد vendor
            $vendorPath = __DIR__ . '/../vendor';
            $vendorExists = is_dir($vendorPath);
            $vendorAutoload = file_exists($vendorPath . '/autoload.php');
            
            if ($vendorExists && $vendorAutoload) {
                $checks[] = ['type' => 'success', 'title' => 'مجلد vendor', 'message' => 'مجلد vendor موجود و autoload.php موجود'];
            } else {
                $hasErrors = true;
                $checks[] = ['type' => 'error', 'title' => 'مجلد vendor', 'message' => 'مجلد vendor غير موجود أو autoload.php مفقود. قم بتشغيل: composer install'];
            }

            // 2. فحص ملف .env
            $envPath = __DIR__ . '/../.env';
            $envExists = file_exists($envPath);
            
            if ($envExists) {
                $checks[] = ['type' => 'success', 'title' => 'ملف .env', 'message' => 'ملف .env موجود'];
                
                // فحص APP_KEY
                $envContent = file_get_contents($envPath);
                if (strpos($envContent, 'APP_KEY=') !== false) {
                    preg_match('/APP_KEY=(.*)/', $envContent, $matches);
                    $appKey = trim($matches[1] ?? '');
                    if (!empty($appKey) && $appKey !== 'base64:' && strlen($appKey) > 10) {
                        $checks[] = ['type' => 'success', 'title' => 'APP_KEY', 'message' => 'APP_KEY موجود ومضبوط'];
                    } else {
                        $hasErrors = true;
                        $checks[] = ['type' => 'error', 'title' => 'APP_KEY', 'message' => 'APP_KEY مفقود أو غير صحيح. قم بتشغيل: php artisan key:generate'];
                    }
                } else {
                    $hasErrors = true;
                    $checks[] = ['type' => 'error', 'title' => 'APP_KEY', 'message' => 'APP_KEY غير موجود في ملف .env'];
                }
            } else {
                $hasErrors = true;
                $checks[] = ['type' => 'error', 'title' => 'ملف .env', 'message' => 'ملف .env غير موجود'];
            }

            // 3. فحص صلاحيات storage
            $storagePath = __DIR__ . '/../storage';
            $storageExists = is_dir($storagePath);
            $storageWritable = $storageExists && is_writable($storagePath);
            
            if ($storageExists && $storageWritable) {
                $checks[] = ['type' => 'success', 'title' => 'مجلد storage', 'message' => 'مجلد storage موجود ويمكن الكتابة فيه'];
            } else {
                $hasErrors = true;
                if (!$storageExists) {
                    $checks[] = ['type' => 'error', 'title' => 'مجلد storage', 'message' => 'مجلد storage غير موجود'];
                } else {
                    $perms = substr(sprintf('%o', fileperms($storagePath)), -4);
                    $checks[] = ['type' => 'error', 'title' => 'مجلد storage', 'message' => "مجلد storage موجود لكن غير قابل للكتابة. الصلاحيات الحالية: $perms. يجب أن تكون 775"];
                }
            }

            // 4. فحص صلاحيات bootstrap/cache
            $bootstrapCachePath = __DIR__ . '/../bootstrap/cache';
            $bootstrapCacheExists = is_dir($bootstrapCachePath);
            $bootstrapCacheWritable = $bootstrapCacheExists && is_writable($bootstrapCachePath);
            
            if ($bootstrapCacheExists && $bootstrapCacheWritable) {
                $checks[] = ['type' => 'success', 'title' => 'مجلد bootstrap/cache', 'message' => 'مجلد bootstrap/cache موجود ويمكن الكتابة فيه'];
            } else {
                $hasErrors = true;
                if (!$bootstrapCacheExists) {
                    $checks[] = ['type' => 'error', 'title' => 'مجلد bootstrap/cache', 'message' => 'مجلد bootstrap/cache غير موجود'];
                } else {
                    $perms = substr(sprintf('%o', fileperms($bootstrapCachePath)), -4);
                    $checks[] = ['type' => 'error', 'title' => 'مجلد bootstrap/cache', 'message' => "مجلد bootstrap/cache موجود لكن غير قابل للكتابة. الصلاحيات الحالية: $perms. يجب أن تكون 775"];
                }
            }

            // 5. محاولة تحميل Laravel
            if ($vendorExists && $vendorAutoload) {
                try {
                    require_once $vendorPath . '/autoload.php';
                    $app = require_once __DIR__ . '/../bootstrap/app.php';
                    $checks[] = ['type' => 'success', 'title' => 'تحميل Laravel', 'message' => 'تم تحميل Laravel بنجاح'];
                } catch (Throwable $e) {
                    $hasErrors = true;
                    $checks[] = ['type' => 'error', 'title' => 'تحميل Laravel', 'message' => 'فشل تحميل Laravel: ' . $e->getMessage()];
                    $checks[] = ['type' => 'error', 'title' => 'تفاصيل الخطأ', 'message' => '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>'];
                }
            }

            // 6. فحص PHP version
            $phpVersion = PHP_VERSION;
            if (version_compare($phpVersion, '8.1.0', '>=')) {
                $checks[] = ['type' => 'success', 'title' => 'إصدار PHP', 'message' => "إصدار PHP: $phpVersion (ممتاز)"];
            } else {
                $checks[] = ['type' => 'warning', 'title' => 'إصدار PHP', 'message' => "إصدار PHP: $phpVersion (يُنصح بـ 8.1 أو أحدث)"];
            }

            // 7. فحص Extensions المطلوبة
            $requiredExtensions = ['pdo', 'mbstring', 'openssl', 'tokenizer', 'json', 'ctype', 'fileinfo'];
            $missingExtensions = [];
            foreach ($requiredExtensions as $ext) {
                if (!extension_loaded($ext)) {
                    $missingExtensions[] = $ext;
                }
            }
            
            if (empty($missingExtensions)) {
                $checks[] = ['type' => 'success', 'title' => 'PHP Extensions', 'message' => 'جميع Extensions المطلوبة موجودة'];
            } else {
                $hasErrors = true;
                $checks[] = ['type' => 'error', 'title' => 'PHP Extensions', 'message' => 'Extensions مفقودة: ' . implode(', ', $missingExtensions)];
            }

            // عرض النتائج
            foreach ($checks as $check) {
                $icon = $check['type'] === 'success' ? '✅' : ($check['type'] === 'error' ? '❌' : '⚠️');
                echo "<div class='check-item {$check['type']}'>";
                echo "<div class='check-title'>{$icon} {$check['title']}</div>";
                echo "<div class='check-message'>{$check['message']}</div>";
                echo "</div>";
            }

            // الملخص
            echo "<div class='summary " . ($hasErrors ? 'error' : 'success') . "'>";
            if ($hasErrors) {
                echo "❌ تم اكتشاف مشاكل. يرجى إصلاحها أعلاه.";
            } else {
                echo "✅ جميع الفحوصات نجحت! إذا كان الموقع لا يزال لا يعمل، تحقق من سجلات الأخطاء.";
            }
            echo "</div>";

            // إرشادات إضافية
            if ($hasErrors) {
                echo "<div class='check-item warning' style='margin-top: 20px;'>";
                echo "<div class='check-title'>📋 خطوات الإصلاح المقترحة:</div>";
                echo "<div class='check-message'>";
                echo "<ol style='margin-right: 20px; margin-top: 10px;'>";
                if (!$vendorExists || !$vendorAutoload) {
                    echo "<li>قم بتشغيل: <code>composer install --no-dev --optimize-autoloader</code></li>";
                }
                if (!$storageWritable || !$bootstrapCacheWritable) {
                    echo "<li>قم بتشغيل: <code>chmod -R 775 storage bootstrap/cache</code></li>";
                    echo "<li>أو استخدم ملف fix-permissions.php الموجود في الجذر</li>";
                }
                if (isset($appKey) && (empty($appKey) || $appKey === 'base64:')) {
                    echo "<li>قم بتشغيل: <code>php artisan key:generate</code></li>";
                }
                echo "<li>قم بتشغيل: <code>php artisan config:clear && php artisan cache:clear</code></li>";
                echo "</ol>";
                echo "</div>";
                echo "</div>";
            }
            ?>
        </div>
    </div>
</body>
</html>
