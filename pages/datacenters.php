<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";
requireModuleAccess("datacenters");

$scope = getScope();
$rol = $scope["rol"];
$id_empresa = $scope["id_empresa"];
$id_datacenter = $scope["id_datacenter"];

$esAdmin = isAdmin();

if ($esAdmin && $_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $pdo->prepare("INSERT INTO datacenter (id_empresa, id_ciudad, nombre, direccion, nivel_seguridad, estado)
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST["id_empresa"],
        $_POST["id_ciudad"],
        trim($_POST["nombre"]),
        trim($_POST["direccion"]),
        $_POST["nivel_seguridad"],
        $_POST["estado"]
    ]);

    header("Location: datacenters.php?ok=1");
    exit;
}

$empresas = [];
$ciudades = $pdo->query("SELECT id_ciudad, nombre FROM ciudad ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

if ($esAdmin) {
    $empresas = $pdo->query("SELECT id_empresa, nombre FROM empresa ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
}

if ($rol === "Administrador") {
    $sql = "SELECT d.*, e.nombre AS empresa, c.nombre AS ciudad
            FROM datacenter d
            INNER JOIN empresa e ON d.id_empresa = e.id_empresa
            INNER JOIN ciudad c ON d.id_ciudad = c.id_ciudad
            ORDER BY d.id_datacenter DESC";
    $stmt = $pdo->query($sql);
} elseif ($rol === "Empresa" || $rol === "Operador") {
    $sql = "SELECT d.*, e.nombre AS empresa, c.nombre AS ciudad
            FROM datacenter d
            INNER JOIN empresa e ON d.id_empresa = e.id_empresa
            INNER JOIN ciudad c ON d.id_ciudad = c.id_ciudad
            WHERE d.id_empresa = ?
            ORDER BY d.id_datacenter DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_empresa]);
} elseif ($rol === "Datacenter") {
    $sql = "SELECT d.*, e.nombre AS empresa, c.nombre AS ciudad
            FROM datacenter d
            INNER JOIN empresa e ON d.id_empresa = e.id_empresa
            INNER JOIN ciudad c ON d.id_ciudad = c.id_ciudad
            WHERE d.id_datacenter = ?
            ORDER BY d.id_datacenter DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_datacenter]);
} elseif ($rol === "Nodo" || $rol === "Equipo") {
    $stmt = $pdo->prepare("
        SELECT d.*, e.nombre AS empresa, c.nombre AS ciudad
        FROM datacenter d
        INNER JOIN empresa e ON d.id_empresa = e.id_empresa
        INNER JOIN ciudad c ON d.id_ciudad = c.id_ciudad
        WHERE d.id_datacenter = ?
    ");
    $stmt->execute([$_SESSION["id_datacenter"] ?? 0]);
} else {
    $stmt = $pdo->prepare("SELECT d.*, '' AS empresa, '' AS ciudad FROM datacenter d WHERE 1=0");
    $stmt->execute();
}

$datacenters = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Datacenters - SYSLOG</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="main-layout">
    <?php include "../includes/menu.php"; ?>
    <div class="content">
        <div class="topbar">
            <div>
                <h1>Datacenters</h1>
                <p class="muted-text">Consulta filtrada por rol</p>
            </div>
            <div class="user-box"><?php echo htmlspecialchars($_SESSION["usuario"]); ?> | <?php echo htmlspecialchars($rol); ?></div>
        </div>

        <?php if (isset($_GET["ok"])): ?>
            <div class="alert success">Datacenter guardado correctamente.</div>
        <?php endif; ?>

        <?php if ($esAdmin): ?>
        <div class="section">
            <div class="section-header"><h2>Nuevo datacenter</h2></div>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Empresa</label>
                        <select name="id_empresa" required>
                            <?php foreach ($empresas as $empresa): ?>
                                <option value="<?php echo $empresa["id_empresa"]; ?>"><?php echo htmlspecialchars($empresa["nombre"]); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ciudad</label>
                        <select name="id_ciudad" required>
                            <?php foreach ($ciudades as $ciudad): ?>
                                <option value="<?php echo $ciudad["id_ciudad"]; ?>"><?php echo htmlspecialchars($ciudad["nombre"]); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="direccion">
                    </div>
                    <div class="form-group">
                        <label>Nivel de seguridad</label>
                        <select name="nivel_seguridad">
                            <option value="bajo">Bajo</option>
                            <option value="medio">Medio</option>
                            <option value="alto">Alto</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="mantenimiento">Mantenimiento</option>
                        </select>
                    </div>
                </div>
                <button class="btn btn-primary" type="submit">Guardar datacenter</button>
            </form>
        </div>
        <?php endif; ?>

        <div class="section">
            <div class="section-header"><h2>Listado de datacenters</h2></div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Empresa</th>
                            <th>Ciudad</th>
                            <th>Dirección</th>
                            <th>Seguridad</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($datacenters as $dc): ?>
                        <tr>
                            <td><?php echo $dc["id_datacenter"]; ?></td>
                            <td><?php echo htmlspecialchars($dc["nombre"]); ?></td>
                            <td><?php echo htmlspecialchars($dc["empresa"]); ?></td>
                            <td><?php echo htmlspecialchars($dc["ciudad"]); ?></td>
                            <td><?php echo htmlspecialchars($dc["direccion"]); ?></td>
                            <td><?php echo htmlspecialchars($dc["nivel_seguridad"]); ?></td>
                            <td><?php echo htmlspecialchars($dc["estado"]); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$datacenters): ?>
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