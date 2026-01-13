<?php
// ======================================================================
// 🔐 AUTENTICACIÓN DE SESIÓN
// ======================================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['logged']) && !isset($_SESSION['logged_in'])) {
    echo json_encode(['success' => false, 'error' => 'Auth requerida']);
    exit;
}
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';

// FIX #1: Asegurar que el objeto de conexión PDO esté disponible globalmente si tu db.php lo necesita.
// Nota: Dado que el db.php crea $pdo, no es estrictamente necesario, pero lo mantenemos para seguridad.
global $pdo;

// Recomendación: Establecer modo de error para forzar errores PDO (si no está en db.php)
// $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Estos modelos no son necesarios en guardar_tiempo.php, pero los mantenemos si son incluidos por auth.php.
// require_once __DIR__ . '/../../models/Curso.php'; 
// require_once __DIR__ . '/../../models/Usuario.php'; 

if (!in_array($_SESSION['rol'], ['cliente'])) {
    die(json_encode(['success' => false, 'error' => 'Acceso denegado']));
}

header('Content-Type: application/json');
require_once __DIR__ . '/../../models/Progreso.php'; // Asegúrarme de que esta ruta es correcta (../../models/Progreso.php)

// Validar entrada
$usuario_id = $_POST['usuario_id'] ?? null;
$modulo_id = $_POST['modulo_id'] ?? null;
$tema_id = $_POST['tema_id'] ?? null; 
$tiempo_visto = $_POST['tiempo_visto'] ?? 0;
$duracion_video = $_POST['duracion_video'] ?? 1;

if (!$usuario_id || !$modulo_id) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

// ======================================================================
// FIX #2: Lógica de la Sesión Defensiva para el chequeo de seguridad
// ======================================================================

// 1. Recuperar el ID del usuario de la sesión, usando la lógica defensiva que funciona en ver_tema.php
$session_usuario_id = $_SESSION['user_id'] ?? null;

// 2. Comprobación de seguridad: El ID enviado por JS debe coincidir con la ID de la sesión.
if (!$session_usuario_id || $usuario_id != $session_usuario_id) {
    // Si la sesión no tiene ID o la ID enviada no coincide, fallar.
    echo json_encode(['success' => false, 'error' => 'Usuario inválido: ID de sesión no coincide o sesión incompleta.']);
    exit;
}

// ======================================================================
// LÓGICA DE GUARDADO
// ======================================================================

$progresoModel = new Progreso($pdo);

try {
    // Lógica: Guardar el tiempo y calcular porcentaje
    $resultado = $progresoModel->guardarTiempo($usuario_id, $modulo_id, $tiempo_visto, $duracion_video);
    
    echo json_encode([
        'success' => true,
        'porcentaje' => $resultado['nuevo_porcentaje']
    ]);
} catch (Exception $e) {
    // Si se lanza una excepción de PDO, la capturamos aquí
    echo json_encode(['success' => false, 'error' => 'Error de DB/Guardado: ' . $e->getMessage()]);
}
?>