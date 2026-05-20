<?php
// lib/jwt.php - Simple JWT implementation using HMAC-SHA256

define('JWT_SECRET', 'sanshitu_jwt_prod_2026_k8x2mNpQr5vL');
define('JWT_EXPIRE', 86400 * 7); // 7 days

function jwt_encode(array $payload): string {
  $header = base64url_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
  $payload['iat'] = time();
  $payload['exp'] = time() + JWT_EXPIRE;
  $payloadEncoded = base64url_encode(json_encode($payload));
  $signature = base64url_encode(
    hash_hmac('sha256', "$header.$payloadEncoded", JWT_SECRET, true)
  );
  return "$header.$payloadEncoded.$signature";
}

function jwt_decode(string $token): ?array {
  $parts = explode('.', $token);
  if (count($parts) !== 3) return null;

  [$header, $payload, $signature] = $parts;
  $expected = base64url_encode(
    hash_hmac('sha256', "$header.$payload", JWT_SECRET, true)
  );

  if (!hash_equals($expected, $signature)) return null;

  $data = json_decode(base64url_decode($payload), true);
  if (!$data) return null;
  if (($data['exp'] ?? 0) < time()) return null;

  return $data;
}

function base64url_encode(string $data): string {
  return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode(string $data): string {
  return base64_decode(strtr($data, '-_', '+/'));
}
