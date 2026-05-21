<?php
// 日志记录接口
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);

$log = [
    'time' => date('Y-m-d H:i:s'),
    'model' => $input['model'] ?? '',
    'ratio' => $input['ratio'] ?? '',
    'prompt' => mb_substr($input['prompt'] ?? '', 0, 100),
    'status' => $input['status'] ?? '',
    'error' => mb_substr($input['error'] ?? '', 0, 200),
    'cost' => $input['cost'] ?? '',
    'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
];

$line = json_encode($log, JSON_UNESCAPED_UNICODE) . "\n";
$logFile = __DIR__ . '/logs/generate.log';
$dir = dirname($logFile);

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// 追加写入，自动换行
file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);

// 只保留最近1000条
$lines = file($logFile);
if (count($lines) > 1000) {
    file_put_contents($logFile, implode('', array_slice($lines, -1000)));
}

echo json_encode(['ok' => true]);
