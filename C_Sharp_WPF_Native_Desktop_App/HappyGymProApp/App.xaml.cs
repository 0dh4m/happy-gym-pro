using System;
using System.Windows;
using System.Windows.Media;

namespace HappyGymProApp
{
    public partial class App : Application
    {
        public static bool IsDarkMode = true;

        // دالة تغيير المظهر (يتم استدعاؤها من أي مكان في التطبيق)
        public static void ToggleTheme()
        {
            IsDarkMode = !IsDarkMode;

            if (IsDarkMode)
            {
                // ألوان الليل (Cyber Dark)
                Current.Resources["BgDeep"] = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#040711"));
                Current.Resources["BgSurface"] = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#C0121826"));
                Current.Resources["BgInput"] = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#991A2234"));
                Current.Resources["BorderGlass"] = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#14FFFFFF"));

                Current.Resources["TextPrimary"] = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#f8fafc"));
                Current.Resources["TextMuted"] = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#94a3b8"));
            }
            else
            {
                // ألوان النهار المريحة (Matte Soft Slate)
                Current.Resources["BgDeep"] = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#94a3b8"));
                Current.Resources["BgSurface"] = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#cbd5e1"));
                Current.Resources["BgInput"] = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#66FFFFFF"));
                Current.Resources["BorderGlass"] = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#330F1722"));

                Current.Resources["TextPrimary"] = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#0f172a"));
                Current.Resources["TextMuted"] = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#334155"));
            }
        }
    }
}