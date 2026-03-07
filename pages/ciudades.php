<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";
requireModuleAccess("ciudades");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"]);
    $departamento = trim($_POST["departamento"]);
    $pais = trim($_POST["pais"]);
    $estado = $_POST["estado"];

    $stmt = $pdo->prepare("INSERT INTO ciudad (nombre, departamento, pais, estado) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nombre, $departamento, $pais, $estado]);

    header("Location: ciudades.php?ok=1");
    exit;
}

$ciudades = $pdo->query("SELECT * FROM ciudad ORDER BY id_ciudad DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ciudades - SYSLOG</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="main-layout">
    <?php include "../includes/menu.php"; ?>
    <div class="content">
        <div class="topbar">
            <div><h1>Ciudades</h1><p class="muted-text">Administración de ciudades</p></div>
            <div class="user-box"><?php echo $_SESSION["usuario"]; ?></div>
        </div>

        <?php if (isset($_GET["ok"])): ?><div class="alert success">Ciudad guardada correctamente.</div><?php endif; ?>

        <div class="section">
            <div class="section-header"><h2>Nueva ciudad</h2></div>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label>Departamento</label>
                        <input type="text" name="departamento" required>
                    </div>
                    <div class="form-group">
                        <label>País</label>
                        <input type="text" name="pais" value="Honduras" required>
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>
                <button class="btn btn-primary" type="submit">Guardar ciudad</button>
            </form>
        </div>

        <div class="section">
            <div class="section-header"><h2>Listado de ciudades</h2></div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Departamento</th>
                            <th>País</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ciudades as $ciudad): ?>
                        <tr>
                            <td><?php echo $ciudad["id_ciudad"]; ?></td>
                            <td><?php echo htmlspecialchars($ciudad["nombre"]); ?></td>
                            <td><?php echo htmlspecialchars($ciudad["departamento"]); ?></td>
                            <td><?php echo htmlspecialchars($ciudad["pais"]); ?></td>
                            <td><span class="badge <?php echo $ciudad["estado"] === "activo" ? "badge-success" : "badge-danger"; ?>"><?php echo $ciudad["estado"]; ?></span></td>
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