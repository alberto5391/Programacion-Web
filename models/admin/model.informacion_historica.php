<?php

class ModelInformacionHistorica
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerMunicipios()
    {
        $resultado = $this->conexion->query(
            "SELECT DISTINCT Municipio FROM Zonas ORDER BY Municipio"
        );
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function buscarCampanas(array $filtros)
    {
        $stmt = $this->conexion->prepare(
            "SELECT c.IdCampana, c.CodigoCampana, c.NombreCampana, c.FechaInicio,
                    c.FechaFin, c.Estado, z.NombreZona, z.Municipio, z.Estado AS EstadoZona
             FROM Campanas c
             INNER JOIN Zonas z ON z.IdZona = c.IdZona
             WHERE (? = '' OR c.FechaInicio >= ?)
               AND (? = '' OR c.FechaInicio <= ?)
               AND (? = '' OR c.Estado = ?)
               AND (? = '' OR z.Municipio = ?)
               AND (
                    ? = ''
                    OR c.CodigoCampana LIKE ?
                    OR c.NombreCampana LIKE ?
                    OR z.NombreZona LIKE ?
                    OR z.Municipio LIKE ?
                    OR EXISTS (
                        SELECT 1
                        FROM CampanaTecnicos ct
                        INNER JOIN Usuarios u ON u.IdUsuario = ct.IdUsuario
                        WHERE ct.IdCampana = c.IdCampana
                          AND CONCAT(u.Nombre, ' ', u.Apellidos) LIKE ?
                    )
               )
             ORDER BY c.FechaInicio DESC, c.IdCampana DESC"
        );
        $termino = '%' . $filtros['busqueda'] . '%';
        $stmt->bind_param(
            'ssssssssssssss',
            $filtros['desde'],
            $filtros['desde'],
            $filtros['hasta'],
            $filtros['hasta'],
            $filtros['estado'],
            $filtros['estado'],
            $filtros['municipio'],
            $filtros['municipio'],
            $filtros['busqueda'],
            $termino,
            $termino,
            $termino,
            $termino,
            $termino
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerCampanaPorId($idCampana)
    {
        $stmt = $this->conexion->prepare(
            "SELECT c.IdCampana, c.CodigoCampana, c.NombreCampana, c.FechaInicio,
                    c.FechaFin, c.Estado, z.NombreZona, z.Municipio,
                    z.Referencias, z.Latitud, z.Longitud, z.Estado AS EstadoZona
             FROM Campanas c
             INNER JOIN Zonas z ON z.IdZona = c.IdZona
             WHERE c.IdCampana = ?"
        );
        $stmt->bind_param('i', $idCampana);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerSolicitantes($idCampana)
    {
        $stmt = $this->conexion->prepare(
            "SELECT s.IdSolicitante, s.TipoOrigen, s.NombreSolicitante,
                    s.TelefonoSolicitante, s.DescripcionSolicitud, s.EstadoSolicitud,
                    oc.FechaSolicitud
             FROM OrigenCampana oc
             INNER JOIN Solicitantes s ON s.IdSolicitante = oc.IdSolicitante
             WHERE oc.IdCampana = ?
             ORDER BY oc.FechaSolicitud, s.NombreSolicitante"
        );
        $stmt->bind_param('i', $idCampana);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerResponsables($idCampana)
    {
        $stmt = $this->conexion->prepare(
            "SELECT u.IdUsuario, CONCAT(u.Nombre, ' ', u.Apellidos) AS NombreCompleto,
                    u.Estado AS EstadoUsuario, t.NumeroEmpleado, t.Especialidad
             FROM CampanaTecnicos ct
             INNER JOIN Usuarios u ON u.IdUsuario = ct.IdUsuario
             LEFT JOIN Tecnicos t ON t.IdUsuario = u.IdUsuario
             WHERE ct.IdCampana = ?
             ORDER BY u.Apellidos, u.Nombre"
        );
        $stmt->bind_param('i', $idCampana);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerAcciones($idCampana)
    {
        $stmt = $this->conexion->prepare(
            "SELECT a.IdAccion, a.FechaHora, a.DetallesResultados, a.Observaciones,
                    ta.Nombre AS TipoAccion, t.NumeroEmpleado,
                    CONCAT(u.Nombre, ' ', u.Apellidos) AS Tecnico
             FROM AccionesCampana a
             INNER JOIN tipoaccion ta ON ta.idTipoAccion = a.idTipoAccion
             LEFT JOIN Tecnicos t ON t.IdTecnico = a.IdTecnico
             LEFT JOIN Usuarios u ON u.IdUsuario = t.IdUsuario
             WHERE a.IdCampana = ?
             ORDER BY a.FechaHora DESC, a.IdAccion DESC"
        );
        $stmt->bind_param('i', $idCampana);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerIncidencias($idCampana)
    {
        $stmt = $this->conexion->prepare(
            "SELECT IdIncidencia, Zona, ActividadRelacionada, Descripcion,
                    FechaRegistro, SincronizadoOffline
             FROM Incidencias
             WHERE IdCampana = ?
             ORDER BY FechaRegistro DESC, IdIncidencia DESC"
        );
        $stmt->bind_param('i', $idCampana);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerPlagasIdentificadas($idCampana)
    {
        $stmt = $this->conexion->prepare(
            "SELECT pi.idplagasidentificadas, pi.NivelInfestacion, pi.Observaciones,
                    p.NombreComun AS Plaga, ta.Nombre AS TipoAccion, a.FechaHora
             FROM plagasidentificadas pi
             INNER JOIN AccionesCampana a ON a.IdAccion = pi.IdAccion
             INNER JOIN tipoaccion ta ON ta.idTipoAccion = a.idTipoAccion
             INNER JOIN Plagas p ON p.IdPlaga = pi.IdPlaga
             WHERE a.IdCampana = ?
             ORDER BY a.FechaHora DESC, pi.idplagasidentificadas DESC"
        );
        $stmt->bind_param('i', $idCampana);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerInsumosUsados($idCampana)
    {
        $stmt = $this->conexion->prepare(
            "SELECT iu.IdInsumoUsado, iu.CantidadAplicada, iu.UnidadMedida,
                    p.NombreComercial AS Producto, a.FechaHora, ta.Nombre AS TipoAccion
             FROM InsumosUsados iu
             INNER JOIN AccionesCampana a ON a.IdAccion = iu.IdAccion
             INNER JOIN tipoaccion ta ON ta.idTipoAccion = a.idTipoAccion
             INNER JOIN Productos p ON p.IdProducto = iu.IdProducto
             WHERE a.IdCampana = ?
             ORDER BY a.FechaHora DESC, iu.IdInsumoUsado DESC"
        );
        $stmt->bind_param('i', $idCampana);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerEvidencias($idCampana)
    {
        $stmt = $this->conexion->prepare(
            "SELECT e.IdEvidencia, e.idTipoAccion, e.TipoActividad, e.RutaArchivo,
                    e.FechaSubida, ta.Nombre AS TipoAccion
             FROM EvidenciasFotograficas e
             INNER JOIN tipoaccion ta ON ta.idTipoAccion = e.idTipoAccion
             WHERE e.IdCampana = ?
             ORDER BY e.FechaSubida DESC, e.IdEvidencia DESC"
        );
        $stmt->bind_param('i', $idCampana);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
