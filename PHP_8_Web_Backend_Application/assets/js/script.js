/**
 * Copyright (C) 2026 Adham Cherkaoui Eddeqaqi (0dh4m). All rights reserved.
 * Proprietary and confidential. Unauthorized copying or distribution is strictly prohibited.
 * Author: Adham Cherkaoui Eddeqaqi (0dh4m) <0dh4mm@gmail.com>
 * GitHub: https://github.com/0dh4m
 */

/**
 * Happy Meal Planner - Ultra Fast UI/UX Engine
 */

document.addEventListener("DOMContentLoaded", function () {
    const htmlElement = document.documentElement;
    const themeIcon = document.getElementById("themeIcon");

    if (localStorage.getItem("theme") === "light") {
        htmlElement.classList.remove("dark");
        if (themeIcon) themeIcon.classList.replace("fa-moon", "fa-sun");
    } else {
        htmlElement.classList.add("dark");
        if (themeIcon) themeIcon.classList.replace("fa-sun", "fa-moon");
    }

    const mealForm = document.getElementById("mealForm");
    const submitBtn = document.getElementById("submitBtn");

    if (mealForm && submitBtn) {
        mealForm.addEventListener("submit", function () {
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> جاري توليد الوجبة بالذكاء الاصطناعي...';
            submitBtn.style.opacity = "0.75";
            submitBtn.style.cursor = "not-allowed";
            setTimeout(() => { submitBtn.disabled = true; }, 10);
        });
    }

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has("target_calories")) {
        const input = document.getElementById("targetCaloriesInput");
        if (input) {
            input.scrollIntoView({ behavior: "smooth", block: "center" });
            input.style.borderColor = "var(--accent-amber)";
            input.style.boxShadow = "0 0 20px rgba(245, 158, 11, 0.4)";
            setTimeout(() => { input.focus(); }, 500);
        }
    }

    const chatInput = document.getElementById("chatInput");
    if (chatInput) {
        chatInput.addEventListener("keydown", function (e) {
            if (e.key === "Enter") { e.preventDefault(); sendChat(); }
        });
    }

    const cursor = document.getElementById('customCursor');
    if (cursor) {
        document.addEventListener('mousemove', (e) => {
            cursor.style.left = e.clientX + 'px';
            cursor.style.top = e.clientY + 'px';
            cursor.style.transform = 'none';
        }, { passive: true });

        const interactiveElements = document.querySelectorAll('a, button, input, label, select');
        interactiveElements.forEach(el => {
            el.addEventListener('mouseenter', () => cursor.classList.add('hovered'));
            el.addEventListener('mouseleave', () => cursor.classList.remove('hovered'));
        });
    }

    const existingToasts = document.querySelectorAll('.toast');
    existingToasts.forEach(toast => {
        setTimeout(() => {
            toast.classList.add('hiding');
            setTimeout(() => toast.remove(), 400);
        }, 4000);
    });

    initParticleMesh();
    init3DCardTiltEngine();
});

function toggleTheme() {
    const htmlElement = document.documentElement;
    const themeIcon = document.getElementById("themeIcon");

    if (htmlElement.classList.contains("dark")) {
        htmlElement.classList.remove("dark");
        localStorage.setItem("theme", "light");
        if (themeIcon) themeIcon.classList.replace("fa-moon", "fa-sun");
    } else {
        htmlElement.classList.add("dark");
        localStorage.setItem("theme", "dark");
        if (themeIcon) themeIcon.classList.replace("fa-sun", "fa-moon");
    }
}

function toggleModal() {
    const modal = document.getElementById("profileModal");
    if (modal) { modal.classList.toggle("hidden"); }
}

function enableEditing() {
    document.querySelectorAll(".profile-input").forEach((input) => {
        input.disabled = false;
        input.style.borderColor = "var(--accent-amber)";
    });
    const editBtn = document.getElementById("editProfileBtn");
    const saveBtn = document.getElementById("saveProfileBtn");
    if (editBtn) editBtn.classList.add("hidden");
    if (saveBtn) saveBtn.classList.remove("hidden");
}

function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function(){
        const output = document.getElementById('avatarPreview');
        if(output) output.src = reader.result;
    };
    if(event.target.files[0]){ reader.readAsDataURL(event.target.files[0]); }
}

function toggleMobileMenu() {
    const nav = document.getElementById('navLinks');
    if (nav) { nav.classList.toggle('active'); }
}

function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icon = type === 'success' ? '<i class="fa-solid fa-circle-check text-2xl"></i>' : '<i class="fa-solid fa-triangle-exclamation text-2xl"></i>';
    toast.innerHTML = `${icon} <span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('hiding');
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

async function sendChat() {
    const input = document.getElementById("chatInput");
    const responseBox = document.getElementById("chatResponse");

    if (!input || !responseBox || !input.value.trim()) return;

    const userQuestion = input.value.trim();
    responseBox.innerHTML = '<span class="text-amber-500"><i class="fa-solid fa-spinner fa-spin"></i> جاري تحليل سؤالك الرياضي...</span>';
    input.value = "";

    try {
        const formData = new FormData();
        formData.append("question", userQuestion);
        const res = await fetch("chat_process.php", { method: "POST", body: formData });
        if (res.ok) { responseBox.innerHTML = await res.text(); } 
        else { responseBox.innerHTML = "حدث خطأ في الاتصال بالمدرب الذكي. يرجى المحاولة لاحقاً."; }
    } catch (err) { responseBox.innerHTML = "تعذر إرسال السؤال، تأكد من اتصال السيرفر."; }
}

function initParticleMesh() {
    let canvas = document.getElementById('space-canvas');
    
    if (!canvas) {
        canvas = document.createElement('canvas');
        canvas.id = 'space-canvas';
        canvas.style.position = 'fixed';
        canvas.style.top = '0';
        canvas.style.left = '0';
        canvas.style.width = '100vw';
        canvas.style.height = '100vh';
        canvas.style.zIndex = '0';
        canvas.style.pointerEvents = 'none';
        document.body.insertBefore(canvas, document.body.firstChild);
    }

    const ctx = canvas.getContext('2d');
    let width = canvas.width = window.innerWidth;
    let height = canvas.height = window.innerHeight;

    window.addEventListener('resize', () => {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
    });

    const particles = [];
    const count = 75; 

    for (let i = 0; i < count; i++) {
        particles.push({
            x: Math.random() * width,
            y: Math.random() * height,
            vx: (Math.random() - 0.5) * 0.4, 
            vy: (Math.random() - 0.5) * 0.4,
            radius: Math.random() * 2 + 1 
        });
    }

    function animate() {
        ctx.clearRect(0, 0, width, height);
        
        const isDark = document.documentElement.classList.contains('dark');
        
        ctx.fillStyle = isDark ? 'rgba(6, 182, 212, 0.7)' : 'rgba(245, 158, 11, 1)';
        ctx.strokeStyle = isDark ? 'rgba(6, 182, 212, 0.25)' : 'rgba(245, 158, 11, 0.6)';

        for (let i = 0; i < count; i++) {
            const p = particles[i];
            p.x += p.vx;
            p.y += p.vy;

            if (p.x < 0 || p.x > width) p.vx *= -1;
            if (p.y < 0 || p.y > height) p.vy *= -1;

            ctx.beginPath();
            ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
            ctx.fill();

            for (let j = i + 1; j < count; j++) {
                const p2 = particles[j];
                const dist = Math.hypot(p.x - p2.x, p.y - p2.y);
                
                if (dist < 140) {
                    ctx.beginPath();
                    ctx.moveTo(p.x, p.y);
                    ctx.lineTo(p2.x, p2.y);
                    ctx.lineWidth = isDark ? 0.8 : 1.2; 
                    ctx.stroke();
                }
            }
        }
        requestAnimationFrame(animate);
    }
    animate();
}

function init3DCardTiltEngine() {
    const currentPage = window.location.pathname.split("/").pop();
    if (currentPage !== 'saved_meals.php') { return; }

    const cards = document.querySelectorAll('.glass-panel, .sci-card');

    cards.forEach(card => {
        card.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const rotateX = ((y - centerY) / centerY) * -8;
            const rotateY = ((x - centerX) / centerX) * 8;
            this.style.transition = 'none';
            this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-5px) scale3d(1.01, 1.01, 1.01)`;
        });

        card.addEventListener('mouseleave', function() {
            this.style.transition = 'transform 0.4s cubic-bezier(0.16, 1, 0.3, 1)';
            this.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0) scale3d(1, 1, 1)';
        });
    });
}