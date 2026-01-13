<?php
// ======================================================================
// 🔐 AUTENTICACIÓN DE SESIÓN
// ======================================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar login (soporta ambas variables)
if (!isset($_SESSION['logged']) && !isset($_SESSION['logged_in'])) {
    header('Location: ../../public/login.php?error=Debes iniciar sesión');
    exit;
}

// ======================================================================
// 📦 INCLUDES Y MODELOS BÁSICOS
// ======================================================================
// Rutas relativas al archivo actual (rol/cliente/curso_detalle.php)
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Usuario.php';

// === INCLUSIÓN DE MODELOS DE CURSO ===
require_once __DIR__ . '/../../models/CursoDetalle.php'; 
require_once __DIR__ . '/../../models/Progreso.php';    
// ===================================

// Validar rol permitido
if (!in_array($_SESSION['rol'], ['cliente'])) {
    die('Acceso denegado');
}

// Obtener datos del usuario actual
$usuarioModel = new Usuario($pdo);
$usuario = $usuarioModel->obtenerPorId($_SESSION['user_id']);

// ======================================================================
// ⚙️ LÓGICA DE DETALLE DEL CURSO
// ======================================================================
$usuario_id = $_SESSION['user_id']; // ID de usuario REAL para el progreso

$curso_id = filter_input(INPUT_GET, 'curso_id', FILTER_VALIDATE_INT);

if (!$curso_id) {
    die("ID de curso no proporcionado.");
}

// Inicialización de Modelos
$cursoModel  = new CursoDetalle($pdo); 
$progresoModel = new Progreso($pdo);

// Obtención de Datos
$curso   = $cursoModel->obtenerCurso($curso_id);

if (!$curso) {
    die("Error: El curso con ID {$curso_id} no fue encontrado.");
}

$modulos = $cursoModel->obtenerModulos($curso_id);

// Nota: Necesitarás implementar una función en Progreso.php para obtener el
// progreso de un módulo/usuario si deseas cargarlo al inicio. Por ahora,
// el JS asumirá 0% hasta que se marque como completado.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($curso['titulo']) ?> - Campus LATAM</title>

    <!-- Archivos head comunes -->
    <?php require_once __DIR__ . '/view/haed.php'; ?>
    
    <!-- =============================================================== -->
    <!-- ESTILOS ESPECÍFICOS DEL CURSO -->
    <!-- =============================================================== -->
    <style>
        /* Estilos básicos para que el modal funcione */
        .curso-header-content { margin-bottom: 40px; }
        .modulo-encabezado { background-color: #333; padding: 15px; cursor: pointer; border-radius: 5px; margin-top: 10px; display: flex; justify-content: space-between; align-items: center; color: #f0f0f0; }
        .modulo-contenido { padding: 0 15px; max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; background-color: #2c2c2c;}
        .modulo-activo .modulo-contenido { max-height: 500px; }
        .lista-temas { list-style: none; padding: 0; margin-top: 10px;}
        .tema-link { padding: 10px; border-bottom: 1px solid #444; cursor: pointer; transition: background-color 0.2s; display: flex; justify-content: space-between; align-items: center; }
        .tema-link:hover { background-color: #444; }
        
        /* Estilos del Modal */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.8); }
        .modal-contenido { background-color: #2c2c2c; margin: 5% auto; padding: 20px; border: 1px solid #888; width: 90%; max-width: 900px; border-radius: 8px; }
        .cerrar-modal { color: #aaa; float: right; font-size: 28px; font-weight: bold; }
        .cerrar-modal:hover, .cerrar-modal:focus { color: #fff; text-decoration: none; cursor: pointer; }
        .video-container { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; }
        .video-container iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
        
        /* Estilo del botón de progreso */
        #btnMarcarCompletado { background-color: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-left: 20px; transition: background-color 0.3s; }
        #btnMarcarCompletado:hover:not(:disabled) { background-color: #45a049; }
        #btnMarcarCompletado:disabled { background-color: #666; cursor: not-allowed; }
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
            <img src="../../img/Avalon_Informatica/Logos/svg/isotype.svg" alt="Logo Avalon">
        </div>

        <!-- Menú -->
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

        <!-- =========================================================== -->
        <!-- 📌 CONTENIDO DINÁMICO DEL CURSO -->
        <!-- =========================================================== -->

        <div class="curso-header-content">
            <h1><?= htmlspecialchars($curso['titulo']) ?></h1>
            <p><?= htmlspecialchars($curso['descripcion']) ?></p>
        </div>

        <h2>Contenido del Curso</h2>
        
        <!-- MODAL (Debe ir una sola vez) -->
        <div id="modal-video" class="modal">
            <div class="modal-contenido">
                <h3 id="modalTemaTitulo">Título del Tema</h3>
                <span id="cerrarModalBtn" class="cerrar-modal">&times;</span>
                <div class="video-container" id="videoContainer">
                    <!-- Aquí se inyectará el iframe de YouTube -->
                </div>
                
                <!-- CONTROLES DE PROGRESO -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                    <p>Progreso del Módulo: <span id="modalProgresoPorcentaje">--</span></p>
                    <button id="btnMarcarCompletado">Marcar Módulo como Completado</button>
                </div>
                
            </div>
        </div>

        <!-- LISTA DE MÓDULOS -->
        <div class="modulos-container">
            <?php foreach ($modulos as $modulo): ?>
                <div class="modulo">
                    <div class="modulo-encabezado">
                        <h4><?= htmlspecialchars($modulo['titulo']) ?></h4>
                        <!-- ID para actualizar el progreso del módulo -->
                        <span class="porcentaje-modulo" id="progreso-modulo-<?= $modulo['id'] ?>">0%</span> 
                    </div>

                    <div class="modulo-contenido">
                        <ul class="lista-temas">
                            <?php 
                                $temas = $cursoModel->obtenerTemas($modulo['id']); 
                            ?>
                            <?php foreach ($temas as $tema): 
                                $videoId = $tema['url_video'] ?? ''; 
                                $duracion = (int)($tema['duracion_segundos'] ?? 0);
                            ?>
                                <!-- data-modulo-id es crucial para la función de progreso -->
                                <li class="tema-link" 
                                    data-modulo-id="<?= $modulo['id'] ?>"
                                    data-video-id="<?= htmlspecialchars($videoId) ?>"
                                    data-titulo="<?= htmlspecialchars($tema['titulo']) ?>"
                                    >
                                    <span><?= htmlspecialchars($tema['titulo']) ?></span>
                                    <?php if ($duracion > 0): ?>
                                        <span style="font-size: 0.8em; color: #888;">Duración: <?= round($duracion / 60) ?> min</span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
    </main>
</div>

<!-- =============================================================== -->
<!-- 🚀 JAVASCRIPT: LÓGICA DE VIDEO Y PROGRESO -->
<!-- =============================================================== -->
<script>
    // Variable global para almacenar el ID del módulo actual que se está viendo
    let currentTemaModuloId = null;

    document.addEventListener('DOMContentLoaded', function() {
        
        // --- Variables DOM ---
        const modal = document.getElementById('modal-video');
        const cerrarBtn = document.getElementById('cerrarModalBtn');
        const videoContainer = document.getElementById('videoContainer');
        const temasVideo = document.querySelectorAll('.tema-link');
        const btnCompletado = document.getElementById('btnMarcarCompletado');
        const progresoDisplay = document.getElementById('modalProgresoPorcentaje');
        
        // El ID de usuario se pasa de PHP a JavaScript
        const USUARIO_ID = <?= json_encode($usuario_id) ?>;
        
        // =======================================================
        // === FUNCIONES DE UTILIDAD ===
        // =======================================================
        
        /**
         * Extrae el ID de YouTube a partir de diversas URLs.
         */
        function extractYouTubeId(url) {
            if (!url) return null;
            const patterns = [/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([^&?]+)/i];
            for (const pattern of patterns) {
                const match = url.match(pattern);
                if (match && match[1].length === 11) { return match[1]; }
            }
            return null;
        }

        /**
         * Llama al backend para marcar un módulo como 100% completado.
         * RUTA A marcar_completado.php: Desde rol/cliente/ va a ../../endpoints/marcar_completado.php
         */
        function marcarCompletado(moduloId, btn) {
            if (!USUARIO_ID || !moduloId) {
                console.error("Error: ID de usuario o módulo no definido.");
                return;
            }

            // Deshabilitar el botón temporalmente
            btn.disabled = true;
            btn.textContent = 'Guardando...';

            const data = new URLSearchParams();
            data.append('usuario_id', USUARIO_ID);
            data.append('modulo_id', moduloId);

            // RUTA CORREGIDA: Asumiendo que marcar_completado.php está en un directorio como 'endpoints/' o 'ajax/'
            // Si está en el mismo nivel que models/, la ruta es '../../marcar_completado.php'
            // ASUMO: el endpoint está en 'ajax/marcar_completado.php' o similar, ajusta si es diferente.
            fetch('marcar_completado.php', { 
                method: 'POST',
                body: data
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(result => {
                if (result.success) {
                    progresoDisplay.textContent = `100%`;
                    btn.textContent = '¡Completado!';
                    btn.style.backgroundColor = '#1e88e5'; 
                    // Actualizar el porcentaje en la lista principal
                    const moduloProgresoElement = document.getElementById(`progreso-modulo-${moduloId}`);
                    if (moduloProgresoElement) {
                        moduloProgresoElement.textContent = '100%';
                    }
                } else {
                    btn.textContent = `Error: ${result.message || 'Fallo de guardado'}`;
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error al marcar completado:', error);
                btn.textContent = 'Error de conexión';
                btn.disabled = false;
            });
        }
        
        // =======================================================
        // === EVENT LISTENERS ===
        // =======================================================

        // --- 1. Lógica del Acordeón ---
        const encabezados = document.querySelectorAll('.modulo-encabezado');
        encabezados.forEach(encabezado => {
            encabezado.addEventListener('click', function() {
                const modulo = this.closest('.modulo');
                const contenido = modulo.querySelector('.modulo-contenido');
                
                if (contenido.style.maxHeight) {
                    contenido.style.maxHeight = null;
                    modulo.classList.remove('modulo-activo');
                } else {
                    contenido.style.maxHeight = contenido.scrollHeight + 15 + "px";
                    modulo.classList.add('modulo-activo');
                }
            });
        });

        // --- 2. Lógica del Modal y Video ---
        temasVideo.forEach(tema => {
            tema.addEventListener('click', function() {
                const fullUrl = this.dataset.videoId;
                const titulo = this.dataset.titulo;
                
                // **ASIGNAR ID DEL MÓDULO ACTUAL**
                currentTemaModuloId = this.dataset.moduloId;
                
                // Reiniciar el botón y el progreso al abrir el modal
                btnCompletado.disabled = false;
                btnCompletado.textContent = 'Marcar Módulo como Completado';
                btnCompletado.style.backgroundColor = '#4CAF50'; 
                progresoDisplay.textContent = 'Cargando...'; 

                const videoId = extractYouTubeId(fullUrl);

                videoContainer.innerHTML = ''; 
                document.getElementById('modalTemaTitulo').textContent = titulo;

                // NOTA: Aquí deberías hacer una llamada AJAX para cargar el progreso real
                // Por ahora, solo simula 0% si no está marcado.
                progresoDisplay.textContent = '0%'; 

                if (videoId) {
                    const youtubeUrl = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
                    
                    videoContainer.innerHTML = `
                        <iframe 
                            src="${youtubeUrl}" 
                            frameborder="0" 
                            allow="autoplay; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen
                            style="width: 100%; height: 100%;">
                        </iframe>
                    `;
                    modal.style.display = 'block';
                } else {
                    videoContainer.innerHTML = '<p style="text-align: center; padding: 20px; color: red;">Video no disponible o URL no reconocida.</p>';
                    modal.style.display = 'block';
                }
            });
        });

        // --- 3. Listener del Botón de Completado ---
        btnCompletado.addEventListener('click', function() {
            if (currentTemaModuloId) {
                marcarCompletado(currentTemaModuloId, btnCompletado);
            } else {
                // Usamos console.error ya que alert() no es recomendado en iframes
                console.error('Error: No se pudo identificar el módulo para guardar el progreso.');
            }
        });

        // --- 4. Cierre del Modal ---
        function closeModalAndStopVideo() {
            modal.style.display = 'none';
            videoContainer.innerHTML = '';
            currentTemaModuloId = null; // Limpiar ID al cerrar
        }

        cerrarBtn.addEventListener('click', closeModalAndStopVideo);

        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                closeModalAndStopVideo();
            }
        });
    });
</script>
</body>
</html>
```

### 🚨 Punto Crítico: Ruta del Endpoint

**¡ATENCIÓN!** Hay un punto clave que debes verificar en tu JavaScript:

He supuesto que el archivo que maneja el guardado (`marcar_completado.php`) está en `../../../ajax/marcar_completado.php` (sube tres niveles desde `rol/cliente/`).

Si tu endpoint está en otra ubicación (por ejemplo, `../../marcar_completado.php` si estuviera en el mismo directorio que los modelos), debes corregir esta línea en el JavaScript:

```javascript
// Busca esta línea en la función `marcarCompletado`:
// fetch('../../../ajax/marcar_completado.php', { // <-- ¡REVISA ESTA RUTA!