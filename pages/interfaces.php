<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";

$equipos = $pdo->query("SELECT id_equipo, nombre FROM equipo ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $pdo->prepare("INSERT INTO interface_red
        (id_equipo, nombre_interface, tipo_interface, velocidad, mac_address, direccion_ip, estado, trafico_actual, descripcion)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST["id_equipo"],
        trim($_POST["nombre_interface"]),
        trim($_POST["tipo_interface"]),
        trim($_POST["velocidad"]),
        trim($_POST["mac_address"]),
        trim($_POST["direccion_ip"]),
        $_POST["estado"],
        $_POST["trafico_actual"],
        trim($_POST["descripcion"])
    ]);

    header("Location: interfaces.php?ok=1");
    exit;
}

$sql = "SELECT i.*, e.nombre AS equipo
        FROM interface_red i
        INNER JOIN equipo e ON i.id_equipo = e.id_equipo
        ORDER BY i.id_interface DESC";
$interfaces = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Interfaces - SYSLOG</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="main-layout">
<?php include "../includes/menu.php"; ?>
<div class="content">
    <div class="topbar">
        <div><h1>Interfaces</h1><p class="muted-text">Administración de interfaces</p></div>
        <div class="user-box"><?php echo $_SESSION["usuario"]; ?></div>
    </div>

    <?php if (isset($_GET["ok"])): ?><div class="alert success">Interfaz guardada correctamente.</div><?php endif; ?>

    <div class="section">
        <div class="section-header"><h2>Nueva interfaz</h2></div>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Equipo</label>
                    <select name="id_equipo" required>
                        <?php foreach ($equipos as $equipo): ?><option value="<?php echo $equipo["id_equipo"]; ?>"><?php echo htmlspecialchars($equipo["nombre"]); ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Nombre interfaz</label><input type="text" name="nombre_interface" required></div>
                <div class="form-group"><label>Tipo</label><input type="text" name="tipo_interface"></div>
                <div class="form-group"><label>Velocidad</label><input type="text" name="velocidad"></div>
                <div class="form-group"><label>MAC</label><input type="text" name="mac_address"></div>
                <div class="form-group"><label>Dirección IP</label><input type="text" name="direccion_ip"></div>
                <div class="form-group">
                    <label>Estado</label>
                    <select name="estado">
                        <option value="up">Up</option>
                        <option value="down">Down</option>
                        <option value="warning">Warning</option>
                        <option value="disabled">Disabled</option>
                    </select>
                </div>
                <div class="form-group"><label>Tráfico actual</label><input type="number" step="0.01" name="trafico_actual" value="0"></div>
            </div>
            <div class="form-group"><label>Descripción</label><textarea name="descripcion"></textarea></div>
            <button class="btn btn-primary" type="submit">Guardar interfaz</button>
        </form>
    </div>

    <div class="section">
        <div class="section-header"><h2>Listado de interfaces</h2></div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Equipo</th>
                        <th>Interfaz</th>
                        <th>Tipo</th>
                        <th>IP</th>
                        <th>Estado</th>
                        <th>Tráfico</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($interfaces as $i): ?>
                    <tr>
                        <td><?php echo $i["id_interface"]; ?></td>
                        <td><?php echo htmlspecialchars($i["equipo"]); ?></td>
                        <td><?php echo htmlspecialchars($i["nombre_interface"]); ?></td>
                        <td><?php echo htmlspecialchars($i["tipo_interface"]); ?></td>
                        <td><?php echo htmlspecialchars($i["direccion_ip"]); ?></td>
                        <td><span class="badge badge-info"><?php echo $i["estado"]; ?></span></td>
                        <td><?php echo number_format((float)$i["trafico_actual"], 2); ?></td>
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