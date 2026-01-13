<?php
// ======================================================================
// 🔐 AUTENTICACIÓN DE SESIÓN
// ======================================================================
// Iniciar sesión si no existe
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado (soporta ambas variables)
if (!isset($_SESSION['logged']) && !isset($_SESSION['logged_in'])) {
    header('Location: ../../public/login.php?error=Debes iniciar sesión');
    exit;
}

// ======================================================================
// 📦 INCLUDES Y MODELOS
// ======================================================================
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Curso.php';
require_once __DIR__ . '/../../models/Usuario.php';

// Solo agentes pueden entrar
if (!in_array($_SESSION['rol'], ['agente'])) {
    die('Acceso denegado');
}

// Instanciar modelos
$cursoModel   = new Curso($pdo);
$usuarioModel = new Usuario($pdo);

// Obtener datos del usuario actual
$usuario = $usuarioModel->obtenerPorId($_SESSION['user_id']);

// Cursos inscritos y disponibles para este usuario
$cursos_inscritos   = $cursoModel->cursosInscritos($_SESSION['user_id']);
$cursos_disponibles = $cursoModel->cursosDisponibles($_SESSION['user_id']);

// ======================================================================
// 📝 INSCRIPCIÓN A CURSO
// ======================================================================
if (isset($_GET['inscribirse']) && $_GET['inscribirse'] === 'si') {

    $curso_id = (int) $_GET['curso_id'];

    // Registrar la inscripción en BD
    $cursoModel->inscribirUsuario($curso_id, $_SESSION['user_id']);

    // Recargar página con mensaje de éxito
    header('Location: dashboard.php?exito=inscrito');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Título dinámico -->
    <title>Campus LATAM <?= htmlspecialchars($usuario['nombre']); ?></title>

    <!-- Head común del sistema -->
    <?php require_once __DIR__ . '/view/haed.php'; ?>
</head>

<body>
<div class="contenedor-principal">

    <!-- =============================================================== -->
    <!-- 🚪 BARRA LATERAL -->
    <!-- =============================================================== -->
    <aside class="barra-lateral">

        <!-- Logo -->
        <div class="logo-avalon">
            <img src="../../img/Avalon_Informatica/Logos/svg/isotype.svg" alt="Logo de Avalon">
        </div>

        <!-- Menú de navegación -->
        <nav class="navegacion">
            <?php require_once __DIR__ . '/view/nav.php'; ?>
        </nav>
    </aside>

    <!-- =============================================================== -->
    <!-- 🖥 CONTENIDO PRINCIPAL -->
    <!-- =============================================================== -->
    <main class="contenido-principal">

        <!-- Header superior -->
        <header class="encabezado-superior">
            <?php require_once __DIR__ . '/view/header.php'; ?>
            <h2>Dashboard Agente</h2>
        </header>

        <!-- Bienvenida -->
        <h1>Bienvenido, <?= htmlspecialchars($usuario['nombre']) ?></h1>
        <p>Empresa: <strong><?= htmlspecialchars($usuario['empresa'] ?: 'Sin empresa') ?></strong></p>


        <!-- =========================================================== -->
        <!-- 🎓 CURSOS INSCRITOS -->
        <!-- =========================================================== -->
        <h2>Mis Cursos Activos</h2>

        <?php if (empty($cursos_inscritos)): ?>
            <p style="color:#666;text-align:center;margin:40px 0;">
                Aún no estás inscrito en ningún curso.
            </p>

        <?php else: ?>
            <div class="grid-cursos">
                <?php foreach ($cursos_inscritos as $curso): ?>
                    <div class="card-curso">

                        <!-- Imagen del curso -->
                        <img src="../../public/uploads/cursos/<?= $curso['imagen'] ?: 'default.jpg' ?>"
                             alt="<?= $curso['titulo'] ?>"
                             class="curso-imagen">

                        <div style="padding:20px;">
                            <h3><?= htmlspecialchars($curso['titulo']) ?></h3>

                            <!-- Continuar curso -->
                            <a href="curso_detalle.php?id=<?= $curso['id'] ?>"
                               class="btn btn-nuevo">
                                Continuar Curso
                            </a>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>


        <!-- =========================================================== -->
        <!-- 🆓 CURSOS DISPONIBLES -->
        <!-- =========================================================== -->
        <h2 style="margin-top:60px;">Cursos Disponibles</h2>

        <?php if (empty($cursos_disponibles)): ?>
            <p style="color:#666;text-align:center;margin:40px 0;">
                No hay cursos disponibles en este momento.
            </p>

        <?php else: ?>
            <div class="grid-cursos">
                <?php foreach ($cursos_disponibles as $curso): ?>
                    <div class="card-curso">

                        <img src="../../public/uploads/cursos/<?= $curso['imagen'] ?: 'default.jpg' ?>"
                             alt="<?= $curso['titulo'] ?>"
                             class="curso-imagen">

                        <div style="padding:20px;">
                            <h3><?= htmlspecialchars($curso['titulo']) ?></h3>
                            <p><?= htmlspecialchars($curso['descripcion'] ?? '') ?></p>

                            <!-- Botón para inscribirse -->
                            <a href="dashboard.php?inscribirse=si&curso_id=<?= $curso['id'] ?>"
                               class="btn btn-success"
                               onclick="return confirm('¿Inscribirte en este curso?')">
                                Inscribirme
                            </a>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>


        <!-- =========================================================== -->
        <!-- ✔ MENSAJE: INSCRIPCIÓN EXITOSA -->
        <!-- =========================================================== -->
        <?php if (isset($_GET['exito']) && $_GET['exito'] === 'inscrito'): ?>
            <div class="alerta exito" style="margin:30px auto;max-width:600px;">
                ¡Te has inscrito correctamente! Ya puedes comenzar el curso.
            </div>
        <?php endif; ?>

    </main>
</div>
</body>
</html>
