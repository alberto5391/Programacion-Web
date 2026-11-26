<?php
class ModelZona {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public static function coordenadasValidas($latitud, $longitud) {
        return is_numeric($latitud)
            && is_numeric($longitud)
            && (float)$latitud >= -90
            && (float)$latitud <= 90
            && (float)$longitud >= -180
            && (float)$longitud <= 180;
    }

    // Registrar una nueva zona
    public function registrarZona($codigo_zona, $nombre_zona, $municipio, $referencias, $latitud, $longitud) {
        $stmt = $this->conexion->prepare("INSERT INTO Zonas (CodigoZona, NombreZona, Municipio, Referencias, Latitud, Longitud) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssdd", $codigo_zona, $nombre_zona, $municipio, $referencias, $latitud, $longitud);
        return $stmt->execute();
    }

    // Obtener todas las zonas ordenadas por fecha de registro descendente
    public function obtenerZonas() {
        $query = "SELECT IdZona, CodigoZona, NombreZona, Municipio, Referencias, Latitud, Longitud, Estado FROM Zonas ORDER BY FechaRegistro DESC";
        return $this->conexion->query($query);
    }

    public function obtenerZonasActivas() {
        return $this->conexion->query("SELECT IdZona, CodigoZona, NombreZona, Municipio, Referencias, Latitud, Longitud, Estado FROM Zonas WHERE Estado = 'Activa' ORDER BY NombreZona");
    }

    // Obtener una zona específica por su ID
    public function obtenerZonaPorId($idZona) {
        $stmt = $this->conexion->prepare("SELECT IdZona, CodigoZona, NombreZona, Municipio, Referencias, Latitud, Longitud, Estado FROM Zonas WHERE IdZona = ?");
        $stmt->bind_param("i", $idZona);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    // Actualizar los datos de una zona existente
    public function actualizarZona($idZona, $codigo_zona, $nombre_zona, $municipio, $referencias, $latitud, $longitud, $estado) {
        $stmt = $this->conexion->prepare("UPDATE Zonas SET CodigoZona = ?, NombreZona = ?, Municipio = ?, Referencias = ?, Latitud = ?, Longitud = ?, Estado = ? WHERE IdZona = ?");
        $stmt->bind_param("ssssddsi", $codigo_zona, $nombre_zona, $municipio, $referencias, $latitud, $longitud, $estado, $idZona);
        return $stmt->execute();
    }

    public function actualizarCoordenadas($idZona, $latitud, $longitud) {
        $stmt = $this->conexion->prepare("UPDATE Zonas SET Latitud = ?, Longitud = ? WHERE IdZona = ?");
        $stmt->bind_param("ddi", $latitud, $longitud, $idZona);
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