<?php
class ModelCampana {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Registrar una nueva campaña
    public function registrarCampana($codigo, $nombre, $id_zona, $fecha_inicio, $fecha_fin, $estado) {
        $stmt = $this->conexion->prepare("INSERT INTO Campanas (CodigoCampana, NombreCampana, IdZona, FechaInicio, FechaFin, Estado) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisss", $codigo, $nombre, $id_zona, $fecha_inicio, $fecha_fin, $estado);
        if (!$stmt->execute()) {
            return false;
        }
        return $this->conexion->insert_id;
    }

    public function obtenerMunicipios() {
        return $this->conexion->query(
            "SELECT DISTINCT Municipio FROM Zonas ORDER BY Municipio"
        );
    }

    public function obtenerCampanas($municipio = '', $busqueda = '') {
        $stmt = $this->conexion->prepare(
            "SELECT c.IdCampana, c.CodigoCampana, c.NombreCampana, c.FechaInicio, c.FechaFin, c.Estado,
                    z.CodigoZona, z.NombreZona, z.Municipio
             FROM Campanas c
             INNER JOIN Zonas z ON c.IdZona = z.IdZona
             WHERE (? = '' OR z.Municipio = ?)
               AND (? = '' OR c.CodigoCampana LIKE CONCAT('%', ?, '%')
                    OR c.NombreCampana LIKE CONCAT('%', ?, '%'))
             ORDER BY c.IdCampana DESC"
        );
        $stmt->bind_param(
            'sssss',
            $municipio,
            $municipio,
            $busqueda,
            $busqueda,
            $busqueda
        );
        $stmt->execute();
        return $stmt->get_result();
    }

    // Obtener una campaña específica por su ID
    public function obtenerCampanaPorId($idCampana) {
        $stmt = $this->conexion->prepare("SELECT IdCampana, CodigoCampana, NombreCampana, IdZona, FechaInicio, FechaFin, Estado FROM Campanas WHERE IdCampana = ?");
        $stmt->bind_param("i", $idCampana);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    // Actualizar los datos de una campaña
    public function actualizarCampana($idCampana, $codigo, $nombre, $id_zona, $fecha_inicio, $fecha_fin, $estado) {
        $stmt = $this->conexion->prepare("UPDATE Campanas SET CodigoCampana = ?, NombreCampana = ?, IdZona = ?, FechaInicio = ?, FechaFin = ?, Estado = ? WHERE IdCampana = ?");
        $stmt->bind_param("ssisssi", $codigo, $nombre, $id_zona, $fecha_inicio, $fecha_fin, $estado, $idCampana);
        return $stmt->execute();
    }

    public function actualizarEstadoCampana($idCampana, $estado, $estadoActual, $usuarioId) {
        $this->conexion->begin_transaction();

        try {
            $stmt = $this->conexion->prepare(
                "UPDATE Campanas
                 SET Estado = ?
                 WHERE IdCampana = ? AND Estado = ?"
            );
            $stmt->bind_param('sis', $estado, $idCampana, $estadoActual);
            if (!$stmt->execute()) {
                throw new RuntimeException('No se pudo actualizar el estado de la campaña.');
            }

            if ($stmt->affected_rows !== 1) {
                $this->conexion->rollback();
                return 0;
            }

            $historial = $this->conexion->prepare(
                "INSERT INTO historial_estados_campana
                    (IdCampana, CodigoCampana, IdUsuario, EstadoAnterior, EstadoNuevo)
                 SELECT c.IdCampana, c.CodigoCampana, ?, ?, ?
                 FROM Campanas c
                 WHERE c.IdCampana = ?"
            );
            $historial->bind_param(
                'issi',
                $usuarioId,
                $estadoActual,
                $estado,
                $idCampana
            );
            if (!$historial->execute() || $historial->affected_rows !== 1) {
                throw new RuntimeException('No se pudo guardar el historial del cambio de estado.');
            }

            $this->conexion->commit();
            return 1;
        } catch (Throwable $e) {
            $this->conexion->rollback();
            throw $e;
        }
    }

    public function obtenerHistorialEstadosCampanas($municipio = '', $busqueda = '') {
        $stmt = $this->conexion->prepare(
            "SELECT h.IdCampana, h.EstadoAnterior, h.EstadoNuevo, h.FechaCambio,
                    COALESCE(CONCAT(u.Nombre, ' ', u.Apellidos), CONCAT('Usuario #', h.IdUsuario)) AS Usuario
             FROM historial_estados_campana h
             LEFT JOIN Campanas c ON c.IdCampana = h.IdCampana
             LEFT JOIN Zonas z ON z.IdZona = c.IdZona
             LEFT JOIN Usuarios u ON u.IdUsuario = h.IdUsuario
             WHERE h.IdCampana IS NOT NULL
               AND (? = '' OR z.Municipio = ?)
               AND (? = '' OR h.CodigoCampana LIKE CONCAT('%', ?, '%')
                    OR c.NombreCampana LIKE CONCAT('%', ?, '%'))
             ORDER BY h.FechaCambio DESC, h.IdHistorialEstado DESC"
        );
        $stmt->bind_param(
            'sssss',
            $municipio,
            $municipio,
            $busqueda,
            $busqueda,
            $busqueda
        );
        $stmt->execute();
        return $stmt->get_result();
    }

    // Eliminar una campaña por su ID (Precaución: Borrado en cascada según BD)
    public function eliminarCampana($idCampana) {
        $stmt = $this->conexion->prepare("DELETE FROM Campanas WHERE IdCampana = ?");
        $stmt->bind_param("i", $idCampana);
        return $stmt->execute();
    }
}
?>