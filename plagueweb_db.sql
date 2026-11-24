-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3307
-- Tiempo de generación: 24-11-2026 a las 07:48:21
-- Versión del servidor: 11.5.2-MariaDB
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `plagueweb_db`
--
CREATE DATABASE IF NOT EXISTS `plagueweb_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `plagueweb_db`;

-- Este archivo es solo para instalar en una base vacía; no es una migración.
DELIMITER //
DROP PROCEDURE IF EXISTS `plagueweb_assert_empty_database`//
CREATE PROCEDURE `plagueweb_assert_empty_database`()
BEGIN
  IF EXISTS (
    SELECT 1
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
  ) THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Importación cancelada: plagueweb_db ya contiene tablas. Usa una base vacía para no modificar datos existentes.';
  END IF;
END//
DELIMITER ;
CALL `plagueweb_assert_empty_database`();
DROP PROCEDURE `plagueweb_assert_empty_database`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `accionescampana`
--

CREATE TABLE IF NOT EXISTS `accionescampana` (
  `IdAccion` int(11) NOT NULL AUTO_INCREMENT,
  `IdCampana` int(11) NOT NULL,
  `IdTecnico` int(11) NOT NULL,
  `TipoAccion` enum('Inspección','Diagnóstico','Aplicación','Seguimiento') NOT NULL,
  `FechaHora` datetime NOT NULL,
  `DetallesResultados` text NOT NULL,
  `Observaciones` text DEFAULT NULL,
  PRIMARY KEY (`IdAccion`),
  KEY `fk_accion_campana` (`IdCampana`),
  KEY `fk_accion_tecnico` (`IdTecnico`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE IF NOT EXISTS `bitacora` (
  `IdBitacora` int(11) NOT NULL AUTO_INCREMENT,
  `IdUsuario` int(11) NOT NULL,
  `IdModulo` int(11) NOT NULL,
  `TipoAccion` varchar(50) NOT NULL,
  `FechaInicioBit` datetime NOT NULL DEFAULT current_timestamp(),
  `FechaFinBit` datetime DEFAULT NULL,
  PRIMARY KEY (`IdBitacora`),
  KEY `fk_bitacora_usuario` (`IdUsuario`),
  KEY `fk_bitacora_modulo` (`IdModulo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `campanas`
--

CREATE TABLE IF NOT EXISTS `campanas` (
  `IdCampana` int(11) NOT NULL AUTO_INCREMENT,
  `CodigoCampana` varchar(50) NOT NULL,
  `NombreCampana` varchar(150) NOT NULL,
  `IdZona` int(11) NOT NULL,
  `FechaInicio` date NOT NULL,
  `FechaFin` date DEFAULT NULL,
  `Estado` enum('Programada','En Curso','Finalizada','Cancelada') DEFAULT 'Programada',
  PRIMARY KEY (`IdCampana`),
  UNIQUE KEY `CodigoCampana` (`CodigoCampana`),
  KEY `fk_campana_zona` (`IdZona`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `campanas`
--

INSERT INTO `campanas` (`IdCampana`, `CodigoCampana`, `NombreCampana`, `IdZona`, `FechaInicio`, `FechaFin`, `Estado`) VALUES
(1, 'CAMP-2026-0100', 'Prevencion de dengue ITVH', 1, '2026-10-28', '2026-10-31', 'Programada'),
(2, '3456', 'sdfgh', 1, '2026-11-03', NULL, 'En Curso'),
(3, 'smd, edi', 'asmnd', 3, '2026-11-23', '2026-11-17', 'Programada'),
(8, '1234566754', 'noviembre', 3, '2026-11-11', '2026-11-30', 'En Curso');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `campanatecnicos`
--

CREATE TABLE IF NOT EXISTS `campanatecnicos` (
  `IdCampana` int(11) NOT NULL,
  `IdUsuario` int(11) NOT NULL,
  PRIMARY KEY (`IdCampana`,`IdUsuario`),
  KEY `fk_ct_usuario` (`IdUsuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evidenciasfotograficas`
--

CREATE TABLE IF NOT EXISTS `evidenciasfotograficas` (
  `IdEvidencia` int(11) NOT NULL AUTO_INCREMENT,
  `IdCampana` int(11) NOT NULL,
  `IdAccion` int(11) DEFAULT NULL,
  `TipoActividad` enum('Inspección','Aplicación','Seguimiento') NOT NULL,
  `RutaArchivo` varchar(255) NOT NULL,
  `FechaSubida` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`IdEvidencia`),
  KEY `fk_evidencia_campana` (`IdCampana`),
  KEY `fk_evidencia_accion` (`IdAccion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidencias`
--

CREATE TABLE IF NOT EXISTS `incidencias` (
  `IdIncidencia` int(11) NOT NULL AUTO_INCREMENT,
  `IdCampana` int(11) NOT NULL,
  `Zona` varchar(100) NOT NULL,
  `ActividadRelacionada` enum('Inspección','Aplicación','Seguimiento') NOT NULL,
  `Descripcion` text NOT NULL,
  `FechaRegistro` datetime DEFAULT current_timestamp(),
  `SincronizadoOffline` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`IdIncidencia`),
  KEY `fk_incidencia_campana` (`IdCampana`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `insumosusados`
--

CREATE TABLE IF NOT EXISTS `insumosusados` (
  `IdAccion` int(11) NOT NULL,
  `IdProducto` int(11) NOT NULL,
  `CantidadAplicada` decimal(10,2) NOT NULL,
  `UnidadMedida` varchar(20) NOT NULL,
  PRIMARY KEY (`IdAccion`,`IdProducto`),
  KEY `fk_iu_producto` (`IdProducto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulos`
--

CREATE TABLE IF NOT EXISTS `modulos` (
  `IdModulo` int(11) NOT NULL AUTO_INCREMENT,
  `NombreModulo` varchar(100) NOT NULL,
  `EstadoModulo` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  PRIMARY KEY (`IdModulo`),
  UNIQUE KEY `NombreModulo` (`NombreModulo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `origencampana`
--

CREATE TABLE IF NOT EXISTS `origencampana` (
  `IdOrigenCampana` int(11) NOT NULL AUTO_INCREMENT,
  `IdCampana` int(11) NOT NULL,
  `IdSolicitante` int(11) NOT NULL,
  `FechaSolicitud` date NOT NULL,
  PRIMARY KEY (`IdOrigenCampana`),
  KEY `fk_origencampana_campana` (`IdCampana`),
  KEY `fk_origencampana_solicitante` (`IdSolicitante`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plagas`
--

CREATE TABLE IF NOT EXISTS `plagas` (
  `IdPlaga` int(11) NOT NULL AUTO_INCREMENT,
  `CodigoPlaga` varchar(20) NOT NULL,
  `NombreComun` varchar(100) NOT NULL,
  `NombreCientifico` varchar(150) DEFAULT NULL,
  `Descripcion` text NOT NULL,
  `TratamientoRecomendado` text DEFAULT NULL,
  `Estado` enum('Activa','Inactiva') DEFAULT 'Activa',
  PRIMARY KEY (`IdPlaga`),
  UNIQUE KEY `CodigoPlaga` (`CodigoPlaga`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `plagas`
--

INSERT INTO `plagas` (`IdPlaga`, `CodigoPlaga`, `NombreComun`, `NombreCientifico`, `Descripcion`, `TratamientoRecomendado`, `Estado`) VALUES
(3, 'sdrfghjkl', 'sxcvbn', 'dfghj', 'wswdfghjk', 'sdfghjk', 'Activa'),
(4, 'PGG001', 'Abejas', 'ghujiop', '´<ghijowmefkogwnroj', 'incendio', 'Activa');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plagasidentificadas`
--

CREATE TABLE IF NOT EXISTS `plagasidentificadas` (
  `IdAccion` int(11) NOT NULL,
  `IdPlaga` int(11) NOT NULL,
  `NivelInfestacion` enum('Bajo','Medio','Alto','Crítico') DEFAULT NULL,
  `Observaciones` text DEFAULT NULL,
  PRIMARY KEY (`IdAccion`,`IdPlaga`),
  KEY `fk_pi_plaga` (`IdPlaga`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE IF NOT EXISTS `productos` (
  `IdProducto` int(11) NOT NULL AUTO_INCREMENT,
  `CodigoProducto` varchar(20) NOT NULL,
  `NombreComercial` varchar(150) NOT NULL,
  `IngredienteActivo` varchar(150) DEFAULT NULL,
  `TipoProducto` enum('Insecticida','Rodenticida','Larvicida','Repelente','Otro') NOT NULL,
  `UnidadMedida` varchar(50) NOT NULL,
  `PresentacionDosis` text DEFAULT NULL,
  `Estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  PRIMARY KEY (`IdProducto`),
  UNIQUE KEY `CodigoProducto` (`CodigoProducto`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`IdProducto`, `CodigoProducto`, `NombreComercial`, `IngredienteActivo`, `TipoProducto`, `UnidadMedida`, `PresentacionDosis`, `Estado`) VALUES
(1, 'PROD-001', 'Paracetamol genérico', 'Acetaminofén', 'Insecticida', 'Caja', '500 mg', 'Activo'),
(2, 'PROD-002', 'Amoxil', 'Amoxicilina trihidratada', 'Rodenticida', 'Frasco', '250 mg/5ml', 'Activo'),
(3, 'wertyui', 'xfcgvb', 'cfgvhbjn', 'Larvicida', 'Por Definir', 'cvbnm', 'Activo'),
(4, 'bvc', 'coca', 'fd', 'Otro', 'Por Definir', 'gfd', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recuperacioncontrasena`
--

CREATE TABLE IF NOT EXISTS `recuperacioncontrasena` (
  `IdRecuperacion` int(11) NOT NULL AUTO_INCREMENT,
  `CorreoContrasena` varchar(150) NOT NULL,
  `CodigoRecuperacion` varchar(255) NOT NULL,
  `NuevaContrasena` varchar(255) DEFAULT NULL,
  `ConfirmarContrasena` varchar(255) DEFAULT NULL,
  `Verificado` tinyint(1) NOT NULL DEFAULT 0,
  `Utilizado` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`IdRecuperacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `IdRol` int(11) NOT NULL AUTO_INCREMENT,
  `NombreRol` varchar(50) NOT NULL,
  PRIMARY KEY (`IdRol`),
  UNIQUE KEY `NombreRol` (`NombreRol`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`IdRol`, `NombreRol`) VALUES
(1, 'Administrador'),
(2, 'Supervisor'),
(3, 'Tecnico');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rolpermisos`
--

CREATE TABLE IF NOT EXISTS `rolpermisos` (
  `IdRolPermisos` int(11) NOT NULL AUTO_INCREMENT,
  `IdUsuario` int(11) NOT NULL,
  `Consultar` tinyint(1) NOT NULL DEFAULT 0,
  `Registrar` tinyint(1) NOT NULL DEFAULT 0,
  `Modificar` tinyint(1) NOT NULL DEFAULT 0,
  `Eliminar` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`IdRolPermisos`),
  UNIQUE KEY `IdUsuario` (`IdUsuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitantes`
--

CREATE TABLE IF NOT EXISTS `solicitantes` (
  `IdSolicitante` int(11) NOT NULL AUTO_INCREMENT,
  `TipoOrigen` enum('Reporte ciudadano','Cliente privado','Programa de salud público','Institución','Solicitud interna','Otro') NOT NULL,
  `NombreSolicitante` varchar(150) NOT NULL,
  `TelefonoSolicitante` varchar(20) DEFAULT NULL,
  `DescripcionSolicitud` text NOT NULL,
  `EstadoSolicitud` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  PRIMARY KEY (`IdSolicitante`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tecnicos`
--

CREATE TABLE IF NOT EXISTS `tecnicos` (
  `IdTecnico` int(11) NOT NULL AUTO_INCREMENT,
  `IdUsuario` int(11) NOT NULL,
  `NumeroEmpleado` varchar(20) NOT NULL,
  `Especialidad` enum('Inspección','Aplicación','Ambas') NOT NULL,
  PRIMARY KEY (`IdTecnico`),
  UNIQUE KEY `IdUsuario` (`IdUsuario`),
  UNIQUE KEY `NumeroEmpleado` (`NumeroEmpleado`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tecnicos`
--

INSERT INTO `tecnicos` (`IdTecnico`, `IdUsuario`, `NumeroEmpleado`, `Especialidad`) VALUES
(1, 4, 'TEC-001', 'Inspección'),
(5, 2, 'TEC-004', 'Ambas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE IF NOT EXISTS `usuarios` (
  `IdUsuario` int(11) NOT NULL AUTO_INCREMENT,
  `IdRol` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Apellidos` varchar(100) NOT NULL,
  `Contacto` varchar(15) NOT NULL,
  `Correo` varchar(150) NOT NULL,
  `Curp` varchar(18) NOT NULL,
  `Rfc` varchar(13) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `FechaCreacion` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`IdUsuario`),
  UNIQUE KEY `Correo` (`Correo`),
  UNIQUE KEY `Curp` (`Curp`),
  UNIQUE KEY `Rfc` (`Rfc`),
  KEY `fk_usuario_rol` (`IdRol`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`IdUsuario`, `IdRol`, `Nombre`, `Apellidos`, `Contacto`, `Correo`, `Curp`, `Rfc`, `Password`, `Estado`, `FechaCreacion`) VALUES
(1, 1, 'Admin', 'Root', '1234567890', 'root@root.com', 'root123', 'root123', '$2y$12$kC1gQgG4I96wkLMA0CUStOHAUFUMB9znUjWHC197AbmLOyaexZa4G', 'Activo', '2026-10-23 06:14:13'),
(2, 2, 'root2', 'root2', 'root2', 'root2@root2.com', 'root2', 'root2', '$2y$12$Fa834aRyCbfNh2IrhDc0p.HQYrT5INjmD3FqGLOeIpSmj.Ri1ws2K', 'Activo', '2026-10-23 15:55:25'),
(3, 3, 'root3', 'root3', 'root3', 'root3@root3.com', 'root3', 'root3', '$2y$12$Diph8QyGt/JlJvyuGeYlTOfYtCWwPuii1qLf0XMNu5cbhgSIqj4hC', 'Activo', '2026-10-23 15:52:02'),
(4, 1, 'JOSE ALBERTO', 'RODRIGUEZ perez', '1234567890', 'alberto@gmail.com', 'ROGA', 'ROGA', '$2y$10$HQFCz/N1zaLN.Syd8ZFEBu6fkWFV.P9VJ8uH74okCtpsGEyxvZRBm', 'Activo', '2026-10-27 19:43:35'),
(5, 2, 'EDUARDO ALBERTO', 'RODRIGUEZ GERONIMO', '123456789', 'prueba1@itvh.tecnm.mx.com', '123456789', '123456789', '$2y$10$kC3HFoaYZX5Hgatg4BbaLe1kITs.tjGIdqnPM1Sq2DwAdodJUVv4u', 'Activo', '2026-10-28 16:26:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zonas`
--

CREATE TABLE IF NOT EXISTS `zonas` (
  `IdZona` int(11) NOT NULL AUTO_INCREMENT,
  `CodigoZona` varchar(20) NOT NULL,
  `NombreZona` varchar(150) NOT NULL,
  `Municipio` varchar(100) NOT NULL,
  `Referencias` text NOT NULL,
  `Latitud` decimal(10,8) DEFAULT NULL,
  `Longitud` decimal(11,8) DEFAULT NULL,
  `Estado` enum('Activa','Inactiva') DEFAULT 'Activa',
  `FechaRegistro` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`IdZona`),
  UNIQUE KEY `CodigoZona` (`CodigoZona`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `zonas`
--

INSERT INTO `zonas` (`IdZona`, `CodigoZona`, `NombreZona`, `Municipio`, `Referencias`, `Latitud`, `Longitud`, `Estado`, `FechaRegistro`) VALUES
(1, 'ZN-001', 'Indeco', 'Centro', 'Por el colegip', NULL, NULL, 'Activa', '2026-10-28 07:07:00'),
(3, 'gfchv', 'kne', 'gb-lk', 'a,sasdfs', NULL, NULL, 'Activa', '2026-10-28 16:27:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zonasprioritarias`
--

CREATE TABLE IF NOT EXISTS `zonasprioritarias` (
  `IdZonaPrioritaria` int(11) NOT NULL AUTO_INCREMENT,
  `IdZona` int(11) NOT NULL,
  `IdPlaga` int(11) NOT NULL,
  `FechaInicialPrioritaria` date NOT NULL,
  `FechaFinalPrioritaria` date NOT NULL,
  PRIMARY KEY (`IdZonaPrioritaria`),
  KEY `fk_zp_zona` (`IdZona`),
  KEY `fk_zp_plaga` (`IdPlaga`)
) ;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `accionescampana`
--
ALTER TABLE `accionescampana`
  ADD CONSTRAINT `fk_accion_campana` FOREIGN KEY (`IdCampana`) REFERENCES `campanas` (`IdCampana`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_accion_tecnico` FOREIGN KEY (`IdTecnico`) REFERENCES `tecnicos` (`IdTecnico`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD CONSTRAINT `fk_bitacora_modulo` FOREIGN KEY (`IdModulo`) REFERENCES `modulos` (`IdModulo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bitacora_usuario` FOREIGN KEY (`IdUsuario`) REFERENCES `usuarios` (`IdUsuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `campanas`
--
ALTER TABLE `campanas`
  ADD CONSTRAINT `fk_campana_zona` FOREIGN KEY (`IdZona`) REFERENCES `zonas` (`IdZona`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `campanatecnicos`
--
ALTER TABLE `campanatecnicos`
  ADD CONSTRAINT `fk_ct_campana` FOREIGN KEY (`IdCampana`) REFERENCES `campanas` (`IdCampana`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ct_usuario` FOREIGN KEY (`IdUsuario`) REFERENCES `usuarios` (`IdUsuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `evidenciasfotograficas`
--
ALTER TABLE `evidenciasfotograficas`
  ADD CONSTRAINT `fk_evidencia_accion` FOREIGN KEY (`IdAccion`) REFERENCES `accionescampana` (`IdAccion`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_evidencia_campana` FOREIGN KEY (`IdCampana`) REFERENCES `campanas` (`IdCampana`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `incidencias`
--
ALTER TABLE `incidencias`
  ADD CONSTRAINT `fk_incidencia_campana` FOREIGN KEY (`IdCampana`) REFERENCES `campanas` (`IdCampana`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `insumosusados`
--
ALTER TABLE `insumosusados`
  ADD CONSTRAINT `fk_iu_accion` FOREIGN KEY (`IdAccion`) REFERENCES `accionescampana` (`IdAccion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_iu_producto` FOREIGN KEY (`IdProducto`) REFERENCES `productos` (`IdProducto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `origencampana`
--
ALTER TABLE `origencampana`
  ADD CONSTRAINT `fk_origencampana_campana` FOREIGN KEY (`IdCampana`) REFERENCES `campanas` (`IdCampana`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_origencampana_solicitante` FOREIGN KEY (`IdSolicitante`) REFERENCES `solicitantes` (`IdSolicitante`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `plagasidentificadas`
--
ALTER TABLE `plagasidentificadas`
  ADD CONSTRAINT `fk_pi_accion` FOREIGN KEY (`IdAccion`) REFERENCES `accionescampana` (`IdAccion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pi_plaga` FOREIGN KEY (`IdPlaga`) REFERENCES `plagas` (`IdPlaga`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `rolpermisos`
--
ALTER TABLE `rolpermisos`
  ADD CONSTRAINT `fk_rol_permisos_usuario` FOREIGN KEY (`IdUsuario`) REFERENCES `usuarios` (`IdUsuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tecnicos`
--
ALTER TABLE `tecnicos`
  ADD CONSTRAINT `fk_tecnico_usuario` FOREIGN KEY (`IdUsuario`) REFERENCES `usuarios` (`IdUsuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`IdRol`) REFERENCES `roles` (`IdRol`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `zonasprioritarias`
--
ALTER TABLE `zonasprioritarias`
  ADD CONSTRAINT `fk_zp_plaga` FOREIGN KEY (`IdPlaga`) REFERENCES `plagas` (`IdPlaga`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_zp_zona` FOREIGN KEY (`IdZona`) REFERENCES `zonas` (`IdZona`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
