<?php
// rol/cliente/realizar_evaluacion.php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Evaluacion.php';
require_once __DIR__ . '/../../models/Progreso.php';

if ($_SESSION['rol'] !== 'cliente') {
    die('Acceso denegado');
}

$evalModel = new Evaluacion($pdo);
$progresoModel = new Progreso($pdo);

$evaluacion_id = $_GET['id'] ?? 0;
// No tenemos metodo obtenerPorId en Evaluacion, pero podemos obtener preguntas directo si tenemos el ID
// O podemos hacer una query rapida aqui para validar
$stmt = $pdo->prepare("SELECT * FROM campus.evaluaciones WHERE id = ?");
$stmt->execute([$evaluacion_id]);
$evaluacion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$evaluacion) die('Evaluación no encontrada');

$modulo_id = $evaluacion['modulo_id'];
$usuario_id = $_SESSION['user_id'];

// Verificar si ya aprobó (opcional, pero útil)
$mejor_nota = $evalModel->obtenerMejorIntento($usuario_id, $evaluacion_id);

$mensaje = '';
$resultado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $respuestas = $_POST['respuestas'] ?? [];
    
    // Calificar
    $calificacion = $evalModel->calificar($usuario_id, $evaluacion_id, $respuestas);
    
    // Recalcular progreso total del módulo
    $progreso_total = $progresoModel->calcularProgresoTotal($usuario_id, $modulo_id);
    
    // Marcar módulo como completado si aprobó todo (>= 92%)
    if ($progreso_total['aprobado']) {
        $progresoModel->marcarCompletado($usuario_id, $modulo_id);
        $mensaje = '<div class="alerta exito">¡Felicidades! Has aprobado el módulo con una calificación final de ' . $progreso_total['nota_final'] . '%.</div>';
    } else {
        $mensaje = '<div class="alerta warning">Tu calificación en el examen fue ' . number_format($calificacion, 1) . '%. Tu promedio final del módulo es ' . $progreso_total['nota_final'] . '%. Necesitas 92% para aprobar.</div>';
    }
    
    $resultado = $calificacion;
}

$preguntas = $evalModel->obtenerPreguntas($evaluacion_id);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Evaluación: <?= htmlspecialchars($evaluacion['titulo']) ?></title>
    <?php require_once __DIR__ . '/view/haed.php'; ?>
    <style>
        .pregunta-block { background: #fff; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .opcion-label { display: block; padding: 10px; border: 1px solid #eee; margin-top: 5px; border-radius: 4px; cursor: pointer; transition: background 0.2s; }
        .opcion-label:hover { background: #f5f5f5; }
        .opcion-input { margin-right: 10px; }
        .resultado-box { text-align: center; padding: 30px; background: #fff; border-radius: 8px; margin-bottom: 20px; }
        .nota-grande { font-size: 3em; font-weight: bold; color: #2196F3; }
    </style>
</head>
<body>
<div class="contenedor-principal">
    <aside class="barra-lateral">
        <div class="logo-avalon"><img src="../../img/Avalon_Informatica/Logos/svg/isotype.svg" alt="Logo"></div>
        <nav class="navegacion"><?php require_once __DIR__ . '/view/nav.php'; ?></nav>
    </aside>

    <main class="contenido-principal">
        <header class="encabezado-superior"><?php require_once __DIR__ . '/view/header.php'; ?></header>

        <div class="header-modulo">
            <h2>Evaluación: <?= htmlspecialchars($evaluacion['titulo']) ?></h2>
        </div>

        <?php if ($resultado !== null): ?>
            <div class="resultado-box">
                <h3>Resultado del Intento</h3>
                <div class="nota-grande"><?= number_format($resultado, 0) ?>%</div>
                <?= $mensaje ?>
                <div style="margin-top:20px;">
                    <a href="curso_detalle.php?id=<?= $modulo_id ?>" class="btn btn-primary">Volver al Módulo</a>
                    <a href="realizar_evaluacion.php?id=<?= $evaluacion_id ?>" class="btn btn-secondary">Intentar de nuevo</a>
                </div>
            </div>
        <?php else: ?>

            <div class="form-card">
                <p><?= nl2br(htmlspecialchars($evaluacion['descripcion'])) ?></p>
                <p><strong>Instrucciones:</strong> Selecciona la respuesta correcta para cada pregunta. Al finalizar, haz clic en "Enviar Respuestas".</p>
            </div>

            <form method="POST">
                <?php foreach ($preguntas as $index => $pregunta): 
                    $opciones = $evalModel->obtenerOpciones($pregunta['id']);
                ?>
                    <div class="pregunta-block">
                        <h4><?= ($index + 1) . '. ' . htmlspecialchars($pregunta['texto_pregunta']) ?></h4>
                        
                        <?php foreach ($opciones as $opcion): ?>
                            <label class="opcion-label">
                                <input type="radio" name="respuestas[<?= $pregunta['id'] ?>]" value="<?= $opcion['id'] ?>" class="opcion-input" required>
                                <?= htmlspecialchars($opcion['texto_opcion']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>

                <?php if (count($preguntas) > 0): ?>
                    <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">Enviar Respuestas</button>
                <?php else: ?>
                    <div class="alerta warning">Esta evaluación aún no tiene preguntas configuradas.</div>
                    <a href="curso_detalle.php?id=<?= $modulo_id ?>" class="btn btn-secondary">Volver</a>
                <?php endif; ?>
            </form>

        <?php endif; ?>

    </main>
</div>
</body>
</html>
