<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Happy Meal Planner' ?></title>
    
    <link rel="icon" type="image/png" href="uploads/logo.png?v=3">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css?v=6.0">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Cairo', 'sans-serif'] },
                    colors: {
                        brand: { 400: '#fbbf24', 500: '#f59e0b', 600: '#d97706' },
                        dark: { 800: '#1a2234', 900: '#121826', 950: '#040711' }
                    }
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            .custom-scroll::-webkit-scrollbar { width: 5px; }
            .custom-scroll::-webkit-scrollbar-thumb { background: rgba(245, 158, 11, 0.3); border-radius: 8px; }
            .chip-checkbox:checked + span { @apply bg-amber-500/10 border-amber-500 text-amber-500 shadow-[0_0_15px_rgba(245,158,11,0.2)]; }
        }
    </style>
</head>
<body class="min-h-screen antialiased selection:bg-amber-500 selection:text-black pb-20">

    <nav class="sticky top-0 z-50 bg-[#040711] border-b border-white/10 mb-8">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="logo font-extrabold text-2xl tracking-wide flex items-center gap-2 text-white">
                    <i class="fa-solid fa-bolt text-amber-500"></i>
                    <span>Happy Meal Planner <span class="bg-amber-500 text-black text-[10px] px-2 py-0.5 rounded font-black align-super">PRO</span></span>
                </div>
                <button onclick="toggleTheme()" class="bg-white/5 border border-white/10 w-9 h-9 rounded-xl text-amber-500 flex items-center justify-center hover:border-amber-500 transition cursor-pointer" title="تغيير المظهر">
                    <i id="themeIcon" class="fa-solid fa-moon"></i>
                </button>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="calculator.php" class="<?= $current_page == 'calculator.php' ? 'bg-amber-500 text-black shadow-[0_0_20px_rgba(245,158,11,0.3)]' : 'bg-white/5 border border-white/10 hover:border-amber-500 text-zinc-300' ?> px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2 transition">
                    <i class="fa-solid fa-calculator <?= $current_page == 'calculator.php' ? '' : 'text-amber-500' ?>"></i> <span class="hidden sm:inline">الحاسبة</span>
                </a>
                <a href="saved_meals.php" class="<?= $current_page == 'saved_meals.php' ? 'bg-amber-500 text-black shadow-[0_0_20px_rgba(245,158,11,0.3)]' : 'bg-white/5 border border-white/10 hover:border-amber-500 text-zinc-300' ?> px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2 transition">
                    <i class="fa-solid fa-bookmark <?= $current_page == 'saved_meals.php' ? '' : 'text-amber-500' ?>"></i> <span class="hidden sm:inline">الوجبات المحفوظة</span>
                </a>
                <a href="index.php" class="<?= $current_page == 'index.php' ? 'bg-amber-500 text-black shadow-[0_0_20px_rgba(245,158,11,0.3)]' : 'bg-white/5 border border-white/10 hover:border-amber-500 text-zinc-300' ?> px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2 transition">
                    <i class="fa-solid fa-utensils <?= $current_page == 'index.php' ? '' : 'text-amber-500' ?>"></i> <span class="hidden sm:inline">مولد الوجبات</span>
                </a> 
                <button onclick="toggleModal()" class="flex items-center gap-2 bg-white/5 border border-white/10 px-3 sm:px-4 py-2 rounded-xl text-sm font-semibold hover:border-amber-500 transition cursor-pointer text-zinc-300">
                    <i class="fa-solid fa-user-gear text-amber-500"></i>
                    <span class="hidden md:inline">الملف الشخصي</span>
                </button>
                <a href="auth/logout.php" class="bg-red-500/10 border border-red-500/20 text-red-400 px-3 py-2 rounded-xl text-sm hover:bg-red-500/20 transition" title="تسجيل الخروج">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
                <div class="w-10 h-10 rounded-full border border-white/20 overflow-hidden bg-white/5 flex items-center justify-center text-amber-400">
                    <img src="<?= htmlspecialchars($user_avatar ?? 'uploads/default-avatar.png'); ?>" alt="Avatar" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </nav>

    <div id="profileModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
        <div class="glass-panel w-full max-w-md p-6 relative border border-white/10 shadow-2xl">
            <button onclick="toggleModal()" class="absolute top-4 left-4 text-zinc-400 hover:text-white text-xl transition cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 rounded-full border border-amber-500/50 overflow-hidden bg-black/40 flex items-center justify-center text-amber-400 shadow-lg shrink-0">
                    <img id="avatarPreview" src="<?= htmlspecialchars($user_avatar ?? 'uploads/default-avatar.png'); ?>" alt="Avatar" class="w-full h-full object-cover">
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">الملف الشخصي للكابتن</h3>
                    <p class="text-xs text-zinc-400">إدارة معلوماتك الشخصية والبدنية</p>
                </div>
            </div>

            <form action="update_profile.php" method="POST" enctype="multipart/form-data" class="space-y-4 text-sm">
                <div>
                    <label class="block text-zinc-400 mb-1">الاسم الكامل:</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user_name ?? ''); ?>" class="input-field w-full px-4 py-2.5 rounded-xl profile-input" disabled required>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-zinc-400 mb-1">الوزن (kg):</label>
                        <input type="number" step="0.1" name="weight" value="<?= htmlspecialchars($user_weight ?? 75); ?>" class="input-field w-full px-4 py-2.5 rounded-xl profile-input" disabled required>
                    </div>
                    <div>
                        <label class="block text-zinc-400 mb-1">الطول (cm):</label>
                        <input type="number" step="0.1" name="height" value="<?= htmlspecialchars($user_height ?? 180); ?>" class="input-field w-full px-4 py-2.5 rounded-xl profile-input" disabled required>
                    </div>
                    <div>
                        <label class="block text-zinc-400 mb-1">العمر:</label>
                        <input type="number" name="age" value="<?= htmlspecialchars($user_age ?? 25); ?>" class="input-field w-full px-4 py-2.5 rounded-xl profile-input" disabled required>
                    </div>
                </div>
                <div>
                    <label class="block text-zinc-400 mb-1">تغيير الصورة الشخصية:</label>
                    <input type="file" name="avatar" id="avatarInput" accept="image/*" class="input-field w-full px-4 py-2 profile-input cursor-pointer" disabled onchange="previewImage(event)">
                </div>

                <div class="mt-6 flex gap-2">
                    <button type="submit" id="saveProfileBtn" class="w-full bg-emerald-500 hover:bg-emerald-600 text-black font-bold py-3 rounded-xl transition shadow-lg hidden cursor-pointer">حفظ التغييرات</button>
                    <button type="button" id="editProfileBtn" onclick="enableEditing()" class="w-full bg-amber-500 hover:bg-amber-400 text-black font-bold py-3 rounded-xl transition shadow-lg cursor-pointer">تعديل البيانات</button>
                    <button type="button" onclick="toggleModal()" class="w-full bg-white/10 hover:bg-white/20 text-white font-bold py-3 rounded-xl transition cursor-pointer">إغلاق</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function(){
                const output = document.getElementById('avatarPreview');
                output.src = reader.result;
            };
            if(event.target.files[0]){
                reader.readAsDataURL(event.target.files[0]);
            }
        }
    </script>