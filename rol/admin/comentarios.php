<?php
// ======================================================================
// 🔐 AUTENTICACIÓN DE SESIÓN
// ======================================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged']) && !isset($_SESSION['logged_in'])) {
    header('Location: ../../public/login.php?error=Debes iniciar sesión');
    exit;
}

if ($_SESSION['rol'] !== 'admin') {
    die('Acceso denegado');
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/ComentarioCurso.php';

$comentarioModel = new ComentarioCurso($pdo);

// Obtener datos para el dashboard
$estadisticas = $comentarioModel->obtenerEstadisticas();
$estadisticasPorRol = $comentarioModel->obtenerEstadisticasPorRol();
$resumenCursos = $comentarioModel->obtenerResumenPorCursos();
$ultimosComentarios = $comentarioModel->obtenerUltimosComentarios(15);

// Convertir estadísticas por rol a array asociativo para fácil acceso
$statsByRol = [];
foreach ($estadisticasPorRol as $stat) {
    $statsByRol[$stat['rol']] = $stat;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comentarios y Calificaciones - Admin</title>
    <?php require_once __DIR__ . '/view/haed.php'; ?>
    <style>
        /* Dashboard styles */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #3498db;
        }
        
        .stat-card.highlight {
            border-left-color: #2ecc71;
        }
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 0.9em;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-value {
            font-size: 2.5em;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }
        
        .stat-subtitle {
            font-size: 0.85em;
            color: #7f8c8d;
        }
        
        /* Stars display */
        .stars {
            color: #f39c12;
            font-size: 1.2em;
            margin: 5px 0;
        }
        
        /* Table styles */
        .data-table {
            width: 100%;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .data-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            background: #34495e;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .data-table tr:hover {
            background: #f8f9fa;
        }
        
        /* Comments section */
        .comment-item {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border-left: 3px solid #3498db;
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .comment-user {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .comment-role {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75em;
            text-transform: uppercase;
            font-weight: bold;
            margin-left: 10px;
        }
        
        .comment-role.agente {
            background: #3498db;
            color: white;
        }
        
        .comment-role.cliente {
            background: #2ecc71;
            color: white;
        }
        
        .comment-date {
            font-size: 0.85em;
            color: #7f8c8d;
        }
        
        .comment-course {
            font-size: 0.9em;
            color: #34495e;
            margin-bottom: 10px;
        }
        
        .comment-ratings {
            display: flex;
            gap: 20px;
            margin: 10px 0;
            flex-wrap: wrap;
        }
        
        .rating-item {
            font-size: 0.85em;
        }
        
        .rating-label {
            color: #7f8c8d;
            display: block;
        }
        
        .comment-text {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
            color: #2c3e50;
            line-height: 1.6;
        }
        
        .section-title {
            font-size: 1.5em;
            margin: 30px 0 20px 0;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #95a5a6;
            font-style: italic;
        }
    </style>
</head>
<body>

<div class="contenedor-principal">
    
    <!-- Sidebar -->
    <aside class="barra-lateral">
        <div class="logo-avalon">
            <img src="../../img/Avalon_Informatica/Logos/svg/isotype.svg" alt="Logo de Avalon">
        </div>
        <nav class="navegacion">
            <?php require_once __DIR__ . '/view/nav.php'; ?>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="contenido-principal">
        
        <header class="encabezado-superior">
            <?php require_once __DIR__ . '/view/header.php'; ?>
            <h2>Comentarios y Calificaciones</h2>
        </header>
        
        <!-- Global Statistics -->
        <div class="stats-grid">
            <div class="stat-card highlight">
                <h3>Total Comentarios</h3>
                <div class="stat-value"><?= $estadisticas['total_comentarios'] ?? 0 ?></div>
                <div class="stat-subtitle">
                    <?= $estadisticas['con_comentarios_texto'] ?? 0 ?> con texto
                </div>
            </div>
            
            <div class="stat-card">
                <h3>Promedio General</h3>
                <div class="stat-value"><?= number_format($estadisticas['promedio_general'] ?? 0, 1) ?></div>
                <div class="stars">
                    <?php
                    $avg = $estadisticas['promedio_general'] ?? 0;
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= round($avg) ? '★' : '☆';
                    }
                    ?>
                </div>
                <div class="stat-subtitle">de 5 estrellas</div>
            </div>
            
            <div class="stat-card">
                <h3>Calidad Contenido</h3>
                <div class="stat-value"><?= number_format($estadisticas['avg_calidad'] ?? 0, 1) ?></div>
                <div class="stat-subtitle">Promedio</div>
            </div>
            
            <div class="stat-card">
                <h3>Facilidad de Uso</h3>
                <div class="stat-value"><?= number_format($estadisticas['avg_facilidad'] ?? 0, 1) ?></div>
                <div class="stat-subtitle">Promedio</div>
            </div>
            
            <div class="stat-card">
                <h3>Utilidad Práctica</h3>
                <div class="stat-value"><?= number_format($estadisticas['avg_utilidad'] ?? 0, 1) ?></div>
                <div class="stat-subtitle">Promedio</div>
            </div>
            
            <?php if (isset($statsByRol['cliente'])): ?>
            <div class="stat-card">
                <h3>Comentarios Clientes</h3>
                <div class="stat-value"><?= $statsByRol['cliente']['total'] ?></div>
                <div class="stat-subtitle">Promedio: <?= $statsByRol['cliente']['promedio'] ?> ★</div>
            </div>
            <?php endif; ?>
            
            <?php if (isset($statsByRol['agente'])): ?>
            <div class="stat-card">
                <h3>Comentarios Agentes</h3>
                <div class="stat-value"><?= $statsByRol['agente']['total'] ?></div>
                <div class="stat-subtitle">Promedio: <?= $statsByRol['agente']['promedio'] ?> ★</div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Course Summary Table -->
        <h3 class="section-title">📊 Resumen por Curso</h3>
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Total Feedbacks</th>
                        <th>Promedio General</th>
                        <th>Calidad</th>
                        <th>Facilidad</th>
                        <th>Utilidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($resumenCursos)): ?>
                        <tr><td colspan="6" class="no-data">No hay cursos con comentarios</td></tr>
                    <?php else: ?>
                        <?php foreach ($resumenCursos as $curso): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($curso['curso_titulo']) ?></strong></td>
                                <td><?= $curso['total_feedbacks'] ?></td>
                                <td>
                                    <strong><?= number_format($curso['promedio_general'] ?? 0, 1) ?></strong>
                                    <span class="stars" style="font-size: 0.9em; margin-left: 5px;">
                                        <?php
                                        $avg = $curso['promedio_general'] ?? 0;
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= round($avg) ? '★' : '☆';
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td><?= number_format($curso['avg_calidad'] ?? 0, 1) ?></td>
                                <td><?= number_format($curso['avg_facilidad'] ?? 0, 1) ?></td>
                                <td><?= number_format($curso['avg_utilidad'] ?? 0, 1) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Recent Comments -->
        <h3 class="section-title">💬 Comentarios Recientes</h3>
        <?php if (empty($ultimosComentarios)): ?>
            <div class="no-data">No hay comentarios con texto todavía</div>
        <?php else: ?>
            <?php foreach ($ultimosComentarios as $comentario): ?>
                <div class="comment-item">
                    <div class="comment-header">
                        <div>
                            <span class="comment-user"><?= htmlspecialchars($comentario['usuario_nombre']) ?></span>
                            <span class="comment-role <?= $comentario['usuario_rol'] ?>">
                                <?= $comentario['usuario_rol'] ?>
                            </span>
                        </div>
                        <span class="comment-date">
                            <?= date('d/m/Y H:i', strtotime($comentario['creado_en'])) ?>
                        </span>
                    </div>
                    
                    <div class="comment-course">
                        📚 <strong><?= htmlspecialchars($comentario['curso_titulo']) ?></strong>
                    </div>
                    
                    <div class="comment-ratings">
                        <div class="rating-item">
                            <span class="rating-label">Calidad:</span>
                            <strong><?= $comentario['calidad_contenido'] ?? 'N/A' ?></strong> ★
                        </div>
                        <div class="rating-item">
                            <span class="rating-label">Facilidad:</span>
                            <strong><?= $comentario['facilidad_uso'] ?? 'N/A' ?></strong> ★
                        </div>
                        <div class="rating-item">
                            <span class="rating-label">Utilidad:</span>
                            <strong><?= $comentario['utilidad_practica'] ?? 'N/A' ?></strong> ★
                        </div>
                    </div>
                    
                    <?php if (!empty($comentario['otros_comentarios'])): ?>
                        <div class="comment-text">
                            <?= nl2br(htmlspecialchars($comentario['otros_comentarios'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
    </main>
</div>

</body>
</html>
