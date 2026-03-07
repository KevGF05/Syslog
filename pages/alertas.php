<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";
requireModuleAccess("alertas");

$scope = getScope();
$rol = $scope["rol"];
$id_empresa = $scope["id_empresa"];
$id_datacenter = $scope["id_datacenter"];
$id_nodo = $scope["id_nodo"];
$id_equipo = $scope["id_equipo"];

$esAdmin = isAdmin();

if ($esAdmin && $_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $pdo->prepare("INSERT INTO alerta
        (nombre_alerta, descripcion, severidad, tipo_objeto, metrica, condicion, valor_referencia, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        trim($_POST["nombre_alerta"]),
        trim($_POST["descripcion"]),
        $_POST["severidad"],
        $_POST["tipo_objeto"],
        trim($_POST["metrica"]),
        $_POST["condicion"],
        $_POST["valor_referencia"] !== "" ? $_POST["valor_referencia"] : null,
        $_POST["estado"]
    ]);

    header("Location: alertas.php?ok=1");
    exit;
}

$alertasDef = $pdo->query("SELECT * FROM alerta ORDER BY id_alerta DESC")->fetchAll(PDO::FETCH_ASSOC);

if ($rol === "Administrador") {
    $stmt = $pdo->query("
        SELECT ea.*, a.nombre_alerta, a.severidad
        FROM evento_alerta ea
        INNER JOIN alerta a ON ea.id_alerta = a.id_alerta
        ORDER BY ea.fecha_evento DESC
    ");
} elseif ($rol === "Empresa" || $rol === "Operador") {
    $stmt = $pdo->prepare("
        SELECT ea.*, a.nombre_alerta, a.severidad
        FROM evento_alerta ea
        INNER JOIN alerta a ON ea.id_alerta = a.id_alerta
        INNER JOIN (
            SELECT i.id_interface AS id_objeto, 'interface' AS tipo_objeto
            FROM interface_red i
            INNER JOIN equipo e ON i.id_equipo = e.id_equipo
            INNER JOIN nodo n ON e.id_nodo = n.id_nodo
            INNER JOIN datacenter d ON n.id_datacenter = d.id_datacenter
            WHERE d.id_empresa = ?
            UNION ALL
            SELECT e.id_equipo, 'equipo'
            FROM equipo e
            INNER JOIN nodo n ON e.id_nodo = n.id_nodo
            INNER JOIN datacenter d ON n.id_datacenter = d.id_datacenter
            WHERE d.id_empresa = ?
            UNION ALL
            SELECT n.id_nodo, 'nodo'
            FROM nodo n
            INNER JOIN datacenter d ON n.id_datacenter = d.id_datacenter
            WHERE d.id_empresa = ?
            UNION ALL
            SELECT d.id_datacenter, 'datacenter'
            FROM datacenter d
            WHERE d.id_empresa = ?
        ) x ON ea.id_objeto = x.id_objeto AND ea.tipo_objeto = x.tipo_objeto
        ORDER BY ea.fecha_evento DESC
    ");
    $stmt->execute([$id_empresa, $id_empresa, $id_empresa, $id_empresa]);
} elseif ($rol === "Datacenter") {
    $stmt = $pdo->prepare("
        SELECT ea.*, a.nombre_alerta, a.severidad
        FROM evento_alerta ea
        INNER JOIN alerta a ON ea.id_alerta = a.id_alerta
        INNER JOIN (
            SELECT i.id_interface AS id_objeto, 'interface' AS tipo_objeto
            FROM interface_red i
            INNER JOIN equipo e ON i.id_equipo = e.id_equipo
            INNER JOIN nodo n ON e.id_nodo = n.id_nodo
            WHERE n.id_datacenter = ?
            UNION ALL
            SELECT e.id_equipo, 'equipo'
            FROM equipo e
            INNER JOIN nodo n ON e.id_nodo = n.id_nodo
            WHERE n.id_datacenter = ?
            UNION ALL
            SELECT n.id_nodo, 'nodo'
            FROM nodo n
            WHERE n.id_datacenter = ?
            UNION ALL
            SELECT ?, 'datacenter'
        ) x ON ea.id_objeto = x.id_objeto AND ea.tipo_objeto = x.tipo_objeto
        ORDER BY ea.fecha_evento DESC
    ");
    $stmt->execute([$id_datacenter, $id_datacenter, $id_datacenter, $id_datacenter]);
} elseif ($rol === "Nodo") {
    $stmt = $pdo->prepare("
        SELECT ea.*, a.nombre_alerta, a.severidad
        FROM evento_alerta ea
        INNER JOIN alerta a ON ea.id_alerta = a.id_alerta
        INNER JOIN (
            SELECT i.id_interface AS id_objeto, 'interface' AS tipo_objeto
            FROM interface_red i
            INNER JOIN equipo e ON i.id_equipo = e.id_equipo
            WHERE e.id_nodo = ?
            UNION ALL
            SELECT e.id_equipo, 'equipo'
            FROM equipo e
            WHERE e.id_nodo = ?
            UNION ALL
            SELECT ?, 'nodo'
        ) x ON ea.id_objeto = x.id_objeto AND ea.tipo_objeto = x.tipo_objeto
        ORDER BY ea.fecha_evento DESC
    ");
    $stmt->execute([$id_nodo, $id_nodo, $id_nodo]);
} elseif ($rol === "Equipo") {
    $stmt = $pdo->prepare("
        SELECT ea.*, a.nombre_alerta, a.severidad
        FROM evento_alerta ea
        INNER JOIN alerta a ON ea.id_alerta = a.id_alerta
        INNER JOIN (
            SELECT id_interface AS id_objeto, 'interface' AS tipo_objeto
            FROM interface_red
            WHERE id_equipo = ?
            UNION ALL
            SELECT ?, 'equipo'
        ) x ON ea.id_objeto = x.id_objeto AND ea.tipo_objeto = x.tipo_objeto
        ORDER BY ea.fecha_evento DESC
    ");
    $stmt->execute([$id_equipo, $id_equipo]);
} else {
    $stmt = $pdo->prepare("
        SELECT ea.*, a.nombre_alerta, a.severidad
        FROM evento_alerta ea
        INNER JOIN alerta a ON ea.id_alerta = a.id_alerta
        WHERE 1=0
    ");
    $stmt->execute();
}

$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alertas - SYSLOG</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="main-layout">
    <?php include "../includes/menu.php"; ?>
    <div class="content">
        <div class="topbar">
            <div>
                <h1>Alertas</h1>
                <p class="muted-text">Definiciones y eventos visibles según el rol</p>
            </div>
            <div class="user-box"><?php echo htmlspecialchars($_SESSION["usuario"]); ?> | <?php echo htmlspecialchars($rol); ?></div>
        </div>

        <?php if (isset($_GET["ok"])): ?>
            <div class="alert success">Alerta guardada correctamente.</div>
        <?php endif; ?>

        <?php if ($esAdmin): ?>
        <div class="section">
            <div class="section-header"><h2>Nueva definición de alerta</h2></div>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group"><label>Nombre</label><input type="text" name="nombre_alerta" required></div>
                    <div class="form-group">
                        <label>Severidad</label>
                        <select name="severidad">
                            <option value="baja">Baja</option>
                            <option value="media">Media</option>
                            <option value="alta">Alta</option>
                            <option value="critica">Crítica</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tipo de objeto</label>
                        <select name="tipo_objeto">
                            <option value="datacenter">Datacenter</option>
                            <option value="nodo">Nodo</option>
                            <option value="equipo">Equipo</option>
                            <option value="interface">Interface</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Métrica</label><input type="text" name="metrica" required></div>
                    <div class="form-group">
                        <label>Condición</label>
                        <select name="condicion">
                            <option value=">">></option>
                            <option value="<"><</option>
                            <option value="=">=</option>
                            <option value=">=">>=</option>
                            <option value="<="><=</option>
                            <option value="fuera_rango">Fuera de rango</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Valor referencia</label><input type="number" step="0.01" name="valor_referencia"></div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado">
                            <option value="activa">Activa</option>
                            <option value="inactiva">Inactiva</option>
                        </select>
                    </div>
                </div>
                <div class="form-group"><label>Descripción</label><textarea name="descripcion"></textarea></div>
                <button class="btn btn-primary" type="submit">Guardar alerta</button>
            </form>
        </div>
        <?php endif; ?>

        <div class="section">
            <div class="section-header"><h2>Definiciones de alertas</h2></div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Severidad</th>
                            <th>Tipo</th>
                            <th>Métrica</th>
                            <th>Condición</th>
                            <th>Valor</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alertasDef as $a): ?>
                        <tr>
                            <td><?php echo $a["id_alerta"]; ?></td>
                            <td><?php echo htmlspecialchars($a["nombre_alerta"]); ?></td>
                            <td><?php echo htmlspecialchars($a["severidad"]); ?></td>
                            <td><?php echo htmlspecialchars($a["tipo_objeto"]); ?></td>
                            <td><?php echo htmlspecialchars($a["metrica"]); ?></td>
                            <td><?php echo htmlspecialchars($a["condicion"]); ?></td>
                            <td><?php echo htmlspecialchars($a["valor_referencia"]); ?></td>
                            <td><?php echo htmlspecialchars($a["estado"]); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$alertasDef): ?>
                        <tr><td colspan="8">No hay definiciones disponibles.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section">
            <div class="section-header"><h2>Eventos de alerta</h2></div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID evento</th>
                            <th>Alerta</th>
                            <th>Severidad</th>
                            <th>Tipo objeto</th>
                            <th>ID objeto</th>
                            <th>Valor detectado</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eventos as $e): ?>
                        <tr>
                            <td><?php echo $e["id_evento"]; ?></td>
                            <td><?php echo htmlspecialchars($e["nombre_alerta"]); ?></td>
                            <td><?php echo htmlspecialchars($e["severidad"]); ?></td>
                            <td><?php echo htmlspecialchars($e["tipo_objeto"]); ?></td>
                            <td><?php echo htmlspecialchars($e["id_objeto"]); ?></td>
                            <td><?php echo htmlspecialchars($e["valor_detectado"]); ?></td>
                            <td><?php echo htmlspecialchars($e["fecha_evento"]); ?></td>
                            <td><?php echo htmlspecialchars($e["estado_evento"]); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$eventos): ?>
                        <tr><td colspan="8">No hay eventos visibles para este usuario.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>