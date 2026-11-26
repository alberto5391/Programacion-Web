<?php

class ModelBitacora
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerUsuarios()
    {
        return $this->conexion->query(
            "SELECT IdUsuario, Nombre, Apellidos FROM Usuarios ORDER BY Nombre, Apellidos"
        );
    }

    public function obtenerModulos()
    {
        return $this->conexion->query(
            "SELECT IdModulo, NombreModulo FROM Modulos WHERE EstadoModulo = 'Activo' ORDER BY NombreModulo"
        );
    }

    public function buscar($usuarioId, $moduloId, $accion, $fechaInicio, $fechaFin)
    {
        $sql = "SELECT b.IdBitacora, b.FechaInicioBit, b.FechaFinBit, b.TipoAccion,
                       CONCAT(u.Nombre, ' ', u.Apellidos) AS Usuario,
                       m.NombreModulo
                FROM Bitacora b
                INNER JOIN Usuarios u ON u.IdUsuario = b.IdUsuario
                INNER JOIN Modulos m ON m.IdModulo = b.IdModulo
                WHERE (? = 0 OR b.IdUsuario = ?)
                  AND (? = 0 OR b.IdModulo = ?)
                  AND (? = '' OR b.TipoAccion = ?)
                  AND (? = '' OR DATE(b.FechaInicioBit) >= ?)
                  AND (? = '' OR DATE(b.FechaInicioBit) <= ?)
                ORDER BY b.FechaInicioBit DESC, b.IdBitacora DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param(
            "iiiissssss",
            $usuarioId,
            $usuarioId,
            $moduloId,
            $moduloId,
            $accion,
            $accion,
            $fechaInicio,
            $fechaInicio,
            $fechaFin,
            $fechaFin
        );
        $stmt->execute();
        return $stmt->get_result();
    }
}
