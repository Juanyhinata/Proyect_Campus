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
                            <h1>Módulo de Arcadia CBOS</h1>
                            <p>Equipo del CAC</p>
                        </div>
                    <?php require_once ('view/header.php'); ?>
                </header>


            <div class="logos-openpos">
                <img src="img/Avalon_Informatica/Arcadia/png/arcadia-isotype-gradient.png" alt="Logo de Arcadia" class="logo-principal">
                <img src="img/Avalon_Informatica/Modulos_Arcadia/png/cbos.png" alt="Logo de Arcadia" class="logo-principal">
            </div>

            <div class="acordeon-container">
<!-- Módulo 1: Gestión de la tienda (CBOS) -->
<div class="modulo">
    <div class="modulo-encabezado">
        <span class="triangulo">&#9658;</span> 
        <h2 class="titulo-modulo">Módulo 1: Gestión de la tienda (CBOS)</h2>
        <span class="porcentaje">0%</span>
    </div>
    <div class="barra-progreso-modulo">
        <div class="progreso-interno" style="width: 0%;"></div>
    </div>
    <div class="modulo-contenido">
        <div id="modal-video" class="modal">
            <div class="modal-contenido">
                <span class="cerrar-modal">&times;</span>
                <div class="video-container">
                </div>
            </div>
        </div>

        <h3>En este módulo aprenderás a utilizar las funciones principales de CBOS, el corazón de la gestión de tienda dentro de Arcadia. </h3>

        <ul class="lista-temas">
            <li class="tema-link" data-video-id="#">Administración de operaciones de tienda</li>
            <li class="tema-link" data-video-id="#">Control de ventas y productos</li>
            <li class="tema-link" data-video-id="#">Material de Apoyo</li>
            <li class="tema-link" data-video-id="#">Monitoreo de incidencias</li>
            <li class="tema-link" data-video-id="KjorwOU-LcI">Reportes básicos para atención al cliente</li>
        </ul>

        <h4>Material de Apoyo</h4>
                <ul>
                    <li><a href="#">pdf_1</a></li>
                </ul>

        <h4>Evaluacion de lo aprendido</h4>
            <p>Realizar Evaluacion da click aqui.</p>
            <span>Calificaion del Modulo Aprobado/Reprobado</span><i>8.5</i>
    </div>
</div>

<!-- Módulo 2: Inicio en CBOS -->
<div class="modulo">
    <div class="modulo-encabezado">
        <span class="triangulo">&#9658;</span> 
        <h2 class="titulo-modulo">Módulo 2: Inicio en CBOS</h2>
        <span class="porcentaje">0%</span>
    </div>
    <div class="barra-progreso-modulo">
        <div class="progreso-interno" style="width: 0%;"></div>
    </div>
    <div class="modulo-contenido">
        <div id="modal-video" class="modal">
            <div class="modal-contenido">
                <span class="cerrar-modal">&times;</span>
                <div class="video-container">
                </div>
            </div>
        </div>
        <ul class="lista-temas">
            <li class="tema-link" data-video-id="#">Avalon Pulse</li>
            <li class="tema-link" data-video-id="#">Calendario</li>
            <li class="tema-link" data-video-id="#">Arcadia 360°</li>
            <li class="tema-link" data-video-id="#">Suministros</li>
            <li class="tema-link" data-video-id="#">Mis favoritos</li>
            <li class="tema-link" data-video-id="#">Tareas de la tienda</li>
            <li class="tema-link" data-video-id="#">Mis tareas de hoy</li>
            <li class="tema-link" data-video-id="#">Fichaje</li>
        </ul>
    </div>
</div>

<!-- Módulo 3: Sección Mis Liquidaciones -->
<div class="modulo">
    <div class="modulo-encabezado">
        <span class="triangulo">&#9658;</span> 
        <h2 class="titulo-modulo">Módulo 3: Sección Mis Liquidaciones</h2>
        <span class="porcentaje">0%</span>
    </div>
    <div class="barra-progreso-modulo">
        <div class="progreso-interno" style="width: 0%;"></div>
    </div>
    <div class="modulo-contenido">
        <div id="modal-video" class="modal">
            <div class="modal-contenido">
                <span class="cerrar-modal">&times;</span>
                <div class="video-container">
                </div>
            </div>
        </div>
        <ul class="lista-temas">
            <li class="tema-link" data-video-id="#">Liquidaciones</li>
            <li class="tema-link" data-video-id="725TL2HiLcY">Informes</li>
            <li class="tema-link" data-video-id="#">Retirada de fondos</li>
            <li class="tema-link" data-video-id="#">Facturas en pista</li>
        </ul>
    </div>
</div>

<!-- Módulo 4: Mi Carburante -->
<div class="modulo">
    <div class="modulo-encabezado">
        <span class="triangulo">&#9658;</span> 
        <h2 class="titulo-modulo">Módulo 4: Mi Carburante</h2>
        <span class="porcentaje">0%</span>
    </div>
    <div class="barra-progreso-modulo">
        <div class="progreso-interno" style="width: 0%;"></div>
    </div>
    <div class="modulo-contenido">
        <div id="modal-video" class="modal">
            <div class="modal-contenido">
                <span class="cerrar-modal">&times;</span>
                <div class="video-container">
                </div>
            </div>
        </div>
        <ul class="lista-temas">
            <li class="tema-link" data-video-id="#">Carburantes</li>
            <li class="tema-link" data-video-id="#">En depósito</li>
        </ul>
    </div>
</div>

<!-- Módulo 5: Mi tienda -->
<div class="modulo">
    <div class="modulo-encabezado">
        <span class="triangulo">&#9658;</span> 
        <h2 class="titulo-modulo">Módulo 5: Mi tienda</h2>
        <span class="porcentaje">0%</span>
    </div>
    <div class="barra-progreso-modulo">
        <div class="progreso-interno" style="width: 0%;"></div>
    </div>
    <div class="modulo-contenido">
        <div id="modal-video" class="modal">
            <div class="modal-contenido">
                <span class="cerrar-modal">&times;</span>
                <div class="video-container">
                </div>
            </div>
        </div>
        <ul class="lista-temas">
            <li class="tema-link" data-video-id="#">Compras</li>
            <li class="tema-link" data-video-id="#">Inventario</li>
            <li class="tema-link" data-video-id="#">Catálogo</li>
        </ul>
    </div>
</div>

<!-- Módulo 6: Contabilización -->
<div class="modulo">
    <div class="modulo-encabezado">
        <span class="triangulo">&#9658;</span> 
        <h2 class="titulo-modulo">Módulo 6: Contabilización</h2>
        <span class="porcentaje">0%</span>
    </div>
    <div class="barra-progreso-modulo">
        <div class="progreso-interno" style="width: 0%;"></div>
    </div>
    <div class="modulo-contenido">
        <div id="modal-video" class="modal">
            <div class="modal-contenido">
                <span class="cerrar-modal">&times;</span>
                <div class="video-container">
                </div>
            </div>
        </div>
        <ul class="lista-temas">
            <li class="tema-link" data-video-id="#">Datos contables</li>
            <li class="tema-link" data-video-id="#">Cuentas corrientes</li>
            <li class="tema-link" data-video-id="#">Informes cuentas corrientes</li>
        </ul>
    </div>
</div>

<!-- Módulo 7: Consultas -->
<div class="modulo">
    <div class="modulo-encabezado">
        <span class="triangulo">&#9658;</span> 
        <h2 class="titulo-modulo">Módulo 7: Consultas</h2>
        <span class="porcentaje">0%</span>
    </div>
    <div class="barra-progreso-modulo">
        <div class="progreso-interno" style="width: 0%;"></div>
    </div>
    <div class="modulo-contenido">
        <div id="modal-video" class="modal">
            <div class="modal-contenido">
                <span class="cerrar-modal">&times;</span>
                <div class="video-container">
                </div>
            </div>
        </div>
        <ul class="lista-temas">
            <li class="tema-link" data-video-id="#">Ventas</li>
            <li class="tema-link" data-video-id="#">Compras</li>
            <li class="tema-link" data-video-id="#">Desatendido</li>
            <li class="tema-link" data-video-id="#">Carburante</li>
            <li class="tema-link" data-video-id="#">Clientes</li>
            <li class="tema-link" data-video-id="#">Rentabilidad</li>
            <!--<li class="tema-link" data-video-id="#">Fiscalidad</li>-->
        </ul>
    </div>
</div>

<!-- Módulo 8: Mantenimiento -->
<div class="modulo">
    <div class="modulo-encabezado">
        <span class="triangulo">&#9658;</span> 
        <h2 class="titulo-modulo">Módulo 8: Mantenimiento</h2>
        <span class="porcentaje">0%</span>
    </div>
    <div class="barra-progreso-modulo">
        <div class="progreso-interno" style="width: 0%;"></div>
    </div>
    <div class="modulo-contenido">
        <div id="modal-video" class="modal">
            <div class="modal-contenido">
                <span class="cerrar-modal">&times;</span>
                <div class="video-container">
                </div>
            </div>
        </div>
        <ul class="lista-temas">
            <li class="tema-link" data-video-id="#">Global</li>
            <li class="tema-link" data-video-id="#">Envío de documentos</li>
            <li class="tema-link" data-video-id="#">Pista</li>
            <li class="tema-link" data-video-id="#">Compañía</li>
            <li class="tema-link" data-video-id="#">Tienda</li>
            <li class="tema-link" data-video-id="#">Contables</li>
        </ul>
    </div>
</div>

<!-- Módulo 9: Otros procesos -->
<div class="modulo">
    <div class="modulo-encabezado">
        <span class="triangulo">&#9658;</span> 
        <h2 class="titulo-modulo">Módulo 9: Otros procesos</h2>
        <span class="porcentaje">0%</span>
    </div>
    <div class="barra-progreso-modulo">
        <div class="progreso-interno" style="width: 0%;"></div>
    </div>
    <div class="modulo-contenido">
        <div id="modal-video" class="modal">
            <div class="modal-contenido">
                <span class="cerrar-modal">&times;</span>
                <div class="video-container">
                </div>
            </div>
        </div>
        <ul class="lista-temas">
            <li class="tema-link" data-video-id="#">Centros autorizados</li>
            <li class="tema-link" data-video-id="#">Importar / Exportar</li>
            <li class="tema-link" data-video-id="#">Tareas y equipos</li>
            <li class="tema-link" data-video-id="#">Control horario</li>
            <li class="tema-link" data-video-id="#">Seguridad y auditoría</li>
            <li class="tema-link" data-video-id="#">Operativa POS</li>
        </ul>
    </div>
</div>
 


            </div>

            
        </main>
        <script src="js/modulos.js"></script> 
</body>
</html>