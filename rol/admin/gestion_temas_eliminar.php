<?php
// ----------------------------------------------------------------------
// gestion_temas_eliminar.php
// ----------------------------------------------------------------------
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Curso.php';
require_once __DIR__ . '/../../models/Modulo.php';
require_once __DIR__ . '/../../models/Tema.php';

// Control de acceso
if ($_SESSION['rol'] !== 'admin') {
    die('Acceso denegado');
}

$moduloModel = new Modulo($pdo);
$temaModel   = new Tema($pdo);

// ------------------------------------------------------------------
// Lógica de Eliminación y Confirmación
// ------------------------------------------------------------------

$id_tema = $_GET['id'] ?? 0;
$modulo_id = $_GET['modulo_id'] ?? 0;
$tema_a_eliminar = null; // Variable para almacenar el tema si se requiere confirmación

if ($id_tema > 0) {
    // 1. OBTENER DATOS DEL TEMA para la confirmación
    $tema_a_eliminar = $temaModel->obtenerPorId($id_tema);
    
    if (!$tema_a_eliminar) {
        // Tema no encontrado, redireccionar para evitar errores
        header("Location: gestion_temas_eliminar.php?modulo_id=" . $modulo_id);
        exit;
    }
    
    // 2. PROCESAR CONFIRMACIÓN
    if (isset($_GET['confirmar']) && $_GET['confirmar'] === 'si') {
        // El usuario CONFIRMÓ la eliminación
        $temaModel->eliminar($id_tema);

        // Redireccionar a la lista limpia
        header("Location: gestion_temas_eliminar.php?modulo_id=" . $modulo_id);
        exit;
    }
    // Si llega aquí sin confirmar, se mantiene $tema_a_eliminar para mostrar la confirmación en el PHP.
}

// ------------------------------------------------------------------
// Lógica para mostrar la lista (si no se está eliminando)
// ------------------------------------------------------------------

$modulo    = $moduloModel->obtenerPorId($modulo_id);

if (!$modulo) {
    die('<h2 style="text-align:center;margin-top:100px;color:#721c24;">Módulo no encontrado</h2>
         <p style="text-align:center;"><a href="gestion_cursos.php" class="btn btn-primary">Volver a Cursos</a></p>');
}

// Solo listamos los temas si no estamos en modo confirmación de eliminación
if (!$tema_a_eliminar) {
    $temas = $temaModel->listarPorModulo($modulo_id);
} else {
    // Si estamos en modo confirmación, no cargamos la lista de temas completa para optimizar
    $temas = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus LATAM</title>

    <?php require_once __DIR__ . '/view/haed.php'; ?>

    <style>
        /* Estilos CSS  */
    </style>
</head>

<body>

<div class="contenedor-principal">

    <aside class="barra-lateral">
        <div class="logo-avalon">
            <img src="../../img/Avalon_Informatica/Logos/svg/isotype.svg" alt="Logo de Avalon">
        </div>

        <nav class="navegacion">
            <?php require_once __DIR__ . '/view/nav.php'; ?>
        </nav>
    </aside>

    <main class="contenido-principal">

        <header class="encabezado-superior">
            <?php require_once __DIR__ . '/view/header.php'; ?>
        </header>

        <?php if ($tema_a_eliminar): ?>
            
            <div style="max-width:550px;margin:60px auto;background:white;padding:40px;border-radius:16px;box-shadow:0 15px 40px rgba(0,0,0,0.15);text-align:center;">
                
                <h2 style="color:#c0392b;">¿Eliminar Tema?</h2>

                <p style="font-size:18px;margin:20px 0;">
                    Estás a punto de eliminar el tema:
                    <br>
                    <strong style="font-size:22px;color:#2c3e50;">
                        <?= htmlspecialchars($tema_a_eliminar['titulo']) ?>
                    </strong>
                </p>

                <p style="color:#b71c1c; font-weight:bold;">
                    Esta acción no se puede deshacer.
                </p>

                <div style="margin-top:30px; display:flex; gap:20px; justify-content:center;">

                    <a href="gestion_temas_eliminar.php?id=<?= $tema_a_eliminar['id'] ?>&modulo_id=<?= $modulo_id ?>&confirmar=si"
                       class="btn btn-danger">
                        Sí, eliminar definitivamente
                    </a>

                    <a href="gestion_temas_eliminar.php?modulo_id=<?= $modulo_id ?>" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>
            </div>

        <?php else: ?>
        <div class="header-modulo">
            <h2><?= htmlspecialchars($modulo['titulo']) ?></h2>

            <?php if ($modulo['evaluacion_activa']): ?>
                <p style="color:#28a745;font-weight:bold;">Evaluación final activa</p>
            <?php endif; ?>

            <a href="gestion_temas_crear.php?modulo_id=<?= $modulo_id ?>" class="btn btn-nuevo" style="margin-top:15px;">
                + Nuevo Tema
            </a>
        </div>

        <?php if (empty($temas)): ?>
            <div style="text-align:center;padding:60px;color:#666;">
                <h3>Aún no hay temas en este módulo</h3>
                <p>¡Empecemos creando el primer contenido!</p>
                <a href="gestion_temas_crear.php?modulo_id=<?= $modulo_id ?>" class="btn btn-nuevo">+ Agregar primer tema</a>
            </div>

        <?php else: ?>
            <div id="temas-lista">
                <?php foreach ($temas as $tema): ?>
                    <div class="tema-item" data-id="<?= $tema['id'] ?>">

                        <div style="display:flex;align-items:center;">
                            <span class="handle">☰</span>
                            <span class="tipo-icon tipo-<?= $tema['tipo'] ?>">
                                <?= $tema['tipo'] === 'video' ? '🎥 Video' : ($tema['tipo'] === 'pdf' ? '📄 PDF' : '📝 Texto') ?>
                            </span>
                            <strong><?= htmlspecialchars($tema['titulo']) ?></strong>
                        </div>

                        <div>
                            <a href="gestion_temas_editar.php?id=<?= $tema['id'] ?>&modulo_id=<?= $modulo_id ?>" 
                               class="btn btn-primary btn-small">Editar</a>

                            <a href="gestion_temas_eliminar.php?id=<?= $tema['id'] ?>&modulo_id=<?= $modulo_id ?>" 
                               class="btn btn-danger btn-small">Eliminar</a>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div style="text-align:center;margin-top:40px;">
            <a href="gestion_modulos.php?curso_id=<?= $modulo['curso_id'] ?>" class="btn btn-secondary">
                Volver a Módulos
            </a>
        </div>
        <?php endif; ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    <?php if (!$tema_a_eliminar): ?>
    new Sortable(document.getElementById('temas-lista'), {
        handle: '.handle',
        animation: 150,
        onEnd: function () {
            const temas = [];
            document.querySelectorAll('.tema-item').forEach((el, i) => {
                temas.push({ id: el.dataset.id, orden: i });
            });

            fetch('ordenar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    modulo_id: <?= $modulo_id ?>, 
                    orden: temas.map(t => t.id) 
                })
            });
        }
    });
    <?php endif; ?>
</script>

</body>
</html>