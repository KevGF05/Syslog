-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-03-2026 a las 05:42:33
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `syslog_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alerta`
--

CREATE TABLE `alerta` (
  `id_alerta` int(11) NOT NULL,
  `nombre_alerta` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `severidad` enum('baja','media','alta','critica') DEFAULT 'media',
  `tipo_objeto` enum('datacenter','nodo','equipo','interface') NOT NULL,
  `metrica` varchar(50) NOT NULL,
  `condicion` enum('>','<','=','>=','<=','fuera_rango') NOT NULL,
  `valor_referencia` decimal(10,2) DEFAULT NULL,
  `estado` enum('activa','inactiva') DEFAULT 'activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `alerta`
--

INSERT INTO `alerta` (`id_alerta`, `nombre_alerta`, `descripcion`, `severidad`, `tipo_objeto`, `metrica`, `condicion`, `valor_referencia`, `estado`) VALUES
(1, 'Interfaz caída', 'La interfaz se encuentra en estado down', 'alta', 'interface', 'estado', '=', NULL, 'activa'),
(2, 'Alto tráfico en interfaz', 'La interfaz superó el límite de tráfico esperado', 'media', 'interface', 'trafico', '>', 90.00, 'activa'),
(3, 'Firewall en warning', 'El firewall se encuentra en estado de advertencia', 'critica', 'equipo', 'estado', '=', NULL, 'activa'),
(4, 'CPU elevada en equipo', 'La CPU del equipo superó el umbral máximo', 'alta', 'equipo', 'cpu', '>', 85.00, 'activa'),
(5, 'Alta latencia en nodo', 'El nodo presenta latencia por encima del rango esperado', 'media', 'nodo', 'latencia', '>', 40.00, 'activa'),
(6, 'Temperatura fuera de rango', 'El datacenter presenta temperatura fuera de rango', 'critica', 'datacenter', 'temperatura', 'fuera_rango', 27.00, 'activa');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ciudad`
--

CREATE TABLE `ciudad` (
  `id_ciudad` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT 'Honduras',
  `estado` enum('activo','inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ciudad`
--

INSERT INTO `ciudad` (`id_ciudad`, `nombre`, `departamento`, `pais`, `estado`) VALUES
(1, 'San Pedro Sula', 'Cortés', 'Honduras', 'activo'),
(2, 'Tegucigalpa', 'Francisco Morazán', 'Honduras', 'activo'),
(3, 'La Ceiba', 'Atlántida', 'Honduras', 'activo'),
(4, 'Choloma', 'Cortés', 'Honduras', 'activo'),
(5, 'El Progreso', 'Yoro', 'Honduras', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `datacenter`
--

CREATE TABLE `datacenter` (
  `id_datacenter` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `id_ciudad` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `nivel_seguridad` enum('bajo','medio','alto') DEFAULT 'medio',
  `estado` enum('activo','inactivo','mantenimiento') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `datacenter`
--

INSERT INTO `datacenter` (`id_datacenter`, `id_empresa`, `id_ciudad`, `nombre`, `direccion`, `nivel_seguridad`, `estado`) VALUES
(1, 1, 1, 'DC Norte', 'Boulevard del Norte, SPS', 'alto', 'activo'),
(2, 1, 2, 'DC Capital', 'Colonia Palmira, Tegucigalpa', 'alto', 'activo'),
(3, 2, 3, 'DC Caribe', 'Zona Viva, La Ceiba', 'medio', 'activo'),
(4, 3, 5, 'DC Progreso', 'Centro Logístico, El Progreso', 'medio', 'mantenimiento');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_grupo_alerta`
--

CREATE TABLE `detalle_grupo_alerta` (
  `id_detalle_grupo_alerta` int(11) NOT NULL,
  `id_grupo_alerta` int(11) NOT NULL,
  `id_alerta` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_grupo_alerta`
--

INSERT INTO `detalle_grupo_alerta` (`id_detalle_grupo_alerta`, `id_grupo_alerta`, `id_alerta`) VALUES
(1, 1, 3),
(2, 1, 6),
(3, 2, 1),
(4, 2, 2),
(5, 3, 5),
(6, 3, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_grupo_usuario`
--

CREATE TABLE `detalle_grupo_usuario` (
  `id_detalle_grupo_usuario` int(11) NOT NULL,
  `id_grupo_usuario` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_grupo_usuario`
--

INSERT INTO `detalle_grupo_usuario` (`id_detalle_grupo_usuario`, `id_grupo_usuario`, `id_usuario`) VALUES
(1, 1, 10),
(2, 1, 11),
(3, 1, 12),
(4, 2, 1),
(5, 2, 4),
(6, 2, 6),
(7, 3, 5),
(8, 3, 9);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `id_empresa` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`id_empresa`, `nombre`, `descripcion`, `estado`) VALUES
(1, 'NetOne Telecom', 'Empresa de telecomunicaciones y monitoreo de red', 'activo'),
(2, 'InfraCloud Honduras', 'Servicios de infraestructura y datacenters', 'activo'),
(3, 'Conectividad Regional', 'Operación de enlaces y nodos regionales', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipo`
--

CREATE TABLE `equipo` (
  `id_equipo` int(11) NOT NULL,
  `id_nodo` int(11) NOT NULL,
  `id_tipo_equipo` int(11) NOT NULL,
  `id_marca` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `ip_gestion` varchar(45) DEFAULT NULL,
  `sistema_operativo` varchar(100) DEFAULT NULL,
  `version_firmware` varchar(100) DEFAULT NULL,
  `estado` enum('activo','inactivo','mantenimiento','warning') DEFAULT 'activo',
  `fecha_instalacion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `equipo`
--

INSERT INTO `equipo` (`id_equipo`, `id_nodo`, `id_tipo_equipo`, `id_marca`, `nombre`, `modelo`, `serial_number`, `ip_gestion`, `sistema_operativo`, `version_firmware`, `estado`, `fecha_instalacion`) VALUES
(1, 1, 1, 1, 'Router Core SPS', 'ASR1001-X', 'CISCO-001', '10.10.1.1', 'IOS-XE', '17.06.04', 'activo', '2024-01-15'),
(2, 1, 2, 1, 'Switch Distribucion SPS', 'Catalyst 9300', 'CISCO-002', '10.10.1.2', 'IOS-XE', '17.03.05', 'activo', '2024-01-16'),
(3, 2, 3, 6, 'Firewall Perimetral SPS', 'FortiGate 100F', 'FORTI-001', '10.10.2.1', 'FortiOS', '7.2.1', 'warning', '2024-02-10'),
(4, 3, 1, 2, 'Router Core TGU', 'NE40E', 'HUA-001', '10.20.1.1', 'VRP', '8.200', 'activo', '2024-03-01'),
(5, 3, 4, 3, 'Servidor Monitoreo TGU', 'PowerEdge R540', 'DELL-001', '10.20.1.10', 'Ubuntu Server', '22.04', 'activo', '2024-03-02'),
(6, 4, 2, 5, 'Switch Backbone LCB', 'CRS326', 'MIK-001', '10.30.1.2', 'RouterOS', '7.9', 'activo', '2024-04-05'),
(7, 4, 5, 4, 'AP Lobby LCB', 'Aruba AP22', 'HP-001', '10.30.2.10', 'ArubaOS', '10.3', 'activo', '2024-04-07'),
(8, 5, 6, 4, 'UPS Progreso', 'SmartUPS 3000', 'HP-UPS-001', '10.40.1.50', 'Embedded', '1.0.5', 'mantenimiento', '2024-05-12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evento_alerta`
--

CREATE TABLE `evento_alerta` (
  `id_evento` int(11) NOT NULL,
  `id_alerta` int(11) NOT NULL,
  `tipo_objeto` enum('datacenter','nodo','equipo','interface') NOT NULL,
  `id_objeto` int(11) NOT NULL,
  `valor_detectado` decimal(10,2) DEFAULT NULL,
  `fecha_evento` datetime DEFAULT current_timestamp(),
  `estado_evento` enum('activa','atendida','cerrada') DEFAULT 'activa',
  `mensaje` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `evento_alerta`
--

INSERT INTO `evento_alerta` (`id_evento`, `id_alerta`, `tipo_objeto`, `id_objeto`, `valor_detectado`, `fecha_evento`, `estado_evento`, `mensaje`) VALUES
(1, 1, 'interface', 2, 0.00, '2026-03-06 08:10:00', 'activa', 'La interfaz Gig0/1 del Router Core SPS está caída'),
(2, 2, 'interface', 4, 95.30, '2026-03-06 08:20:00', 'activa', 'La interfaz Gig1/0/2 del Switch Distribucion SPS superó el umbral de tráfico'),
(3, 3, 'equipo', 3, 1.00, '2026-03-06 08:30:00', 'activa', 'El Firewall Perimetral SPS se encuentra en estado warning'),
(4, 4, 'equipo', 5, 91.00, '2026-03-06 08:45:00', 'atendida', 'El Servidor Monitoreo TGU presentó CPU elevada'),
(5, 5, 'nodo', 1, 55.00, '2026-03-06 09:00:00', 'cerrada', 'El Nodo SPS-Core-01 presentó latencia superior a la permitida'),
(6, 6, 'datacenter', 4, 29.00, '2026-03-06 09:10:00', 'activa', 'El DC Progreso reporta temperatura fuera del rango establecido');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo_alerta`
--

CREATE TABLE `grupo_alerta` (
  `id_grupo_alerta` int(11) NOT NULL,
  `nombre_grupo_alerta` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `grupo_alerta`
--

INSERT INTO `grupo_alerta` (`id_grupo_alerta`, `nombre_grupo_alerta`, `descripcion`, `estado`) VALUES
(1, 'Alertas Críticas', 'Alertas de impacto alto o severidad crítica', 'activo'),
(2, 'Alertas de Interfaces', 'Alertas relacionadas con estado o tráfico de interfaces', 'activo'),
(3, 'Alertas de Infraestructura', 'Alertas relacionadas con nodos y datacenters', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo_usuario`
--

CREATE TABLE `grupo_usuario` (
  `id_grupo_usuario` int(11) NOT NULL,
  `nombre_grupo` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `grupo_usuario`
--

INSERT INTO `grupo_usuario` (`id_grupo_usuario`, `nombre_grupo`, `descripcion`, `estado`) VALUES
(1, 'NOC Central', 'Grupo principal de monitoreo 24/7', 'activo'),
(2, 'Admins de Red', 'Grupo técnico de administración de red', 'activo'),
(3, 'Soporte Datacenter', 'Grupo de soporte para datacenters', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `interface_red`
--

CREATE TABLE `interface_red` (
  `id_interface` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `nombre_interface` varchar(100) NOT NULL,
  `tipo_interface` varchar(50) DEFAULT NULL,
  `velocidad` varchar(50) DEFAULT NULL,
  `mac_address` varchar(50) DEFAULT NULL,
  `direccion_ip` varchar(45) DEFAULT NULL,
  `estado` enum('up','down','warning','disabled') DEFAULT 'up',
  `trafico_actual` decimal(10,2) DEFAULT 0.00,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `interface_red`
--

INSERT INTO `interface_red` (`id_interface`, `id_equipo`, `nombre_interface`, `tipo_interface`, `velocidad`, `mac_address`, `direccion_ip`, `estado`, `trafico_actual`, `descripcion`) VALUES
(1, 1, 'Gig0/0', 'Ethernet', '1Gbps', '00:11:22:33:44:01', '172.16.1.1', 'up', 125.50, 'Uplink principal'),
(2, 1, 'Gig0/1', 'Ethernet', '1Gbps', '00:11:22:33:44:02', '172.16.1.2', 'down', 0.00, 'Enlace de respaldo'),
(3, 2, 'Gig1/0/1', 'Ethernet', '1Gbps', '00:11:22:33:44:03', '172.16.2.1', 'up', 78.90, 'Distribución a usuarios'),
(4, 2, 'Gig1/0/2', 'Ethernet', '1Gbps', '00:11:22:33:44:04', '172.16.2.2', 'warning', 95.30, 'Puerto con alto tráfico'),
(5, 3, 'port1', 'Ethernet', '1Gbps', '00:11:22:33:44:05', '172.16.3.1', 'warning', 88.40, 'Interfaz WAN firewall'),
(6, 4, 'GE0/0/0', 'Ethernet', '10Gbps', '00:11:22:33:44:06', '172.16.4.1', 'up', 240.75, 'Backbone principal'),
(7, 5, 'eth0', 'Ethernet', '1Gbps', '00:11:22:33:44:07', '172.16.5.10', 'up', 60.20, 'Interfaz de monitoreo'),
(8, 6, 'ether1', 'Ethernet', '1Gbps', '00:11:22:33:44:08', '172.16.6.1', 'up', 130.00, 'Enlace backbone'),
(9, 7, 'wlan0', 'Wireless', '300Mbps', '00:11:22:33:44:09', '172.16.7.1', 'up', 40.00, 'Red inalámbrica lobby'),
(10, 8, 'power0', 'Power', 'N/A', '00:11:22:33:44:10', NULL, 'disabled', 0.00, 'Módulo energético');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `limite_monitoreo`
--

CREATE TABLE `limite_monitoreo` (
  `id_limite` int(11) NOT NULL,
  `tipo_objeto` enum('datacenter','nodo','equipo','interface') NOT NULL,
  `id_objeto` int(11) NOT NULL,
  `metrica` varchar(50) NOT NULL,
  `valor_min` decimal(10,2) DEFAULT NULL,
  `valor_max` decimal(10,2) DEFAULT NULL,
  `unidad_medida` varchar(20) DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `limite_monitoreo`
--

INSERT INTO `limite_monitoreo` (`id_limite`, `tipo_objeto`, `id_objeto`, `metrica`, `valor_min`, `valor_max`, `unidad_medida`, `estado`, `fecha_creacion`) VALUES
(1, 'interface', 1, 'trafico', 0.00, 150.00, 'Mbps', 'activo', '2026-03-06 20:39:34'),
(2, 'interface', 2, 'trafico', 0.00, 100.00, 'Mbps', 'activo', '2026-03-06 20:39:34'),
(3, 'interface', 4, 'trafico', 0.00, 90.00, 'Mbps', 'activo', '2026-03-06 20:39:34'),
(4, 'equipo', 3, 'cpu', 0.00, 85.00, '%', 'activo', '2026-03-06 20:39:34'),
(5, 'equipo', 5, 'memoria', 20.00, 90.00, '%', 'activo', '2026-03-06 20:39:34'),
(6, 'nodo', 1, 'latencia', 0.00, 40.00, 'ms', 'activo', '2026-03-06 20:39:34'),
(7, 'datacenter', 4, 'temperatura', 18.00, 27.00, 'C', 'activo', '2026-03-06 20:39:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marca`
--

CREATE TABLE `marca` (
  `id_marca` int(11) NOT NULL,
  `nombre_marca` varchar(100) NOT NULL,
  `descripcion` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `marca`
--

INSERT INTO `marca` (`id_marca`, `nombre_marca`, `descripcion`) VALUES
(1, 'Cisco', 'Fabricante de equipos de red'),
(2, 'Huawei', 'Fabricante de telecomunicaciones'),
(3, 'Dell', 'Fabricante de servidores'),
(4, 'HP', 'Fabricante de infraestructura'),
(5, 'Mikrotik', 'Fabricante de networking'),
(6, 'Fortinet', 'Fabricante de seguridad');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nodo`
--

CREATE TABLE `nodo` (
  `id_nodo` int(11) NOT NULL,
  `id_datacenter` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `ubicacion` varchar(150) DEFAULT NULL,
  `ip_principal` varchar(45) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` enum('activo','inactivo','mantenimiento') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `nodo`
--

INSERT INTO `nodo` (`id_nodo`, `id_datacenter`, `nombre`, `ubicacion`, `ip_principal`, `descripcion`, `estado`) VALUES
(1, 1, 'Nodo SPS-Core-01', 'Rack A1', '192.168.10.1', 'Nodo principal de distribución', 'activo'),
(2, 1, 'Nodo SPS-Edge-02', 'Rack A2', '192.168.10.2', 'Nodo perimetral de acceso', 'activo'),
(3, 2, 'Nodo TGU-Core-01', 'Rack B1', '192.168.20.1', 'Nodo principal de la capital', 'activo'),
(4, 3, 'Nodo LCB-Backbone-01', 'Rack C1', '192.168.30.1', 'Nodo backbone del litoral', 'activo'),
(5, 4, 'Nodo PRO-Distribucion-01', 'Rack D1', '192.168.40.1', 'Nodo de distribución regional', 'mantenimiento');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion_alerta`
--

CREATE TABLE `notificacion_alerta` (
  `id_notificacion` int(11) NOT NULL,
  `id_evento` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_grupo_usuario` int(11) DEFAULT NULL,
  `medio_envio` enum('sistema','correo','sms') DEFAULT 'sistema',
  `fecha_envio` datetime DEFAULT current_timestamp(),
  `estado_envio` enum('pendiente','enviado','fallido') DEFAULT 'pendiente',
  `observacion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificacion_alerta`
--

INSERT INTO `notificacion_alerta` (`id_notificacion`, `id_evento`, `id_usuario`, `id_grupo_usuario`, `medio_envio`, `fecha_envio`, `estado_envio`, `observacion`) VALUES
(1, 1, NULL, 1, 'sistema', '2026-03-06 08:11:00', 'enviado', 'Notificación enviada al grupo NOC Central'),
(2, 1, 10, NULL, 'correo', '2026-03-06 08:12:00', 'enviado', 'Correo enviado a Operador NOC 1'),
(3, 2, NULL, 1, 'sistema', '2026-03-06 08:21:00', 'enviado', 'Alerta de tráfico enviada al NOC'),
(4, 3, NULL, 2, 'correo', '2026-03-06 08:31:00', 'enviado', 'Alerta crítica enviada a administradores de red'),
(5, 4, 1, NULL, 'correo', '2026-03-06 08:46:00', 'enviado', 'Administrador informado de CPU elevada'),
(6, 6, NULL, 3, 'sistema', '2026-03-06 09:11:00', 'pendiente', 'Pendiente de revisión por soporte de datacenter');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL,
  `descripcion` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `nombre_rol`, `descripcion`) VALUES
(1, 'Administrador', 'Acceso total al sistema'),
(2, 'Empresa', 'Acceso a datos de su empresa'),
(3, 'Datacenter', 'Acceso a su datacenter'),
(4, 'Nodo', 'Acceso a su nodo'),
(5, 'Equipo', 'Acceso a su equipo'),
(6, 'Operador', 'Monitoreo y consulta de alertas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_equipo`
--

CREATE TABLE `tipo_equipo` (
  `id_tipo_equipo` int(11) NOT NULL,
  `nombre_tipo` varchar(100) NOT NULL,
  `descripcion` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_equipo`
--

INSERT INTO `tipo_equipo` (`id_tipo_equipo`, `nombre_tipo`, `descripcion`) VALUES
(1, 'Router', 'Equipo de enrutamiento'),
(2, 'Switch', 'Equipo de conmutación'),
(3, 'Firewall', 'Equipo de seguridad perimetral'),
(4, 'Servidor', 'Servidor de aplicaciones o monitoreo'),
(5, 'Access Point', 'Punto de acceso inalámbrico'),
(6, 'UPS', 'Sistema de respaldo de energía');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `id_empresa` int(11) DEFAULT NULL,
  `nombre_empresa` varchar(100) DEFAULT NULL,
  `id_datacenter` int(11) DEFAULT NULL,
  `id_nodo` int(11) DEFAULT NULL,
  `id_equipo` int(11) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `estado` enum('activo','inactivo','bloqueado') DEFAULT 'activo',
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `id_rol`, `id_empresa`, `nombre_empresa`, `id_datacenter`, `id_nodo`, `id_equipo`, `nombre`, `correo`, `telefono`, `username`, `password`, `estado`, `fecha_creacion`) VALUES
(1, 1, NULL, NULL, NULL, NULL, NULL, 'Administrador General', 'admin@syslog.com', '9999-0001', 'admin', '123456', 'activo', '2026-03-06 20:39:34'),
(2, 2, 1, 'NetOne Telecom', NULL, NULL, NULL, 'Laura Mendoza', 'laura.mendoza@netone.com', '9999-0002', 'empresa1', '111111', 'activo', '2026-03-06 20:39:34'),
(3, 2, 2, 'InfraCloud Honduras', NULL, NULL, NULL, 'Carlos Figueroa', 'carlos.figueroa@infracloud.com', '9999-0003', 'empresa2', '222222', 'activo', '2026-03-06 20:39:34'),
(4, 3, 1, 'NetOne Telecom', 1, NULL, NULL, 'Andrea Pineda', 'andrea.pineda@netone.com', '9999-0004', 'dcuser1', '333333', 'activo', '2026-03-06 20:39:34'),
(5, 3, 2, 'InfraCloud Honduras', 3, NULL, NULL, 'Jorge Rivera', 'jorge.rivera@infracloud.com', '9999-0005', 'dcuser2', '444444', 'activo', '2026-03-06 20:39:34'),
(6, 4, 1, 'NetOne Telecom', 1, 1, NULL, 'Marvin Salgado', 'marvin.salgado@netone.com', '9999-0006', 'nodouser1', '555555', 'activo', '2026-03-06 20:39:34'),
(7, 4, 3, 'Conectividad Regional', 4, 5, NULL, 'Daniela Flores', 'daniela.flores@regional.com', '9999-0007', 'nodouser2', '666666', 'activo', '2026-03-06 20:39:34'),
(8, 5, 1, 'NetOne Telecom', 1, 1, 1, 'Kevin Torres', 'kevin.torres@netone.com', '9999-0008', 'equipouser1', '777777', 'activo', '2026-03-06 20:39:34'),
(9, 5, 2, 'InfraCloud Honduras', 3, 4, 6, 'Sofía García', 'sofia.garcia@infracloud.com', '9999-0009', 'equipouser2', '888888', 'activo', '2026-03-06 20:39:34'),
(10, 6, 1, 'NetOne Telecom', NULL, NULL, NULL, 'Operador NOC 1', 'operador1@syslog.com', '9999-0010', 'operador1', '999999', 'activo', '2026-03-06 20:39:34'),
(11, 6, 1, 'NetOne Telecom', NULL, NULL, NULL, 'Operador NOC 2', 'operador2@syslog.com', '9999-0011', 'operador2', '101010', 'activo', '2026-03-06 20:39:34'),
(12, 6, 3, 'Conectividad Regional', NULL, NULL, NULL, 'Monitoreo Regional', 'regional@syslog.com', '9999-0012', 'regional1', '121212', 'activo', '2026-03-06 20:39:34');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alerta`
--
ALTER TABLE `alerta`
  ADD PRIMARY KEY (`id_alerta`);

--
-- Indices de la tabla `ciudad`
--
ALTER TABLE `ciudad`
  ADD PRIMARY KEY (`id_ciudad`);

--
-- Indices de la tabla `datacenter`
--
ALTER TABLE `datacenter`
  ADD PRIMARY KEY (`id_datacenter`),
  ADD KEY `fk_datacenter_empresa` (`id_empresa`),
  ADD KEY `fk_datacenter_ciudad` (`id_ciudad`);

--
-- Indices de la tabla `detalle_grupo_alerta`
--
ALTER TABLE `detalle_grupo_alerta`
  ADD PRIMARY KEY (`id_detalle_grupo_alerta`),
  ADD UNIQUE KEY `uk_grupo_alerta` (`id_grupo_alerta`,`id_alerta`),
  ADD KEY `fk_dga_alerta` (`id_alerta`);

--
-- Indices de la tabla `detalle_grupo_usuario`
--
ALTER TABLE `detalle_grupo_usuario`
  ADD PRIMARY KEY (`id_detalle_grupo_usuario`),
  ADD UNIQUE KEY `uk_grupo_usuario` (`id_grupo_usuario`,`id_usuario`),
  ADD KEY `fk_dgu_usuario` (`id_usuario`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id_empresa`);

--
-- Indices de la tabla `equipo`
--
ALTER TABLE `equipo`
  ADD PRIMARY KEY (`id_equipo`),
  ADD KEY `fk_equipo_nodo` (`id_nodo`),
  ADD KEY `fk_equipo_tipo` (`id_tipo_equipo`),
  ADD KEY `fk_equipo_marca` (`id_marca`);

--
-- Indices de la tabla `evento_alerta`
--
ALTER TABLE `evento_alerta`
  ADD PRIMARY KEY (`id_evento`),
  ADD KEY `fk_evento_alerta` (`id_alerta`);

--
-- Indices de la tabla `grupo_alerta`
--
ALTER TABLE `grupo_alerta`
  ADD PRIMARY KEY (`id_grupo_alerta`),
  ADD UNIQUE KEY `nombre_grupo_alerta` (`nombre_grupo_alerta`);

--
-- Indices de la tabla `grupo_usuario`
--
ALTER TABLE `grupo_usuario`
  ADD PRIMARY KEY (`id_grupo_usuario`),
  ADD UNIQUE KEY `nombre_grupo` (`nombre_grupo`);

--
-- Indices de la tabla `interface_red`
--
ALTER TABLE `interface_red`
  ADD PRIMARY KEY (`id_interface`),
  ADD KEY `fk_interface_equipo` (`id_equipo`);

--
-- Indices de la tabla `limite_monitoreo`
--
ALTER TABLE `limite_monitoreo`
  ADD PRIMARY KEY (`id_limite`);

--
-- Indices de la tabla `marca`
--
ALTER TABLE `marca`
  ADD PRIMARY KEY (`id_marca`),
  ADD UNIQUE KEY `nombre_marca` (`nombre_marca`);

--
-- Indices de la tabla `nodo`
--
ALTER TABLE `nodo`
  ADD PRIMARY KEY (`id_nodo`),
  ADD KEY `fk_nodo_datacenter` (`id_datacenter`);

--
-- Indices de la tabla `notificacion_alerta`
--
ALTER TABLE `notificacion_alerta`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `fk_notificacion_evento` (`id_evento`),
  ADD KEY `fk_notificacion_usuario` (`id_usuario`),
  ADD KEY `fk_notificacion_grupo_usuario` (`id_grupo_usuario`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre_rol` (`nombre_rol`);

--
-- Indices de la tabla `tipo_equipo`
--
ALTER TABLE `tipo_equipo`
  ADD PRIMARY KEY (`id_tipo_equipo`),
  ADD UNIQUE KEY `nombre_tipo` (`nombre_tipo`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_usuario_rol` (`id_rol`),
  ADD KEY `fk_usuario_empresa` (`id_empresa`),
  ADD KEY `fk_usuario_datacenter` (`id_datacenter`),
  ADD KEY `fk_usuario_nodo` (`id_nodo`),
  ADD KEY `fk_usuario_equipo` (`id_equipo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alerta`
--
ALTER TABLE `alerta`
  MODIFY `id_alerta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `ciudad`
--
ALTER TABLE `ciudad`
  MODIFY `id_ciudad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `datacenter`
--
ALTER TABLE `datacenter`
  MODIFY `id_datacenter` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `detalle_grupo_alerta`
--
ALTER TABLE `detalle_grupo_alerta`
  MODIFY `id_detalle_grupo_alerta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `detalle_grupo_usuario`
--
ALTER TABLE `detalle_grupo_usuario`
  MODIFY `id_detalle_grupo_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `equipo`
--
ALTER TABLE `equipo`
  MODIFY `id_equipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `evento_alerta`
--
ALTER TABLE `evento_alerta`
  MODIFY `id_evento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `grupo_alerta`
--
ALTER TABLE `grupo_alerta`
  MODIFY `id_grupo_alerta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `grupo_usuario`
--
ALTER TABLE `grupo_usuario`
  MODIFY `id_grupo_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `interface_red`
--
ALTER TABLE `interface_red`
  MODIFY `id_interface` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `limite_monitoreo`
--
ALTER TABLE `limite_monitoreo`
  MODIFY `id_limite` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `marca`
--
ALTER TABLE `marca`
  MODIFY `id_marca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `nodo`
--
ALTER TABLE `nodo`
  MODIFY `id_nodo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `notificacion_alerta`
--
ALTER TABLE `notificacion_alerta`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `tipo_equipo`
--
ALTER TABLE `tipo_equipo`
  MODIFY `id_tipo_equipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `datacenter`
--
ALTER TABLE `datacenter`
  ADD CONSTRAINT `fk_datacenter_ciudad` FOREIGN KEY (`id_ciudad`) REFERENCES `ciudad` (`id_ciudad`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_datacenter_empresa` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_grupo_alerta`
--
ALTER TABLE `detalle_grupo_alerta`
  ADD CONSTRAINT `fk_dga_alerta` FOREIGN KEY (`id_alerta`) REFERENCES `alerta` (`id_alerta`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dga_grupo` FOREIGN KEY (`id_grupo_alerta`) REFERENCES `grupo_alerta` (`id_grupo_alerta`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_grupo_usuario`
--
ALTER TABLE `detalle_grupo_usuario`
  ADD CONSTRAINT `fk_dgu_grupo` FOREIGN KEY (`id_grupo_usuario`) REFERENCES `grupo_usuario` (`id_grupo_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dgu_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `equipo`
--
ALTER TABLE `equipo`
  ADD CONSTRAINT `fk_equipo_marca` FOREIGN KEY (`id_marca`) REFERENCES `marca` (`id_marca`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_equipo_nodo` FOREIGN KEY (`id_nodo`) REFERENCES `nodo` (`id_nodo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_equipo_tipo` FOREIGN KEY (`id_tipo_equipo`) REFERENCES `tipo_equipo` (`id_tipo_equipo`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `evento_alerta`
--
ALTER TABLE `evento_alerta`
  ADD CONSTRAINT `fk_evento_alerta` FOREIGN KEY (`id_alerta`) REFERENCES `alerta` (`id_alerta`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `interface_red`
--
ALTER TABLE `interface_red`
  ADD CONSTRAINT `fk_interface_equipo` FOREIGN KEY (`id_equipo`) REFERENCES `equipo` (`id_equipo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `nodo`
--
ALTER TABLE `nodo`
  ADD CONSTRAINT `fk_nodo_datacenter` FOREIGN KEY (`id_datacenter`) REFERENCES `datacenter` (`id_datacenter`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `notificacion_alerta`
--
ALTER TABLE `notificacion_alerta`
  ADD CONSTRAINT `fk_notificacion_evento` FOREIGN KEY (`id_evento`) REFERENCES `evento_alerta` (`id_evento`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notificacion_grupo_usuario` FOREIGN KEY (`id_grupo_usuario`) REFERENCES `grupo_usuario` (`id_grupo_usuario`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notificacion_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_datacenter` FOREIGN KEY (`id_datacenter`) REFERENCES `datacenter` (`id_datacenter`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuario_empresa` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuario_equipo` FOREIGN KEY (`id_equipo`) REFERENCES `equipo` (`id_equipo`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuario_nodo` FOREIGN KEY (`id_nodo`) REFERENCES `nodo` (`id_nodo`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
