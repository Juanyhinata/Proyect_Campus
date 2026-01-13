<?php
// rol/admin/usuarios_eliminar.php
session_start();
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Usuario.php';

if (!in_array($_SESSION['rol'], ['admin'])) {
    header('Location: ../../public/login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Evitar que se elimine a sí mismo
    if ($id == $_SESSION['user_id']) {
        die('No puedes eliminar tu propia cuenta.');
    }

    $usuarioModel = new Usuario($pdo);
    
    try {
        if ($usuarioModel->eliminar($id)) {
            header('Location: usuarios.php?msg=eliminado');
        } else {
            die('Error al eliminar el usuario.');
        }
    } catch (Exception $e) {
        die('Error al eliminar: ' . $e->getMessage());
    }
} else {
    header('Location: usuarios.php');
}
?>
