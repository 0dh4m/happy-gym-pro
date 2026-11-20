import express from 'express';
import dotenv from 'dotenv';
import path from 'path';
import { fileURLToPath } from 'url';
import { GoogleGenAI, Type } from '@google/genai';

dotenv.config();

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
app.use(express.json());

// Initialize Gemini Client
const apiKey = process.env.GEMINI_API_KEY || "YOUR_GEMINI_API_KEY_HERE";
const ai = new GoogleGenAI({
  apiKey: apiKey,
  httpOptions: {
    headers: {
      'User-Agent': 'aistudio-build',
    }
  }
});

// 1. Endpoint: AI-Powered Meal Generator
app.post('/api/generate-recipe', async (req, res) => {
  try {
    const { ingredients, targetCalories, mealType, fitnessGoal, notes } = req.body;

    if (!ingredients || !Array.isArray(ingredients) || ingredients.length === 0) {
      return res.status(400).json({ status: 'error', message: 'المرجو اختيار مكون واحد على الأقل' });
    }

    const prompt = `اصنع لي وصفة مغربية صحية مخصصة للرياضيين ومحبّي كمال الأجسام بناءً على المعايير التالية:
- المكونات المتاحة: ${ingredients.join(', ')}
- السعرات الحرارية المستهدفة: ${targetCalories} سعرة حرارية
- نوع الوجبة: ${mealType} (مثال: فطور، غداء، عشاء، وجبة خفيفة، قبل/بعد التمرين)
- الهدف الرياضي الكلي: ${fitnessGoal} (مثال: ضخامة Bulking، تنشيف Cutting، ثبات الوزن Maintenance)
- ملاحظات إضافية: ${notes || 'لا توجد ملاحظات'}

يجب أن تكون المقادير دقيقة ومحسوبة لتلائم السعرات المستهدفة (${targetCalories} kcal) قدر الإمكان.
تأكد من كتابة اسم الوصفة بالدارجة المغربية أو مصطلح مغربي معروف لتكون قريبة للقلب ومألوفة.
اشرح طريقة التحضير بالدارجة المغربية المبسطة وكأنك كوتش طيب في المطبخ.`;

    const response = await ai.models.generateContent({
      model: "gemini-3.5-flash",
      contents: prompt,
      config: {
        systemInstruction: "أنت خبير تغذية مغربي وطاهٍ رياضي بارع. تصمم وصفات مغربية معدلة لتناسب لاعبي كمال الأجسام مع حساب دقيق للماكروز والسعرات.",
        responseMimeType: "application/json",
        responseSchema: {
          type: Type.OBJECT,
          properties: {
            recipeName: { type: Type.STRING, description: "اسم الوجبة بالدارجة المغربية والعربية الفصحى" },
            cookingTime: { type: Type.STRING, description: "مدة التحضير والطهي" },
            calories: { type: Type.INTEGER, description: "إجمالي السعرات الحرارية في الوجبة" },
            protein: { type: Type.NUMBER, description: "كمية البروتين بالجرام" },
            carbs: { type: Type.NUMBER, description: "كمية الكاربوهيدرات بالجرام" },
            fat: { type: Type.NUMBER, description: "كمية الدهون بالجرام" },
            ingredients: {
              type: Type.ARRAY,
              items: {
                type: Type.OBJECT,
                properties: {
                  name: { type: Type.STRING, description: "اسم المكون بالدارجة أو العربية" },
                  amount: { type: Type.STRING, description: "الكمية الموصى بها بالجرامات أو الملاعق" }
                },
                required: ["name", "amount"]
              }
            },
            instructions: {
              type: Type.ARRAY,
              items: { type: Type.STRING, description: "خطوات التحضير بالتفصيل بالدارجة" }
            },
            fitnessBenefit: { type: Type.STRING, description: "لماذا هذه الوجبة ممتازة لهذا الهدف بالدارجة المغربية" }
          },
          required: ["recipeName", "cookingTime", "calories", "protein", "carbs", "fat", "ingredients", "instructions", "fitnessBenefit"]
        }
      }
    });

    const text = response.text || "{}";
    const recipeData = JSON.parse(text.trim());
    return res.json({ status: 'success', recipe: recipeData });

  } catch (error: any) {
    console.error('Error in recipe generation:', error);
    return res.status(500).json({ status: 'error', message: 'فشل في توليد الوصفة بالذكاء الاصطناعي. المرجو المحاولة مجدداً.' });
  }
});

// 2. Endpoint: Coach Gemini Chatbot
app.post('/api/coach-chat', async (req, res) => {
  try {
    const { message, history } = req.body;

    if (!message) {
      return res.status(400).json({ status: 'error', message: 'المرجو كتابة رسالة' });
    }

    const systemInstruction = `أنت 'الكوتش جيميناي' (Coach Gemini)، كوتش ومدرب كمال أجسام وتغذية مغربي أسطوري.
تتحدث بالدارجة المغربية الشعبية والرياضية الممزوجة بالتشجيع والطاقة الإيجابية.
نصائحك دقيقة ومحفزة جداً وتستند إلى العلم والرياضة.
تحب استخدام كلمات مثل: 'الوحش'، 'البطل'، 'الترينينغ'، 'الضخامة'، 'التقطاع'، 'النشاف'، 'الصدر'، 'الباي'، 'الماكلة والنعاس والخدمة'.
تجنب الحديث برسمية مفرطة، بل تحدث بصيغة الكوتش الصديق والمخلص المتفاني.
إذا سألك عن تمارين أو نصائح عضلية، أعطه روتين تدريبي أو نصيحة ذهبية بالأرقام.
كن إيجابياً ومحفزاً واختم دائماً بعبارات تشجيعية قوية.`;

    const chatHistory = (history || []).map((msg: any) => ({
      role: msg.role === 'user' ? 'user' : 'model',
      parts: [{ text: msg.content }]
    }));

    const chatInstance = ai.chats.create({
      model: "gemini-3.5-flash",
      config: {
        systemInstruction: systemInstruction,
        temperature: 0.8,
      },
      history: chatHistory
    });

    const response = await chatInstance.sendMessage({ message: message });
    return res.json({ status: 'success', reply: response.text });

  } catch (error: any) {
    console.error('Error in coach chat:', error);
    return res.status(500).json({ status: 'error', message: 'فشل في الاتصال بالكوتش جيميناي. المرجو المحاولة مجدداً.' });
  }
});

// Live server and Vite middleware routing
const port = 3000;

if (process.env.NODE_ENV === 'production') {
  // Serve static dist in production
  app.use(express.static(path.join(__dirname, 'dist')));
  app.get('*', (req, res) => {
    res.sendFile(path.join(__dirname, 'dist', 'index.html'));
  });
} else {
  // Use Vite development server as middleware
  const { createServer: createViteServer } = await import('vite');
  const vite = await createViteServer({
    server: { middlewareMode: true },
    appType: 'spa',
  });
  app.use(vite.middlewares);
}

app.listen(port, '0.0.0.0', () => {
  console.log(`Server is running at http://0.0.0.0:${port}`);
});
