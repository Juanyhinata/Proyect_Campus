
<?php
// config/db.php

$envPath = __DIR__ . '/../.env';

if (!file_exists($envPath)) {
    die('.env no encontrado en la raíz del proyecto');
}

$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$env = [];

foreach ($lines as $line) {
    $line = trim($line);
    
    // Ignorar comentarios
    if (str_starts_with($line, '#') || $line === '') {
        continue;
    }
    
    // Separar clave=valor
    if (str_contains($line, '=')) {
        [$key, $value] = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }
}

// Ahora sí asignar variables
$host = $env['DB_HOST'] ?? 'localhost';
$port = $env['DB_PORT'] ?? '5432';
$db   = $env['DB_NAME'] ?? 'campus';
$user = $env['DB_USER'] ?? 'postgres';
$pass = $env['DB_PASS'] ?? '';

$dsn = "pgsql:host=$host;port=$port;dbname=$db";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>