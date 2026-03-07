<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";
requireModuleAccess("interfaces");

$scope = getScope();
$rol = $scope["rol"];
$id_empresa = $scope["id_empresa"];
$id_datacenter = $scope["id_datacenter"];
$id_nodo = $scope["id_nodo"];
$id_equipo = $scope["id_equipo"];

$esAdmin = isAdmin();
$equiposSelect = [];

if ($esAdmin) {
    $equiposSelect = $pdo->query("SELECT id_equipo, nombre FROM equipo ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
}

if ($esAdmin && $_SERVER["REQUEST_METHOD"] === "POST") {
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

if ($rol === "Administrador") {
    $stmt = $pdo->query("
        SELECT i.*, e.nombre AS equipo
        FROM interface_red i
        INNER JOIN equipo e ON i.id_equipo = e.id_equipo
        ORDER BY i.id_interface DESC
    ");
} elseif ($rol === "Empresa" || $rol === "Operador") {
    $stmt = $pdo->prepare("
        SELECT i.*, e.nombre AS equipo
        FROM interface_red i
        INNER JOIN equipo e ON i.id_equipo = e.id_equipo
        INNER JOIN nodo n ON e.id_nodo = n.id_nodo
        INNER JOIN datacenter d ON n.id_datacenter = d.id_datacenter
        WHERE d.id_empresa = ?
        ORDER BY i.id_interface DESC
    ");
    $stmt->execute([$id_empresa]);
} elseif ($rol === "Datacenter") {
    $stmt = $pdo->prepare("
        SELECT i.*, e.nombre AS equipo
        FROM interface_red i
        INNER JOIN equipo e ON i.id_equipo = e.id_equipo
        INNER JOIN nodo n ON e.id_nodo = n.id_nodo
        WHERE n.id_datacenter = ?
        ORDER BY i.id_interface DESC
    ");
    $stmt->execute([$id_datacenter]);
} elseif ($rol === "Nodo") {
    $stmt = $pdo->prepare("
        SELECT i.*, e.nombre AS equipo
        FROM interface_red i
        INNER JOIN equipo e ON i.id_equipo = e.id_equipo
        WHERE e.id_nodo = ?
        ORDER BY i.id_interface DESC
    ");
    $stmt->execute([$id_nodo]);
} elseif ($rol === "Equipo") {
    $stmt = $pdo->prepare("
        SELECT i.*, e.nombre AS equipo
        FROM interface_red i
        INNER JOIN equipo e ON i.id_equipo = e.id_equipo
        WHERE i.id_equipo = ?
        ORDER BY i.id_interface DESC
    ");
    $stmt->execute([$id_equipo]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM interface_red WHERE 1=0");
    $stmt->execute();
}

$interfaces = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            <div>
                <h1>Interfaces</h1>
                <p class="muted-text">Consulta filtrada por rol</p>
            </div>
            <div class="user-box"><?php echo htmlspecialchars($_SESSION["usuario"]); ?> | <?php echo htmlspecialchars($rol); ?></div>
        </div>

        <?php if (isset($_GET["ok"])): ?>
            <div class="alert success">Interfaz guardada correctamente.</div>
        <?php endif; ?>

        <?php if ($esAdmin): ?>
        <div class="section">
            <div class="section-header"><h2>Nueva interfaz</h2></div>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Equipo</label>
                        <select name="id_equipo" required>
                            <?php foreach ($equiposSelect as $e): ?>
                                <option value="<?php echo $e["id_equipo"]; ?>"><?php echo htmlspecialchars($e["nombre"]); ?></option>
                            <?php endforeach; ?>
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
        <?php endif; ?>

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
                            <td><?php echo htmlspecialchars($i["estado"]); ?></td>
                            <td><?php echo number_format((float)$i["trafico_actual"], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$interfaces): ?>
                        <tr><td colspan="7">No hay registros disponibles.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>