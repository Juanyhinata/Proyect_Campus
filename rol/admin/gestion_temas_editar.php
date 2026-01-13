<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Curso.php';
require_once __DIR__ . '/../../models/Modulo.php';
require_once __DIR__ . '/../../models/Tema.php';

if ($_SESSION['rol'] !== 'admin') {
    die('Acceso denegado');
}

$temaModel   = new Tema($pdo);
$moduloModel = new Modulo($pdo);

$id   = $_GET['id'] ?? 0;
$tema = $temaModel->obtenerPorId($id);

if (!$tema) die('Tema no encontrado');

$modulo = $moduloModel->obtenerPorId($tema['modulo_id']);
$mensaje = '';

if ($_POST) {
    $titulo = trim($_POST['titulo'] ?? '');
    $tipo   = $_POST['tipo'] ?? $tema['tipo'];

    if ($titulo === '') {
        $mensaje = '<div class="alerta error">El título es obligatorio</div>';
    } else {
        $video_id = $tema['video_id'];
        $pdf_ruta = $tema['pdf_ruta'];
        $duracion_segundos = $tema['duracion_segundos'] ?? 0;

        // Si es VIDEO
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

        // Si es PDF y se sube un archivo nuevo
        if ($tipo === 'pdf' && isset($_FILES['pdf']) && $_FILES['pdf']['error'] === 0) {
            $ext = pathinfo($_FILES['pdf']['name'], PATHINFO_EXTENSION);
            $nuevo = 'pdf_' . time() . '.' . $ext;
            $ruta = '../../public/uploads/pdfs/' . $nuevo;

            if (move_uploaded_file($_FILES['pdf']['tmp_name'], $ruta)) {
                if ($tema['pdf_ruta'] && file_exists('../../public/uploads/pdfs/' . $tema['pdf_ruta'])) {
                    unlink('../../public/uploads/pdfs/' . $tema['pdf_ruta']);
                }
                $pdf_ruta = $nuevo;
            }
        }

        if ($temaModel->actualizar($id, $titulo, $tipo, $video_id, $pdf_ruta, $duracion_segundos)) {
            header("Location: gestion_temas.php?modulo_id={$tema['modulo_id']}&exito=tema_actualizado");
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

    <!-- LATERAL -->
    <aside class="barra-lateral">
        <div class="logo-avalon">
            <img src="../../img/Avalon_Informatica/Logos/svg/isotype.svg" alt="Logo Avalon">
        </div>

        <nav class="navegacion">
            <?php require_once __DIR__ . '/view/nav.php'; ?>
        </nav>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-principal">

        <header class="encabezado-superior">
            <?php require_once __DIR__ . '/view/header.php'; ?>
        </header>

        <div class="header-modulo">
            <h2>Editar Tema del Módulo: <?= htmlspecialchars($modulo['titulo']) ?></h2>
        </div>

        <div class="form-card">
            <h3>Editar tema</h3>

            <?= $mensaje ?>

            <form method="POST" enctype="multipart/form-data">

                <!-- Título -->
                <div class="form-group">
                    <label>Título del tema</label>
                    <input type="text" name="titulo" class="input" value="<?= htmlspecialchars($tema['titulo']) ?>" required>
                </div>

                <!-- Tipo -->
                <div class="form-group">
                    <label>Tipo de contenido</label>
                    <select name="tipo" class="input">
                        <option value="video" <?= $tema['tipo']=='video'?'selected':'' ?>>Video</option>
                        <option value="pdf"   <?= $tema['tipo']=='pdf'?'selected':'' ?>>PDF</option>
                        <option value="texto" <?= $tema['tipo']=='texto'?'selected':'' ?>>Texto</option>
                    </select>
                </div>

                <!-- Si es video -->
                <?php if ($tema['tipo'] == 'video'): ?>
                <div class="form-group">
                    <label>ID o enlace de YouTube</label>
                    <input type="text" name="video_id" class="input" value="<?= $tema['video_id'] ?>">
                </div>
                <div class="form-group">
                    <label>Duración (MM:SS o HH:MM:SS)</label>
                    <?php 
                        // Convertir segundos a formato legible para mostrar
                        $seg = $tema['duracion_segundos'] ?? 0;
                        $horas = floor($seg / 3600);
                        $minutos = floor(($seg % 3600) / 60);
                        $segundos = $seg % 60;
                        $formato = sprintf('%02d:%02d', $minutos, $segundos);
                        if ($horas > 0) {
                            $formato = sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);
                        }
                    ?>
                    <input type="text" name="duracion" class="input" value="<?= $formato ?>" placeholder="Ej: 05:30" pattern="[0-9:]+">
                    <small style="color:#666;">Tiempo total del video para calcular el progreso.</small>
                </div>
                <?php endif; ?>

                <!-- Si es PDF -->
                <?php if ($tema['tipo'] == 'pdf'): ?>
                <div class="form-group">
                    <label>Subir nuevo PDF</label>
                    <input type="file" name="pdf" class="input">
                    <p>Actual: <strong><?= $tema['pdf_ruta'] ?></strong></p>
                </div>
                <?php endif; ?>

                <button class="btn btn-primary" type="submit">Guardar cambios</button>
            </form>
        </div>

        <div style="margin-top:35px;">
            <a href="gestion_temas.php?modulo_id=<?= $tema['modulo_id'] ?>" class="btn btn-secondary">Volver</a>
        </div>

    </main>
</div>

</body>
</html>
