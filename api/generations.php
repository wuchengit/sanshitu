<?php
// api/generations.php — 生成历史服务端存储
// GET: 查当前用户历史（最近50条）
// POST: 新建记录 / 更新已有记录（通过body.id区分）
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
    exit;
}

$pdo = db();

if ($method === 'GET') {
    $stmt = $pdo->prepare(
        'SELECT id, user_id, prompt, model, info, resolution, aspect, width, height, file_size, image_url, thumb_url, status, created_at FROM ' . tableName('generations') . ' WHERE user_id = ? ORDER BY created_at ASC LIMIT 50'
    );
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();
    echo json_encode(['ok' => true, 'data' => $rows]);
    exit;
}

if ($method === 'POST' || $method === 'PUT') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => '无效请求']);
        exit;
    }

    $updateId = !empty($body['id']) ? (int)$body['id'] : 0;

    if ($updateId > 0) {
        // UPDATE: 更新已有记录（更新提供的字段）
        $sets = [];
        $params = [];
        foreach (['image_url', 'thumb_url', 'status', 'info', 'prompt', 'model', 'resolution', 'aspect'] as $f) {
            if (isset($body[$f])) {
                $sets[] = "`$f`=?";
                $params[] = $body[$f];
            }
        }
        foreach (['width', 'height', 'file_size'] as $f) {
            if (isset($body[$f])) {
                $sets[] = "`$f`=?";
                $params[] = (int)$body[$f];
            }
        }
        if (!empty($sets)) {
            $params[] = $updateId;
            $params[] = $userId;
            $stmt = $pdo->prepare('UPDATE ' . tableName('generations') . ' SET ' . implode(',', $sets) . ' WHERE id=? AND user_id=?');
            $stmt->execute($params);
        }
        echo json_encode(['ok' => true, 'id' => $updateId]);
    } else {
        // INSERT: 新建记录（status默认pending）
        $stmt = $pdo->prepare(
            'INSERT INTO ' . tableName('generations') . ' (user_id, prompt, model, info, resolution, aspect, status) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $body['prompt'] ?? '',
            $body['model'] ?? '',
            $body['info'] ?? '',
            $body['resolution'] ?? '',
            $body['aspect'] ?? '',
            $body['status'] ?? 'pending'
        ]);
        echo json_encode(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
    }
    exit;
}

if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => '缺少 id']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT image_url, thumb_url FROM ' . tableName('generations') . ' WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => '记录不存在']);
        exit;
    }

    $docRoot = '/usr/local/lighthouse/softwares/wordpress';
    $parsed = parse_url($row['image_url']);
    if (!empty($parsed['path'])) {
        @unlink($docRoot . $parsed['path']);
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
