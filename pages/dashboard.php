<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";

requireModuleAccess("dashboard");

$scope = getScope();
$rol = $scope["rol"];
$id_empresa = $scope["id_empresa"];
$id_datacenter = $scope["id_datacenter"];
$id_nodo = $scope["id_nodo"];
$id_equipo = $scope["id_equipo"];

function badgeClase($valor) {
    $valor = strtolower(trim($valor));

    if (in_array($valor, ["activo", "up", "enviado"])) return "badge badge-success";
    if (in_array($valor, ["activa", "alta", "critica", "crítica", "down", "fallido", "bloqueado"])) return "badge badge-danger";
    if (in_array($valor, ["warning", "media", "pendiente", "mantenimiento"])) return "badge badge-warning";
    if (in_array($valor, ["cerrada", "atendida", "inactivo", "disabled"])) return "badge badge-info";

    return "badge badge-gray";
}

$total_empresas = 0;
$total_ciudades = 0;
$total_datacenters = 0;
$total_nodos = 0;
$total_equipos = 0;
$total_interfaces = 0;
$total_alertas_activas = 0;
$total_alertas_criticas = 0;
$total_usuarios = 0;

$ultimas_alertas = [];
$equipos = [];
$interfaces = [];

if ($rol === "Administrador") {
    $total_empresas = $pdo->query("SELECT COUNT(*) FROM empresa")->fetchColumn();
    $total_ciudades = $pdo->query("SELECT COUNT(*) FROM ciudad")->fetchColumn();
    $total_datacenters = $pdo->query("SELECT COUNT(*) FROM datacenter")->fetchColumn();
    $total_nodos = $pdo->query("SELECT COUNT(*) FROM nodo")->fetchColumn();
    $total_equipos = $pdo->query("SELECT COUNT(*) FROM equipo")->fetchColumn();
    $total_interfaces = $pdo->query("SELECT COUNT(*) FROM interface_red")->fetchColumn();
    $total_alertas_activas = $pdo->query("SELECT COUNT(*) FROM evento_alerta WHERE estado_evento = 'activa'")->fetchColumn();
    $total_alertas_criticas = $pdo->query("
        SELECT COUNT(*)
        FROM evento_alerta ea
        INNER JOIN alerta a ON ea.id_alerta = a.id_alerta
        WHERE ea.estado_evento = 'activa'
          AND a.severidad = 'critica'
    ")->fetchColumn();
    $total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuario")->fetchColumn();

    $ultimas_alertas = $pdo->query("
        SELECT ea.id_evento, a.nombre_alerta, a.severidad, ea.tipo_objeto, ea.id_objeto,
               ea.valor_detectado, ea.fecha_evento, ea.estado_evento, ea.mensaje
        FROM evento_alerta ea
        INNER JOIN alerta a ON ea.id_alerta = a.id_alerta
        ORDER BY ea.fecha_evento DESC
        LIMIT 8
    ")->fetchAll(PDO::FETCH_ASSOC);

    $equipos = $pdo->query("
        SELECT e.id_equipo, e.nombre, t.nombre_tipo, m.nombre_marca, n.nombre AS nodo,
               e.ip_gestion, e.estado
        FROM equipo e
        INNER JOIN tipo_equipo t ON e.id_tipo_equipo = t.id_tipo_equipo
        INNER JOIN marca m ON e.id_marca = m.id_marca
        INNER JOIN nodo n ON e.id_nodo = n.id_nodo
        ORDER BY e.id_equipo DESC
        LIMIT 8
    ")->fetchAll(PDO::FETCH_ASSOC);

    $interfaces = $pdo->query("
        SELECT i.id_interface, i.nombre_interface, i.tipo_interface, i.estado,
               i.trafico_actual, i.direccion_ip, e.nombre AS equipo
        FROM interface_red i
        INNER JOIN equipo e ON i.id_equipo = e.id_equipo
        ORDER BY i.id_interface DESC
        LIMIT 8
    ")->fetchAll(PDO::FETCH_ASSOC);
}

if ($rol === "Empresa" || $rol === "Operador") {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM empresa WHERE id_empresa = ?");
    $stmt->execute([$id_empresa]);
    $total_empresas = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT c.id_ciudad)
        FROM ciudad c
        INNER JOIN datacenter d ON c.id_ciudad = d.id_ciudad
        WHERE d.id_empresa = ?
    ");
    $stmt->execute([$id_empresa]);
    $total_ciudades = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM datacenter WHERE id_empresa = ?");
    $stmt->execute([$id_empresa]);
    $total_datacenters = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM nodo n
        INNER JOIN datacenter d ON n.id_datacenter = d.id_datacenter
        WHERE d.id_empresa = ?
    ");
    $stmt->execute([$id_empresa]);
    $total_nodos = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM equipo e
        INNER JOIN nodo n ON e.id_nodo = n.id_nodo
        INNER JOIN datacenter d ON n.id_datacenter = d.id_datacenter
        WHERE d.id_empresa = ?
    ");
    $stmt->execute([$id_empresa]);
    $total_equipos = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM interface_red i
        INNER JOIN equipo e ON i.id_equipo = e.id_equipo
        INNER JOIN nodo n ON e.id_nodo = n.id_nodo
        INNER JOIN datacenter d ON n.id_datacenter = d.id_datacenter
        WHERE d.id_empresa = ?
    ");
    $stmt->execute([$id_empresa]);
    $total_interfaces = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM evento_alerta ea
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
        WHERE ea.estado_evento = 'activa'
    ");
    $stmt->execute([$id_empresa, $id_empresa, $id_empresa, $id_empresa]);
    $total_alertas_activas = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
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
        WHERE ea.estado_evento = 'activa'
          AND a.severidad = 'critica'
    ");
    $stmt->execute([$id_empresa, $id_empresa, $id_empresa, $id_empresa]);
    $total_alertas_criticas = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuario WHERE id_empresa = ?");
    $stmt->execute([$id_empresa]);
    $total_usuarios = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT ea.id_evento, a.nombre_alerta, a.severidad, ea.tipo_objeto, ea.id_objeto,
               ea.valor_detectado, ea.fecha_evento, ea.estado_evento, ea.mensaje
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
        LIMIT 8
    ");
    $stmt->execute([$id_empresa, $id_empresa, $id_empresa, $id_empresa]);
    $ultimas_alertas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT e.id_equipo, e.nombre, t.nombre_tipo, m.nombre_marca, n.nombre AS nodo,
               e.ip_gestion, e.estado
        FROM equipo e
        INNER JOIN tipo_equipo t ON e.id_tipo_equipo = t.id_tipo_equipo
        INNER JOIN marca m ON e.id_marca = m.id_marca
        INNER JOIN nodo n ON e.id_nodo = n.id_nodo
        INNER JOIN datacenter d ON n.id_datacenter = d.id_datacenter
        WHERE d.id_empresa = ?
        ORDER BY e.id_equipo DESC
        LIMIT 8
    ");
    $stmt->execute([$id_empresa]);
    $equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT i.id_interface, i.nombre_interface, i.tipo_interface, i.estado,
               i.trafico_actual, i.direccion_ip, e.nombre AS equipo
        FROM interface_red i
        INNER JOIN equipo e ON i.id_equipo = e.id_equipo
        INNER JOIN nodo n ON e.id_nodo = n.id_nodo
        INNER JOIN datacenter d ON n.id_datacenter = d.id_datacenter
        WHERE d.id_empresa = ?
        ORDER BY i.id_interface DESC
        LIMIT 8
    ");
    $stmt->execute([$id_empresa]);
    $interfaces = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($rol === "Datacenter") {
    $total_empresas = $id_empresa ? 1 : 0;
    $total_ciudades = 1;
    $total_datacenters = 1;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM nodo WHERE id_datacenter = ?");
    $stmt->execute([$id_datacenter]);
    $total_nodos = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM equipo e
        INNER JOIN nodo n ON e.id_nodo = n.id_nodo
        WHERE n.id_datacenter = ?
    ");
    $stmt->execute([$id_datacenter]);
    $total_equipos = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM interface_red i
        INNER JOIN equipo e ON i.id_equipo = e.id_equipo
        INNER JOIN nodo n ON e.id_nodo = n.id_nodo
        WHERE n.id_datacenter = ?
    ");
    $stmt->execute([$id_datacenter]);
    $total_interfaces = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM evento_alerta ea
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
        WHERE ea.estado_evento = 'activa'
    ");
    $stmt->execute([$id_datacenter, $id_datacenter, $id_datacenter, $id_datacenter]);
    $total_alertas_activas = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
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
        WHERE ea.estado_evento = 'activa'
          AND a.severidad = 'critica'
    ");
    $stmt->execute([$id_datacenter, $id_datacenter, $id_datacenter, $id_datacenter]);
    $total_alertas_criticas = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT ea.id_evento, a.nombre_alerta, a.severidad, ea.tipo_objeto, ea.id_objeto,
               ea.valor_detectado, ea.fecha_evento, ea.estado_evento, ea.mensaje
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
        LIMIT 8
    ");
    $stmt->execute([$id_datacenter, $id_datacenter, $id_datacenter, $id_datacenter]);
    $ultimas_alertas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT e.id_equipo, e.nombre, t.nombre_tipo, m.nombre_marca, n.nombre AS nodo,
               e.ip_gestion, e.estado
        FROM equipo e
        INNER JOIN tipo_equipo t ON e.id_tipo_equipo = t.id_tipo_equipo
        INNER JOIN marca m ON e.id_marca = m.id_marca
        INNER JOIN nodo n ON e.id_nodo = n.id_nodo
        WHERE n.id_datacenter = ?
        ORDER BY e.id_equipo DESC
        LIMIT 8
    ");
    $stmt->execute([$id_datacenter]);
    $equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT i.id_interface, i.nombre_interface, i.tipo_interface, i.estado,
               i.trafico_actual, i.direccion_ip, e.nombre AS equipo
        FROM interface_red i
        INNER JOIN equipo e ON i.id_equipo = e.id_equipo
        INNER JOIN nodo n ON e.id_nodo = n.id_nodo
        WHERE n.id_datacenter = ?
        ORDER BY i.id_interface DESC
        LIMIT 8
    ");
    $stmt->execute([$id_datacenter]);
    $interfaces = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($rol === "Nodo") {
    $total_nodos = 1;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM equipo WHERE id_nodo = ?");
    $stmt->execute([$id_nodo]);
    $total_equipos = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM interface_red i
        INNER JOIN equipo e ON i.id_equipo = e.id_equipo
        WHERE e.id_nodo = ?
    ");
    $stmt->execute([$id_nodo]);
    $total_interfaces = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT ea.id_evento, a.nombre_alerta, a.severidad, ea.tipo_objeto, ea.id_objeto,
               ea.valor_detectado, ea.fecha_evento, ea.estado_evento, ea.mensaje
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
        LIMIT 8
    ");
    $stmt->execute([$id_nodo, $id_nodo, $id_nodo]);
    $ultimas_alertas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT e.id_equipo, e.nombre, t.nombre_tipo, m.nombre_marca, n.nombre AS nodo,
               e.ip_gestion, e.estado
        FROM equipo e
        INNER JOIN tipo_equipo t ON e.id_tipo_equipo = t.id_tipo_equipo
        INNER JOIN marca m ON e.id_marca = m.id_marca
        INNER JOIN nodo n ON e.id_nodo = n.id_nodo
        WHERE e.id_nodo = ?
        ORDER BY e.id_equipo DESC
        LIMIT 8
    ");
    $stmt->execute([$id_nodo]);
    $equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT i.id_interface, i.nombre_interface, i.tipo_interface, i.estado,
               i.trafico_actual, i.direccion_ip, e.nombre AS equipo
        FROM interface_red i
        INNER JOIN equipo e ON i.id_equipo = e.id_equipo
        WHERE e.id_nodo = ?
        ORDER BY i.id_interface DESC
        LIMIT 8
    ");
    $stmt->execute([$id_nodo]);
    $interfaces = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_alertas_activas = count(array_filter($ultimas_alertas, fn($a) => $a["estado_evento"] === "activa"));
    $total_alertas_criticas = count(array_filter($ultimas_alertas, fn($a) => strtolower($a["severidad"]) === "critica" && $a["estado_evento"] === "activa"));
}

if ($rol === "Equipo") {
    $total_equipos = 1;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM interface_red WHERE id_equipo = ?");
    $stmt->execute([$id_equipo]);
    $total_interfaces = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT ea.id_evento, a.nombre_alerta, a.severidad, ea.tipo_objeto, ea.id_objeto,
               ea.valor_detectado, ea.fecha_evento, ea.estado_evento, ea.mensaje
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
        LIMIT 8
    ");
    $stmt->execute([$id_equipo, $id_equipo]);
    $ultimas_alertas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT e.id_equipo, e.nombre, t.nombre_tipo, m.nombre_marca, n.nombre AS nodo,
               e.ip_gestion, e.estado
        FROM equipo e
        INNER JOIN tipo_equipo t ON e.id_tipo_equipo = t.id_tipo_equipo
        INNER JOIN marca m ON e.id_marca = m.id_marca
        INNER JOIN nodo n ON e.id_nodo = n.id_nodo
        WHERE e.id_equipo = ?
        LIMIT 1
    ");
    $stmt->execute([$id_equipo]);
    $equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT i.id_interface, i.nombre_interface, i.tipo_interface, i.estado,
               i.trafico_actual, i.direccion_ip, e.nombre AS equipo
        FROM interface_red i
        INNER JOIN equipo e ON i.id_equipo = e.id_equipo
        WHERE i.id_equipo = ?
        ORDER BY i.id_interface DESC
        LIMIT 8
    ");
    $stmt->execute([$id_equipo]);
    $interfaces = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_alertas_activas = count(array_filter($ultimas_alertas, fn($a) => $a["estado_evento"] === "activa"));
    $total_alertas_criticas = count(array_filter($ultimas_alertas, fn($a) => strtolower($a["severidad"]) === "critica" && $a["estado_evento"] === "activa"));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SYSLOG</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="main-layout">

    <?php include "../includes/menu.php"; ?>

    <div class="content">
        <div class="topbar">
            <div>
                <h1>Dashboard</h1>
                <p class="muted-text">Acceso según rol: <?php echo htmlspecialchars($rol); ?></p>
            </div>
            <div class="user-box">
                <?php echo htmlspecialchars($_SESSION["usuario"]); ?> | <?php echo htmlspecialchars($rol); ?>
            </div>
        </div>

        <?php if (isset($_GET["denied"])): ?>
            <div class="alert error">No tienes permiso para acceder a ese módulo.</div>
        <?php endif; ?>

        <div class="cards">
            <div class="card blue">
                <h3>Empresas</h3>
                <p><?php echo $total_empresas; ?></p>
            </div>
            <div class="card green">
                <h3>Ciudades</h3>
                <p><?php echo $total_ciudades; ?></p>
            </div>
            <div class="card cyan">
                <h3>Datacenters</h3>
                <p><?php echo $total_datacenters; ?></p>
            </div>
            <div class="card gray">
                <h3>Nodos</h3>
                <p><?php echo $total_nodos; ?></p>
            </div>
            <div class="card orange">
                <h3>Equipos</h3>
                <p><?php echo $total_equipos; ?></p>
            </div>
            <div class="card blue">
                <h3>Interfaces</h3>
                <p><?php echo $total_interfaces; ?></p>
            </div>
            <div class="card red">
                <h3>Alertas activas</h3>
                <p><?php echo $total_alertas_activas; ?></p>
            </div>
            <div class="card red">
                <h3>Alertas críticas</h3>
                <p><?php echo $total_alertas_criticas; ?></p>
            </div>
            <div class="card green">
                <h3>Usuarios</h3>
                <p><?php echo $total_usuarios; ?></p>
            </div>
        </div>

        <div class="section">
            <div class="section-header">
                <div>
                    <h2>Últimas alertas</h2>
                    <p>Solo las alertas permitidas para este usuario</p>
                </div>
                <?php if (canAccess("alertas")): ?>
                    <a href="alertas.php" class="btn btn-primary">Ver alertas</a>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID Evento</th>
                            <th>Alerta</th>
                            <th>Severidad</th>
                            <th>Tipo</th>
                            <th>ID Objeto</th>
                            <th>Valor</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($ultimas_alertas): ?>
                            <?php foreach ($ultimas_alertas as $alerta): ?>
                                <tr>
                                    <td><?php echo $alerta["id_evento"]; ?></td>
                                    <td><?php echo htmlspecialchars($alerta["nombre_alerta"]); ?></td>
                                    <td><span class="<?php echo badgeClase($alerta["severidad"]); ?>"><?php echo htmlspecialchars($alerta["severidad"]); ?></span></td>
                                    <td><?php echo htmlspecialchars($alerta["tipo_objeto"]); ?></td>
                                    <td><?php echo htmlspecialchars($alerta["id_objeto"]); ?></td>
                                    <td><?php echo htmlspecialchars($alerta["valor_detectado"]); ?></td>
                                    <td><span class="<?php echo badgeClase($alerta["estado_evento"]); ?>"><?php echo htmlspecialchars($alerta["estado_evento"]); ?></span></td>
                                    <td><?php echo htmlspecialchars($alerta["fecha_evento"]); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8">No hay alertas visibles para este usuario.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (canAccess("equipos")): ?>
        <div class="section">
            <div class="section-header">
                <div>
                    <h2>Equipos visibles</h2>
                    <p>Equipos disponibles según el alcance del rol</p>
                </div>
                <a href="equipos.php" class="btn btn-success">Ver equipos</a>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Equipo</th>
                            <th>Tipo</th>
                            <th>Marca</th>
                            <th>Nodo</th>
                            <th>IP gestión</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($equipos): ?>
                            <?php foreach ($equipos as $equipo): ?>
                                <tr>
                                    <td><?php echo $equipo["id_equipo"]; ?></td>
                                    <td><?php echo htmlspecialchars($equipo["nombre"]); ?></td>
                                    <td><?php echo htmlspecialchars($equipo["nombre_tipo"]); ?></td>
                                    <td><?php echo htmlspecialchars($equipo["nombre_marca"]); ?></td>
                                    <td><?php echo htmlspecialchars($equipo["nodo"]); ?></td>
                                    <td><?php echo htmlspecialchars($equipo["ip_gestion"]); ?></td>
                                    <td><span class="<?php echo badgeClase($equipo["estado"]); ?>"><?php echo htmlspecialchars($equipo["estado"]); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7">No hay equipos visibles para este usuario.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if (canAccess("interfaces")): ?>
        <div class="section">
            <div class="section-header">
                <div>
                    <h2>Interfaces visibles</h2>
                    <p>Interfaces disponibles según el alcance del rol</p>
                </div>
                <a href="interfaces.php" class="btn btn-warning">Ver interfaces</a>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Interfaz</th>
                            <th>Equipo</th>
                            <th>Tipo</th>
                            <th>IP</th>
                            <th>Estado</th>
                            <th>Tráfico</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($interfaces): ?>
                            <?php foreach ($interfaces as $interface): ?>
                                <tr>
                                    <td><?php echo $interface["id_interface"]; ?></td>
                                    <td><?php echo htmlspecialchars($interface["nombre_interface"]); ?></td>
                                    <td><?php echo htmlspecialchars($interface["equipo"]); ?></td>
                                    <td><?php echo htmlspecialchars($interface["tipo_interface"]); ?></td>
                                    <td><?php echo htmlspecialchars($interface["direccion_ip"]); ?></td>
                                    <td><span class="<?php echo badgeClase($interface["estado"]); ?>"><?php echo htmlspecialchars($interface["estado"]); ?></span></td>
                                    <td><?php echo number_format((float)$interface["trafico_actual"], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7">No hay interfaces visibles para este usuario.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <div class="footer">
            SYSLOG © 2026 | Dashboard con permisos por rol
        </div>
    </div>
</div>
</body>
</html>