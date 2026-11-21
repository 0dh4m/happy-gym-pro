<?php
/**
 * Copyright (C) 2026 Adham Cherkaoui Eddeqaqi (0dh4m). All rights reserved.
 * Proprietary and confidential. Unauthorized copying or distribution is strictly prohibited.
 * Author: Adham Cherkaoui Eddeqaqi (0dh4m) <adamcherkaoui99@gmail.com>
 * GitHub: https://github.com/0dh4m
 */
 
$page_title = 'Happy Meal Planner | حاسبة السعرات';
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM users WHERE id = $user_id");

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    header("Location: auth/login.php");
    exit();
}

$user_name = $user['name'] ?? 'كابتن';
$user_weight = $user['weight'] ?? 75;
$user_height = $user['height'] ?? 180;
$user_age = $user['age'] ?? 25;
$user_avatar = !empty($user['avatar']) ? $user['avatar'] : 'uploads/default-avatar.png';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['calculate_macros'])) {
    $weight = floatval($_POST['weight']);
    $height = floatval($_POST['height']);
    $age = intval($_POST['age']);
    $gender = $_POST['gender'];
    $fitnessGoal = $_POST['fitnessGoal'];
    $activity = floatval($_POST['activity']);

    $bmr = ($gender == 'male') 
        ? (10 * $weight) + (6.25 * $height) - (5 * $age) + 5 
        : (10 * $weight) + (6.25 * $height) - (5 * $age) - 161;

    $tdee = $bmr * $activity;

    if ($fitnessGoal == 'bulking') {
        $targetCalories = $tdee + 500;
        $protein = $weight * 2.2; 
        $fat = ($targetCalories * 0.25) / 9; 
    } elseif ($fitnessGoal == 'cutting') {
        $targetCalories = $tdee - 500;
        $protein = $weight * 2.4; 
        $fat = ($targetCalories * 0.25) / 9;
    } else {
        $targetCalories = $tdee;
        $protein = $weight * 2.0;
        $fat = ($targetCalories * 0.25) / 9;
    }
    
    $carbs = ($targetCalories - (($protein * 4) + ($fat * 9))) / 4;

    $stmt = $conn->prepare("UPDATE users SET weight = ?, height = ?, age = ?, gender = ?, activity = ?, fitness_goal = ? WHERE id = ?");
    $stmt->bind_param("dddsisi", $weight, $height, $age, $gender, $activity, $fitnessGoal, $user_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['macros'] = [
        'calories' => round($targetCalories),
        'protein' => round($protein),
        'carbs' => round($carbs),
        'fat' => round($fat)
    ];

    $_SESSION['success_msg'] = "تم حساب السعرات والماكروز بنجاح!";
    
    $user_weight = $weight;
    $user_height = $height;
    $user_age = $age;
}

include 'includes/header.php';
?>

    <main class="max-w-4xl mx-auto px-6">
        
        <div class="mb-10 text-center md:text-right">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-white mb-3 tracking-tight">حاسبة السعرات الحرارية والماكروز</h1>
            <p class="text-zinc-400 text-lg max-w-2xl">أهلاً بك كابتن <span class="text-amber-500 font-bold"><?= htmlspecialchars($user_name); ?></span>، حدد مؤشراتك البدنية وهدفك لمعرفة الاحتياج الدقيق لجسمك.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
            <form action="calculator.php" method="POST" class="glass-panel p-8 md:col-span-7 space-y-5">
                <h3 class="text-lg font-bold text-amber-500 mb-4 border-b border-white/10 pb-2 flex items-center gap-2">
                    <i class="fa-solid fa-sliders"></i> أدخل بياناتك البدنية
                </h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-zinc-400 mb-1">الوزن (kg)</label>
                        <input type="number" step="0.1" name="weight" value="<?= htmlspecialchars($user_weight); ?>" class="input-field w-full px-4 py-3 rounded-xl focus:outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm text-zinc-400 mb-1">الطول (cm)</label>
                        <input type="number" step="0.1" name="height" value="<?= htmlspecialchars($user_height); ?>" class="input-field w-full px-4 py-3 rounded-xl focus:outline-none" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-zinc-400 mb-1">العمر</label>
                        <input type="number" name="age" value="<?= htmlspecialchars($user_age); ?>" class="input-field w-full px-4 py-3 rounded-xl focus:outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm text-zinc-400 mb-1">الجنس</label>
                        <select name="gender" class="input-field w-full px-4 py-3 rounded-xl focus:outline-none">
                            <option value="male" <?= (isset($user['gender']) && $user['gender'] == 'male') ? 'selected' : ''; ?>>ذكر</option>
                            <option value="female" <?= (isset($user['gender']) && $user['gender'] == 'female') ? 'selected' : ''; ?>>أنثى</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-1">الهدف الرياضي</label>
                    <select name="fitnessGoal" class="input-field w-full px-4 py-3 rounded-xl focus:outline-none">
                        <option value="bulking" <?= (isset($user['fitness_goal']) && $user['fitness_goal'] == 'bulking') ? 'selected' : ''; ?>>تضخيم عضلي (Bulking)</option>
                        <option value="cutting" <?= (isset($user['fitness_goal']) && $user['fitness_goal'] == 'cutting') ? 'selected' : ''; ?>>تنشيف وحرق دهون (Cutting)</option>
                        <option value="maintenance" <?= (isset($user['fitness_goal']) && $user['fitness_goal'] == 'maintenance') ? 'selected' : ''; ?>>محافظة على الوزن (Maintenance)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-1">معدل النشاط البدني</label>
                    <select name="activity" class="input-field w-full px-4 py-3 rounded-xl focus:outline-none">
                        <option value="1.2" <?= (isset($user['activity']) && $user['activity'] == 1.2) ? 'selected' : ''; ?>>خفيف (بدون تمرين تقريباً)</option>
                        <option value="1.55" <?= (isset($user['activity']) && $user['activity'] == 1.55) ? 'selected' : ''; ?>>متوسط (تمرين 3-5 أيام أسبوعياً)</option>
                        <option value="1.725" <?= (isset($user['activity']) && $user['activity'] == 1.725) ? 'selected' : ''; ?>>عالي (تمرين شاق 6-7 أيام)</option>
                    </select>
                </div>

                <button type="submit" name="calculate_macros" class="w-full bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-black font-extrabold py-4 rounded-xl shadow-[0_0_20px_rgba(245,158,11,0.3)] transition mt-4 cursor-pointer">
                    احسب السعرات والماكروز <i class="fa-solid fa-arrow-left ml-2"></i>
                </button>
            </form>

            <div class="glass-panel p-8 md:col-span-5 flex flex-col justify-between">
                <div>
                    <h3 class="text-lg font-bold text-white mb-6 border-b border-white/10 pb-2 flex items-center gap-2">
                        <i class="fa-solid fa-chart-pie text-amber-500"></i> نتيجة التحليل الغذائي
                    </h3>

                    <?php if(isset($_SESSION['macros'])): $m = $_SESSION['macros']; ?>
                        <div class="space-y-4 text-center">
                            <div class="bg-black/30 p-5 rounded-2xl border border-white/10 shadow-inner">
                                <span class="text-zinc-400 text-sm block mb-1">السعرات الحرارية المستهدفة</span>
                                <span class="text-4xl font-black text-amber-500"><?= $m['calories']; ?> <span class="text-sm font-normal text-zinc-400">Kcal</span></span>
                            </div>
                            
                            <div class="grid grid-cols-3 gap-2">
                                <div class="bg-black/30 p-3 rounded-xl border border-white/10">
                                    <span class="text-xs text-zinc-400 block mb-1">بروتين</span>
                                    <span class="text-lg font-extrabold text-emerald-400"><?= $m['protein']; ?>g</span>
                                </div>
                                <div class="bg-black/30 p-3 rounded-xl border border-white/10">
                                    <span class="text-xs text-zinc-400 block mb-1">كربوهيدرات</span>
                                    <span class="text-lg font-extrabold text-amber-400"><?= $m['carbs']; ?>g</span>
                                </div>
                                <div class="bg-black/30 p-3 rounded-xl border border-white/10">
                                    <span class="text-xs text-zinc-400 block mb-1">دهون</span>
                                    <span class="text-lg font-extrabold text-rose-400"><?= $m['fat']; ?>g</span>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-zinc-500 py-16">
                            <i class="fa-solid fa-calculator text-5xl mb-3 opacity-30 text-amber-500"></i>
                            <p class="text-sm">أدخل بياناتك واضغط على زر الحساب لعرض السعرات والماكروز هنا.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mt-6 pt-4 border-t border-white/10">
                    <?php 
                        $linkTarget = "index.php";
                        if (isset($_SESSION['macros']['calories'])) {
                            $linkTarget .= "?target_calories=" . $_SESSION['macros']['calories'];
                        }
                    ?>
                    <a href="<?= $linkTarget ?>" class="block text-center bg-white/10 hover:bg-white/20 border border-white/20 text-white font-extrabold py-3.5 rounded-xl transition text-sm shadow-lg">
                        الانتقال لتوليد الوجبات بهذه السعرات <i class="fa-solid fa-arrow-left ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </main>

<?php include 'includes/footer.php'; ?>