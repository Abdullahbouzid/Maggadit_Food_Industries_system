<?php

// بيانات الاتصال من البيئة أو القيم الافتراضية
$host = getenv('DB_HOST') ?: 'srv1803.hstgr.io';
$db = getenv('DB_DATABASE') ?: 'u248377748_maggadit_db';
$user = getenv('DB_USERNAME') ?: 'u248377748_maggadit_db';
$pass = getenv('DB_PASSWORD') ?: 'Maggadit_db2026';
$port = getenv('DB_PORT') ?: '3306';

echo '<h3>فحص الاتصال بقاعدة البيانات</h3>';
echo "المضيف: $host <br>";
echo "قاعدة البيانات: $db <br>";
echo "المستخدم: $user <br>";
echo "المنفذ: $port <br><br>";

try {
    // محاولة إنشاء اتصال PDO بقاعدة البيانات
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_TIMEOUT => 5, // مهلة 5 ثوانٍ
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    echo '✅ الاتصال بقاعدة البيانات ناجح!';
} catch (PDOException $e) {
    // عرض رسالة الخطأ إذا فشل الاتصال
    echo '❌ فشل الاتصال: '.$e->getMessage();

    if (strpos($e->getMessage(), 'driver not found') !== false) {
        echo '<br>⚠️ يبدو أن إضافة pdo_mysql غير مفعلة في نسخة PHP هذه.';
    }
}
