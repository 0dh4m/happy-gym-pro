/**
 * Copyright (C) 2026 Adham Cherkaoui Eddeqaqi (0dh4m). All rights reserved.
 * Proprietary and confidential. Unauthorized copying or distribution is strictly prohibited.
 * Author: Adham Cherkaoui Eddeqaqi (0dh4m) <adamcherkaoui99@gmail.com>
 * GitHub: https://github.com/0dh4m
 */

﻿using System;
using System.IO;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Input;
using System.Windows.Media.Imaging;
using Microsoft.Win32;
using MySql.Data.MySqlClient;

namespace HappyGymProApp
{
    public partial class MainWindow : Window
    {
        private int currentUserId;
        private const string connectionString = "Server=localhost;Port=3306;Database=gym_db;Uid=root;Pwd=;Charset=utf8mb4;";
        private readonly string uploadsFolder = @"C:\xampp\htdocs\hmp\uploads\";

        public MainWindow(int userId)
        {
            InitializeComponent();
            currentUserId = userId;
        }

        private void Window_Loaded(object sender, RoutedEventArgs e)
        {
            LoadUserData();

            // فتح شاشة مولد الوجبات كشاشة أولية وتفعيل الزر ديالها
            Nav_Planner_Click(null, null);
        }

        // ====================================================
        // 1. إدارة الملف الشخصي والصور 
        // ====================================================

        private async void LoadUserData()
        {
            try
            {
                using (MySqlConnection conn = new MySqlConnection(connectionString))
                {
                    await conn.OpenAsync();
                    string query = "SELECT name, weight, height, age, avatar FROM users WHERE id = @id";
                    using (MySqlCommand cmd = new MySqlCommand(query, conn))
                    {
                        cmd.Parameters.AddWithValue("@id", currentUserId);
                        using (var reader = await cmd.ExecuteReaderAsync())
                        {
                            if (await reader.ReadAsync())
                            {
                                TxtProfileName.Text = reader["name"]?.ToString();
                                TxtProfileWeight.Text = reader["weight"]?.ToString();
                                TxtProfileHeight.Text = reader["height"]?.ToString();
                                TxtProfileAge.Text = reader["age"]?.ToString();

                                string avatarFileName = reader["avatar"]?.ToString();
                                LoadAvatarImage(avatarFileName);
                            }
                        }
                    }
                }
            }
            catch (Exception ex)
            {
                Console.WriteLine("Error loading data: " + ex.Message);
            }
        }

        private void LoadAvatarImage(string fileName)
        {
            try
            {
                if (!string.IsNullOrEmpty(fileName))
                {
                    string safeFileName = System.IO.Path.GetFileName(fileName);
                    string fullPath = System.IO.Path.Combine(uploadsFolder, safeFileName);

                    if (System.IO.File.Exists(fullPath))
                    {
                        byte[] imageData = System.IO.File.ReadAllBytes(fullPath);
                        using (var ms = new System.IO.MemoryStream(imageData))
                        {
                            BitmapImage bitmap = new BitmapImage();
                            bitmap.BeginInit();
                            bitmap.CacheOption = BitmapCacheOption.OnLoad;
                            bitmap.StreamSource = ms;
                            bitmap.EndInit();
                            bitmap.Freeze();

                            ImgUserAvatar.Source = bitmap;
                            ImgModalAvatar.Source = bitmap;
                            return;
                        }
                    }
                }

                // الصورة الافتراضية إذا لم توجد الصورة في المسار
                ImgUserAvatar.Source = new BitmapImage(new Uri("pack://application:,,,/logo.ico"));
                ImgModalAvatar.Source = new BitmapImage(new Uri("pack://application:,,,/logo.ico"));
            }
            catch (Exception ex)
            {
                Console.WriteLine("Avatar Load Error: " + ex.Message);
            }
        }

        private async void ChangeAvatar_Click(object sender, RoutedEventArgs e)
        {
            OpenFileDialog openFileDialog = new OpenFileDialog();
            openFileDialog.Filter = "Image files (*.png;*.jpeg;*.jpg)|*.png;*.jpeg;*.jpg";

            if (openFileDialog.ShowDialog() == true)
            {
                try
                {
                    string selectedFilePath = openFileDialog.FileName;
                    string fileExtension = System.IO.Path.GetExtension(selectedFilePath);

                    string newFileName = $"user_{currentUserId}_{DateTime.Now.Ticks}{fileExtension}";
                    string destinationPath = System.IO.Path.Combine(uploadsFolder, newFileName);

                    if (!Directory.Exists(uploadsFolder)) Directory.CreateDirectory(uploadsFolder);

                    File.Copy(selectedFilePath, destinationPath, true);

                    using (MySqlConnection conn = new MySqlConnection(connectionString))
                    {
                        await conn.OpenAsync();
                        string query = "UPDATE users SET avatar = @avatar WHERE id = @id";
                        using (MySqlCommand cmd = new MySqlCommand(query, conn))
                        {
                            cmd.Parameters.AddWithValue("@avatar", newFileName);
                            cmd.Parameters.AddWithValue("@id", currentUserId);
                            await cmd.ExecuteNonQueryAsync();
                        }
                    }

                    LoadAvatarImage(newFileName);
                    MessageBox.Show("تم تغيير الصورة بنجاح! 📸", "نجاح", MessageBoxButton.OK, MessageBoxImage.Information);
                }
                catch (Exception ex)
                {
                    MessageBox.Show("وقع خطأ أثناء تغيير الصورة:\n" + ex.Message, "خطأ", MessageBoxButton.OK, MessageBoxImage.Error);
                }
            }
        }

        private async void SaveProfile_Click(object sender, RoutedEventArgs e)
        {
            try
            {
                using (MySqlConnection conn = new MySqlConnection(connectionString))
                {
                    await conn.OpenAsync();
                    string query = "UPDATE users SET name=@name, weight=@weight, height=@height, age=@age WHERE id=@id";
                    using (MySqlCommand cmd = new MySqlCommand(query, conn))
                    {
                        cmd.Parameters.AddWithValue("@name", TxtProfileName.Text.Trim());
                        cmd.Parameters.AddWithValue("@weight", TxtProfileWeight.Text.Trim());
                        cmd.Parameters.AddWithValue("@height", TxtProfileHeight.Text.Trim());
                        cmd.Parameters.AddWithValue("@age", TxtProfileAge.Text.Trim());
                        cmd.Parameters.AddWithValue("@id", currentUserId);
                        await cmd.ExecuteNonQueryAsync();
                    }
                }
                MessageBox.Show("تم حفظ التغييرات بنجاح ✔️", "نجاح", MessageBoxButton.OK, MessageBoxImage.Information);
                CloseProfileModal_Click(null, null);
            }
            catch (Exception ex)
            {
                MessageBox.Show("خطأ أثناء الحفظ:\n" + ex.Message, "خطأ", MessageBoxButton.OK, MessageBoxImage.Error);
            }
        }

        // ====================================================
        // 2. التنقل بين الشاشات والأزرار (Navigation)
        // ====================================================

        private void UpdateActiveTab(Button activeBtn)
        {
            BtnNavPlanner.Style = (Style)FindResource("GlassButton");
            BtnNavSaved.Style = (Style)FindResource("GlassButton");
            BtnNavCalc.Style = (Style)FindResource("GlassButton");

            if (activeBtn != null)
                activeBtn.Style = (Style)FindResource("PrimaryButton");
        }

        private void SwitchView(Grid viewToShow)
        {
            ViewPlanner.Visibility = Visibility.Collapsed;
            ViewCalculator.Visibility = Visibility.Collapsed;
            ViewSavedMeals.Visibility = Visibility.Collapsed;

            if (viewToShow != null)
                viewToShow.Visibility = Visibility.Visible;
        }

        private void Nav_Planner_Click(object sender, RoutedEventArgs e)
        {
            SwitchView(ViewPlanner);
            UpdateActiveTab(BtnNavPlanner);
        }

        private void Nav_Calculator_Click(object sender, RoutedEventArgs e)
        {
            SwitchView(ViewCalculator);
            UpdateActiveTab(BtnNavCalc);
        }

        private void Nav_Saved_Click(object sender, RoutedEventArgs e)
        {
            SwitchView(ViewSavedMeals);
            UpdateActiveTab(BtnNavSaved);
            LoadSavedMeals(); // 👈 هادي هي لي كانت ناقصة باش تعمر الوجبات المحفوظة!
        }

        private void LoadSavedMeals()
        {
            // بيانات تجريبية لتعبئة الكروت والديناميكية حتى تتأكد من الشكل
            var mealsList = new System.Collections.Generic.List<SavedMealModel>
            {
                new SavedMealModel
                {
                    Id = 1,
                    Title = "طبق البيض المخفوق بالأرز الأبيض مع السلطة الخضراء",
                    Calories = "Kcal 700", Protein = "33 غرام", Carbs = "88 غرام", Fat = "28 غرام",
                    Ingredients = new System.Collections.Generic.List<string> {
                        "أرز أبيض مطبوخ: 250 غرام (حوالي كوب ونصف)",
                        "بيض كامل: 4 حبات (حجم كبير)",
                        "خس: 100 غرام",
                        "طماطم: 100 غرام (حبة متوسطة)",
                        "زيت للطهي (أو رذاذ زيت): 5 غرام"
                    },
                    Instructions = "✔️ 1. قم بطهي الأرز الأبيض بالطريقة المعتادة.\n✔️ 2. في مقلاة غير لاصقة، اخفق البيض جيداً واطهيه مع القليل من الزيت.\n✔️ 3. قطع الخس والطماطم لتجهيز السلطة وقدمها بجانب الأرز والبيض."
                },
                new SavedMealModel
                {
                    Id = 2,
                    Title = "طبق صدور الدجاج المسلوقة مع الأرز واللوبيا",
                    Calories = "Kcal 200", Protein = "21 غرام", Carbs = "25 غرام", Fat = "2 غرام",
                    Ingredients = new System.Collections.Generic.List<string> {
                        "أرز أبيض (مطهو): 50 غرام",
                        "لوبيا مسلوقة: 50 غرام",
                        "ملح وبهارات حسب الرغبة (بدون زيت)"
                    },
                    Instructions = "✔️ 1. تحضير الدجاج: يُغسل صدر الدجاج جيداً ويسلق في قدر صغير مع الملح وورق الغار (15 دقيقة).\n✔️ 2. يُقدم الدجاج المسلوق والمقطع مع الأرز واللوبيا الدافئة."
                }
            };

            LstSavedMeals.ItemsSource = mealsList;
        }

        // ====================================================
        // 3. التحكم في النافذة (Windows Controls & Theme)
        // ====================================================

        private void TitleBar_MouseDown(object sender, MouseButtonEventArgs e)
        {
            if (e.ChangedButton == MouseButton.Left) this.DragMove();
        }

        private void FloatingTitleBar_MouseLeave(object sender, MouseEventArgs e) { }
        private void BtnMinimize_Click(object sender, RoutedEventArgs e) => WindowState = WindowState.Minimized;
        private void BtnMaximize_Click(object sender, RoutedEventArgs e) => WindowState = WindowState == WindowState.Maximized ? WindowState.Normal : WindowState.Maximized;
        private void BtnClose_Click(object sender, RoutedEventArgs e) => Application.Current.Shutdown();

        private void Logout_Click(object sender, RoutedEventArgs e)
        {
            new LoginWindow().Show();
            this.Close();
        }

        private void OpenProfileModal_Click(object sender, RoutedEventArgs e) => ModalProfile.Visibility = Visibility.Visible;
        private void CloseProfileModal_Click(object sender, RoutedEventArgs e) => ModalProfile.Visibility = Visibility.Collapsed;

        // تفعيل زر تبديل المظهر (Light / Dark Mode)
        private void BtnThemeToggle_Click(object sender, RoutedEventArgs e)
        {
            App.ToggleTheme();
            TxtThemeIcon.Text = App.IsDarkMode ? "🌙" : "☀️";
        }

        // ====================================================
        // 4. دوال العمليات (Placeholder)
        // ====================================================

        private void ChooseMealImage_Click(object sender, RoutedEventArgs e) { }
        private void BtnGenerateMeal_Click(object sender, RoutedEventArgs e) { }
        private void BtnSaveMeal_Click(object sender, RoutedEventArgs e) { }
        private void BtnSendChat_Click(object sender, RoutedEventArgs e) { }
        private void TxtChatQuestion_KeyDown(object sender, KeyEventArgs e) { if (e.Key == Key.Enter) BtnSendChat_Click(sender, e); }
        private void CalculatorInput_Changed(object sender, TextChangedEventArgs e) { }
        private void CalculatorCombo_Changed(object sender, SelectionChangedEventArgs e) { }
        private void BtnCalculate_Click(object sender, RoutedEventArgs e) { }
        private void TransferToPlanner_Click(object sender, RoutedEventArgs e)
        {
            if (TxtPlannerCalories != null && LblCalories != null)
                TxtPlannerCalories.Text = LblCalories.Text.Replace(" Kcal", "");
            Nav_Planner_Click(null, null);
        }
        private void BtnDeleteSavedMeal_Click(object sender, RoutedEventArgs e) { }
    }

    // كلاس تمثيل بيانات الوجبة المحفوظة
    public class SavedMealModel
    {
        public int Id { get; set; }
        public string Title { get; set; }
        public string Calories { get; set; }
        public string Protein { get; set; }
        public string Carbs { get; set; }
        public string Fat { get; set; }
        public System.Collections.Generic.List<string> Ingredients { get; set; }
        public string Instructions { get; set; }
    }
}