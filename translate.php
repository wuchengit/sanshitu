<?php
// 翻译代理 - 通过服务器代理调用 DeepSeek API
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$input = json_decode(file_get_contents('php://input'), true);
$prompt = $input['prompt'] ?? '';
$key = $input['key'] ?? 'sk-c583e696cd0e4caa9055a7e6f62b4390';

if (!$prompt || strlen($prompt) < 3) {
    echo json_encode(['error' => '提示词太短']);
    exit;
}

// 走服务器代理
$ctx = stream_context_create([
    'http' => [
        'proxy' => 'tcp://127.0.0.1:7890',
        'request_fulluri' => true,
        'header' => "Content-Type: application/json\r\nAuthorization: Bearer $key\r\n",
        'method' => 'POST',
        'content' => json_encode([
            'model' => 'deepseek-v4-flash',
            'messages' => [
                ['role' => 'system', 'content' => 'Translate the following English prompt to Chinese. Output only the translation, no explanation, no extra text.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 2000
        ]),
        'timeout' => 30
    ]
]);

$result = @file_get_contents('https://api.deepseek.com/chat/completions', false, $ctx);

if (!$result) {
    echo json_encode(['error' => '翻译请求失败']);
    exit;
}

$data = json_decode($result, true);
$translation = $data['choices'][0]['message']['content'] ?? '';

echo json_encode(['translation' => $translation]);
