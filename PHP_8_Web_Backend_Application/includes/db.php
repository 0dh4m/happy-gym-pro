<?php
/**
 * Copyright (C) 2026 Adham Cherkaoui Eddeqaqi (0dh4m). All rights reserved.
 * Proprietary and confidential. Unauthorized copying or distribution is strictly prohibited.
 * Author: Adham Cherkaoui Eddeqaqi (0dh4m) <0dh4mm@gmail.com>
 * GitHub: https://github.com/0dh4m
 */

$host = getenv('HAPPY_GYM_DB_HOST') ?: "localhost";
$user = getenv('HAPPY_GYM_DB_USER') ?: "root";
$pass = getenv('HAPPY_GYM_DB_PASS') ?: "";
$dbname = getenv('HAPPY_GYM_DB_NAME') ?: "gym_db";

// تفعيل نظام التقاط الأخطاء المتقدم لتفادي ظهور كود برمجي عند الخطأ
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    // رسالة نقية في حال نسيان تشغيل MySQL في XAMPP
    die("<div style='text-align:center; padding:50px; font-family:tahoma;'>
            <h2 style='color:#ef4444;'>⚠️ عذراً كابتن، فشل الاتصال بقاعدة البيانات</h2>
            <p>المرجو التأكد من تشغيل <b>MySQL</b> في برنامج XAMPP.</p>
         </div>");
}
?>