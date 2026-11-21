/**
 * Copyright (C) 2026 Adham Cherkaoui Eddeqaqi (0dh4m). All rights reserved.
 * Proprietary and confidential. Unauthorized copying or distribution is strictly prohibited.
 * Author: Adham Cherkaoui Eddeqaqi (0dh4m) <adamcherkaoui99@gmail.com>
 * GitHub: https://github.com/0dh4m
 */

    <canvas id="space-canvas" class="fixed inset-0 pointer-events-none z-[-1] opacity-60"></canvas>

    <div id="toastContainer" class="toast-container">
        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="toast success">
                <i class="fa-solid fa-circle-check text-2xl"></i>
                <span><?= $_SESSION['success_msg'] ?></span>
            </div>
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="toast error">
                <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                <span><?= $_SESSION['error_msg'] ?></span>
            </div>
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="toast error">
                <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                <span><?= $error ?></span>
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

    <script src="assets/js/script.js?v=6.0"></script>
</body>
</html>