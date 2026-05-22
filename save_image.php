<?php
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$url = $input['url'] ?? '';

if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(['error' => '无效的 URL']);
    exit;
}

// 用户数据独立存储，脱离项目目录
$dataRoot = '/var/www/sanshitu-data';
$baseDir = $dataRoot . '/images';
$thumbDir = $baseDir . '/thumbs';
if (!is_dir($baseDir)) mkdir($baseDir, 0755, true);
if (!is_dir($thumbDir)) mkdir($thumbDir, 0755, true);

$filename = date('Ymd') . '_' . substr(md5($url . microtime(true)), 0, 12);
$filepath = $baseDir . '/' . $filename . '.png';
$thumbName = $filename . '.jpg';
$thumbPath = $thumbDir . '/' . $thumbName;

// 用 curl 下载（走代理）
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_USERAGENT => 'Mozilla/5.0'
]);
$imageData = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($imageData === false || $httpCode !== 200) {
    http_response_code(502);
    echo json_encode(['error' => "下载失败 (HTTP $httpCode)"]);
    exit;
}

// 保存原图
file_put_contents($filepath, $imageData);

// 生成缩略图
$origImage = @imagecreatefromstring($imageData);
if ($origImage) {
    $ow = imagesx($origImage);
    $oh = imagesy($origImage);
    
    $tw = min($ow, 200);
    $th = intval($oh * ($tw / $ow));
    
    $thumbImage = imagecreatetruecolor($tw, $th);
    imagecopyresampled($thumbImage, $origImage, 0, 0, 0, 0, $tw, $th, $ow, $oh);
    imagejpeg($thumbImage, $thumbPath, 75);
    imagedestroy($origImage);
    imagedestroy($thumbImage);
} else {
    copy($filepath, $thumbPath);
}

$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];

echo json_encode([
    'original' => "$scheme://$host/data/images/$filename.png",
    'thumb' => "$scheme://$host/data/images/thumbs/$thumbName",
    'size' => filesize($filepath)
]);
