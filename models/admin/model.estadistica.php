<?php

class ModelEstadistica
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerMunicipios()
    {
        $resultado = $this->conexion->query(
            "SELECT DISTINCT Municipio
             FROM Zonas
             WHERE Estado = 'Activa'
             ORDER BY Municipio"
        );
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerResumen($fechaInicio, $fechaFin, $municipio)
    {
        $stmt = $this->conexion->prepare(
            "SELECT COUNT(*) AS TotalCampanas,
                    COALESCE(SUM(c.Estado = 'Finalizada'), 0) AS CampanasFinalizadas,
                    COALESCE(SUM(c.Estado = 'En Curso'), 0) AS CampanasEnCurso
             FROM Campanas c
             INNER JOIN Zonas z ON z.IdZona = c.IdZona
             WHERE c.FechaInicio >= ?
               AND c.FechaInicio < DATE_ADD(?, INTERVAL 1 DAY)
               AND (? = '' OR z.Municipio = ?)"
        );
        $stmt->bind_param('ssss', $fechaInicio, $fechaFin, $municipio, $municipio);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerCampanasPorPeriodo($fechaInicio, $fechaFin, $municipio)
    {
        $stmt = $this->conexion->prepare(
            "SELECT DATE_FORMAT(c.FechaInicio, '%Y-%m') AS Periodo,
                    COUNT(*) AS TotalCampanas,
                    COALESCE(SUM(c.Estado = 'Finalizada'), 0) AS CampanasFinalizadas
             FROM Campanas c
             INNER JOIN Zonas z ON z.IdZona = c.IdZona
             WHERE c.FechaInicio >= ?
               AND c.FechaInicio < DATE_ADD(?, INTERVAL 1 DAY)
               AND (? = '' OR z.Municipio = ?)
             GROUP BY DATE_FORMAT(c.FechaInicio, '%Y-%m')
             ORDER BY Periodo"
        );
        $stmt->bind_param('ssss', $fechaInicio, $fechaFin, $municipio, $municipio);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerPlagasFrecuentes($fechaInicio, $fechaFin, $municipio)
    {
        $stmt = $this->conexion->prepare(
            "SELECT p.IdPlaga, p.NombreComun AS Plaga,
                    COUNT(pi.idplagasidentificadas) AS Identificaciones
             FROM plagasidentificadas pi
             INNER JOIN AccionesCampana a ON a.IdAccion = pi.IdAccion
             INNER JOIN Campanas c ON c.IdCampana = a.IdCampana
             INNER JOIN Zonas z ON z.IdZona = c.IdZona
             INNER JOIN Plagas p ON p.IdPlaga = pi.IdPlaga
             WHERE a.FechaHora >= CONCAT(?, ' 00:00:00')
               AND a.FechaHora < DATE_ADD(?, INTERVAL 1 DAY)
               AND (? = '' OR z.Municipio = ?)
             GROUP BY p.IdPlaga, p.NombreComun
             ORDER BY Identificaciones DESC, p.NombreComun
             LIMIT 10"
        );
        $stmt->bind_param('ssss', $fechaInicio, $fechaFin, $municipio, $municipio);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerProductosMasUsados($fechaInicio, $fechaFin, $municipio)
    {
        $stmt = $this->conexion->prepare(
            "SELECT p.IdProducto, p.NombreComercial AS Producto, p.UnidadMedida,
                    COUNT(DISTINCT a.IdAccion) AS Aplicaciones,
                    COALESCE(SUM(iu.CantidadAplicada), 0) AS CantidadUtilizada
             FROM InsumosUsados iu
             INNER JOIN AccionesCampana a ON a.IdAccion = iu.IdAccion
             INNER JOIN tipoaccion ta ON ta.idTipoAccion = a.idTipoAccion
             INNER JOIN Campanas c ON c.IdCampana = a.IdCampana
             INNER JOIN Zonas z ON z.IdZona = c.IdZona
             INNER JOIN Productos p ON p.IdProducto = iu.IdProducto
             WHERE ta.Nombre = 'Aplicación'
               AND a.FechaHora >= CONCAT(?, ' 00:00:00')
               AND a.FechaHora < DATE_ADD(?, INTERVAL 1 DAY)
               AND (? = '' OR z.Municipio = ?)
             GROUP BY p.IdProducto, p.NombreComercial, p.UnidadMedida
             ORDER BY Aplicaciones DESC, CantidadUtilizada DESC, p.NombreComercial
             LIMIT 10"
        );
        $stmt->bind_param('ssss', $fechaInicio, $fechaFin, $municipio, $municipio);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerIncidenciasPorActividad($fechaInicio, $fechaFin, $municipio)
    {
        $stmt = $this->conexion->prepare(
            "SELECT i.ActividadRelacionada AS Actividad,
                    COUNT(i.IdIncidencia) AS TotalIncidencias
             FROM Incidencias i
             INNER JOIN Campanas c ON c.IdCampana = i.IdCampana
             INNER JOIN Zonas z ON z.IdZona = c.IdZona
             WHERE i.FechaRegistro >= CONCAT(?, ' 00:00:00')
               AND i.FechaRegistro < DATE_ADD(?, INTERVAL 1 DAY)
               AND (? = '' OR z.Municipio = ?)
             GROUP BY i.ActividadRelacionada
             ORDER BY TotalIncidencias DESC, i.ActividadRelacionada"
        );
        $stmt->bind_param('ssss', $fechaInicio, $fechaFin, $municipio, $municipio);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerEstadisticasZonas($fechaInicio, $fechaFin, $municipio)
    {
        $stmt = $this->conexion->prepare(
            "SELECT z.IdZona, z.NombreZona, z.Municipio, z.Latitud, z.Longitud,
                    COALESCE(SUM(m.TotalAcciones), 0) AS TotalAcciones,
                    COALESCE(SUM(m.TotalIdentificaciones), 0) AS TotalIdentificaciones,
                    COALESCE(SUM(m.TotalIncidencias), 0) AS TotalIncidencias
             FROM Zonas z
             LEFT JOIN (
                 SELECT c.IdZona, COUNT(DISTINCT a.IdAccion) AS TotalAcciones,
                        0 AS TotalIdentificaciones, 0 AS TotalIncidencias
                 FROM Campanas c
                 INNER JOIN AccionesCampana a ON a.IdCampana = c.IdCampana
                 WHERE a.FechaHora >= CONCAT(?, ' 00:00:00')
                   AND a.FechaHora < DATE_ADD(?, INTERVAL 1 DAY)
                 GROUP BY c.IdZona
                 UNION ALL
                 SELECT c.IdZona, 0, COUNT(pi.idplagasidentificadas), 0
                 FROM Campanas c
                 INNER JOIN AccionesCampana a ON a.IdCampana = c.IdCampana
                 INNER JOIN plagasidentificadas pi ON pi.IdAccion = a.IdAccion
                 WHERE a.FechaHora >= CONCAT(?, ' 00:00:00')
                   AND a.FechaHora < DATE_ADD(?, INTERVAL 1 DAY)
                 GROUP BY c.IdZona
                 UNION ALL
                 SELECT c.IdZona, 0, 0, COUNT(i.IdIncidencia)
                 FROM Campanas c
                 INNER JOIN Incidencias i ON i.IdCampana = c.IdCampana
                 WHERE i.FechaRegistro >= CONCAT(?, ' 00:00:00')
                   AND i.FechaRegistro < DATE_ADD(?, INTERVAL 1 DAY)
                 GROUP BY c.IdZona
             ) m ON m.IdZona = z.IdZona
             WHERE z.Estado = 'Activa'
               AND (? = '' OR z.Municipio = ?)
             GROUP BY z.IdZona, z.NombreZona, z.Municipio, z.Latitud, z.Longitud
             ORDER BY TotalIncidencias DESC, TotalIdentificaciones DESC, TotalAcciones DESC,
                      z.NombreZona"
        );
        $stmt->bind_param(
            'ssssssss',
            $fechaInicio,
            $fechaFin,
            $fechaInicio,
            $fechaFin,
            $fechaInicio,
            $fechaFin,
            $municipio,
            $municipio
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
