# 🏋️‍♂️ Happy Gym PRO (PFE) — Hybrid Nutrition & Meal-Planning Ecosystem

<div align="center">

[![Author](https://img.shields.io/badge/Author-0dh4m-00E5FF?style=for-the-badge&logo=github&logoColor=white)](https://github.com/0dh4m)
[![PFE Project](https://img.shields.io/badge/Project-PFE%20Engineering%20Thesis-7928CA?style=for-the-badge)](https://github.com/0dh4m/happy-gym-pro)
[![Architecture](https://img.shields.io/badge/Architecture-C%23%20WPF%20%7C%20PHP%208%20%7C%20Gemini%20AI-FF0080?style=for-the-badge)](https://github.com/0dh4m/happy-gym-pro)
[![License](https://img.shields.io/badge/License-Proprietary-red?style=for-the-badge)](LICENSE)

**End-of-studies (PFE) project: a nutrition and meal-planning ecosystem for gym members.**
It combines a C# WPF desktop meal planner, a PHP 8 web application, and a Gemini-powered meal-planning service.

[Architecture](#-system-architecture) • [Features](#-key-capabilities) • [Installation](#-quickstart-guide) • [License](#-license)

</div>

> **Scope note:** despite the "gym" branding, this repository implements **nutrition coaching and meal planning** — member registration, macro calculation, AI meal generation, saved meals and WhatsApp sharing. It does **not** include gym access control, RFID/biometric gates, POS billing, class scheduling or member CRM.

---

## 🏗️ System Architecture

```text
┌──────────────────────────────────────────────────────────────────────┐
│                     HAPPY GYM PRO (PFE) ECOSYSTEM                    │
└──────────────────────────────────────────────────────────────────────┘
          │                                  │
          ▼                                  ▼
┌──────────────────────────┐      ┌──────────────────────────┐
│  DESKTOP MEAL PLANNER    │      │  PHP 8 WEB APPLICATION   │
│  C# WPF / .NET Framework │      │  MySQL + Tailwind CSS    │
│  • Login / Signup        │      │  • Auth + password reset │
│  • Macro calculator      │      │  • Macro calculator      │
│  • Gemini meal plans     │      │  • Gemini chat assistant │
│  • WhatsApp OTP/share    │      │  • Saved / logged meals  │
└──────────────────────────┘      └──────────────────────────┘
          │                                  │
          └────────────────┬─────────────────┘
                           ▼
              ┌──────────────────────────┐
              │  GEMINI MEAL SERVICE     │
              │  React + Vite + Express  │
              │  • AI meal generation    │
              │  • Firebase config       │
              └──────────────────────────┘
```

---

## ⚡ Key Capabilities

### 1. 🪟 C# WPF Desktop App — “Happy Meal Planner PRO”
- Glassmorphic dark UI with login, signup and OTP-based password recovery.
- BMR / TDEE and macro calculator (protein, carbs, fats) based on weight, height, age, gender, activity and goal.
- Gemini-powered meal plan generation through `Services/AiMealService.cs`.
- WhatsApp integration for OTP delivery and sharing results through `Services/WhatsAppService.cs` (OpenWA gateway).
- Country/dial-code picker via `Services/CountryService.cs`.

### 2. 🌐 PHP 8 Web Application
- Session-based authentication: signup, login, logout, forgot/reset password with a 6-digit OTP.
- Macro calculator that stores weight, height, age, gender, activity and fitness goal on the `users` row.
- AI chat endpoint (`chat_process.php`) and meal generation (`process.php`) using the Gemini API.
- Save/log meals (`logged_meals`), list and delete them from `saved_meals.php`.
- Profile editing with avatar upload (`update_profile.php`).
- Responsive Tailwind CSS interface in Arabic (RTL).

### 3. 🧠 Gemini Meal-Planning Service
- React 19 + TypeScript + Vite front end served by an Express proxy (`server.ts`).
- Calls the Gemini API with a structured response schema for meal plans.
- Firebase applet configuration and Firestore rules included.

---

## 📁 Repository Structure

```text
happy-gym-pro/
├── C_Sharp_WPF_Native_Desktop_App/
│   ├── HappyGymProApp/                  # WPF views, services, models
│   │   └── Services/                    # AiMealService, WhatsAppService, MacroCalculator…
│   └── HappyGymProApp.sln
├── C_Sharp_Desktop_System_Library/      # Supporting WinForms system library
├── PHP_8_Web_Backend_Application/
│   ├── auth/                            # signup, login, logout, forgot/reset password
│   ├── includes/                        # db.php, header.php, footer.php
│   ├── assets/                          # Tailwind-based CSS and JS
│   ├── index.php                        # Dashboard + AI chat
│   ├── calculator.php                   # Macro calculator
│   ├── process.php / chat_process.php   # Gemini API endpoints
│   ├── saved_meals.php                  # Saved meal CRUD
│   └── update_profile.php               # Profile + avatar upload
├── AI_Gemini_Meal_Planner_Service/      # React + Vite + Express service
│   ├── src/                             # App.tsx, firebase.ts, styles
│   ├── server.ts                        # Express + Gemini proxy (port 3000)
│   ├── .env.example
│   └── package.json
├── database.sql                         # MySQL schema for the PHP application
├── LICENSE
└── README.md
```

> The academic PFE report (`Dossier_Academique/`) is intentionally excluded by `.gitignore` and is **not** part of this repository.

---

## 🚀 Quickstart Guide

### 1. Database (required for the PHP app)

```bash
mysql -u root -p < database.sql
```

This creates the `gym_db` database with the `users` and `logged_meals` tables used by the PHP code.

Configure the connection either with environment variables or by editing `PHP_8_Web_Backend_Application/includes/db.php`:

```text
HAPPY_GYM_DB_HOST=localhost
HAPPY_GYM_DB_USER=root
HAPPY_GYM_DB_PASS=
HAPPY_GYM_DB_NAME=gym_db
```

### 2. PHP Web Application

Requirements: **PHP 8.1+** with `mysqli`, **MySQL 8 / MariaDB**.

```bash
cd PHP_8_Web_Backend_Application
php -S localhost:8000
```

Open `http://localhost:8000`. Set `GEMINI_API_KEY` in your environment (or edit `chat_process.php` / `process.php`) before using the AI features.

### 3. C# WPF Desktop App

1. Open `C_Sharp_WPF_Native_Desktop_App/HappyGymProApp.sln` in **Visual Studio 2022**.
2. Restore NuGet packages and build the solution.
3. Set your keys in:
   - `Services/AiMealService.cs` → `GeminiApiKey`
   - `Services/WhatsAppService.cs` → `ApiKey` (OpenWA gateway on `localhost:2785`)
4. Update the MySQL connection string in `App.config` if needed, then run with **F5**.

### 4. Gemini Meal Service

```bash
cd AI_Gemini_Meal_Planner_Service
npm install
cp .env.example .env      # then set GEMINI_API_KEY
npm run dev
```

The service starts on `http://localhost:3000`.

---

## 🔐 Configuration Notes

- API keys are **placeholders** in the source (`YOUR_GEMINI_API_KEY_HERE`, `YOUR_OPENWA_API_KEY_HERE`). Never commit real keys.
- The PHP application stores hashed passwords with `password_hash()` and verifies them with `password_verify()`.
- OTP reset codes are stored in `users.reset_otp` with a `reset_otp_expiry` timestamp.

---

## 👤 Author

**Adham Cherkaoui Eddeqaqi (0dh4m)**
- 🌐 Portfolio: [0dh4m.github.io](https://0dh4m.github.io/)
- 🐙 GitHub: [@0dh4m](https://github.com/0dh4m)
- ✉️ Email: 0dh4mm@gmail.com

---

## 📄 License

Proprietary and confidential — see [LICENSE](LICENSE). Unauthorized copying or distribution is prohibited.

*Copyright © 2026 Adham Cherkaoui Eddeqaqi (0dh4m). All rights reserved.*
