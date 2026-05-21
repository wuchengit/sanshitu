<?php
// lib/auth.php - Auth middleware, returns user_id or sends 401
require_once __DIR__ . '/jwt.php';

function requireAuth(): int {
  $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
  if (!preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
    http_response_code(401);
    echo json_encode(['error' => 'Missing authorization header']);
    exit;
  }

  $payload = jwt_decode($m[1]);
  if (!$payload || empty($payload['sub'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid or expired token']);
    exit;
  }

  return (int) $payload['sub'];
}
