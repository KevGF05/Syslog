# Syslog - Monitoreo de Infraestructura

Panel administrativo web para monitorear infraestructura de red. La información se organiza de forma jerárquica: **empresas → ciudades → datacenters → nodos → equipos → interfaces**, y cada usuario solo ve lo que su rol le permite.

## Funcionalidades

- Dashboard con contadores de infraestructura y las últimas alertas.
- Gestión de empresas, ciudades, datacenters, nodos, equipos e interfaces de red.
- Alertas con niveles de severidad (crítica, alta, media), límites de monitoreo y notificaciones.
- Reportes.
- Usuarios y grupos con acceso por rol: Administrador, Empresa, Datacenter, Nodo, Equipo y Operador NOC.
- Contraseñas cifradas con `password_hash()` / `password_verify()`.

## Tecnologías

PHP 8 · MySQL / MariaDB (PDO) · HTML · CSS

## Instalación local (XAMPP)

1. Copia la carpeta del proyecto dentro de `htdocs`.
2. En phpMyAdmin crea una base de datos llamada `syslog_db` e importa `syslog_db.sql`.
3. Si tu MySQL usa otro usuario o contraseña, ajústalo en `includes/db.php`.
4. Abre `http://localhost/Syslog/` en el navegador.

## Usuario de prueba

| Usuario | Contraseña | Rol |
|---|---|---|
| `admin` | `123456` | Administrador |

El archivo SQL incluye más usuarios de ejemplo para cada rol.
