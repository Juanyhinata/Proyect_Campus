<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Tema.php';

$tema_id = $_GET['tema_id'] ?? 0;
$modulo_id = $_GET['modulo_id'] ?? 0;

$temaModel = new Tema($pdo);
$tema = $temaModel->obtenerPorId($tema_id);

if (!$tema || $tema['modulo_id'] != $modulo_id) die('Error');

if ($tema['tipo'] === 'video' && $tema['video_id']) {
    echo '<iframe width="100%" height="500" src="https://www.youtube.com/embed/'.$tema['video_id'].'" frameborder="0" allowfullscreen></iframe>';
} elseif ($tema['tipo'] === 'pdf' && $tema['pdf_ruta']) {
    echo '<iframe src="../../public/uploads/pdfs/'.$tema['pdf_ruta'].'" width="100%" height="700"></iframe>';
} else {
    echo '<div style="padding:30px;font-size:18px;">'.nl2br(htmlspecialchars($tema['titulo'])).'</div>';
}

// Marcar como visto
$pdo->prepare("INSERT INTO campus.modulo_progreso (usuario_id, modulo_id, porcentaje, completado) 
               VALUES (?, ?, 100, true) ON CONFLICT (usuario_id, modulo_id) DO UPDATE SET porcentaje = 100, completado = true")
    ->execute([$_SESSION['user_id'], $modulo_id]);
    ?>