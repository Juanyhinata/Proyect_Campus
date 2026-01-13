<?php
// includes/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si NO está logged y NO está logged_in → no entra
if (!isset($_SESSION['logged']) && !isset($_SESSION['logged_in'])) {
    header('Location: ../../public/login.php?error=Debes iniciar sesión');
    exit;
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Curso.php';
require_once __DIR__ . '/../../models/Modulo.php';

if ($_SESSION['rol'] !== 'admin') die('Acceso denegado');

$cursoModel = new Curso($pdo);
$moduloModel = new Modulo($pdo);

$curso_id = $_GET['curso_id'] ?? 0;
$curso = $cursoModel->obtenerPorId($curso_id);

if (!$curso) {
    header('Location: gestion_cursos.php?error=curso_no_encontrado');
    exit;
}

$modulos = $moduloModel->listarPorCurso($curso_id);
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

        <!-- HEADER SUPERIOR -->
        <header class="encabezado-superior">
            <?php require_once __DIR__ . '/view/header.php'; ?>
        </header>

        <!-- ZONA ADMIN -->
        <main class="admin-main">

            <div class="admin-cards">
                <h2>Módulos del curso</h2>
                <p style="color:#666;">
                    <strong><?= htmlspecialchars($curso['titulo']) ?></strong>
                </p>
            </div>

            <a href="gestion_modulos_crear.php?curso_id=<?= $curso_id ?>" class="btn btn-nuevo">+ Nuevo Módulo</a>

            <div id="modulos-lista">
                <?php foreach ($modulos as $modulo): ?>
                    <div class="modulo-item" data-id="<?= $modulo['id'] ?>">

                        <div style="display:flex; align-items:center;">
                            <span class="handle">☰</span>
                            <strong><?= htmlspecialchars($modulo['titulo']) ?></strong>

                            <?php if ($modulo['evaluacion_activa']): ?>
                                <span class="eval-activa" style="margin-left:10px;">
                                    (Evaluación activa)
                                </span>
                            <?php endif; ?>
                        </div>

                        <div>
                            <a href="gestion_modulos_editar.php?id=<?= $modulo['id'] ?>&curso_id=<?= $curso_id ?>"
                               class="btn btn-primary btn-small">Editar</a>

                            <a href="gestion_modulos_eliminar.php?id=<?= $modulo['id'] ?>&curso_id=<?= $curso_id ?>"
                               class="btn btn-danger btn-small"
                               onclick="return confirm('¿Eliminar este módulo y sus temas?')">Eliminar</a>

                            <a href="gestion_temas.php?modulo_id=<?= $modulo['id'] ?>"
                               class="btn btn-success btn-small">Temas</a>

                            <a href="gestion_evaluaciones.php?modulo_id=<?= $modulo['id'] ?>"
                               class="btn btn-warning btn-small" style="background-color: #ff9800; border-color: #f57c00;">Evaluación</a>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

        </main> <!-- cierre admin-main -->

    </main> <!-- cierre contenido-principal -->

</div> <!-- cierre contenedor-principal -->

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
    new Sortable(document.getElementById('modulos-lista'), {
        handle: '.handle',
        animation: 150,

        onEnd: function () {
            const orden = [];

            document.querySelectorAll('.modulo-item').forEach((el, index) => {
                orden.push({
                    id: el.dataset.id,
                    orden: index + 1    // evita orden 0, más limpio
                });
            });

            fetch('ordenar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    curso_id: <?= $curso_id ?>,
                    orden: orden
                })
            })
            .then(r => r.text())
            .then(console.log)
            .catch(console.error);
        }
    });
</script>


</body>
</html>
