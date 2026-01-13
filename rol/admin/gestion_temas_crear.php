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
$temaModel = new Tema($pdo);

$modulo_id = $_GET['modulo_id'] ?? 0;
$modulo = $moduloModel->obtenerPorId($modulo_id);

if (!$modulo) die('Módulo no encontrado');

$mensaje = '';

if ($_POST) {
    $titulo = trim($_POST['titulo'] ?? '');
    $tipo = $_POST['tipo'] ?? 'video';

    if ($titulo === '') {
        $mensaje = '<div class="alerta error">El título es obligatorio</div>';
    } else {

        $video_id = null;
        $pdf_ruta = null;
        $duracion_segundos = 0;

        // VIDEO
        if ($tipo === 'video') {
            if (!empty($_POST['video_id'])) {
                preg_match('/v=([^&]+)/', $_POST['video_id'], $matches);
                $video_id = $matches[1] ?? $_POST['video_id'];
            }
            
            // Procesar duración (MM:SS o HH:MM:SS)
            $duracion_str = trim($_POST['duracion'] ?? '');
            if ($duracion_str) {
                $partes = array_reverse(explode(':', $duracion_str));
                $segundos = 0;
                $multiplicador = 1;
                foreach ($partes as $parte) {
                    $segundos += (int)$parte * $multiplicador;
                    $multiplicador *= 60;
                }
                $duracion_segundos = $segundos;
            }
        }

        // PDF
        if ($tipo === 'pdf' && isset($_FILES['pdf']) && $_FILES['pdf']['error'] === 0) {
            $ext = pathinfo($_FILES['pdf']['name'], PATHINFO_EXTENSION);
            $nuevo = 'pdf_' . time() . '.' . $ext;
            $ruta = '../../public/uploads/pdfs/' . $nuevo;

            if (move_uploaded_file($_FILES['pdf']['tmp_name'], $ruta)) {
                $pdf_ruta = $nuevo;
            }
        }

        if ($temaModel->crear($modulo_id, $titulo, $tipo, $video_id, $pdf_ruta, $duracion_segundos)) {
            header("Location: gestion_temas.php?modulo_id=$modulo_id&exito=tema_creado");
            exit;
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

        <!-- FORMULARIO -->
        <div class="formulario-tema">
            <h2>Nuevo Tema</h2>
            <p>Módulo: <strong><?= htmlspecialchars($modulo['titulo']) ?></strong></p>

            <?= $mensaje ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label>Título del tema *</label>
                    <input type="text" name="titulo" required placeholder="Ej: Introducción al sistema">
                </div>

                <div class="form-group">
                    <label>Tipo de contenido</label>
                    <div class="tipo-selector">
                        <div class="tipo-btn active" onclick="seleccionarTipo(event, 'video')">Video de YouTube</div>
                        <div class="tipo-btn" onclick="seleccionarTipo(event, 'pdf')">PDF</div>
                        <div class="tipo-btn" onclick="seleccionarTipo(event, 'texto')">Texto</div>
                    </div>
                    <input type="hidden" name="tipo" id="tipo" value="video">
                </div>

                <div id="contenido-video">
                    <div class="form-group">
                        <label>URL de YouTube</label>
                        <input type="text" name="video_id" placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                    <div class="form-group">
                        <label>Duración (MM:SS o HH:MM:SS)</label>
                        <input type="text" name="duracion" placeholder="Ej: 05:30" pattern="[0-9:]+">
                        <small style="color:#666;">Tiempo total del video para calcular el progreso.</small>
                    </div>
                </div>

                <div id="contenido-pdf" style="display:none;">
                    <label>Subir PDF</label>
                    <input type="file" name="pdf" accept=".pdf">
                </div>

                <div style="margin-top:30px;">
                    <button type="submit" class="btn btn-nuevo">Crear Tema</button>
                    <a href="gestion_temas.php?modulo_id=<?= $modulo_id ?>" class="btn btn-secondary">Cancelar</a>
                </div>

            </form>
        </div>

    </main>
</div>


<script>
function seleccionarTipo(event, tipo) {
    document.getElementById('tipo').value = tipo;

    document.querySelectorAll('.tipo-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');

    document.getElementById('contenido-video').style.display = (tipo === 'video') ? 'block' : 'none';
    document.getElementById('contenido-pdf').style.display = (tipo === 'pdf') ? 'block' : 'none';
}
</script>

</body>
</html>
