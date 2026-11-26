<?php
class ModelSolicitante {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // 1. Insertar un nuevo solicitante/origen
    public function registrarSolicitante($tipo, $nombre, $contacto, $descripcion, $estado) {
        $stmt = $this->conexion->prepare("INSERT INTO Solicitantes (TipoOrigen, NombreSolicitante, TelefonoSolicitante, DescripcionSolicitud, EstadoSolicitud) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $tipo, $nombre, $contacto, $descripcion, $estado);
        return $stmt->execute();
    }

    // 2. Obtener todos los solicitantes (Catálogo)
    public function obtenerSolicitantes() {
        $query = "SELECT IdSolicitante, TipoOrigen, NombreSolicitante AS Nombre, TelefonoSolicitante AS Contacto, DescripcionSolicitud AS Descripcion, EstadoSolicitud AS Estado FROM Solicitantes ORDER BY IdSolicitante DESC";
        return $this->conexion->query($query);
    }

    public function obtenerSolicitantesPorCampana($id_campana) {
        $stmt = $this->conexion->prepare("SELECT s.IdSolicitante, s.TipoOrigen, s.NombreSolicitante, oc.FechaSolicitud FROM OrigenCampana oc INNER JOIN Solicitantes s ON s.IdSolicitante = oc.IdSolicitante WHERE oc.IdCampana = ? ORDER BY s.NombreSolicitante");
        $stmt->bind_param("i", $id_campana);
        $stmt->execute();
        return $stmt->get_result();
    }

    // 3. Vincular un solicitante a una campaña existente
    public function vincularCampana($id_campana, $id_solicitante, $fecha_solicitud) {
        $stmt = $this->conexion->prepare("INSERT INTO OrigenCampana (IdCampana, IdSolicitante, FechaSolicitud) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id_campana, $id_solicitante, $fecha_solicitud);
        return $stmt->execute();
    }

    public function reemplazarSolicitantesCampana($id_campana, array $solicitantes) {
        $stmtEliminar = $this->conexion->prepare("DELETE FROM OrigenCampana WHERE IdCampana = ?");
        $stmtEliminar->bind_param("i", $id_campana);
        if (!$stmtEliminar->execute()) {
            return false;
        }

        foreach ($solicitantes as $id_solicitante => $fecha_solicitud) {
            if (!$this->vincularCampana($id_campana, (int)$id_solicitante, $fecha_solicitud)) {
                return false;
            }
        }
        return true;
    }

    // 4. Obtener campañas activas para el <select>
    public function obtenerCampanasDisponibles() {
        $query = "SELECT IdCampana, CodigoCampana, NombreCampana FROM Campanas WHERE Estado != 'Cancelada'";
        return $this->conexion->query($query);
    }

    // 5. Obtener las campañas vinculadas para la tabla final
    public function obtenerCampanasConSolicitante() {
        $query = "SELECT c.IdCampana, c.CodigoCampana, c.NombreCampana, c.FechaInicio, oc.FechaSolicitud,
                         z.NombreZona, s.TipoOrigen, s.NombreSolicitante
                  FROM Campanas c
                  INNER JOIN Zonas z ON c.IdZona = z.IdZona
                  INNER JOIN OrigenCampana oc ON c.IdCampana = oc.IdCampana
                  INNER JOIN Solicitantes s ON oc.IdSolicitante = s.IdSolicitante
                  ORDER BY c.IdCampana DESC";
        return $this->conexion->query($query);
    }
}
?>