<?php
final class PermissionPolicy
{
    private const ROLE_ACTIONS = [
        'Administrador' => ['consultar', 'registrar', 'modificar', 'eliminar'],
        'Supervisor' => ['consultar', 'registrar', 'modificar'],
        'Tecnico' => ['consultar', 'registrar']
    ];

    private const READ_ONLY_MODULES = [
        'dashboard',
        'super_dashboard',
        'tecnico_dashboard',
        'bitacora',
        'informacion_historica',
        'estadisticas',
        'graficas',
        'graficas_productos',
        'graficas_incidencias',
        'graficas_plagas',
        'graficas_zonas',
        'historial_zonas',
        'reporte_campana',
        'reporte_inspecciones',
        'reporte_aplicaciones',
        'zonasprioritarias'
    ];

    private const MODULE_ALIASES = [
        'registro_campanas' => 'campanas',
        'estado_campana' => 'campanas',
        'coordenadas_gps' => 'zonas',
        'inspeccion_diagnostico' => 'accionescampana',
        'tratamientos' => 'accionescampana',
        'seguimiento_posterior' => 'accionescampana',
        'responsables' => 'campanatecnicos',
        'zonas_prioritarias' => 'zonasprioritarias'
    ];

    private const RECORD_ID_FIELDS = [
        'usuarios' => 'id_usuario',
        'zonas' => 'id_zona',
        'plagas' => 'id_plaga',
        'productos' => 'id_producto',
        'tecnicos' => 'id_tecnico',
        'campanas' => 'id_campana',
        'solicitantes' => 'id_solicitante',
        'evidencias' => 'id_evidencia',
        'accionescampana' => 'id_accion',
        'incidencias' => 'id_incidencia',
        'zonasprioritarias' => 'id_zona_prioritaria'
    ];

    private const UNMANAGED_MODULES = [
        'home',
        'login',
        'logout',
        'notificaciones',
        'notificacion_exportar',
        'notificacion_modificar',
        'notificacion_desactivar',
        'recuperar_contraseña'
    ];

    public static function governs($module)
    {
        return is_string($module)
            && !in_array($module, self::UNMANAGED_MODULES, true);
    }

    public static function roleActions()
    {
        return self::ROLE_ACTIONS;
    }

    public static function can($role, $module, $action)
    {
        if (!self::governs($module)) {
            return true;
        }

        $module = self::canonicalModule($module);

        if (in_array($module, ['usuarios', 'control_acceso'], true)) {
            return $role === 'Administrador';
        }

        if (in_array($module, self::READ_ONLY_MODULES, true)) {
            return $action === 'consultar';
        }

        return isset(self::ROLE_ACTIONS[$role])
            && in_array($action, self::ROLE_ACTIONS[$role], true);
    }

    public static function requestedAction($module, $method, array $get, array $post)
    {
        $canonicalModule = self::canonicalModule($module);
        if (in_array($canonicalModule, self::READ_ONLY_MODULES, true)) {
            return 'consultar';
        }

        $accion = $post['accion'] ?? $get['accion'] ?? null;
        if (is_string($accion) && $accion === 'eliminar') {
            return 'eliminar';
        }

        if ($module === 'estado_campana' && $method === 'POST') {
            return 'modificar';
        }

        if ($method === 'GET') {
            return is_string($accion) && $accion === 'editar'
                ? 'modificar'
                : 'consultar';
        }

        if ($method !== 'POST') {
            return 'consultar';
        }

        if ($module === 'coordenadas_gps' && $accion === 'actualizar_coordenadas') {
            return 'registrar';
        }

        if (
            is_string($accion)
            && in_array($accion, ['modificar', 'actualizar', 'modificar_responsable'], true)
        ) {
            return 'modificar';
        }

        $recordIdField = self::RECORD_ID_FIELDS[$canonicalModule] ?? null;
        if ($recordIdField !== null && isset($post[$recordIdField]) && $post[$recordIdField] !== '') {
            return 'modificar';
        }

        return 'registrar';
    }

    private static function canonicalModule($module)
    {
        return self::MODULE_ALIASES[$module] ?? $module;
    }
}

function appCan($module, $action)
{
    return PermissionPolicy::can(
        $_SESSION['rol'] ?? '',
        $module,
        $action
    );
}
