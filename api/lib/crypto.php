<?php
// lib/crypto.php - AES-256-CBC encryption for user sensitive data
define('CRYPTO_KEY', 'aes256_sanshitu_2026_j9vKx4mNp2qR8wL');
define('LOOKUP_KEY', 'hmac_lookup_2026_h5tY7uP3sW9bF');

function encrypt(string $plaintext): string {
  $iv = openssl_random_pseudo_bytes(16);
  $encrypted = openssl_encrypt($plaintext, 'aes-256-cbc', CRYPTO_KEY, OPENSSL_RAW_DATA, $iv);
  return base64_encode($iv . $encrypted);
}

function decrypt(string $ciphertext): ?string {
  $data = base64_decode($ciphertext);
  if (strlen($data) < 17) return null;
  $iv = substr($data, 0, 16);
  $encrypted = substr($data, 16);
  $result = openssl_decrypt($encrypted, 'aes-256-cbc', CRYPTO_KEY, OPENSSL_RAW_DATA, $iv);
  return $result === false ? null : $result;
}

// Deterministic hash for email lookup (not reversible, used for WHERE queries)
function email_lookup_hash(string $email): string {
  return hash_hmac('sha256', strtolower(trim($email)), LOOKUP_KEY);
}
