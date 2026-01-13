<?php
// ----------------------------------------------------------------------
// gestion_cursos_eliminar.php - Muestra la confirmación y ejecuta la eliminación.
// ----------------------------------------------------------------------
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Curso.php';

// 1. Control de acceso
if ($_SESSION['rol'] !== 'admin') die('Acceso denegado');

$cursoModel = new Curso($pdo); 
$id = $_GET['id'] ?? 0;

// 2. Obtener datos del curso para la confirmación
if ($id <= 0 || !$curso = $cursoModel->obtenerPorId($id)) {
    header('Location: gestion_cursos.php?error=curso_no_encontrado');
    exit;
}

// 3. Procesar la confirmación de eliminación (si llega con ?confirmar=si)
if (isset($_GET['confirmar']) && $_GET['confirmar'] === 'si') {
    
    // 3.1. Eliminar imagen del sistema de archivos (si existe)
    if ($curso['imagen'] && file_exists("../../public/uploads/cursos/" . $curso['imagen'])) {
        unlink("../../public/uploads/cursos/" . $curso['imagen']);
    }

    // 3.2. Eliminar curso de la base de datos (y, por CASCADE, módulos y temas)
    // NOTA: Asumimos que la eliminación en la DB también manejará la eliminación de módulos y temas.
    if ($cursoModel->eliminar($id)) {
        header("Location: gestion_cursos.php?exito=curso_eliminado");
        exit;
    } else {
        die("Error crítico al eliminar el curso y sus dependencias.");
    }
}
// Si no hay confirmación, el script continúa a la sección HTML para mostrar el formulario de confirmación.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Curso - <?= htmlspecialchars($curso['titulo']) ?></title>

<?php require_once __DIR__ . '/view/haed.php'; ?>

    <style>
        /* -------------------------------------- */
        /* ESTILOS ESPECÍFICOS PARA LA CONFIRMACIÓN */
        /* -------------------------------------- */
        .confirmar-box {
            max-width: 650px;
            margin: 60px auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            border-top: 5px solid #c0392b; /* Rojo de advertencia */
        }
        .confirmar-box h2 {
            color: #c0392b;
            font-size: 2.2rem;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .curso-titulo {
            background: #fdf2f2;
            border: 1px solid #f9dcdc;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 1.5rem;
            color: #2c3e50;
        }
        .curso-imagen-previa {
            max-width: 150px;
            height: auto;
            border-radius: 8px;
            margin: 15px 0 25px 0;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .mensaje-advertencia {
            background: #fbecec;
            color: #b71c1c;
            padding: 20px;
            border-radius: 10px;
            margin: 30px 0;
            font-weight: bold;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        .botones {
            margin-top: 40px;
            display: flex;
            gap: 20px;
            justify-content: center;
        }
        .btn-danger {
            background-color: #e74c3c;
            color: white;
            padding: 12px 25px;
            border-radius: 6px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }
        .btn-secondary {
            background-color: #bdc3c7;
            color: #2c3e50;
            padding: 12px 25px;
            border-radius: 6px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .btn-secondary:hover {
            background-color: #95a5a6;
        }

    </style>
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
                    
                    <div class="confirmar-box">
                        <h2>¡Advertencia!</h2>
                        
                        <p style="font-size: 1.2rem; color: #34495e;">Estás a punto de eliminar permanentemente el siguiente curso:</p>
                        
                        <div class="curso-titulo">
                            <strong><?= htmlspecialchars($curso['titulo']) ?></strong>
                        </div>
                        
                        <?php if ($curso['imagen']): ?>
                            <img src="../../public/uploads/cursos/<?= $curso['imagen'] ?>" 
                                 alt="<?= htmlspecialchars($curso['titulo']) ?>" 
                                 class="curso-imagen-previa">
                        <?php endif; ?>

                        <div class="mensaje-advertencia">
                            Se eliminarán también **todos sus módulos, temas y cualquier evaluación asociada**.
                            <br>Esta acción **NO se puede deshacer**.
                        </div>

                        <div class="botones">
                            <a href="gestion_cursos_eliminar.php?id=<?= $id ?>&confirmar=si" 
                               class="btn btn-danger">
                                Sí, eliminar permanentemente
                            </a>
                            <a href="gestion_cursos.php" class="btn btn-secondary">
                                Cancelar
                            </a>
                        </div>
                        
                    </div>
                </div>  
        </main>
    </div>
</body>
</html>