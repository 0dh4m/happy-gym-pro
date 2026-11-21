<?php
/**
 * Copyright (C) 2026 Adham Cherkaoui Eddeqaqi (0dh4m). All rights reserved.
 * Proprietary and confidential. Unauthorized copying or distribution is strictly prohibited.
 * Author: Adham Cherkaoui Eddeqaqi (0dh4m) <0dh4mm@gmail.com>
 * GitHub: https://github.com/0dh4m
 */

session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$geminiApiKey = "YOUR_GEMINI_API_KEY_HERE";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate_meal'])) {
    
    $user_id = $_SESSION['user_id'];
    $target_calories_meal = isset($_POST['target_calories']) ? intval($_POST['target_calories']) : 700;
    
    $prompt_ingredients = "";
    $image_parts = null;

    if (isset($_FILES['food_image']) && $_FILES['food_image']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['food_image']['tmp_name'];
        $fileType = mime_content_type($fileTmpPath);
        
        if (strpos($fileType, 'image/') === 0) {
            $imageData = base64_encode(file_get_contents($fileTmpPath));
            
            $image_parts = array(
                "inline_data" => array(
                    "mime_type" => $fileType,
                    "data" => $imageData
                )
            );
        }
    }

    if (!$image_parts) {
        if (!isset($_POST['ingredients']) || empty($_POST['ingredients'])) {
            $_SESSION['error_msg'] = "المرجو اختيار مكون واحد على الأقل أو رفع صورة للمكونات.";
            header("Location: index.php");
            exit();
        }
        $clean_ingredients = array_map('htmlspecialchars', $_POST['ingredients']);
        $prompt_ingredients = "المكونات المتاحة هي: [" . implode('، ', $clean_ingredients) . "].";
    } else {
        $prompt_ingredients = "قم بتحليل هذه الصورة بعناية، واستخرج منها جميع المكونات الغذائية والخضروات الظاهرة فيها.";
    }

    $prompt = "أنت خبير تغذية كمال أجسام احترافي. مطلوب منك تصميم وجبة واحدة متكاملة تحتوي بدقة على حوالي **$target_calories_meal سعرة حرارية**.
    $prompt_ingredients
    
    يجب أن يتضمن ردك الأقسام التالية بوضوح تام:
    1. السطر الأول: اسم واقعي، منطقي ووصفي للطبق فقط (مثل: طبق الدجاج بالأرز واللوبيا) بدون أسماء تجارية أو سينمائية مبالغ فيها وبدون رموز زائدة.
    2. المقادير والكميات: الكميات بالغرام أو الوحدات لكل مكون تم استخدامه ليصل مجموع السعرات إلى $target_calories_meal Kcal تقريباً.
    3. طريقة التحضير: خطوات التحضير بالتفصيل الممل والمنسق.
    4. القيم الغذائية: إجمالي السعرات، البروتين، الكربوهيدرات، والدهون.";

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent?key=" . $geminiApiKey;
    
    $contents_parts = array(array("text" => $prompt));
    if ($image_parts) {
        $contents_parts[] = $image_parts; // إضافة الصورة للطلب إذا كانت موجودة
    }

    $data = array(
        "contents" => array(
            array("parts" => $contents_parts)
        )
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    
    if(!curl_errno($ch)) {
        $aiResult = json_decode($response, true);
        
        if(isset($aiResult['candidates'][0]['content']['parts'][0]['text'])){
            $ai_text = $aiResult['candidates'][0]['content']['parts'][0]['text'];
            
            $lines = explode("\n", trim($ai_text));
            $extracted_meal_name = !empty($lines[0]) ? trim(str_replace(['*', '#', '1', '.'], '', $lines[0])) : "طبق الوجبة الصحية";
            if(mb_strlen($extracted_meal_name) > 60) {
                $extracted_meal_name = mb_substr($extracted_meal_name, 0, 57) . '...';
            }

            $_SESSION['ai_result'] = $ai_text;
            $_SESSION['generated_meal_name'] = $extracted_meal_name;
            $_SESSION['generated_meal_calories'] = $target_calories_meal;
            
            $_SESSION['success_msg'] = "تم توليد الوجبة بنجاح!";
        } else {
            $_SESSION['error_msg'] = "خطأ في تحليل البيانات من جيميناي API.";
        }
    }
    curl_close($ch);
    $conn->close();
    
    header("Location: index.php");
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>