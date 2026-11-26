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
$tipoSeguimiento = 'Seguimiento';
$mensaje = '';
$seguimientoEditar = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $idEntrada = $_POST['id_accion'] ?? '';

    if ($accion === 'eliminar') {
        $idEliminar = $_POST['id'] ?? '';
        $mensaje = is_string($idEliminar) && ctype_digit($idEliminar) && (int)$idEliminar > 0
            && $modeloOperaciones->eliminarAccionPorTipo((int)$idEliminar, $tipoSeguimiento)
                ? 'Seguimiento eliminado.'
                : 'No se encontró el seguimiento para eliminar.';
    } elseif ($accion === 'guardar_seguimiento') {
        $idCampanaEntrada = $_POST['id_campana'] ?? '';
        $idTecnicoEntrada = $_POST['id_tecnico'] ?? '';
        $fechaEntrada = $_POST['fecha_seguimiento'] ?? '';
        $presencia = $_POST['presencia_plaga'] ?? '';
        $resultadosEntrada = $_POST['resultados_seg'] ?? '';
        $incidenciasEntrada = $_POST['incidencias'] ?? '';
        $idValido = $idEntrada === ''
            || (is_string($idEntrada) && ctype_digit($idEntrada) && (int)$idEntrada > 0);
        $campanaValida = is_string($idCampanaEntrada)
            && ctype_digit($idCampanaEntrada)
            && (int)$idCampanaEntrada > 0;
        $fecha = is_string($fechaEntrada)
            ? DateTime::createFromFormat('!Y-m-d\TH:i', $fechaEntrada)
            : false;
        $fechaValida = $fecha
            && $fecha->format('Y-m-d\TH:i') === $fechaEntrada;
        $resultados = is_string($resultadosEntrada) ? trim($resultadosEntrada) : '';
        $incidencias = is_string($incidenciasEntrada) ? trim($incidenciasEntrada) : '';
        $presenciasPermitidas = ['Ausente', 'Presente', 'Reincidente'];

        if ($_SESSION['rol'] === 'Tecnico') {
            $idTecnico = $modeloOperaciones->obtenerTecnicoPorUsuario((int)$_SESSION['id_usuario']);
        } else {
            $idTecnico = is_string($idTecnicoEntrada)
                && ctype_digit($idTecnicoEntrada)
                && (int)$idTecnicoEntrada > 0
                    ? (int)$idTecnicoEntrada
                    : null;
        }

        if (
            !$idValido
            || !$campanaValida
            || !$fechaValida
            || !is_string($presencia)
            || !in_array($presencia, $presenciasPermitidas, true)
            || $resultados === ''
            || $idTecnico === null
        ) {
            $mensaje = 'Completa los datos de campaña, responsable, fecha y resultado.';
        } else {
            $detalles = 'Presencia: ' . $presencia . "\nResultados: " . $resultados;
            $fechaHora = $fecha->format('Y-m-d H:i:s');
            $observaciones = $incidencias !== '' ? $incidencias : null;

            if ($idEntrada === '') {
                $guardado = $modeloOperaciones->registrarAccion(
                    (int)$idCampanaEntrada,
                    $idTecnico,
                    $tipoSeguimiento,
                    $fechaHora,
                    $detalles,
                    $observaciones
                );
            } else {
                $guardado = $modeloOperaciones->actualizarAccion(
                    (int)$idEntrada,
                    (int)$idCampanaEntrada,
                    $idTecnico,
                    $tipoSeguimiento,
                    $fechaHora,
                    $detalles,
                    $observaciones
                );
            }
            $mensaje = $guardado
                ? 'Seguimiento guardado correctamente.'
                : 'No se pudo guardar el seguimiento. Verifica las referencias seleccionadas.';
        }
    } else {
        $mensaje = 'La acción solicitada no es válida.';
    }
}

if (($_GET['accion'] ?? '') === 'editar' && isset($_GET['id'])) {
    $idEditar = $_GET['id'];
    if (is_string($idEditar) && ctype_digit($idEditar) && (int)$idEditar > 0) {
        $seguimientoEditar = $modeloOperaciones->obtenerAccionPorIdYTipo(
            (int)$idEditar,
            $tipoSeguimiento
        );
        if ($seguimientoEditar) {
            $lineasResultado = explode("\nResultados: ", $seguimientoEditar['DetallesResultados'], 2);
            $seguimientoEditar['Presencia'] = strncmp($lineasResultado[0], 'Presencia: ', 11) === 0
                ? substr($lineasResultado[0], strlen('Presencia: '))
                : '';
            $seguimientoEditar['Resultado'] = $lineasResultado[1] ?? $seguimientoEditar['DetallesResultados'];
        }
    }
}

$resultadoCampanas = $modeloOperaciones->obtenerCampanas();
$resultadoTecnicos = $modeloOperaciones->obtenerTecnicosActivos();
$resultadoSeguimientos = $modeloOperaciones->obtenerAccionesPorTipo($tipoSeguimiento);
if (!$resultadoCampanas || !$resultadoTecnicos || !$resultadoSeguimientos) {
    throw new RuntimeException('No se pudieron consultar los datos de seguimiento.');
}

require_once VIEW_PATH . 'super/seguimiento_posterior.php';