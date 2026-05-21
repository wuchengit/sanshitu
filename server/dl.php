<?php
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); exit; }

$url = $_GET['url'] ?? '';
if (!$url) { http_response_code(400); exit; }

$ch = curl_init($url);
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30, CURLOPT_FOLLOWLOCATION => true]);
$data = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if ($httpCode !== 200 || !$data) { http_response_code(404); exit; }

$ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'png';
header('Content-Type: ' . ($contentType ?: 'image/png'));
header('Content-Disposition: attachment; filename="sanshitu.' . $ext . '"');
header('Content-Length: ' . strlen($data));
echo $data;
