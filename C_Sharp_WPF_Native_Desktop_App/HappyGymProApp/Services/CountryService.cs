/**
 * Copyright (C) 2026 Adham Cherkaoui Eddeqaqi (0dh4m). All rights reserved.
 * Proprietary and confidential. Unauthorized copying or distribution is strictly prohibited.
 * Author: Adham Cherkaoui Eddeqaqi (0dh4m) <0dh4mm@gmail.com>
 * GitHub: https://github.com/0dh4m
 */

﻿using System.Collections.Generic;

namespace HappyGymProApp.Services
{
    public class CountryItem
    {
        public string Name { get; set; }
        public string Code { get; set; } // كود الاتصال الدولي بدون علامة +
        public string DisplayText => $"{Name} (+{Code})";
    }

    public static class CountryService
    {
        public static List<CountryItem> GetCountries()
        {
            return new List<CountryItem>
            {
                // الدول العربية (في المقدمة لسهولة الاختيار)
                new CountryItem { Name = "المغرب", Code = "212" },
                new CountryItem { Name = "الجزائر", Code = "213" },
                new CountryItem { Name = "تونس", Code = "216" },
                new CountryItem { Name = "مصر", Code = "20" },
                new CountryItem { Name = "السعودية", Code = "966" },
                new CountryItem { Name = "الإمارات", Code = "971" },
                new CountryItem { Name = "قطر", Code = "974" },
                new CountryItem { Name = "الكويت", Code = "965" },
                new CountryItem { Name = "البحرين", Code = "973" },
                new CountryItem { Name = "عمان", Code = "968" },
                new CountryItem { Name = "الأردن", Code = "962" },
                new CountryItem { Name = "العراق", Code = "964" },
                new CountryItem { Name = "فلسطين", Code = "970" },
                new CountryItem { Name = "لبنان", Code = "961" },
                new CountryItem { Name = "سوريا", Code = "963" },
                new CountryItem { Name = "اليمن", Code = "967" },
                new CountryItem { Name = "ليبيا", Code = "218" },
                new CountryItem { Name = "السودان", Code = "249" },
                new CountryItem { Name = "موريتانيا", Code = "222" },
                new CountryItem { Name = "الصومال", Code = "252" },
                new CountryItem { Name = "جيبوتي", Code = "253" },
                new CountryItem { Name = "جزر القمر", Code = "269" },

                // باقي دول العالم الشهيرة
                new CountryItem { Name = "فرنسا", Code = "33" },
                new CountryItem { Name = "إسبانيا", Code = "34" },
                new CountryItem { Name = "إيطاليا", Code = "39" },
                new CountryItem { Name = "ألمانيا", Code = "49" },
                new CountryItem { Name = "المملكة المتحدة", Code = "44" },
                new CountryItem { Name = "الولايات المتحدة / كندا", Code = "1" },
                new CountryItem { Name = "تركيا", Code = "90" },
                new CountryItem { Name = "روسيا", Code = "7" },
                new CountryItem { Name = "الصين", Code = "86" },
                new CountryItem { Name = "اليابان", Code = "81" },
                new CountryItem { Name = "كوريا الجنوبية", Code = "82" },
                new CountryItem { Name = "الهند", Code = "91" },
                new CountryItem { Name = "البرازيل", Code = "55" },
                new CountryItem { Name = "الأرجنتين", Code = "54" },
                new CountryItem { Name = "المكسيك", Code = "52" },
                new CountryItem { Name = "كندا", Code = "1" },
                new CountryItem { Name = "أستراليا", Code = "61" },
                new CountryItem { Name = "بلجيكا", Code = "32" },
                new CountryItem { Name = "هولندا", Code = "31" },
                new CountryItem { Name = "سويسرا", Code = "41" },
                new CountryItem { Name = "البرتغال", Code = "351" },
                new CountryItem { Name = "السويد", Code = "46" },
                new CountryItem { Name = "النرويج", Code = "47" },
                new CountryItem { Name = "الدنمارك", Code = "45" },
                new CountryItem { Name = "فنلندا", Code = "358" },
                new CountryItem { Name = "بولندا", Code = "48" },
                new CountryItem { Name = "اليونان", Code = "30" },
                new CountryItem { Name = "جمهورية التشيك", Code = "420" },
                new CountryItem { Name = "المجر", Code = "36" },
                new CountryItem { Name = "رومانيا", Code = "40" },
                new CountryItem { Name = "أوكرانيا", Code = "380" },
                new CountryItem { Name = "جنوب إفريقيا", Code = "27" },
                new CountryItem { Name = "نيجيريا", Code = "234" },
                new CountryItem { Name = "كينيا", Code = "254" },
                new CountryItem { Name = "غانا", Code = "233" },
                new CountryItem { Name = "السنغال", Code = "221" },
                new CountryItem { Name = "ساحل العاج", Code = "225" },
                new CountryItem { Name = "الكاميرون", Code = "237" },
                new CountryItem { Name = "ماليزيا", Code = "60" },
                new CountryItem { Name = "إندونيسيا", Code = "62" },
                new CountryItem { Name = "باكستان", Code = "92" },
                new CountryItem { Name = "بنغلاديش", Code = "880" },
                new CountryItem { Name = "الفلبين", Code = "63" },
                new CountryItem { Name = "فيتنام", Code = "84" },
                new CountryItem { Name = "تايلاند", Code = "66" }
            };
        }
    }
}