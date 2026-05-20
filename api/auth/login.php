<?php
// auth/login.php - POST {email, password} → {token, user}
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/jwt.php';

$input = json_decode(file_get_contents('php://input'), true);
$email = trim(strtolower($input['email'] ?? ''));
$password = $input['password'] ?? '';

if (!$email || !$password) {
  http_response_code(400);
  echo json_encode(['error' => 'Email and password required']);
  exit;
}

$row = db()->prepare('SELECT id, email, password_hash FROM users WHERE email = ?');
$row->execute([$email]);
$user = $row->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Invalid email or password']);
  exit;
}

$token = jwt_encode(['sub' => $user['id'], 'email' => $user['email']]);
echo json_encode(['token' => $token, 'user' => ['id' => $user['id'], 'email' => $user['email']]]);
