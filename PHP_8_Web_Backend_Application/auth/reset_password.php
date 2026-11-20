<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['reset_user_id'])) {
    header("Location: forgot_password.php");
    exit();
}

$user_id = $_SESSION['reset_user_id'];
$phone = $_SESSION['reset_target_phone'] ?? '';
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_otp = trim($_POST['otp'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($entered_otp) || empty($new_password) || empty($confirm_password)) {
        $error = "يرجى ملء جميع الحقول.";
    } elseif ($new_password !== $confirm_password) {
        $error = "كلمتا المرور غير متطابقتين.";
    } elseif (strlen($new_password) < 6) {
        $error = "كلمة المرور يجب أن تتكون من 6 خانات على الأقل.";
    } else {
        $stmt = $conn->prepare("SELECT id, reset_otp_expiry FROM users WHERE id = ? AND reset_otp = ?");
        $stmt->bind_param("is", $user_id, $entered_otp);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $now = date('Y-m-d H:i:s');

            if ($now > $row['reset_otp_expiry']) {
                $error = "انتهت صلاحية رمز التحقق، يرجى طلب رمز جديد.";
            } else {
                $hashed_pass = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE users SET password = ?, reset_otp = NULL, reset_otp_expiry = NULL WHERE id = ?");
                $update->bind_param("si", $hashed_pass, $user_id);
                $update->execute();
                $update->close();

                unset($_SESSION['reset_user_id']);
                unset($_SESSION['reset_target_phone']);

                $_SESSION['success_msg'] = "تم تغيير كلمة المرور بنجاح! يمكنك تسجيل الدخول الآن.";
                header("Location: login.php");
                exit();
            }
        } else {
            $error = "رمز التحقق (OTP) غير صحيح.";
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
    <title>Happy Meals Planner | تأكيد الرمز وتعيين كلمة المرور</title>
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
                <i class="fa-solid fa-key"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-white">إعادة تعيين كلمة المرور</h1>
            <p class="text-zinc-400 text-sm mt-1">أدخل الرمز المكون من 6 أرقام المرسل إلى واتسابك (<?= htmlspecialchars($phone); ?>)</p>
        </div>

        <form action="reset_password.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm text-zinc-400 mb-1">رمز التحقق (OTP):</label>
                <input type="text" name="otp" maxlength="6" class="input-field px-4 py-3 rounded-xl w-full text-center text-xl font-extrabold tracking-widest text-amber-500" placeholder="123456" required>
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-1">كلمة المرور الجديدة:</label>
                <input type="password" name="new_password" class="input-field px-4 py-3 rounded-xl w-full text-sm" placeholder="••••••••" required>
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-1">تأكيد كلمة المرور الجديدة:</label>
                <input type="password" name="confirm_password" class="input-field px-4 py-3 rounded-xl w-full text-sm" placeholder="••••••••" required>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-black font-extrabold py-3.5 rounded-xl shadow-[0_0_20px_rgba(245,158,11,0.3)] transition mt-4 cursor-pointer flex items-center justify-center gap-2">
                <span>حفظ كلمة المرور الجديدة</span>
                <i class="fa-solid fa-check"></i>
            </button>
        </form>

        <div class="text-center mt-6 text-sm text-zinc-400">
            لم يصلك الرمز؟ <a href="forgot_password.php" class="text-emerald-400 font-bold hover:underline">إعادة إرسال الرمز</a>
        </div>
    </div>

    <div id="toastContainer" class="toast-container">
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