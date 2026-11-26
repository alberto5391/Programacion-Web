<?php
// 1. Validar sesión integrada (El session_start ya está en index.php)
if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['rol'], ['Administrador', 'Supervisor', 'Tecnico'], true)) {
    header("Location: index.php?modulo=login");
    exit();
}

require_once MODEL_PATH . 'model.operaciones.php';
$modeloOperaciones = new ModelOperaciones($conexion);
$mensaje = '';
$accionEditar = null;
$tiposAccion = ['Inspección', 'Diagnóstico'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $idEntrada = $_POST['id_accion'] ?? '';

    if ($accion === 'eliminar') {
        $idEliminar = $_POST['id'] ?? '';
        $tipoEliminar = $_POST['tipo_accion'] ?? '';
        $mensaje = is_string($idEliminar) && ctype_digit($idEliminar) && (int)$idEliminar > 0
            && is_string($tipoEliminar) && in_array($tipoEliminar, $tiposAccion, true)
            && $modeloOperaciones->eliminarAccionPorTipo((int)$idEliminar, $tipoEliminar)
                ? 'Registro eliminado.'
                : 'No se encontró el registro para eliminar.';
    } elseif ($accion === 'guardar_accion') {
        $idCampanaEntrada = $_POST['id_campana'] ?? '';
        $idTecnicoEntrada = $_POST['id_tecnico'] ?? '';
        $tipo = $_POST['tipo_accion'] ?? '';
        $fechaEntrada = $_POST['fecha_hora'] ?? '';
        $ubicacionEntrada = $_POST['ubicacion'] ?? '';
        $observacionesEntrada = $_POST['observaciones'] ?? '';
        $plagaEntrada = $_POST['id_plaga'] ?? '';
        $nivel = $_POST['nivel_infestacion'] ?? '';
        $caracteristicasEntrada = $_POST['caracteristicas'] ?? '';
        $campanaValida = is_string($idCampanaEntrada)
            && ctype_digit($idCampanaEntrada)
            && (int)$idCampanaEntrada > 0
            && $modeloOperaciones->campanaOperativa((int)$idCampanaEntrada);
        $fecha = is_string($fechaEntrada)
            ? DateTime::createFromFormat('!Y-m-d\TH:i', $fechaEntrada)
            : false;
        $fechaValida = $fecha && $fecha->format('Y-m-d\TH:i') === $fechaEntrada;
        $idValido = $idEntrada === ''
            || (is_string($idEntrada) && ctype_digit($idEntrada) && (int)$idEntrada > 0);
        $observaciones = is_string($observacionesEntrada) ? trim($observacionesEntrada) : '';
        $caracteristicas = is_string($caracteristicasEntrada) ? trim($caracteristicasEntrada) : '';
        $ubicacion = is_string($ubicacionEntrada) ? trim($ubicacionEntrada) : '';
        $plagaIdValido = is_string($plagaEntrada)
            && ctype_digit($plagaEntrada)
            && (int)$plagaEntrada > 0
            && $modeloOperaciones->plagaActiva((int)$plagaEntrada);
        $nivelesPermitidos = ['Bajo', 'Medio', 'Alto', 'Crítico'];

        if ($_SESSION['rol'] === 'Tecnico') {
            $idTecnico = $modeloOperaciones->obtenerTecnicoPorUsuario((int)$_SESSION['id_usuario']);
        } else {
            $idTecnico = is_string($idTecnicoEntrada)
                && ctype_digit($idTecnicoEntrada)
                && (int)$idTecnicoEntrada > 0
                    ? (int)$idTecnicoEntrada
                    : null;
        }

        $tipoValido = is_string($tipo) && in_array($tipo, $tiposAccion, true);
        $detalleValido = $tipo === 'Inspección'
            ? $ubicacion !== ''
            : ($plagaIdValido && is_string($nivel) && in_array($nivel, $nivelesPermitidos, true) && $caracteristicas !== '');

        if (
            !$campanaValida
            || !$fechaValida
            || !$idValido
            || !$tipoValido
            || !$detalleValido
            || $idTecnico === null
            || !$modeloOperaciones->tecnicoActivo($idTecnico)
        ) {
            $mensaje = 'Completa los datos de campaña, responsable, fecha y diagnóstico o ubicación.';
        } else {
            $detalles = $tipo === 'Inspección'
                ? 'Ubicación: ' . $ubicacion
                : 'Nivel de infestación: ' . $nivel . "\n" . $caracteristicas;
            $observacionPlaga = $tipo === 'Diagnóstico' ? $caracteristicas : null;
            $fechaHora = $fecha->format('Y-m-d H:i:s');
            $observaciones = $observaciones !== '' ? $observaciones : null;
            $transaccionIniciada = false;

            try {
                $conexion->begin_transaction();
                $transaccionIniciada = true;
                if ($idEntrada === '') {
                    if (!$modeloOperaciones->registrarAccion(
                        (int)$idCampanaEntrada,
                        $idTecnico,
                        $tipo,
                        $fechaHora,
                        $detalles,
                        $observaciones
                    )) {
                        throw new RuntimeException('No se pudo registrar la acción.');
                    }
                    $idAccion = (int)$conexion->insert_id;
                } else {
                    $idAccion = (int)$idEntrada;
                    $accionExistente = $modeloOperaciones->obtenerAccionInspeccionDiagnosticoPorId($idAccion);
                    if (!$accionExistente || $accionExistente['TipoAccion'] !== $tipo) {
                        throw new RuntimeException('No se encontró la acción para modificar.');
                    }
                    if (!$modeloOperaciones->actualizarAccion(
                        $idAccion,
                        (int)$idCampanaEntrada,
                        $idTecnico,
                        $tipo,
                        $fechaHora,
                        $detalles,
                        $observaciones
                    )) {
                        throw new RuntimeException('No se pudo actualizar la acción.');
                    }
                }

                if ($tipo === 'Diagnóstico') {
                    if (!$modeloOperaciones->actualizarPlagaIdentificada(
                        $idAccion,
                        (int)$plagaEntrada,
                        $nivel,
                        $observacionPlaga
                    )) {
                        throw new RuntimeException('No se pudo guardar la plaga identificada.');
                    }
                } elseif (!$modeloOperaciones->eliminarPlagasIdentificadas($idAccion)) {
                    throw new RuntimeException('No se pudieron actualizar las plagas asociadas.');
                }

                $conexion->commit();
                $transaccionIniciada = false;
                $mensaje = 'Registro guardado correctamente.';
            } catch (mysqli_sql_exception $e) {
                if ($transaccionIniciada) {
                    $conexion->rollback();
                }
                error_log('Error al guardar una inspección o diagnóstico: ' . $e->getMessage());
                $mensaje = 'No se pudo guardar el registro. Verifica las referencias y vuelve a intentarlo.';
            } catch (RuntimeException $e) {
                if ($transaccionIniciada) {
                    $conexion->rollback();
                }
                $mensaje = $e->getMessage();
            }
        }
    } else {
        $mensaje = 'La acción solicitada no es válida.';
    }
}

if (($_GET['accion'] ?? '') === 'editar' && isset($_GET['id'])) {
    $idEditar = $_GET['id'];
    if (is_string($idEditar) && ctype_digit($idEditar) && (int)$idEditar > 0) {
        $accionEditar = $modeloOperaciones->obtenerAccionInspeccionDiagnosticoPorId((int)$idEditar);
    }
}

$resultadoCampanas = $modeloOperaciones->obtenerCampanas();
$resultadoTecnicos = $modeloOperaciones->obtenerTecnicosActivos();
$resultadoPlagas = $modeloOperaciones->obtenerPlagasActivas();
$resultadoAcciones = $modeloOperaciones->obtenerAccionesInspeccionDiagnostico();
if (!$resultadoCampanas || !$resultadoTecnicos || !$resultadoPlagas || !$resultadoAcciones) {
    throw new RuntimeException('No se pudieron consultar las inspecciones y diagnósticos.');
}

require_once VIEW_PATH . 'tec/inspeccion_diagnostico.php';
?>