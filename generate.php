<?php
set_time_limit(600);
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
    CURLOPT_TIMEOUT => 600
]);

$startTime = microtime(true);
$response = curl_exec($ch);
$duration = round((microtime(true) - $startTime) * 1000);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// 失败时写日志
$json = json_decode($response, true);
$hasImage = !empty($json['results'][0]['url']);
$isError = $curlError || $httpCode >= 400 || !$hasImage;
// 临时：记录所有响应，排查格式问题
error_log(json_encode(['time'=>date('Y-m-d H:i:s'),'model'=>$model,'prompt'=>mb_substr($prompt,0,50),'status'=>$curlError?'curl_error':($httpCode>=400?'http_error':($hasImage?'ok':'no_image')),'error'=>$curlError?:($json['error']??$json['message']??''),'cost'=>$duration.'ms','http_code'=>$httpCode,'has_keys'=>json_encode(array_keys($json??[]))],JSON_UNESCAPED_UNICODE).PHP_EOL, 3, '/var/www/sanshitu-data/logs/generate.log');
if ($isError) {
  $logDir = '/var/www/sanshitu-data/logs';
  if (!is_dir($logDir)) mkdir($logDir, 0755, true);
  $logEntry = json_encode([
    'time' => date('Y-m-d H:i:s'),
    'model' => $model,
    'prompt' => mb_substr($prompt, 0, 100),
    'status' => 'failed',
    'error' => $curlError ?: ($json['error'] ?? $json['message'] ?? "HTTP $httpCode"),
    'cost' => $duration . 'ms'
  ], JSON_UNESCAPED_UNICODE) . "\n";
  error_log($logEntry, 3, $logDir . '/generate.log');
}

http_response_code($httpCode);
echo $response;
