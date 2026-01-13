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
// ESTO VA EN TODAS LAS PÁGINAS QUE USEN BASE DE DATOS
require_once __DIR__ . '/../../includes/auth.php';      // protege + inicia sesión
require_once __DIR__ . '/../../config/db.php';          // ← ESTA ES LA QUE FALTABA
require_once __DIR__ . '/../../models/Curso.php';
// rol/admin/dashboard.php
if ($_SESSION['rol'] !== 'admin') {
    die('Acceso denegado');
}

$cursoModel = new Curso($pdo);
$id = $_GET['id'] ?? 0;

if ($id <= 0 || !$curso = $cursoModel->obtenerPorId($id)) {
    header('Location: gestion_cursos.php?error=curso_no_encontrado');
    exit;
}

$mensaje = '';

// Procesar formulario
if ($_POST) {
    $titulo      = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $activo      = isset($_POST['activo']) ? 1 : 0;

    if ($titulo === '') {
        $mensaje = '<div class="alerta error">El título es obligatorio</div>';
    } else {
        $imagen = $curso['imagen']; // mantener la actual por defecto

        // Si suben nueva imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $nuevo_nombre = 'curso_' . time() . '_' . rand(1000,9999) . '.' . $ext;
                $ruta = '../../public/uploads/cursos/' . $nuevo_nombre;

                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta)) {
                    // Borrar imagen anterior si existe
                    if ($curso['imagen'] && file_exists('../../public/uploads/cursos/' . $curso['imagen'])) {
                        unlink('../../public/uploads/cursos/' . $curso['imagen']);
                    }
                    $imagen = $nuevo_nombre;
                }
            } else {
                $mensaje = '<div class="alerta error">Formato de imagen no permitido</div>';
            }
        }

        if ($mensaje === '') {
            if ($cursoModel->actualizar($id, $titulo, $descripcion, $imagen, $activo)) {
                header("Location: gestion_cursos.php?exito=curso_actualizado");
                exit;
            } else {
                $mensaje = '<div class="alerta error">Error al actualizar</div>';
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
    <title>Campus LATAM</title>

<?php require_once __DIR__ . '/view/haed.php'; ?>

</head>
<body>
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

                   



     
        <div class="header-seccion">
            <h2>Gestión de Cursos</h2>
            
        </div>

        <div class="formulario-curso">
            <h2>Editar Curso</h2>
            <?= $mensaje ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Título del Curso *</label>
                    <input type="text" name="titulo" required value="<?= htmlspecialchars($curso['titulo']) ?>">
                </div>

                <div class="form-group">
                    <label>Descripción *</label>
                    <textarea name="descripcion" required><?= htmlspecialchars($curso['descripcion']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Imagen actual</label>
                    <?php if ($curso['imagen']): ?>
                        <img src="../../public/uploads/cursos/<?= $curso['imagen'] ?>" alt="<?= $curso['titulo'] ?>" class="curso-imagen">
                    <?php else: ?>
                        <div class="sin-imagen">Sin imagen</div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Imagen nueva</label>
                    <input type="file" name="imagen">
                </div>

                <div class="form-group">
                    <label>Activo</label>
                    <input type="checkbox" name="activo" <?= $curso['activo'] ? 'checked' : '' ?>>
                </div>

                <div style="display:flex; gap:15px; margin-top:30px;">
                    <button type="submit" class="btn btn-nuevo">Actualizar Curso</button>
                    <a href="gestion_cursos.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
        
</body>
</html>