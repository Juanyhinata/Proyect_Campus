<?php
session_start();
require_once __DIR__ . '/../config/db.php';  // <-- esta línea trae la conexión

if ($_POST) {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if ($email === '' || $password === '') {
        header('Location: ../public/login.php?error=Complete todos los campos');
        exit;
    }

    // Buscar el usuario
    $sql = "SELECT id, nombre, password, rol, activo FROM campus.usuarios WHERE email = :email LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    // Verificar contraseña
    if ($user && password_verify($password, $user['password'])) {
        
        // Verificar si el usuario está activo
        if ($user['activo'] != 1) {
            header('Location: ../public/login.php?error=Tu cuenta está inactiva. Contacta al administrador.');
            exit;
        }
        
        // Login exitoso
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nombre']  = $user['nombre'];
        $_SESSION['rol']     = $user['rol'];
        $_SESSION['logged']  = true;

        // Redirección según rol
        if ($user['rol'] === 'admin') {
            header('Location: ../rol/admin/dashboard.php');
        } elseif ($user['rol'] === 'agente') {
            header('Location: ../rol/agente/dashboard.php');
        } elseif ($user['rol'] === 'cliente') {
            header('Location: ../rol/cliente/dashboard.php');
        } elseif ($user['rol'] === 'invitado') {
            header('Location: ../rol/invitado/dashboard.php');
        }
        exit;
    } else {
        header('Location: ../public/login.php?error=Email o contraseña incorrecta');
        exit;
    }
}