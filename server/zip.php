<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

$raw = $_POST['urls'] ?? file_get_contents('php://input');
$data = json_decode($raw, true);
$urls = isset($data['urls']) ? $data['urls'] : (array_keys($data)===range(0,count($data)-1) ? $data : []);

if (empty($urls)) { http_response_code(400); echo json_encode(['error'=>'No URLs']); exit; }

// Work in web-accessible dl directory
$dlDir = __DIR__ . '/dl';
if (!is_dir($dlDir)) mkdir($dlDir, 0755);
$batchId = uniqid('batch_');
$tmpDir = $dlDir . '/' . $batchId;
mkdir($tmpDir);

$mh = curl_multi_init();
$handles = [];
foreach ($urls as $i => $url) {
    $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'png';
    $name = 'sanshitu_' . ($i + 1) . '.' . $ext;
    $path = $tmpDir . '/' . $name;
    $fp = fopen($path, 'w');
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_FILE => $fp, CURLOPT_TIMEOUT => 30, CURLOPT_FOLLOWLOCATION => true]);
    curl_multi_add_handle($mh, $ch);
    $handles[] = [$ch, $fp, $path];
}
do { curl_multi_exec($mh, $running); curl_multi_select($mh); } while ($running);
$files = [];
foreach ($handles as [$ch, $fp, $path]) {
    curl_multi_remove_handle($mh, $ch); curl_close($ch); fclose($fp);
    if (filesize($path) > 0) $files[] = $path;
}
curl_multi_close($mh);

if (empty($files)) {
    array_map('unlink', glob($tmpDir . '/*')); rmdir($tmpDir);
    http_response_code(500); echo json_encode(['error'=>'All downloads failed']); exit;
}

$zipPath = $tmpDir . '/sanshitu_batch.zip';
$zip = new ZipArchive();
$zip->open($zipPath, ZipArchive::CREATE);
foreach ($files as $file) $zip->addFile($file, basename($file));
$zip->close();

// Move zip out of working dir and clean up
$dlName = $batchId . '.zip';
rename($zipPath, $dlDir . '/' . $dlName);
array_map('unlink', $files);
rmdir($tmpDir);

// Clean up old downloads (>5 min)
foreach (glob($dlDir . '/*.zip') as $f) {
    if (time() - filemtime($f) > 300) unlink($f);
}
// Clean up leftover temp dirs
foreach (glob($dlDir . '/batch_*', GLOB_ONLYDIR) as $d) {
    @array_map('unlink', glob($d . '/*')); @rmdir($d);
}

echo json_encode(['ok'=>true, 'url'=>'/dl/'.$dlName]);
