<?php
class ModelZona {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Registrar una nueva zona
    public function registrarZona($codigo_zona, $nombre_zona, $municipio, $referencias) {
        $stmt = $this->conexion->prepare("INSERT INTO Zonas (CodigoZona, NombreZona, Municipio, Referencias) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $codigo_zona, $nombre_zona, $municipio, $referencias);
        return $stmt->execute();
    }

    // Obtener todas las zonas ordenadas por fecha de registro descendente
    public function obtenerZonas() {
        $query = "SELECT IdZona, CodigoZona, NombreZona, Municipio, Referencias, Estado FROM Zonas ORDER BY FechaRegistro DESC";
        return $this->conexion->query($query);
    }

    // Obtener una zona específica por su ID
    public function obtenerZonaPorId($idZona) {
        $stmt = $this->conexion->prepare("SELECT IdZona, CodigoZona, NombreZona, Municipio, Referencias, Estado FROM Zonas WHERE IdZona = ?");
        $stmt->bind_param("i", $idZona);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    // Actualizar los datos de una zona existente
    public function actualizarZona($idZona, $codigo_zona, $nombre_zona, $municipio, $referencias, $estado) {
        $stmt = $this->conexion->prepare("UPDATE Zonas SET CodigoZona = ?, NombreZona = ?, Municipio = ?, Referencias = ?, Estado = ? WHERE IdZona = ?");
        $stmt->bind_param("sssssi", $codigo_zona, $nombre_zona, $municipio, $referencias, $estado, $idZona);
        return $stmt->execute();
    }

    // Eliminar una zona por su ID
    public function eliminarZona($idZona) {
        $stmt = $this->conexion->prepare("DELETE FROM Zonas WHERE IdZona = ?");
        $stmt->bind_param("i", $idZona);
        return $stmt->execute();
    }
}
?>