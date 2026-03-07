<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";

$repNodos = $pdo->query("
    SELECT n.nombre, d.nombre AS datacenter, COUNT(e.id_equipo) AS total_equipos
    FROM nodo n
    INNER JOIN datacenter d ON n.id_datacenter = d.id_datacenter
    LEFT JOIN equipo e ON n.id_nodo = e.id_nodo
    GROUP BY n.id_nodo, n.nombre, d.nombre
    ORDER BY n.nombre
")->fetchAll(PDO::FETCH_ASSOC);

$repCiudades = $pdo->query("
    SELECT c.nombre, COUNT(dc.id_datacenter) AS total_datacenters
    FROM ciudad c
    LEFT JOIN datacenter dc ON c.id_ciudad = dc.id_ciudad
    GROUP BY c.id_ciudad, c.nombre
    ORDER BY c.nombre
")->fetchAll(PDO::FETCH_ASSOC);

$repMarcas = $pdo->query("
    SELECT m.nombre_marca, COUNT(e.id_equipo) AS total_equipos
    FROM marca m
    LEFT JOIN equipo e ON m.id_marca = e.id_marca
    GROUP BY m.id_marca, m.nombre_marca
    ORDER BY m.nombre_marca
")->fetchAll(PDO::FETCH_ASSOC);

$repTipos = $pdo->query("
    SELECT t.nombre_tipo, COUNT(e.id_equipo) AS total_equipos
    FROM tipo_equipo t
    LEFT JOIN equipo e ON t.id_tipo_equipo = e.id_tipo_equipo
    GROUP BY t.id_tipo_equipo, t.nombre_tipo
    ORDER BY t.nombre_tipo
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
        <div><h1>Reportes</h1><p class="muted-text">Reportes avanzados del sistema</p></div>
        <div class="user-box"><?php echo $_SESSION["usuario"]; ?></div>
    </div>

    <div class="section">
        <div class="section-header"><h2>Reporte por nodo</h2></div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Nodo</th><th>Datacenter</th><th>Total equipos</th></tr></thead>
                <tbody>
                    <?php foreach ($repNodos as $r): ?>
                    <tr><td><?php echo htmlspecialchars($r["nombre"]); ?></td><td><?php echo htmlspecialchars($r["datacenter"]); ?></td><td><?php echo $r["total_equipos"]; ?></td></tr>
                    <?php endforeach; ?>
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
                    <tr><td><?php echo $r["id_evento"]; ?></td><td><?php echo htmlspecialchars($r["nombre_alerta"]); ?></td><td><?php echo $r["severidad"]; ?></td><td><?php echo $r["estado_evento"]; ?></td><td><?php echo $r["fecha_evento"]; ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-header"><h2>Reporte por ciudad</h2></div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Ciudad</th><th>Total datacenters</th></tr></thead>
                <tbody>
                    <?php foreach ($repCiudades as $r): ?>
                    <tr><td><?php echo htmlspecialchars($r["nombre"]); ?></td><td><?php echo $r["total_datacenters"]; ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-header"><h2>Reporte por marca</h2></div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Marca</th><th>Total equipos</th></tr></thead>
                <tbody>
                    <?php foreach ($repMarcas as $r): ?>
                    <tr><td><?php echo htmlspecialchars($r["nombre_marca"]); ?></td><td><?php echo $r["total_equipos"]; ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-header"><h2>Reporte por tipo de equipo</h2></div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Tipo</th><th>Total equipos</th></tr></thead>
                <tbody>
                    <?php foreach ($repTipos as $r): ?>
                    <tr><td><?php echo htmlspecialchars($r["nombre_tipo"]); ?></td><td><?php echo $r["total_equipos"]; ?></td></tr>
                    <?php endforeach; ?>
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
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>
</body>
</html>