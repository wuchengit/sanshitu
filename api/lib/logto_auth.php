<?php
// lib/logto_auth.php - Validate Logto access tokens and return Logto user sub.
ini_set('display_errors', 0);

function requireLogtoAuth(): string {
  $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
  if (!preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
    authError('Missing authorization header');
  }

  $token = trim($m[1]);
  if ($token === '') {
    authError('Missing bearer token');
  }

  $endpoint = getenv('LOGTO_USERINFO_ENDPOINT') ?: 'https://auth.aiwuuw.com/oidc/me';
  $ch = curl_init($endpoint);
  curl_setopt_array($ch, [
    CURLOPT_HTTPGET => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
  ]);

  $response = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $curlError = curl_error($ch);
  curl_close($ch);

  if ($response === false || $httpCode !== 200) {
    authError($curlError ? 'Token validation failed' : 'Invalid or expired token');
  }

  $profile = json_decode($response, true);
  if (!is_array($profile) || empty($profile['sub'])) {
    authError('Invalid token profile');
  }

  return (string) $profile['sub'];
}

function authError(string $message): void {
  http_response_code(401);
  echo json_encode(['error' => $message]);
  exit;
}
