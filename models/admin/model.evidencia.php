<?php
class ModelEvidencia {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Guardar la ruta de la evidencia en la base de datos
    public function obtenerTiposAccion() {
        return $this->conexion->query(
            "SELECT idTipoAccion, Nombre FROM tipoaccion ORDER BY Nombre"
        );
    }

    public function tipoAccionExiste($id_tipo_accion) {
        $stmt = $this->conexion->prepare(
            "SELECT idTipoAccion FROM tipoaccion WHERE idTipoAccion = ?"
        );
        $stmt->bind_param("i", $id_tipo_accion);
        $stmt->execute();
        return $stmt->get_result()->num_rows === 1;
    }

    public function registrarEvidencia($id_campana, $id_tipo_accion, $ruta_archivo, $tipo_actividad) {
        $stmt = $this->conexion->prepare(
            "INSERT INTO EvidenciasFotograficas (IdCampana, idTipoAccion, TipoActividad, RutaArchivo)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("iiss", $id_campana, $id_tipo_accion, $tipo_actividad, $ruta_archivo);
        return $stmt->execute();
    }

    public function buscarTodasLasEvidencias() {
        return $this->conexion->query(
            "SELECT e.IdEvidencia, e.IdCampana, e.idTipoAccion, e.RutaArchivo AS RutaFoto,
                    e.FechaSubida AS FechaRegistro, e.TipoActividad,
                    ta.Nombre AS NombreTipoAccion, c.CodigoCampana, c.NombreCampana
             FROM EvidenciasFotograficas e
             INNER JOIN Campanas c ON c.IdCampana = e.IdCampana
             INNER JOIN tipoaccion ta ON ta.idTipoAccion = e.idTipoAccion
             ORDER BY e.FechaSubida DESC, e.IdEvidencia DESC"
        );
    }

    public function obtenerEvidenciaPorId($id_evidencia) {
        $stmt = $this->conexion->prepare(
            "SELECT IdEvidencia, IdCampana, idTipoAccion, TipoActividad, RutaArchivo
             FROM EvidenciasFotograficas WHERE IdEvidencia = ?"
        );
        $stmt->bind_param("i", $id_evidencia);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function actualizarEvidencia($id_evidencia, $id_campana, $id_tipo_accion, $tipo_actividad, $ruta_archivo = null) {
        if ($ruta_archivo === null) {
            $stmt = $this->conexion->prepare(
                "UPDATE EvidenciasFotograficas
                 SET IdCampana = ?, idTipoAccion = ?, TipoActividad = ?
                 WHERE IdEvidencia = ?"
            );
            $stmt->bind_param("iisi", $id_campana, $id_tipo_accion, $tipo_actividad, $id_evidencia);
        } else {
            $stmt = $this->conexion->prepare(
                "UPDATE EvidenciasFotograficas
                 SET IdCampana = ?, idTipoAccion = ?, TipoActividad = ?, RutaArchivo = ?
                 WHERE IdEvidencia = ?"
            );
            $stmt->bind_param("iissi", $id_campana, $id_tipo_accion, $tipo_actividad, $ruta_archivo, $id_evidencia);
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