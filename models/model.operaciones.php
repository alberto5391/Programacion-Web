<?php
class ModelOperaciones
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerCampanas()
    {
        return $this->conexion->query(
            "SELECT c.IdCampana, c.CodigoCampana, c.NombreCampana, z.NombreZona
             FROM Campanas c
             INNER JOIN Zonas z ON z.IdZona = c.IdZona
             WHERE c.Estado <> 'Cancelada'
             ORDER BY c.FechaInicio DESC"
        );
    }

    public function campanaOperativa($idCampana)
    {
        $stmt = $this->conexion->prepare(
            "SELECT IdCampana FROM Campanas WHERE IdCampana = ? AND Estado <> 'Cancelada'"
        );
        $stmt->bind_param('i', $idCampana);
        $stmt->execute();
        return $stmt->get_result()->num_rows === 1;
    }

    public function obtenerIncidencias()
    {
        return $this->conexion->query(
            "SELECT i.IdIncidencia, i.IdCampana, i.Zona, i.ActividadRelacionada,
                    i.Descripcion, i.FechaRegistro, c.CodigoCampana, c.NombreCampana
             FROM Incidencias i
             INNER JOIN Campanas c ON c.IdCampana = i.IdCampana
             ORDER BY i.FechaRegistro DESC, i.IdIncidencia DESC"
        );
    }

    public function obtenerIncidenciaPorId($id)
    {
        $stmt = $this->conexion->prepare(
            "SELECT IdIncidencia, IdCampana, ActividadRelacionada, Descripcion
             FROM Incidencias
             WHERE IdIncidencia = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function registrarIncidencia($idCampana, $actividad, $descripcion)
    {
        $stmt = $this->conexion->prepare(
            "INSERT INTO Incidencias (IdCampana, Zona, ActividadRelacionada, Descripcion)
             SELECT c.IdCampana, z.NombreZona, ?, ?
             FROM Campanas c
             INNER JOIN Zonas z ON z.IdZona = c.IdZona
             WHERE c.IdCampana = ?"
        );
        $stmt->bind_param('ssi', $actividad, $descripcion, $idCampana);
        return $stmt->execute() && $stmt->affected_rows === 1;
    }

    public function actualizarIncidencia($id, $idCampana, $actividad, $descripcion)
    {
        $stmt = $this->conexion->prepare(
            "UPDATE Incidencias i
             INNER JOIN Campanas c ON c.IdCampana = ?
             INNER JOIN Zonas z ON z.IdZona = c.IdZona
             SET i.IdCampana = c.IdCampana,
                 i.Zona = z.NombreZona,
                 i.ActividadRelacionada = ?,
                 i.Descripcion = ?
             WHERE i.IdIncidencia = ?"
        );
        $stmt->bind_param('issi', $idCampana, $actividad, $descripcion, $id);
        return $stmt->execute();
    }

    public function eliminarIncidencia($id)
    {
        $stmt = $this->conexion->prepare(
            "DELETE FROM Incidencias WHERE IdIncidencia = ?"
        );
        $stmt->bind_param('i', $id);
        return $stmt->execute() && $stmt->affected_rows === 1;
    }

    public function obtenerTecnicosActivos()
    {
        return $this->conexion->query(
            "SELECT t.IdTecnico, t.IdUsuario, t.NumeroEmpleado,
                    CONCAT(u.Nombre, ' ', u.Apellidos) AS NombreCompleto
             FROM Tecnicos t
             INNER JOIN Usuarios u ON u.IdUsuario = t.IdUsuario
             WHERE u.Estado = 'Activo'
             ORDER BY u.Apellidos, u.Nombre"
        );
    }

    public function obtenerUsuariosTecnicosActivos()
    {
        return $this->conexion->query(
            "SELECT u.IdUsuario, CONCAT(u.Nombre, ' ', u.Apellidos) AS NombreCompleto,
                    t.NumeroEmpleado
             FROM Tecnicos t
             INNER JOIN Usuarios u ON u.IdUsuario = t.IdUsuario
             INNER JOIN Roles r ON r.IdRol = u.IdRol
             WHERE u.Estado = 'Activo' AND r.NombreRol = 'Tecnico'
             ORDER BY u.Apellidos, u.Nombre"
        );
    }

    public function usuarioTecnicoActivo($idUsuario)
    {
        $stmt = $this->conexion->prepare(
            "SELECT u.IdUsuario
             FROM Usuarios u
             INNER JOIN Roles r ON r.IdRol = u.IdRol
             INNER JOIN Tecnicos t ON t.IdUsuario = u.IdUsuario
             WHERE u.IdUsuario = ? AND u.Estado = 'Activo' AND r.NombreRol = 'Tecnico'"
        );
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        return $stmt->get_result()->num_rows === 1;
    }

    public function obtenerAsignaciones()
    {
        return $this->conexion->query(
            "SELECT ct.IdCampana, ct.IdUsuario, c.CodigoCampana, c.NombreCampana,
                    z.NombreZona, t.NumeroEmpleado,
                    CONCAT(u.Nombre, ' ', u.Apellidos) AS NombreCompleto
             FROM CampanaTecnicos ct
             INNER JOIN Campanas c ON c.IdCampana = ct.IdCampana
             INNER JOIN Zonas z ON z.IdZona = c.IdZona
             INNER JOIN Usuarios u ON u.IdUsuario = ct.IdUsuario
             INNER JOIN Tecnicos t ON t.IdUsuario = u.IdUsuario
             ORDER BY c.FechaInicio DESC, u.Apellidos, u.Nombre"
        );
    }

    public function asignacionExiste($idCampana, $idUsuario)
    {
        $stmt = $this->conexion->prepare(
            "SELECT IdCampana FROM CampanaTecnicos WHERE IdCampana = ? AND IdUsuario = ?"
        );
        $stmt->bind_param('ii', $idCampana, $idUsuario);
        $stmt->execute();
        return $stmt->get_result()->num_rows === 1;
    }

    public function registrarAsignacion($idCampana, $idUsuario)
    {
        $stmt = $this->conexion->prepare(
            "INSERT INTO CampanaTecnicos (IdCampana, IdUsuario) VALUES (?, ?)"
        );
        $stmt->bind_param('ii', $idCampana, $idUsuario);
        return $stmt->execute();
    }

    public function eliminarAsignacion($idCampana, $idUsuario)
    {
        $stmt = $this->conexion->prepare(
            "DELETE FROM CampanaTecnicos WHERE IdCampana = ? AND IdUsuario = ?"
        );
        $stmt->bind_param('ii', $idCampana, $idUsuario);
        return $stmt->execute() && $stmt->affected_rows === 1;
    }

    public function obtenerTecnicoPorUsuario($idUsuario)
    {
        $stmt = $this->conexion->prepare(
            "SELECT IdTecnico FROM Tecnicos WHERE IdUsuario = ?"
        );
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        $tecnico = $stmt->get_result()->fetch_assoc();
        return $tecnico ? (int)$tecnico['IdTecnico'] : null;
    }

    public function tecnicoActivo($idTecnico)
    {
        $stmt = $this->conexion->prepare(
            "SELECT t.IdTecnico
             FROM Tecnicos t
             INNER JOIN Usuarios u ON u.IdUsuario = t.IdUsuario
             WHERE t.IdTecnico = ? AND u.Estado = 'Activo'"
        );
        $stmt->bind_param('i', $idTecnico);
        $stmt->execute();
        return $stmt->get_result()->num_rows === 1;
    }

    public function plagaActiva($idPlaga)
    {
        $stmt = $this->conexion->prepare(
            "SELECT IdPlaga FROM Plagas WHERE IdPlaga = ? AND Estado = 'Activa'"
        );
        $stmt->bind_param('i', $idPlaga);
        $stmt->execute();
        return $stmt->get_result()->num_rows === 1;
    }

    public function obtenerPlagasActivas()
    {
        return $this->conexion->query(
            "SELECT IdPlaga, NombreComun
             FROM Plagas
             WHERE Estado = 'Activa'
             ORDER BY NombreComun"
        );
    }

    public function obtenerProductosActivos()
    {
        return $this->conexion->query(
            "SELECT IdProducto, NombreComercial, UnidadMedida
             FROM Productos
             WHERE Estado = 'Activo'
             ORDER BY NombreComercial"
        );
    }

    public function obtenerAccionesInspeccionDiagnostico()
    {
        return $this->conexion->query(
            "SELECT a.IdAccion, a.IdCampana, a.IdTecnico, a.TipoAccion,
                    a.FechaHora, a.DetallesResultados, a.Observaciones,
                    c.CodigoCampana, c.NombreCampana, z.NombreZona,
                    t.NumeroEmpleado, CONCAT(u.Nombre, ' ', u.Apellidos) AS Tecnico,
                    pi.IdPlaga, p.NombreComun AS Plaga, pi.NivelInfestacion
             FROM AccionesCampana a
             INNER JOIN Campanas c ON c.IdCampana = a.IdCampana
             INNER JOIN Zonas z ON z.IdZona = c.IdZona
             INNER JOIN Tecnicos t ON t.IdTecnico = a.IdTecnico
             INNER JOIN Usuarios u ON u.IdUsuario = t.IdUsuario
             LEFT JOIN PlagasIdentificadas pi ON pi.IdAccion = a.IdAccion
             LEFT JOIN Plagas p ON p.IdPlaga = pi.IdPlaga
             WHERE a.TipoAccion IN ('Inspección', 'Diagnóstico')
             ORDER BY a.FechaHora DESC, a.IdAccion DESC"
        );
    }

    public function obtenerAccionInspeccionDiagnosticoPorId($id)
    {
        $stmt = $this->conexion->prepare(
            "SELECT a.IdAccion, a.IdCampana, a.IdTecnico, a.TipoAccion,
                    a.FechaHora, a.DetallesResultados, a.Observaciones,
                    pi.IdPlaga, pi.NivelInfestacion
             FROM AccionesCampana a
             LEFT JOIN PlagasIdentificadas pi ON pi.IdAccion = a.IdAccion
             WHERE a.IdAccion = ?
               AND a.TipoAccion IN ('Inspección', 'Diagnóstico')
             LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function registrarPlagaIdentificada($idAccion, $idPlaga, $nivel, $observaciones)
    {
        $stmt = $this->conexion->prepare(
            "INSERT INTO PlagasIdentificadas
                (IdAccion, IdPlaga, NivelInfestacion, Observaciones)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param('iiss', $idAccion, $idPlaga, $nivel, $observaciones);
        return $stmt->execute();
    }

    public function obtenerAccionesAplicacion()
    {
        return $this->conexion->query(
            "SELECT a.IdAccion, a.IdCampana, a.IdTecnico, a.FechaHora,
                    a.DetallesResultados, a.Observaciones,
                    c.CodigoCampana, c.NombreCampana, z.NombreZona,
                    t.NumeroEmpleado, CONCAT(u.Nombre, ' ', u.Apellidos) AS Tecnico,
                    iu.IdProducto, iu.CantidadAplicada, iu.UnidadMedida,
                    p.NombreComercial AS Producto
             FROM AccionesCampana a
             INNER JOIN Campanas c ON c.IdCampana = a.IdCampana
             INNER JOIN Zonas z ON z.IdZona = c.IdZona
             INNER JOIN Tecnicos t ON t.IdTecnico = a.IdTecnico
             INNER JOIN Usuarios u ON u.IdUsuario = t.IdUsuario
             LEFT JOIN InsumosUsados iu ON iu.IdAccion = a.IdAccion
             LEFT JOIN Productos p ON p.IdProducto = iu.IdProducto
             WHERE a.TipoAccion = 'Aplicación'
             ORDER BY a.FechaHora DESC, a.IdAccion DESC"
        );
    }

    public function obtenerAccionAplicacionPorId($id)
    {
        $stmt = $this->conexion->prepare(
            "SELECT a.IdAccion, a.IdCampana, a.IdTecnico, a.FechaHora,
                    a.DetallesResultados, a.Observaciones,
                    iu.IdProducto, iu.CantidadAplicada, iu.UnidadMedida
             FROM AccionesCampana a
             LEFT JOIN InsumosUsados iu ON iu.IdAccion = a.IdAccion
             WHERE a.IdAccion = ? AND a.TipoAccion = 'Aplicación'
             LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerProductoPorId($id)
    {
        $stmt = $this->conexion->prepare(
            "SELECT IdProducto, NombreComercial, UnidadMedida
             FROM Productos
             WHERE IdProducto = ? AND Estado = 'Activo'"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function reemplazarInsumoUsado($idAccion, $idProducto, $cantidad, $unidad)
    {
        $delete = $this->conexion->prepare(
            "DELETE FROM InsumosUsados WHERE IdAccion = ?"
        );
        $delete->bind_param('i', $idAccion);
        if (!$delete->execute()) {
            return false;
        }

        $insert = $this->conexion->prepare(
            "INSERT INTO InsumosUsados
                (IdAccion, IdProducto, CantidadAplicada, UnidadMedida)
             VALUES (?, ?, ?, ?)"
        );
        $insert->bind_param('iids', $idAccion, $idProducto, $cantidad, $unidad);
        return $insert->execute();
    }

    public function actualizarPlagaIdentificada($idAccion, $idPlaga, $nivel, $observaciones)
    {
        $delete = $this->conexion->prepare(
            "DELETE FROM PlagasIdentificadas WHERE IdAccion = ?"
        );
        $delete->bind_param('i', $idAccion);
        if (!$delete->execute()) {
            return false;
        }

        return $this->registrarPlagaIdentificada(
            $idAccion,
            $idPlaga,
            $nivel,
            $observaciones
        );
    }

    public function eliminarPlagasIdentificadas($idAccion)
    {
        $stmt = $this->conexion->prepare(
            "DELETE FROM PlagasIdentificadas WHERE IdAccion = ?"
        );
        $stmt->bind_param('i', $idAccion);
        return $stmt->execute();
    }

    public function obtenerAccionesPorTipo($tipo)
    {
        $stmt = $this->conexion->prepare(
            "SELECT a.IdAccion, a.IdCampana, a.IdTecnico, a.TipoAccion,
                    a.FechaHora, a.DetallesResultados, a.Observaciones,
                    c.CodigoCampana, c.NombreCampana, z.NombreZona,
                    t.NumeroEmpleado, CONCAT(u.Nombre, ' ', u.Apellidos) AS Tecnico
             FROM AccionesCampana a
             INNER JOIN Campanas c ON c.IdCampana = a.IdCampana
             INNER JOIN Zonas z ON z.IdZona = c.IdZona
             INNER JOIN Tecnicos t ON t.IdTecnico = a.IdTecnico
             INNER JOIN Usuarios u ON u.IdUsuario = t.IdUsuario
             WHERE a.TipoAccion = ?
             ORDER BY a.FechaHora DESC, a.IdAccion DESC"
        );
        $stmt->bind_param('s', $tipo);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function obtenerAccionPorIdYTipo($id, $tipo)
    {
        $stmt = $this->conexion->prepare(
            "SELECT IdAccion, IdCampana, IdTecnico, FechaHora,
                    DetallesResultados, Observaciones
             FROM AccionesCampana
             WHERE IdAccion = ? AND TipoAccion = ?"
        );
        $stmt->bind_param('is', $id, $tipo);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function registrarAccion($idCampana, $idTecnico, $tipo, $fechaHora, $detalles, $observaciones)
    {
        $stmt = $this->conexion->prepare(
            "INSERT INTO AccionesCampana
                (IdCampana, IdTecnico, TipoAccion, FechaHora, DetallesResultados, Observaciones)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('iissss', $idCampana, $idTecnico, $tipo, $fechaHora, $detalles, $observaciones);
        return $stmt->execute() && $stmt->affected_rows === 1;
    }

    public function actualizarAccion($id, $idCampana, $idTecnico, $tipo, $fechaHora, $detalles, $observaciones)
    {
        $stmt = $this->conexion->prepare(
            "UPDATE AccionesCampana
             SET IdCampana = ?, IdTecnico = ?, FechaHora = ?,
                 DetallesResultados = ?, Observaciones = ?
             WHERE IdAccion = ? AND TipoAccion = ?"
        );
        $stmt->bind_param(
            'iisssis',
            $idCampana,
            $idTecnico,
            $fechaHora,
            $detalles,
            $observaciones,
            $id,
            $tipo
        );
        return $stmt->execute();
    }

    public function eliminarAccionPorTipo($id, $tipo)
    {
        $stmt = $this->conexion->prepare(
            "DELETE FROM AccionesCampana WHERE IdAccion = ? AND TipoAccion = ?"
        );
        $stmt->bind_param('is', $id, $tipo);
        return $stmt->execute() && $stmt->affected_rows === 1;
    }
}
