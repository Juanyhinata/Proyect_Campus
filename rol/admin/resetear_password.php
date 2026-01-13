<?php
// rol/admin/resetear_password.php
session_start();
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Usuario.php';

if (!in_array($_SESSION['rol'], ['admin'])) {
    header('Location: ../../public/login.php');
    exit;
}

$usuarioModel = new Usuario($pdo);
$usuarios = $usuarioModel->listarTodos();

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = (int)$_POST['usuario_id'];
    $nueva_password = $_POST['nueva_password'];
    $confirmar_password = $_POST['confirmar_password'];

    if (empty($usuario_id) || empty($nueva_password) || empty($confirmar_password)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif ($nueva_password !== $confirmar_password) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($nueva_password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        try {
            if ($usuarioModel->resetearPassword($usuario_id, $nueva_password)) {
                $usuario_afectado = $usuarioModel->obtenerPorId($usuario_id);
                $exito = 'Contraseña reseteada correctamente para el usuario: ' . htmlspecialchars($usuario_afectado['nombre']) . '. Comunícale la nueva contraseña al usuario.';
            } else {
                $error = 'Error al resetear la contraseña.';
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
    <title>Resetear Contraseña - Campus LATAM</title>
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
                <h1>Resetear Contraseña de Usuario</h1>
                <p>Establece una nueva contraseña para usuarios que hayan olvidado la suya</p>
            </div>
        </header>

        <div class="contenedor-contenido">
            <a href="usuarios.php" class="btn btn-secondary" style="margin-bottom:20px; border-radius: 50px; padding: 10px 20px;">&larr; Volver a Usuarios</a>

            <?php if ($exito): ?>
                <div class="alerta exito" style="border-radius: 12px; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.1); padding: 15px; margin-bottom: 20px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb;">
                    <strong>✓ Éxito:</strong> <?= $exito ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alerta error" style="border-radius: 12px; box-shadow: 0 4px 15px rgba(220, 53, 69, 0.1); padding: 15px; margin-bottom: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;">
                    <strong>✗ Error:</strong> <?= $error ?>
                </div>
            <?php endif; ?>

            <div class="form-card" style="max-width: 700px; margin: 0 auto; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
                <form action="" method="POST" class="formulario-crear" onsubmit="return confirm('¿Estás seguro de resetear la contraseña de este usuario?');">
                    
                    <div class="grupo-formulario" style="margin-bottom: 25px;">
                        <label for="usuario_id" style="font-weight: 600; color: #444; margin-bottom: 8px; display: block;">Seleccionar Usuario *</label>
                        <select name="usuario_id" id="usuario_id" required class="input-form" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9;">
                            <option value="">-- Selecciona un usuario --</option>
                            <?php foreach ($usuarios as $u): ?>
                                <option value="<?= $u['id'] ?>">
                                    <?= htmlspecialchars($u['nombre']) ?> - <?= htmlspecialchars($u['email']) ?> (<?= ucfirst($u['rol']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grupo-formulario" style="margin-bottom: 25px;">
                        <label for="nueva_password" style="font-weight: 600; color: #444; margin-bottom: 8px; display: block;">Nueva Contraseña *</label>
                        <input type="password" name="nueva_password" id="nueva_password" required class="input-form" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9;">
                        <small style="color: #888; display: block; margin-top: 5px;">Mínimo 6 caracteres. Comunícale esta contraseña al usuario.</small>
                    </div>

                    <div class="grupo-formulario" style="margin-bottom: 25px;">
                        <label for="confirmar_password" style="font-weight: 600; color: #444; margin-bottom: 8px; display: block;">Confirmar Nueva Contraseña *</label>
                        <input type="password" name="confirmar_password" id="confirmar_password" required class="input-form" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9;">
                    </div>

                    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 15px; margin-bottom: 25px;">
                        <strong style="color: #856404;">⚠️ Importante:</strong>
                        <p style="margin: 8px 0 0 0; color: #856404;">Después de resetear la contraseña, deberás comunicarle la nueva contraseña al usuario manualmente. El sistema no enviará ningún correo electrónico automático.</p>
                    </div>

                    <div style="margin-top: 30px; text-align: right;">
                        <button type="submit" class="btn btn-primary" style="padding: 12px 30px; border-radius: 50px; font-size: 16px; box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);">Resetear Contraseña</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>
