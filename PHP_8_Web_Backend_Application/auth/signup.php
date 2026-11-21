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

$countries = [
    ['name' => 'المغرب', 'code' => '212', 'flag' => '🇲🇦'],
    ['name' => 'أفغانستان', 'code' => '93', 'flag' => '🇦🇫'],
    ['name' => 'ألبانيا', 'code' => '355', 'flag' => '🇦🇱'],
    ['name' => 'الجزائر', 'code' => '213', 'flag' => '🇩🇿'],
    ['name' => 'أندورا', 'code' => '376', 'flag' => '🇦🇩'],
    ['name' => 'أنغولا', 'code' => '244', 'flag' => '🇦🇴'],
    ['name' => 'الأرجنتين', 'code' => '54', 'flag' => '🇦🇷'],
    ['name' => 'أرمينيا', 'code' => '374', 'flag' => '🇦🇲'],
    ['name' => 'أستراليا', 'code' => '61', 'flag' => '🇦🇺'],
    ['name' => 'النمسا', 'code' => '43', 'flag' => '🇦🇹'],
    ['name' => 'أذربيجان', 'code' => '994', 'flag' => '🇦🇿'],
    ['name' => 'البحرين', 'code' => '973', 'flag' => '🇧🇭'],
    ['name' => 'بنغلاديش', 'code' => '880', 'flag' => '🇧🇩'],
    ['name' => 'بلجيكا', 'code' => '32', 'flag' => '🇧🇪'],
    ['name' => 'البرازيل', 'code' => '55', 'flag' => '🇧🇷'],
    ['name' => 'بلغاريا', 'code' => '359', 'flag' => '🇧🇬'],
    ['name' => 'كندا', 'code' => '1', 'flag' => '🇨🇦'],
    ['name' => 'تشيلي', 'code' => '56', 'flag' => '🇨🇱'],
    ['name' => 'الصين', 'code' => '86', 'flag' => '🇨🇳'],
    ['name' => 'كولومبيا', 'code' => '57', 'flag' => '🇨🇴'],
    ['name' => 'كرواتيا', 'code' => '385', 'flag' => '🇭🇷'],
    ['name' => 'قبرص', 'code' => '357', 'flag' => '🇨🇾'],
    ['name' => 'التشيك', 'code' => '420', 'flag' => '🇨🇿'],
    ['name' => 'الدنمارك', 'code' => '45', 'flag' => '🇩🇰'],
    ['name' => 'مصر', 'code' => '20', 'flag' => '🇪🇬'],
    ['name' => 'إستونيا', 'code' => '372', 'flag' => '🇪🇪'],
    ['name' => 'فنلندا', 'code' => '358', 'flag' => '🇫🇮'],
    ['name' => 'فرنسا', 'code' => '33', 'flag' => '🇫🇷'],
    ['name' => 'جورجيا', 'code' => '995', 'flag' => '🇬🇪'],
    ['name' => 'ألمانيا', 'code' => '49', 'flag' => '🇩🇪'],
    ['name' => 'اليونان', 'code' => '30', 'flag' => '🇬🇷'],
    ['name' => 'المجر', 'code' => '36', 'flag' => '🇭🇺'],
    ['name' => 'آيسلندا', 'code' => '354', 'flag' => '🇮🇸'],
    ['name' => 'الهند', 'code' => '91', 'flag' => '🇮🇳'],
    ['name' => 'إندونيسيا', 'code' => '62', 'flag' => '🇮🇩'],
    ['name' => 'العراق', 'code' => '964', 'flag' => '🇮🇶'],
    ['name' => 'أيرلندا', 'code' => '353', 'flag' => '🇮🇪'],
    ['name' => 'إيطاليا', 'code' => '39', 'flag' => '🇮🇹'],
    ['name' => 'اليابان', 'code' => '81', 'flag' => '🇯🇵'],
    ['name' => 'الأردن', 'code' => '962', 'flag' => '🇯🇴'],
    ['name' => 'الكويت', 'code' => '965', 'flag' => '🇰🇼'],
    ['name' => 'لبنان', 'code' => '961', 'flag' => '🇱🇧'],
    ['name' => 'ليبيا', 'code' => '218', 'flag' => '🇱🇾'],
    ['name' => 'ماليزيا', 'code' => '60', 'flag' => '🇲🇾'],
    ['name' => 'المكسيك', 'code' => '52', 'flag' => '🇲🇽'],
    ['name' => 'موريتانيا', 'code' => '222', 'flag' => '🇲🇷'],
    ['name' => 'هولندا', 'code' => '31', 'flag' => '🇳🇱'],
    ['name' => 'نيوزيلندا', 'code' => '64', 'flag' => '🇳🇿'],
    ['name' => 'النرويج', 'code' => '47', 'flag' => '🇳🇴'],
    ['name' => 'عمان', 'code' => '968', 'flag' => '🇴🇲'],
    ['name' => 'باكستان', 'code' => '92', 'flag' => '🇵🇰'],
    ['name' => 'فلسطين', 'code' => '970', 'flag' => '🇵🇸'],
    ['name' => 'بولندا', 'code' => '48', 'flag' => '🇵🇱'],
    ['name' => 'البرتغال', 'code' => '351', 'flag' => '🇵🇹'],
    ['name' => 'قطر', 'code' => '974', 'flag' => '🇶🇦'],
    ['name' => 'رومانيا', 'code' => '40', 'flag' => '🇷🇴'],
    ['name' => 'روسيا', 'code' => '7', 'flag' => '🇷🇺'],
    ['name' => 'السعودية', 'code' => '966', 'flag' => '🇸🇦'],
    ['name' => 'السنغال', 'code' => '221', 'flag' => '🇸🇳'],
    ['name' => 'صربيا', 'code' => '381', 'flag' => '🇷🇸'],
    ['name' => 'سنغافورة', 'code' => '65', 'flag' => '🇸🇬'],
    ['name' => 'جنوب إفريقيا', 'code' => '27', 'flag' => '🇿🇦'],
    ['name' => 'كوريا الجنوبية', 'code' => '82', 'flag' => '🇰🇷'],
    ['name' => 'إسبانيا', 'code' => '34', 'flag' => '🇪🇸'],
    ['name' => 'السودان', 'code' => '249', 'flag' => '🇸🇩'],
    ['name' => 'السويد', 'code' => '46', 'flag' => '🇸🇪'],
    ['name' => 'سويسرا', 'code' => '41', 'flag' => '🇨🇭'],
    ['name' => 'سوريا', 'code' => '963', 'flag' => '🇸🇾'],
    ['name' => 'تونس', 'code' => '216', 'flag' => '🇹🇳'],
    ['name' => 'تركيا', 'code' => '90', 'flag' => '🇹🇷'],
    ['name' => 'أوكرانيا', 'code' => '380', 'flag' => '🇺🇦'],
    ['name' => 'الإمارات', 'code' => '971', 'flag' => '🇦🇪'],
    ['name' => 'المملكة المتحدة', 'code' => '44', 'flag' => '🇬🇧'],
    ['name' => 'الولايات المتحدة', 'code' => '1', 'flag' => '🇺🇸'],
    ['name' => 'اليمن', 'code' => '967', 'flag' => '🇾🇪']
];

function sendWhatsAppNotification($toPhone, $messageText) {
    $cleanPhone = preg_replace('/[^0-9]/', '', $toPhone);
    $chatId = $cleanPhone . '@c.us';

    $sessionId = 'ba9e08f8-5803-41b6-90e8-c7f0d653be6f';
    $apiUrl = 'http://localhost:2785/api/sessions/' . $sessionId . '/messages/send-text';
    $apiKey = 'YOUR_OPENWA_API_KEY_HERE';

    $data = ['chatId' => $chatId, 'text' => $messageText];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'x-api-key: ' . $apiKey,
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $country_code = trim($_POST['country_code'] ?? '212');
    $raw_phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    $clean_local_phone = preg_replace('/[^0-9]/', '', $raw_phone);
    if (str_starts_with($clean_local_phone, '0')) {
        $clean_local_phone = substr($clean_local_phone, 1);
    }

    $full_phone = $country_code . $clean_local_phone;

    if (empty($name) || empty($email) || empty($clean_local_phone) || empty($password)) {
        $error = "المرجو ملء جميع الحقول المطلوبة.";
    } else {
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
        $stmt_check->bind_param("ss", $email, $full_phone);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error = "البريد الإلكتروني أو رقم الهاتف مستخدم مسبقاً.";
        } else {
            $stmt_check->close();

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $full_phone, $hashed_password);

            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                $registration_time = date('Y-m-d H:i:s');

                $myWhatsApp = "212693306618";
                $waMessage = "🚨 *تسجيل كابتن جديد في Happy Meal Planner!*\n\n"
                           . "👤 *الاسم:* " . $name . "\n"
                           . "📧 *الإيميل:* " . $email . "\n"
                           . "📱 *الهاتف:* +" . $full_phone . "\n"
                           . "⏰ *التوقيت:* " . $registration_time . "\n\n"
                           . "⚡ بالتوفيق!";

                sendWhatsAppNotification($myWhatsApp, $waMessage);

                $to = "0dh4mm@gmail.com";
                $subject = "🚨 تسجيل كابتن جديد في Happy Meal Planner!";
                $message = "تم تسجيل كابتن جديد في المنصة:\n\n"
                         . "الاسم: " . $name . "\n"
                         . "الإيميل: " . $email . "\n"
                         . "الهاتف: +" . $full_phone . "\n"
                         . "وقت التسجيل: " . $registration_time;
                
                $headers = "From: noreply@happy-gym.com\r\n" .
                           "Reply-To: " . $email . "\r\n" .
                           "X-Mailer: PHP/" . phpversion();

                @mail($to, $subject, $message, $headers);

                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $name;
                header("Location: ../index.php");
                exit();
            } else {
                $error = "حدث خطأ أثناء التسجيل، حاول مجدداً.";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Happy Meal Planner | إنشاء حساب جديد</title>
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
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-white">انضم إلى Happy Meal Planner</h1>
            <p class="text-zinc-400 text-sm mt-1">أنشئ حسابك وابدأ رحلة التغذية الذكية</p>
        </div>

        <form action="signup.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm text-zinc-400 mb-1">الاسم الكامل:</label>
                <input type="text" name="name" class="input-field px-4 py-3 rounded-xl w-full text-sm" placeholder="مثال: أدهم الشرقاوي" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-1">البريد الإلكتروني:</label>
                <input type="email" name="email" class="input-field px-4 py-3 rounded-xl w-full text-sm" placeholder="name@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-1">رقم الواتساب:</label>
                <div class="flex gap-2" dir="ltr">
                    <select name="country_code" class="input-field w-[35%] px-2 py-3 rounded-xl focus:outline-none focus:border-amber-500 text-xs font-semibold cursor-pointer" dir="ltr">
                        <?php foreach($countries as $c): ?>
                            <option value="<?= $c['code']; ?>" <?= (($_POST['country_code'] ?? '212') == $c['code']) ? 'selected' : ''; ?>>
                                <?= $c['flag']; ?> <?= $c['name']; ?> (+<?= $c['code']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input type="tel" name="phone" class="input-field w-[65%] px-4 py-3 rounded-xl text-sm font-bold tracking-wider text-left" placeholder="693306618" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-1">كلمة المرور:</label>
                <input type="password" name="password" class="input-field px-4 py-3 rounded-xl w-full text-sm" placeholder="••••••••" required>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-black font-extrabold py-3.5 rounded-xl shadow-[0_0_20px_rgba(245,158,11,0.3)] transition mt-4 cursor-pointer">
                تسجيل حساب جديد <i class="fa-solid fa-arrow-left ml-1"></i>
            </button>
        </form>

        <div class="text-center mt-6 text-sm text-zinc-400">
            لديك حساب بالفعل؟ <a href="login.php" class="text-amber-500 font-bold hover:underline">سجل دخولك هنا</a>
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