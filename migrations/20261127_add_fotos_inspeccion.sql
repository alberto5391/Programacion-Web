USE `plagueweb_db`;

-- Fotografía (opcional) de cada inspección o diagnóstico registrado por el técnico.
-- Una foto por registro de AccionesCampana (IdAccion es UNIQUE).
-- Se guarda la ruta relativa del archivo, igual que en evidenciasfotograficas.RutaArchivo;
-- la imagen queda en resources/uploads/inspecciones/.
-- Si se elimina la inspección o el diagnóstico, su fila se borra sola (ON DELETE CASCADE).
CREATE TABLE IF NOT EXISTS `fotosinspeccion` (
  `IdFotoInspeccion` int(11) NOT NULL AUTO_INCREMENT,
  `IdAccion` int(11) NOT NULL,
  `RutaFoto` varchar(255) NOT NULL,
  `FechaSubida` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`IdFotoInspeccion`),
  UNIQUE KEY `uk_fotoinspeccion_accion` (`IdAccion`),
  CONSTRAINT `fk_fotoinspeccion_accion` FOREIGN KEY (`IdAccion`) REFERENCES `accionescampana` (`IdAccion`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
