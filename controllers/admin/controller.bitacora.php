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
$accionesPermitidas = ['Creación', 'Modificación', 'Desactivación', 'Eliminación lógica'];
if (!in_array($accion, $accionesPermitidas, true)) {
    $accion = '';
}
$usuarios = $modeloBitacora->obtenerUsuarios();
$modulos = $modeloBitacora->obtenerModulos();
$registros = $modeloBitacora->buscar($usuarioId, $moduloId, $accion, $fechaInicio, $fechaFin);

// 3. Cargar la vista
require_once VIEW_PATH . 'admin/bitacora.php';
?>