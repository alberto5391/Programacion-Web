<?php
class ModelEvidencia {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Guardar la ruta de la evidencia en la base de datos
    public function registrarEvidencia($id_campana, $ruta_archivo, $tipo_actividad, $id_accion = null) {
        $stmt = $this->conexion->prepare("INSERT INTO EvidenciasFotograficas (IdCampana, IdAccion, TipoActividad, RutaArchivo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $id_campana, $id_accion, $tipo_actividad, $ruta_archivo);
        return $stmt->execute();
    }

    // Buscar evidencias por ID de campaña
    public function buscarEvidenciasPorCampana($id_campana) {
        $stmt = $this->conexion->prepare("SELECT IdEvidencia, RutaArchivo AS RutaFoto, FechaSubida AS FechaRegistro, TipoActividad FROM EvidenciasFotograficas WHERE IdCampana = ? ORDER BY FechaSubida DESC");
        $stmt->bind_param("i", $id_campana);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function obtenerEvidenciaPorId($id_evidencia) {
        $stmt = $this->conexion->prepare("SELECT IdEvidencia, IdCampana, TipoActividad, RutaArchivo FROM EvidenciasFotograficas WHERE IdEvidencia = ?");
        $stmt->bind_param("i", $id_evidencia);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function actualizarTipoActividad($id_evidencia, $tipo_actividad) {
        $stmt = $this->conexion->prepare("UPDATE EvidenciasFotograficas SET TipoActividad = ? WHERE IdEvidencia = ?");
        $stmt->bind_param("si", $tipo_actividad, $id_evidencia);
        return $stmt->execute();
    }

    public function eliminarEvidencia($id_evidencia) {
        $stmt = $this->conexion->prepare("DELETE FROM EvidenciasFotograficas WHERE IdEvidencia = ?");
        $stmt->bind_param("i", $id_evidencia);
        return $stmt->execute() && $stmt->affected_rows === 1;
    }

    public function obtenerRutasPorCampana($id_campana) {
        $stmt = $this->conexion->prepare("SELECT RutaArchivo FROM EvidenciasFotograficas WHERE IdCampana = ?");
        $stmt->bind_param("i", $id_campana);
        $stmt->execute();
        return $stmt->get_result();
    }
}
?>