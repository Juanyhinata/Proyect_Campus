<?php
// rol/admin/gestion_evaluaciones.php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Modulo.php';
require_once __DIR__ . '/../../models/Evaluacion.php';

if ($_SESSION['rol'] !== 'admin') {
    die('Acceso denegado');
}

$moduloModel = new Modulo($pdo);
$evalModel = new Evaluacion($pdo);

$modulo_id = $_GET['modulo_id'] ?? 0;
$modulo = $moduloModel->obtenerPorId($modulo_id);

if (!$modulo) die('Módulo no encontrado');

// Manejo de Creación/Edición de Evaluación
$mensaje = '';
$evaluacion = $evalModel->obtenerPorModulo($modulo_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Guardar/Actualizar Evaluación
    if (isset($_POST['accion']) && $_POST['accion'] === 'guardar_evaluacion') {
        $titulo = trim($_POST['titulo']);
        $descripcion = trim($_POST['descripcion']);
        
        if ($evaluacion) {
            $evalModel->actualizar($evaluacion['id'], $titulo, $descripcion);
            $mensaje = '<div class="alerta exito">Evaluación actualizada correctamente.</div>';
        } else {
            $evalModel->crear($modulo_id, $titulo, $descripcion);
            $mensaje = '<div class="alerta exito">Evaluación creada correctamente.</div>';
        }
        // Recargar
        $evaluacion = $evalModel->obtenerPorModulo($modulo_id);
    }
    
    // 2. Agregar Pregunta
    if (isset($_POST['accion']) && $_POST['accion'] === 'agregar_pregunta') {
        $texto = trim($_POST['texto_pregunta']);
        $evalModel->agregarPregunta($evaluacion['id'], $texto);
        $mensaje = '<div class="alerta exito">Pregunta agregada.</div>';
    }

    // 3. Eliminar Pregunta
    if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar_pregunta') {
        $evalModel->eliminarPregunta($_POST['pregunta_id']);
        $mensaje = '<div class="alerta exito">Pregunta eliminada.</div>';
    }

    // 4. Agregar Opción
    if (isset($_POST['accion']) && $_POST['accion'] === 'agregar_opcion') {
        $pregunta_id = $_POST['pregunta_id'];
        $texto = trim($_POST['texto_opcion']);
        $es_correcta = isset($_POST['es_correcta']);
        $evalModel->agregarOpcion($pregunta_id, $texto, $es_correcta);
    }
    
    // 5. Eliminar Opciones (Limpiar)
    if (isset($_POST['accion']) && $_POST['accion'] === 'limpiar_opciones') {
        $evalModel->eliminarOpcionesPregunta($_POST['pregunta_id']);
    }
}

$preguntas = $evaluacion ? $evalModel->obtenerPreguntas($evaluacion['id']) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Evaluaciones</title>
    <?php require_once __DIR__ . '/view/haed.php'; ?>
    <style>
        .pregunta-card { background: #fff; border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; }
        .opciones-list { margin-left: 20px; list-style: none; padding: 0; }
        .opcion-item { padding: 5px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; }
        .correcta { color: green; font-weight: bold; }
        .btn-sm { padding: 2px 8px; font-size: 0.8em; }
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
            <h2>Evaluación del Módulo: <?= htmlspecialchars($modulo['titulo']) ?></h2>
            <a href="gestion_modulos.php?curso_id=<?= $modulo['curso_id'] ?>" class="btn btn-secondary">Volver a Módulos</a>
        </div>

        <?= $mensaje ?>

        <!-- SECCIÓN 1: DATOS GENERALES -->
        <div class="form-card">
            <h3>Configuración General</h3>
            <form method="POST">
                <input type="hidden" name="accion" value="guardar_evaluacion">
                <div class="form-group">
                    <label>Título del Examen</label>
                    <input type="text" name="titulo" class="input" value="<?= $evaluacion['titulo'] ?? 'Examen Final' ?>" required>
                </div>
                <div class="form-group">
                    <label>Descripción / Instrucciones</label>
                    <textarea name="descripcion" class="input" rows="3"><?= $evaluacion['descripcion'] ?? 'Responde las siguientes preguntas.' ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Guardar Configuración</button>
            </form>
        </div>

        <?php if ($evaluacion): ?>
            <!-- SECCIÓN 2: PREGUNTAS -->
            <div style="margin-top: 30px;">
                <h3>Preguntas</h3>
                
                <!-- Formulario Nueva Pregunta -->
                <div class="form-card" style="background: #f9f9f9;">
                    <form method="POST" style="display:flex; gap:10px;">
                        <input type="hidden" name="accion" value="agregar_pregunta">
                        <input type="text" name="texto_pregunta" class="input" placeholder="Escribe una nueva pregunta..." required style="flex:1;">
                        <button type="submit" class="btn btn-nuevo">+ Agregar Pregunta</button>
                    </form>
                </div>

                <!-- Listado de Preguntas -->
                <?php foreach ($preguntas as $pregunta): 
                    $opciones = $evalModel->obtenerOpciones($pregunta['id']);
                ?>
                    <div class="pregunta-card">
                        <div style="display:flex; justify-content:space-between;">
                            <strong><?= htmlspecialchars($pregunta['texto_pregunta']) ?></strong>
                            <form method="POST" onsubmit="return confirm('¿Eliminar pregunta?');">
                                <input type="hidden" name="accion" value="eliminar_pregunta">
                                <input type="hidden" name="pregunta_id" value="<?= $pregunta['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        </div>

                        <!-- Opciones -->
                        <ul class="opciones-list">
                            <?php foreach ($opciones as $opcion): ?>
                                <li class="opcion-item">
                                    <span class="<?= $opcion['es_correcta'] ? 'correcta' : '' ?>">
                                        <?= $opcion['es_correcta'] ? '✅' : '⚪' ?> <?= htmlspecialchars($opcion['texto_opcion']) ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <!-- Agregar Opción -->
                        <form method="POST" style="margin-top:10px; display:flex; gap:5px; align-items:center;">
                            <input type="hidden" name="accion" value="agregar_opcion">
                            <input type="hidden" name="pregunta_id" value="<?= $pregunta['id'] ?>">
                            <input type="text" name="texto_opcion" placeholder="Nueva opción" required style="padding:5px;">
                            <label><input type="checkbox" name="es_correcta"> Correcta</label>
                            <button type="submit" class="btn btn-sm btn-secondary">Agregar</button>
                        </form>
                        
                        <!-- Limpiar Opciones -->
                        <?php if(count($opciones) > 0): ?>
                        <form method="POST" style="margin-top:5px;" onsubmit="return confirm('¿Borrar todas las opciones de esta pregunta?');">
                             <input type="hidden" name="accion" value="limpiar_opciones">
                             <input type="hidden" name="pregunta_id" value="<?= $pregunta['id'] ?>">
                             <button type="submit" style="background:none; border:none; color:red; font-size:0.8em; cursor:pointer;">[Limpiar opciones]</button>
                        </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($preguntas)): ?>
                    <p style="color:#666;">No hay preguntas agregadas aún.</p>
                <?php endif; ?>

            </div>
        <?php endif; ?>

    </main>
</div>
</body>
</html>
