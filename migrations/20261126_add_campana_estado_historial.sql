USE `plagueweb_db`;

CREATE TABLE IF NOT EXISTS `historial_estados_campana` (
  `IdHistorialEstado` int(11) NOT NULL AUTO_INCREMENT,
  `IdCampana` int(11) DEFAULT NULL,
  `CodigoCampana` varchar(50) NOT NULL,
  `IdUsuario` int(11) NOT NULL,
  `EstadoAnterior` enum('Programada','En Curso','Finalizada','Cancelada') NOT NULL,
  `EstadoNuevo` enum('Programada','En Curso','Finalizada','Cancelada') NOT NULL,
  `FechaCambio` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`IdHistorialEstado`),
  KEY `idx_historial_campana_fecha` (`IdCampana`, `FechaCambio`),
  KEY `idx_historial_campana_usuario` (`IdUsuario`),
  CONSTRAINT `fk_historial_estado_campana` FOREIGN KEY (`IdCampana`) REFERENCES `campanas` (`IdCampana`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
