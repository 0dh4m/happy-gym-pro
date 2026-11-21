<?php
/**
 * Copyright (C) 2026 Adham Cherkaoui Eddeqaqi (0dh4m). All rights reserved.
 * Proprietary and confidential. Unauthorized copying or distribution is strictly prohibited.
 * Author: Adham Cherkaoui Eddeqaqi (0dh4m) <0dh4mm@gmail.com>
 * GitHub: https://github.com/0dh4m
 */

session_start();
require '../includes/db.php';

$error = '';
$success = '';

if (isset($_SESSION['success_msg'])) {
    $success = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "المرجو ملء جميع الحقول.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $name, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $name;
                header("Location: ../index.php");
                exit();
            } else {
                $error = "كلمة المرور غير صحيحة.";
            }
        } else {
            $error = "البريد الإلكتروني غير مسجل.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Happy Meal Planner | تسجيل الدخول</title>
    <link rel="icon" type="image/png" href="../uploads/logo.png?v=3">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css?v=5.0">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Cairo', 'sans-serif'] },
                    colors: {
                        brand: { 500: '#f59e0b', 600: '#d97706' },
                        dark: { 800: '#1a2234', 900: '#121826', 950: '#040711' }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen flex items-center justify-center p-6 relative antialiased selection:bg-amber-500 selection:text-black">

    <button onclick="toggleTheme()" class="absolute top-6 left-6 bg-white/5 border border-white/10 w-10 h-10 rounded-xl text-amber-500 flex items-center justify-center hover:border-amber-500 transition cursor-pointer shadow-lg backdrop-blur-md z-20" title="تغيير المظهر">
        <i id="themeIcon" class="fa-solid fa-moon"></i>
    </button>

    <div class="glass-panel w-full max-w-md p-8 relative z-10">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-500/10 text-amber-500 text-2xl mb-3 shadow-inner border border-amber-500/20">
                <i class="fa-solid fa-right-to-bracket"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-white">مرحباً بعودتك كابتن</h1>
            <p class="text-zinc-400 text-sm mt-1">سجل دخولك لمتابعة وجباتك وبرنامجك</p>
        </div>

        <form action="login.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm text-zinc-400 mb-1">البريد الإلكتروني:</label>
                <input type="email" name="email" class="input-field px-4 py-3 rounded-xl w-full text-sm" placeholder="name@example.com" required>
            </div>
            <div>
                <label class="block text-sm text-zinc-400 mb-1">كلمة المرور:</label>
                <input type="password" name="password" class="input-field px-4 py-3 rounded-xl w-full text-sm" placeholder="••••••••" required>
            </div>

            <div class="flex items-center justify-between text-sm pt-1">
                <a href="forgot_password.php" class="text-amber-500 hover:underline">نسيت كلمة المرور؟</a>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-black font-extrabold py-3.5 rounded-xl shadow-[0_0_20px_rgba(245,158,11,0.3)] transition mt-4 cursor-pointer">
                تسجيل الدخول <i class="fa-solid fa-arrow-left ml-1"></i>
            </button>
        </form>

        <div class="text-center mt-6 text-sm text-zinc-400">
            ليس لديك حساب؟ <a href="signup.php" class="text-amber-500 font-bold hover:underline">أنشئ حساباً جديداً</a>
        </div>
    </div>

    <div id="toastContainer" class="toast-container">
        <?php if (!empty($success)): ?>
            <div class="toast success">
                <i class="fa-solid fa-circle-check text-2xl"></i>
                <span><?= $success; ?></span>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="toast error">
                <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                <span><?= $error; ?></span>
            </div>
        <?php endif; ?>
    </div>

    <div class="cursor-wrapper" id="customCursor">
        <div class="cursor-line cursor-line-t"></div>
        <div class="cursor-line cursor-line-b"></div>
        <div class="cursor-line cursor-line-l"></div>
        <div class="cursor-line cursor-line-r"></div>
        <div class="cursor-nucleus"></div>
        <div class="cursor-bracket cursor-bracket-tl"></div>
        <div class="cursor-bracket cursor-bracket-tr"></div>
        <div class="cursor-bracket cursor-bracket-bl"></div>
        <div class="cursor-bracket cursor-bracket-br"></div>
    </div>

    <script src="../assets/js/script.js?v=5.0"></script>
</body>
</html>