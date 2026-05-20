<?php
// auth/login.php - POST {email, password} → {token, user}
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

if (!$email || !$password) {
  http_response_code(400);
  echo json_encode(['error' => 'Email and password required']);
  exit;
}

// Lookup by email hash
$hash = email_lookup_hash($email);
$row = db()->prepare(
  'SELECT id, email_enc, password_hash FROM users WHERE email_hash = ?'
);
$row->execute([$hash]);
$user = $row->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Invalid email or password']);
  exit;
}

// Decrypt email for response
$decryptedEmail = decrypt($user['email_enc']) ?: $email;

$token = jwt_encode(['sub' => $user['id'], 'email' => $decryptedEmail]);
echo json_encode([
  'token' => $token,
  'user' => ['id' => $user['id'], 'email' => $decryptedEmail]
]);
