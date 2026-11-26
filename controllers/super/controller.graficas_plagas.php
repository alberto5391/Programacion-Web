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
$hoy = new DateTimeImmutable('today');
$fechaInicioPredeterminada = $hoy->modify('first day of this month')->modify('-11 months')->format('Y-m-d');
$fechaFinPredeterminada = $hoy->format('Y-m-d');
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
    $mensajes[] = 'La fecha indicada no es válida; se mostraron los últimos 12 meses.';
    $fechaInicio = $fechaInicioPredeterminada;
    $fechaFin = $fechaFinPredeterminada;
} else {
    $fechaInicio = $fechaInicio !== '' ? $fechaInicio : $fechaInicioPredeterminada;
    $fechaFin = $fechaFin !== '' ? $fechaFin : $fechaFinPredeterminada;
}

if ($fechaInicio > $fechaFin) {
    $mensajes[] = 'El rango de fechas no es válido; se mostraron los últimos 12 meses.';
    $fechaInicio = $fechaInicioPredeterminada;
    $fechaFin = $fechaFinPredeterminada;
}

$municipios = $modeloEstadistica->obtenerMunicipios();
$municipiosValidos = array_column($municipios, 'Municipio');
if ($municipio !== '' && !in_array($municipio, $municipiosValidos, true)) {
    $mensajes[] = 'El municipio seleccionado no es válido; se mostrarán todos los municipios.';
    $municipio = '';
}

$plagasFrecuentes = $modeloEstadistica->obtenerPlagasFrecuentes($fechaInicio, $fechaFin, $municipio);
$plagasPorPeriodo = $modeloEstadistica->obtenerPlagasPorPeriodo($fechaInicio, $fechaFin, $municipio);
$resumenPlagas = $modeloEstadistica->obtenerResumenPlagas($fechaInicio, $fechaFin, $municipio);
$totalIdentificaciones = (int) ($resumenPlagas['TotalIdentificaciones'] ?? 0);
$totalEspecies = (int) ($resumenPlagas['Especies'] ?? 0);
$altasOCriticas = (int) ($resumenPlagas['AltasOCriticas'] ?? 0);

$identificacionesPorPeriodo = [];
foreach ($plagasPorPeriodo as $periodo) {
    $identificacionesPorPeriodo[$periodo['Periodo']] = (int) $periodo['Identificaciones'];
}

$meses = [
    1 => 'Enero',
    2 => 'Febrero',
    3 => 'Marzo',
    4 => 'Abril',
    5 => 'Mayo',
    6 => 'Junio',
    7 => 'Julio',
    8 => 'Agosto',
    9 => 'Septiembre',
    10 => 'Octubre',
    11 => 'Noviembre',
    12 => 'Diciembre',
];
$labelsPeriodo = [];
$datosPeriodo = [];
$inicioMes = new DateTimeImmutable(substr($fechaInicio, 0, 7) . '-01');
$finMes = new DateTimeImmutable(substr($fechaFin, 0, 7) . '-01');
for ($mes = $inicioMes; $mes <= $finMes; $mes = $mes->modify('+1 month')) {
    $periodo = $mes->format('Y-m');
    $labelsPeriodo[] = $meses[(int) $mes->format('n')] . ' ' . $mes->format('Y');
    $datosPeriodo[] = $identificacionesPorPeriodo[$periodo] ?? 0;
}

$labelsPlagas = array_column($plagasFrecuentes, 'Plaga');
$datosPlagas = array_map(
    static function ($plaga) {
        return (int) $plaga['Identificaciones'];
    },
    $plagasFrecuentes
);
$opcionesJson = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
$labelsPeriodoJson = json_encode($labelsPeriodo, $opcionesJson);
$datosPeriodoJson = json_encode($datosPeriodo, $opcionesJson);
$labelsPlagasJson = json_encode($labelsPlagas, $opcionesJson);
$datosPlagasJson = json_encode($datosPlagas, $opcionesJson);

require_once VIEW_PATH . 'super/graficas_plagas.php';