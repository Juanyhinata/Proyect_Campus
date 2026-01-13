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
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Curso.php';
require_once __DIR__ . '/../../models/Usuario.php';

if (!in_array($_SESSION['rol'], ['cliente'])) {
    die('Acceso denegado');
}

require_once __DIR__ . '/../../models/CursoDetalle.php';
require_once __DIR__ . '/../../models/Progreso.php';
require_once __DIR__ . '/../../models/Evaluacion.php'; // Agregado para evaluaciones

$curso_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Intenta encontrar el ID del usuario en las variables de sesión comunes
$usuario_id = $_SESSION['usuario']['id'] 
              ?? $_SESSION['id'] 
              ?? $_SESSION['user_id'] 
              ?? $_SESSION['usuario_id'] 
              ?? null;

if (!$usuario_id) {
    die("Error: No se pudo recuperar el ID del usuario de la sesión.");
}

if (!$curso_id) die('Curso no especificado');

$detalleModel = new CursoDetalle($pdo);
$progresoModel = new Progreso($pdo);
$evalModel = new Evaluacion($pdo); // Instancia de evaluación

$curso = $detalleModel->obtenerCurso($curso_id);
$modulos = $detalleModel->obtenerModulos($curso_id);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($curso['titulo'] ?? 'Detalle del Curso'); ?></title>
    <!-- Head común del sistema -->
    <?php require_once __DIR__ . '/view/haed.php'; ?>
    <style>
        /* Estilos específicos para esta vista que no están en el global */
        .curso-header { margin-bottom: 30px; border-bottom: 2px solid #ddd; padding-bottom: 20px; }
        .modulo-card { background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; overflow: hidden; }
        .modulo-header { background: #fff; padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .modulo-title { font-size: 1.2em; font-weight: bold; margin: 0; }
        
        .progreso-wrapper { width: 200px; text-align: right; } /* Aumentado ancho para texto extra */
        .progress-bar-bg { background: #eee; height: 8px; border-radius: 4px; margin-top: 5px; width: 100%; }
        .progress-bar-fill { height: 100%; border-radius: 4px; background: #3498db; transition: width 0.3s; }
        .completado .progress-bar-fill { background: #2ecc71; }
        
        .tema-list { list-style: none; padding: 0; margin: 0; }
        .tema-item { border-bottom: 1px solid #f9f9f9; }
        .tema-link { display: block; padding: 15px 20px; text-decoration: none; color: #555; transition: background 0.2s; display: flex; align-items: center; }
        .tema-link:hover { background: #f0f4f8; }
        .tema-icon { margin-right: 10px; font-size: 1.2em; }
        .tema-link:active { transform: scale(0.99); }
    </style>
</head>
<body>

<div class="contenedor-principal">

    <!-- =============================================================== -->
    <!-- 🚪 BARRA LATERAL -->
    <!-- =============================================================== -->
    <aside class="barra-lateral">
        <!-- Logo -->
        <div class="logo-avalon">
            <img src="../../img/Avalon_Informatica/Logos/svg/isotype.svg" alt="Logo de Avalon">
        </div>
        <!-- Menú de navegación -->
        <nav class="navegacion">
            <?php require_once __DIR__ . '/view/nav.php'; ?>
        </nav>
    </aside>

    <!-- =============================================================== -->
    <!-- 🖥 CONTENIDO PRINCIPAL -->
    <!-- =============================================================== -->
    <main class="contenido-principal">

        <!-- Header superior -->
        <header class="encabezado-superior">
            <?php require_once __DIR__ . '/view/header.php'; ?>
            <h2>Detalle del Curso</h2>
        </header>

        <div class="curso-header">
            <h1><?php echo htmlspecialchars($curso['titulo']); ?></h1>
            <a href="dashboard.php" class="btn btn-secondary" style="float:right; margin-top:-40px;">⬅ Volver al Dashboard</a>
        </div>

        <?php if (empty($modulos)): ?>
            <p>No hay módulos disponibles en este curso.</p>
        <?php else: ?>
            <?php foreach ($modulos as $modulo): 
                $temas = $detalleModel->obtenerTemas($modulo['id']);
                
                // Calcular progreso ponderado (80% videos, 20% examen)
                $progreso_total = $progresoModel->calcularProgresoTotal($usuario_id, $modulo['id']);
                $porcentaje = $progreso_total['nota_final'];
                $is_completado = $progreso_total['aprobado'];
                
                // Verificar si hay evaluación activa
                $evaluacion = $evalModel->obtenerPorModulo($modulo['id']);
            ?>
                <div class="modulo-card">
                    <div class="modulo-header">
                        <h3 class="modulo-title"><?php echo htmlspecialchars($modulo['titulo']); ?></h3>
                        <div class="progreso-wrapper <?php echo $is_completado ? 'completado' : ''; ?>">
                            <small>Nota Final: <?php echo $porcentaje; ?>%</small>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill" style="width: <?php echo min($porcentaje, 100); ?>%;"></div>
                            </div>
                            <small style="font-size:0.8em; color:#666;">
                                (Videos: <?= $progreso_total['promedio_videos'] ?>% | Examen: <?= $progreso_total['nota_examen'] ?>%)
                            </small>
                        </div>
                    </div>
                    
                    <ul class="tema-list">
                        <?php foreach ($temas as $tema): ?>
                            <li class="tema-item">
                                <a href="ver_tema.php?id=<?php echo $tema['id']; ?>" class="tema-link">
                                    <span class="tema-icon">
                                        <?php echo ($tema['tipo'] === 'video') ? '🎥' : (($tema['tipo'] === 'pdf') ? '📄' : '📝'); ?>
                                    </span>
                                    <?php echo htmlspecialchars($tema['titulo']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        
                        <?php if ($evaluacion): ?>
                            <li class="tema-item" style="background-color: #fff8e1;">
                                <a href="realizar_evaluacion.php?id=<?php echo $evaluacion['id']; ?>" class="tema-link" style="color: #f57c00; font-weight: bold;">
                                    <span class="tema-icon">📝</span>
                                    Evaluación: <?php echo htmlspecialchars($evaluacion['titulo']); ?>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if (empty($temas) && !$evaluacion): ?>
                            <li class="tema-item" style="padding:15px; color:#999;">Sin contenido asignado.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Feedback Section -->
        <?php
        require_once __DIR__ . '/../../models/ComentarioCurso.php';
        $comentarioModel = new ComentarioCurso($pdo);
        $comentarioExistente = $comentarioModel->obtenerComentarioUsuarioCurso($usuario_id, $curso_id);
        ?>

        <div class="feedback-section" style="margin-top: 40px;">
            <div class="feedback-card">
                <h3 class="feedback-title">
                    <?= $comentarioExistente ? '✏️ Editar mi calificación' : '⭐ Calificar este curso' ?>
                </h3>
                <p style="color: #666; margin-bottom: 20px;">
                    Tu opinión nos ayuda a mejorar constantemente. Comparte tu experiencia:
                </p>

                <form id="feedback-form">
                    <input type="hidden" name="curso_id" value="<?= $curso_id ?>">
                    <input type="hidden" name="usuario_id" value="<?= $usuario_id ?>">

                    <!-- Rating Questions -->
                    <div class="rating-questions">
                        <div class="rating-question">
                            <label>Calidad del contenido</label>
                            <div class="star-rating" data-field="calidad_contenido">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star" data-value="<?= $i ?>">☆</span>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="calidad_contenido" value="<?= $comentarioExistente['calidad_contenido'] ?? '' ?>">
                        </div>

                        <div class="rating-question">
                            <label>Facilidad de uso</label>
                            <div class="star-rating" data-field="facilidad_uso">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star" data-value="<?= $i ?>">☆</span>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="facilidad_uso" value="<?= $comentarioExistente['facilidad_uso'] ?? '' ?>">
                        </div>

                        <div class="rating-question">
                            <label>Utilidad práctica</label>
                            <div class="star-rating" data-field="utilidad_practica">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star" data-value="<?= $i ?>">☆</span>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="utilidad_practica" value="<?= $comentarioExistente['utilidad_practica'] ?? '' ?>">
                        </div>
                    </div>

                    <!-- Comment Textarea -->
                    <div class="comment-box">
                        <label for="otros_comentarios">Otros comentarios</label>
                        <textarea 
                            id="otros_comentarios" 
                            name="otros_comentarios" 
                            rows="4" 
                            placeholder="Comparte tu experiencia, sugerencias o cualquier comentario adicional..."
                        ><?= htmlspecialchars($comentarioExistente['otros_comentarios'] ?? '') ?></textarea>
                    </div>

                    <div id="feedback-message"></div>
                    <button type="submit" class="btn btn-primary">
                        <?= $comentarioExistente ? 'Actualizar mi calificación' : 'Enviar calificación' ?>
                    </button>
                </form>
            </div>
        </div>

    </main>
</div>

<style>
    .feedback-section { margin-top: 40px; }
    .feedback-card {
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .feedback-title {
        font-size: 1.5em;
        margin-bottom: 10px;
        color: #2c3e50;
    }
    .rating-questions {
        display: grid;
        gap: 20px;
        margin-bottom: 25px;
    }
    .rating-question label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #34495e;
    }
    .star-rating {
        font-size: 2em;
        cursor: pointer;
        user-select: none;
    }
    .star-rating .star {
        color: #ddd;
        transition: color 0.2s;
    }
    .star-rating .star.active,
    .star-rating .star:hover,
    .star-rating .star:hover ~ .star {
        color: #f39c12;
    }
    .comment-box {
        margin-bottom: 20px;
    }
    .comment-box label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #34495e;
    }
    .comment-box textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-family: inherit;
        font-size: 0.95em;
        resize: vertical;
    }
    #feedback-message {
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 5px;
        display: none;
    }
    #feedback-message.success {
        display: block;
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    #feedback-message.error {
        display: block;
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>

<script>
// Star rating functionality
document.querySelectorAll('.star-rating').forEach(ratingDiv => {
    const field = ratingDiv.dataset.field;
    const input = document.querySelector(`input[name="${field}"]`);
    const stars = ratingDiv.querySelectorAll('.star');
    
    // Load existing rating
    const existingValue = parseInt(input.value);
    if (existingValue) {
        stars.forEach((star, index) => {
            if (index < existingValue) {
                star.classList.add('active');
                star.textContent = '★';
            }
        });
    }
    
    // Click handler
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const value = this.dataset.value;
            input.value = value;
            
            // Update visual
            stars.forEach((s, index) => {
                if (index < value) {
                    s.classList.add('active');
                    s.textContent = '★';
                } else {
                    s.classList.remove('active');
                    s.textContent = '☆';
                }
            });
        });
        
        // Hover effect
        star.addEventListener('mouseenter', function() {
            const value = this.dataset.value;
            stars.forEach((s, index) => {
                if (index < value) {
                    s.textContent = '★';
                } else {
                    s.textContent = '☆';
                }
            });
        });
    });
    
    // Reset on mouse leave
    ratingDiv.addEventListener('mouseleave', function() {
        const currentValue = parseInt(input.value) || 0;
        stars.forEach((s, index) => {
            if (index < currentValue) {
                s.textContent = '★';
            } else {
                s.textContent = '☆';
            }
        });
    });
});

// Form submission
document.getElementById('feedback-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const messageDiv = document.getElementById('feedback-message');
    
    // Validate at least one rating
    const calidad = formData.get('calidad_contenido');
    const facilidad = formData.get('facilidad_uso');
    const utilidad = formData.get('utilidad_practica');
    
    if (!calidad && !facilidad && !utilidad) {
        messageDiv.className = 'error';
        messageDiv.textContent = 'Por favor, califica al menos un aspecto del curso.';
        return;
    }
    
    try {
        const response = await fetch('guardar_comentario.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            messageDiv.className = 'success';
            messageDiv.textContent = '✓ ' + result.message;
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        } else {
            messageDiv.className = 'error';
            messageDiv.textContent = '✗ ' + result.message;
        }
    } catch (error) {
        messageDiv.className = 'error';
        messageDiv.textContent = '✗ Error al enviar la calificación. Inténtalo nuevamente.';
    }
});
</script>

</body>
</html>