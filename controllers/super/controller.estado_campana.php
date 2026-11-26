<?php
if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['rol'] ?? '', ['Administrador', 'Supervisor', 'Tecnico'], true)) {
    header("Location: index.php?modulo=login");
    exit();
}

require_once MODEL_PATH . 'admin/model.campana.php';
$modeloCampana = new ModelCampana($conexion);
$mensaje = '';
$estadosPermitidos = ['Programada', 'En Curso', 'Finalizada', 'Cancelada'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'actualizar_estado_campana') {
    $idEntrada = $_POST['id_campana'] ?? '';
    $estado = $_POST['estado_campana'] ?? '';
    if (
        !is_string($idEntrada)
        || !ctype_digit($idEntrada)
        || (int)$idEntrada < 1
        || !is_string($estado)
        || !in_array($estado, $estadosPermitidos, true)
    ) {
        $mensaje = 'Selecciona una campaña y un estado válidos.';
    } else {
        $mensaje = $modeloCampana->actualizarEstadoCampana((int)$idEntrada, $estado)
            ? 'Estado de campaña actualizado.'
            : 'No se pudo actualizar el estado de la campaña.';
    }
}

$resultadoCampanas = $modeloCampana->obtenerCampanas();
if (!$resultadoCampanas) {
    throw new RuntimeException('No se pudieron consultar las campañas.');
}
$campanas = $resultadoCampanas->fetch_all(MYSQLI_ASSOC);
require_once VIEW_PATH . 'super/estado_campana.php';