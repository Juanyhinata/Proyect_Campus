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
// rol/admin/dashboard.php
if ($_SESSION['rol'] !== 'admin') {
    die('Acceso denegado');
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
            <a href="gestion_cursos_crear.php" class="btn-nuevo">+ Nuevo Curso</a>
        </div>

        <div class="grid-cursos">
            <?php
            $stmt = $pdo->query("SELECT * FROM campus.cursos ORDER BY creado_en DESC");
            while ($curso = $stmt->fetch()):
            ?>
            <div class="card-curso">
                <?php if ($curso['imagen']): ?>
                    <img src="../../public/uploads/cursos/<?= $curso['imagen'] ?>" alt="<?= $curso['titulo'] ?>" class="curso-imagen">
                <?php else: ?>
                    <div class="sin-imagen">Sin imagen</div>
                <?php endif; ?>

                <h3><?= htmlspecialchars($curso['titulo']) ?></h3>
                <p><?= htmlspecialchars(substr($curso['descripcion'], 0, 100)) ?>...</p>
                
                <div class="acciones">
                    <a href="gestion_cursos_editar.php?id=<?= $curso['id'] ?>" class="btn btn-primary btn-small">Editar</a>
                    <a href="gestion_cursos_eliminar.php?id=<?= $curso['id'] ?>" 
                       class="btn btn-danger btn-small" 
                       onclick="return confirm('¿Seguro que querés eliminar este curso y todo su contenido?')">
                       Eliminar
                    </a>
                    <a href="gestion_modulos.php?curso_id=<?= $curso['id'] ?>" class="btn btn-success btn-small">   
                       Módulos (<?= $pdo->query("SELECT COUNT(*) FROM campus.modulos WHERE curso_id = {$curso['id']}")->fetchColumn() ?>)
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>
        
</body>
</html>