<?php
if (
    !isset($_SESSION['id_usuario'])
    || !in_array($_SESSION['rol'] ?? '', ['Administrador', 'Supervisor', 'Tecnico'], true)
) {
    header("Location: index.php?modulo=login");
    exit();
}

require_once MODEL_PATH . 'admin/model.estadistica.php';

$modeloEstadistica = new ModelEstadistica($conexion);
$mensajes = [];
$validarFecha = static function ($fecha) {
    if ($fecha === '') {
        return true;
    }

    $fechaValida = DateTime::createFromFormat('!Y-m-d', $fecha);
    return $fechaValida && $fechaValida->format('Y-m-d') === $fecha;
};

$fechaInicio = isset($_GET['desde']) && is_string($_GET['desde']) ? trim($_GET['desde']) : '';
$fechaFin = isset($_GET['hasta']) && is_string($_GET['hasta']) ? trim($_GET['hasta']) : '';
$municipio = isset($_GET['municipio']) && is_string($_GET['municipio'])
    ? trim($_GET['municipio'])
    : '';

if (!$validarFecha($fechaInicio) || !$validarFecha($fechaFin)) {
    $mensajes[] = 'La fecha indicada no es válida; se ignoró el filtro de fechas.';
    $fechaInicio = '';
    $fechaFin = '';
}
if ($fechaInicio !== '' && $fechaFin !== '' && $fechaInicio > $fechaFin) {
    $mensajes[] = 'El rango de fechas no es válido; se mostrarán todos los periodos.';
    $fechaInicio = '';
    $fechaFin = '';
}

$municipios = $modeloEstadistica->obtenerMunicipios();
$municipiosValidos = array_column($municipios, 'Municipio');
if ($municipio !== '' && !in_array($municipio, $municipiosValidos, true)) {
    $mensajes[] = 'El municipio seleccionado no es válido; se mostrarán todos los municipios.';
    $municipio = '';
}

$fechaInicioConsulta = $fechaInicio !== '' ? $fechaInicio : '1000-01-01';
$fechaFinConsulta = $fechaFin !== '' ? $fechaFin : '9999-12-30';
$incidenciasPorTipo = $modeloEstadistica->obtenerIncidenciasPorTipo(
    $fechaInicioConsulta,
    $fechaFinConsulta,
    $municipio
);
$totalIncidencias = array_sum(array_map(
    static function ($incidencia) {
        return (int) $incidencia['TotalIncidencias'];
    },
    $incidenciasPorTipo
));
$labelsGrafica = array_column($incidenciasPorTipo, 'Tipo');
$cantidadesGrafica = array_map(
    static function ($incidencia) {
        return (int) $incidencia['TotalIncidencias'];
    },
    $incidenciasPorTipo
);
$opcionesJson = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
$labelsJson = json_encode($labelsGrafica, $opcionesJson);
$cantidadesJson = json_encode($cantidadesGrafica, $opcionesJson);

require_once VIEW_PATH . 'super/graficas_incidencias.php';
?>