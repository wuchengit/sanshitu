<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

$body = file_get_contents('php://input');
$data = json_decode($body, true);
$urls = $data['urls'] ?? [];

if (empty($urls)) { http_response_code(400); echo json_encode(['error'=>'No URLs']); exit; }

$tmpDir = sys_get_temp_dir() . '/zip_' . uniqid();
mkdir($tmpDir);

$files = [];
$mh = curl_multi_init();
$handles = [];

foreach ($urls as $i => $url) {
    $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'png';
    $name = 'sanshitu_' . ($i + 1) . '.' . $ext;
    $path = $tmpDir . '/' . $name;
    $fp = fopen($path, 'w');
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_FILE => $fp,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    curl_multi_add_handle($mh, $ch);
    $handles[] = [$ch, $fp, $path];
}

do { curl_multi_exec($mh, $running); curl_multi_select($mh); } while ($running);
foreach ($handles as [$ch, $fp, $path]) {
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
    fclose($fp);
    if (filesize($path) > 0) $files[] = $path;
}
curl_multi_close($mh);

if (empty($files)) {
    array_map('unlink', glob($tmpDir . '/*'));
    rmdir($tmpDir);
    http_response_code(500);
    echo json_encode(['error'=>'All downloads failed']);
    exit;
}

$zipPath = $tmpDir . '/sanshitu_batch.zip';
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
    http_response_code(500);
    exit;
}
foreach ($files as $file) {
    $zip->addFile($file, basename($file));
}
$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="sanshitu_batch.zip"');
header('Content-Length: ' . filesize($zipPath));
readfile($zipPath);

array_map('unlink', $files);
unlink($zipPath);
rmdir($tmpDir);
