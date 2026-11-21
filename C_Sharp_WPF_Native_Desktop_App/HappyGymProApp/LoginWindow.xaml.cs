/**
 * Copyright (C) 2026 Adham Cherkaoui Eddeqaqi (0dh4m). All rights reserved.
 * Proprietary and confidential. Unauthorized copying or distribution is strictly prohibited.
 * Author: Adham Cherkaoui Eddeqaqi (0dh4m) <0dh4mm@gmail.com>
 * GitHub: https://github.com/0dh4m
 */

﻿using System;
using System.Collections.Generic;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Input;
using System.Windows.Media;
using System.Windows.Shapes;
using MySql.Data.MySqlClient;
using BCrypt.Net;

namespace HappyGymProApp
{
    public partial class LoginWindow : Window
    {
        private const string ConnectionString = "Server=localhost;Port=3306;Database=gym_db;Uid=root;Pwd=;Charset=utf8mb4;Pooling=true;Min Pool Size=2;Max Pool Size=20;";

        // === متغيرات جزيئات الفضاء ===
        private List<Particle> particles = new List<Particle>();
        private Random rand = new Random();
        private SolidColorBrush darkParticleBrush;
        private SolidColorBrush darkLineBrush;
        private SolidColorBrush lightParticleBrush;
        private SolidColorBrush lightLineBrush;

        public LoginWindow()
        {
            InitializeComponent();

            // رجعنا الماوس الطبيعي ديال الويندوز باش ميبقاش الثقل نهائياً
            this.Cursor = Cursors.Arrow;

            // تشغيل محرك الجزيئات وتحديث أيقونة المظهر عند فتح النافذة
            Loaded += (s, e) => {
                SetupParticles();
                TxtThemeIcon.Text = App.IsDarkMode ? "🌙" : "☀️";

                // لمسة احترافية: وضع المؤشر مباشرة في خانة الإيميل عند فتح التطبيق
                TxtEmail.Focus();
            };
        }

        // ====================================================
        // 1. تحريك النافذة وإغلاق التطبيق (ضرورية حيت النافذة شفافة)
        // ====================================================
        private void Window_MouseDown(object sender, MouseButtonEventArgs e)
        {
            if (e.ChangedButton == MouseButton.Left)
            {
                this.DragMove(); // هادي لي كتخليك تجر النافذة من أي بلاصة خاوية
            }
        }

        private void BtnClose_Click(object sender, RoutedEventArgs e)
        {
            Application.Current.Shutdown(); // إغلاق التطبيق نهائياً
        }

        // ====================================================
        // 2. إدارة الوضع الليلي والنهاري
        // ====================================================
        private void BtnThemeToggle_Click(object sender, RoutedEventArgs e)
        {
            App.ToggleTheme();
            TxtThemeIcon.Text = App.IsDarkMode ? "🌙" : "☀️";
        }

        // ====================================================
        // 3. جزيئات الفضاء التفاعلية (خفيفة على المعالج)
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
        // 4. أزرار وأحداث تسجيل الدخول
        // ====================================================
        private async void BtnLogin_Click(object sender, RoutedEventArgs e)
        {
            string email = TxtEmail.Text.Trim();
            string password = TxtPassword.Password;

            if (string.IsNullOrEmpty(email) || string.IsNullOrEmpty(password))
            {
                ShowError("المرجو إدخال البريد الإلكتروني وكلمة المرور.");
                return;
            }

            BtnLogin.IsEnabled = false;
            BtnLogin.Content = "جاري التحقق... ⏳";
            HideError();

            try
            {
                using (MySqlConnection conn = new MySqlConnection(ConnectionString))
                {
                    await conn.OpenAsync();
                    string query = "SELECT id, password FROM users WHERE email = @email LIMIT 1";

                    using (MySqlCommand cmd = new MySqlCommand(query, conn))
                    {
                        cmd.Parameters.AddWithValue("@email", email);

                        using (var reader = await cmd.ExecuteReaderAsync())
                        {
                            if (await reader.ReadAsync())
                            {
                                int userId = Convert.ToInt32(reader["id"]);
                                string hashedPassword = reader["password"]?.ToString() ?? "";

                                bool isPasswordValid = false;
                                try
                                {
                                    isPasswordValid = BCrypt.Net.BCrypt.Verify(password, hashedPassword);
                                }
                                catch
                                {
                                    isPasswordValid = (password == hashedPassword);
                                }

                                if (isPasswordValid)
                                {
                                    MainWindow main = new MainWindow(userId);
                                    main.Show();
                                    this.Close();
                                    return;
                                }
                            }
                        }
                    }
                }

                ShowError("البريد الإلكتروني أو كلمة المرور غير صحيحة.");
            }
            catch (Exception ex)
            {
                ShowError("خطأ في الاتصال بقاعدة البيانات:\n" + ex.Message);
            }
            finally
            {
                BtnLogin.IsEnabled = true;
                BtnLogin.Content = "تسجيل الدخول 🚀";
            }
        }

        private void TxtPassword_KeyDown(object sender, KeyEventArgs e)
        {
            if (e.Key == Key.Enter)
            {
                BtnLogin_Click(sender, e);
            }
        }

        private void ForgotPassword_MouseDown(object sender, MouseButtonEventArgs e)
        {
            ForgotPasswordWindow forgotWindow = new ForgotPasswordWindow();
            forgotWindow.Show();
            this.Close();
        }

        private void OpenSignup_MouseDown(object sender, MouseButtonEventArgs e)
        {
            SignupWindow signup = new SignupWindow();
            signup.Show();
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