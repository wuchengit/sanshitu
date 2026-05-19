<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error'=>'Method not allowed']); exit; }

$body = file_get_contents('php://input');
$data = json_decode($body, true);
if (!$data) { http_response_code(400); echo json_encode(['error'=>'Invalid JSON']); exit; }

$model = $data['model'] ?? 'gpt-image-2';
$prompt = $data['prompt'] ?? '';
$aspectRatio = $data['aspectRatio'] ?? '9:16';
$images = $data['images'] ?? [];
$resolution = $data['resolution'] ?? null;
$customKey = $data['_key'] ?? '';

// Use custom key if provided, otherwise server key
$serverKey = 'sk-9dbaa91bdf764459812856da0ae5a7e4';
$apiKey = $customKey ?: $serverKey;

$reqBody = ['model' => $model, 'prompt' => $prompt, 'aspectRatio' => $aspectRatio, 'replyType' => 'json'];
if ($images) $reqBody['images'] = $images;
if ($resolution) $reqBody['resolution'] = $resolution;

$ch = curl_init('https://grsai.dakka.com.cn/v1/api/generate');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($reqBody),
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 120
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

http_response_code($httpCode);
echo $response;
