<?php
// view/header.php

// Aseguramos que la sesión esté activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que el usuario esté logueado
if (!isset($_SESSION['logged']) && !isset($_SESSION['logged_in'])) {
    header('Location: ../../../login.php?error=Inicia sesión primero');
    exit;
}

// Tomar los datos correctos de la sesión (los que guardaste en el login)
$nombre_usuario = $_SESSION['nombre'] ?? 'Usuario';
$rol_usuario    = $_SESSION['rol'] ?? 'cliente';
$rol_display    = ucfirst($rol_usuario) . ' Avalon';  // Admin Avalon, Cliente Avalon, etc.
?>

<div class="titulo-pagina">
    <h1>CAMPUS AVALON LATAM</h1>
    <p><?php echo htmlspecialchars($rol_display); ?></p>
</div>

<div class="usuario-info">
    <span class="nombre-usuario"><?php echo htmlspecialchars($nombre_usuario); ?></span>
    <img src="../../img/Agent.png" alt="Icono de usuario" class="icono-usuario">
    <!-- Si querés cambiar el ícono según rol, descomentá esto:
    <?php 
    // $icono = $rol_usuario === 'admin' ? 'Admin.png' : ($rol_usuario === 'agente' ? 'Agent.png' : 'Cliente.png');
    // echo '<img src="../../img/' . $icono . '" alt="Icono" class="icono-usuario">';
    ?>
    -->
</div>
    