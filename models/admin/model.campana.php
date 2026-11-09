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
        return $stmt->execute();
    }

    // Obtener todas las campañas unidas con su zona correspondiente
    public function obtenerCampanas() {
        $query = "SELECT c.IdCampana, c.CodigoCampana, c.NombreCampana, c.FechaInicio, c.FechaFin, c.Estado, 
                         z.CodigoZona, z.NombreZona 
                  FROM Campanas c
                  INNER JOIN Zonas z ON c.IdZona = z.IdZona
                  ORDER BY c.IdCampana DESC";
        return $this->conexion->query($query);
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

    // Eliminar una campaña por su ID (Precaución: Borrado en cascada según BD)
    public function eliminarCampana($idCampana) {
        $stmt = $this->conexion->prepare("DELETE FROM Campanas WHERE IdCampana = ?");
        $stmt->bind_param("i", $idCampana);
        return $stmt->execute();
    }
}
?>