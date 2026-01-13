<?php
// 1. Asegurarse de que la sesión esté iniciada. Es CRUCIAL para acceder a $_SESSION.
/*
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Definir las variables que se usarán en el PHP
// Si el usuario no está logueado o la variable no existe, usamos valores por defecto.
$nombre_usuario = $_SESSION['user_name'] ?? 'Usuario_conectado';
$rol_usuario = $_SESSION['user_rol'] ?? 'Invitado';

// NOTA: El rol se muestra en el <p> y se utiliza para el icono
$rol_display = $rol_usuario . ' Avalon';
$icono_ruta = "img/Agent.png"; 

// Si tiene un icono diferente para Administrador o Cliente, puedes añadir lógica aquí:
/*
if ($rol_usuario === 'Cliente') {
    $icono_ruta = "/Campus_Latam/public/img/Cliente.png";
}
*/
?>
<!--
<div class="titulo-pagina">
    <h1>CAMPUS AVALON LATAM</h1>
    <p></*?php echo htmlspecialchars($rol_display); ?></p>
</div>
    <div class="usuario-info">
        <span class="nombre-usuario"></*?php echo htmlspecialchars($nombre_usuario); ?></span>
        <img src="</*?php echo $icono_ruta; ?>" alt="Icono de usuario" class="icono-usuario">
        <img src="/Campus_Latam/campus_Agentes/public/img/Agent.png" alt="Icono de usuario" class="icono-usuario">-->
    <!--</div>-->

    <div class="titulo-pagina">
    <h1>CAMPUS AVALON LATAM</h1>
    <p>Test Usuario</p>
</div>
    <div class="usuario-info">
        <span class="nombre-usuario">Test Usuario</span>
        
        <img src="img/Agent.png" alt="Icono de usuario" class="icono-usuario">
    </div>