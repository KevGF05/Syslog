<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";
requireModuleAccess("equipos");

$scope = getScope();
$rol = $scope["rol"];
$id_empresa = $scope["id_empresa"];
$id_datacenter = $scope["id_datacenter"];
$id_nodo = $scope["id_nodo"];
$id_equipo = $scope["id_equipo"];

$esAdmin = isAdmin();

$nodos = [];
$tipos = $pdo->query("SELECT id_tipo_equipo, nombre_tipo FROM tipo_equipo ORDER BY nombre_tipo")->fetchAll(PDO::FETCH_ASSOC);
$marcas = $pdo->query("SELECT id_marca, nombre_marca FROM marca ORDER BY nombre_marca")->fetchAll(PDO::FETCH_ASSOC);

if ($esAdmin) {
    $nodos = $pdo->query("SELECT id_nodo, nombre FROM nodo ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
}

if ($esAdmin && $_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $pdo->prepare("INSERT INTO equipo
        (id_nodo, id_tipo_equipo, id_marca, nombre, modelo, serial_number, ip_gestion, sistema_operativo, version_firmware, estado, fecha_instalacion)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST["id_nodo"],
        $_POST["id_tipo_equipo"],
        $_POST["id_marca"],
        trim($_POST["nombre"]),
        trim($_POST["modelo"]),
        trim($_POST["serial_number"]),
        trim($_POST["ip_gestion"]),
        trim($_POST["sistema_operativo"]),
        trim($_POST["version_firmware"]),
        $_POST["estado"],
        $_POST["fecha_instalacion"]
    ]);

    header("Location: equipos.php?ok=1");
    exit;
}

if ($rol === "Administrador") {
    $stmt = $pdo->query("
        SELECT e.*, n.nombre AS nodo, t.nombre_tipo, m.nombre_marca
        FROM equipo e
        INNER JOIN nodo n ON e.id_nodo = n.id_nodo
        INNER JOIN tipo_equipo t ON e.id_tipo_equipo = t.id_tipo_equipo
        INNER JOIN marca m ON e.id_marca = m.id_marca
        ORDER BY e.id_equipo DESC
    ");
} elseif ($rol === "Empresa" || $rol === "Operador") {
    $stmt = $pdo->prepare("
        SELECT e.*, n.nombre AS nodo, t.nombre_tipo, m.nombre_marca
        FROM equipo e
        INNER JOIN nodo n ON e.id_nodo = n.id_nodo
        INNER JOIN datacenter d ON n.id_datacenter = d.id_datacenter
        INNER JOIN tipo_equipo t ON e.id_tipo_equipo = t.id_tipo_equipo
        INNER JOIN marca m ON e.id_marca = m.id_marca
        WHERE d.id_empresa = ?
        ORDER BY e.id_equipo DESC
    ");
    $stmt->execute([$id_empresa]);
} elseif ($rol === "Datacenter") {
    $stmt = $pdo->prepare("
        SELECT e.*, n.nombre AS nodo, t.nombre_tipo, m.nombre_marca
        FROM equipo e
        INNER JOIN nodo n ON e.id_nodo = n.id_nodo
        INNER JOIN tipo_equipo t ON e.id_tipo_equipo = t.id_tipo_equipo
        INNER JOIN marca m ON e.id_marca = m.id_marca
        WHERE n.id_datacenter = ?
        ORDER BY e.id_equipo DESC
    ");
    $stmt->execute([$id_datacenter]);
} elseif ($rol === "Nodo") {
    $stmt = $pdo->prepare("
        SELECT e.*, n.nombre AS nodo, t.nombre_tipo, m.nombre_marca
        FROM equipo e
        INNER JOIN nodo n ON e.id_nodo = n.id_nodo
        INNER JOIN tipo_equipo t ON e.id_tipo_equipo = t.id_tipo_equipo
        INNER JOIN marca m ON e.id_marca = m.id_marca
        WHERE e.id_nodo = ?
        ORDER BY e.id_equipo DESC
    ");
    $stmt->execute([$id_nodo]);
} elseif ($rol === "Equipo") {
    $stmt = $pdo->prepare("
        SELECT e.*, n.nombre AS nodo, t.nombre_tipo, m.nombre_marca
        FROM equipo e
        INNER JOIN nodo n ON e.id_nodo = n.id_nodo
        INNER JOIN tipo_equipo t ON e.id_tipo_equipo = t.id_tipo_equipo
        INNER JOIN marca m ON e.id_marca = m.id_marca
        WHERE e.id_equipo = ?
    ");
    $stmt->execute([$id_equipo]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM equipo WHERE 1=0");
    $stmt->execute();
}

$equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Equipos - SYSLOG</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="main-layout">
    <?php include "../includes/menu.php"; ?>
    <div class="content">
        <div class="topbar">
            <div>
                <h1>Equipos</h1>
                <p class="muted-text">Consulta filtrada por rol</p>
            </div>
            <div class="user-box"><?php echo htmlspecialchars($_SESSION["usuario"]); ?> | <?php echo htmlspecialchars($rol); ?></div>
        </div>

        <?php if (isset($_GET["ok"])): ?>
            <div class="alert success">Equipo guardado correctamente.</div>
        <?php endif; ?>

        <?php if ($esAdmin): ?>
        <div class="section">
            <div class="section-header"><h2>Nuevo equipo</h2></div>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nodo</label>
                        <select name="id_nodo" required>
                            <?php foreach ($nodos as $n): ?>
                                <option value="<?php echo $n["id_nodo"]; ?>"><?php echo htmlspecialchars($n["nombre"]); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tipo</label>
                        <select name="id_tipo_equipo" required>
                            <?php foreach ($tipos as $t): ?>
                                <option value="<?php echo $t["id_tipo_equipo"]; ?>"><?php echo htmlspecialchars($t["nombre_tipo"]); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Marca</label>
                        <select name="id_marca" required>
                            <?php foreach ($marcas as $m): ?>
                                <option value="<?php echo $m["id_marca"]; ?>"><?php echo htmlspecialchars($m["nombre_marca"]); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Nombre</label><input type="text" name="nombre" required></div>
                    <div class="form-group"><label>Modelo</label><input type="text" name="modelo"></div>
                    <div class="form-group"><label>Serial</label><input type="text" name="serial_number"></div>
                    <div class="form-group"><label>IP gestión</label><input type="text" name="ip_gestion"></div>
                    <div class="form-group"><label>Sistema operativo</label><input type="text" name="sistema_operativo"></div>
                    <div class="form-group"><label>Firmware</label><input type="text" name="version_firmware"></div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="mantenimiento">Mantenimiento</option>
                            <option value="warning">Warning</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Fecha instalación</label><input type="date" name="fecha_instalacion"></div>
                </div>
                <button class="btn btn-primary" type="submit">Guardar equipo</button>
            </form>
        </div>
        <?php endif; ?>

        <div class="section">
            <div class="section-header"><h2>Listado de equipos</h2></div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Equipo</th>
                            <th>Nodo</th>
                            <th>Tipo</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>IP</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($equipos as $equipo): ?>
                        <tr>
                            <td><?php echo $equipo["id_equipo"]; ?></td>
                            <td><?php echo htmlspecialchars($equipo["nombre"]); ?></td>
                            <td><?php echo htmlspecialchars($equipo["nodo"]); ?></td>
                            <td><?php echo htmlspecialchars($equipo["nombre_tipo"]); ?></td>
                            <td><?php echo htmlspecialchars($equipo["nombre_marca"]); ?></td>
                            <td><?php echo htmlspecialchars($equipo["modelo"]); ?></td>
                            <td><?php echo htmlspecialchars($equipo["ip_gestion"]); ?></td>
                            <td><?php echo htmlspecialchars($equipo["estado"]); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$equipos): ?>
                        <tr><td colspan="8">No hay registros disponibles.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>