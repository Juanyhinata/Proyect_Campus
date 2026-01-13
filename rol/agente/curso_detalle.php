<?php
// ===========================================================
// 🔐 AUTENTICACIÓN Y MODELOS
// ===========================================================
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Curso.php';
require_once __DIR__ . '/../../models/Modulo.php';
require_once __DIR__ . '/../../models/Tema.php';

// Solo roles permitidos
if (!in_array($_SESSION['rol'], ['cliente', 'agente'])) {
    die('Acceso denegado');
}

// Instancias de modelos
$cursoModel  = new Curso($pdo);
$moduloModel = new Modulo($pdo);
$temaModel   = new Tema($pdo);

// ===========================================================
// 📘 OBTENER CURSO
// ===========================================================
$curso_id = $_GET['id'] ?? 0;
$curso = $cursoModel->obtenerPorId($curso_id);

// Validar: curso existe + usuario está inscrito
$inscrito = $pdo->prepare("
    SELECT 1 
    FROM campus.curso_usuario 
    WHERE usuario_id = ? AND curso_id = ?
");
$inscrito->execute([$_SESSION['user_id'], $curso_id]);

if (!$curso || !$inscrito->fetch()) {
    die('Curso no encontrado o no tienes acceso');
}

// ===========================================================
// 📚 LISTAR MÓDULOS + CÁLCULO DE PROGRESO
// ===========================================================
$modulos = $moduloModel->listarPorCurso($curso_id);

$progreso_total = 0;
$modulos_completados = 0;

foreach ($modulos as &$modulo) {

    // Temas del módulo
    $temas = $temaModel->listarPorModulo($modulo['id']);
    $modulo['temas'] = $temas;

    $temas_total = count($temas);
    $temas_vistos = 0;

    // Contar temas vistos
    foreach ($temas as $tema) {
        $visto = $pdo->prepare("
            SELECT 1 
            FROM campus.modulo_progreso
            WHERE usuario_id = ? AND modulo_id = ? AND porcentaje = 100
        ");
        $visto->execute([$_SESSION['user_id'], $modulo['id']]);

        if ($visto->fetch()) {
            $temas_vistos++;
        }
    }

    // Progreso del módulo
    $modulo['progreso'] = $temas_total > 0 
        ? round(($temas_vistos / $temas_total) * 100) 
        : 0;

    if ($modulo['progreso'] == 100) {
        $modulos_completados++;
    }
}

// Progreso global del curso
$progreso_total = count($modulos) > 0 
    ? round(($modulos_completados / count($modulos)) * 100) 
    : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Título dinámico -->
    <title>Campus LATAM - <?= htmlspecialchars($_SESSION['nombre'] ?? 'Alumno'); ?></title>

    <!-- Head común del sistema -->
    <?php require_once __DIR__ . '/view/haed.php'; ?>
</head>

<body>
<div class="contenedor-principal">

    <!-- =============================================================== -->
    <!-- 🚪 BARRA LATERAL -->
    <!-- =============================================================== -->
    <aside class="barra-lateral">

        <!-- Logo del sistema -->
        <div class="logo-avalon">
            <img src="../../img/Avalon_Informatica/Logos/svg/isotype.svg" alt="Logo de Avalon">
        </div>

        <!-- Menú lateral -->
        <nav class="navegacion">
            <?php require_once __DIR__ . '/view/nav.php'; ?>
        </nav>
    </aside>

    <!-- =============================================================== -->
    <!-- 🖥 CONTENIDO PRINCIPAL -->
    <!-- =============================================================== -->
    <main class="contenido-principal">

        <!-- --------------------------------------------- -->
        <!-- 🧠 Información del Curso -->
        <!-- --------------------------------------------- -->
        <div class="curso-header">
            <h1><?= htmlspecialchars($curso['titulo']) ?></h1>
            <p><?= htmlspecialchars($curso['descripcion'] ?? '') ?></p>

            <div style="margin-top:20px;">
                <strong>Progreso general: <?= $progreso_total ?>%</strong>
                <div class="progreso-barra">
                    <div class="progreso-fill" style="width:<?= $progreso_total ?>%"></div>
                </div>
            </div>
        </div>

        <!-- --------------------------------------------- -->
        <!-- 📦 LISTA DE MÓDULOS -->
        <!-- --------------------------------------------- -->
        <?php foreach ($modulos as $modulo): ?>
            <div class="modulo-card">

                <!-- Encabezado del módulo -->
                <div class="modulo-header" onclick="toggleModulo(this)">
                    <div>
                        <?= htmlspecialchars($modulo['titulo']) ?>
                        <?php if ($modulo['evaluacion_activa']): ?>
                            <span class="evaluacion-badge">Evaluación Final</span>
                        <?php endif; ?>
                    </div>

                    <div>
                        Progreso: <?= $modulo['progreso'] ?>%
                        <span class="flecha">↓</span>
                    </div>
                </div>

                <!-- Contenido del módulo -->
                <div class="modulo-contenido" style="display:none;">

                    <!-- Temas del módulo -->
                    <?php foreach ($modulo['temas'] as $tema): ?>

                        <?php
                        $visto = $pdo->prepare("
                            SELECT completado 
                            FROM campus.modulo_progreso
                            WHERE usuario_id = ? AND modulo_id = ?
                        ");
                        $visto->execute([$_SESSION['user_id'], $modulo['id']]);
                        $visto = $visto->fetchColumn() ?: false;
                        ?>

                        <div class="tema-link <?= $visto ? 'tema-visto' : '' ?>">
                            <div class="tema-titulo">

                                <!-- Tipo de tema -->
                                <?php if ($tema['tipo'] === 'video'): ?>
                                    🎬 Video de YouTube
                                <?php elseif ($tema['tipo'] === 'pdf'): ?>
                                    📄 PDF
                                <?php else: ?>
                                    📝 Texto
                                <?php endif; ?>

                                <strong><?= htmlspecialchars($tema['titulo']) ?></strong>
                            </div>

                            <div>
                                <?php if ($visto): ?>
                                    <span class="visto">Visto</span>
                                <?php else: ?>
                                    <button 
                                        onclick="marcarVisto(<?= $modulo['id'] ?>, this)" 
                                        class="btn btn-primary btn-small">
                                        Marcar como visto
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php endforeach; ?>

                    <!-- Evaluación final -->
                    <?php if ($modulo['evaluacion_activa'] && $modulo['progreso'] == 100): ?>
                        <div class="evaluacion-box listo">
                            <h3>Evaluación Final del Módulo</h3>
                            <a href="evaluacion.php?modulo_id=<?= $modulo['id'] ?>" 
                               class="btn btn-danger btn-large">
                                Rendir Evaluación
                            </a>
                        </div>

                    <?php elseif ($modulo['evaluacion_activa']): ?>
                        <div class="evaluacion-box bloqueo">
                            Completa todos los temas para desbloquear la evaluación
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        <?php endforeach; ?>
        
        <!-- Feedback Section -->
        <?php
        $usuario_id = $_SESSION['user_id'];
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

<!-- =============================================================== -->
<!-- 📌 Scripts -->
<!-- =============================================================== -->
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
function toggleModulo(el) {
    const contenido = el.nextElementSibling;
    const flecha = el.querySelector('.flecha');

    const visible = contenido.style.display === 'block';
    contenido.style.display = visible ? 'none' : 'block';
    flecha.textContent = visible ? '↓' : '↑';
}

function marcarVisto(modulo_id, btn) {
    fetch('marcar_visto.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ modulo_id })
    }).then(() => {
        btn.parentElement.innerHTML = '<span class="visto">Visto</span>';
        location.reload();
    });
}

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

