<?php
/**
 * Copyright (C) 2026 Adham Cherkaoui Eddeqaqi (0dh4m). All rights reserved.
 * Proprietary and confidential. Unauthorized copying or distribution is strictly prohibited.
 * Author: Adham Cherkaoui Eddeqaqi (0dh4m) <adamcherkaoui99@gmail.com>
 * GitHub: https://github.com/0dh4m
 */
 
$page_title = 'Happy Meal Planner | الوجبات المحفوظة';
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['delete_meal_id'])) {
    $meal_id_to_delete = intval($_POST['delete_meal_id']);
    $del_stmt = $conn->prepare("DELETE FROM logged_meals WHERE id = ? AND user_id = ?");
    $del_stmt->bind_param("ii", $meal_id_to_delete, $user_id);
    if ($del_stmt->execute()) {
        $_SESSION['success_msg'] = "تم حذف الوجبة من أرشيفك بنجاح.";
    }
    $del_stmt->close();
    header("Location: saved_meals.php");
    exit();
}

$user_res = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $user_res->fetch_assoc();

$user_name = $user['name'] ?? 'كابتن';
$user_weight = $user['weight'] ?? 75;
$user_height = $user['height'] ?? 180;
$user_age = $user['age'] ?? 25;
$user_avatar = !empty($user['avatar']) ? $user['avatar'] : 'uploads/default-avatar.png';

$meals_res = $conn->query("SELECT * FROM logged_meals WHERE user_id = $user_id ORDER BY created_at DESC");

function parseMeal($content, $calories) {
    $ingredients = [];
    $steps = [];
    $extracted_protein = null;
    $extracted_carbs = null;
    $extracted_fat = null;
    
    $lines = explode("\n", str_replace("\r", "", $content));
    $meal_title = !empty($lines[0]) ? trim(str_replace(['*', '#'], '', $lines[0])) : "وجبة صحية متكاملة";

    $is_steps_section = false;

    foreach ($lines as $line) {
        $line = trim(str_replace(['*', '#'], '', $line));
        if (empty($line) || $line === $meal_title) continue;

        if (str_contains($line, 'البروتين')) {
            preg_match('/[0-9]+/', $line, $matches);
            if (!empty($matches[0])) $extracted_protein = $matches[0] . ' غرام';
            continue;
        }
        if (str_contains($line, 'الكاربوهيدرات') || str_contains($line, 'الكارب')) {
            preg_match('/[0-9]+/', $line, $matches);
            if (!empty($matches[0])) $extracted_carbs = $matches[0] . ' غرام';
            continue;
        }
        if (str_contains($line, 'الدهون')) {
            preg_match('/[0-9]+/', $line, $matches);
            if (!empty($matches[0])) $extracted_fat = $matches[0] . ' غرام';
            continue;
        }

        if (str_contains($line, 'القيم الغذائية') || str_contains($line, 'إجمالي السعرات الحرارية')) {
            continue;
        }

        if (str_contains($line, 'طريقة التحضير') || preg_match('/^\d+\./', $line) || str_contains($line, 'اسلق') || str_contains($line, 'في مقلاة') || str_contains($line, 'في طبق') || str_contains($line, 'أضف') || str_contains($line, 'قم بتسخين') || str_contains($line, 'يغسل') || str_contains($line, 'تقطع') || str_contains($line, 'تخفق') || str_contains($line, 'تسكب') || str_contains($line, 'توزع')) {
            $is_steps_section = true;
        }

        if ($is_steps_section || preg_match('/^\d+\./', $line)) {
            $steps[] = $line;
        } else {
            $ingredients[] = $line;
        }
    }
    
    if (empty($ingredients)) {
        $ingredients[] = "مكونات غذائية متوازنة وموزونة بدقة";
    }
    if (empty($steps)) {
        $steps[] = "1. طهي المكونات بطريقة صحية للحفاظ على القيمة الغذائية.";
    }
    
    $cal = intval($calories) > 0 ? intval($calories) : 700;
    
    $p = $extracted_protein ?? round(($cal * 0.30) / 4) . ' غرام';
    $c = $extracted_carbs ?? round(($cal * 0.50) / 4) . ' غرام';
    $f = $extracted_fat ?? round(($cal * 0.20) / 9) . ' غرام';
    
    return [
        'ingredients' => $ingredients,
        'steps' => $steps,
        'calories' => $cal,
        'protein' => $p,
        'carbs' => $c,
        'fat' => $f
    ];
}

include 'includes/header.php';
?>

    <main class="max-w-7xl mx-auto px-6">
        
        <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-white/10 pb-6">
            <div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-white mb-2 flex items-center gap-3">
                    <i class="fa-solid fa-bookmark text-amber-500"></i> أرشيف الوجبات الذكية
                </h1>
                <p class="text-zinc-400 text-base">استعرض وجباتك الغذائية المحفوظة والمصممة خصيصاً لهدف الكابتن <span class="text-amber-500 font-bold"><?= htmlspecialchars($user_name); ?></span>.</p>
            </div>
            <a href="index.php" class="bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-black font-extrabold px-6 py-3 rounded-xl transition flex items-center justify-center gap-2 shadow-[0_0_20px_rgba(245,158,11,0.3)] w-fit cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i> توليد وجبة جديدة
            </a>
        </div>

        <?php if ($meals_res && $meals_res->num_rows > 0): ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <?php while($meal = $meals_res->fetch_assoc()): 
                    $parsed = parseMeal($meal['meal_content'], $meal['calories']);
                ?>
                    <div class="glass-panel p-6 flex flex-col justify-between hover:border-amber-500/60 transition-all duration-300 relative overflow-hidden">
                        
                        <div class="flex flex-col h-full">
                            <div class="bg-amber-500/10 border-r-4 border-amber-500 p-3.5 rounded-xl flex items-center justify-between mb-4 gap-3 shrink-0">
                                <div>
                                    <h3 class="font-extrabold text-amber-500 text-base leading-snug line-clamp-1"><?= htmlspecialchars($meal['meal_name']); ?></h3>
                                    <span class="text-[11px] text-zinc-400 mt-0.5 flex items-center gap-1"><i class="fa-regular fa-clock"></i> <?= $meal['created_at']; ?></span>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-extrabold text-xs px-2.5 py-1 rounded-lg">
                                        <?= $parsed['calories']; ?> Kcal
                                    </span>
                                    <form action="saved_meals.php" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه الوجبة؟');" class="m-0">
                                        <input type="hidden" name="delete_meal_id" value="<?= $meal['id']; ?>">
                                        <button type="submit" class="bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 px-2.5 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer">
                                            <i class="fa-solid fa-trash-can"></i> حذف
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="max-h-[300px] overflow-y-auto pr-2 custom-scroll space-y-4 mb-4">
                                <div>
                                    <h4 class="text-xs font-bold text-amber-500 mb-2">المقادير والمكونات:</h4>
                                    <div class="space-y-2">
                                        <?php foreach ($parsed['ingredients'] as $ing): ?>
                                            <div class="flex items-center bg-amber-500/5 dark:bg-amber-500/10 px-3.5 py-2.5 rounded-xl border border-amber-500/20">
                                                <span class="font-bold text-zinc-800 dark:text-zinc-100 text-xs sm:text-sm flex items-center gap-2">
                                                    <i class="fa-solid fa-utensils text-amber-500 text-xs"></i> <?= htmlspecialchars($ing); ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-xs font-bold text-amber-500 mb-2">طريقة التحضير:</h4>
                                    <div class="space-y-2">
                                        <?php foreach ($parsed['steps'] as $step): ?>
                                            <div class="flex items-start gap-2.5 bg-zinc-100 dark:bg-dark-800/60 p-2.5 rounded-xl border border-zinc-200 dark:border-zinc-800">
                                                <i class="fa-solid fa-circle-check text-amber-500 mt-1 shrink-0 text-xs"></i>
                                                <span class="text-zinc-700 dark:text-zinc-200 text-xs sm:text-sm leading-relaxed"><?= htmlspecialchars($step); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto pt-4 border-t border-zinc-200 dark:border-zinc-800 shrink-0">
                                <h4 class="text-xs font-bold text-amber-500 mb-2">القيم الغذائية التقديرية:</h4>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center">
                                    <div class="bg-brand-500/5 dark:bg-brand-500/10 p-2 rounded-xl border border-brand-500/20 shadow-sm">
                                        <i class="fa-solid fa-fire text-amber-500 mb-0.5 block text-xs"></i>
                                        <span class="text-[9px] text-zinc-400 block">السعرات</span>
                                        <span class="text-xs font-bold text-zinc-900 dark:text-white"><?= $parsed['calories']; ?> Kcal</span>
                                    </div>
                                    <div class="bg-brand-500/5 dark:bg-brand-500/10 p-2 rounded-xl border border-brand-500/20 shadow-sm">
                                        <i class="fa-solid fa-dumbbell text-emerald-500 mb-0.5 block text-xs"></i>
                                        <span class="text-[9px] text-zinc-400 block">البروتين</span>
                                        <span class="text-xs font-bold text-zinc-900 dark:text-white"><?= $parsed['protein']; ?></span>
                                    </div>
                                    <div class="bg-brand-500/5 dark:bg-brand-500/10 p-2 rounded-xl border border-brand-500/20 shadow-sm">
                                        <i class="fa-solid fa-wheat-awn text-amber-500 mb-0.5 block text-xs"></i>
                                        <span class="text-[9px] text-zinc-400 block">الكارب</span>
                                        <span class="text-xs font-bold text-zinc-900 dark:text-white"><?= $parsed['carbs']; ?></span>
                                    </div>
                                    <div class="bg-brand-500/5 dark:bg-brand-500/10 p-2 rounded-xl border border-brand-500/20 shadow-sm">
                                        <i class="fa-solid fa-droplet text-blue-500 mb-0.5 block text-xs"></i>
                                        <span class="text-[9px] text-zinc-400 block">الدهون</span>
                                        <span class="text-xs font-bold text-zinc-900 dark:text-white"><?= $parsed['fat']; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="glass-panel text-center py-20 px-6 max-w-xl mx-auto border border-zinc-200 dark:border-zinc-800/80">
                <div class="w-16 h-16 rounded-2xl bg-amber-500/10 text-amber-500 border border-amber-500/20 flex items-center justify-center mx-auto mb-4 text-2xl shadow-inner">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <h3 class="text-xl font-bold text-zinc-900 dark:text-white mb-2">لا توجد أي وجبات محفوظة حالياً</h3>
                <p class="text-zinc-500 dark:text-zinc-400 text-sm max-w-md mx-auto mb-6">توجه إلى صفحة مولد الوجبات الذكي، حدد سعراتك ومكوناتك المفضلة، واضغط على توليد ثم احفظ الوجبة لتظهر هنا بشكل أنيق.</p>
                <a href="index.php" class="bg-amber-500 hover:bg-amber-600 text-black font-bold px-6 py-3 rounded-xl transition inline-flex items-center gap-2 shadow-lg cursor-pointer">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> ابدأ توليد أول وجبة
                </a>
            </div>
        <?php endif; ?>

    </main>

<?php include 'includes/footer.php'; ?>