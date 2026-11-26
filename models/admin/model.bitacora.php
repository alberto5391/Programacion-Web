<?php

class ModelBitacora
{
    private const MODULOS_AUDITABLES = [
        'dashboard' => 'Dashboard General',
        'super_dashboard' => 'Dashboard General',
        'tecnico_dashboard' => 'Dashboard General',
        'usuarios' => 'Gestión de Usuarios',
        'control_acceso' => 'Roles y Permisos',
        'zonas' => 'Gestión de Zonas',
        'coordenadas_gps' => 'Gestión de Zonas',
        'zonas_prioritarias' => 'Zonas Prioritarias',
        'plagas' => 'Gestión de Plagas',
        'productos' => 'Catálogo de Productos',
        'tecnicos' => 'Gestión de Técnicos',
        'evidencias' => 'Evidencias Fotográficas',
        'solicitantes' => 'Clientes y Predios',
        'bitacora' => 'Bitácora del Sistema',
        'informacion_historica' => 'Campañas de Control',
        'estadisticas' => 'Reportes Estadísticos',
        'graficas' => 'Reportes Estadísticos',
        'graficas_productos' => 'Reportes Estadísticos',
        'graficas_incidencias' => 'Reportes Estadísticos',
        'graficas_plagas' => 'Reportes Estadísticos',
        'graficas_zonas' => 'Reportes Estadísticos',
        'campanas' => 'Campañas de Control',
        'registro_campanas' => 'Campañas de Control',
        'estado_campana' => 'Campañas de Control',
        'reporte_campana' => 'Campañas de Control',
        'historial_zonas' => 'Mapa Georreferenciado',
        'reporte_inspecciones' => 'Inspecciones en Campo',
        'inspeccion_diagnostico' => 'Diagnósticos Técnicos',
        'reporte_aplicaciones' => 'Aplicaciones de Control',
        'tratamientos' => 'Aplicaciones de Control',
        'seguimiento_posterior' => 'Seguimiento y Monitoreo',
        'responsables' => 'Agenda de Técnicos',
        'incidencias' => 'Historial de Incidencias'
    ];

    private const TIPOS_ACCION = [
        'Consulta',
        'Creación',
        'Modificación',
        'Desactivación',
        'Eliminación',
        'Exportación',
        'Generación de reporte'
    ];

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

    public function obtenerTiposAccion()
    {
        return $this->conexion->query(
            "SELECT DISTINCT TipoAccion FROM Bitacora ORDER BY TipoAccion"
        );
    }

    public static function soportaModulo($modulo)
    {
        return isset(self::MODULOS_AUDITABLES[$modulo]);
    }

    public static function tiposAccion()
    {
        return self::TIPOS_ACCION;
    }

    public static function tipoAccionParaSolicitud($modulo, $metodo, array $get, array $post)
    {
        if (isset($get['exportar']) && $get['exportar'] === 'csv') {
            return 'Exportación';
        }
        if (isset($get['reporte']) && $get['reporte'] === '1') {
            return 'Generación de reporte';
        }
        if ($metodo === 'GET') {
            return 'Consulta';
        }

        $accionSolicitada = $post['accion'] ?? '';
        if (is_string($accionSolicitada)) {
            $accionNormalizada = strtolower($accionSolicitada);
            if ($accionNormalizada === 'eliminar') {
                return 'Eliminación';
            }
            if (strpos($accionNormalizada, 'desactiv') !== false) {
                return 'Desactivación';
            }
            if (
                strpos($accionNormalizada, 'actualizar_') === 0
                || strpos($accionNormalizada, 'modificar_') === 0
            ) {
                return 'Modificación';
            }
        }

        $accionPermiso = PermissionPolicy::requestedAction(
            $modulo,
            $metodo,
            $get,
            $post
        );
        if ($accionPermiso === 'eliminar') {
            return 'Eliminación';
        }
        if ($accionPermiso === 'modificar') {
            return 'Modificación';
        }

        return 'Creación';
    }

    public function registrarSolicitud($usuarioId, $modulo, $tipoAccion, $fechaInicio, $fechaFin)
    {
        $nombreModulo = self::MODULOS_AUDITABLES[$modulo] ?? null;
        if ($nombreModulo === null) {
            throw new InvalidArgumentException('El módulo no tiene una correspondencia de auditoría.');
        }

        $sql = "INSERT INTO Bitacora (IdUsuario, IdModulo, TipoAccion, FechaInicioBit, FechaFinBit)
                SELECT ?, IdModulo, ?, ?, ?
                FROM Modulos
                WHERE NombreModulo = ? AND EstadoModulo = 'Activo'
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param(
            'issss',
            $usuarioId,
            $tipoAccion,
            $fechaInicio,
            $fechaFin,
            $nombreModulo
        );
        $stmt->execute();

        if ($stmt->affected_rows !== 1) {
            throw new RuntimeException('No se pudo asociar la acción auditada con un módulo activo.');
        }

        return true;
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
