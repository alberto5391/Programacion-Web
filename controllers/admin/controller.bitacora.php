<?php
// 1. Validar sesión integrada (El session_start ya está en index.php)
if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['rol'], ['Administrador', 'Supervisor', 'Tecnico'], true)) {
    header("Location: index.php?modulo=login");
    exit();
}

require_once MODEL_PATH . 'admin/model.bitacora.php';
$modeloBitacora = new ModelBitacora($conexion);

$usuarioId = filter_input(INPUT_GET, 'usuario', FILTER_VALIDATE_INT);
$moduloId = filter_input(INPUT_GET, 'modulo_filtro', FILTER_VALIDATE_INT);
$accion = isset($_GET['accion']) && is_string($_GET['accion']) ? trim($_GET['accion']) : '';
$fechaInicio = isset($_GET['fechaInicio']) && is_string($_GET['fechaInicio']) ? $_GET['fechaInicio'] : '';
$fechaFin = isset($_GET['fechaFin']) && is_string($_GET['fechaFin']) ? $_GET['fechaFin'] : '';
$usuarios = $modeloBitacora->obtenerUsuarios()->fetch_all(MYSQLI_ASSOC);
$modulos = $modeloBitacora->obtenerModulos()->fetch_all(MYSQLI_ASSOC);
$tiposAccion = ModelBitacora::tiposAccion();
foreach ($modeloBitacora->obtenerTiposAccion()->fetch_all(MYSQLI_ASSOC) as $tipoExistente) {
    if (
        isset($tipoExistente['TipoAccion'])
        && is_string($tipoExistente['TipoAccion'])
        && $tipoExistente['TipoAccion'] !== ''
        && !in_array($tipoExistente['TipoAccion'], $tiposAccion, true)
    ) {
        $tiposAccion[] = $tipoExistente['TipoAccion'];
    }
}
$fechaValida = static function ($fecha) {
    $date = DateTime::createFromFormat('!Y-m-d', $fecha);
    return $date && $date->format('Y-m-d') === $fecha;
};
if (!$fechaValida($fechaInicio)) {
    $fechaInicio = '';
}
if (!$fechaValida($fechaFin)) {
    $fechaFin = '';
}
if ($fechaInicio !== '' && $fechaFin !== '' && $fechaInicio > $fechaFin) {
    $fechaFin = '';
}
$usuarioId = $usuarioId !== false && $usuarioId !== null && $usuarioId > 0 ? $usuarioId : 0;
$moduloId = $moduloId !== false && $moduloId !== null && $moduloId > 0 ? $moduloId : 0;
if (!in_array($accion, $tiposAccion, true)) {
    $accion = '';
}
$registros = $modeloBitacora->buscar($usuarioId, $moduloId, $accion, $fechaInicio, $fechaFin);

if (isset($_GET['exportar']) && $_GET['exportar'] === 'csv') {
    $nombreArchivo = 'bitacora-' . date('Ymd-His') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store, no-cache, must-revalidate');

    $salida = fopen('php://output', 'wb');
    fwrite($salida, "\xEF\xBB\xBF");
    fputcsv($salida, ['ID', 'Usuario', 'Módulo', 'Acción', 'Inicio', 'Fin'], ';', '"', '\\');
    while ($registro = $registros->fetch_assoc()) {
        $fila = [
            $registro['IdBitacora'],
            $registro['Usuario'],
            $registro['NombreModulo'],
            $registro['TipoAccion'],
            $registro['FechaInicioBit'],
            $registro['FechaFinBit'] ?? ''
        ];
        $fila = array_map(static function ($valor) {
            $valor = (string) $valor;
            return preg_match('/^\s*[=+\-@]/u', $valor) === 1 ? "'" . $valor : $valor;
        }, $fila);
        fputcsv($salida, $fila, ';', '"', '\\');
    }
    fclose($salida);
    exit();
}

$filtrosConsulta = [
    'modulo' => 'bitacora',
    'usuario' => $usuarioId,
    'modulo_filtro' => $moduloId,
    'accion' => $accion,
    'fechaInicio' => $fechaInicio,
    'fechaFin' => $fechaFin
];

if (isset($_GET['reporte']) && $_GET['reporte'] === '1') {
    $usuarioSeleccionado = 'Todos los usuarios';
    foreach ($usuarios as $usuario) {
        if ((int) $usuario['IdUsuario'] === $usuarioId) {
            $usuarioSeleccionado = $usuario['Nombre'] . ' ' . $usuario['Apellidos'];
            break;
        }
    }

    $moduloSeleccionado = 'Todos los módulos';
    foreach ($modulos as $modulo) {
        if ((int) $modulo['IdModulo'] === $moduloId) {
            $moduloSeleccionado = $modulo['NombreModulo'];
            break;
        }
    }

    $registros = $registros->fetch_all(MYSQLI_ASSOC);
    $urlVolver = BASE_URL . 'index.php?' . http_build_query($filtrosConsulta);
    require_once VIEW_PATH . 'admin/bitacora_reporte.php';
    exit();
}

require_once VIEW_PATH . 'admin/bitacora.php';
?>