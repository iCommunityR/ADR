<?php
// src/bootstrap.php - load config and create PDO connection
$config = require __DIR__ . '/../config/config.php';
$db = $config['db'] ?? [];

$host = $db['host'] ?? '127.0.0.1';
$port = $db['port'] ?? '3306';
$name = $db['name'] ?? 'africa_adr';
$user = $db['user'] ?? 'root';
$pass = $db['pass'] ?? '';
$charset = $db['charset'] ?? 'utf8mb4';

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $name, $charset);

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo "Database connection failed: " . htmlspecialchars($e->getMessage());
    exit;
}

// expose app config for views
$app = $config['app'] ?? ['name' => 'African Disputes Resolution'];
