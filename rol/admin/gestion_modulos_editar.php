<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Curso.php';
require_once __DIR__ . '/../../models/Modulo.php';

if ($_SESSION['rol'] !== 'admin') {
    die('Acceso denegado');
}

$moduloModel = new Modulo($pdo);
$cursoModel  = new Curso($pdo);

$id      = $_GET['id'] ?? 0;
$modulo  = $moduloModel->obtenerPorId($id);

if (!$modulo) {
    die('<h2 style="text-align:center;margin-top:100px;color:#721c24;">Módulo no encontrado</h2>');
}

$curso   = $cursoModel->obtenerPorId($modulo['curso_id']);
$mensaje = '';

if ($_POST) {
    $titulo             = trim($_POST['titulo'] ?? '');
    $evaluacion_activa  = isset($_POST['evaluacion_activa']) ? 1 : 0;

    if ($titulo === '') {
        $mensaje = '<div class="alerta error">El título es obligatorio</div>';
    } else {
        if ($moduloModel->actualizar($id, $titulo, $evaluacion_activa)) {
            header("Location: gestion_modulos.php?curso_id={$modulo['curso_id']}&exito=modulo_actualizado");
            exit;
        } else {
            $mensaje = '<div class="alerta error">Error al actualizar</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus LATAM</title>
    <?php require_once __DIR__ . '/view/haed.php'; ?>
</head>

<body>

<div class="contenedor-principal">

    <!-- BARRA LATERAL -->
    <aside class="barra-lateral">
        <div class="logo-avalon">
            <img src="../../img/Avalon_Informatica/Logos/svg/isotype.svg" alt="Logo de Avalon">
        </div>

        <nav class="navegacion">
            <?php require_once __DIR__ . '/view/nav.php'; ?>
        </nav>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-principal">

        <!-- HEADER -->
        <header class="encabezado-superior">
            <?php require_once __DIR__ . '/view/header.php'; ?>
        </header>

        <!-- ADMIN AREA -->
        <div class="admin-main">

            <h2>Editar Módulo</h2>
            <div class="curso-info">
                Curso: <strong><?= htmlspecialchars($curso['titulo']) ?></strong>
            </div>

            <?= $mensaje ?>

            <form method="POST">
                
                <div class="form-group">
                    <label>Título del Módulo *</label>
                    <input
                        type="text"
                        name="titulo"
                        required
                        value="<?= htmlspecialchars($modulo['titulo']) ?>"
                    >
                </div>

                <div class="checkbox-eval">
                    <input
                        type="checkbox"
                        name="evaluacion_activa"
                        id="eval"
                        <?= $modulo['evaluacion_activa'] ? 'checked' : '' ?>
                    >
                    <label for="eval" style="margin:0; cursor:pointer; font-weight:600;">
                        Módulo con evaluación final activa
                    </label>
                </div>

                <div style="display:flex; gap:15px; margin-top:30px;">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    <a href="gestion_modulos.php?curso_id=<?= $modulo['curso_id'] ?>" class="btn btn-secondary">Cancelar</a>
                </div>

            </form>

        </div> <!-- admin-main -->

    </main>

</div>

</body>
</html>
