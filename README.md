# 🏋️‍♂️ Happy Gym PRO — Enterprise Fitness & Sports Facility Ecosystem

<div align="center">

[![Author](https://img.shields.io/badge/Author-0dh4m-00E5FF?style=for-the-badge&logo=github&logoColor=white)](https://github.com/0dh4m)
[![PFE Project](https://img.shields.io/badge/Project-PFE%20Engineering%20Thesis-7928CA?style=for-the-badge)](https://github.com/0dh4m/happy-gym-pro)
[![Architecture](https://img.shields.io/badge/Architecture-C%23%20WPF%20%7C%20PHP%208%20%7C%20Gemini%20AI-FF0080?style=for-the-badge)](https://github.com/0dh4m/happy-gym-pro)
[![License](https://img.shields.io/badge/License-Proprietary-red?style=for-the-badge)](https://github.com/0dh4m)

**A high-performance, hybrid enterprise management suite engineered for modern gyms, crossfit boxes, and sports clubs.**  
Combines a native Windows desktop front-desk terminal, responsive PHP 8 web application, multimodal Gemini AI nutrition coach, and automated WhatsApp notification engine.

[View Architecture](#-system-architecture) • [Features](#-key-capabilities) • [Installation](#-quickstart-guide)

</div>

---

## 🏗️ System Architecture

Happy Gym PRO is architected as a decoupled, multi-tier ecosystem designed to handle continuous daily club operations, access control, and digital member services:

```text
┌─────────────────────────────────────────────────────────────────────────────┐
│                              HAPPY GYM PRO ECOSYSTEM                         │
└─────────────────────────────────────────────────────────────────────────────┘
          │                                        │
          ▼                                        ▼
┌───────────────────────────────┐        ┌───────────────────────────────┐
│   DESKTOP FRONT-DESK SUITE    │        │       WEB PORTAL & API        │
│   • C# WPF / .NET Modern UI   │        │   • PHP 8.2 + MySQL InnoDB    │
│   • Biometric / RFID Readers  │        │   • Member Self-Service Pass  │
│   • Cash Drawer & POS Billing │        │   • Trainer Class Schedules   │
│   • Real-Time Access Gate     │        │   • Digital QR Code Check-in  │
└───────────────────────────────┘        └───────────────────────────────┘
          │                                        │
          └───────────────────┬────────────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                        AI & AUTOMATION SERVICES                             │
│   • Gemini AI Nutritionist: Automated personalized macro & meal plans       │
│   • WhatsApp Gateway: Automated membership renewal alerts & OTP passes      │
│   • Analytics Engine: Revenue forecast, churn detection, peak gym hours     │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## ⚡ Key Capabilities

### 1. 🪟 Front-Desk Windows Desktop App (C# WPF)
- **Glassmorphic Modern UI**: Custom dark-themed Material Design interface built for touchscreens and desktop workstations.
- **Member Check-in & Gate Automation**: Sub-second badge scanning (RFID / Barcode / Biometrics) with audible access confirmation.
- **Point of Sale (POS) & Financials**: Manage recurring subscriptions, multi-tiered plans (Monthly, Quarterly, Annual, VIP), locker rentals, and supplements.
- **Visual Analytics**: Interactive live charts for daily admissions, revenue trends, and active member capacity.

### 2. 🌐 Member & Staff Web Portal (PHP 8 & MySQL)
- **Responsive Mobile Pass**: Members access digital membership cards, QR code passes, and attendance history on any mobile device.
- **Coach Scheduling**: Book private training sessions, group fitness classes, and view trainer availability.
- **Role-Based Access Control (RBAC)**: Fine-grained permissions for Receptionists, Managers, Coaches, and Super Admins.

### 3. 🧠 Multimodal Gemini AI Nutrition & Fitness Coach
- **Automated Macro Calculations**: Calculates BMR, TDEE, and macro splits (Protein/Carb/Fats) tailored to specific goals (Hypertrophy, Fat Loss, Endurance).
- **Personalized 7-Day Meal Plans**: Uses Google Gemini AI to formulate complete weekly nutrition agendas factoring in dietary allergies and caloric needs.

### 4. 📲 WhatsApp Notification & OTP Engine
- **Automated Expiry Warnings**: Sends automated courteous alerts 7 days, 3 days, and 1 day prior to membership expiry.
- **Instant Welcome Receipts**: Delivers instant digital payment invoices and membership credentials straight to members' WhatsApp.

---

## 📁 Repository Structure

```text
happy-gym-pro/
├── C_Sharp_WPF_Native_Desktop_App/      # Visual Studio Solution & WPF Source
│   ├── HappyGymProApp/                  # Modern UI Views, ViewModels, Services
│   └── HappyGymProApp.sln               # Solution configuration
├── C_Sharp_Desktop_System_Library/      # Data access layer & shared models
├── PHP_8_Web_Backend_Application/       # Production Web Portal & REST Endpoints
│   ├── auth/                            # Authentication & Session Handlers
│   ├── includes/                        # Database connections & helper classes
│   ├── calculator.php                   # Interactive Macro & BMR Calculator
│   └── chat_process.php                 # Gemini AI Assistant Gateway
├── AI_Gemini_Meal_Planner_Service/      # Multimodal AI Nutrition Microservice
│   ├── assets/                          # AI Prompts & Meal plan generators
│   └── index.html                       # Standalone AI Coach Web Interface
└── Dossier_Academique/                  # Graduation Thesis & Engineering Defense
    └── Rapport_PFE_-_Happy_Gym_PRO.pdf  # Comprehensive 80+ Page PFE Report
```

---

## 🚀 Quickstart Guide

### Web Application & AI Portal
1. **Requirements**: PHP 8.1+, MySQL 8.0+, Apache/Nginx.
2. **Database Setup**: Import `database.sql` into MySQL.
3. **Environment Config**: Copy `.env.example` to `.env` and set your database credentials and `GEMINI_API_KEY`.
4. **Run Server**:
   ```bash
   cd PHP_8_Web_Backend_Application
   php -S localhost:8000
   ```

### Desktop Management Suite (C# WPF)
1. Open `C_Sharp_WPF_Native_Desktop_App/HappyGymProApp.sln` in **Visual Studio 2022**.
2. Restore NuGet packages (`Ctrl + Shift + B`).
3. Update connection string in `App.config` to point to your MySQL database.
4. Hit **F5** to run the desktop application.

---

## 👤 Author & Copyright

**Adham Cherkaoui Eddeqaqi (0dh4m)**  
- 🌐 Portfolio: [0dh4m.github.io/portfolio](https://0dh4m.github.io/portfolio)
- 🐙 GitHub: [@0dh4m](https://github.com/0dh4m)
- 💼 LinkedIn: [Adham Cherkaoui](https://www.linkedin.com)
- 📧 Email: 0dh4mm@gmail.com

*Copyright © 2026 Adham Cherkaoui Eddeqaqi (0dh4m). All rights reserved.*
