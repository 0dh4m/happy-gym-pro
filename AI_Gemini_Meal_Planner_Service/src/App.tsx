/**
 * @license
 * SPDX-License-Identifier: Apache-2.0
 */

import React, { useState, useEffect, useRef } from 'react';
import { 
  Dumbbell, 
  Flame, 
  Sparkles, 
  Scale, 
  Utensils, 
  Plus, 
  Trash2, 
  Save, 
  MessageSquare, 
  Send, 
  Check, 
  RotateCcw, 
  Activity, 
  ChevronRight, 
  Apple, 
  Info,
  TrendingUp,
  Award,
  BookOpen
} from 'lucide-react';

// Import Firebase configured modules and operations
import { 
  auth, 
  db, 
  googleProvider, 
  handleFirestoreError, 
  OperationType 
} from './firebase';
import { 
  signInWithPopup, 
  signOut, 
  onAuthStateChanged, 
  User 
} from 'firebase/auth';
import { 
  doc, 
  setDoc, 
  getDoc, 
  collection, 
  query, 
  orderBy, 
  onSnapshot, 
  deleteDoc, 
  serverTimestamp 
} from 'firebase/firestore';

// Structuring types
interface Ingredient {
  id: string;
  name: string;
  category: 'proteins' | 'carbs' | 'fats';
}

interface GeneratedRecipe {
  recipeName: string;
  cookingTime: string;
  calories: number;
  protein: number;
  carbs: number;
  fat: number;
  ingredients: Array<{ name: string; amount: string }>;
  instructions: string[];
  fitnessBenefit: string;
}

interface LoggedMeal {
  id: string;
  name: string;
  calories: number;
  protein: number;
  carbs: number;
  fat: number;
  timestamp: string;
}

interface ChatMessage {
  id: string;
  role: 'user' | 'model';
  content: string;
}

const getApiUrl = (endpoint: string) => {
  // If we are served from /gym-app/ or any path that contains gym-app, use /gym-app/api/
  if (window.location.pathname.includes('/gym-app')) {
    return `/gym-app/api/${endpoint}`;
  }
  return `/api/${endpoint}`;
};

// Default Moroccan-focused ingredients for Bodybuilders
const DEFAULT_INGREDIENTS: Ingredient[] = [
  // Proteins
  { id: 'eggs', name: 'البيض كامل (Eggs)', category: 'proteins' },
  { id: 'egg_whites', name: 'أبيض البيض (Egg Whites)', category: 'proteins' },
  { id: 'chicken_breast', name: 'صدر الدجاج (Chicken Breast)', category: 'proteins' },
  { id: 'turkey_breast', name: 'لاداند / ديك رومي (Turkey)', category: 'proteins' },
  { id: 'minced_meat', name: 'كفتة بقري (Minced Beef)', category: 'proteins' },
  { id: 'tuna', name: 'الطون المعلبة بالماء (Tuna)', category: 'proteins' },
  { id: 'sardines', name: 'سردين مغربي (Sardines)', category: 'proteins' },
  { id: 'whey_protein', name: 'واي بروتين (Whey Protein)', category: 'proteins' },
  { id: 'cottage_cheese', name: 'الجبن البلدي / بيرلي مسوس (Cottage/Perly)', category: 'proteins' },
  
  // Carbs
  { id: 'oats', name: 'الشوفان (Oats)', category: 'carbs' },
  { id: 'white_rice', name: 'الأرز الأبيض (White Rice)', category: 'carbs' },
  { id: 'basmati_rice', name: 'أرز بسمتي (Basmati)', category: 'carbs' },
  { id: 'sweet_potato', name: 'البطاطا الحلوة (Sweet Potato)', category: 'carbs' },
  { id: 'potato', name: 'البطاطس (Potato)', category: 'carbs' },
  { id: 'couscous', name: 'كسكس كامل (Whole Wheat Couscous)', category: 'carbs' },
  { id: 'whole_bread', name: 'خبز الزرع / الخبز الكامل (Whole Wheat Bread)', category: 'carbs' },
  { id: 'banana', name: 'الموز (Banana)', category: 'carbs' },
  { id: 'pasta', name: 'المقرونية / المعكرونة (Pasta)', category: 'carbs' },
  
  // Fats
  { id: 'olive_oil', name: 'زيت الزيتون البكر (Olive Oil)', category: 'fats' },
  { id: 'peanut_butter', name: 'زبدة كاوكاو (Peanut Butter)', category: 'fats' },
  { id: 'almonds', name: 'اللوز (Almonds)', category: 'fats' },
  { id: 'walnuts', name: 'الكركاع / الجوز (Walnuts)', category: 'fats' },
  { id: 'avocado', name: 'الأفوكادو (Avocado)', category: 'fats' },
  { id: 'dates', name: 'التمر (Dates)', category: 'fats' },
  { id: 'honey', name: 'العسل الحر (Honey)', category: 'fats' },
];

export default function App() {
  // General active tab
  const [activeTab, setActiveTab] = useState<'calculator' | 'generator' | 'tracker' | 'coach'>('generator');

  // Firebase Auth states
  const [user, setUser] = useState<User | null>(null);
  const [isAuthLoading, setIsAuthLoading] = useState<boolean>(true);
  const [isSyncing, setIsSyncing] = useState<boolean>(false);

  // Calculator State (Mifflin-St Jeor)
  const [weight, setWeight] = useState<number>(75);
  const [height, setHeight] = useState<number>(178);
  const [age, setAge] = useState<number>(23);
  const [gender, setGender] = useState<'male' | 'female'>('male');
  const [activity, setActivity] = useState<number>(1.375); // 1.2: sedentary, 1.375: light, 1.55: moderate, 1.725: intense
  const [fitnessGoal, setFitnessGoal] = useState<'bulking' | 'cutting' | 'maintenance'>('bulking');
  
  // Calculated Macro Results
  const [dailyCalorieTarget, setDailyCalorieTarget] = useState<number>(3100);
  const [targetMacros, setTargetMacros] = useState<{ protein: number; carbs: number; fat: number }>({
    protein: 160,
    carbs: 400,
    fat: 75,
  });

  // AI Generator state
  const [selectedIngredients, setSelectedIngredients] = useState<string[]>(['eggs', 'oats', 'banana', 'peanut_butter']);
  const [customIngredients, setCustomIngredients] = useState<string[]>([]);
  const [newCustomInput, setNewCustomInput] = useState<string>('');
  
  const [mealCalories, setMealCalories] = useState<number>(650);
  const [mealType, setMealType] = useState<string>('فطور (Ftour)');
  const [customNotes, setCustomNotes] = useState<string>('');
  
  const [isGenerating, setIsGenerating] = useState<boolean>(false);
  const [generatedRecipe, setGeneratedRecipe] = useState<GeneratedRecipe | null>(null);
  const [generationError, setGenerationError] = useState<string | null>(null);

  // Daily tracker state
  const [loggedMeals, setLoggedMeals] = useState<LoggedMeal[]>([]);
  
  // Chat console state
  const [chatMessages, setChatMessages] = useState<ChatMessage[]>([
    {
      id: 'welcome',
      role: 'model',
      content: 'السلام عليكم يا بطل! أنا الكوتش جيميناي مجهز بالذكاء الاصطناعي ومستعد نعاونك توصل للفورمة ديال أحلامك. بغيتي روتين تدريبي؟ أسئلة على الماكلة أو المكملات؟ سولني أي حاجة بالدارجة وجد راسك لطحن الحديد! 💪🇲🇦'
    }
  ]);
  const [currentChatInput, setCurrentChatInput] = useState<string>('');
  const [isChatLoading, setIsChatLoading] = useState<boolean>(false);
  const chatEndRef = useRef<HTMLDivElement>(null);

  // Setup Real-time Firebase listeners and local fallbacks
  useEffect(() => {
    const unsubscribeAuth = onAuthStateChanged(auth, async (currentUser) => {
      setUser(currentUser);
      setIsAuthLoading(false);

      if (currentUser) {
        // Fetch User Profile from Firestore once on login to initialize states
        try {
          const userRef = doc(db, 'users', currentUser.uid);
          const userSnap = await getDoc(userRef);
          if (userSnap.exists()) {
            const pData = userSnap.data();
            setWeight(Number(pData.weight) || 75);
            setHeight(Number(pData.height) || 178);
            setAge(Number(pData.age) || 23);
            setGender(pData.gender || 'male');
            setActivity(Number(pData.activity) || 1.375);
            setFitnessGoal(pData.fitnessGoal || 'bulking');
          }
        } catch (error) {
          console.error("Error reading profile from Firestore:", error);
        }

        // Live list subscription to Logged Meals subcollection
        const mealsRef = collection(db, 'users', currentUser.uid, 'logged_meals');
        const unsubscribeMeals = onSnapshot(mealsRef, (snap) => {
          const meals: LoggedMeal[] = [];
          snap.forEach((docSnap) => {
            const data = docSnap.data();
            meals.push({
              id: docSnap.id,
              name: data.name || '',
              calories: Number(data.calories) || 0,
              protein: Number(data.protein) || 0,
              carbs: Number(data.carbs) || 0,
              fat: Number(data.fat) || 0,
              timestamp: data.timestamp || '',
            });
          });
          // Sort meals locally by chronological order (descending ID)
          meals.sort((a, b) => b.id.localeCompare(a.id));
          setLoggedMeals(meals);
        }, (error) => {
          handleFirestoreError(error, OperationType.LIST, `users/${currentUser.uid}/logged_meals`);
        });

        // Live list subscription to Custom Ingredients subcollection
        const customIngsRef = collection(db, 'users', currentUser.uid, 'custom_ingredients');
        const unsubscribeIngs = onSnapshot(customIngsRef, (snap) => {
          const ingredientsList: string[] = [];
          snap.forEach((docSnap) => {
            ingredientsList.push(docSnap.id);
          });
          setCustomIngredients(ingredientsList);
        }, (error) => {
          handleFirestoreError(error, OperationType.LIST, `users/${currentUser.uid}/custom_ingredients`);
        });

        return () => {
          unsubscribeMeals();
          unsubscribeIngs();
        };
      } else {
        // Fallback: anonymous users use localStorage
        const savedMeals = localStorage.getItem('logged_meals');
        if (savedMeals) {
          try {
            setLoggedMeals(JSON.parse(savedMeals));
          } catch (e) {
            console.error(e);
          }
        }

        const savedCustom = localStorage.getItem('custom_ingredients');
        if (savedCustom) {
          try {
            setCustomIngredients(JSON.parse(savedCustom));
          } catch (e) {
            console.error(e);
          }
        }

        const savedProfile = localStorage.getItem('user_profile_data');
        if (savedProfile) {
          try {
            const parsed = JSON.parse(savedProfile);
            setWeight(parsed.weight || 75);
            setHeight(parsed.height || 178);
            setAge(parsed.age || 23);
            setGender(parsed.gender || 'male');
            setActivity(parsed.activity || 1.375);
            setFitnessGoal(parsed.fitnessGoal || 'bulking');
          } catch (e) {
            console.error(e);
          }
        }
      }
    });

    return () => {
      unsubscribeAuth();
    };
  }, []);

  // Save profile & Recalculate macros automatically when inputs change
  useEffect(() => {
    // Mifflin-St Jeor Formula
    let bmr = 0;
    if (gender === 'male') {
      bmr = bmr = (10 * weight) + (6.25 * height) - (5 * age) + 5;
    } else {
      bmr = (10 * weight) + (6.25 * height) - (5 * age) - 161;
    }

    const tdee = bmr * activity;
    let targetCals = Math.round(tdee);

    // Goal adjustments (Bulking +500, Cutting -500, Maintenance +0)
    if (fitnessGoal === 'bulking') {
      targetCals += 500;
    } else if (fitnessGoal === 'cutting') {
      targetCals -= 500;
    }

    // Guard minimum safe calories
    if (targetCals < 1200) targetCals = 1200;

    setDailyCalorieTarget(targetCals);

    // Calculate macro splits based on goals
    let proteinGrams = 0;
    let fatGrams = 0;
    let carbGrams = 0;

    if (fitnessGoal === 'bulking') {
      // High carbs, moderate fat, high protein (2.2g per kg)
      proteinGrams = Math.round(weight * 2.2);
      fatGrams = Math.round((targetCals * 0.25) / 9);
      carbGrams = Math.round((targetCals - (proteinGrams * 4) - (fatGrams * 9)) / 4);
    } else if (fitnessGoal === 'cutting') {
      // Super high protein (2.5g per kg), low carbs, moderate-low fat
      proteinGrams = Math.round(weight * 2.4);
      fatGrams = Math.round((targetCals * 0.20) / 9);
      carbGrams = Math.round((targetCals - (proteinGrams * 4) - (fatGrams * 9)) / 4);
    } else {
      // Balanced
      proteinGrams = Math.round(weight * 2.0);
      fatGrams = Math.round((targetCals * 0.25) / 9);
      carbGrams = Math.round((targetCals - (proteinGrams * 4) - (fatGrams * 9)) / 4);
    }

    if (carbGrams < 50) carbGrams = 50; // Guard for extra low carbs

    const macros = { protein: proteinGrams, carbs: carbGrams, fat: fatGrams };
    setTargetMacros(macros);

    // Save profile configurations
    localStorage.setItem('user_profile_data', JSON.stringify({
      weight, height, age, gender, activity, fitnessGoal
    }));
  }, [weight, height, age, gender, activity, fitnessGoal]);

  // Scroll to bottom of Coach Chat
  useEffect(() => {
    if (chatEndRef.current) {
      chatEndRef.current.scrollIntoView({ behavior: 'smooth' });
    }
  }, [chatMessages, isChatLoading]);

  // Handle adding custom ingredient
  const handleAddCustomIngredient = async () => {
    if (!newCustomInput.trim()) return;
    const cleanName = newCustomInput.trim();
    if (customIngredients.includes(cleanName) || DEFAULT_INGREDIENTS.some(i => i.name.toLowerCase() === cleanName.toLowerCase())) {
      setNewCustomInput('');
      return;
    }

    if (user) {
      try {
        await setDoc(doc(db, 'users', user.uid, 'custom_ingredients', cleanName), {
          name: cleanName,
          userId: user.uid,
          createdAt: serverTimestamp()
        });
      } catch (error) {
        handleFirestoreError(error, OperationType.WRITE, `users/${user.uid}/custom_ingredients/${cleanName}`);
      }
    } else {
      const updated = [...customIngredients, cleanName];
      setCustomIngredients(updated);
      localStorage.setItem('custom_ingredients', JSON.stringify(updated));
    }
    setNewCustomInput('');
    
    // Automatically select the new ingredient
    setSelectedIngredients(prev => [...prev, cleanName]);
  };

  // Remove custom ingredient
  const handleRemoveCustomIngredient = async (name: string) => {
    if (user) {
      try {
        await deleteDoc(doc(db, 'users', user.uid, 'custom_ingredients', name));
      } catch (error) {
        handleFirestoreError(error, OperationType.DELETE, `users/${user.uid}/custom_ingredients/${name}`);
      }
    } else {
      const updated = customIngredients.filter(item => item !== name);
      setCustomIngredients(updated);
      localStorage.setItem('custom_ingredients', JSON.stringify(updated));
    }
    setSelectedIngredients(prev => prev.filter(i => i !== name));
  };

  // Toggle ingredient selection
  const handleToggleIngredient = (idOrName: string) => {
    if (selectedIngredients.includes(idOrName)) {
      setSelectedIngredients(prev => prev.filter(i => i !== idOrName));
    } else {
      setSelectedIngredients(prev => [...prev, idOrName]);
    }
  };

  // Generate recipe via AI
  const handleGenerateRecipe = async () => {
    if (selectedIngredients.length === 0) {
      setGenerationError('المرجو اختيار مكون واحد على الأقل لتوليد الوجبة!');
      return;
    }

    setIsGenerating(true);
    setGenerationError(null);
    setGeneratedRecipe(null);

    // Collect names of all selected ingredients
    const selectedNames = selectedIngredients.map(item => {
      const def = DEFAULT_INGREDIENTS.find(di => di.id === item);
      return def ? def.name : item;
    });

    try {
      const response = await fetch(getApiUrl('generate-recipe'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          ingredients: selectedNames,
          targetCalories: mealCalories,
          mealType: mealType,
          fitnessGoal: fitnessGoal === 'bulking' ? 'التضخيم وعضلات قوية' : fitnessGoal === 'cutting' ? 'التنشيف وإبراز العضلات' : 'المحافظة على الوزن واللياقة البدنية',
          notes: customNotes
        })
      });

      const data = await response.json();
      if (data.status === 'success') {
        setGeneratedRecipe(data.recipe);
      } else {
        setGenerationError(data.message || 'حدث خطأ غير متوقع أثناء توليد الوصفة.');
      }
    } catch (err) {
      console.error(err);
      setGenerationError('فشل الاتصال بالخادم. تأكد من تشغيل السيرفر وحاول مجدداً.');
    } finally {
      setIsGenerating(false);
    }
  };

  // Log meal into diary
  const handleLogMeal = async (recipe: GeneratedRecipe) => {
    const mealId = Date.now().toString();
    const newMeal: LoggedMeal = {
      id: mealId,
      name: recipe.recipeName,
      calories: recipe.calories,
      protein: recipe.protein,
      carbs: recipe.carbs,
      fat: recipe.fat,
      timestamp: new Date().toLocaleTimeString('ar-MA', { hour: '2-digit', minute: '2-digit' })
    };

    if (user) {
      try {
        await setDoc(doc(db, 'users', user.uid, 'logged_meals', mealId), {
          ...newMeal,
          createdAt: serverTimestamp(),
          userId: user.uid
        });
        alert('✅ تم حفظ الوجبة وتزامنها في سجل اليوم في السحاب!');
      } catch (error) {
        handleFirestoreError(error, OperationType.WRITE, `users/${user.uid}/logged_meals/${mealId}`);
      }
    } else {
      const updated = [newMeal, ...loggedMeals];
      setLoggedMeals(updated);
      localStorage.setItem('logged_meals', JSON.stringify(updated));
      alert('✅ تم تسجيل وجبتك في سجل التغذية اليومي بنجاح!');
    }
  };

  // Clear or erase food log
  const handleDeleteLoggedMeal = async (id: string) => {
    if (user) {
      try {
        await deleteDoc(doc(db, 'users', user.uid, 'logged_meals', id));
      } catch (error) {
        handleFirestoreError(error, OperationType.DELETE, `users/${user.uid}/logged_meals/${id}`);
      }
    } else {
      const updated = loggedMeals.filter(m => m.id !== id);
      setLoggedMeals(updated);
      localStorage.setItem('logged_meals', JSON.stringify(updated));
    }
  };

  const handleClearAllLogs = async () => {
    if (confirm('هل أنت متأكد من مسح جميع وجبات اليوم؟')) {
      if (user) {
        try {
          for (const m of loggedMeals) {
            await deleteDoc(doc(db, 'users', user.uid, 'logged_meals', m.id));
          }
        } catch (error) {
          handleFirestoreError(error, OperationType.DELETE, `users/${user.uid}/logged_meals`);
        }
      } else {
        setLoggedMeals([]);
        localStorage.removeItem('logged_meals');
      }
    }
  };

  // Auth operations
  const handleSignIn = async () => {
    try {
      await signInWithPopup(auth, googleProvider);
    } catch (err) {
      console.error("Sign in error:", err);
    }
  };

  const handleSignOut = async () => {
    try {
      await signOut(auth);
      setLoggedMeals([]);
      setCustomIngredients([]);
    } catch (err) {
      console.error("Sign out error:", err);
    }
  };

  const handleSaveProfile = async () => {
    if (!user) return;
    setIsSyncing(true);
    try {
      const userRef = doc(db, 'users', user.uid);
      await setDoc(userRef, {
        weight,
        height,
        age,
        gender,
        activity,
        fitnessGoal,
        updatedAt: serverTimestamp()
      });
      alert('💾 تم حفظ معطياتك البدنية في السحابة بنجاح!');
    } catch (error) {
      handleFirestoreError(error, OperationType.WRITE, `users/${user.uid}`);
    } finally {
      setIsSyncing(false);
    }
  };

  // Coach Chat interactions
  const handleSendChatMessage = async (presetMessage?: string) => {
    const textToSend = presetMessage || currentChatInput;
    if (!textToSend.trim()) return;

    const userMsgId = Date.now().toString();
    const newUserMessage: ChatMessage = {
      id: userMsgId,
      role: 'user',
      content: textToSend
    };

    setChatMessages(prev => [...prev, newUserMessage]);
    if (!presetMessage) setCurrentChatInput('');
    setIsChatLoading(true);

    try {
      const chatHistory = chatMessages.map(m => ({
        role: m.role,
        content: m.content
      }));

      const response = await fetch(getApiUrl('coach-chat'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          message: textToSend,
          history: chatHistory
        })
      });

      const data = await response.json();
      if (data.status === 'success') {
        setChatMessages(prev => [...prev, {
          id: (Date.now() + 1).toString(),
          role: 'model',
          content: data.reply
        }]);
      } else {
        setChatMessages(prev => [...prev, {
          id: (Date.now() + 1).toString(),
          role: 'model',
          content: 'سمح ليا يا وحش، وقع مشكل في الاتصال بالشبكة التمرينية. حاول مرة أخرى!'
        }]);
      }
    } catch (err) {
      console.error(err);
      setChatMessages(prev => [...prev, {
        id: (Date.now() + 1).toString(),
        role: 'model',
        content: 'سمح ليا يا بطل، لقيت مشكلة فالسيرفر. عاود صيفط الميساج تفادياً للديكالاج!'
      }]);
    } finally {
      setIsChatLoading(false);
    }
  };

  // Calculated daily totals from track logs
  const dailyTotals = loggedMeals.reduce((acc, m) => {
    acc.calories += m.calories;
    acc.protein += m.protein;
    acc.carbs += m.carbs;
    acc.fat += m.fat;
    return acc;
  }, { calories: 0, protein: 0, carbs: 0, fat: 0 });

  return (
    <div className="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans selection:bg-teal-500 selection:text-slate-900" dir="rtl">
      
      {/* Dynamic Navigation Header */}
      <header className="border-b border-slate-850 bg-slate-900/40 backdrop-blur-md sticky top-0 z-30">
        <div className="max-w-7xl mx-auto px-4 py-3 flex flex-col sm:flex-row justify-between items-center gap-4">
          <div className="flex items-center gap-3">
            <div className="bg-gradient-to-tr from-teal-500 to-emerald-400 p-2.5 rounded-xl text-slate-950 shadow-lg shadow-teal-500/10">
              <Dumbbell className="h-6 w-6 animate-pulse" />
            </div>
            <div>
              <h1 className="text-xl font-black bg-gradient-to-r from-teal-400 via-emerald-400 to-amber-300 bg-clip-text text-transparent flex items-center gap-2">
                AI Gym Meal Planner
              </h1>
              <p className="text-xs text-slate-400">مخطط التغذية الرياضية ومكثف العضلات جيميناي 🇲🇦</p>
            </div>
          </div>

          {/* Quick Stats in Navigation Bar */}
          <div className="flex items-center gap-8 bg-slate-900/80 px-4 py-1.5 rounded-full border border-slate-800 text-sm hidden md:flex">
            <div className="flex items-center gap-2">
              <Flame className="h-4 w-4 text-amber-500" />
              <span className="text-slate-400">اليوم:</span>
              <span className="font-bold text-slate-200">{Math.round(dailyTotals.calories)} / {dailyCalorieTarget} Kcal</span>
            </div>
            <div className="h-4 w-[1px] bg-slate-800" />
            <div className="text-slate-400 flex items-center gap-1.5">
              <span>بروتين:</span>
              <span className="font-bold text-teal-400">{Math.round(dailyTotals.protein)}g/{targetMacros.protein}g</span>
            </div>
          </div>

          <div className="flex items-center gap-3">
            {isAuthLoading ? (
              <div className="text-xs text-slate-400 animate-pulse">جاري فحص السحابة...</div>
            ) : user ? (
              <div className="flex items-center gap-2 bg-slate-950 border border-slate-850 px-3 py-1 rounded-xl">
                {user.photoURL ? (
                  <img src={user.photoURL} alt={user.displayName || ""} className="w-5 h-5 rounded-full border border-teal-500/50" referrerPolicy="no-referrer" />
                ) : (
                  <div className="w-5 h-5 rounded-full bg-teal-500 text-slate-950 font-black text-[10px] flex items-center justify-center">
                    {(user.displayName || 'B').slice(0, 1)}
                  </div>
                )}
                <div className="text-right">
                  <p className="text-[10px] font-bold text-slate-200 truncate max-w-[80px]">{user.displayName || 'البطل'}</p>
                  <span className="text-[8px] text-teal-400 font-medium block">متزامن بالكامل ●</span>
                </div>
                <button 
                  onClick={handleSignOut} 
                  className="text-red-400 hover:text-red-300 text-[10px] font-extrabold pr-2 mr-1 border-r border-slate-800 cursor-pointer"
                >
                  خروج
                </button>
              </div>
            ) : (
              <button
                onClick={handleSignIn}
                className="bg-gradient-to-tr from-teal-500 via-emerald-400 to-emerald-500 hover:opacity-95 text-slate-950 text-xs font-black px-3.5 py-1.5 rounded-xl flex items-center gap-1.5 shadow-md shadow-teal-500/10 cursor-pointer"
              >
                <span>🔑 دخول Google</span>
              </button>
            )}
          </div>
        </div>
      </header>

      {/* Main Container Layout */}
      <main className="flex-1 max-w-7xl w-full mx-auto p-4 md:py-8 grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        {/* Navigation Sidebar Controls */}
        <div className="lg:col-span-1 flex flex-col gap-4">
          <div className="bg-slate-900 rounded-2xl p-4 border border-slate-800/80 flex flex-row lg:flex-col gap-2 overflow-x-auto overflow-y-hidden lg:overflow-visible sticky lg:top-24">
            
            <button
              onClick={() => setActiveTab('generator')}
              className={`flex items-center gap-3 w-full text-right px-4 py-3 rounded-xl transition duration-200 whitespace-nowrap ${
                activeTab === 'generator'
                  ? 'bg-gradient-to-l from-teal-500/20 to-teal-500/5 text-teal-400 border-r-4 border-teal-400 font-bold'
                  : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40'
              }`}
            >
              <Sparkles className="h-5 w-5 shrink-0" />
              <span>مولد الوجبات الذكي</span>
            </button>

            <button
              onClick={() => setActiveTab('calculator')}
              className={`flex items-center gap-3 w-full text-right px-4 py-3 rounded-xl transition duration-200 whitespace-nowrap ${
                activeTab === 'calculator'
                  ? 'bg-gradient-to-l from-teal-500/20 to-teal-500/5 text-teal-400 border-r-4 border-teal-400 font-bold'
                  : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40'
              }`}
            >
              <Scale className="h-5 w-5 shrink-0" />
              <span>حاسبة السعرات والماكروز</span>
            </button>

            <button
              onClick={() => setActiveTab('tracker')}
              className={`flex items-center gap-3 w-full text-right px-4 py-3 rounded-xl transition duration-200 whitespace-nowrap relative ${
                activeTab === 'tracker'
                  ? 'bg-gradient-to-l from-teal-500/20 to-teal-500/5 text-teal-400 border-r-4 border-teal-400 font-bold'
                  : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40'
              }`}
            >
              <Utensils className="h-5 w-5 shrink-0" />
              <span>سجل الوجبات اليومي</span>
              {loggedMeals.length > 0 && (
                <span className="absolute left-3 top-1/2 -translate-y-1/2 bg-teal-500 text-slate-950 text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center animate-bounce">
                  {loggedMeals.length}
                </span>
              )}
            </button>

            <button
              onClick={() => setActiveTab('coach')}
              className={`flex items-center gap-3 w-full text-right px-4 py-3 rounded-xl transition duration-200 whitespace-nowrap ${
                activeTab === 'coach'
                  ? 'bg-gradient-to-l from-teal-500/20 to-teal-500/5 text-teal-400 border-r-4 border-teal-400 font-bold'
                  : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40'
              }`}
            >
              <MessageSquare className="h-5 w-5 shrink-0" />
              <span>دردش مع الكوتش جيميناي</span>
            </button>
          </div>

          {/* Motivation Coach Widget */}
          <div className="bg-gradient-to-br from-slate-900 to-slate-950 rounded-2xl p-4 border border-slate-850 relative overflow-hidden hidden lg:block">
            <div className="absolute -right-8 -bottom-8 opacity-5 text-teal-400">
              <Dumbbell className="w-32 h-32" />
            </div>
            <Award className="h-5 w-5 text-amber-400 mb-2" />
            <h4 className="text-sm font-semibold text-slate-100 flex items-center gap-1.5">كلام الموتيفاسيون ديال الكوتش:</h4>
            <p className="text-xs text-slate-400 leading-relaxed mt-1 italic">
              "الحديد يا وحش ما كيعرفش الأعذار. الترينينغ بلا ماكلة بحال الخيمة بلا وتاد. تبرع بالماكروز، نعم العضلات ديالك بالبروتين وشوف النتيجة فعينيك!"
            </p>
          </div>
        </div>

        {/* Dynamic Content Columns */}
        <div className="lg:col-span-3 flex flex-col gap-6">

          {/* 1. CALCULATOR PANEL */}
          {activeTab === 'calculator' && (
            <div className="bg-slate-900 p-6 rounded-2xl border border-slate-800 shadow-xl transition-all">
              <div className="flex items-center gap-3 mb-6">
                <Scale className="h-6 w-6 text-teal-400" />
                <div>
                  <h2 className="text-xl font-bold text-slate-100">حاسبة الطاقة والماكروز الدقيقة</h2>
                  <p className="text-xs text-slate-400">احسب احتياجك اليومي TDEE وحصتك من البروتينات والكربوهيدرات والدهون للوصول للهدف</p>
                </div>
              </div>

              {/* Grid Form parameters */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {/* Inputs area */}
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm text-slate-300 mb-1.5 font-medium">الهدف الحالي (Fitness Goal)</label>
                    <div className="grid grid-cols-3 gap-2">
                      <button
                        onClick={() => setFitnessGoal('bulking')}
                        className={`py-2.5 px-3 rounded-xl border text-xs font-bold transition duration-150 ${
                          fitnessGoal === 'bulking'
                            ? 'bg-amber-500/15 text-amber-400 border-amber-400/50 shadow-md shadow-amber-500/5'
                            : 'border-slate-800 bg-slate-900 text-slate-400 hover:text-slate-200'
                        }`}
                      >
                        🔥 التضخيم (Bulking)
                      </button>
                      <button
                        onClick={() => setFitnessGoal('cutting')}
                        className={`py-2.5 px-3 rounded-xl border text-xs font-bold transition duration-150 ${
                          fitnessGoal === 'cutting'
                            ? 'bg-teal-500/15 text-teal-400 border-teal-400/50 shadow-md shadow-teal-500/5'
                            : 'border-slate-800 bg-slate-900 text-slate-400 hover:text-slate-200'
                        }`}
                      >
                        ⚡ التنشيف (Cutting)
                      </button>
                      <button
                        onClick={() => setFitnessGoal('maintenance')}
                        className={`py-2.5 px-3 rounded-xl border text-xs font-bold transition duration-150 ${
                          fitnessGoal === 'maintenance'
                            ? 'bg-emerald-550/15 text-emerald-400 border-emerald-400/50'
                            : 'border-slate-800 bg-slate-900 text-slate-400 hover:text-slate-200'
                        }`}
                      >
                        ⚖️ المحافظة (Maintenance)
                      </button>
                    </div>
                  </div>

                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <label htmlFor="weight-input" className="block text-sm text-slate-400 mb-1">الوزن الحالي (كجم)</label>
                      <div className="relative">
                        <input
                          id="weight-input"
                          type="number"
                          value={weight}
                          onChange={(e) => setWeight(Math.max(30, parseInt(e.target.value) || 0))}
                          className="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-center text-slate-200 font-bold focus:border-teal-500 focus:outline-none"
                        />
                        <span className="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-500">kg</span>
                      </div>
                    </div>

                    <div>
                      <label htmlFor="height-input" className="block text-sm text-slate-400 mb-1">الطول (سم)</label>
                      <div className="relative">
                        <input
                          id="height-input"
                          type="number"
                          value={height}
                          onChange={(e) => setHeight(Math.max(100, parseInt(e.target.value) || 0))}
                          className="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-center text-slate-200 font-bold focus:border-teal-500 focus:outline-none"
                        />
                        <span className="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-500">cm</span>
                      </div>
                    </div>
                  </div>

                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <label htmlFor="age-input" className="block text-sm text-slate-400 mb-1">السن بالسنوات</label>
                      <input
                        id="age-input"
                        type="number"
                        value={age}
                        onChange={(e) => setAge(Math.max(12, parseInt(e.target.value) || 0))}
                        className="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-center text-slate-200 font-bold focus:border-teal-500 focus:outline-none"
                      />
                    </div>

                    <div>
                      <label className="block text-sm text-slate-400 mb-1">الجنس</label>
                      <div className="grid grid-cols-2 gap-2">
                        <button
                          onClick={() => setGender('male')}
                          className={`py-2 px-3 rounded-xl border text-xs font-semibold ${
                            gender === 'male' ? 'bg-slate-800 text-teal-400 border-teal-500/50' : 'border-slate-800 hover:text-slate-200'
                          }`}
                        >
                          ذكر
                        </button>
                        <button
                          onClick={() => setGender('female')}
                          className={`py-2 px-3 rounded-xl border text-xs font-semibold ${
                            gender === 'female' ? 'bg-slate-800 text-teal-400 border-teal-500/50' : 'border-slate-800 hover:text-slate-200'
                          }`}
                        >
                          أنثى
                        </button>
                      </div>
                    </div>
                  </div>

                  <div>
                    <label htmlFor="activity-select" className="block text-sm text-slate-300 mb-1.5 font-medium">مستوى النشاط والمجهود الرياضي</label>
                    <select
                      id="activity-select"
                      value={activity}
                      onChange={(e) => setActivity(parseFloat(e.target.value))}
                      className="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-300 focus:border-teal-500 focus:outline-none text-sm"
                    >
                      <option value={1.2}>خامل (مكتب، بدون تمارين رياضية)</option>
                      <option value={1.375}>مجهود خفيف (تمارين مابين 1 إلى 3 أيام فالأسبوع)</option>
                      <option value={1.55}>مجهود متوسط (تمارين مكثفة 3 إلى 5 أيام فالأسبوع)</option>
                      <option value={1.725}>مجهود عالي وصعب (تمارين يومية صعبة ومجهود بدني)</option>
                    </select>
                  </div>
                </div>

                {/* Calculations visualization panel */}
                <div className="bg-slate-950 rounded-xl p-5 border border-slate-800 flex flex-col justify-between">
                  <div className="text-center pb-4 border-b border-slate-800/65">
                    <span className="text-xs text-slate-400 uppercase tracking-widest block mb-1">حساب السعرات المستهدفة لليوم</span>
                    <span className="text-4xl font-black bg-gradient-to-l from-teal-400 to-emerald-400 bg-clip-text text-transparent">{dailyCalorieTarget}</span>
                    <span className="text-sm font-bold text-slate-300 ml-1">Kcal / يوم</span>
                    <div className="mt-2 text-xs text-slate-400 font-semibold bg-slate-900 py-1.5 px-3 rounded-lg inline-block">
                      {fitnessGoal === 'bulking' && '📈 مستهدف الضخامة (زيادة السعرات بمعدل +500 Kcal)'}
                      {fitnessGoal === 'cutting' && '📉 مستهدف التنشيف والتقطاع (عجز السعرات بمعدل -500 Kcal)'}
                      {fitnessGoal === 'maintenance' && '⚖️ مستهدف المحافظة والثبات (التوازن الحراري)'}
                    </div>
                  </div>

                  {/* Micro Targets split bars */}
                  <div className="space-y-4 py-4 flex-1 flex flex-col justify-center">
                    <div>
                      <div className="flex justify-between text-xs mb-1">
                        <span className="text-slate-300 font-semibold">🍗 البروتينات (Protein)</span>
                        <span className="font-extrabold text-teal-400">{targetMacros.protein}g <span className="text-slate-500">({targetMacros.protein * 4} kcal)</span></span>
                      </div>
                      <div className="w-full h-2.5 bg-slate-900 rounded-full overflow-hidden">
                        <div className="bg-teal-400 h-full rounded-full" style={{ width: '30%' }} />
                      </div>
                    </div>

                    <div>
                      <div className="flex justify-between text-xs mb-1">
                        <span className="text-slate-300 font-semibold">🌾 الكاربوهايدرات (Carbs)</span>
                        <span className="font-extrabold text-amber-400">{targetMacros.carbs}g <span className="text-slate-500">({targetMacros.carbs * 4} kcal)</span></span>
                      </div>
                      <div className="w-full h-2.5 bg-slate-900 rounded-full overflow-hidden">
                        <div className="bg-amber-400 h-full rounded-full" style={{ width: '50%' }} />
                      </div>
                    </div>

                    <div>
                      <div className="flex justify-between text-xs mb-1">
                        <span className="text-slate-300 font-semibold">🥑 الدهون الصحية (Fats)</span>
                        <span className="font-extrabold text-rose-400">{targetMacros.fat}g <span className="text-slate-500">({targetMacros.fat * 9} kcal)</span></span>
                      </div>
                      <div className="w-full h-2.5 bg-slate-900 rounded-full overflow-hidden">
                        <div className="bg-rose-400 h-full rounded-full" style={{ width: '20%' }} />
                      </div>
                    </div>
                  </div>

                  <div className="space-y-3">
                    <button
                      onClick={() => {
                        // Adjust meal settings accordingly
                        setMealCalories(Math.round(dailyCalorieTarget / 4));
                        setActiveTab('generator');
                      }}
                      className="w-full bg-slate-900 hover:bg-slate-850 border border-slate-800 hover:border-teal-500/40 text-xs font-semibold py-2.5 rounded-xl transition duration-150 flex items-center justify-center gap-2 cursor-pointer"
                    >
                      <span>تطبيق كهدف مع وجبات المولد الذكي</span>
                      <ChevronRight className="h-3.5 w-3.5" />
                    </button>

                    {user && (
                      <button
                        onClick={handleSaveProfile}
                        disabled={isSyncing}
                        className="w-full bg-teal-500/15 hover:bg-teal-500/25 border border-teal-500/30 text-teal-400 text-xs font-bold py-2.5 rounded-xl transition duration-150 flex items-center justify-center gap-2 cursor-pointer"
                      >
                        <Save className="h-4 w-4" />
                        <span>{isSyncing ? 'جاري التزامن...' : 'مزامنة وحفظ المعطيات فالسحاب'}</span>
                      </button>
                    )}
                  </div>
                </div>

              </div>
            </div>
          )}

          {/* 2. MEAL GENERATOR PANEL */}
          {activeTab === 'generator' && (
            <div className="space-y-6">

              {/* Generator Settings controls */}
              <div className="bg-slate-900 p-6 rounded-2xl border border-slate-800 shadow-xl">
                <div className="flex items-center gap-3 mb-6">
                  <Sparkles className="h-6 w-6 text-teal-400" />
                  <div>
                    <h2 className="text-xl font-bold text-slate-100">مولد الوجبات المغربي الذكي بالذكاء الاصطناعي جيميناي</h2>
                    <p className="text-xs text-slate-400">حدد ميزانيتك الغذائية والمكونات المتاحة لتوليد طبخ مغربي عالي الجودة للرياضيين</p>
                  </div>
                </div>

                {/* Inner flex layout columns */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">

                  {/* Left ingredients selector lists (col spans 2) */}
                  <div className="md:col-span-2 space-y-4">
                    <div className="flex justify-between items-center pb-2 border-b border-slate-850">
                      <span className="text-sm font-bold text-slate-200">اختر المكونات المتاحة للتطبيق:</span>
                      <span className="text-xs text-slate-500 bg-slate-950 px-2 py-0.5 rounded-md font-bold">المختارة: {selectedIngredients.length}</span>
                    </div>

                    {/* Quick tabs filters of protein/carbs/fats */}
                    <div className="space-y-4">
                      
                      {/* Proteins */}
                      <div>
                        <span className="text-xs font-black text-teal-400 uppercase tracking-wide block mb-2">🍗 مصادر البروتين والمكملات (Proteins & Supplements)</span>
                        <div className="flex flex-wrap gap-2">
                          {DEFAULT_INGREDIENTS.filter(i => i.category === 'proteins').map(ing => {
                            const selected = selectedIngredients.includes(ing.id);
                            return (
                              <button
                                key={ing.id}
                                onClick={() => handleToggleIngredient(ing.id)}
                                className={`text-xs px-3 py-2 rounded-xl border transition duration-150 flex items-center gap-1.5 ${
                                  selected 
                                    ? 'bg-teal-500/10 text-teal-300 border-teal-500/40 shadow-sm' 
                                    : 'bg-slate-950 hover:bg-slate-850 text-slate-400 border-slate-850'
                                }`}
                              >
                                {selected ? <Check className="h-3.5 w-3.5 text-teal-400" /> : <div className="w-3 w-3 rounded bg-slate-800 border border-slate-700" />}
                                <span>{ing.name}</span>
                              </button>
                            );
                          })}
                        </div>
                      </div>

                      {/* Carbs */}
                      <div>
                        <span className="text-xs font-black text-amber-400 uppercase tracking-wide block mb-2">🌾 مصادر الكربوهيدرات والنشويات (Carbohydrates)</span>
                        <div className="flex flex-wrap gap-2">
                          {DEFAULT_INGREDIENTS.filter(i => i.category === 'carbs').map(ing => {
                            const selected = selectedIngredients.includes(ing.id);
                            return (
                              <button
                                key={ing.id}
                                onClick={() => handleToggleIngredient(ing.id)}
                                className={`text-xs px-3 py-2 rounded-xl border transition duration-150 flex items-center gap-1.5 ${
                                  selected 
                                    ? 'bg-amber-500/10 text-amber-300 border-amber-500/40 shadow-sm' 
                                    : 'bg-slate-950 hover:bg-slate-850 text-slate-400 border-slate-850'
                                }`}
                              >
                                {selected ? <Check className="h-3.5 w-3.5 text-amber-400" /> : <div className="w-3 w-3 rounded bg-slate-800 border border-slate-700" />}
                                <span>{ing.name}</span>
                              </button>
                            );
                          })}
                        </div>
                      </div>

                      {/* Fats & Superfoods */}
                      <div>
                        <span className="text-xs font-black text-rose-400 uppercase tracking-wide block mb-2">🥑 مصادر الدهون الصحية والمقويات المغربية (Healthy Fats & Superfoods)</span>
                        <div className="flex flex-wrap gap-2">
                          {DEFAULT_INGREDIENTS.filter(i => i.category === 'fats').map(ing => {
                            const selected = selectedIngredients.includes(ing.id);
                            return (
                              <button
                                key={ing.id}
                                onClick={() => handleToggleIngredient(ing.id)}
                                className={`text-xs px-3 py-2 rounded-xl border transition duration-150 flex items-center gap-1.5 ${
                                  selected 
                                    ? 'bg-rose-500/10 text-rose-300 border-rose-500/40 shadow-sm' 
                                    : 'bg-slate-950 hover:bg-slate-850 text-slate-400 border-slate-850'
                                }`}
                              >
                                {selected ? <Check className="h-3.5 w-3.5 text-rose-400" /> : <div className="w-3 w-3 rounded bg-slate-800 border border-slate-700" />}
                                <span>{ing.name}</span>
                              </button>
                            );
                          })}
                        </div>
                      </div>

                      {/* Custom User Added Ingredients */}
                      <div>
                        <span className="text-xs font-black text-amber-500 uppercase tracking-wide block mb-2">➕ مكوناتك الشخصية الإضافية ({customIngredients.length})</span>
                        <div className="flex flex-wrap gap-2">
                          {customIngredients.map(name => {
                            const selected = selectedIngredients.includes(name);
                            return (
                              <div
                                key={name}
                                className={`flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-xl border transition duration-150 ${
                                  selected 
                                    ? 'bg-teal-500/15 text-teal-300 border-teal-500/50' 
                                    : 'bg-slate-950 text-slate-400 border-slate-850'
                                }`}
                              >
                                <button
                                  onClick={() => handleToggleIngredient(name)}
                                  className="flex items-center gap-1"
                                >
                                  {selected ? <Check className="h-3.5 w-3.5 text-teal-400" /> : <div className="w-3 w-3 rounded bg-slate-800 border border-slate-700" />}
                                  <span>{name}</span>
                                </button>
                                <button
                                  onClick={() => handleRemoveCustomIngredient(name)}
                                  className="mr-1.5 text-red-500 hover:text-red-400 font-extrabold"
                                  title="حذف المكون"
                                >
                                  ×
                                </button>
                              </div>
                            );
                          })}
                          
                          {/* Quick Add Custom Ingredient form */}
                          <div className="flex items-center gap-1 w-full max-w-xs mt-1">
                            <input
                              type="text"
                              value={newCustomInput}
                              onChange={(e) => setNewCustomInput(e.target.value)}
                              placeholder="أضف مكون مغربي آخر..."
                              className="bg-slate-950 border border-slate-800 rounded-lg px-2.5 py-1.5 text-xs text-slate-300 focus:outline-none focus:border-teal-500 flex-1"
                              onKeyDown={(e) => { if (e.key === 'Enter') handleAddCustomIngredient(); }}
                            />
                            <button
                              onClick={handleAddCustomIngredient}
                              className="bg-slate-800 hover:bg-slate-750 text-teal-400 p-2 rounded-lg border border-slate-700 flex items-center justify-center transition"
                            >
                              <Plus className="h-3.5 w-3.5" />
                            </button>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>

                  {/* Right hand setup parameters (col spans 1) */}
                  <div className="bg-slate-950 rounded-xl p-4 border border-slate-800 space-y-4">
                    <div>
                      <span className="text-xs font-bold text-slate-400 block mb-1">نسبة السعرات المحددة للوجبة:</span>
                      <div className="flex justify-between items-center text-sm font-extrabold mb-1">
                        <span className="text-slate-200">{mealCalories} Kcal</span>
                        <span className="text-[10px] text-teal-400 bg-teal-500/10 px-2 py-0.5 rounded-full">تقدر تعدلها</span>
                      </div>
                      <input
                        type="range"
                        min="200"
                        max="1500"
                        step="25"
                        value={mealCalories}
                        onChange={(e) => setMealCalories(parseInt(e.target.value))}
                        className="w-full h-1.5 bg-slate-900 rounded-lg appearance-none cursor-pointer accent-teal-400"
                      />
                      <div className="grid grid-cols-4 gap-1 mt-2">
                        {[400, 600, 800, 1000].map(v => (
                          <button
                            key={v}
                            onClick={() => setMealCalories(v)}
                            className="text-[10px] bg-slate-900 py-1 rounded text-slate-400 hover:text-white"
                          >
                            {v}
                          </button>
                        ))}
                      </div>
                    </div>

                    <div>
                      <label htmlFor="meal-type-select" className="text-xs font-bold text-slate-400 block mb-1">صنف الوقت للوجبة:</label>
                      <select
                        id="meal-type-select"
                        value={mealType}
                        onChange={(e) => setMealType(e.target.value)}
                        className="w-full bg-slate-900 border border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-teal-500"
                      >
                        <option value="وجبة فطور (Breakfast)">وجبة الفطور (Ftour)</option>
                        <option value="وجبة الغداء (Lunch)">وجبة رئيسية - الغداء (Lunch)</option>
                        <option value="وجبة عشاء خفيفة (Dinner)">وجبة العشاء (Dinner)</option>
                        <option value="وجبة هيرماس قبل التدريب (Pre-workout)">حصن طاقة قبل التمرين (Pre-Workout)</option>
                        <option value="ما بعد التدريب (Post-workout)">وجبة استشفاء عضلية بعد التمرين</option>
                        <option value="مشروب / مخفوق بروتيني (Mega Shake)">مشروب وضخامة بروتينات بليند</option>
                      </select>
                    </div>

                    <div>
                      <label htmlFor="notes-area" className="text-xs font-bold text-slate-400 block mb-1">ملاحظات أو رغبات خاصة (اختياري):</label>
                      <textarea
                        id="notes-area"
                        value={customNotes}
                        onChange={(e) => setCustomNotes(e.target.value)}
                        placeholder="أريد وجبة جاهزة في 5 دقائق، بدون كلوتين، توابل حارة..."
                        className="w-full h-20 bg-slate-900 border border-slate-800 rounded-lg p-2 text-xs text-slate-200 focus:outline-none focus:border-teal-500"
                      />
                    </div>

                    <button
                      onClick={handleGenerateRecipe}
                      disabled={isGenerating}
                      className="w-full bg-gradient-to-tr from-teal-500 to-emerald-400 hover:from-teal-600 hover:to-emerald-500 text-slate-950 font-black py-3 px-4 rounded-xl transition duration-150 flex items-center justify-center gap-2 group cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <Sparkles className="h-5 w-5 animate-pulse group-hover:rotate-12 transition-transform" />
                      <span>{isGenerating ? 'جاري الطهي الذكي...' : 'توليد الوجبة بالذكاء الاصطناعي'}</span>
                    </button>
                  </div>

                </div>

                {generationError && (
                  <div className="mt-4 p-4 bg-red-950/40 border border-red-500/20 text-red-200 text-xs rounded-xl flex items-center gap-2.5">
                    <Info className="h-4 w-4 text-red-400 shrink-0" />
                    <span>{generationError}</span>
                  </div>
                )}
              </div>

              {/* Glowing Loader Section */}
              {isGenerating && (
                <div className="bg-slate-900 p-8 rounded-2xl border border-slate-800 flex flex-col items-center justify-center text-center gap-6 animate-pulse">
                  <div className="relative">
                    <div className="w-16 h-16 border-4 border-teal-500/10 border-t-teal-400 rounded-full animate-spin" />
                    <Sparkles className="h-6 w-6 text-amber-400 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2" />
                  </div>
                  <div className="max-w-md">
                    <h3 className="text-md font-bold text-slate-200">صبر شوية يا بطل! الكوتش جيميناي كايحسب الماكروز...</h3>
                    <p className="text-xs text-slate-400 mt-1 leading-relaxed italic">
                      "كنقادو ليك طبق مغربي صحي ملائم لهاد السعرات ديالك، بمقادير غرام دقيقة باش العضلة تستفيد مزيان وما تضيعش الترينينغ ديالك!"
                    </p>
                  </div>
                </div>
              )}

              {/* Generated Recipe Card details output */}
              {generatedRecipe && (
                <div className="bg-slate-900 rounded-2xl border border-slate-800 shadow-2xl overflow-hidden transition-all duration-300">
                  
                  {/* Glowing header banner inside Card */}
                  <div className="p-6 bg-gradient-to-tr from-slate-950 to-slate-900 border-b border-slate-850 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                      <div className="flex items-center gap-2">
                        <span className="text-[10px] bg-teal-500/15 text-teal-400 px-2.5 py-0.5 rounded-full font-bold">وصفة كوتش جيميناي مخصصة</span>
                        <span className="text-[10px] bg-slate-800 text-slate-300 px-2 py-0.5 rounded-full font-bold">⏱️ تحضير في {generatedRecipe.cookingTime}</span>
                      </div>
                      <h3 className="text-2xl font-black mt-1.5 text-slate-100 flex items-center gap-2">
                        <Apple className="h-6 w-6 text-teal-400 shrink-0" />
                        <span>{generatedRecipe.recipeName}</span>
                      </h3>
                    </div>

                    <button
                      onClick={() => handleLogMeal(generatedRecipe)}
                      className="bg-slate-800 hover:bg-slate-750 border border-slate-700 hover:border-teal-500/40 text-xs text-teal-400 font-extrabold py-2.5 px-4 rounded-xl transition duration-150 flex items-center gap-2 cursor-pointer"
                    >
                      <Save className="h-4 w-4" />
                      <span>حفظ الوجبة في سجل اليوم</span>
                    </button>
                  </div>

                  {/* Body elements grid layout */}
                  <div className="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">

                    {/* Macro totals inside side grid */}
                    <div className="md:col-span-1 bg-slate-950/80 p-4 rounded-xl border border-slate-850 h-fit space-y-4">
                      <div className="text-center font-bold">
                        <span className="text-xs text-slate-400 block mb-0.5">مجموع السعرات الحرارية</span>
                        <span className="text-3xl font-black text-amber-400">{generatedRecipe.calories} <span className="text-sm">Kcal</span></span>
                      </div>

                      {/* Small visual graphs */}
                      <div className="h-[1px] bg-slate-850" />

                      <div className="space-y-3">
                        <div className="flex justify-between text-xs">
                          <span className="text-slate-400 font-bold">🍗 پروتين:</span>
                          <span className="font-black text-teal-400">{generatedRecipe.protein}g</span>
                        </div>
                        <div className="flex justify-between text-xs">
                          <span className="text-slate-400 font-bold">🌾 كاربوهيدرات:</span>
                          <span className="font-black text-amber-400">{generatedRecipe.carbs}g</span>
                        </div>
                        <div className="flex justify-between text-xs">
                          <span className="text-slate-400 font-bold">🥑 دهون صحية:</span>
                          <span className="font-black text-rose-400">{generatedRecipe.fat}g</span>
                        </div>
                      </div>

                      <div className="h-[1px] bg-slate-850" />

                      {/* Coach advice */}
                      <div>
                        <span className="text-xs font-black text-teal-400 uppercase tracking-widest block mb-1">💡 الفائدة العضلية و الرياضية:</span>
                        <p className="text-xs text-slate-300 leading-relaxed font-semibold">
                          {generatedRecipe.fitnessBenefit}
                        </p>
                      </div>
                    </div>

                    {/* Preparation of recipe steps (middle & right column) */}
                    <div className="md:col-span-2 space-y-6">
                      
                      {/* Ingredients checkboxes */}
                      <div>
                        <h4 className="text-sm font-bold text-slate-200 border-r-4 border-amber-400 pr-2 mb-3">المقادير والجرامات والوحدات الدقيقة:</h4>
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                          {generatedRecipe.ingredients.map((ing, idx) => (
                            <div 
                              key={idx}
                              className="bg-slate-950 px-3.5 py-2.5 rounded-xl border border-slate-850 flex items-center justify-between text-xs select-none"
                            >
                              <span className="text-slate-200 font-medium">{ing.name}</span>
                              <span className="font-black text-teal-300 bg-teal-500/10 px-2.5 py-1 rounded-lg border border-teal-500/10">{ing.amount}</span>
                            </div>
                          ))}
                        </div>
                      </div>

                      {/* Step-by-step preparations */}
                      <div>
                        <h4 className="text-sm font-bold text-slate-200 border-r-4 border-teal-400 pr-2 mb-3">كيفية طبخ وتحضير الوجبة:</h4>
                        <div className="space-y-3">
                          {generatedRecipe.instructions.map((step, idx) => (
                            <div 
                              key={idx}
                              className="bg-slate-950 p-4 rounded-xl border border-slate-850 flex gap-3 text-xs leading-relaxed"
                            >
                              <div className="bg-slate-900 border border-slate-700 w-6 h-6 rounded-full shrink-0 flex items-center justify-center font-bold text-slate-300">
                                {idx + 1}
                              </div>
                              <span className="text-slate-300 font-medium">{step}</span>
                            </div>
                          ))}
                        </div>
                      </div>

                    </div>

                  </div>

                </div>
              )}

            </div>
          )}

          {/* 3. DAILY TRACKER PANEL */}
          {activeTab === 'tracker' && (
            <div className="space-y-6">
              
              {/* Daily progress graphs block */}
              <div className="bg-slate-900 p-6 rounded-2xl border border-slate-800 shadow-xl">
                <div className="flex justify-between items-center mb-6">
                  <div className="flex items-center gap-3">
                    <Utensils className="h-6 w-6 text-teal-400" />
                    <div>
                      <h2 className="text-xl font-bold text-slate-100">سجل التغذية والتتبع لليوم</h2>
                      <p className="text-xs text-slate-400 font-semibold text-teal-400">تابع نسبة المجهود المستهلك طوال اليوم</p>
                    </div>
                  </div>

                  <button
                    onClick={handleClearAllLogs}
                    className="text-xs text-red-400 hover:text-red-300 border border-slate-800 hover:border-red-500/25 px-3 py-1.5 rounded-xl transition cursor-pointer"
                  >
                    تفريغ السجل
                  </button>
                </div>

                {/* Dashboard statistics rings grid */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                  
                  {/* Big Calories Progress Indicator */}
                  <div className="md:col-span-1 bg-slate-950 p-4 rounded-xl border border-slate-800/80 flex flex-col items-center justify-center text-center">
                    <span className="text-xs text-slate-400 font-medium mb-1">الرصيد الكلي للسعرات</span>
                    <span className="text-3xl font-black text-slate-100">{Math.round(dailyTotals.calories)}</span>
                    <span className="text-[10px] text-slate-500 font-bold uppercase mt-0.5">من أصل {dailyCalorieTarget} Kcal</span>
                    
                    {/* Linear visual loading gauge */}
                    <div className="w-full h-2 bg-slate-900 rounded-full overflow-hidden mt-4">
                      <div 
                        className="bg-gradient-to-r from-teal-500 to-emerald-400 h-full rounded-full transition-all duration-300" 
                        style={{ width: `${Math.min(100, (dailyTotals.calories / dailyCalorieTarget) * 100)}%` }} 
                      />
                    </div>
                    
                    <span className="text-[11px] font-bold text-teal-400 mt-2">
                      {Math.round((dailyTotals.calories / dailyCalorieTarget) * 100)}% من مجموع هدفك اليومي
                    </span>
                  </div>

                  {/* Micro macronutrient splits dashboard */}
                  <div className="md:col-span-3 bg-slate-950 p-4 rounded-xl border border-slate-800/80 grid grid-cols-3 gap-4">
                    
                    {/* Protein tracker */}
                    <div className="flex flex-col justify-between p-2 rounded-lg bg-slate-900/40 border border-slate-850">
                      <div>
                        <span className="text-xs text-slate-400 font-bold block">🍗 بروتين مسجل</span>
                        <span className="text-xl font-extrabold text-teal-400 mt-1 block">{Math.round(dailyTotals.protein)}g</span>
                      </div>
                      <div className="mt-2 pt-2 border-t border-slate-850/50">
                        <span className="text-[10px] text-slate-500 block">المستهدف: {targetMacros.protein}g</span>
                        <div className="w-full h-1.5 bg-slate-950 rounded-full overflow-hidden mt-1">
                          <div 
                            className="bg-teal-400 h-full transition-all duration-300" 
                            style={{ width: `${Math.min(100, (dailyTotals.protein / targetMacros.protein) * 100)}%` }} 
                          />
                        </div>
                      </div>
                    </div>

                    {/* Carbs tracker */}
                    <div className="flex flex-col justify-between p-2 rounded-lg bg-slate-900/40 border border-slate-850">
                      <div>
                        <span className="text-xs text-slate-400 font-bold block">🌾 كاربوهيدرات مسجل</span>
                        <span className="text-xl font-extrabold text-amber-400 mt-1 block">{Math.round(dailyTotals.carbs)}g</span>
                      </div>
                      <div className="mt-2 pt-2 border-t border-slate-850/50">
                        <span className="text-[10px] text-slate-500 block">المستهدف: {targetMacros.carbs}g</span>
                        <div className="w-full h-1.5 bg-slate-950 rounded-full overflow-hidden mt-1">
                          <div 
                            className="bg-amber-400 h-full transition-all duration-300" 
                            style={{ width: `${Math.min(100, (dailyTotals.carbs / targetMacros.carbs) * 100)}%` }} 
                          />
                        </div>
                      </div>
                    </div>

                    {/* Fat tracker */}
                    <div className="flex flex-col justify-between p-2 rounded-lg bg-slate-900/40 border border-slate-850">
                      <div>
                        <span className="text-xs text-slate-400 font-bold block">🥑 دهون صحية مسجلة</span>
                        <span className="text-xl font-extrabold text-rose-400 mt-1 block">{Math.round(dailyTotals.fat)}g</span>
                      </div>
                      <div className="mt-2 pt-2 border-t border-slate-850/50">
                        <span className="text-[10px] text-slate-500 block">المستهدف: {targetMacros.fat}g</span>
                        <div className="w-full h-1.5 bg-slate-950 rounded-full overflow-hidden mt-1">
                          <div 
                            className="bg-rose-400 h-full transition-all duration-300" 
                            style={{ width: `${Math.min(100, (dailyTotals.fat / targetMacros.fat) * 100)}%` }} 
                          />
                        </div>
                      </div>
                    </div>

                  </div>

                </div>
              </div>

              {/* Logged meals history table */}
              <div className="bg-slate-900 p-6 rounded-2xl border border-slate-800 shadow-xl">
                <h3 className="text-md font-extrabold text-slate-100 mb-4 flex items-center gap-2">
                  <Activity className="h-4 w-4 text-teal-400" />
                  <span>تاريخ الوجبات المستهلكة اليوم ({loggedMeals.length})</span>
                </h3>

                {loggedMeals.length === 0 ? (
                  <div className="p-12 border border-dashed border-slate-800 text-center rounded-xl">
                    <Utensils className="h-10 w-10 text-slate-600 mx-auto mb-3" />
                    <p className="text-slate-400 text-sm">لم تقم بتسجيل أي وجبة لليوم بعد.</p>
                    <p className="text-slate-500 text-xs mt-1">اذهب إلى علامة التبويب "مولد الوجبات الذكي" لتخطيط وجبة وحفظها هنا.</p>
                  </div>
                ) : (
                  <div className="space-y-3">
                    {loggedMeals.map(meal => (
                      <div 
                        key={meal.id}
                        className="p-4 bg-slate-950 rounded-xl border border-slate-850 flex items-center justify-between text-xs"
                      >
                        <div className="space-y-1">
                          <div className="flex items-center gap-2">
                            <span className="font-extrabold text-slate-200 text-sm">{meal.name}</span>
                            <span className="text-[10px] text-slate-500 bg-slate-900 px-2 py-0.5 rounded-md">{meal.timestamp}</span>
                          </div>
                          <div className="flex gap-4 text-slate-400">
                            <span>🔥 {meal.calories} kcal</span>
                            <span>• بروتين: {meal.protein}g</span>
                            <span>• كارب: {meal.carbs}g</span>
                            <span>• دهون: {meal.fat}g</span>
                          </div>
                        </div>

                        <button
                          onClick={() => handleDeleteLoggedMeal(meal.id)}
                          className="bg-slate-900 hover:bg-slate-850 hover:text-red-400 p-2.5 rounded-lg border border-slate-800 transition text-slate-400 cursor-pointer"
                          title="حذف الوجبة"
                        >
                          <Trash2 className="h-4 w-4" />
                        </button>
                      </div>
                    ))}
                  </div>
                )}
              </div>

            </div>
          )}

          {/* 4. ASK COACH CHATBOT PANEL */}
          {activeTab === 'coach' && (
            <div className="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl h-[640px] flex flex-col overflow-hidden">
              
              {/* Header chatbot info */}
              <div className="p-4 bg-gradient-to-tr from-slate-950 to-slate-900 border-b border-slate-850 flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-full bg-gradient-to-tr from-teal-500 to-emerald-400 p-0.5 relative shrink-0">
                    <div className="w-full h-full rounded-full bg-slate-900 flex items-center justify-center font-extrabold text-teal-400 text-sm">
                      🏋️‍♂️
                    </div>
                    <span className="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border border-slate-900 rounded-full" />
                  </div>
                  <div>
                    <h3 className="font-extrabold text-slate-100 text-sm">الكوتش جيميناي الرياضي</h3>
                    <p className="text-[10px] text-teal-400 flex items-center gap-0.5 font-bold">● مستشار تغذية وكمال أجسام مغربي</p>
                  </div>
                </div>

                <button
                  onClick={() => {
                    setChatMessages([
                      {
                        id: 'welcome',
                        role: 'model',
                        content: 'مرحبا بيك من جديد يا بطل! أنا الكوتش جيميناي مستعد نجاوب على أي تساؤل رياضي بالدارجة المغربية. قوليا شكون بغينا نطحنو اليوم؟ 💪🏋️‍♂️'
                      }
                    ]);
                  }}
                  className="bg-slate-950 hover:bg-slate-900 text-slate-400 hover:text-white p-2 text-xs rounded-xl border border-slate-850 flex items-center gap-1 cursor-pointer"
                  title="إعادة بدء الدردشة"
                >
                  <RotateCcw className="h-3.5 w-3.5" />
                  <span className="hidden sm:inline">تصفية</span>
                </button>
              </div>

              {/* Message history layout */}
              <div className="flex-1 p-4 overflow-y-auto space-y-4 bg-slate-950/40">
                {chatMessages.map(msg => {
                  const isUser = msg.role === 'user';
                  return (
                    <div 
                      key={msg.id}
                      className={`flex gap-3 max-w-[85%] ${isUser ? 'mr-auto flex-row-reverse' : 'ml-auto'}`}
                    >
                      <div className={`w-8 h-8 rounded-full shrink-0 flex items-center justify-center font-bold text-xs ${
                        isUser ? 'bg-slate-800 text-slate-300' : 'bg-teal-500/15 text-teal-400 border border-teal-500/10'
                      }`}>
                        {isUser ? '👤' : '🤖'}
                      </div>
                      
                      <div className={`p-3.5 rounded-2xl text-xs leading-relaxed font-semibold whitespace-pre-wrap ${
                        isUser 
                          ? 'bg-gradient-to-tr from-slate-900 to-slate-850 text-slate-200 border border-slate-800 rounded-tr-none' 
                          : 'bg-slate-900/90 text-slate-300 border border-slate-850/80 rounded-tl-none'
                      }`}>
                        {msg.content}
                      </div>
                    </div>
                  );
                })}

                {isChatLoading && (
                  <div className="flex gap-3 max-w-[85%] ml-auto">
                    <div className="w-8 h-8 rounded-full bg-teal-500/15 text-teal-400 flex items-center justify-center font-bold text-xs animate-spin">
                      ⏳
                    </div>
                    <div className="p-3 bg-slate-900 rounded-2xl rounded-tl-none border border-slate-850 flex items-center gap-1.5 text-xs text-slate-400 font-bold italic">
                      <span>الكوتش جيميناي كايوجد لك الجواب...</span>
                    </div>
                  </div>
                )}
                
                <div ref={chatEndRef} />
              </div>

              {/* Popular quick-request prompts */}
              <div className="p-3 bg-slate-950 border-t border-slate-850/60 overflow-x-auto whitespace-nowrap flex gap-2">
                {[
                  "عطيني برنامج تدريب 4 حصص فالسلسلة لتضخيم الدراع والجسم؟",
                  "شنو ناكل قبل الحصة بنص ساعة باش نملك طاقة زوينة؟",
                  "أنا مقطوع من الماكلة، كيفاش نزيد الوزن بسرعة وصحة؟",
                  "برنامج كارديو زوين لتنشيف الكرش وإبراز السيكسباك؟"
                ].map((txt, idx) => (
                  <button
                    key={idx}
                    onClick={() => handleSendChatMessage(txt)}
                    className="text-[10.5px] bg-slate-900 border border-slate-850 hover:border-teal-500/35 text-slate-400 hover:text-teal-400 px-3 py-2 rounded-xl transition cursor-pointer shrink-0"
                  >
                    {txt}
                  </button>
                ))}
              </div>

              {/* Chat Input message block */}
              <div className="p-4 bg-slate-900/90 border-t border-slate-850 flex gap-2">
                <input
                  type="text"
                  value={currentChatInput}
                  onChange={(e) => setCurrentChatInput(e.target.value)}
                  placeholder="سول الكوتش: كيفاش نزيد الوزن، عضلات الباي، المكملات..."
                  className="bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-slate-200 focus:outline-none focus:border-teal-500 flex-1"
                  onKeyDown={(e) => { if (e.key === 'Enter') handleSendChatMessage(); }}
                  disabled={isChatLoading}
                />
                
                <button
                  onClick={() => handleSendChatMessage()}
                  disabled={isChatLoading}
                  className="bg-gradient-to-tr from-teal-500 to-emerald-400 text-slate-950 p-3 rounded-xl transition hover:opacity-90 disabled:opacity-50 flex items-center justify-center cursor-pointer shrink-0"
                >
                  <Send className="h-5 w-5 transform rotate-180" />
                </button>
              </div>

            </div>
          )}

        </div>

      </main>

      {/* Aesthetic Footer Info */}
      <footer className="border-t border-slate-900 bg-slate-950/20 py-6 mt-12 text-center text-xs text-slate-500">
        <div className="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row justify-between items-center gap-4 font-semibold">
          <p>أول تطبيق مغربي مدعوم بالذكاء الاصطناعي لتخطيط غداء الرياضيين 🇲🇦🏋️‍♂️</p>
          <div className="flex gap-4">
            <span className="text-slate-400">كوتش متكامل بنسخة Gemini 3.5</span>
            <span>•</span>
            <span className="text-slate-400">بناء و طاقة بلا حدود</span>
          </div>
        </div>
      </footer>
    </div>
  );
}
