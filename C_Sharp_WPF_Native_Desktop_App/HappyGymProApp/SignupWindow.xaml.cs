/**
 * Copyright (C) 2026 Adham Cherkaoui Eddeqaqi (0dh4m). All rights reserved.
 * Proprietary and confidential. Unauthorized copying or distribution is strictly prohibited.
 * Author: Adham Cherkaoui Eddeqaqi (0dh4m) <adamcherkaoui99@gmail.com>
 * GitHub: https://github.com/0dh4m
 */

﻿using System;
using System.Collections.Generic;
using System.Text.RegularExpressions; // تم إرجاع هادي باش يخدم Regex
using System.Windows;
using System.Windows.Controls;
using System.Windows.Input;
using System.Windows.Media;
using System.Windows.Shapes;
using HappyGymProApp.Services; // تم إرجاع هادي باش يخدم CountryService و CountryItem
using MySql.Data.MySqlClient;
using BCrypt.Net;

namespace HappyGymProApp
{
    public partial class SignupWindow : Window
    {
        private string connectionString = "Server=localhost;Port=3306;Database=gym_db;Uid=root;Pwd=;Charset=utf8mb4;";
        private const string AdminWhatsApp = "212693306618";

        // === متغيرات جزيئات الفضاء ===
        private List<Particle> particles = new List<Particle>();
        private Random rand = new Random();
        private SolidColorBrush darkParticleBrush;
        private SolidColorBrush darkLineBrush;
        private SolidColorBrush lightParticleBrush;
        private SolidColorBrush lightLineBrush;

        public SignupWindow()
        {
            InitializeComponent();

            // إرجاع الماوس الافتراضي لتفادي أي ثقل في الأداء
            this.Cursor = Cursors.Arrow;
        }

        private void Window_Loaded(object sender, RoutedEventArgs e)
        {
            SetupParticles(); // تشغيل محرك الفضاء
            LoadCountries();

            // التأكد من أيقونة المظهر عند التحميل
            TxtThemeIcon.Text = App.IsDarkMode ? "🌙" : "☀️";

            // وضع المؤشر تلقائياً في خانة الاسم الكامل
            TxtName.Focus();
        }

        private void LoadCountries()
        {
            try
            {
                var countries = CountryService.GetCountries();
                CmbCountry.ItemsSource = countries;
                CmbCountry.SelectedIndex = 0; // المغرب افتراضياً
            }
            catch { }
        }

        // ====================================================
        // 1. تحريك النافذة وإغلاق التطبيق
        // ====================================================
        private void Window_MouseDown(object sender, MouseButtonEventArgs e)
        {
            if (e.ChangedButton == MouseButton.Left)
            {
                this.DragMove();
            }
        }

        private void BtnClose_Click(object sender, RoutedEventArgs e)
        {
            Application.Current.Shutdown();
        }

        // ====================================================
        // 2. إدارة الوضع الليلي والنهاري
        // ====================================================
        private void BtnThemeToggle_Click(object sender, RoutedEventArgs e)
        {
            App.ToggleTheme(); // استدعاء دالة تغيير المظهر المركزية
            TxtThemeIcon.Text = App.IsDarkMode ? "🌙" : "☀️";
        }

        // ====================================================
        // 3. جزيئات الفضاء التفاعلية
        // ====================================================
        public class Particle
        {
            public double X, Y, Vx, Vy, Radius;
            public Ellipse Shape;
        }

        private void SetupParticles()
        {
            darkParticleBrush = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#5906B6D4"));
            darkLineBrush = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#1A06B6D4"));

            lightParticleBrush = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#E6F59E0B"));
            lightLineBrush = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#4DF59E0B"));

            int count = 50;
            for (int i = 0; i < count; i++)
            {
                var p = new Particle
                {
                    X = rand.NextDouble() * SystemParameters.PrimaryScreenWidth,
                    Y = rand.NextDouble() * SystemParameters.PrimaryScreenHeight,
                    Vx = (rand.NextDouble() - 0.5) * 1.5,
                    Vy = (rand.NextDouble() - 0.5) * 1.5,
                    Radius = rand.NextDouble() * 2 + 1,
                    Shape = new Ellipse { Width = 4, Height = 4 }
                };

                Canvas.SetLeft(p.Shape, p.X);
                Canvas.SetTop(p.Shape, p.Y);
                if (SpaceCanvas != null) SpaceCanvas.Children.Add(p.Shape);
                particles.Add(p);
            }

            CompositionTarget.Rendering += AnimateParticles;
        }

        private void AnimateParticles(object sender, EventArgs e)
        {
            if (SpaceCanvas == null) return;

            SpaceCanvas.Children.Clear();
            List<Line> lines = new List<Line>();

            double width = ActualWidth > 0 ? ActualWidth : SystemParameters.PrimaryScreenWidth;
            double height = ActualHeight > 0 ? ActualHeight : SystemParameters.PrimaryScreenHeight;

            Brush currentParticle = App.IsDarkMode ? darkParticleBrush : lightParticleBrush;
            Brush currentLine = App.IsDarkMode ? darkLineBrush : lightLineBrush;

            for (int i = 0; i < particles.Count; i++)
            {
                var p = particles[i];
                p.X += p.Vx;
                p.Y += p.Vy;

                if (p.X < 0 || p.X > width) p.Vx *= -1;
                if (p.Y < 0 || p.Y > height) p.Vy *= -1;

                p.Shape.Fill = currentParticle;

                Canvas.SetLeft(p.Shape, p.X);
                Canvas.SetTop(p.Shape, p.Y);
                SpaceCanvas.Children.Add(p.Shape);

                for (int j = i + 1; j < particles.Count; j++)
                {
                    var p2 = particles[j];
                    double dist = Math.Sqrt(Math.Pow(p.X - p2.X, 2) + Math.Pow(p.Y - p2.Y, 2));

                    if (dist < 130)
                    {
                        Line line = new Line
                        {
                            X1 = p.X + p.Radius,
                            Y1 = p.Y + p.Radius,
                            X2 = p2.X + p2.Radius,
                            Y2 = p2.Y + p2.Radius,
                            Stroke = currentLine,
                            StrokeThickness = 1
                        };
                        lines.Add(line);
                    }
                }
            }

            foreach (var line in lines)
            {
                SpaceCanvas.Children.Add(line);
            }
        }

        // ====================================================
        // 4. تسجيل حساب جديد
        // ====================================================
        private async void BtnSignup_Click(object sender, RoutedEventArgs e)
        {
            string name = TxtName.Text.Trim();
            string email = TxtEmail.Text.Trim();
            string rawPhone = TxtPhone.Text.Trim();
            string password = TxtPassword.Password;

            if (string.IsNullOrEmpty(name) || string.IsNullOrEmpty(email) || string.IsNullOrEmpty(rawPhone) || string.IsNullOrEmpty(password))
            {
                ShowError("المرجو ملء جميع الحقول المطلوبة.");
                return;
            }

            string cleanLocalPhone = Regex.Replace(rawPhone, @"[^\d]", "");
            if (cleanLocalPhone.StartsWith("0")) cleanLocalPhone = cleanLocalPhone.Substring(1);

            string countryCode = ((CountryItem)CmbCountry.SelectedItem)?.Code ?? "212";
            countryCode = countryCode.TrimStart('+');
            string fullPhone = countryCode + cleanLocalPhone;

            BtnSignup.IsEnabled = false;
            BtnSignup.Content = "جاري إنشاء الحساب... ⏳";
            HideError();

            try
            {
                string hashedPassword = BCrypt.Net.BCrypt.HashPassword(password);

                using (MySqlConnection conn = new MySqlConnection(connectionString))
                {
                    await conn.OpenAsync();
                    string query = "INSERT INTO users (name, email, phone, password) VALUES (@name, @email, @phone, @password)";

                    using (MySqlCommand cmd = new MySqlCommand(query, conn))
                    {
                        cmd.Parameters.AddWithValue("@name", name);
                        cmd.Parameters.AddWithValue("@email", email);
                        cmd.Parameters.AddWithValue("@phone", fullPhone);
                        cmd.Parameters.AddWithValue("@password", hashedPassword);

                        await cmd.ExecuteNonQueryAsync();
                    }
                }

                MessageBox.Show("تم إنشاء حسابك بنجاح! يمكنك الآن تسجيل الدخول.", "نجاح", MessageBoxButton.OK, MessageBoxImage.Information);

                LoginWindow login = new LoginWindow();
                login.Show();
                this.Close();
            }
            catch (Exception ex)
            {
                ShowError("خطأ أثناء إنشاء الحساب (ربما البريد مستخدم مسبقاً):\n" + ex.Message);
                BtnSignup.IsEnabled = true;
                BtnSignup.Content = "تسجيل حساب جديد 🚀";
            }
        }

        private void OpenLogin_MouseDown(object sender, MouseButtonEventArgs e)
        {
            LoginWindow login = new LoginWindow();
            login.Show();
            this.Close();
        }

        private void ShowError(string msg)
        {
            TxtErrorMsg.Text = msg;
            BorderError.Visibility = Visibility.Visible;
        }

        private void HideError()
        {
            BorderError.Visibility = Visibility.Collapsed;
        }
    }
}