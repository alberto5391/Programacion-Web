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
$metrica = isset($_GET['metrica']) && is_string($_GET['metrica'])
    ? trim($_GET['metrica'])
    : 'identificaciones';

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

$metricas = [
    'identificaciones' => [
        'campo' => 'TotalIdentificaciones',
        'etiqueta' => 'Identificaciones de plagas',
    ],
    'acciones' => [
        'campo' => 'TotalAcciones',
        'etiqueta' => 'Acciones realizadas',
    ],
    'incidencias' => [
        'campo' => 'TotalIncidencias',
        'etiqueta' => 'Incidencias registradas',
    ],
];
if (!isset($metricas[$metrica])) {
    $metrica = 'identificaciones';
}
$campoMetrica = $metricas[$metrica]['campo'];
$etiquetaMetrica = $metricas[$metrica]['etiqueta'];

$zonas = $modeloEstadistica->obtenerEstadisticasZonas($fechaInicio, $fechaFin, $municipio);
$totales = [
    'TotalIdentificaciones' => 0,
    'TotalAcciones' => 0,
    'TotalIncidencias' => 0,
];
$zonasConActividad = 0;
foreach ($zonas as $zona) {
    foreach ($totales as $campo => $total) {
        $totales[$campo] += (int) $zona[$campo];
    }

    if (
        (int) $zona['TotalIdentificaciones'] > 0
        || (int) $zona['TotalAcciones'] > 0
        || (int) $zona['TotalIncidencias'] > 0
    ) {
        $zonasConActividad++;
    }
}

usort($zonas, static function ($zonaA, $zonaB) use ($campoMetrica) {
    $comparacion = (int) $zonaB[$campoMetrica] <=> (int) $zonaA[$campoMetrica];
    return $comparacion !== 0
        ? $comparacion
        : strcasecmp($zonaA['NombreZona'], $zonaB['NombreZona']);
});

$zonasConDatos = array_values(array_filter($zonas, static function ($zona) use ($campoMetrica) {
    return (int) $zona[$campoMetrica] > 0;
}));
$zonasDestacadas = array_slice($zonasConDatos, 0, 10);
$labelsGrafica = array_map(static function ($zona) {
    return $zona['NombreZona'] . ' - ' . $zona['Municipio'];
}, $zonasDestacadas);
$datosGrafica = array_map(static function ($zona) use ($campoMetrica) {
    return (int) $zona[$campoMetrica];
}, $zonasDestacadas);
$opcionesJson = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
$labelsJson = json_encode($labelsGrafica, $opcionesJson);
$datosJson = json_encode($datosGrafica, $opcionesJson);

require_once VIEW_PATH . 'super/graficas_zonas.php';