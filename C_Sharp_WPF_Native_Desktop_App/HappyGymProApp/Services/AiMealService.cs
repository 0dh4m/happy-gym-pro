/**
 * Copyright (C) 2026 Adham Cherkaoui Eddeqaqi (0dh4m). All rights reserved.
 * Proprietary and confidential. Unauthorized copying or distribution is strictly prohibited.
 * Author: Adham Cherkaoui Eddeqaqi (0dh4m) <adamcherkaoui99@gmail.com>
 * GitHub: https://github.com/0dh4m
 */

﻿using System;
using System.IO;
using System.Net;
using System.Net.Http;
using System.Net.Http.Headers;
using System.Text;
using System.Threading.Tasks;
using Newtonsoft.Json.Linq;

namespace HappyGymProApp.Services
{
    public static class AiMealService
    {
        private const string GeminiApiKey = "YOUR_GEMINI_API_KEY_HERE";

        // استخدام موديل gemini-pro المعتمد والمستقر للشات والنصوص
        private const string GeminiApiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent";

        private static readonly HttpClient httpClient;

        static AiMealService()
        {
            ServicePointManager.SecurityProtocol = SecurityProtocolType.Tls12;

            var handler = new HttpClientHandler
            {
                AutomaticDecompression = DecompressionMethods.GZip | DecompressionMethods.Deflate
            };

            httpClient = new HttpClient(handler)
            {
                Timeout = TimeSpan.FromSeconds(25)
            };
            httpClient.DefaultRequestHeaders.Accept.Add(new MediaTypeWithQualityHeaderValue("application/json"));
        }

        public static async Task<string> GenerateMealAsync(string calories, string ingredients, string imagePath = null)
        {
            try
            {
                string prompt = $@"
أنت خبير تغذية رياضية وشيف محترف. قم بابتكار وجبة رياضية صحية متوازنة تعتمد على البيانات التالية:
- السعرات الحرارية المستهدفة: {calories} Kcal تقريباً.
- المكونات المتاحة أو المطلوبة: {(string.IsNullOrWhiteSpace(ingredients) ? "اختر مكونات صحية متوفرة وشائعة للرياضيين" : ingredients)}.

يجب أن تلتزم بالهيكل الصارم التالي بدقة وبدون أي مقدمات أو خاتمات خارجة عنه:

# [اسم الوجبة الصحي والمميز]

المقادير الموزونة:
- [المكون 1]: [الوزن بالغرام بدقة]
- [المكون 2]: [الوزن بالغرام بدقة]
- [المكون 3]: [الوزن بالغرام بدقة]
- [التوابل وزيت الطهي المقترح]: [الكمية الدقيقة بالملعقة أو الغرام]

طريقة التحضير:
1. [مرحلة التجهيز والتتبيل: شرح مفصل لغسيل وتقطيع المكونات وتتبيلها بالبهارات والمقادير].
2. [مرحلة الطهي الأولى: تحديد طريقة الطهي (شواء، سلق، قلاية هوائية)، درجة الحرارة، والوقت بالدقائق بدقة].
3. [مرحلة الطهي المكملة: تحضير الكاربوهيدرات أو الخضار وتوقيت دمج المكونات].
4. [مرحلة اللمسة النهائية والتقديم: كيفية تقديم الطبق متكاملاً، مع نصيحة لتعزيز النكهة وامتصاص المغذيات].

القيم الغذائية:
- إجمالي السعرات الحرارية: ~{calories} Kcal
";

                JObject requestBody = new JObject
                {
                    ["contents"] = new JArray
                    {
                        new JObject
                        {
                            ["parts"] = new JArray
                            {
                                new JObject { ["text"] = prompt }
                            }
                        }
                    }
                };

                using (var content = new StringContent(requestBody.ToString(), Encoding.UTF8, "application/json"))
                {
                    var response = await httpClient.PostAsync($"{GeminiApiUrl}?key={GeminiApiKey}", content);
                    string jsonResponse = await response.Content.ReadAsStringAsync();

                    if (response.IsSuccessStatusCode)
                    {
                        JObject parsed = JObject.Parse(jsonResponse);
                        string generatedText = parsed["candidates"]?[0]?["content"]?["parts"]?[0]?["text"]?.ToString();
                        return !string.IsNullOrEmpty(generatedText) ? generatedText.Trim() : GetFallbackMeal(calories);
                    }
                }

                return GetFallbackMeal(calories);
            }
            catch
            {
                return GetFallbackMeal(calories);
            }
        }

        /// <summary>
        /// الشات مع المدرب الذكي
        /// </summary>
        public static async Task<string> AskCoachAsync(string question)
        {
            if (string.IsNullOrWhiteSpace(question))
            {
                return "يرجى كتابة سؤال للمدرب الذكي.";
            }

            try
            {
                string prompt = $"أنت كابتن ومدرب كمال أجسام وخبير تغذية رياضية في Happy Gym PRO. أجب عن هذا السؤال بإيجاز ودقة واحترافية وبشكل مباشر باللغة العربية في جملتين أو ثلاث: {question}";

                var requestBody = new JObject
                {
                    ["contents"] = new JArray
                    {
                        new JObject
                        {
                            ["parts"] = new JArray
                            {
                                new JObject { ["text"] = prompt }
                            }
                        }
                    }
                };

                using (var content = new StringContent(requestBody.ToString(), Encoding.UTF8, "application/json"))
                {
                    var response = await httpClient.PostAsync($"{GeminiApiUrl}?key={GeminiApiKey}", content);
                    string jsonResponse = await response.Content.ReadAsStringAsync();

                    if (response.IsSuccessStatusCode)
                    {
                        JObject parsed = JObject.Parse(jsonResponse);
                        string reply = parsed["candidates"]?[0]?["content"]?["parts"]?[0]?["text"]?.ToString();

                        if (!string.IsNullOrWhiteSpace(reply))
                        {
                            return reply.Trim();
                        }
                    }
                    else
                    {
                        return $"⚠️ خطأ من السيرفر (كود {response.StatusCode}):\n{jsonResponse}";
                    }
                }

                return "كابتن، لم يتم استلام رد.";
            }
            catch (Exception ex)
            {
                return $"⚠️ خطأ في الاتصال بالبرمجية: {ex.Message}";
            }
        }

        private static string GetFallbackMeal(string cal)
        {
            return $@"# وجبة البناء العضلي المتكاملة ({cal} Kcal)

المقادير الموزونة:
- صدور دجاج مخلية: 220 غرام
- أرز بسمتي أبيض (موزون جاف): 90 غرام
- بروكلي طازج مقطع: 150 غرام
- زيت زيتون بكر ممتاز: 10 غرام (ملعقة طعام)
- تتبيلة (ملح هيمالايا، فلفل أسود، ثوم بودرة، بابريكا مدخنة): 5 غرام

طريقة التحضير:
1. التتبيل والتجهيز: قطّع صدور الدجاج إلى شرائح متساوية وتبّلها بخليط الملح، الفلفل، الثوم البودرة والبابريكا مع نصف ملعقة زيت زيتون، واتركها 10 دقائق لامتصاص النكهة.
2. طهي الأرز: اغسل الأرز جيداً وصفّه، ثم ضعه في قدر مع 180 مل ماء ورشة ملح على نار متوسطة حتى الغليان، ثم اخفض النار وغطّ القدر لمدة 12 دقيقة حتى ينضج تماماً.
3. شواء الدجاج: سخن مقلاة غير لاصقة على نار متوسطة إلى عالية، ضع قطع الدجاج واطهها لمدة 5-6 دقائق لكل جهة حتى تكتسب لوناً ذهبياً وتنضج من الداخل.
4. طهي البروكلي والتقديم: اطهِ البروكلي على البخار لمدة 4 دقائق للحفاظ على قرمشته وقيمته الغذائية، ثم وزع الأرز والدجاج والبروكلي في الطبق واسكب فوق الخضار ما تبقى من زيت الزيتون.

القيم الغذائية:
- إجمالي السعرات الحرارية: ~{cal} Kcal";
        }
    }
}