<?php
// includes/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged']) && !isset($_SESSION['logged_in'])) {
    header('Location: ../../public/login.php?error=Debes iniciar sesión');
    exit;
}
?>

<?php

// rol/admin/dashboard.php
if ($_SESSION['rol'] !== 'admin') {
    die('Acceso denegado');
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
<!-- Botón flotante del chat JArizmendi - VERSIÓN DEFINITIVA CON ROBOT -->
<div id="chat-flotante">
  <button onclick="abrirChat()" title="Abrir asistente JArizmendi">
    <img src="boot/boot.png" alt="Asistente Robot">
  </button>
</div>

<style>
  #chat-flotante {
    position: fixed;
    bottom: 28px;
    right: 28px;
    z-index: 99999;
  }

  #chat-flotante button {
    width: 68px;
    height: 68px;
    /* Mantenemos el fondo por si la imagen tarda en cargar */
    background: linear-gradient(135deg, #00d4aa, #826eff);
    border: 2px solid #fff; /* Opcional: un borde blanco fino se ve muy elegante */
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 10px 30px rgba(0, 212, 170, 0.5);
    transition: all 0.35s ease;
    
    /* CAMBIOS CLAVE PARA LA IMAGEN: */
    padding: 0;           /* Quitamos el relleno para que la foto llegue al borde */
    overflow: hidden;     /* Esto hace que la foto cuadrada se recorte en círculo */
    display: flex;        /* Mantiene todo centrado */
    align-items: center;
    justify-content: center;
    animation: pulse 4s infinite;
  }

  /* Estilos específicos para que la foto se vea perfecta */
  #chat-flotante button img {
    width: 100%;
    height: 100%;
    object-fit: cover;    /* IMPORTANTE: Evita que la cara se estire o aplaste */
    object-position: center top; /* Enfoca la cara si la imagen es muy alta */
    display: block;
  }

  #chat-flotante button:hover {
    transform: scale(1.15);
    box-shadow: 0 15px 40px rgba(0, 212, 170, 0.7);
  }

  @keyframes pulse {
    0% { box-shadow: 0 10px 30px rgba(0, 212, 170, 0.5); }
    50% { box-shadow: 0 10px 30px rgba(130, 110, 255, 0.6); }
    100% { box-shadow: 0 10px 30px rgba(0, 212, 170, 0.5); }
  }
</style>

<script>
  function abrirChat() {
    window.open('boot/chat.html', 'JArizmendiChat', 'width=920,height=780,resizable=yes,scrollbars=yes');
  }
</script>
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





<head>
    <meta charset="UTF-8">
    <title>Admin - Panel Principal</title>
    
</head>

 

    <main class="admin-main">
        <h1>Panel Administrador</h1>
        <div class="admin-cards">
           
        </div>
    </main>

            
        </main>
</body>
</html>