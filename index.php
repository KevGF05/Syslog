<?php
session_start();

if (isset($_SESSION['usuario'])) {
    header("Location: pages/dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SYSLOG</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-body">

    <div class="login-container">
        <div class="login-logo">
            <h1>SYSLOG</h1>
            <p>Sistema de monitoreo y gestión de infraestructura</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert error">
                Usuario o contraseña incorrectos.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['logout'])): ?>
            <div class="alert success">
                Sesión cerrada correctamente.
            </div>
        <?php endif; ?>

        <form action="actions/login.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="username">Usuario</label>
                <input type="text" name="username" id="username" placeholder="Ingresa tu usuario" required>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" placeholder="Ingresa tu contraseña" required>
            </div>

            <button type="submit">Ingresar</button>
        </form>

        <div class="login-footer">
            SYSLOG © 2026
        </div>
    </div>

</body>
</html>