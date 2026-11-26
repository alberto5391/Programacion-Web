USE `plagueweb_db`;

-- Existing incidencias remain NULL until their category is assigned in the application.
CREATE TABLE IF NOT EXISTS `tipoincidencia` (
  `IdTipoIncidencia` int(11) NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`IdTipoIncidencia`),
  UNIQUE KEY `Nombre` (`Nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tipoincidencia` (`Nombre`) VALUES
('Presencia de plagas'),
('Saneamiento y atrayentes'),
('Infraestructura del sitio'),
('Condiciones ambientales'),
('Acceso y restricciones'),
('Equipo o insumos'),
('Seguridad operativa'),
('Otro')
ON DUPLICATE KEY UPDATE `Nombre` = VALUES(`Nombre`);

SET @database_name = DATABASE();
SET @has_type_column = (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @database_name
    AND TABLE_NAME = 'incidencias'
    AND COLUMN_NAME = 'IdTipoIncidencia'
);
SET @migration_sql = IF(
  @has_type_column = 0,
  'ALTER TABLE `incidencias` ADD COLUMN `IdTipoIncidencia` int(11) DEFAULT NULL AFTER `IdCampana`, ADD KEY `fk_incidencia_tipo` (`IdTipoIncidencia`)',
  'SELECT 1'
);
PREPARE incidencia_type_column FROM @migration_sql;
EXECUTE incidencia_type_column;
DEALLOCATE PREPARE incidencia_type_column;

SET @has_type_foreign_key = (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = @database_name
    AND TABLE_NAME = 'incidencias'
    AND CONSTRAINT_NAME = 'fk_incidencia_tipo'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);
SET @migration_sql = IF(
  @has_type_foreign_key = 0,
  'ALTER TABLE `incidencias` ADD CONSTRAINT `fk_incidencia_tipo` FOREIGN KEY (`IdTipoIncidencia`) REFERENCES `tipoincidencia` (`IdTipoIncidencia`) ON DELETE RESTRICT ON UPDATE CASCADE',
  'SELECT 1'
);
PREPARE incidencia_type_foreign_key FROM @migration_sql;
EXECUTE incidencia_type_foreign_key;
DEALLOCATE PREPARE incidencia_type_foreign_key;
