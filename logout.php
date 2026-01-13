<?php
// public/logout.php

session_start();           // Inicia la sesión para poder destruirla, romperla, exterminarla degollarla etc ...

// Borra TODAS las variables de sesión, que mueran cobardes jijiji
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

// Redirige al login limpio, con un mensaje de sesion cerrada correctamente por no decir ya te fuiste cobarde
header("Location: login.php?mensaje=Sesión cerrada correctamente");
exit;
?>