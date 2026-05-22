<?php
// user/custom.php - CRUD for custom presets
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/logto_auth.php';
require_once __DIR__ . '/../lib/crypto.php';

$userId = requireLogtoAuth();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $category = $_GET['category'] ?? '';
  $table = tableName('custom_presets');
  $sql = "SELECT id, category, name, label, prompt, sort_order FROM {$table} WHERE user_id = ?";
  $params = [$userId];
  if ($category) { $sql .= ' AND category = ?'; $params[] = $category; }
  $sql .= ' ORDER BY sort_order, id';
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $presets = $stmt->fetchAll();
  // Decrypt prompts
  foreach ($presets as &$p) {
    $p['prompt'] = decrypt($p['prompt']) ?: $p['prompt'];
  }
  echo json_encode(['presets' => $presets]);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $input = json_decode(file_get_contents('php://input'), true);
  $category = $input['category'] ?? '';
  $name = trim($input['name'] ?? '');
  $label = trim($input['label'] ?? '');
  $prompt = trim($input['prompt'] ?? '');

  if (!$category || !$name || !$prompt) {
    http_response_code(400);
    echo json_encode(['error' => 'category, name, prompt required']);
    exit;
  }

  $encrypted = encrypt($prompt);
  $stmt = $pdo->prepare(
    "INSERT INTO {$table} (user_id, category, name, label, prompt) VALUES (?, ?, ?, ?, ?)"
  );
  $stmt->execute([$userId, $category, $name, $label, $encrypted]);
  $id = (int) $pdo->lastInsertId();

  echo json_encode(['ok' => true, 'id' => $id, 'name' => $name, 'category' => $category]);

} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
  $input = json_decode(file_get_contents('php://input'), true);
  $id = (int)($input['id'] ?? 0);
  if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'id required for update']);
    exit;
  }
  $category = $input['category'] ?? '';
  $name = trim($input['name'] ?? '');
  $label = trim($input['label'] ?? '');
  $prompt = trim($input['prompt'] ?? '');
  if (!$category || !$name || !$prompt) {
    http_response_code(400);
    echo json_encode(['error' => 'category, name, prompt required']);
    exit;
  }
  $encrypted = encrypt($prompt);
  $stmt = $pdo->prepare("UPDATE {$table} SET category = ?, name = ?, label = ?, prompt = ? WHERE id = ? AND user_id = ?");
  $stmt->execute([$category, $name, $label, $encrypted, $id, $userId]);
  if ($stmt->rowCount() > 0) {
    echo json_encode(['ok' => true, 'id' => $id, 'name' => $name, 'category' => $category]);
  } else {
    http_response_code(404);
    echo json_encode(['error' => 'Preset not found or not yours']);
  }

} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  $input = json_decode(file_get_contents('php://input'), true);
  $id = (int)($input['id'] ?? 0);
  if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'id required']);
    exit;
  }
  $stmt = $pdo->prepare("DELETE FROM {$table} WHERE id = ? AND user_id = ?");
  $stmt->execute([$id, $userId]);
  echo json_encode(['ok' => true, 'deleted' => $stmt->rowCount() > 0]);
} else {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
}
