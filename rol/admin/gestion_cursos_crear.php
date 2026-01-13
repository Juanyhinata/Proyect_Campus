<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Curso.php';

if ($_SESSION['rol'] !== 'admin') die('Acceso denegado');

$curso = new Curso($pdo);  // ¡Instancia con el $pdo!

$mensaje = '';

if ($_POST) {
    $titulo = trim($_POST['titulo'] ?? '');
    if ($titulo === '') {
        $mensaje = '<div class="alerta error">El título es obligatorio</div>';
    } else {
        $imagen = '';
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $nuevo = 'curso_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['imagen']['tmp_name'], '../../public/uploads/cursos/' . $nuevo);
            $imagen = $nuevo;
        }

        if ($curso->crear($titulo, $_POST['descripcion'] ?? '', $imagen, isset($_POST['activo']))) {
            header('Location: gestion_cursos.php?exito=curso_creado');
            exit;
        } else {
            $mensaje = '<div class="alerta error">Error al crear el curso</div>';
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
            <div class="formulario-curso">
            <h2>Crear Nuevo Curso</h2>

            <?= $mensaje ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Título del Curso *</label>
                    <input type="text" name="titulo" required value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>" placeholder="Escribe el título del curso">
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" placeholder="Escribe una descripción atractiva del curso..."><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Imagen del Curso (recomendado 1200x600)</label>
                    <input type="file" name="imagen" accept="image/*" onchange="previewImage(event)">
                    <img id="preview" class="vista-previa" src="" alt="Vista previa">
                </div>

                <div class="form-group">
                    <label class="checkbox-activo">
                        <input type="checkbox" name="activo" checked>
                        Curso activo (visible para alumnos)
                    </label>
                </div>

                <div style="display:flex; gap:15px; margin-top:30px;">
                    <button type="submit" class="btn btn-nuevo">Crear Curso</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
            
        </div>  
    </div>        
        <script>
        function previewImage(event) {
            const preview = document.getElementById('preview');
            const file = event.target.files[0];
            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            }
        }
    </script>
</body>
</html>