<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus LATAM</title>

<?php require_once ('view/haed.php'); ?>

</head>
<body>
    <div class="contenedor-principal">
        <aside class="barra-lateral">
                <div class="logo-avalon">
                     <img src="img/Avalon_Informatica/Logos/svg/isotype.svg" alt="Logo de Avalon">
                </div>

                    <nav class="navegacion">
                       <?php require_once ('view/nav.php'); ?>
                    </nav>
            </aside>

        <main class="contenido-principal">
                <header class="encabezado-superior">
                    <div class="titulo-pagina">
                            <h1>Cursos Disponibles</h1>
                            
                        </div>

                    <?php require_once ('view/header.php'); ?>
                        
                </header>
               <div class="tarjetas-container-cursos">
                  <a href="C_Arcadia/Curso_Arcadia_CAC.php" class="enlace-tarjeta">    
                    <div class="tarjeta-curso">
                        <img src="img/Avalon_Informatica/Modulos_Arcadia/png/Workspace.png" alt="Logo del curso" class="tarjeta-logo-curso">
                        <h3 class="tipo-curso">Arcadiar</h3>
                        <p class="instructor">Gestor Administrativo</p>
                    </div>
                  </a>

                    <a href="C_Openpos/Curso_Openpos_CAC.php" class="enlace-tarjeta">
                        <div class="tarjeta-curso">
                            <img src="img/Avalon_Informatica/Openpos/png/OpenPOSng_v.png" alt="Logo del curso" class="tarjeta-logo-curso">
                            <h3 class="tipo-curso">OpenPOS NG</h3>
                            <p class="instructor">Software de Banck Office</p>
                        </div>
                    </a> 
                    <a href="Curso_LocalBOS_CAC.php" class="enlace-tarjeta">
                        <div class="tarjeta-curso">
                            <img src="img/Avalon_Informatica/Otros/LocalBOS/png/localBos.png" alt="Logo del curso" class="tarjeta-logo-curso">
                            <h3 class="tipo-curso">LocalBOS</h3>
                            <p class="instructor">Software de Banck Office</p>
                        </div>
                    </a>

                    <a href="Curso_Autopagos_CAC.php" class="enlace-tarjeta">
                        <div class="tarjeta-curso">
                            <img src="img/Avalon_Informatica/Otros/Estacion_digital/png/laEstacionDigital.png" alt="Logo del curso" class="tarjeta-logo-curso">
                            <h3 class="tipo-curso">Auto-Pagos</h3>
                            <p class="instructor">Software de Banck Office</p>
                        </div>
                    </a>    
                    <a href="Curso_Desarrollo_Latam_CAC.php" class="enlace-tarjeta">
                        <div class="tarjeta-curso">
                            <img src="img/Avalon_Informatica/Logos/png/logo-horizontal-big.png" alt="Logo del curso" class="tarjeta-logo-curso">
                            <h3 class="tipo-curso">Desarrollos Latam</h3>
                            <p class="instructor">Nuevo Productos Latam</p>
                        </div>
                    </a>
                   
                </div>
                
            
        </main>
</body>
</html>