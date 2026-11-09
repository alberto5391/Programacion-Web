<?php
class ModelSolicitante {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // 1. Insertar un nuevo solicitante/origen
    public function registrarSolicitante($tipo, $nombre, $contacto, $descripcion, $estado) {
        $stmt = $this->conexion->prepare("INSERT INTO Solicitantes (TipoOrigen, Nombre, Contacto, Descripcion, Estado) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $tipo, $nombre, $contacto, $descripcion, $estado);
        return $stmt->execute();
    }

    // 2. Obtener todos los solicitantes (Catálogo)
    public function obtenerSolicitantes() {
        $query = "SELECT IdSolicitante, TipoOrigen, Nombre, Contacto, Descripcion, Estado FROM Solicitantes ORDER BY IdSolicitante DESC";
        return $this->conexion->query($query);
    }

    // 3. Vincular un solicitante a una campaña existente
    public function vincularCampana($id_campana, $id_solicitante, $fecha_solicitud) {
        // Asumiendo que la tabla Campanas tiene IdSolicitante y FechaSolicitud
        $stmt = $this->conexion->prepare("UPDATE Campanas SET IdSolicitante = ?, FechaSolicitud = ? WHERE IdCampana = ?");
        $stmt->bind_param("isi", $id_solicitante, $fecha_solicitud, $id_campana);
        return $stmt->execute();
    }

    // 4. Obtener campañas activas para el <select>
    public function obtenerCampanasDisponibles() {
        $query = "SELECT IdCampana, CodigoCampana, NombreCampana FROM Campanas WHERE Estado != 'Cancelada'";
        return $this->conexion->query($query);
    }

    // 5. Obtener las campañas vinculadas para la tabla final
    public function obtenerCampanasConSolicitante() {
        $query = "SELECT c.IdCampana, c.CodigoCampana, c.NombreCampana, c.FechaInicio, c.FechaSolicitud, 
                         z.NombreZona, s.TipoOrigen, s.Nombre AS NombreSolicitante
                  FROM Campanas c
                  INNER JOIN Zonas z ON c.IdZona = z.IdZona
                  INNER JOIN Solicitantes s ON c.IdSolicitante = s.IdSolicitante
                  ORDER BY c.IdCampana DESC";
        return $this->conexion->query($query);
    }
}
?>