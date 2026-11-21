<?php
/**
 * Copyright (C) 2026 Adham Cherkaoui Eddeqaqi (0dh4m). All rights reserved.
 * Proprietary and confidential. Unauthorized copying or distribution is strictly prohibited.
 * Author: Adham Cherkaoui Eddeqaqi (0dh4m) <adamcherkaoui99@gmail.com>
 * GitHub: https://github.com/0dh4m
 */
 
$page_title = 'Happy Meal Planner | AI Coach';
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_manual_meal'])) {
    $meal_name = $_POST['meal_name'] ?? 'وجبة مخصصة';
    $calories = intval($_POST['calories'] ?? 700);
    $meal_content = $_POST['meal_content'] ?? '';

    $stmt = $conn->prepare("INSERT INTO logged_meals (user_id, meal_name, calories, meal_content) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $user_id, $meal_name, $calories, $meal_content);
    
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "تم حفظ الوجبة في أرشيفك بنجاح!";
    } else {
        $_SESSION['error_msg'] = "حدث خطأ أثناء حفظ الوجبة.";
    }
    $stmt->close();
    header("Location: index.php");
    exit();
}

$result = $conn->query("SELECT * FROM users WHERE id = $user_id");
if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    header("Location: auth/login.php");
    exit();
}

$user_name = isset($user['name']) ? $user['name'] : 'كابتن';
$user_weight = isset($user['weight']) ? $user['weight'] : 75;
$user_height = isset($user['height']) ? $user['height'] : 180;
$user_age = isset($user['age']) ? $user['age'] : 25;
$user_avatar = isset($user['avatar']) && !empty($user['avatar']) ? $user['avatar'] : 'uploads/default-avatar.png';

$default_calories = 700;
if (isset($_GET['target_calories']) && !empty($_GET['target_calories'])) {
    $default_calories = intval($_GET['target_calories']);
} elseif (isset($_SESSION['macros']['calories']) && !empty($_SESSION['macros']['calories'])) {
    $default_calories = intval($_SESSION['macros']['calories']);
}

include 'includes/header.php';
?>

    <main class="max-w-7xl mx-auto px-6">
        
        <div class="mb-10 text-center md:text-right">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-white mb-3 tracking-tight">مولد الوجبات الذكي</h1>
            <p class="text-zinc-400 text-lg max-w-2xl">أهلاً بك كابتن <span class="text-amber-500 font-bold"><?= htmlspecialchars($user_name); ?></span>، اختر المكونات أو ارفع صورة للمكونات المتوفرة لديك لتوليد وجبتك.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-7 space-y-6">
                <form action="process.php" method="POST" enctype="multipart/form-data" id="mealForm" class="glass-panel p-6 sm:p-8 space-y-6">
                    
                    <h3 class="text-lg font-bold text-amber-500 border-b border-white/10 pb-2 flex items-center gap-2">
                        <i class="fa-solid fa-fire"></i> تحديد السعرات والمكونات
                    </h3>
                    
                    <div class="bg-black/30 p-4 rounded-xl border border-white/10">
                        <label class="block text-sm font-bold text-white mb-2">
                            <i class="fa-solid fa-fire text-amber-500 ml-1"></i> السعرات الحرارية المطلوبة في هذه الوجبة (Kcal)
                        </label>
                        <input type="number" name="target_calories" id="targetCaloriesInput" value="<?= $default_calories; ?>" step="1" min="200" max="5000" class="input-field w-full px-4 py-3 rounded-xl text-lg font-bold text-amber-500" required>
                    </div>

                    <div class="bg-black/30 p-4 rounded-xl border border-white/10">
                        <label class="block text-sm font-bold text-white mb-2">
                            <i class="fa-solid fa-camera text-amber-500 ml-1"></i> أو ارفع صورة للمكونات / الثلاجة (اختياري):
                        </label>
                        <input type="file" name="food_image" accept="image/*" class="input-field w-full text-sm py-2 cursor-pointer">
                        <p class="text-xs text-zinc-400 mt-1">الذكاء الاصطناعي سيقوم بقراءة الصورة واستخراج المكونات تلقائياً.</p>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <h4 class="text-xs font-bold text-zinc-400 mb-3"><i class="fa-solid fa-wheat-awn text-amber-500 ml-1"></i> 1. الكربوهيدرات ومصادر الطاقة</h4>
                            <div class="flex flex-wrap gap-2 max-h-40 overflow-y-auto p-3 bg-black/40 rounded-xl border border-white/10 custom-scroll">
                                <?php 
                                $carbs = ["الأرز", "المعكرونة", "الشوفان", "البطاطس", "البطاطا الحلوة", "الخبز", "الكسكس", "الكينوا", "الذرة", "التوست", "الشعير", "البرغل", "البسكويت", "رقائق الذرة", "النشاء", "طحين القمح", "نودلز", "الكعك", "التمر", "الزبيب", "التين", "الموز", "عجينة البيتزا", "الكرواسون", "الفطير"];
                                foreach($carbs as $item):
                                ?>
                                <label class="relative cursor-pointer">
                                    <input type="checkbox" name="ingredients[]" value="<?= $item; ?>" <?= in_array($item, ["الأرز", "المعكرونة"]) ? 'checked' : ''; ?> class="chip-checkbox peer sr-only">
                                    <span class="block px-3 py-1.5 rounded-full text-xs font-semibold transition shadow-sm bg-black/5 border border-black/10 text-zinc-600 dark:bg-white/5 dark:border-white/10 dark:text-zinc-300 hover:border-amber-500/50 peer-checked:bg-amber-500 peer-checked:border-amber-500 peer-checked:text-white dark:peer-checked:bg-amber-500/15 dark:peer-checked:border-amber-500/50 dark:peer-checked:text-amber-400"><?= $item; ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs font-bold text-zinc-400 mb-3"><i class="fa-solid fa-drumstick-bite text-amber-500 ml-1"></i> 2. مصادر البروتين واللحوم والأجبان</h4>
                            <div class="flex flex-wrap gap-2 max-h-40 overflow-y-auto p-3 bg-black/40 rounded-xl border border-white/10 custom-scroll">
                                <?php 
                                $proteins = ["دجاج", "لحم مفروم", "لحم بقري", "بيض", "التونة", "السلمون", "واي بروتين", "ديك رومي", "لحم غنمي", "سردين", "مكريل", "سمك القد", "الجمبري", "جبن قريش", "جبن أبيض", "جبن أحمر", "جبن موزاريلا", "الحليب", "اللبن", "ياغورت يوناني", "كازين", "لحم عجل", "مرق اللحم", "السجق", "المرتديلا", "سمك الهامور", "سمك التراوت", "جبن ريكوتا", "جبن بارميزان", "كريمة الطبخ", "بروتين الصويا", "التوفو", "نقانق", "برغر"];
                                foreach($proteins as $item):
                                ?>
                                <label class="relative cursor-pointer">
                                    <input type="checkbox" name="ingredients[]" value="<?= $item; ?>" <?= in_array($item, ["دجاج", "بيض"]) ? 'checked' : ''; ?> class="chip-checkbox peer sr-only">
                                    <span class="block px-3 py-1.5 rounded-full text-xs font-semibold transition shadow-sm bg-black/5 border border-black/10 text-zinc-600 dark:bg-white/5 dark:border-white/10 dark:text-zinc-300 hover:border-amber-500/50 peer-checked:bg-amber-500 peer-checked:border-amber-500 peer-checked:text-white dark:peer-checked:bg-amber-500/15 dark:peer-checked:border-amber-500/50 dark:peer-checked:text-amber-400"><?= $item; ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs font-bold text-zinc-400 mb-3"><i class="fa-solid fa-carrot text-amber-500 ml-1"></i> 3. الخضروات</h4>
                            <div class="flex flex-wrap gap-2 max-h-40 overflow-y-auto p-3 bg-black/40 rounded-xl border border-white/10 custom-scroll">
                                <?php 
                                $vegetables = ["بروكلي", "سبانخ", "خس", "طماطم", "جزر", "بصل", "ثوم", "فلفل حلو", "خيار", "ملفوف", "قرنبيط", "كوسة", "بازلاء", "فاصوليا خضراء", "قرع عسلي", "بنجر", "بقدونس", "قزبر", "كرفس", "نعناع", "جرجير", "فجل", "باذنجان", "فطر", "هليون", "خرشوف", "بامية", "زيتون", "لفت", "سلق", "طماطم كريز", "فلفل حار"];
                                foreach($vegetables as $item):
                                ?>
                                <label class="relative cursor-pointer">
                                    <input type="checkbox" name="ingredients[]" value="<?= $item; ?>" class="chip-checkbox peer sr-only">
                                    <span class="block px-3 py-1.5 rounded-full text-xs font-semibold transition shadow-sm bg-black/5 border border-black/10 text-zinc-600 dark:bg-white/5 dark:border-white/10 dark:text-zinc-300 hover:border-amber-500/50 peer-checked:bg-amber-500 peer-checked:border-amber-500 peer-checked:text-white dark:peer-checked:bg-amber-500/15 dark:peer-checked:border-amber-500/50 dark:peer-checked:text-amber-400"><?= $item; ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs font-bold text-zinc-400 mb-3"><i class="fa-solid fa-seedling text-amber-500 ml-1"></i> 4. البقوليات والقطاني</h4>
                            <div class="flex flex-wrap gap-2 max-h-40 overflow-y-auto p-3 bg-black/40 rounded-xl border border-white/10 custom-scroll">
                                <?php 
                                $legumes = ["اللوبيا", "العدس", "الحمص", "الفول", "فاصوليا سوداء", "بازلاء جافة", "فول الصويا", "الترمس", "الطحينة", "فاصوليا بيضاء", "فاصوليا حمراء", "شوربة العدس", "الفلافل"];
                                foreach($legumes as $item):
                                ?>
                                <label class="relative cursor-pointer">
                                    <input type="checkbox" name="ingredients[]" value="<?= $item; ?>" <?= in_array($item, ["اللوبيا"]) ? 'checked' : ''; ?> class="chip-checkbox peer sr-only">
                                    <span class="block px-3 py-1.5 rounded-full text-xs font-semibold transition shadow-sm bg-black/5 border border-black/10 text-zinc-600 dark:bg-white/5 dark:border-white/10 dark:text-zinc-300 hover:border-amber-500/50 peer-checked:bg-amber-500 peer-checked:border-amber-500 peer-checked:text-white dark:peer-checked:bg-amber-500/15 dark:peer-checked:border-amber-500/50 dark:peer-checked:text-amber-400"><?= $item; ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs font-bold text-zinc-400 mb-3"><i class="fa-solid fa-jar text-amber-500 ml-1"></i> 5. الدهون والمكسرات والزيوت</h4>
                            <div class="flex flex-wrap gap-2 max-h-40 overflow-y-auto p-3 bg-black/40 rounded-xl border border-white/10 custom-scroll">
                                <?php 
                                $fats = ["زبدة الفول السوداني", "لوز", "كركاع", "زيت الزيتون", "زيت نباتي", "زبدة", "زيت جوز الهند", "أفوكادو", "بذور الكتان", "بذور الشيا", "بذور اليقطين", "بذور دوار الشمس", "كاجو", "بندق", "فستق", "جوز الأمريكي", "سمسم", "طحينة", "شوكولاتة سوداء", "كريمة الخفق", "مكسرات مشكلة"];
                                foreach($fats as $item):
                                ?>
                                <label class="relative cursor-pointer">
                                    <input type="checkbox" name="ingredients[]" value="<?= $item; ?>" class="chip-checkbox peer sr-only">
                                    <span class="block px-3 py-1.5 rounded-full text-xs font-semibold transition shadow-sm bg-black/5 border border-black/10 text-zinc-600 dark:bg-white/5 dark:border-white/10 dark:text-zinc-300 hover:border-amber-500/50 peer-checked:bg-amber-500 peer-checked:border-amber-500 peer-checked:text-white dark:peer-checked:bg-amber-500/15 dark:peer-checked:border-amber-500/50 dark:peer-checked:text-amber-400"><?= $item; ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10">
                        <button type="submit" name="generate_meal" id="submitBtn" class="w-full bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-black font-extrabold py-4 rounded-xl shadow-[0_0_20px_rgba(245,158,11,0.3)] transition flex items-center justify-center gap-3 cursor-pointer">
                            <span>توليد الوجبة بالذكاء الاصطناعي</span>
                            <i class="fa-solid fa-wand-magic-sparkles"></i> 
                        </button>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-5 flex flex-col gap-6">
                <div class="glass-panel p-6 sm:p-8 flex flex-col relative overflow-hidden">
                    <div class="absolute -top-24 -left-24 w-48 h-48 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="flex items-center justify-between border-b border-white/10 pb-4 mb-6 relative z-10">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500 shadow-inner">
                                <i class="fa-solid fa-clipboard-check text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-extrabold text-white">التقرير الغذائي الذكي</h3>
                                <p class="text-[11px] text-zinc-400">محلل بواسطة الذكاء الاصطناعي</p>
                            </div>
                        </div>
                        
                        <?php if(isset($_SESSION['ai_result'])): ?>
                            <form action="index.php" method="POST">
                                <input type="hidden" name="save_manual_meal" value="1">
                                <input type="hidden" name="meal_name" value="<?= htmlspecialchars($_SESSION['generated_meal_name'] ?? 'وجبة مخصصة'); ?>">
                                <input type="hidden" name="calories" value="<?= htmlspecialchars($_SESSION['generated_meal_calories'] ?? 700); ?>">
                                <input type="hidden" name="meal_content" value="<?= htmlspecialchars($_SESSION['ai_result']); ?>">
                                <button type="submit" class="bg-amber-500 hover:bg-amber-400 text-black px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-[0_0_15px_rgba(245,158,11,0.3)] cursor-pointer">
                                    <i class="fa-solid fa-bookmark"></i> حفظ الوجبة
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div class="max-h-[460px] overflow-y-auto pr-2 relative z-10 custom-scroll">
                        
                        <?php if(isset($_SESSION['ai_result'])): ?>
                            <?php 
                                $raw_text = $_SESSION['ai_result'];
                                $lines = explode("\n", trim($raw_text));
                                $meal_title = !empty($lines[0]) ? trim(str_replace(['*', '#'], '', $lines[0])) : "طبق الوجبة الصحية المتكاملة";
                            ?>
                            <div class="space-y-4">
                                <div class="bg-gradient-to-r from-amber-500/15 to-transparent border-r-4 border-amber-500 p-4 rounded-xl shadow-sm">
                                    <span class="text-[10px] font-bold text-amber-500 uppercase tracking-wider block mb-1">اسم الوجبة المقترحة</span>
                                    <h4 class="text-base font-extrabold text-white flex items-center gap-2">
                                        <i class="fa-solid fa-plate-wheat text-amber-500"></i> <?= htmlspecialchars($meal_title); ?>
                                    </h4>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center">
                                    <div class="bg-black/30 p-2.5 rounded-xl border border-white/10 shadow-sm">
                                        <i class="fa-solid fa-fire text-amber-500 mb-1 block text-sm"></i>
                                        <span class="text-[10px] text-zinc-400 block">السعرات</span>
                                        <span class="text-xs font-bold text-white"><?= $_SESSION['generated_meal_calories'] ?? 700; ?> Kcal</span>
                                    </div>
                                    <div class="bg-black/30 p-2.5 rounded-xl border border-white/10 shadow-sm">
                                        <i class="fa-solid fa-dumbbell text-emerald-400 mb-1 block text-sm"></i>
                                        <span class="text-[10px] text-zinc-400 block">البروتين</span>
                                        <span class="text-xs font-bold text-white">عالي الجودة</span>
                                    </div>
                                    <div class="bg-black/30 p-2.5 rounded-xl border border-white/10 shadow-sm">
                                        <i class="fa-solid fa-wheat-awn text-amber-400 mb-1 block text-sm"></i>
                                        <span class="text-[10px] text-zinc-400 block">الكارب</span>
                                        <span class="text-xs font-bold text-white">طاقة صافية</span>
                                    </div>
                                    <div class="bg-black/30 p-2.5 rounded-xl border border-white/10 shadow-sm">
                                        <i class="fa-solid fa-droplet text-cyan-400 mb-1 block text-sm"></i>
                                        <span class="text-[10px] text-zinc-400 block">الدهون</span>
                                        <span class="text-xs font-bold text-white">صحية ومدروسة</span>
                                    </div>
                                </div>

                                <div class="space-y-2 pt-2">
                                    <h5 class="text-xs font-extrabold text-amber-500 uppercase tracking-wider flex items-center gap-2 pb-1">
                                        <i class="fa-solid fa-scale-balanced"></i> تفاصيل التحضير والمقادير:
                                    </h5>
                                    
                                    <?php 
                                        foreach($lines as $line) {
                                            $line = trim(str_replace(['*', '#'], '', $line));
                                            if(empty($line) || $line === $meal_title) continue;

                                            if(preg_match('/^\d+\./', $line) || str_contains($line, 'اسلق') || str_contains($line, 'في مقلاة') || str_contains($line, 'في طبق') || str_contains($line, 'أضف') || str_contains($line, 'قم بتسخين')) {
                                                echo '<div class="flex items-start gap-2.5 bg-black/30 p-3 rounded-xl border border-white/10 mb-2">
                                                        <i class="fa-solid fa-circle-check text-amber-500 mt-1 shrink-0 text-xs"></i>
                                                        <span class="text-zinc-200 text-xs sm:text-sm leading-relaxed">' . htmlspecialchars($line) . '</span>
                                                      </div>';
                                            } 
                                            else if(str_contains($line, 'غرام') || str_contains($line, 'ملعقة') || str_contains($line, 'حبة') || str_contains($line, 'عدد') || str_contains($line, 'جرام')) {
                                                echo '<div class="flex items-center bg-amber-500/10 px-3.5 py-2.5 rounded-xl border border-amber-500/20 mb-2">
                                                        <span class="font-bold text-white text-xs sm:text-sm flex items-center gap-2">
                                                            <i class="fa-solid fa-utensils text-amber-500 text-xs"></i> ' . htmlspecialchars($line) . '
                                                        </span>
                                                      </div>';
                                            }
                                            else {
                                                echo '<p class="text-xs sm:text-sm text-zinc-300 font-semibold mb-2 pt-2 border-t border-white/10">' . htmlspecialchars($line) . '</p>';
                                            }
                                        }
                                    ?>
                                </div>
                            </div>
                            <?php 
                                unset($_SESSION['ai_result']);
                                unset($_SESSION['generated_meal_name']);
                                unset($_SESSION['generated_meal_calories']);
                            ?>
                        <?php else: ?>
                            <div class="text-center py-20 px-4">
                                <div class="w-16 h-16 rounded-2xl bg-amber-500/10 text-amber-500 border border-amber-500/20 flex items-center justify-center mx-auto mb-4 text-2xl shadow-inner">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                                </div>
                                <h4 class="text-base font-bold text-white mb-1">في انتظار توليد وجبتك الذكية</h4>
                                <p class="text-zinc-400 text-xs max-w-xs mx-auto">قم بتحديد سعراتك واختيار المكونات، ثم اضغط على زر التوليد لتظهر تفاصيل الوجبة هنا بشكل منسق ومريح للقراءة.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="glass-panel p-5 flex flex-col">
                    <div class="flex items-center gap-3 border-b border-white/10 pb-2.5 mb-3">
                        <div class="w-7 h-7 rounded-full bg-amber-500/10 flex items-center justify-center text-amber-500">
                            <i class="fa-solid fa-comments text-xs"></i>
                        </div>
                        <h3 class="text-sm font-bold text-white">اسأل المدرب الذكي</h3>
                    </div>
                    <!-- تم تكبير المساحة قليلاً من h-24 إلى h-36 لتكون أكبر شوية ومريحة -->
                    <div id="chatResponse" class="bg-black/30 p-3 rounded-xl border border-white/10 text-xs text-zinc-300 h-36 overflow-y-auto mb-3 leading-relaxed custom-scroll">
                        أهلاً بك كابتن! اسألني أي سؤال بخصوص التغذية أو السعرات وسأجيبك فوراً.
                    </div>
                    <div class="flex gap-2">
                        <input type="text" id="chatInput" placeholder="اسأل المدرب..." class="input-field text-xs py-2 px-3 w-full rounded-xl">
                        <button type="button" onclick="sendChat()" class="bg-amber-500 hover:bg-amber-400 text-black px-4 py-2 rounded-xl font-bold transition flex items-center justify-center cursor-pointer shadow-md text-xs">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </main>

<?php include 'includes/footer.php'; ?>