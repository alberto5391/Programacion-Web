USE `plagueweb_db`;

-- Fotografía (opcional) de cada tratamiento/aplicación registrado por el técnico:
-- el producto aplicado o el área tratada. Una foto por registro de AccionesCampana (IdAccion es UNIQUE).
-- Se guarda la ruta relativa del archivo, igual que en evidenciasfotograficas.RutaArchivo;
-- la imagen queda en resources/uploads/tratamientos/.
-- Si se elimina la aplicación, su fila se borra sola (ON DELETE CASCADE).
CREATE TABLE IF NOT EXISTS `fotostratamiento` (
  `IdFotoTratamiento` int(11) NOT NULL AUTO_INCREMENT,
  `IdAccion` int(11) NOT NULL,
  `RutaFoto` varchar(255) NOT NULL,
  `FechaSubida` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`IdFotoTratamiento`),
  UNIQUE KEY `uk_fototratamiento_accion` (`IdAccion`),
  CONSTRAINT `fk_fototratamiento_accion` FOREIGN KEY (`IdAccion`) REFERENCES `accionescampana` (`IdAccion`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
