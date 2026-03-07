<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";

$datacenters = $pdo->query("SELECT id_datacenter, nombre FROM datacenter ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_datacenter = $_POST["id_datacenter"];
    $nombre = trim($_POST["nombre"]);
    $ubicacion = trim($_POST["ubicacion"]);
    $ip_principal = trim($_POST["ip_principal"]);
    $descripcion = trim($_POST["descripcion"]);
    $estado = $_POST["estado"];

    $stmt = $pdo->prepare("INSERT INTO nodo (id_datacenter, nombre, ubicacion, ip_principal, descripcion, estado) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id_datacenter, $nombre, $ubicacion, $ip_principal, $descripcion, $estado]);

    header("Location: nodos.php?ok=1");
    exit;
}

$sql = "SELECT n.*, d.nombre AS datacenter
        FROM nodo n
        INNER JOIN datacenter d ON n.id_datacenter = d.id_datacenter
        ORDER BY n.id_nodo DESC";
$nodos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nodos - SYSLOG</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="main-layout">
<?php include "../includes/menu.php"; ?>
<div class="content">
    <div class="topbar">
        <div><h1>Nodos</h1><p class="muted-text">Administración de nodos</p></div>
        <div class="user-box"><?php echo $_SESSION["usuario"]; ?></div>
    </div>

    <?php if (isset($_GET["ok"])): ?><div class="alert success">Nodo guardado correctamente.</div><?php endif; ?>

    <div class="section">
        <div class="section-header"><h2>Nuevo nodo</h2></div>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Datacenter</label>
                    <select name="id_datacenter" required>
                        <?php foreach ($datacenters as $dc): ?>
                            <option value="<?php echo $dc["id_datacenter"]; ?>"><?php echo htmlspecialchars($dc["nombre"]); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" required>
                </div>
                <div class="form-group">
                    <label>Ubicación</label>
                    <input type="text" name="ubicacion">
                </div>
                <div class="form-group">
                    <label>IP principal</label>
                    <input type="text" name="ip_principal">
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
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion"></textarea>
            </div>
            <button class="btn btn-primary" type="submit">Guardar nodo</button>
        </form>
    </div>

    <div class="section">
        <div class="section-header"><h2>Listado de nodos</h2></div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Datacenter</th>
                        <th>Ubicación</th>
                        <th>IP principal</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($nodos as $nodo): ?>
                    <tr>
                        <td><?php echo $nodo["id_nodo"]; ?></td>
                        <td><?php echo htmlspecialchars($nodo["nombre"]); ?></td>
                        <td><?php echo htmlspecialchars($nodo["datacenter"]); ?></td>
                        <td><?php echo htmlspecialchars($nodo["ubicacion"]); ?></td>
                        <td><?php echo htmlspecialchars($nodo["ip_principal"]); ?></td>
                        <td><span class="badge badge-info"><?php echo $nodo["estado"]; ?></span></td>
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