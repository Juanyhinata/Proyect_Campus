<?php
// rol/admin/perfil.php
session_start();
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Usuario.php';

if (!in_array($_SESSION['rol'], ['admin'])) {
    header('Location: ../../public/login.php');
    exit;
}

$usuarioModel = new Usuario($pdo);
$usuario = $usuarioModel->obtenerPorId($_SESSION['user_id']);

if (!$usuario) {
    die('Error al cargar el perfil');
}

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'actualizar_perfil') {
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);

        if (empty($nombre) || empty($email)) {
            $error = 'El nombre y el email son obligatorios.';
        } else {
            try {
                if ($usuarioModel->actualizarPerfil($_SESSION['user_id'], $nombre, $email)) {
                    $exito = 'Perfil actualizado correctamente.';
                    // Actualizar sesión con el nuevo nombre
                    $_SESSION['nombre'] = $nombre;
                    // Recargar datos
                    $usuario = $usuarioModel->obtenerPorId($_SESSION['user_id']);
                } else {
                    $error = 'Error al actualizar el perfil.';
                }
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    } elseif ($accion === 'cambiar_password') {
        $password_actual = $_POST['password_actual'];
        $password_nueva = $_POST['password_nueva'];
        $password_confirmar = $_POST['password_confirmar'];

        if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) {
            $error = 'Todos los campos de contraseña son obligatorios.';
        } elseif ($password_nueva !== $password_confirmar) {
            $error = 'Las contraseñas nuevas no coinciden.';
        } elseif (strlen($password_nueva) < 6) {
            $error = 'La contraseña debe tener al menos 6 caracteres.';
        } else {
            try {
                if ($usuarioModel->cambiarPassword($_SESSION['user_id'], $password_actual, $password_nueva)) {
                    $exito = 'Contraseña cambiada correctamente.';
                } else {
                    $error = 'La contraseña actual es incorrecta.';
                }
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Campus LATAM</title>
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
                <h1>Mi Perfil</h1>
                <p>Administra tu información personal y contraseña</p>
            </div>
        </header>

        <div class="contenedor-contenido">
            <?php if ($exito): ?>
                <div class="alerta exito" style="border-radius: 12px; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.1);"><?= $exito ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alerta error" style="border-radius: 12px; box-shadow: 0 4px 15px rgba(220, 53, 69, 0.1);"><?= $error ?></div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; max-width: 1200px; margin: 0 auto;">
                
                <!-- Actualizar Perfil -->
                <div class="form-card" style="padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
                    <h2 style="margin-bottom: 20px; color: #333; font-size: 20px;">Información del Perfil</h2>
                    <form action="" method="POST" class="formulario-crear">
                        <input type="hidden" name="accion" value="actualizar_perfil">
                        
                        <div class="grupo-formulario" style="margin-bottom: 20px;">
                            <label for="nombre" style="font-weight: 600; color: #444; margin-bottom: 8px; display: block;">Nombre Completo *</label>
                            <input type="text" name="nombre" id="nombre" required class="input-form" value="<?= htmlspecialchars($usuario['nombre']) ?>" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9;">
                        </div>

                        <div class="grupo-formulario" style="margin-bottom: 20px;">
                            <label for="email" style="font-weight: 600; color: #444; margin-bottom: 8px; display: block;">Email *</label>
                            <input type="email" name="email" id="email" required class="input-form" value="<?= htmlspecialchars($usuario['email']) ?>" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9;">
                        </div>

                        <div class="grupo-formulario" style="margin-bottom: 20px;">
                            <label style="font-weight: 600; color: #444; margin-bottom: 8px; display: block;">Rol</label>
                            <input type="text" readonly class="input-form" value="<?= ucfirst($usuario['rol']) ?>" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #e9ecef; cursor: not-allowed;">
                        </div>

                        <div style="margin-top: 25px; text-align: right;">
                            <button type="submit" class="btn btn-primary" style="padding: 12px 30px; border-radius: 50px; font-size: 16px; box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);">Actualizar Perfil</button>
                        </div>
                    </form>
                </div>

                <!-- Cambiar Contraseña -->
                <div class="form-card" style="padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
                    <h2 style="margin-bottom: 20px; color: #333; font-size: 20px;">Cambiar Contraseña</h2>
                    <form action="" method="POST" class="formulario-crear">
                        <input type="hidden" name="accion" value="cambiar_password">
                        
                        <div class="grupo-formulario" style="margin-bottom: 20px;">
                            <label for="password_actual" style="font-weight: 600; color: #444; margin-bottom: 8px; display: block;">Contraseña Actual *</label>
                            <input type="password" name="password_actual" id="password_actual" required class="input-form" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9;">
                        </div>

                        <div class="grupo-formulario" style="margin-bottom: 20px;">
                            <label for="password_nueva" style="font-weight: 600; color: #444; margin-bottom: 8px; display: block;">Nueva Contraseña *</label>
                            <input type="password" name="password_nueva" id="password_nueva" required class="input-form" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9;">
                            <small style="color: #888; display: block; margin-top: 5px;">Mínimo 6 caracteres</small>
                        </div>

                        <div class="grupo-formulario" style="margin-bottom: 20px;">
                            <label for="password_confirmar" style="font-weight: 600; color: #444; margin-bottom: 8px; display: block;">Confirmar Nueva Contraseña *</label>
                            <input type="password" name="password_confirmar" id="password_confirmar" required class="input-form" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9;">
                        </div>

                        <div style="margin-top: 25px; text-align: right;">
                            <button type="submit" class="btn btn-primary" style="padding: 12px 30px; border-radius: 50px; font-size: 16px; box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);">Cambiar Contraseña</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </main>
</div>
</body>
</html>
