<?php
if (
    !isset($_SESSION['id_usuario'])
    || !in_array($_SESSION['rol'] ?? '', ['Administrador', 'Supervisor', 'Tecnico'], true)
) {
    header("Location: index.php?modulo=login");
    exit();
}

require_once MODEL_PATH . 'model.operaciones.php';
$modeloOperaciones = new ModelOperaciones($conexion);
$tiposIncidencia = $modeloOperaciones->obtenerTiposIncidencia()->fetch_all(MYSQLI_ASSOC);
$idsTiposIncidencia = array_map('intval', array_column($tiposIncidencia, 'IdTipoIncidencia'));
$mensaje = '';
$incidenciaEditar = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'eliminar') {
        $idEntrada = $_POST['id'] ?? '';
        if (is_string($idEntrada) && ctype_digit($idEntrada) && (int)$idEntrada > 0) {
            $mensaje = $modeloOperaciones->eliminarIncidencia((int)$idEntrada)
                ? 'Incidencia eliminada.'
                : 'No se encontró la incidencia para eliminar.';
        } else {
            $mensaje = 'La incidencia indicada no es válida.';
        }
    } elseif ($accion === 'guardar_incidencia') {
        $idCampanaEntrada = $_POST['id_campana'] ?? '';
        $actividad = $_POST['actividad'] ?? '';
        $idTipoIncidenciaEntrada = $_POST['id_tipo_incidencia'] ?? '';
        $descripcionEntrada = $_POST['detalle'] ?? '';
        $descripcion = is_string($descripcionEntrada) ? trim($descripcionEntrada) : '';
        $idEntrada = $_POST['id_incidencia'] ?? '';
        $actividadesPermitidas = ['Inspección', 'Aplicación', 'Seguimiento'];
        $idCampanaValido = is_string($idCampanaEntrada)
            && ctype_digit($idCampanaEntrada)
            && (int)$idCampanaEntrada > 0;
        $idValido = $idEntrada === ''
            || (is_string($idEntrada) && ctype_digit($idEntrada) && (int)$idEntrada > 0);
        $tipoIncidenciaValido = is_string($idTipoIncidenciaEntrada)
            && ctype_digit($idTipoIncidenciaEntrada)
            && (int)$idTipoIncidenciaEntrada > 0
            && in_array((int)$idTipoIncidenciaEntrada, $idsTiposIncidencia, true);

        if (
            !$idCampanaValido
            || !$idValido
            || !$tipoIncidenciaValido
            || !is_string($actividad)
            || !in_array($actividad, $actividadesPermitidas, true)
            || $descripcion === ''
        ) {
            $mensaje = 'Completa una campaña, actividad, tipo de incidencia y descripción válidos.';
        } else {
            if ($idEntrada === '') {
                $guardado = $modeloOperaciones->registrarIncidencia(
                    (int)$idCampanaEntrada,
                    $actividad,
                    $descripcion,
                    (int)$idTipoIncidenciaEntrada
                );
            } else {
                $guardado = $modeloOperaciones->actualizarIncidencia(
                    (int)$idEntrada,
                    (int)$idCampanaEntrada,
                    $actividad,
                    $descripcion,
                    (int)$idTipoIncidenciaEntrada
                );
            }

            $mensaje = $guardado
                ? 'Incidencia guardada correctamente.'
                : 'No se pudo guardar la incidencia. Verifica que la campaña exista.';
        }
    } else {
        $mensaje = 'La acción solicitada no es válida.';
    }
}

if (($_GET['accion'] ?? '') === 'editar' && isset($_GET['id'])) {
    $idEntrada = $_GET['id'];
    if (is_string($idEntrada) && ctype_digit($idEntrada) && (int)$idEntrada > 0) {
        $incidenciaEditar = $modeloOperaciones->obtenerIncidenciaPorId((int)$idEntrada);
    }
}

$resultadoCampanas = $modeloOperaciones->obtenerCampanas();
$resultadoIncidencias = $modeloOperaciones->obtenerIncidencias();
if (!$resultadoCampanas || !$resultadoIncidencias) {
    throw new RuntimeException('No se pudieron consultar las campañas y las incidencias.');
}
require_once VIEW_PATH . 'super/incidencias.php';