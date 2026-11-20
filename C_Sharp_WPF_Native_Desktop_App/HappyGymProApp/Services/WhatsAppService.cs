using System;
using System.Net.Http;
using System.Text;
using System.Text.RegularExpressions;
using System.Threading.Tasks;
using Newtonsoft.Json;

namespace HappyGymProApp.Services
{
    public class WhatsAppService
    {
        private static readonly HttpClient client = new HttpClient();
        private const string SessionId = "ba9e08f8-5803-41b6-90e8-c7f0d653be6f";
        private const string ApiUrl = "http://localhost:2785/api/sessions/" + SessionId + "/messages/send-text";
        private const string ApiKey = "YOUR_OPENWA_API_KEY_HERE";

        // إرسال الرسائل العامة
        public static async Task<bool> SendMessageAsync(string toPhone, string messageText)
        {
            try
            {
                string cleanPhone = Regex.Replace(toPhone, @"[^\d]", "");
                string chatId = cleanPhone + "@c.us";

                var payload = new
                {
                    chatId = chatId,
                    text = messageText
                };

                var json = JsonConvert.SerializeObject(payload);
                var content = new StringContent(json, Encoding.UTF8, "application/json");

                using (var request = new HttpRequestMessage(HttpMethod.Post, ApiUrl))
                {
                    request.Headers.Add("x-api-key", ApiKey);
                    request.Headers.Add("Authorization", "Bearer " + ApiKey);
                    request.Content = content;

                    var response = await client.SendAsync(request);
                    return response.IsSuccessStatusCode;
                }
            }
            catch
            {
                return false;
            }
        }

        // إرسال تنبيهات استرجاع كلمة المرور (OTP)
        public static async Task<bool> SendNotificationAsync(string toPhone, string messageText)
        {
            return await SendMessageAsync(toPhone, messageText);
        }
    }
}