<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Curso.php';
require_once __DIR__ . '/../../models/Modulo.php';

if ($_SESSION['rol'] !== 'admin') {
    die('Acceso denegado');
}

$moduloModel = new Modulo($pdo);

$id        = $_GET['id'] ?? 0;
$curso_id  = $_GET['curso_id'] ?? 0;

// Validar módulo
$modulo = $moduloModel->obtenerPorId($id);

if (!$modulo || $modulo['curso_id'] != $curso_id) {
    header("Location: gestion_modulos.php?curso_id=$curso_id&error=modulo_no_encontrado");
    exit;
}

// Si confirma → eliminar y redirigir
if (isset($_GET['confirmar']) && $_GET['confirmar'] === 'si') {
    $moduloModel->eliminar($id);
    header("Location: gestion_modulos.php?curso_id=$curso_id&exito=modulo_eliminado");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Módulo | Campus LATAM</title>
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

        <!-- CONTENIDO DEL CUERPO -->
        <div class="admin-main">

            <div style="max-width:550px;margin:60px auto;background:white;padding:40px;border-radius:16px;box-shadow:0 15px 40px rgba(0,0,0,0.15);text-align:center;">

                <h2 style="color:#c0392b;">¿Eliminar módulo?</h2>

                <p style="font-size:18px;margin:20px 0;">
                    Estás a punto de eliminar el módulo:
                    <br>
                    <strong style="font-size:22px;color:#2c3e50;">
                        <?= htmlspecialchars($modulo['titulo']) ?>
                    </strong>
                </p>

                <p style="color:#b71c1c; font-weight:bold;">
                    También se eliminarán sus temas (videos, PDFs, evaluaciones).  
                    <br>Esta acción no se puede deshacer.
                </p>

                <div style="margin-top:30px; display:flex; gap:20px; justify-content:center;">

                    <a href="gestion_modulos_eliminar.php?id=<?= $id ?>&curso_id=<?= $curso_id ?>&confirmar=si"
                       class="btn btn-danger">
                       Sí, eliminar definitivamente
                    </a>

                    <a href="gestion_modulos.php?curso_id=<?= $curso_id ?>" class="btn btn-secondary">
                        Cancelar
                    </a>

                </div>

            </div>

        </div>
    </main>

</div>

</body>
</html>
