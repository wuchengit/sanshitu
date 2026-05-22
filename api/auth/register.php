<?php
// auth/register.php - POST {email, password} → {token, user}
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/jwt.php';
require_once __DIR__ . '/../lib/crypto.php';

$input = json_decode(file_get_contents('php://input'), true);
$email = trim(strtolower($input['email'] ?? ''));
$password = $input['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid email']);
  exit;
}
if (strlen($password) < 6) {
  http_response_code(400);
  echo json_encode(['error' => 'Password must be at least 6 characters']);
  exit;
}

// Check existing by email hash
$hash = email_lookup_hash($email);
$table = tableName('users');
$stmt = db()->prepare("SELECT id FROM {$table} WHERE email_hash = ?");
$stmt->execute([$hash]);
if ($stmt->fetch()) {
  http_response_code(409);
  echo json_encode(['error' => 'Email already registered']);
  exit;
}

// Create user (email encrypted, no plaintext)
$emailEnc = encrypt($email);
$pwdHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
db()->prepare(
  "INSERT INTO {$table} (email_hash, email_enc, password_hash) VALUES (?, ?, ?)"
)->execute([$hash, $emailEnc, $pwdHash]);
$userId = (int) db()->lastInsertId();

$token = jwt_encode(['sub' => $userId, 'email' => $email]);
echo json_encode(['token' => $token, 'user' => ['id' => $userId, 'email' => $email]]);
