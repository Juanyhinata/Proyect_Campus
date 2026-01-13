
/* ADMINISTRADOR
// crear-admin.php  ← Ejecutár este archivo una sola vez

require_once 'config/db.php';  // Esto ya trae el $pdo conectado

$email = 'admin@campus.com';
$password = '123456';  // Cambiarla después por una fuerte

// Verificar si ya existe (para no duplicar)
$stmt = $pdo->prepare("SELECT id FROM campus.usuarios WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    die("El usuario $email ya existe. Todo bien.");
}

// Crear el hash seguro de la contraseña
$hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO campus.usuarios (nombre, email, password, rol) 
        VALUES ('Administrador', ?, ?, 'admin')";

$stmt = $pdo->prepare($sql);
$stmt->execute([$email, $hash]);*/


<?php
require_once 'config/db.php';

$email = 'agente@campus.com';
$password = '123456';

$stmt = $pdo->prepare("SELECT id FROM campus.usuarios WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    die("El usuario $email ya existe. Todo bien.");
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO campus.usuarios (nombre, email, password, rol, empresa) 
        VALUES ('Juan Perez', ?, ?, 'agente', 'Avalon CAC')";

$stmt = $pdo->prepare($sql);
$stmt->execute([$email, $hash]);

echo "Agente creado correctamente.<br>";
echo "Email: $email<br>";
echo "Contraseña: $password<br>";
?>




