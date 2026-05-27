<?php
// api/assets.php — 用户资源库
// GET: 获取当前用户全部资源（最多 100 条）
// POST: 上传新资源（multipart/form-data, field: file）
// DELETE ?id=xx: 删除指定资源（需本人）

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/logto_auth.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $userId = requireLogtoAuth();
} catch (Exception $e) {
    exit;
}

$pdo = db();

// 确保表存在
$pdo->exec(
    'CREATE TABLE IF NOT EXISTS ' . tableName('assets') . ' (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id VARCHAR(64) NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        thumb_path VARCHAR(255) NOT NULL,
        preview_path VARCHAR(255) DEFAULT NULL,
        width INT DEFAULT 0,
        height INT DEFAULT 0,
        file_size INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);

// 兼容旧表：加 preview_path 列
try {
    $pdo->exec('ALTER TABLE ' . tableName('assets') . ' ADD COLUMN preview_path VARCHAR(255) DEFAULT NULL AFTER thumb_path');
} catch (\PDOException $e) {
    // 列已存在，忽略
}

// ========== GET ==========
if ($method === 'GET') {
    $stmt = $pdo->prepare(
        'SELECT id, user_id, file_path, thumb_path, preview_path, width, height, file_size, created_at FROM ' . tableName('assets') . ' WHERE user_id = ? ORDER BY created_at DESC LIMIT 100'
    );
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();
    echo json_encode(['ok' => true, 'data' => $rows]);
    exit;
}

// ========== POST ==========
if ($method === 'POST') {
    // 优先处理 genpreview（补生预览图）
    if (isset($_GET['action']) && $_GET['action'] === 'genpreview') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => '缺少 id']);
            exit;
        }

        $stmt = $pdo->prepare('SELECT id, file_path, user_id FROM ' . tableName('assets') . ' WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => '记录不存在']);
            exit;
        }

        if ($row['user_id'] !== $userId) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => '无权操作']);
            exit;
        }

        // 已有预览图则直接返回
        $stmt2 = $pdo->prepare('SELECT preview_path FROM ' . tableName('assets') . ' WHERE id = ?');
        $stmt2->execute([$id]);
        $existing = $stmt2->fetch();
        if (!empty($existing['preview_path'])) {
            echo json_encode(['ok' => true, 'preview_path' => $existing['preview_path'], 'cached' => true]);
            exit;
        }

        // 解析原图本地路径
        $dataRoot = '/var/www/sanshitu-data';
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        $fileUrl = $row['file_path'];
        $localFile = null;
        if (strpos($fileUrl, $baseUrl) === 0) {
            $localFile = $dataRoot . substr(parse_url($fileUrl, PHP_URL_PATH), 5);
        }

        if (!$localFile || !file_exists($localFile)) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => '原图文件不存在']);
            exit;
        }

        $imgInfo = @getimagesize($localFile);
        $mimeType = $imgInfo['mime'] ?? 'image/jpeg';
        $width = $imgInfo ? $imgInfo[0] : 0;
        $height = $imgInfo ? $imgInfo[1] : 0;

        $previewName = pathinfo(basename($fileUrl), PATHINFO_FILENAME) . '_preview.' . pathinfo(basename($fileUrl), PATHINFO_EXTENSION);
        $previewLocal = dirname($localFile) . '/' . $previewName;

        $img = null;
        switch ($mimeType) {
            case 'image/jpeg': $img = @imagecreatefromjpeg($localFile); break;
            case 'image/png':  $img = @imagecreatefrompng($localFile); break;
            case 'image/gif':  $img = @imagecreatefromgif($localFile); break;
            case 'image/webp': $img = @imagecreatefromwebp($localFile); break;
        }

        if (!$img) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => '无法读取图片']);
            exit;
        }

        $ow = imagesx($img);
        $oh = imagesy($img);
        $pw = $ow; $ph = $oh;
        $maxDim = 800;
        if ($pw > $maxDim || $ph > $maxDim) {
            if ($pw >= $ph) {
                $ph = (int)($ph * ($maxDim / $pw));
                $pw = $maxDim;
            } else {
                $pw = (int)($pw * ($maxDim / $ph));
                $ph = $maxDim;
            }
        }

        $preview = imagecreatetruecolor($pw, $ph);
        if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
            imagealphablending($preview, false);
            imagesavealpha($preview, true);
        }
        imagecopyresampled($preview, $img, 0, 0, 0, 0, $pw, $ph, $ow, $oh);

        switch ($mimeType) {
            case 'image/jpeg': imagejpeg($preview, $previewLocal, 60); break;
            case 'image/png':  imagepng($preview, $previewLocal, 6); break;
            case 'image/gif':  imagegif($preview, $previewLocal); break;
            case 'image/webp': imagewebp($preview, $previewLocal, 60); break;
        }
        imagedestroy($preview);
        imagedestroy($img);

        $previewUrl = dirname($fileUrl) . '/' . $previewName;

        $stmt3 = $pdo->prepare('UPDATE ' . tableName('assets') . ' SET preview_path = ? WHERE id = ?');
        $stmt3->execute([$previewUrl, $id]);

        echo json_encode(['ok' => true, 'preview_path' => $previewUrl]);
        exit;
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $err = $_FILES['file']['error'] ?? 'missing';
        $messages = [
            UPLOAD_ERR_INI_SIZE => '文件超过服务器限制',
            UPLOAD_ERR_FORM_SIZE => '文件超过表单限制',
            UPLOAD_ERR_PARTIAL => '文件上传不完整',
            UPLOAD_ERR_NO_FILE => '未选择文件',
        ];
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => $messages[$err] ?? '上传失败 (code ' . $err . ')']);
        exit;
    }

    $file = $_FILES['file'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => '不支持的格式，仅支持 jpg/png/gif/webp']);
        exit;
    }

    if ($file['size'] > 30 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => '文件大小不能超过 30MB']);
        exit;
    }

    // 用户目录
    $dataRoot = '/var/www/sanshitu-data';
    $userDir = $dataRoot . '/assets/' . $userId;
    if (!is_dir($userDir)) mkdir($userDir, 0755, true);

    $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $ext = $extMap[$mimeType];
    $origName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
    $baseName = pathinfo($origName, PATHINFO_FILENAME);
    $filename = time() . '_' . substr(md5($baseName . microtime(true)), 0, 8) . '.' . $ext;
    $filePath = $userDir . '/' . $filename;
    $thumbName = pathinfo($filename, PATHINFO_FILENAME) . '_thumb.' . $ext;
    $thumbPath = $userDir . '/' . $thumbName;
    $previewName = pathinfo($filename, PATHINFO_FILENAME) . '_preview.' . $ext;
    $previewPath = $userDir . '/' . $previewName;

    // 移动上传文件
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => '文件保存失败']);
        exit;
    }

    // 读取图片信息
    $imgInfo = @getimagesize($filePath);
    $width = $imgInfo ? $imgInfo[0] : 0;
    $height = $imgInfo ? $imgInfo[1] : 0;
    $fileSize = filesize($filePath);

    // 生成预览图 + 缩略图
    $previewCreated = false;
    $thumbCreated = false;
    $img = null;
    switch ($mimeType) {
        case 'image/jpeg': $img = @imagecreatefromjpeg($filePath); break;
        case 'image/png':  $img = @imagecreatefrompng($filePath); break;
        case 'image/gif':  $img = @imagecreatefromgif($filePath); break;
        case 'image/webp': $img = @imagecreatefromwebp($filePath); break;
    }

    if ($img) {
        $ow = imagesx($img);
        $oh = imagesy($img);

        // 1. 生成预览图（最长边 800px）
        $pw = $ow; $ph = $oh;
        $maxDim = 800;
        if ($pw > $maxDim || $ph > $maxDim) {
            if ($pw >= $ph) {
                $ph = (int)($ph * ($maxDim / $pw));
                $pw = $maxDim;
            } else {
                $pw = (int)($pw * ($maxDim / $ph));
                $ph = $maxDim;
            }
        }
        $preview = imagecreatetruecolor($pw, $ph);
        if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
            imagealphablending($preview, false);
            imagesavealpha($preview, true);
        }
        imagecopyresampled($preview, $img, 0, 0, 0, 0, $pw, $ph, $ow, $oh);
        switch ($mimeType) {
            case 'image/jpeg': imagejpeg($preview, $previewPath, 60); break;
            case 'image/png':  imagepng($preview, $previewPath, 6); break;
            case 'image/gif':  imagegif($preview, $previewPath); break;
            case 'image/webp': imagewebp($preview, $previewPath, 60); break;
        }
        imagedestroy($preview);
        $previewCreated = true;

        // 2. 生成缩略图（200px 宽）
        $tw = min($ow, 200);
        $th = (int)($oh * ($tw / $ow));
        $thumb = imagecreatetruecolor($tw, $th);
        if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
        }
        imagecopyresampled($thumb, $img, 0, 0, 0, 0, $tw, $th, $ow, $oh);
        switch ($mimeType) {
            case 'image/jpeg': imagejpeg($thumb, $thumbPath, 75); break;
            case 'image/png':  imagepng($thumb, $thumbPath, 6); break;
            case 'image/gif':  imagegif($thumb, $thumbPath); break;
            case 'image/webp': imagewebp($thumb, $thumbPath, 75); break;
        }
        imagedestroy($thumb);
        imagedestroy($img);
        $thumbCreated = true;
    }

    if (!$thumbCreated) {
        copy($filePath, $thumbPath);
    }
    if (!$previewCreated) {
        copy($filePath, $previewPath);
    }

    // 构建 URL
    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $fileUrl = "$scheme://$host/data/assets/$userId/$filename";
    $thumbUrl = "$scheme://$host/data/assets/$userId/$thumbName";
    $previewUrl = "$scheme://$host/data/assets/$userId/$previewName";

    // 记数据库
    $stmt = $pdo->prepare(
        'INSERT INTO ' . tableName('assets') . ' (user_id, file_path, thumb_path, preview_path, width, height, file_size) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $fileUrl, $thumbUrl, $previewUrl, $width, $height, $fileSize]);

    echo json_encode([
        'ok' => true,
        'id' => (int)$pdo->lastInsertId(),
        'file_path' => $fileUrl,
        'thumb_path' => $thumbUrl,
        'preview_path' => $previewUrl,
        'width' => $width,
        'height' => $height,
        'file_size' => $fileSize
    ]);
    exit;
}

// ========== DELETE ==========
if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => '缺少 id']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT file_path, thumb_path, preview_path, user_id FROM ' . tableName('assets') . ' WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => '记录不存在']);
        exit;
    }

    if ($row['user_id'] !== $userId) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => '无权删除']);
        exit;
    }

    // 删除服务器文件
    $dataRoot = '/var/www/sanshitu-data';
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

    $toLocal = function($url) use ($baseUrl, $dataRoot) {
        if (strpos($url, $baseUrl) === 0) {
            return $dataRoot . substr(parse_url($url, PHP_URL_PATH), 5); // strip /data
        }
        return null;
    };

    $localFile = $toLocal($row['file_path']);
    $localThumb = $toLocal($row['thumb_path']);
    $localPreview = $toLocal($row['preview_path']);

    if ($localFile) @unlink($localFile);
    if ($localThumb) @unlink($localThumb);
    if ($localPreview) @unlink($localPreview);

    $stmt = $pdo->prepare('DELETE FROM ' . tableName('assets') . ' WHERE id = ?');
    $stmt->execute([$id]);

    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
