<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../index.php");
    exit;
}

function currentRole() {
    return $_SESSION["rol"] ?? "";
}

function isAdmin() {
    return currentRole() === "Administrador";
}

function canAccess($module) {
    $role = currentRole();

    $permissions = [
        "Administrador" => [
            "dashboard",
            "empresas",
            "ciudades",
            "datacenters",
            "nodos",
            "equipos",
            "interfaces",
            "alertas",
            "reportes",
            "usuarios",
            "grupos"
        ],

        "Empresa" => [
            "dashboard",
            "datacenters",
            "nodos",
            "equipos",
            "interfaces",
            "alertas",
            "reportes"
        ],

        "Datacenter" => [
            "dashboard",
            "datacenters",
            "nodos",
            "equipos",
            "interfaces",
            "alertas",
            "reportes"
        ],

        "Nodo" => [
            "dashboard",
            "nodos",
            "equipos",
            "interfaces",
            "alertas",
            "reportes"
        ],

        "Equipo" => [
            "dashboard",
            "equipos",
            "interfaces",
            "alertas",
            "reportes"
        ],

        "Operador" => [
            "dashboard",
            "datacenters",
            "nodos",
            "equipos",
            "interfaces",
            "alertas",
            "reportes"
        ]
    ];

    return in_array($module, $permissions[$role] ?? []);
}

function requireModuleAccess($module) {
    if (!canAccess($module)) {
        header("Location: dashboard.php?denied=1");
        exit;
    }
}

function getScope() {
    return [
        "rol"           => $_SESSION["rol"] ?? "",
        "id_empresa"    => $_SESSION["id_empresa"] ?? null,
        "id_datacenter" => $_SESSION["id_datacenter"] ?? null,
        "id_nodo"       => $_SESSION["id_nodo"] ?? null,
        "id_equipo"     => $_SESSION["id_equipo"] ?? null
    ];
}
?>