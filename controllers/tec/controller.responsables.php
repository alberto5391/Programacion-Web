<?php
// 1. Validar sesión integrada (El session_start ya está en index.php)
if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['rol'], ['Administrador', 'Supervisor', 'Tecnico'], true)) {
    header("Location: index.php?modulo=login");
    exit();
}

require_once MODEL_PATH . 'model.operaciones.php';
$modeloOperaciones = new ModelOperaciones($conexion);
$mensaje = '';
$asignacionEditar = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'eliminar') {
        $idCampanaEntrada = $_POST['id_campana'] ?? '';
        $idUsuarioEntrada = $_POST['id_usuario'] ?? '';
        $mensaje = is_string($idCampanaEntrada) && ctype_digit($idCampanaEntrada)
            && is_string($idUsuarioEntrada) && ctype_digit($idUsuarioEntrada)
            && $modeloOperaciones->eliminarAsignacion((int)$idCampanaEntrada, (int)$idUsuarioEntrada)
                ? 'Responsable retirado de la campaña.'
                : 'No se encontró la asignación para eliminar.';
    } elseif (in_array($accion, ['guardar_responsable', 'modificar_responsable'], true)) {
        $idCampanaEntrada = $_POST['id_campana'] ?? '';
        $idUsuarioEntrada = $_POST['id_usuario'] ?? '';
        $idCampanaAnteriorEntrada = $_POST['id_campana_original'] ?? '';
        $idUsuarioAnteriorEntrada = $_POST['id_usuario_original'] ?? '';
        $idCampanaValido = is_string($idCampanaEntrada)
            && ctype_digit($idCampanaEntrada)
            && (int)$idCampanaEntrada > 0
            && $modeloOperaciones->campanaOperativa((int)$idCampanaEntrada);
        $idUsuarioValido = is_string($idUsuarioEntrada)
            && ctype_digit($idUsuarioEntrada)
            && (int)$idUsuarioEntrada > 0;
        if ($_SESSION['rol'] === 'Tecnico') {
            $idUsuario = (int)$_SESSION['id_usuario'];
            $idUsuarioValido = $modeloOperaciones->usuarioTecnicoActivo($idUsuario);
        } else {
            $idUsuario = $idUsuarioValido ? (int)$idUsuarioEntrada : 0;
        }

        if (!$idCampanaValido || !$idUsuarioValido || !$modeloOperaciones->usuarioTecnicoActivo($idUsuario)) {
            $mensaje = 'Selecciona una campaña activa y un técnico activo.';
        } elseif ($accion === 'guardar_responsable') {
            try {
                $mensaje = $modeloOperaciones->registrarAsignacion((int)$idCampanaEntrada, $idUsuario)
                    ? 'Técnico asignado a la campaña.'
                    : 'No se pudo asignar el técnico.';
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() === 1062) {
                    $mensaje = 'El técnico ya está asignado a esa campaña.';
                } else {
                    throw $e;
                }
            }
        } else {
            $idCampanaAnteriorValido = is_string($idCampanaAnteriorEntrada)
                && ctype_digit($idCampanaAnteriorEntrada)
                && (int)$idCampanaAnteriorEntrada > 0;
            $idUsuarioAnteriorValido = is_string($idUsuarioAnteriorEntrada)
                && ctype_digit($idUsuarioAnteriorEntrada)
                && (int)$idUsuarioAnteriorEntrada > 0;

            if (!$idCampanaAnteriorValido || !$idUsuarioAnteriorValido) {
                $mensaje = 'La asignación que intentas modificar no es válida.';
            } else {
                $conexion->begin_transaction();
                try {
                    if (
                        !$modeloOperaciones->eliminarAsignacion((int)$idCampanaAnteriorEntrada, (int)$idUsuarioAnteriorEntrada)
                        || !$modeloOperaciones->registrarAsignacion((int)$idCampanaEntrada, $idUsuario)
                    ) {
                        throw new RuntimeException('No se pudo actualizar la asignación.');
                    }
                    $conexion->commit();
                    $mensaje = 'Asignación actualizada.';
                } catch (mysqli_sql_exception $e) {
                    $conexion->rollback();
                    error_log('Error al actualizar una asignación de campaña: ' . $e->getMessage());
                    $mensaje = 'No se pudo actualizar la asignación. Revisa si el técnico ya está asignado.';
                } catch (RuntimeException $e) {
                    $conexion->rollback();
                    $mensaje = $e->getMessage();
                }
            }
        }
    } else {
        $mensaje = 'La acción solicitada no es válida.';
    }
}

if (($_GET['accion'] ?? '') === 'editar') {
    $idCampanaEditar = $_GET['id_campana'] ?? '';
    $idUsuarioEditar = $_GET['id_usuario'] ?? '';
    if (
        is_string($idCampanaEditar) && ctype_digit($idCampanaEditar)
        && is_string($idUsuarioEditar) && ctype_digit($idUsuarioEditar)
    ) {
        $asignacionEditar = $modeloOperaciones->asignacionExiste(
            (int)$idCampanaEditar,
            (int)$idUsuarioEditar
        ) ? [
            'IdCampana' => (int)$idCampanaEditar,
            'IdUsuario' => (int)$idUsuarioEditar
        ] : null;
    }
}

$resultadoCampanas = $modeloOperaciones->obtenerCampanas();
$resultadoTecnicos = $modeloOperaciones->obtenerUsuariosTecnicosActivos();
$resultadoAsignaciones = $modeloOperaciones->obtenerAsignaciones();
if (!$resultadoCampanas || !$resultadoTecnicos || !$resultadoAsignaciones) {
    throw new RuntimeException('No se pudieron consultar las asignaciones de campaña.');
}

require_once VIEW_PATH . 'tec/responsables.php';
?>