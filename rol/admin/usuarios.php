<?php
// rol/admin/usuarios.php
session_start();
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Usuario.php';

// Verificar rol
if (!in_array($_SESSION['rol'], ['admin'])) {
    header('Location: ../../public/login.php');
    exit;
}

$usuarioModel = new Usuario($pdo);

// Parámetros de búsqueda y paginación
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 10;

if ($pagina < 1) $pagina = 1;

// Obtener datos
$usuarios = $usuarioModel->listarPaginado($pagina, $por_pagina, $busqueda);
$total_usuarios = $usuarioModel->contarUsuarios($busqueda);
$total_paginas = ceil($total_usuarios / $por_pagina);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Campus LATAM</title>
    <?php require_once __DIR__ . '/view/haed.php'; ?>
</head>
<body>
<div class="contenedor-principal">
    
    <!-- Sidebar -->
    <aside class="barra-lateral">
        <div class="logo-avalon">
            <img src="../../img/Avalon_Informatica/Logos/svg/isotype.svg" alt="Logo">
        </div>
        <nav class="navegacion">
            <?php require_once __DIR__ . '/view/nav.php'; ?>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="contenido-principal">
        <header class="encabezado-superior">
            <?php require_once __DIR__ . '/view/header.php'; ?>
            <div class="titulo-pagina">
                <h1>Gestión de Usuarios</h1>
                <p>Administra los accesos y roles de la plataforma</p>
            </div>
        </header>

        <div class="contenedor-contenido">
            
            <!-- Barra de herramientas -->
            <div class="barra-herramientas" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <form action="" method="GET" class="buscador" style="display:flex; gap:15px; flex-grow: 1; max-width: 600px;">
                    <div style="position: relative; flex-grow: 1;">
                        <input type="text" name="q" placeholder="Buscar por nombre, email o empresa..." value="<?= htmlspecialchars($busqueda) ?>" class="input-form" style="width: 100%; padding-left: 40px; border-radius: 50px; border: 1px solid #e0e0e0; background: #f9f9f9;">
                        <span style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #aaa;">🔍</span>
                    </div>
                    <button type="submit" class="btn btn-primary" style="border-radius: 50px; padding: 10px 25px;">Buscar</button>
                    <?php if($busqueda): ?>
                        <a href="usuarios.php" class="btn btn-secondary" style="border-radius: 50px;">Limpiar</a>
                    <?php endif; ?>
                </form>

                <a href="usuarios_crear.php" class="btn btn-nuevo" style="box-shadow: 0 4px 15px rgba(32, 201, 151, 0.4);">
                    <span style="margin-right: 5px;">+</span> Nuevo Usuario
                </a>
            </div>

            <!-- Tabla de Usuarios -->
            <div class="tabla-responsive" style="background: white; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.05); overflow: hidden; padding: 5px;">
                <table class="tabla-datos" style="width: 100%; border-collapse: separate; border-spacing: 0;">
                    <thead style="background-color: #f8f9fa;">
                        <tr>
                            <th style="padding: 18px 25px; text-align: left; color: #555; font-weight: 600; border-bottom: 2px solid #eee;">ID</th>
                            <th style="padding: 18px 25px; text-align: left; color: #555; font-weight: 600; border-bottom: 2px solid #eee;">Nombre</th>
                            <th style="padding: 18px 25px; text-align: left; color: #555; font-weight: 600; border-bottom: 2px solid #eee;">Email</th>
                            <th style="padding: 18px 25px; text-align: left; color: #555; font-weight: 600; border-bottom: 2px solid #eee;">Rol</th>
                            <th style="padding: 18px 25px; text-align: left; color: #555; font-weight: 600; border-bottom: 2px solid #eee;">Empresa</th>
                            <th style="padding: 18px 25px; text-align: left; color: #555; font-weight: 600; border-bottom: 2px solid #eee;">Estado</th>
                            <th style="padding: 18px 25px; text-align: center; color: #555; font-weight: 600; border-bottom: 2px solid #eee;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="7" style="text-align:center; padding: 40px; color: #888;">
                                    <div style="font-size: 40px; margin-bottom: 10px;">😕</div>
                                    No se encontraron usuarios.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $u): ?>
                                <tr style="transition: background 0.2s;">
                                    <td style="padding: 18px 25px; border-bottom: 1px solid #f0f0f0; color: #666;">#<?= $u['id'] ?></td>
                                    <td style="padding: 18px 25px; border-bottom: 1px solid #f0f0f0;">
                                        <div style="display: flex; align-items: center;">
                                            <div style="width: 35px; height: 35px; background: #e0e7ff; color: #4f46e5; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 12px; font-size: 14px;">
                                                <?= strtoupper(substr($u['nombre'], 0, 1)) ?>
                                            </div>
                                            <span style="font-weight: 500; color: #333;"><?= htmlspecialchars($u['nombre']) ?></span>
                                        </div>
                                    </td>
                                    <td style="padding: 18px 25px; border-bottom: 1px solid #f0f0f0; color: #555;"><?= htmlspecialchars($u['email']) ?></td>
                                    <td style="padding: 18px 25px; border-bottom: 1px solid #f0f0f0;">
                                        <?php
                                            $rolColor = 'secondary';
                                            switch($u['rol']) {
                                                case 'admin': $rolColor = 'danger'; break;
                                                case 'agente': $rolColor = 'primary'; break;
                                                case 'cliente': $rolColor = 'success'; break;
                                            }
                                        ?>
                                        <span class="badge badge-<?= $u['rol'] ?>" style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background-color: var(--<?= $rolColor ?>-hover, #ccc); color: white; text-transform: capitalize;">
                                            <?= ucfirst($u['rol']) ?>
                                        </span>
                                    </td>
                                    <td style="padding: 18px 25px; border-bottom: 1px solid #f0f0f0; color: #555;"><?= htmlspecialchars($u['empresa'] ?: '-') ?></td>
                                    <td style="padding: 18px 25px; border-bottom: 1px solid #f0f0f0;">
                                        <?php if ($u['activo']): ?>
                                            <span style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background-color: #28a745; color: white;">Activo</span>
                                        <?php else: ?>
                                            <span style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background-color: #dc3545; color: white;">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 18px 25px; border-bottom: 1px solid #f0f0f0; text-align: center;">
                                        <a href="usuarios_editar.php?id=<?= $u['id'] ?>" class="btn-icon editar" title="Editar" style="margin-right: 10px; text-decoration: none; font-size: 18px;">✏️</a>
                                        <a href="usuarios_eliminar.php?id=<?= $u['id'] ?>" class="btn-icon eliminar" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este usuario?');" style="text-decoration: none; font-size: 18px;">🗑️</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <?php if ($total_paginas > 1): ?>
                <div class="paginacion" style="margin-top:30px; text-align:center; display: flex; justify-content: center; gap: 10px;">
                    <?php if ($pagina > 1): ?>
                        <a href="?pagina=<?= $pagina - 1 ?>&q=<?= urlencode($busqueda) ?>" class="btn-paginacion" style="padding: 10px 20px; border: 1px solid #ddd; border-radius: 8px; text-decoration: none; color: #555; background: white; transition: all 0.2s;">&laquo; Anterior</a>
                    <?php endif; ?>

                    <span style="padding: 10px 20px; background: #007bff; color: white; border-radius: 8px; font-weight: bold; box-shadow: 0 4px 10px rgba(0,123,255,0.3);">Página <?= $pagina ?> de <?= $total_paginas ?></span>

                    <?php if ($pagina < $total_paginas): ?>
                        <a href="?pagina=<?= $pagina + 1 ?>&q=<?= urlencode($busqueda) ?>" class="btn-paginacion" style="padding: 10px 20px; border: 1px solid #ddd; border-radius: 8px; text-decoration: none; color: #555; background: white; transition: all 0.2s;">Siguiente &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </main>
</div>
</body>
</html>
