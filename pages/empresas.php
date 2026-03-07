<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";
requireModuleAccess("empresas");
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"]);
    $descripcion = trim($_POST["descripcion"]);
    $estado = $_POST["estado"];

    $stmt = $pdo->prepare("INSERT INTO empresa (nombre, descripcion, estado) VALUES (?, ?, ?)");
    $stmt->execute([$nombre, $descripcion, $estado]);

    header("Location: empresas.php?ok=1");
    exit;
}

$empresas = $pdo->query("SELECT * FROM empresa ORDER BY id_empresa DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Empresas - SYSLOG</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="main-layout">
    <?php include "../includes/menu.php"; ?>
    <div class="content">
        <div class="topbar">
            <div>
                <h1>Empresas</h1>
                <p class="muted-text">Administración de empresas</p>
            </div>
            <div class="user-box"><?php echo $_SESSION["usuario"]; ?></div>
        </div>

        <?php if (isset($_GET["ok"])): ?>
            <div class="alert success">Empresa guardada correctamente.</div>
        <?php endif; ?>

        <div class="section">
            <div class="section-header"><h2>Nueva empresa</h2></div>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion"></textarea>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Guardar empresa</button>
                </div>
            </form>
        </div>

        <div class="section">
            <div class="section-header"><h2>Listado de empresas</h2></div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($empresas as $empresa): ?>
                        <tr>
                            <td><?php echo $empresa["id_empresa"]; ?></td>
                            <td><?php echo htmlspecialchars($empresa["nombre"]); ?></td>
                            <td><?php echo htmlspecialchars($empresa["descripcion"]); ?></td>
                            <td><span class="badge <?php echo $empresa["estado"] === "activo" ? "badge-success" : "badge-danger"; ?>"><?php echo $empresa["estado"]; ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>