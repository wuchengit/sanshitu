<?php
// api/generations.php — 生成历史服务端存储
// GET: 查当前用户历史（最近50条）
// POST: 保存记录
// DELETE ?id=xx: 删除记录（需本人）

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
    // authError already sends response
    exit;
}

$pdo = db();

if ($method === 'GET') {
    $stmt = $pdo->prepare(
        'SELECT id, user_id, prompt, model, info, resolution, aspect, width, height, file_size, image_url, thumb_url, created_at FROM ' . tableName('generations') . ' WHERE user_id = ? ORDER BY created_at DESC LIMIT 50'
    );
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();
    echo json_encode(['ok' => true, 'data' => $rows]);
    exit;
}

if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body || empty($body['image_url'])) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => '缺少 image_url']);
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO ' . tableName('generations') . ' (user_id, prompt, model, info, resolution, aspect, width, height, file_size, image_url, thumb_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $userId,
        $body['prompt'] ?? '',
        $body['model'] ?? '',
        $body['info'] ?? '',
        $body['resolution'] ?? '',
        $body['aspect'] ?? '',
        (int)($body['width'] ?? 0),
        (int)($body['height'] ?? 0),
        (int)($body['file_size'] ?? 0),
        $body['image_url'],
        $body['thumb_url'] ?? ''
    ]);

    echo json_encode(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
    exit;
}

if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => '缺少 id']);
        exit;
    }

    // 确认记录属于当前用户
    $stmt = $pdo->prepare('SELECT image_url, thumb_url FROM ' . tableName('generations') . ' WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => '记录不存在']);
        exit;
    }

    // 删除文件（尝试删除，不存在的忽略）
    $docRoot = '/usr/local/lighthouse/softwares/wordpress';
    // image_url 是完整 URL，转成文件路径
    $parsed = parse_url($row['image_url']);
    if (!empty($parsed['path'])) {
        $imgPath = $docRoot . $parsed['path'];
        @unlink($imgPath);
    }
    if (!empty($row['thumb_url'])) {
        $parsedT = parse_url($row['thumb_url']);
        if (!empty($parsedT['path'])) {
            @unlink($docRoot . $parsedT['path']);
        }
    }

    $stmt = $pdo->prepare('DELETE FROM ' . tableName('generations') . ' WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);

    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
