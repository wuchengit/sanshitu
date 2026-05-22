<?php
// lib/db.php - Database connection
define('DB_HOST', 'localhost');
define('DB_NAME', 'wordpress');
define('DB_USER', 'wordpress');
define('DB_PASS', 'aa78f97da47f08e7');

// Test mode: API requests with X-Test-Mode header use test_ prefixed tables
function isTestMode(): bool {
  $headers = function_exists('getallheaders') ? getallheaders() : [];
  return !empty($headers['X-Test-Mode']) || 
         (isset($_SERVER['HTTP_X_TEST_MODE']) && $_SERVER['HTTP_X_TEST_MODE'] === '1');
}

function db(): PDO {
  static $pdo = null;
  if ($pdo === null) {
    $pdo = new PDO(
      "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
      DB_USER, DB_PASS,
      [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
  }
  return $pdo;
}

// Resolve table name with test prefix when in test mode
function tableName(string $name): string {
  return isTestMode() ? "test_{$name}" : $name;
}
