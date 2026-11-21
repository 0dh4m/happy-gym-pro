<?php
/**
 * Copyright (C) 2026 Adham Cherkaoui Eddeqaqi (0dh4m). All rights reserved.
 * Proprietary and confidential. Unauthorized copying or distribution is strictly prohibited.
 * Author: Adham Cherkaoui Eddeqaqi (0dh4m) <adamcherkaoui99@gmail.com>
 * GitHub: https://github.com/0dh4m
 */

session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id']; // استخدام ID المستخدم الحقيقي من الجلسة
    $name = htmlspecialchars($_POST['name']);
    $weight = floatval($_POST['weight']);
    $height = floatval($_POST['height']);
    $age = intval($_POST['age']);

    $avatar_sql_snippet = "";
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['avatar']['name'];
        $filetmp = $_FILES['avatar']['tmp_name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }
            
            $new_filename = "user_" . $user_id . "_" . time() . "." . $ext;
            $destination = "uploads/" . $new_filename;

            if (move_uploaded_file($filetmp, $destination)) {
                $avatar_sql_snippet = ", avatar = '$destination'";
            }
        }
    }

    $sql = "UPDATE users SET name = ?, weight = ?, height = ?, age = ? $avatar_sql_snippet WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdiii", $name, $weight, $height, $age, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "تم تحديث الملف الشخصي بنجاح!";
    } else {
        $_SESSION['error_msg'] = "حدث خطأ أثناء التحديث: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();

    header("Location: index.php");
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>