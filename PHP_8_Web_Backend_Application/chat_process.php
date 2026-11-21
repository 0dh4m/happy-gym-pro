<?php
/**
 * Copyright (C) 2026 Adham Cherkaoui Eddeqaqi (0dh4m). All rights reserved.
 * Proprietary and confidential. Unauthorized copying or distribution is strictly prohibited.
 * Author: Adham Cherkaoui Eddeqaqi (0dh4m) <adamcherkaoui99@gmail.com>
 * GitHub: https://github.com/0dh4m
 */

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['question'])) {
    $question = htmlspecialchars($_POST['question']);
    $geminiApiKey = "YOUR_GEMINI_API_KEY_HERE";

    $prompt = "أنت مدرب لياقة بدنية وخبير تغذية كمال أجسام ذكي ومحترف. أجب باختصار واحترافية على هذا السؤال الرياضي: " . $question;

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent?key=" . $geminiApiKey;
    $data = array(
        "contents" => array(
            array("parts" => array(array("text" => $prompt)))
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
    curl_close($ch);

    $aiResult = json_decode($response, true);
    if(isset($aiResult['candidates'][0]['content']['parts'][0]['text'])){
        echo nl2br(htmlspecialchars($aiResult['candidates'][0]['content']['parts'][0]['text']));
    } else {
        echo "عذراً، لم أستطع الإجابة على هذا السؤال حالياً.";
    }
}
?>