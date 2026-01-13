<?php
// public/logout.php

session_start();           // Inicia la sesión para poder destruirla

// Borra TODAS las variables de sesión
$_SESSION = [];

// Borra la cookie de sesión (si existe)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruye completamente la sesión
session_destroy();

// Redirige al login limpio
header("Location: login.php?mensaje=Sesión cerrada correctamente");
exit;
?>