<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../includes/db.php';

$error = '';
$success = '';

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

function sendWhatsAppOTP($toPhone, $otpCode) {
    $cleanPhone = preg_replace('/[^0-9]/', '', $toPhone);
    $chatId = $cleanPhone . '@c.us';

    $sessionId = 'ba9e08f8-5803-41b6-90e8-c7f0d653be6f';
    $apiUrl = 'http://localhost:2785/api/sessions/' . $sessionId . '/messages/send-text';
    $apiKey = 'YOUR_OPENWA_API_KEY_HERE';

    $message = "🔐 *رمز استرجاع الحساب - Happy Meal Planner PRO*\n\n"
             . "رمز التحقق (OTP) الخاص بك هو:\n"
             . "👉 *" . $otpCode . "*\n\n"
             . "⏳ هذا الرمز صالح لمدة 10 دقائق فقط.\n"
             . "⚠️ لا تشارك هذا الرمز مع أي شخص.";

    $data = ['chatId' => $chatId, 'text' => $message];

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
    $country_code = trim($_POST['country_code'] ?? '212');
    $raw_phone = trim($_POST['phone'] ?? '');

    $clean_local_phone = preg_replace('/[^0-9]/', '', $raw_phone);
    if (str_starts_with($clean_local_phone, '0')) {
        $clean_local_phone = substr($clean_local_phone, 1);
    }

    $full_phone = $country_code . $clean_local_phone;

    if (empty($clean_local_phone)) {
        $error = "يرجى إدخال رقم الهاتف.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, phone FROM users WHERE phone = ?");
        $stmt->bind_param("s", $full_phone);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            $user = $res->fetch_assoc();
            $otp = strval(random_int(100000, 999999));
            $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $update = $conn->prepare("UPDATE users SET reset_otp = ?, reset_otp_expiry = ? WHERE id = ?");
            $update->bind_param("ssi", $otp, $expiry, $user['id']);
            $update->execute();
            $update->close();

            sendWhatsAppOTP($full_phone, $otp);

            $_SESSION['reset_user_id'] = $user['id'];
            $_SESSION['reset_target_phone'] = '+' . $full_phone;

            header("Location: reset_password.php");
            exit();
        } else {
            $error = "لم يتم العثور على أي حساب مسجل بهذا الرقم.";
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
    <title>Happy Meal Planner | استرجاع كلمة المرور</title>
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
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-500/10 text-emerald-400 text-2xl mb-3 shadow-inner border border-emerald-500/20">
                <i class="fa-brands fa-whatsapp"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-white">استرجاع كلمة المرور</h1>
            <p class="text-zinc-400 text-sm mt-1">أدخل رقم هاتفك المسجل لتلقي رمز التحقق (OTP) عبر الواتساب</p>
        </div>

        <form action="forgot_password.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm text-zinc-400 mb-1">رقم الواتساب المسجل:</label>
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

            <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-400 text-black font-extrabold py-3.5 rounded-xl shadow-[0_0_20px_rgba(16,185,129,0.3)] transition mt-4 cursor-pointer flex items-center justify-center gap-2">
                <span>إرسال الرمز عبر WhatsApp</span>
                <i class="fa-solid fa-paper-plane text-sm"></i>
            </button>
        </form>

        <div class="text-center mt-6 text-sm text-zinc-400">
            تذكرت كلمة المرور؟ <a href="login.php" class="text-amber-500 font-bold hover:underline">تسجيل الدخول</a>
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