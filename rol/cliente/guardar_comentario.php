<?php
// ======================================================================
// 🔐 AUTENTICACIÓN DE SESIÓN
// ======================================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged']) && !isset($_SESSION['logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit;
}

if (!in_array($_SESSION['rol'], ['cliente'])) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/ComentarioCurso.php';

header('Content-Type: application/json');

try {
    // Validar datos requeridos
    $curso_id = filter_input(INPUT_POST, 'curso_id', FILTER_VALIDATE_INT);
    $usuario_id = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
    
    if (!$curso_id || !$usuario_id) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit;
    }
    
    // Validar que el usuario de la sesión coincide
    $session_user_id = $_SESSION['usuario']['id'] 
                      ?? $_SESSION['id'] 
                      ?? $_SESSION['user_id'] 
                      ?? $_SESSION['usuario_id'] 
                      ?? null;
    
    if ($session_user_id != $usuario_id) {
        echo json_encode(['success' => false, 'message' => 'Usuario no autorizado']);
        exit;
    }
    
    // Recopilar ratings (opcion ales, pero al menos uno debe existir)
    $calidad_contenido = filter_input(INPUT_POST, 'calidad_contenido', FILTER_VALIDATE_INT);
    $facilidad_uso = filter_input(INPUT_POST, 'facilidad_uso', FILTER_VALIDATE_INT);
    $utilidad_practica = filter_input(INPUT_POST, 'utilidad_practica', FILTER_VALIDATE_INT);
    $otros_comentarios = trim($_POST['otros_comentarios'] ?? '');
    
    // Preparar datos
    $datos = [
        'curso_id' => $curso_id,
        'usuario_id' => $usuario_id,
        'calidad_contenido' => $calidad_contenido ? max(1, min(5, $calidad_contenido)) : null,
        'facilidad_uso' => $facilidad_uso ? max(1, min(5, $facilidad_uso)) : null,
        'utilidad_practica' => $utilidad_practica ? max(1, min(5, $utilidad_practica)) : null,
        'otros_comentarios' => !empty($otros_comentarios) ? $otros_comentarios : null
    ];
    
    // Guardar en base de datos
    $comentarioModel = new ComentarioCurso($pdo);
    $result = $comentarioModel->guardarComentario($datos);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Calificación guardada exitosamente. ¡Gracias por tu feedback!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al guardar la calificación'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
