
<?php
// public/login.php
// Incluye el inicio de sesión para limpiar cualquier sesión activa antes de loguearse
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Latam - Iniciar Sesión</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="background"></div>
    <header>
        <div class="logo">CAMPUS LATAM</div>
        <nav>
            <a href="index.php">Inicio</a>
        </nav>
    </header>

    <main>
        <section class="login-container">
            <h2>Login</h2>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alerta error">
                    <span>❌</span>
                    <p><?php echo htmlspecialchars($_GET['error']); ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['mensaje'])): ?>
                <div class="alerta exito">
                    <span>✅</span>
                    <p><?php echo htmlspecialchars($_GET['mensaje']); ?></p>
                </div>
            <?php endif; ?>
            <form action="Controllers/login_controller.php" method="POST"> 
                <div class="form-group">
                    <label for="email">Email ID</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-options">
                    </div>
                <button type="submit">Login</button>
                <!--<p class="register-prompt">¿No tienes cuenta? <a href="registro.php" class="register-link">Regístrate</a></p>
                <p class="register-prompt">¿Olvidaste la contraseña? <a href="recuperar.php" class="register-link">Recuperar</a></p>-->
            </form>
        </section>
    </main>
</body>
</html>