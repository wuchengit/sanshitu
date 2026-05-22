<?php
// user/config.php - GET → read config, POST → save config
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/crypto.php';

$userId = requireAuth();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $key = $_GET['key'] ?? 'default';
  $table = tableName('user_configs');
  $stmt = $pdo->prepare(
    "SELECT config_data FROM {$table} WHERE user_id = ? AND config_key = ?"
  );
  $stmt->execute([$userId, $key]);
  $row = $stmt->fetch();

  $config = null;
  if ($row && $row['config_data']) {
    $decrypted = decrypt($row['config_data']);
    $config = $decrypted ? json_decode($decrypted, true) : null;
  }
  echo json_encode(['config' => $config]);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $input = json_decode(file_get_contents('php://input'), true);
  $key = $input['key'] ?? 'default';
  $data = $input['data'] ?? null;

  if ($data === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing config data']);
    exit;
  }

  $encrypted = encrypt(json_encode($data));
  $pdo->prepare(
    "INSERT INTO {$table} (user_id, config_key, config_data)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE config_data = VALUES(config_data)"
  )->execute([$userId, $key, $encrypted]);

  echo json_encode(['ok' => true, 'key' => $key]);
} else {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
}
