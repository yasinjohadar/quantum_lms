<?php

// عرض الأخطاء
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Laravel Database Connection Test</h2><hr>";

// قراءة ملف .env
$envPath = __DIR__ . '/.env';

if (!file_exists($envPath)) {
    die("❌ ملف .env غير موجود");
}

$env = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$data = [];
foreach ($env as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (!str_contains($line, '=')) continue;

    [$key, $value] = explode('=', $line, 2);
    $data[$key] = trim($value, "\"'");
}

// القيم المطلوبة
$host = $data['DB_HOST'] ?? null;
$port = $data['DB_PORT'] ?? 3306;
$db   = $data['DB_DATABASE'] ?? null;
$user = $data['DB_USERNAME'] ?? null;
$pass = $data['DB_PASSWORD'] ?? null;

// فحص القيم
if (!$host || !$db || !$user) {
    die("❌ إعدادات قاعدة البيانات ناقصة في ملف .env");
}

echo "🔎 DB_HOST: $host<br>";
echo "🔎 DB_PORT: $port<br>";
echo "🔎 DB_DATABASE: $db<br>";
echo "🔎 DB_USERNAME: $user<br><hr>";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "<h3 style='color:green'>✅ تم الاتصال بقاعدة البيانات بنجاح</h3>";
} catch (PDOException $e) {
    echo "<h3 style='color:red'>❌ فشل الاتصال بقاعدة البيانات</h3>";
    echo "<strong>سبب الخطأ:</strong><br>";
    echo $e->getMessage();
}
