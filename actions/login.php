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
              AND u.password = ?
              AND u.estado = 'activo'
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $password]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $_SESSION["id_usuario"] = $usuario["id_usuario"];
        $_SESSION["usuario"] = $usuario["nombre"];
        $_SESSION["username"] = $usuario["username"];
        $_SESSION["correo"] = $usuario["correo"];
        $_SESSION["rol"] = $usuario["nombre_rol"];
        $_SESSION["id_rol"] = $usuario["id_rol"];
        $_SESSION["id_empresa"] = $usuario["id_empresa"];

        header("Location: ../pages/dashboard.php");
        exit;
    } else {
        header("Location: ../index.php?error=1");
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}
?>