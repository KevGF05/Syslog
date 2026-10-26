<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";
requireModuleAccess("usuarios");

if (!isAdmin()) {
    header("Location: dashboard.php?denied=1");
    exit;
}

$roles = $pdo->query("SELECT id_rol, nombre_rol FROM rol ORDER BY nombre_rol")->fetchAll(PDO::FETCH_ASSOC);
$empresas = $pdo->query("SELECT id_empresa, nombre FROM empresa ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

$datacenters = $pdo->query("
    SELECT id_datacenter, id_empresa, nombre
    FROM datacenter
    ORDER BY nombre
")->fetchAll(PDO::FETCH_ASSOC);

$nodos = $pdo->query("
    SELECT n.id_nodo, n.id_datacenter, d.id_empresa, n.nombre
    FROM nodo n
    INNER JOIN datacenter d ON n.id_datacenter = d.id_datacenter
    ORDER BY n.nombre
")->fetchAll(PDO::FETCH_ASSOC);

$equipos = $pdo->query("
    SELECT e.id_equipo, e.id_nodo, n.id_datacenter, d.id_empresa, e.nombre
    FROM equipo e
    INNER JOIN nodo n ON e.id_nodo = n.id_nodo
    INNER JOIN datacenter d ON n.id_datacenter = d.id_datacenter
    ORDER BY e.nombre
")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_rol = $_POST["id_rol"];
    $id_empresa = $_POST["id_empresa"] !== "" ? $_POST["id_empresa"] : null;
    $id_datacenter = $_POST["id_datacenter"] !== "" ? $_POST["id_datacenter"] : null;
    $id_nodo = $_POST["id_nodo"] !== "" ? $_POST["id_nodo"] : null;
    $id_equipo = $_POST["id_equipo"] !== "" ? $_POST["id_equipo"] : null;

    $nombre = trim($_POST["nombre"]);
    $correo = trim($_POST["correo"]);
    $telefono = trim($_POST["telefono"]);
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $estado = $_POST["estado"];

    $stmt = $pdo->prepare("
        INSERT INTO usuario
        (id_rol, id_empresa, id_datacenter, id_nodo, id_equipo, nombre, correo, telefono, username, password, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $id_rol,
        $id_empresa,
        $id_datacenter,
        $id_nodo,
        $id_equipo,
        $nombre,
        $correo,
        $telefono,
        $username,
        password_hash($password, PASSWORD_DEFAULT),
        $estado
    ]);

    header("Location: usuarios.php?ok=1");
    exit;
}

$usuarios = $pdo->query("
    SELECT 
        u.*,
        r.nombre_rol,
        e.nombre AS empresa_nombre,
        d.nombre AS datacenter,
        n.nombre AS nodo,
        eq.nombre AS equipo
    FROM usuario u
    INNER JOIN rol r ON u.id_rol = r.id_rol
    LEFT JOIN empresa e ON u.id_empresa = e.id_empresa
    LEFT JOIN datacenter d ON u.id_datacenter = d.id_datacenter
    LEFT JOIN nodo n ON u.id_nodo = n.id_nodo
    LEFT JOIN equipo eq ON u.id_equipo = eq.id_equipo
    ORDER BY u.id_usuario DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - SYSLOG</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="main-layout">
    <?php include "../includes/menu.php"; ?>

    <div class="content">
        <div class="topbar">
            <div>
                <h1>Usuarios</h1>
                <p class="muted-text">Administración de usuarios por jerarquía</p>
            </div>
            <div class="user-box">
                <?php echo htmlspecialchars($_SESSION["usuario"]); ?> | Administrador
            </div>
        </div>

        <?php if (isset($_GET["ok"])): ?>
            <div class="alert success">Usuario guardado correctamente.</div>
        <?php endif; ?>

        <div class="section">
            <div class="section-header">
                <h2>Nuevo usuario</h2>
            </div>

            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Rol</label>
                        <select name="id_rol" id="id_rol" required>
                            <option value="">Seleccione un rol</option>
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?php echo $rol["id_rol"]; ?>">
                                    <?php echo htmlspecialchars($rol["nombre_rol"]); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Empresa</label>
                        <select name="id_empresa" id="id_empresa">
                            <option value="">Seleccione empresa</option>
                            <?php foreach ($empresas as $empresa): ?>
                                <option value="<?php echo $empresa["id_empresa"]; ?>">
                                    <?php echo htmlspecialchars($empresa["nombre"]); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Datacenter</label>
                        <select name="id_datacenter" id="id_datacenter">
                            <option value="">Seleccione datacenter</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Nodo</label>
                        <select name="id_nodo" id="id_nodo">
                            <option value="">Seleccione nodo</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Equipo</label>
                        <select name="id_equipo" id="id_equipo">
                            <option value="">Seleccione equipo</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Nombre completo</label>
                        <input type="text" name="nombre" required>
                    </div>

                    <div class="form-group">
                        <label>Correo</label>
                        <input type="email" name="correo" required>
                    </div>

                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono">
                    </div>

                    <div class="form-group">
                        <label>Usuario</label>
                        <input type="text" name="username" required>
                    </div>

                    <div class="form-group">
                        <label>Contraseña</label>
                        <input type="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="bloqueado">Bloqueado</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Guardar usuario</button>
                </div>
            </form>
        </div>

        <div class="section">
            <div class="section-header">
                <h2>Listado de usuarios</h2>
            </div>

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
                            <th>Datacenter</th>
                            <th>Nodo</th>
                            <th>Equipo</th>
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
                                <td><?php echo htmlspecialchars($u["empresa_nombre"] ?? ""); ?></td>
                                <td><?php echo htmlspecialchars($u["datacenter"] ?? ""); ?></td>
                                <td><?php echo htmlspecialchars($u["nodo"] ?? ""); ?></td>
                                <td><?php echo htmlspecialchars($u["equipo"] ?? ""); ?></td>
                                <td><?php echo htmlspecialchars($u["estado"]); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$usuarios): ?>
                            <tr>
                                <td colspan="10">No hay usuarios registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const datacenters = <?php echo json_encode($datacenters, JSON_UNESCAPED_UNICODE); ?>;
const nodos = <?php echo json_encode($nodos, JSON_UNESCAPED_UNICODE); ?>;
const equipos = <?php echo json_encode($equipos, JSON_UNESCAPED_UNICODE); ?>;

const empresaSelect = document.getElementById("id_empresa");
const datacenterSelect = document.getElementById("id_datacenter");
const nodoSelect = document.getElementById("id_nodo");
const equipoSelect = document.getElementById("id_equipo");
const rolSelect = document.getElementById("id_rol");

function resetSelect(select, placeholder) {
    select.innerHTML = `<option value="">${placeholder}</option>`;
}

function fillDatacenters() {
    const empresaId = empresaSelect.value;
    resetSelect(datacenterSelect, "Seleccione datacenter");
    resetSelect(nodoSelect, "Seleccione nodo");
    resetSelect(equipoSelect, "Seleccione equipo");

    datacenters
        .filter(dc => String(dc.id_empresa) === String(empresaId))
        .forEach(dc => {
            datacenterSelect.innerHTML += `<option value="${dc.id_datacenter}">${dc.nombre}</option>`;
        });
}

function fillNodos() {
    const datacenterId = datacenterSelect.value;
    resetSelect(nodoSelect, "Seleccione nodo");
    resetSelect(equipoSelect, "Seleccione equipo");

    nodos
        .filter(n => String(n.id_datacenter) === String(datacenterId))
        .forEach(n => {
            nodoSelect.innerHTML += `<option value="${n.id_nodo}">${n.nombre}</option>`;
        });
}

function fillEquipos() {
    const nodoId = nodoSelect.value;
    resetSelect(equipoSelect, "Seleccione equipo");

    equipos
        .filter(eq => String(eq.id_nodo) === String(nodoId))
        .forEach(eq => {
            equipoSelect.innerHTML += `<option value="${eq.id_equipo}">${eq.nombre}</option>`;
        });
}

function ajustarCamposPorRol() {
    const rolTexto = rolSelect.options[rolSelect.selectedIndex]?.text || "";

    empresaSelect.disabled = false;
    datacenterSelect.disabled = false;
    nodoSelect.disabled = false;
    equipoSelect.disabled = false;

    if (rolTexto === "Administrador") {
        empresaSelect.value = "";
        resetSelect(datacenterSelect, "No aplica");
        resetSelect(nodoSelect, "No aplica");
        resetSelect(equipoSelect, "No aplica");
        empresaSelect.disabled = true;
        datacenterSelect.disabled = true;
        nodoSelect.disabled = true;
        equipoSelect.disabled = true;
    } else if (rolTexto === "Empresa" || rolTexto === "Operador") {
        resetSelect(datacenterSelect, "Seleccione datacenter");
        resetSelect(nodoSelect, "Seleccione nodo");
        resetSelect(equipoSelect, "Seleccione equipo");
        datacenterSelect.disabled = true;
        nodoSelect.disabled = true;
        equipoSelect.disabled = true;
    } else if (rolTexto === "Datacenter") {
        datacenterSelect.disabled = false;
        nodoSelect.disabled = true;
        equipoSelect.disabled = true;
    } else if (rolTexto === "Nodo") {
        datacenterSelect.disabled = false;
        nodoSelect.disabled = false;
        equipoSelect.disabled = true;
    } else if (rolTexto === "Equipo") {
        datacenterSelect.disabled = false;
        nodoSelect.disabled = false;
        equipoSelect.disabled = false;
    }
}

empresaSelect.addEventListener("change", () => {
    fillDatacenters();
    ajustarCamposPorRol();
});

datacenterSelect.addEventListener("change", () => {
    fillNodos();
    ajustarCamposPorRol();
});

nodoSelect.addEventListener("change", () => {
    fillEquipos();
    ajustarCamposPorRol();
});

rolSelect.addEventListener("change", ajustarCamposPorRol);

ajustarCamposPorRol();
</script>

</body>
</html>