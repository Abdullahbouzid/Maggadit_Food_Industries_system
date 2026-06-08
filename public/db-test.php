<?php
// بيانات الاتصال بقاعدة البيانات الخاصة بك
$host = 'localhost'; 
$db = 'u248377748_maggadit_db';
$user = 'u248377748_maggadit_db';
$pass = 'Maggadit_db2026';

try {
    // محاولة إنشاء اتصال PDO بقاعدة البيانات
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    // تعيين وضع الخطأ إلى الاستثناءات
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ الاتصال بقاعدة البيانات ناجح!";
} catch (PDOException $e) {
    // عرض رسالة الخطأ إذا فشل الاتصال
    echo "❌ فشل الاتصال: " . $e->getMessage();
}
?>