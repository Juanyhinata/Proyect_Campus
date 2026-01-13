<?php
// includes/auth.php  ← VERSIÓN FINAL SIN WARNINGS

// === 1. Configuración de sesión ANTES de cualquier cosa ===
if (session_status() === PHP_SESSION_NONE) {
    // Configuramos antes de iniciar la sesión
    ini_set('session.gc_maxlifetime', 7200);           // 2 horas máximo
    session_set_cookie_params([
        'lifetime' => 7200,
        'path'     => '/',
        'domain'   => '',
        'secure'   => false,       // true solo si usa HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    // Si querés que se borre al cerrar el navegador:
    // session_set_cookie_params(0);

    session_start();
}

// Regenerar ID de sesión periódicamente (cada 30 minutos) en lugar de en cada carga
// Esto mejora la seguridad sin romper las sesiones
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 1800 segundos = 30 minutos
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// === 2. Protección: si no está logueado, lo echamos ===
if (!isset($_SESSION['logged']) && !isset($_SESSION['logged_in'])) {
    // Limpiamos cualquier output anterior
    if (ob_get_length()) ob_clean();
    
    header('Location: ../public/login.php?error=Inicia sesión primero');
    exit;
}

// Opcional: renovar tiempo de vida en cada visita
$_SESSION['last_activity'] = time();
?>