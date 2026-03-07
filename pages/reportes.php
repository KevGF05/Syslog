<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";
requireModuleAccess("reportes");

$scope = getScope();
$rol = $scope["rol"];
$id_empresa = $scope["id_empresa"];
$id_datacenter = $scope["id_datacenter"];
$id_nodo = $scope["id_nodo"];
$id_equipo = $scope["id_equipo"];

if ($rol === "Administrador") {
    $repNodos = $pdo->query("
        SELECT n.nombre, d.nombre AS datacenter, COUNT(e.id_equipo) AS total_equipos
        FROM nodo n
        INNER JOIN datacenter d ON n.id_datacenter = d.id_datacenter
        LEFT JOIN equipo e ON n.id_nodo = e.id_nodo
        GROUP BY n.id_nodo, n.nombre, d.nombre
        ORDER BY n.nombre
    ")->fetchAll(PDO::FETCH_ASSOC);

    $repInterfaces = $pdo->query("
        SELECT i.nombre_interface, e.nombre AS equipo, i.estado, i.trafico_actual
        FROM interface_red i
        INNER JOIN equipo e ON i.id_equipo = e.id_equipo
        ORDER BY i.id_interface DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $repAlertas = $pdo->query("
        SELECT ea.id_evento, a.nombre_alerta, a.severidad, ea.estado_evento, ea.fecha_evento
        FROM evento_alerta ea
        INNER JOIN alerta a ON ea.id_alerta = a.id_alerta
        ORDER BY ea.fecha_evento DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} elseif ($rol === "Empresa" || $rol === "Operador") {
    $stmt = $pdo->prepare("
        SELECT n.nombre, d.nombre AS datacenter, COUNT(e.id_equipo) AS total_equipos
        FROM nodo n
        INNER JOIN datacenter d ON n.id_datacenter = d.id_datacenter
        LEFT JOIN equipo e ON n.id_nodo = e.id_nodo
        WHERE d.id_empresa = ?
        GROUP BY n.id_nodo, n.nombre, d.nombre
        ORDER BY n.nombre
    ");
    $stmt->execute([$id_empresa]);
    $repNodos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT i.nombre_interface, e.nombre AS equipo, i.estado, i.trafico_actual
        FROM interface_red i
        INNER JOIN equipo e ON i.id_equipo = e.id_equipo
        INNER JOIN nodo n ON e.id_nodo = n.id_nodo
        INNER JOIN datacenter d ON n.id_datacenter = d.id_datacenter
        WHERE d.id_empresa = ?
        ORDER BY i.id_interface DESC
    ");
    $stmt->execute([$id_empresa]);
    $repInterfaces = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT ea.id_evento, a.nombre_alerta, a.severidad, ea.estado_evento, ea.fecha_evento
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
    $repAlertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($rol === "Datacenter") {
    $stmt = $pdo->prepare("
        SELECT n.nombre, d.nombre AS datacenter, COUNT(e.id_equipo) AS total_equipos
        FROM nodo n
        INNER JOIN datacenter d ON n.id_datacenter = d.id_datacenter
        LEFT JOIN equipo e ON n.id_nodo = e.id_nodo
        WHERE n.id_datacenter = ?
        GROUP BY n.id_nodo, n.nombre, d.nombre
        ORDER BY n.nombre
    ");
    $stmt->execute([$id_datacenter]);
    $repNodos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT i.nombre_interface, e.nombre AS equipo, i.estado, i.trafico_actual
        FROM interface_red i
        INNER JOIN equipo e ON i.id_equipo = e.id_equipo
        INNER JOIN nodo n ON e.id_nodo = n.id_nodo
        WHERE n.id_datacenter = ?
        ORDER BY i.id_interface DESC
    ");
    $stmt->execute([$id_datacenter]);
    $repInterfaces = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT ea.id_evento, a.nombre_alerta, a.severidad, ea.estado_evento, ea.fecha_evento
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
    $repAlertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($rol === "Nodo") {
    $stmt = $pdo->prepare("
        SELECT n.nombre, d.nombre AS datacenter, COUNT(e.id_equipo) AS total_equipos
        FROM nodo n
        INNER JOIN datacenter d ON n.id_datacenter = d.id_datacenter
        LEFT JOIN equipo e ON n.id_nodo = e.id_nodo
        WHERE n.id_nodo = ?
        GROUP BY n.id_nodo, n.nombre, d.nombre
    ");
    $stmt->execute([$id_nodo]);
    $repNodos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT i.nombre_interface, e.nombre AS equipo, i.estado, i.trafico_actual
        FROM interface_red i
        INNER JOIN equipo e ON i.id_equipo = e.id_equipo
        WHERE e.id_nodo = ?
        ORDER BY i.id_interface DESC
    ");
    $stmt->execute([$id_nodo]);
    $repInterfaces = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT ea.id_evento, a.nombre_alerta, a.severidad, ea.estado_evento, ea.fecha_evento
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
    $repAlertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($rol === "Equipo") {
    $stmt = $pdo->prepare("
        SELECT n.nombre, d.nombre AS datacenter, 1 AS total_equipos
        FROM equipo e
        INNER JOIN nodo n ON e.id_nodo = n.id_nodo
        INNER JOIN datacenter d ON n.id_datacenter = d.id_datacenter
        WHERE e.id_equipo = ?
    ");
    $stmt->execute([$id_equipo]);
    $repNodos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT i.nombre_interface, e.nombre AS equipo, i.estado, i.trafico_actual
        FROM interface_red i
        INNER JOIN equipo e ON i.id_equipo = e.id_equipo
        WHERE i.id_equipo = ?
        ORDER BY i.id_interface DESC
    ");
    $stmt->execute([$id_equipo]);
    $repInterfaces = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT ea.id_evento, a.nombre_alerta, a.severidad, ea.estado_evento, ea.fecha_evento
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
    $repAlertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $repNodos = [];
    $repInterfaces = [];
    $repAlertas = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes - SYSLOG</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="main-layout">
    <?php include "../includes/menu.php"; ?>
    <div class="content">
        <div class="topbar">
            <div>
                <h1>Reportes</h1>
                <p class="muted-text">Reportes visibles según el rol</p>
            </div>
            <div class="user-box"><?php echo htmlspecialchars($_SESSION["usuario"]); ?> | <?php echo htmlspecialchars($rol); ?></div>
        </div>

        <div class="section">
            <div class="section-header"><h2>Reporte por nodo</h2></div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Nodo</th><th>Datacenter</th><th>Total equipos</th></tr></thead>
                    <tbody>
                        <?php foreach ($repNodos as $r): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r["nombre"]); ?></td>
                            <td><?php echo htmlspecialchars($r["datacenter"]); ?></td>
                            <td><?php echo $r["total_equipos"]; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$repNodos): ?>
                        <tr><td colspan="3">No hay datos disponibles.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section">
            <div class="section-header"><h2>Reporte por eventos o alertas</h2></div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>ID</th><th>Alerta</th><th>Severidad</th><th>Estado</th><th>Fecha</th></tr></thead>
                    <tbody>
                        <?php foreach ($repAlertas as $r): ?>
                        <tr>
                            <td><?php echo $r["id_evento"]; ?></td>
                            <td><?php echo htmlspecialchars($r["nombre_alerta"]); ?></td>
                            <td><?php echo htmlspecialchars($r["severidad"]); ?></td>
                            <td><?php echo htmlspecialchars($r["estado_evento"]); ?></td>
                            <td><?php echo htmlspecialchars($r["fecha_evento"]); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$repAlertas): ?>
                        <tr><td colspan="5">No hay datos disponibles.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section">
            <div class="section-header"><h2>Reporte por status de interfaces</h2></div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Interfaz</th><th>Equipo</th><th>Estado</th><th>Tráfico</th></tr></thead>
                    <tbody>
                        <?php foreach ($repInterfaces as $r): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r["nombre_interface"]); ?></td>
                            <td><?php echo htmlspecialchars($r["equipo"]); ?></td>
                            <td><?php echo htmlspecialchars($r["estado"]); ?></td>
                            <td><?php echo number_format((float)$r["trafico_actual"], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$repInterfaces): ?>
                        <tr><td colspan="4">No hay datos disponibles.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>