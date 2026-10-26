<?php
session_start();
require_once "../includes/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (empty($username) || empty($password)) {
        header("Location: ../index.php?error=1");
        exit;
    }

    $sql = "SELECT u.*, r.nombre_rol
            FROM usuario u
            INNER JOIN rol r ON u.id_rol = r.id_rol
            WHERE u.username = ?
              AND u.estado = 'activo'
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    $valido = false;

    if ($usuario) {
        if (password_verify($password, $usuario["password"])) {
            $valido = true;
        } elseif (password_get_info($usuario["password"])["algo"] === null && hash_equals($usuario["password"], $password)) {
            // Usuario antiguo con la contraseña en texto plano: se cifra al iniciar sesión
            $valido = true;
            $update = $pdo->prepare("UPDATE usuario SET password = ? WHERE id_usuario = ?");
            $update->execute([password_hash($password, PASSWORD_DEFAULT), $usuario["id_usuario"]]);
        }
    }

    if ($valido) {
        session_regenerate_id(true);

        $_SESSION["id_usuario"]     = $usuario["id_usuario"];
        $_SESSION["usuario"]        = $usuario["nombre"];
        $_SESSION["username"]       = $usuario["username"];
        $_SESSION["correo"]         = $usuario["correo"];
        $_SESSION["rol"]            = $usuario["nombre_rol"];
        $_SESSION["id_rol"]         = $usuario["id_rol"];
        $_SESSION["id_empresa"]     = $usuario["id_empresa"];
        $_SESSION["id_datacenter"]  = $usuario["id_datacenter"];
        $_SESSION["id_nodo"]        = $usuario["id_nodo"];
        $_SESSION["id_equipo"]      = $usuario["id_equipo"];

        header("Location: ../pages/dashboard.php");
        exit;
    } else {
        header("Location: ../index.php?error=1");
        exit;
    }
}

header("Location: ../index.php");
exit;
?>