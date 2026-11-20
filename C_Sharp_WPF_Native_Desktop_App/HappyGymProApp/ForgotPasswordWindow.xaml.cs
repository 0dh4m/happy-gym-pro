using System;
using System.Collections.Generic;
using System.Text.RegularExpressions;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Input;
using System.Windows.Media;
using System.Windows.Shapes;
using HappyGymProApp.Services;
using MySql.Data.MySqlClient;
using BCrypt.Net;

namespace HappyGymProApp
{
    public partial class ForgotPasswordWindow : Window
    {
        private const string ConnectionString = "Server=localhost;Port=3306;Database=gym_db;Uid=root;Pwd=;Charset=utf8mb4;";
        private string generatedOtp = "";
        private string targetFullPhone = "";

        // === متغيرات جزيئات الفضاء ===
        private List<Particle> particles = new List<Particle>();
        private Random rand = new Random();
        private SolidColorBrush darkParticleBrush;
        private SolidColorBrush darkLineBrush;
        private SolidColorBrush lightParticleBrush;
        private SolidColorBrush lightLineBrush;

        public ForgotPasswordWindow()
        {
            InitializeComponent();

            // إخفاء مؤشر الماوس الافتراضي نهائياً لمنع ظهوره فوق العناصر والأزرار
            this.Cursor = Cursors.None;
            Mouse.OverrideCursor = Cursors.None;
        }

        private void Window_Loaded(object sender, RoutedEventArgs e)
        {
            SetupParticles(); // تشغيل محرك الفضاء

            try
            {
                var countries = CountryService.GetCountries();
                CmbCountry.ItemsSource = countries;
                CmbCountry.SelectedIndex = 0; // المغرب افتراضياً
            }
            catch { }

            // التأكد من أيقونة المظهر عند التحميل
            TxtThemeIcon.Text = App.IsDarkMode ? "🌙" : "☀️";
        }

        // ====================================================
        // 1. تتبع حركة مؤشر الماوس المخصص وتأثير الـ Hover
        // ====================================================
        private void Window_MouseMove(object sender, MouseEventArgs e)
        {
            Point pos = e.GetPosition(this);

            // تحريك المؤشر المخصص لمنتصف مكان الفأرة بالضبط (-20 لأن العرض والارتفاع 40)
            Canvas.SetLeft(CustomCursor, pos.X - 20);
            Canvas.SetTop(CustomCursor, pos.Y - 20);

            // فحص ما إذا كان الماوس يمر فوق عنصر تفاعلي لتحويل لونه للبرتقالي
            IInputElement element = InputHitTest(pos);
            bool isInteractive = false;

            if (element is DependencyObject depObj)
            {
                var parent = depObj;
                while (parent != null)
                {
                    if (parent is Button || parent is TextBox || parent is PasswordBox || parent is ComboBox || parent is TextBlock)
                    {
                        isInteractive = true;
                        break;
                    }
                    parent = LogicalTreeHelper.GetParent(parent) ?? VisualTreeHelper.GetParent(parent);
                }
            }

            // أخذ الألوان مباشرة من App.xaml لتدعم وضع الليل والنهار
            Brush targetBrush = isInteractive
                ? (SolidColorBrush)FindResource("AccentAmber")
                : (SolidColorBrush)FindResource("TextPrimary");

            CursorLineT.Background = targetBrush;
            CursorLineB.Background = targetBrush;
            CursorLineL.Background = targetBrush;
            CursorLineR.Background = targetBrush;
            CursorDot.Fill = targetBrush;
        }

        // ====================================================
        // 2. إدارة الوضع الليلي والنهاري (الاعتماد على App.xaml)
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
            darkParticleBrush = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#5906B6D4")); // سيان لليل
            darkLineBrush = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#1A06B6D4"));

            lightParticleBrush = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#E6F59E0B")); // برتقالي ساطع للنهار
            lightLineBrush = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#4DF59E0B"));

            int count = 50; // عدد الجزيئات
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
        // 4. حدث تغيير الدولة في القائمة المنسدلة
        // ====================================================
        private void CmbCountry_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            // معالجة تغيير الدولة
        }

        // ====================================================
        // 5. إرسال رمز OTP عبر الواتساب
        // ====================================================
        private async void BtnSendOtp_Click(object sender, RoutedEventArgs e)
        {
            string rawPhone = TxtPhone.Text.Trim();
            string cleanLocalPhone = Regex.Replace(rawPhone, @"[^\d]", "");
            if (cleanLocalPhone.StartsWith("0")) cleanLocalPhone = cleanLocalPhone.Substring(1);

            if (string.IsNullOrEmpty(cleanLocalPhone))
            {
                ShowMessage("المرجو إدخال رقم الهاتف المسجل به.", false);
                return;
            }

            string countryCode = ((CountryItem)CmbCountry.SelectedItem)?.Code ?? "212";
            countryCode = countryCode.TrimStart('+');
            targetFullPhone = countryCode + cleanLocalPhone;

            BtnSendOtp.IsEnabled = false;
            BtnSendOtp.Content = "جاري إرسال الرمز... ⏳";
            HideMessage();

            try
            {
                bool userExists = false;

                using (MySqlConnection conn = new MySqlConnection(ConnectionString))
                {
                    await conn.OpenAsync();
                    string query = "SELECT id FROM users WHERE phone LIKE @phone LIMIT 1";

                    using (MySqlCommand cmd = new MySqlCommand(query, conn))
                    {
                        cmd.Parameters.AddWithValue("@phone", "%" + cleanLocalPhone);
                        var result = await cmd.ExecuteScalarAsync();
                        userExists = (result != null);
                    }
                }

                if (!userExists)
                {
                    ShowMessage("رقم الهاتف هذا غير مسجل لدينا في المنصة.", false);
                    BtnSendOtp.IsEnabled = true;
                    BtnSendOtp.Content = "إرسال الرمز عبر WhatsApp 📲";
                    return;
                }

                Random rnd = new Random();
                generatedOtp = rnd.Next(100000, 999999).ToString();

                string waMessage = "🔐 *رمز استرجاع الحساب - Happy Gym PRO*\n\n"
                                 + "رمز التحقق (OTP) الخاص بك هو:\n"
                                 + $"👉 *{generatedOtp}*\n\n"
                                 + "⏳ هذا الرمز صالح لمدة 10 دقائق.\n"
                                 + "⚠️ لا تشارك هذا الرمز مع أي شخص.";

                bool sent = await WhatsAppService.SendMessageAsync(targetFullPhone, waMessage);

                if (sent)
                {
                    TxtSubTitle.Text = $"أدخل الرمز المكون من 6 أرقام المرسل إلى (+{targetFullPhone})";
                    ShowMessage("تم إرسال رمز التحقق إلى واتسابك بنجاح! 📲", true);
                    PanelPhoneStep.Visibility = Visibility.Collapsed;
                    PanelResetStep.Visibility = Visibility.Visible;
                }
                else
                {
                    ShowMessage("تعذر إرسال الرسالة عبر الواتساب، تأكد من تشغيل السيرفر.", false);
                    BtnSendOtp.IsEnabled = true;
                    BtnSendOtp.Content = "إرسال الرمز عبر WhatsApp 📲";
                }
            }
            catch (Exception ex)
            {
                ShowMessage("حدث خطأ:\n" + ex.Message, false);
                BtnSendOtp.IsEnabled = true;
                BtnSendOtp.Content = "إرسال الرمز عبر WhatsApp 📲";
            }
        }

        // ====================================================
        // 6. التحقق من الرمز وتغيير كلمة المرور
        // ====================================================
        private async void BtnResetPassword_Click(object sender, RoutedEventArgs e)
        {
            string enteredOtp = TxtOtp.Text.Trim();
            string newPassword = TxtNewPassword.Password;
            string confirmPassword = TxtConfirmPassword.Password;

            if (string.IsNullOrEmpty(enteredOtp) || string.IsNullOrEmpty(newPassword) || string.IsNullOrEmpty(confirmPassword))
            {
                ShowMessage("المرجو ملء جميع الحقول المطلوبة.", false);
                return;
            }

            if (enteredOtp != generatedOtp)
            {
                ShowMessage("رمز التحقق غير صحيح، يرجى التأكد وإعادة المحاولة.", false);
                return;
            }

            if (newPassword.Length < 6)
            {
                ShowMessage("كلمة المرور يجب أن تتكون من 6 خانات على الأقل.", false);
                return;
            }

            if (newPassword != confirmPassword)
            {
                ShowMessage("كلمتا المرور غير متطابقتين.", false);
                return;
            }

            BtnResetPassword.IsEnabled = false;
            BtnResetPassword.Content = "جاري تحديث كلمة المرور... ⏳";
            HideMessage();

            try
            {
                string hashedPassword = BCrypt.Net.BCrypt.HashPassword(newPassword);

                using (MySqlConnection conn = new MySqlConnection(ConnectionString))
                {
                    await conn.OpenAsync();
                    string query = "UPDATE users SET password = @pwd WHERE phone LIKE @phone";

                    using (MySqlCommand cmd = new MySqlCommand(query, conn))
                    {
                        cmd.Parameters.AddWithValue("@pwd", hashedPassword);
                        cmd.Parameters.AddWithValue("@phone", "%" + targetFullPhone);
                        await cmd.ExecuteNonQueryAsync();
                    }
                }

                MessageBox.Show("تم تغيير كلمة المرور بنجاح! يمكنك الآن تسجيل الدخول بكلمة المرور الجديدة.", "تم بنجاح", MessageBoxButton.OK, MessageBoxImage.Information);

                LoginWindow login = new LoginWindow();
                login.Show();
                this.Close();
            }
            catch (Exception ex)
            {
                ShowMessage("خطأ أثناء تحديث كلمة المرور:\n" + ex.Message, false);
                BtnResetPassword.IsEnabled = true;
                BtnResetPassword.Content = "حفظ وتغيير كلمة المرور 🔐";
            }
        }

        private void BackToLogin_MouseDown(object sender, MouseButtonEventArgs e)
        {
            LoginWindow login = new LoginWindow();
            login.Show();
            this.Close();
        }

        private void ShowMessage(string msg, bool isSuccess)
        {
            TxtStatusMsg.Text = msg;
            BorderMsg.Visibility = Visibility.Visible;

            if (isSuccess)
            {
                BorderMsg.Background = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#1c2e24"));
                BorderMsg.BorderBrush = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#34d399"));
                BorderMsg.BorderThickness = new Thickness(1);
                TxtStatusMsg.Foreground = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#34d399"));
            }
            else
            {
                BorderMsg.Background = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#26f87171"));
                BorderMsg.BorderBrush = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#f87171"));
                BorderMsg.BorderThickness = new Thickness(1);
                TxtStatusMsg.Foreground = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#f87171"));
            }
        }

        private void HideMessage()
        {
            BorderMsg.Visibility = Visibility.Collapsed;
        }
    }
}