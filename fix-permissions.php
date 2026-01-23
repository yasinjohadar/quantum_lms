<?php
/**
 * Laravel Permissions Fix Script
 * قم بزيارة: quantum-academy.online/fix-permissions.php
 * 
 * تحذير: احذف هذا الملف بعد الإصلاح لأسباب أمنية!
 */

// عرض جميع الأخطاء
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

// حماية بسيطة - يمكنك إضافة كلمة مرور هنا
$password = 'CHANGE_THIS_PASSWORD'; // غيّر هذه القيمة!
$action = $_GET['action'] ?? '';

if ($action === 'fix' && isset($_GET['pass']) && $_GET['pass'] === $password) {
    // تنفيذ الإصلاح
    $results = [];
    
    // دالة لإصلاح الصلاحيات
    function fixPermissions($path, $mode = 0775) {
        if (!file_exists($path)) {
            return ['success' => false, 'message' => "المسار غير موجود: $path"];
        }
        
        if (is_dir($path)) {
            // إصلاح الصلاحيات للمجلد
            if (@chmod($path, $mode)) {
                // إصلاح الصلاحيات للملفات والمجلدات الفرعية
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );
                
                $fixed = 0;
                foreach ($iterator as $item) {
                    if (@chmod($item->getPathname(), $mode)) {
                        $fixed++;
                    }
                }
                
                return ['success' => true, 'message' => "تم إصلاح $fixed ملف/مجلد في: $path"];
            } else {
                return ['success' => false, 'message' => "فشل تغيير الصلاحيات: $path"];
            }
        } else {
            if (@chmod($path, $mode)) {
                return ['success' => true, 'message' => "تم إصلاح الصلاحيات: $path"];
            } else {
                return ['success' => false, 'message' => "فشل تغيير الصلاحيات: $path"];
            }
        }
    }
    
    // إصلاح storage
    $storagePath = __DIR__ . '/storage';
    $results[] = ['path' => 'storage', 'result' => fixPermissions($storagePath)];
    
    // إصلاح bootstrap/cache
    $bootstrapCachePath = __DIR__ . '/bootstrap/cache';
    $results[] = ['path' => 'bootstrap/cache', 'result' => fixPermissions($bootstrapCachePath)];
    
    // عرض النتائج
    ?>
    <!DOCTYPE html>
    <html dir="rtl" lang="ar">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>إصلاح الصلاحيات - Laravel</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 20px;
                min-height: 100vh;
            }
            .container {
                max-width: 800px;
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
            .content {
                padding: 30px;
            }
            .result-item {
                padding: 15px;
                margin: 10px 0;
                border-radius: 8px;
                border-right: 4px solid #ddd;
                background: #f9f9f9;
            }
            .result-item.success {
                border-right-color: #28a745;
                background: #d4edda;
            }
            .result-item.error {
                border-right-color: #dc3545;
                background: #f8d7da;
            }
            .result-title {
                font-weight: bold;
                font-size: 16px;
                margin-bottom: 5px;
            }
            .result-message {
                color: #666;
                font-size: 14px;
            }
            .warning {
                background: #fff3cd;
                border: 2px solid #ffc107;
                padding: 20px;
                border-radius: 8px;
                margin-top: 20px;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🔧 إصلاح الصلاحيات</h1>
                <p>نتائج عملية الإصلاح</p>
            </div>
            <div class="content">
                <?php
                foreach ($results as $result) {
                    $icon = $result['result']['success'] ? '✅' : '❌';
                    $class = $result['result']['success'] ? 'success' : 'error';
                    echo "<div class='result-item $class'>";
                    echo "<div class='result-title'>{$icon} {$result['path']}</div>";
                    echo "<div class='result-message'>{$result['result']['message']}</div>";
                    echo "</div>";
                }
                ?>
                <div class="warning">
                    <strong>⚠️ تحذير أمني:</strong><br>
                    يرجى حذف هذا الملف (fix-permissions.php) بعد الانتهاء من الإصلاح!
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// عرض نموذج الإدخال
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إصلاح الصلاحيات - Laravel</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            max-width: 500px;
            width: 100%;
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
        .content {
            padding: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
        .info {
            background: #d1ecf1;
            border: 2px solid #bee5eb;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 إصلاح الصلاحيات</h1>
            <p>أداة إصلاح صلاحيات Laravel</p>
        </div>
        <div class="content">
            <div class="warning">
                ⚠️ <strong>تحذير:</strong> هذا السكربت سيحاول تعديل صلاحيات الملفات. تأكد من تغيير كلمة المرور الافتراضية في الملف!
            </div>
            
            <form method="GET" action="">
                <input type="hidden" name="action" value="fix">
                <div class="form-group">
                    <label for="pass">كلمة المرور:</label>
                    <input type="password" id="pass" name="pass" required placeholder="أدخل كلمة المرور">
                </div>
                <button type="submit" class="btn">إصلاح الصلاحيات</button>
            </form>
            
            <div class="info">
                <strong>ℹ️ معلومات:</strong><br>
                هذا السكربت سيحاول إصلاح صلاحيات المجلدات التالية:<br>
                • storage<br>
                • bootstrap/cache<br><br>
                <strong>ملاحظة:</strong> بعد الانتهاء، احذف هذا الملف لأسباب أمنية!
            </div>
        </div>
    </div>
</body>
</html>
