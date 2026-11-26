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

    public function buscarTodasLasEvidencias() {
        return $this->conexion->query(
            "SELECT e.IdEvidencia, e.IdCampana, e.RutaArchivo AS RutaFoto, e.FechaSubida AS FechaRegistro,
                    e.TipoActividad, c.CodigoCampana, c.NombreCampana
             FROM EvidenciasFotograficas e
             INNER JOIN Campanas c ON c.IdCampana = e.IdCampana
             ORDER BY e.FechaSubida DESC, e.IdEvidencia DESC"
        );
    }

    public function obtenerEvidenciaPorId($id_evidencia) {
        $stmt = $this->conexion->prepare("SELECT IdEvidencia, IdCampana, TipoActividad, RutaArchivo FROM EvidenciasFotograficas WHERE IdEvidencia = ?");
        $stmt->bind_param("i", $id_evidencia);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function actualizarEvidencia($id_evidencia, $id_campana, $tipo_actividad, $ruta_archivo = null) {
        if ($ruta_archivo === null) {
            $stmt = $this->conexion->prepare(
                "UPDATE EvidenciasFotograficas SET IdCampana = ?, TipoActividad = ? WHERE IdEvidencia = ?"
            );
            $stmt->bind_param("isi", $id_campana, $tipo_actividad, $id_evidencia);
        } else {
            $stmt = $this->conexion->prepare(
                "UPDATE EvidenciasFotograficas SET IdCampana = ?, TipoActividad = ?, RutaArchivo = ? WHERE IdEvidencia = ?"
            );
            $stmt->bind_param("issi", $id_campana, $tipo_actividad, $ruta_archivo, $id_evidencia);
        }
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