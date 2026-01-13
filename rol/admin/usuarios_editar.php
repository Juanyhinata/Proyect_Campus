<?php
// rol/admin/usuarios_editar.php
session_start();
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Usuario.php';

if (!in_array($_SESSION['rol'], ['admin'])) {
    header('Location: ../../public/login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$usuarioModel = new Usuario($pdo);
$usuario = $usuarioModel->obtenerPorId($id);

if (!$usuario) {
    die('Usuario no encontrado');
}

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; // Si está vacío no se actualiza
    $rol = $_POST['rol'];
    $empresa = trim($_POST['empresa']);
    $activo = isset($_POST['activo']) ? 1 : 0;

    if (empty($nombre) || empty($email) || empty($rol)) {
        $error = 'Nombre, Email y Rol son obligatorios.';
    } else {
        try {
            if ($usuarioModel->actualizar($id, $nombre, $email, $rol, $empresa, $password ?: null, $activo)) {
                $exito = 'Usuario actualizado correctamente.';
                // Recargar datos
                $usuario = $usuarioModel->obtenerPorId($id);
            } else {
                $error = 'Error al actualizar. Verifique los datos.';
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Campus LATAM</title>
    <?php require_once __DIR__ . '/view/haed.php'; ?>
</head>
<body>
<div class="contenedor-principal">
    <aside class="barra-lateral">
        <div class="logo-avalon">
            <img src="../../img/Avalon_Informatica/Logos/svg/isotype.svg" alt="Logo">
        </div>
        <nav class="navegacion">
            <?php require_once __DIR__ . '/view/nav.php'; ?>
        </nav>
    </aside>

    <main class="contenido-principal">
        <header class="encabezado-superior">
            <?php require_once __DIR__ . '/view/header.php'; ?>
            <div class="titulo-pagina">
                <h1>Editar Usuario</h1>
                <p>Modifica los datos y permisos del usuario</p>
            </div>
        </header>

        <div class="contenedor-contenido">
            <a href="usuarios.php" class="btn btn-secondary" style="margin-bottom:20px; border-radius: 50px; padding: 10px 20px;">&larr; Volver al listado</a>

            <?php if ($exito): ?>
                <div class="alerta exito" style="border-radius: 12px; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.1);"><?= $exito ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alerta error" style="border-radius: 12px; box-shadow: 0 4px 15px rgba(220, 53, 69, 0.1);"><?= $error ?></div>
            <?php endif; ?>

            <div class="form-card" style="max-width: 700px; margin: 0 auto; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
                <form action="" method="POST" class="formulario-crear">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="grupo-formulario" style="margin-bottom: 20px;">
                            <label for="nombre" style="font-weight: 600; color: #444; margin-bottom: 8px; display: block;">Nombre Completo *</label>
                            <input type="text" name="nombre" id="nombre" required class="input-form" value="<?= htmlspecialchars($usuario['nombre']) ?>" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9;">
                        </div>

                        <div class="grupo-formulario" style="margin-bottom: 20px;">
                            <label for="email" style="font-weight: 600; color: #444; margin-bottom: 8px; display: block;">Email *</label>
                            <input type="email" name="email" id="email" required class="input-form" value="<?= htmlspecialchars($usuario['email']) ?>" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9;">
                        </div>
                    </div>

                    <div class="grupo-formulario" style="margin-bottom: 20px;">
                        <label for="password" style="font-weight: 600; color: #444; margin-bottom: 8px; display: block;">Contraseña <small style="color: #888; font-weight: normal;">(Dejar en blanco para mantener la actual)</small></label>
                        <input type="password" name="password" id="password" class="input-form" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="grupo-formulario" style="margin-bottom: 20px;">
                            <label for="rol" style="font-weight: 600; color: #444; margin-bottom: 8px; display: block;">Rol *</label>
                            <select name="rol" id="rol" class="input-form" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9;">
                                <option value="cliente" <?= $usuario['rol'] === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                                <option value="agente" <?= $usuario['rol'] === 'agente' ? 'selected' : '' ?>>Agente</option>
                                <option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                <option value="invitado" <?= $usuario['rol'] === 'invitado' ? 'selected' : '' ?>>Invitado</option>
                            </select>
                        </div>

                        <div class="grupo-formulario" style="margin-bottom: 20px;">
                            <label for="empresa" style="font-weight: 600; color: #444; margin-bottom: 8px; display: block;">Empresa</label>
                            <input type="text" name="empresa" id="empresa" class="input-form" value="<?= htmlspecialchars($usuario['empresa']) ?>" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9;">
                        </div>
                    </div>

                    <div class="grupo-formulario" style="margin-bottom: 20px;">
                        <label style="font-weight: 600; color: #444; margin-bottom: 8px; display: flex; align-items: center; cursor: pointer;">
                            <input type="checkbox" name="activo" id="activo" value="1" <?= $usuario['activo'] ? 'checked' : '' ?> style="width: 20px; height: 20px; margin-right: 10px; cursor: pointer;">
                            <span>Usuario Activo</span>
                        </label>
                        <small style="color: #888; margin-left: 30px; display: block;">Los usuarios inactivos no podrán acceder al sistema</small>
                    </div>

                    <div style="margin-top: 30px; text-align: right;">
                        <button type="submit" class="btn btn-primary" style="padding: 12px 30px; border-radius: 50px; font-size: 16px; box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);">Actualizar Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>
