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

// ======================================================================
// DEFINICIÓN DE FUNCIÓN HELPER
// ======================================================================

// Función para extraer el ID de YouTube de varias URLs
function obtenerYoutubeId($url) {
    $patrones = [
        '/(?:https?:\/\/(?:www\.)?youtube\.com\/watch\/?\?v=)([^&]+)/i', // youtube.com/watch?v=ID
        '/(?:https?:\/\/(?:www\.)?youtu\.be\/)([^?&#]+)/i'              // youtu.be/ID
    ];
    
    foreach ($patrones as $patron) {
        if (preg_match($patron, $url, $matches)) {
            return $matches[1];
        }
    }
    return false;
}

// ---------------------------------------------------------
// LÓGICA DEL ARCHIVO
// ---------------------------------------------------------

$tema_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$usuario_id = $_SESSION['user_id'] ?? null;

if (!$usuario_id) {
    header('Location: ../../public/login.php?error=Sesion_invalida_sin_ID');
    exit;
}
if (!$tema_id) {
    die("ID de tema inválido.");
}

// Cargar el tema y su módulo asociado
$stmt = $pdo->prepare("
    SELECT t.*, m.id as modulo_rel_id, m.curso_id 
    FROM campus.temas t 
    JOIN campus.modulos m ON t.modulo_id = m.id 
    WHERE t.id = :id
");

$stmt->execute([':id' => $tema_id]);
$tema = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tema) {
    die("Tema no encontrado.");
}

// Extraer el ID de YouTube y generar URL
$youtube_id = obtenerYoutubeId($tema['video_id']);
$embed_url = $youtube_id ? "https://www.youtube.com/embed/{$youtube_id}?autoplay=1&rel=0&modestbranding=1" : null;

// Recuperar progreso del usuario para este módulo
require_once __DIR__ . '/../../models/Progreso.php';
$progresoModel = new Progreso($pdo);
$progresoActual = $progresoModel->obtenerProgreso($usuario_id, $tema['modulo_rel_id']);

$tiempo_inicial = $progresoActual ? $progresoActual['tiempo_visto'] : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tema['titulo']); ?></title>
    <!-- Head común del sistema -->
    <?php require_once __DIR__ . '/view/haed.php'; ?>
    <style>
        /* Estilos específicos */
        .video-container { position: relative; padding-bottom: 56.25%; height: 0; background: #000; margin-bottom: 20px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.3); cursor: pointer; }
        .video-container iframe, .video-container #youtube-player { position: absolute; top: 0; left: 0; width: 100%; height: 100%; } 
        
        /* Overlay invisible para interceptar clics */
        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10;
            background: rgba(0,0,0,0); /* Transparente */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Botón de Play personalizado */
        .play-icon {
            width: 80px;
            height: 80px;
            background: rgba(0,0,0,0.6);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: all 0.3s ease;
            backdrop-filter: blur(2px);
        }

        .play-icon svg {
            width: 40px;
            height: 40px;
            fill: white;
            margin-left: 5px;
        }

        .video-container:hover .play-icon {
            background: rgba(255, 0, 0, 0.8);
            transform: scale(1.1);
        }

        /* Cuando está reproduciendo, ocultamos el ícono */
        .video-container.is-playing .play-icon {
            opacity: 0;
            transform: scale(1.5);
            pointer-events: none;
        } 
        
        .tema-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        #status-msg { font-size: 1em; color: #666; margin-top: 15px; font-weight: 500; }
        .content-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="contenedor-principal">

    <!-- =============================================================== -->
    <!-- 🚪 BARRA LATERAL -->
    <!-- =============================================================== -->
    <aside class="barra-lateral">
        <div class="logo-avalon">
            <img src="../../img/Avalon_Informatica/Logos/svg/isotype.svg" alt="Logo de Avalon">
        </div>
        <nav class="navegacion">
            <?php require_once __DIR__ . '/view/nav.php'; ?>
        </nav>
    </aside>

    <!-- =============================================================== -->
    <!-- 🖥 CONTENIDO PRINCIPAL -->
    <!-- =============================================================== -->
    <main class="contenido-principal">

        <header class="encabezado-superior">
            <?php require_once __DIR__ . '/view/header.php'; ?>
            <h2>Visualización de Tema</h2>
        </header>

        <div class="content-box">
            <div class="tema-header">
                <h1><?php echo htmlspecialchars($tema['titulo']); ?></h1>
                <a href="curso_detalle.php?id=<?php echo $tema['curso_id']; ?>" class="btn btn-secondary">⬅ Volver al Curso</a>
            </div>

            <?php if ($tema['tipo'] === 'video' && $youtube_id): ?>
                <div class="video-container" id="video-container-wrapper">
                    <!-- Div donde la API de YouTube montará el iframe -->
                    <div id="youtube-player"></div>
                    
                    <!-- Overlay para interceptar clics -->
                    <div id="video-overlay" class="video-overlay">
                        <div class="play-icon" id="play-icon">
                            <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                    </div>
                </div>
                <div id="status-msg">Haz clic en el video para reproducir</div>
            <?php elseif ($tema['tipo'] === 'pdf'): ?>
                <iframe src="<?php echo htmlspecialchars($tema['pdf_ruta']); ?>" width="100%" height="600px" style="border:none;"></iframe>
            <?php else: ?>
                <p>Contenido de texto o formato desconocido o URL de video inválida.</p>
            <?php endif; ?>
        </div>

    </main>
</div>

<script>
    // =========================================================
    // CONFIGURACIÓN Y VARIABLES
    // =========================================================
    const usuario_id = <?php echo $usuario_id; ?>;
    const modulo_id = <?php echo $tema['modulo_rel_id']; ?>;
    const tema_id = <?php echo $tema['id']; ?>;
    let tiempoGuardado = <?php echo $tiempo_inicial; ?>;
    let duracionTotal = <?php echo isset($tema['duracion_segundos']) ? (int)$tema['duracion_segundos'] : 0; ?>; 
    let completado = <?php echo ($progresoActual && $progresoActual['completado']) ? 'true' : 'false'; ?>;
    const videoId = "<?php echo $youtube_id; ?>";
    
    let player;
    let progressInterval;

    // =========================================================
    // API DE YOUTUBE
    // =========================================================
    
    // Cargar la API de IFrame de forma asíncrona
    var tag = document.createElement('script');
    tag.src = "https://www.youtube.com/iframe_api";
    var firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

    // Función llamada automáticamente por la API cuando está lista
    function onYouTubeIframeAPIReady() {
        if (!videoId) return;

        player = new YT.Player('youtube-player', {
            height: '100%',
            width: '100%',
            videoId: videoId,
            playerVars: {
                'autoplay': 0,        // No autoiniciar (esperar clic)
                'controls': 0,        // OCULTAR controles nativos
                'rel': 0,             // No videos relacionados
                'modestbranding': 1,  // Minimizar branding
                'disablekb': 1,       // Deshabilitar teclado
                'fs': 0,              // Sin botón fullscreen
                'iv_load_policy': 3   // Ocultar anotaciones
            },
            events: {
                'onReady': onPlayerReady,
                'onStateChange': onPlayerStateChange
            }
        });
    }

    function onPlayerReady(event) {
        // Restaurar tiempo guardado si existe
        if (tiempoGuardado > 0) {
            player.seekTo(tiempoGuardado);
        }
        
        // Advertencia si la duración es 0
        if (duracionTotal === 0 && player.getDuration() > 0) {
            // Intentar recuperar duración de la API si la BD falló
            duracionTotal = player.getDuration();
        } else if (duracionTotal === 0) {
            const statusMsg = document.getElementById('status-msg');
            if(statusMsg) {
                 statusMsg.innerText = "⚠️ Advertencia: Video sin duración registrada. Progreso no se guardará.";
                 statusMsg.style.color = "#ffcc00";
            }
        }
    }

    function onPlayerStateChange(event) {
        const container = document.getElementById('video-container-wrapper');
        const statusMsg = document.getElementById('status-msg');
        
        if (event.data == YT.PlayerState.PLAYING) {
            container.classList.add('is-playing');
            if(statusMsg) statusMsg.innerText = "Reproduciendo...";
            startProgressTracking();
        } else {
            container.classList.remove('is-playing');
            if(event.data == YT.PlayerState.PAUSED) {
                 if(statusMsg) statusMsg.innerText = "Pausado";
                 stopProgressTracking();
            } else if (event.data == YT.PlayerState.ENDED) {
                 if(statusMsg) statusMsg.innerText = "Finalizado";
                 stopProgressTracking();
                 marcarComoCompletado();
            }
        }
    }

    // =========================================================
    // CONTROL DE REPRODUCCIÓN (OVERLAY)
    // =========================================================
    document.getElementById('video-overlay').addEventListener('click', function() {
        if(!player || typeof player.getPlayerState !== 'function') return;
        
        const state = player.getPlayerState();
        if (state === YT.PlayerState.PLAYING) {
            player.pauseVideo();
        } else {
            player.playVideo();
        }
    });

    // =========================================================
    // SEGUIMIENTO DE PROGRESO
    // =========================================================
    function startProgressTracking() {
        if (duracionTotal <= 0 || completado || progressInterval) return;
        
        progressInterval = setInterval(() => {
            if(!player || typeof player.getCurrentTime !== 'function') return;
            
            const currentTime = player.getCurrentTime();
            // Actualizamos la variable global
            tiempoGuardado = currentTime;
            
            // Guardar en servidor
            guardarProgreso(tiempoGuardado);

            // Verificar completado (95%)
            if (tiempoGuardado >= duracionTotal * 0.95) {
                marcarComoCompletado();
                clearInterval(progressInterval);
                progressInterval = null;
            }
        }, 5000);
    }

    function stopProgressTracking() {
        if (progressInterval) {
            clearInterval(progressInterval);
            progressInterval = null;
        }
        // Guardar último estado al pausar
        if(tiempoGuardado > 0 && !completado) {
            guardarProgreso(tiempoGuardado);
        }
    }

    // Guardar al cerrar ventana (backup)
    window.addEventListener('beforeunload', () => {
        if (player && typeof player.getCurrentTime === 'function') {
            guardarProgreso(player.getCurrentTime());
        }
    });

    async function guardarProgreso(tiempoActual) {
        if(duracionTotal <= 0) return;

        const formData = new FormData();
        formData.append('usuario_id', usuario_id);
        formData.append('modulo_id', modulo_id);
        formData.append('tema_id', tema_id); 
        formData.append('tiempo_visto', Math.floor(tiempoActual));
        formData.append('duracion_video', duracionTotal);

        try {
            const response = await fetch('guardar_tiempo.php', { method: 'POST', body: formData });
            // No procesamos respuesta cada 5s para no saturar consola, solo errores
        } catch (error) {
            console.error('Error guardando progreso:', error);
        }
    }

    async function marcarComoCompletado() {
        if (completado) return;
        completado = true;
        
        const formData = new FormData();
        formData.append('usuario_id', usuario_id);
        formData.append('modulo_id', modulo_id);

        try {
            const response = await fetch('marcar_completado.php', { method: 'POST', body: formData });
            const data = await response.json();
            if(data.success) {
                const statusMsg = document.getElementById('status-msg');
                if(statusMsg) {
                    statusMsg.innerText = '✅ ¡Módulo Completado!';
                    statusMsg.style.color = '#4CAF50';
                }
            }
        } catch (error) {
            console.error('Error marcando completado:', error);
        }
    }
</script>
</body>
</html>