<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";

$usuarios = $pdo->query("SELECT id_usuario, nombre FROM usuario ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$alertas = $pdo->query("SELECT id_alerta, nombre_alerta FROM alerta ORDER BY nombre_alerta")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST["tipo_form"] === "grupo_usuario") {
    $stmt = $pdo->prepare("INSERT INTO grupo_usuario (nombre_grupo, descripcion, estado) VALUES (?, ?, ?)");
    $stmt->execute([trim($_POST["nombre_grupo"]), trim($_POST["descripcion"]), $_POST["estado"]]);
    header("Location: grupos.php?ok=1");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST["tipo_form"] === "grupo_alerta") {
    $stmt = $pdo->prepare("INSERT INTO grupo_alerta (nombre_grupo_alerta, descripcion, estado) VALUES (?, ?, ?)");
    $stmt->execute([trim($_POST["nombre_grupo_alerta"]), trim($_POST["descripcion_alerta"]), $_POST["estado_alerta"]]);
    header("Location: grupos.php?ok=2");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST["tipo_form"] === "asignar_usuario") {
    $stmt = $pdo->prepare("INSERT IGNORE INTO detalle_grupo_usuario (id_grupo_usuario, id_usuario) VALUES (?, ?)");
    $stmt->execute([$_POST["id_grupo_usuario"], $_POST["id_usuario"]]);
    header("Location: grupos.php?ok=3");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST["tipo_form"] === "asignar_alerta") {
    $stmt = $pdo->prepare("INSERT IGNORE INTO detalle_grupo_alerta (id_grupo_alerta, id_alerta) VALUES (?, ?)");
    $stmt->execute([$_POST["id_grupo_alerta"], $_POST["id_alerta"]]);
    header("Location: grupos.php?ok=4");
    exit;
}

$gruposUsuario = $pdo->query("SELECT * FROM grupo_usuario ORDER BY id_grupo_usuario DESC")->fetchAll(PDO::FETCH_ASSOC);
$gruposAlerta = $pdo->query("SELECT * FROM grupo_alerta ORDER BY id_grupo_alerta DESC")->fetchAll(PDO::FETCH_ASSOC);

$detalleUsuarios = $pdo->query("
    SELECT gu.nombre_grupo, u.nombre
    FROM detalle_grupo_usuario d
    INNER JOIN grupo_usuario gu ON d.id_grupo_usuario = gu.id_grupo_usuario
    INNER JOIN usuario u ON d.id_usuario = u.id_usuario
    ORDER BY gu.nombre_grupo, u.nombre
")->fetchAll(PDO::FETCH_ASSOC);

$detalleAlertas = $pdo->query("
    SELECT ga.nombre_grupo_alerta, a.nombre_alerta
    FROM detalle_grupo_alerta d
    INNER JOIN grupo_alerta ga ON d.id_grupo_alerta = ga.id_grupo_alerta
    INNER JOIN alerta a ON d.id_alerta = a.id_alerta
    ORDER BY ga.nombre_grupo_alerta, a.nombre_alerta
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Grupos - SYSLOG</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="main-layout">
<?php include "../includes/menu.php"; ?>
<div class="content">
    <div class="topbar">
        <div><h1>Grupos</h1><p class="muted-text">Grupos de usuarios y grupos de alertas</p></div>
        <div class="user-box"><?php echo $_SESSION["usuario"]; ?></div>
    </div>

    <?php if (isset($_GET["ok"])): ?><div class="alert success">Operación realizada correctamente.</div><?php endif; ?>

    <div class="section">
        <div class="section-header"><h2>Crear grupo de usuarios</h2></div>
        <form method="POST">
            <input type="hidden" name="tipo_form" value="grupo_usuario">
            <div class="form-grid">
                <div class="form-group"><label>Nombre del grupo</label><input type="text" name="nombre_grupo" required></div>
                <div class="form-group">
                    <label>Estado</label>
                    <select name="estado">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="form-group"><label>Descripción</label><textarea name="descripcion"></textarea></div>
            <button class="btn btn-primary" type="submit">Guardar grupo usuario</button>
        </form>
    </div>

    <div class="section">
        <div class="section-header"><h2>Crear grupo de alertas</h2></div>
        <form method="POST">
            <input type="hidden" name="tipo_form" value="grupo_alerta">
            <div class="form-grid">
                <div class="form-group"><label>Nombre del grupo</label><input type="text" name="nombre_grupo_alerta" required></div>
                <div class="form-group">
                    <label>Estado</label>
                    <select name="estado_alerta">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="form-group"><label>Descripción</label><textarea name="descripcion_alerta"></textarea></div>
            <button class="btn btn-primary" type="submit">Guardar grupo alerta</button>
        </form>
    </div>

    <div class="section">
        <div class="section-header"><h2>Asignar usuario a grupo</h2></div>
        <form method="POST">
            <input type="hidden" name="tipo_form" value="asignar_usuario">
            <div class="form-grid">
                <div class="form-group">
                    <label>Grupo usuario</label>
                    <select name="id_grupo_usuario">
                        <?php foreach ($gruposUsuario as $g): ?><option value="<?php echo $g["id_grupo_usuario"]; ?>"><?php echo htmlspecialchars($g["nombre_grupo"]); ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Usuario</label>
                    <select name="id_usuario">
                        <?php foreach ($usuarios as $u): ?><option value="<?php echo $u["id_usuario"]; ?>"><?php echo htmlspecialchars($u["nombre"]); ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button class="btn btn-success" type="submit">Asignar usuario</button>
        </form>
    </div>

    <div class="section">
        <div class="section-header"><h2>Asignar alerta a grupo</h2></div>
        <form method="POST">
            <input type="hidden" name="tipo_form" value="asignar_alerta">
            <div class="form-grid">
                <div class="form-group">
                    <label>Grupo alerta</label>
                    <select name="id_grupo_alerta">
                        <?php foreach ($gruposAlerta as $g): ?><option value="<?php echo $g["id_grupo_alerta"]; ?>"><?php echo htmlspecialchars($g["nombre_grupo_alerta"]); ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Alerta</label>
                    <select name="id_alerta">
                        <?php foreach ($alertas as $a): ?><option value="<?php echo $a["id_alerta"]; ?>"><?php echo htmlspecialchars($a["nombre_alerta"]); ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button class="btn btn-success" type="submit">Asignar alerta</button>
        </form>
    </div>

    <div class="section">
        <div class="section-header"><h2>Detalle grupos de usuarios</h2></div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Grupo</th><th>Usuario</th></tr></thead>
                <tbody>
                    <?php foreach ($detalleUsuarios as $d): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($d["nombre_grupo"]); ?></td>
                        <td><?php echo htmlspecialchars($d["nombre"]); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-header"><h2>Detalle grupos de alertas</h2></div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Grupo alerta</th><th>Alerta</th></tr></thead>
                <tbody>
                    <?php foreach ($detalleAlertas as $d): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($d["nombre_grupo_alerta"]); ?></td>
                        <td><?php echo htmlspecialchars($d["nombre_alerta"]); ?></td>
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