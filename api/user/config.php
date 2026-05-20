<?php
// user/config.php - GET → read config, POST → save config
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/auth.php';

$userId = requireAuth();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $key = $_GET['key'] ?? 'default';
  $stmt = $pdo->prepare('SELECT config_data FROM user_configs WHERE user_id = ? AND config_key = ?');
  $stmt->execute([$userId, $key]);
  $row = $stmt->fetch();
  echo json_encode(['config' => $row ? json_decode($row['config_data'], true) : null]);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $input = json_decode(file_get_contents('php://input'), true);
  $key = $input['key'] ?? 'default';
  $data = $input['data'] ?? null;

  if ($data === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing config data']);
    exit;
  }

  // Upsert
  $pdo->prepare(
    'INSERT INTO user_configs (user_id, config_key, config_data)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE config_data = VALUES(config_data)'
  )->execute([$userId, $key, json_encode($data)]);

  echo json_encode(['ok' => true, 'key' => $key]);
} else {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
}
