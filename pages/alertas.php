<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
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

$alertas = $pdo->query("SELECT * FROM alerta ORDER BY id_alerta DESC")->fetchAll(PDO::FETCH_ASSOC);
$eventos = $pdo->query("
    SELECT ea.*, a.nombre_alerta, a.severidad
    FROM evento_alerta ea
    INNER JOIN alerta a ON ea.id_alerta = a.id_alerta
    ORDER BY ea.fecha_evento DESC
")->fetchAll(PDO::FETCH_ASSOC);
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
        <div><h1>Alertas</h1><p class="muted-text">Definición y eventos de alertas</p></div>
        <div class="user-box"><?php echo $_SESSION["usuario"]; ?></div>
    </div>

    <?php if (isset($_GET["ok"])): ?><div class="alert success">Alerta guardada correctamente.</div><?php endif; ?>

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
                    <?php foreach ($alertas as $a): ?>
                    <tr>
                        <td><?php echo $a["id_alerta"]; ?></td>
                        <td><?php echo htmlspecialchars($a["nombre_alerta"]); ?></td>
                        <td><span class="badge badge-warning"><?php echo $a["severidad"]; ?></span></td>
                        <td><?php echo $a["tipo_objeto"]; ?></td>
                        <td><?php echo htmlspecialchars($a["metrica"]); ?></td>
                        <td><?php echo htmlspecialchars($a["condicion"]); ?></td>
                        <td><?php echo htmlspecialchars($a["valor_referencia"]); ?></td>
                        <td><span class="badge badge-info"><?php echo $a["estado"]; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
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
                        <th>Estado evento</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($eventos as $e): ?>
                    <tr>
                        <td><?php echo $e["id_evento"]; ?></td>
                        <td><?php echo htmlspecialchars($e["nombre_alerta"]); ?></td>
                        <td><span class="badge badge-danger"><?php echo $e["severidad"]; ?></span></td>
                        <td><?php echo $e["tipo_objeto"]; ?></td>
                        <td><?php echo $e["id_objeto"]; ?></td>
                        <td><?php echo htmlspecialchars($e["valor_detectado"]); ?></td>
                        <td><?php echo $e["fecha_evento"]; ?></td>
                        <td><span class="badge badge-info"><?php echo $e["estado_evento"]; ?></span></td>
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