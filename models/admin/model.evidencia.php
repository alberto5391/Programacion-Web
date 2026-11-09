<?php
class ModelEvidencia {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Guardar la ruta de la evidencia en la base de datos
    public function registrarEvidencia($id_campana, $ruta_foto) {
        // Asumiendo que tu tabla se llama Evidencias y tiene estos campos
        $stmt = $this->conexion->prepare("INSERT INTO Evidencias (IdCampana, RutaFoto, FechaRegistro) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $id_campana, $ruta_foto);
        return $stmt->execute();
    }

    // Buscar evidencias por ID de campaña
    public function buscarEvidenciasPorCampana($id_campana) {
        $stmt = $this->conexion->prepare("SELECT IdEvidencia, RutaFoto, FechaRegistro FROM Evidencias WHERE IdCampana = ? ORDER BY FechaRegistro DESC");
        $stmt->bind_param("s", $id_campana);
        $stmt->execute();
        return $stmt->get_result();
    }
}
?>