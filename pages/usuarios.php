<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";

$roles = $pdo->query("SELECT id_rol, nombre_rol FROM rol ORDER BY nombre_rol")->fetchAll(PDO::FETCH_ASSOC);
$empresas = $pdo->query("SELECT id_empresa, nombre FROM empresa ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $pdo->prepare("INSERT INTO usuario
        (id_rol, id_empresa, nombre, correo, telefono, username, password, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST["id_rol"],
        $_POST["id_empresa"] !== "" ? $_POST["id_empresa"] : null,
        trim($_POST["nombre"]),
        trim($_POST["correo"]),
        trim($_POST["telefono"]),
        trim($_POST["username"]),
        trim($_POST["password"]),
        $_POST["estado"]
    ]);

    header("Location: usuarios.php?ok=1");
    exit;
}

$sql = "SELECT u.*, r.nombre_rol, e.nombre AS empresa
        FROM usuario u
        INNER JOIN rol r ON u.id_rol = r.id_rol
        LEFT JOIN empresa e ON u.id_empresa = e.id_empresa
        ORDER BY u.id_usuario DESC";
$usuarios = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios - SYSLOG</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="main-layout">
<?php include "../includes/menu.php"; ?>
<div class="content">
    <div class="topbar">
        <div><h1>Usuarios</h1><p class="muted-text">Administración de usuarios y roles</p></div>
        <div class="user-box"><?php echo $_SESSION["usuario"]; ?></div>
    </div>

    <?php if (isset($_GET["ok"])): ?><div class="alert success">Usuario guardado correctamente.</div><?php endif; ?>

    <div class="section">
        <div class="section-header"><h2>Nuevo usuario</h2></div>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Rol</label>
                    <select name="id_rol" required>
                        <?php foreach ($roles as $rol): ?><option value="<?php echo $rol["id_rol"]; ?>"><?php echo htmlspecialchars($rol["nombre_rol"]); ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Empresa</label>
                    <select name="id_empresa">
                        <option value="">Sin empresa</option>
                        <?php foreach ($empresas as $empresa): ?><option value="<?php echo $empresa["id_empresa"]; ?>"><?php echo htmlspecialchars($empresa["nombre"]); ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Nombre</label><input type="text" name="nombre" required></div>
                <div class="form-group"><label>Correo</label><input type="email" name="correo" required></div>
                <div class="form-group"><label>Teléfono</label><input type="text" name="telefono"></div>
                <div class="form-group"><label>Usuario</label><input type="text" name="username" required></div>
                <div class="form-group"><label>Contraseña</label><input type="text" name="password" required></div>
                <div class="form-group">
                    <label>Estado</label>
                    <select name="estado">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                        <option value="bloqueado">Bloqueado</option>
                    </select>
                </div>
            </div>
            <button class="btn btn-primary" type="submit">Guardar usuario</button>
        </form>
    </div>

    <div class="section">
        <div class="section-header"><h2>Listado de usuarios</h2></div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Empresa</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?php echo $u["id_usuario"]; ?></td>
                        <td><?php echo htmlspecialchars($u["nombre"]); ?></td>
                        <td><?php echo htmlspecialchars($u["username"]); ?></td>
                        <td><?php echo htmlspecialchars($u["correo"]); ?></td>
                        <td><?php echo htmlspecialchars($u["nombre_rol"]); ?></td>
                        <td><?php echo htmlspecialchars($u["empresa"] ?? ""); ?></td>
                        <td><span class="badge badge-info"><?php echo $u["estado"]; ?></span></td>
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