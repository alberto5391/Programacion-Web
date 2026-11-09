<?php
class ModelPlaga {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Registrar una nueva plaga
    public function registrarPlaga($codigo, $nombre_comun, $nombre_cientifico, $descripcion, $tratamiento) {
        $stmt = $this->conexion->prepare("INSERT INTO Plagas (CodigoPlaga, NombreComun, NombreCientifico, Descripcion, TratamientoRecomendado) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $codigo, $nombre_comun, $nombre_cientifico, $descripcion, $tratamiento);
        return $stmt->execute();
    }

    // Obtener todas las plagas para mostrarlas en la tabla
    public function obtenerPlagas() {
        $query = "SELECT IdPlaga, CodigoPlaga, NombreComun, NombreCientifico, Descripcion, Estado FROM Plagas ORDER BY IdPlaga DESC";
        return $this->conexion->query($query);
    }

    // Obtener una plaga específica por su ID
    public function obtenerPlagaPorId($idPlaga) {
        $stmt = $this->conexion->prepare("SELECT IdPlaga, CodigoPlaga, NombreComun, NombreCientifico, Descripcion, TratamientoRecomendado, Estado FROM Plagas WHERE IdPlaga = ?");
        $stmt->bind_param("i", $idPlaga);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    // Actualizar los datos de una plaga existente
    public function actualizarPlaga($idPlaga, $codigo, $nombre_comun, $nombre_cientifico, $descripcion, $tratamiento, $estado) {
        $stmt = $this->conexion->prepare("UPDATE Plagas SET CodigoPlaga = ?, NombreComun = ?, NombreCientifico = ?, Descripcion = ?, TratamientoRecomendado = ?, Estado = ? WHERE IdPlaga = ?");
        $stmt->bind_param("ssssssi", $codigo, $nombre_comun, $nombre_cientifico, $descripcion, $tratamiento, $estado, $idPlaga);
        return $stmt->execute();
    }

    // Eliminar una plaga por su ID
    public function eliminarPlaga($idPlaga) {
        $stmt = $this->conexion->prepare("DELETE FROM Plagas WHERE IdPlaga = ?");
        $stmt->bind_param("i", $idPlaga);
        return $stmt->execute();
    }
}
?>