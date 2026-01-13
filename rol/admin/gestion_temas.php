<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Curso.php';
require_once __DIR__ . '/../../models/Modulo.php';
require_once __DIR__ . '/../../models/Tema.php';

if ($_SESSION['rol'] !== 'admin') {
    die('Acceso denegado');
}

$moduloModel = new Modulo($pdo);
$temaModel   = new Tema($pdo);

$modulo_id = $_GET['modulo_id'] ?? 0;
$modulo    = $moduloModel->obtenerPorId($modulo_id);

if (!$modulo) {
    die('<h2 style="text-align:center;margin-top:100px;color:#721c24;">Módulo no encontrado</h2>
         <p style="text-align:center;"><a href="gestion_cursos.php" class="btn btn-primary">Volver a Cursos</a></p>');
}

$temas = $temaModel->listarPorModulo($modulo_id);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus LATAM</title>

    <?php require_once __DIR__ . '/view/haed.php'; ?>

    <style>
        .header-modulo {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
        }
        .tema-item {
            background: white;
            padding: 20px;
            margin: 15px 0;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }
        .tema-item:hover { background: #f8f9fa; transform: translateY(-3px); }
        .handle { font-size: 28px; color: #007bff; cursor: grab; margin-right: 15px; }
        .tipo-icon { font-size: 24px; margin-right: 10px; }
        .tipo-video { color: #ff0000; }
        .tipo-pdf   { color: #f1c40f; }
        .tipo-texto { color: #2ecc71; }
    </style>
</head>

<body>

<div class="contenedor-principal">

    <!-- Lateral -->
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

        <!-- 🔥 AQUÍ ESTABA EL DIV MAL CERRADO — YA ARREGLADO 🤪 -->
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
                               class="btn btn-danger btn-small"
                               onclick="return confirm('¿Eliminar este tema?')">Eliminar</a>
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

    </main>
</div>

<!-- Drag & Drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
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
</script>

</body>
</html>
