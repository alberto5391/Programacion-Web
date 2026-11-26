<?php
if (
    !isset($_SESSION['id_usuario'])
    || !in_array($_SESSION['rol'] ?? '', ['Administrador', 'Supervisor', 'Tecnico'], true)
) {
    header("Location: index.php?modulo=login");
    exit();
}

require_once MODEL_PATH . 'admin/model.informacion_historica.php';

$modeloHistorico = new ModelInformacionHistorica($conexion);
$mensajes = [];
$estadosPermitidos = ['Programada', 'En Curso', 'Finalizada', 'Cancelada'];
$validarFecha = static function ($fecha) {
    if ($fecha === '') {
        return true;
    }

    $fechaValida = DateTime::createFromFormat('!Y-m-d', $fecha);
    return $fechaValida && $fechaValida->format('Y-m-d') === $fecha;
};

$filtros = [
    'desde' => isset($_GET['desde']) && is_string($_GET['desde']) ? trim($_GET['desde']) : '',
    'hasta' => isset($_GET['hasta']) && is_string($_GET['hasta']) ? trim($_GET['hasta']) : '',
    'estado' => isset($_GET['estado']) && is_string($_GET['estado']) ? trim($_GET['estado']) : '',
    'municipio' => isset($_GET['municipio']) && is_string($_GET['municipio']) ? trim($_GET['municipio']) : '',
    'busqueda' => isset($_GET['busqueda']) && is_string($_GET['busqueda'])
        ? substr(trim($_GET['busqueda']), 0, 100)
        : ''
];

if (!$validarFecha($filtros['desde']) || !$validarFecha($filtros['hasta'])) {
    $mensajes[] = 'Una fecha no es válida; se ignoró el filtro de fechas.';
    $filtros['desde'] = '';
    $filtros['hasta'] = '';
}
if ($filtros['desde'] !== '' && $filtros['hasta'] !== '' && $filtros['desde'] > $filtros['hasta']) {
    $mensajes[] = 'El rango de fechas no es válido; se ignoró el filtro de fechas.';
    $filtros['desde'] = '';
    $filtros['hasta'] = '';
}
if ($filtros['estado'] !== '' && !in_array($filtros['estado'], $estadosPermitidos, true)) {
    $mensajes[] = 'El estado seleccionado no es válido.';
    $filtros['estado'] = '';
}

$municipios = $modeloHistorico->obtenerMunicipios();
$municipiosValidos = array_column($municipios, 'Municipio');
if ($filtros['municipio'] !== '' && !in_array($filtros['municipio'], $municipiosValidos, true)) {
    $mensajes[] = 'El municipio seleccionado no es válido.';
    $filtros['municipio'] = '';
}

$campanas = $modeloHistorico->buscarCampanas($filtros);
$campanaHistorica = null;
$solicitantes = [];
$responsables = [];
$acciones = [];
$incidencias = [];
$plagasIdentificadas = [];
$insumosUsados = [];
$evidencias = [];
$idCampanaEntrada = isset($_GET['id_campana']) && is_string($_GET['id_campana'])
    ? $_GET['id_campana']
    : '';

if ($idCampanaEntrada !== '') {
    if (!ctype_digit($idCampanaEntrada) || (int)$idCampanaEntrada < 1) {
        $mensajes[] = 'La campaña solicitada no es válida.';
    } else {
        $idCampana = (int)$idCampanaEntrada;
        $campanaHistorica = $modeloHistorico->obtenerCampanaPorId($idCampana);
        if (!$campanaHistorica) {
            $mensajes[] = 'No se encontró la campaña solicitada.';
        } else {
            $solicitantes = $modeloHistorico->obtenerSolicitantes($idCampana);
            $responsables = $modeloHistorico->obtenerResponsables($idCampana);
            $acciones = $modeloHistorico->obtenerAcciones($idCampana);
            $incidencias = $modeloHistorico->obtenerIncidencias($idCampana);
            $plagasIdentificadas = $modeloHistorico->obtenerPlagasIdentificadas($idCampana);
            $insumosUsados = $modeloHistorico->obtenerInsumosUsados($idCampana);
            $evidencias = $modeloHistorico->obtenerEvidencias($idCampana);
        }
    }
}

require_once VIEW_PATH . 'admin/informacion_historica.php';
