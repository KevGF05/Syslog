<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";

/* =========================
   CONTADORES PRINCIPALES
========================= */
$total_empresas = $pdo->query("SELECT COUNT(*) FROM empresa")->fetchColumn();
$total_ciudades = $pdo->query("SELECT COUNT(*) FROM ciudad")->fetchColumn();
$total_datacenters = $pdo->query("SELECT COUNT(*) FROM datacenter")->fetchColumn();
$total_nodos = $pdo->query("SELECT COUNT(*) FROM nodo")->fetchColumn();
$total_equipos = $pdo->query("SELECT COUNT(*) FROM equipo")->fetchColumn();
$total_interfaces = $pdo->query("SELECT COUNT(*) FROM interface_red")->fetchColumn();
$total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuario")->fetchColumn();
$total_alertas_activas = $pdo->query("SELECT COUNT(*) FROM evento_alerta WHERE estado_evento = 'activa'")->fetchColumn();

/* =========================
   ALERTAS CRÍTICAS
========================= */
$total_alertas_criticas = $pdo->query("
    SELECT COUNT(*) 
    FROM evento_alerta ea
    INNER JOIN alerta a ON ea.id_alerta = a.id_alerta
    WHERE ea.estado_evento = 'activa'
      AND a.severidad = 'critica'
")->fetchColumn();

/* =========================
   ÚLTIMAS ALERTAS
========================= */
$sql_alertas = "
    SELECT 
        ea.id_evento,
        a.nombre_alerta,
        a.severidad,
        ea.tipo_objeto,
        ea.id_objeto,
        ea.valor_detectado,
        ea.fecha_evento,
        ea.estado_evento,
        ea.mensaje
    FROM evento_alerta ea
    INNER JOIN alerta a ON ea.id_alerta = a.id_alerta
    ORDER BY ea.fecha_evento DESC
    LIMIT 8
";
$ultimas_alertas = $pdo->query($sql_alertas)->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   EQUIPOS RECIENTES
========================= */
$sql_equipos = "
    SELECT 
        e.id_equipo,
        e.nombre,
        t.nombre_tipo,
        m.nombre_marca,
        n.nombre AS nodo,
        e.ip_gestion,
        e.estado
    FROM equipo e
    INNER JOIN tipo_equipo t ON e.id_tipo_equipo = t.id_tipo_equipo
    INNER JOIN marca m ON e.id_marca = m.id_marca
    INNER JOIN nodo n ON e.id_nodo = n.id_nodo
    ORDER BY e.id_equipo DESC
    LIMIT 8
";
$equipos = $pdo->query($sql_equipos)->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   INTERFACES RECIENTES
========================= */
$sql_interfaces = "
    SELECT 
        i.id_interface,
        i.nombre_interface,
        i.tipo_interface,
        i.estado,
        i.trafico_actual,
        i.direccion_ip,
        e.nombre AS equipo
    FROM interface_red i
    INNER JOIN equipo e ON i.id_equipo = e.id_equipo
    ORDER BY i.id_interface DESC
    LIMIT 8
";
$interfaces = $pdo->query($sql_interfaces)->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   FUNCIÓN DE BADGES
========================= */
function badgeClase($valor) {
    $valor = strtolower(trim($valor));

    if (in_array($valor, ["activo", "up", "enviado"])) {
        return "badge badge-success";
    }

    if (in_array($valor, ["activa", "alta", "critica", "crítica", "down", "bloqueado", "fallido"])) {
        return "badge badge-danger";
    }

    if (in_array($valor, ["warning", "media", "pendiente", "mantenimiento"])) {
        return "badge badge-warning";
    }

    if (in_array($valor, ["atendida", "cerrada", "inactivo", "disabled"])) {
        return "badge badge-info";
    }

    return "badge badge-gray";
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

    <!-- SIDEBAR -->
    <?php include "../includes/menu.php"; ?>

    <!-- CONTENIDO -->
    <div class="content">

        <div class="topbar">
            <div>
                <h1>Dashboard</h1>
                <p class="muted-text">Resumen general del sistema SYSLOG</p>
            </div>
            <div class="user-box">
                <?php echo htmlspecialchars($_SESSION["usuario"]); ?> | <?php echo htmlspecialchars($_SESSION["rol"]); ?>
            </div>
        </div>

        <!-- TARJETAS -->
        <div class="cards">
            <div class="card blue">
                <h3>Empresas</h3>
                <p><?php echo $total_empresas; ?></p>
                <small>Registradas en el sistema</small>
            </div>

            <div class="card green">
                <h3>Ciudades</h3>
                <p><?php echo $total_ciudades; ?></p>
                <small>Ubicaciones disponibles</small>
            </div>

            <div class="card cyan">
                <h3>Datacenters</h3>
                <p><?php echo $total_datacenters; ?></p>
                <small>Centros de datos</small>
            </div>

            <div class="card gray">
                <h3>Nodos</h3>
                <p><?php echo $total_nodos; ?></p>
                <small>Nodos registrados</small>
            </div>

            <div class="card orange">
                <h3>Equipos</h3>
                <p><?php echo $total_equipos; ?></p>
                <small>Equipos en infraestructura</small>
            </div>

            <div class="card blue">
                <h3>Interfaces</h3>
                <p><?php echo $total_interfaces; ?></p>
                <small>Interfaces monitoreadas</small>
            </div>

            <div class="card red">
                <h3>Alertas activas</h3>
                <p><?php echo $total_alertas_activas; ?></p>
                <small>Eventos pendientes</small>
            </div>

            <div class="card red">
                <h3>Alertas críticas</h3>
                <p><?php echo $total_alertas_criticas; ?></p>
                <small>Alta prioridad</small>
            </div>

            <div class="card green">
                <h3>Usuarios</h3>
                <p><?php echo $total_usuarios; ?></p>
                <small>Usuarios registrados</small>
            </div>
        </div>

        <!-- ALERTAS RECIENTES -->
        <div class="section">
            <div class="section-header">
                <div>
                    <h2>Últimas alertas</h2>
                    <p>Eventos recientes registrados en el sistema</p>
                </div>
                <a href="alertas.php" class="btn btn-primary">Ver alertas</a>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID Evento</th>
                            <th>Alerta</th>
                            <th>Severidad</th>
                            <th>Tipo objeto</th>
                            <th>ID objeto</th>
                            <th>Valor detectado</th>
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
                                    <td>
                                        <span class="<?php echo badgeClase($alerta["severidad"]); ?>">
                                            <?php echo htmlspecialchars($alerta["severidad"]); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($alerta["tipo_objeto"]); ?></td>
                                    <td><?php echo htmlspecialchars($alerta["id_objeto"]); ?></td>
                                    <td><?php echo htmlspecialchars($alerta["valor_detectado"]); ?></td>
                                    <td>
                                        <span class="<?php echo badgeClase($alerta["estado_evento"]); ?>">
                                            <?php echo htmlspecialchars($alerta["estado_evento"]); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($alerta["fecha_evento"]); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">No hay alertas registradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- EQUIPOS -->
        <div class="section">
            <div class="section-header">
                <div>
                    <h2>Equipos recientes</h2>
                    <p>Últimos equipos agregados al sistema</p>
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
                                    <td>
                                        <span class="<?php echo badgeClase($equipo["estado"]); ?>">
                                            <?php echo htmlspecialchars($equipo["estado"]); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No hay equipos registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- INTERFACES -->
        <div class="section">
            <div class="section-header">
                <div>
                    <h2>Interfaces recientes</h2>
                    <p>Estado actual de interfaces monitoreadas</p>
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
                            <th>Tráfico actual</th>
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
                                    <td>
                                        <span class="<?php echo badgeClase($interface["estado"]); ?>">
                                            <?php echo htmlspecialchars($interface["estado"]); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format((float)$interface["trafico_actual"], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No hay interfaces registradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="footer">
            SYSLOG © 2026 | Dashboard principal
        </div>
    </div>
</div>

</body>
</html>