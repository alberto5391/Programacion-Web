USE `plagueweb_db`;

-- Agrega la fotografía (opcional) de cada incidencia.
-- Se guarda la ruta relativa del archivo, igual que en evidenciasfotograficas.RutaArchivo;
-- la imagen queda en resources/uploads/incidencias/.
SET @database_name = DATABASE();
SET @has_foto_column = (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @database_name
    AND TABLE_NAME = 'incidencias'
    AND COLUMN_NAME = 'RutaFoto'
);
SET @migration_sql = IF(
  @has_foto_column = 0,
  'ALTER TABLE `incidencias` ADD COLUMN `RutaFoto` varchar(255) DEFAULT NULL AFTER `Descripcion`',
  'SELECT 1'
);
PREPARE incidencia_foto_column FROM @migration_sql;
EXECUTE incidencia_foto_column;
DEALLOCATE PREPARE incidencia_foto_column;
