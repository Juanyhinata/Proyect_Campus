<?php
//rol/cliente/marcar_completado.php
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
require_once __DIR__ . '/../../models/Curso.php';
require_once __DIR__ . '/../../models/Usuario.php';

if (!in_array($_SESSION['rol'], ['cliente'])) {
    die(json_encode(['success' => false, 'error' => 'Acceso denegado']));
}

header('Content-Type: application/json');
require_once __DIR__ . '/../../models/Progreso.php';

$usuario_id = $_POST['usuario_id'] ?? null;
$modulo_id = $_POST['modulo_id'] ?? null;

if (!$usuario_id || !$modulo_id) {
    echo json_encode(['success' => false, 'error' => 'Datos faltantes']);
    exit;
}

if ($usuario_id != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => 'Seguridad: ID no coincide']);
    exit;
}

$progresoModel = new Progreso($pdo);

try {
    $progresoModel->marcarCompletado($usuario_id, $modulo_id);
    echo json_encode(["success" => true]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
