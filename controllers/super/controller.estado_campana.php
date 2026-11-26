<?php
if (
    !isset($_SESSION['id_usuario'])
    || !in_array($_SESSION['rol'] ?? '', ['Administrador', 'Supervisor', 'Tecnico'], true)
) {
    header("Location: index.php?modulo=login");
    exit();
}

require_once MODEL_PATH . 'admin/model.campana.php';
$modeloCampana = new ModelCampana($conexion);
$mensaje = '';
$tipoMensaje = 'info';
$estadosPermitidos = ['Programada', 'En Curso', 'Finalizada', 'Cancelada'];
$transicionesPermitidas = [
    'Programada' => ['En Curso', 'Cancelada'],
    'En Curso' => ['Finalizada', 'Cancelada'],
    'Finalizada' => [],
    'Cancelada' => [],
];

$filtros = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
$municipio = isset($filtros['municipio']) && is_string($filtros['municipio'])
    ? trim($filtros['municipio'])
    : '';
$estadoFiltro = isset($filtros['estado']) && is_string($filtros['estado'])
    ? trim($filtros['estado'])
    : '';
$busqueda = isset($filtros['buscar']) && is_string($filtros['buscar'])
    ? trim($filtros['buscar'])
    : '';

$resultadoMunicipios = $modeloCampana->obtenerMunicipios();
if (!$resultadoMunicipios) {
    throw new RuntimeException('No se pudieron consultar los municipios.');
}
$municipios = array_column($resultadoMunicipios->fetch_all(MYSQLI_ASSOC), 'Municipio');
if ($municipio !== '' && !in_array($municipio, $municipios, true)) {
    $mensaje = 'El municipio seleccionado no es válido; se mostrarán todos.';
    $tipoMensaje = 'warning';
    $municipio = '';
}

if ($estadoFiltro !== '' && !in_array($estadoFiltro, $estadosPermitidos, true)) {
    $mensaje = 'El estado seleccionado no es válido; se mostrarán todos.';
    $tipoMensaje = 'warning';
    $estadoFiltro = '';
}

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
        $tipoMensaje = 'danger';
    } else {
        $idCampana = (int) $idEntrada;
        $campanaActual = $modeloCampana->obtenerCampanaPorId($idCampana);

        if (!$campanaActual) {
            $mensaje = 'La campaña seleccionada ya no existe.';
            $tipoMensaje = 'danger';
        } elseif ($campanaActual['Estado'] === $estado) {
            $mensaje = 'La campaña ya tiene ese estado.';
            $tipoMensaje = 'info';
        } elseif (!in_array($estado, $transicionesPermitidas[$campanaActual['Estado']] ?? [], true)) {
            $mensaje = 'Ese cambio de estado no está permitido.';
            $tipoMensaje = 'warning';
        } else {
            $resultadoActualizacion = $modeloCampana->actualizarEstadoCampana(
                $idCampana,
                $estado,
                $campanaActual['Estado'],
                (int) $_SESSION['id_usuario']
            );

            if ($resultadoActualizacion === 1) {
                $mensaje = 'Estado de campaña actualizado y guardado en el historial.';
                $tipoMensaje = 'success';
                if ($estadoFiltro === $campanaActual['Estado']) {
                    $estadoFiltro = $estado;
                }
            } elseif ($resultadoActualizacion === 0) {
                $estadoRecargado = $modeloCampana->obtenerCampanaPorId($idCampana);
                if (!$estadoRecargado) {
                    $mensaje = 'La campaña ya no existe.';
                    $tipoMensaje = 'danger';
                } elseif ($estadoRecargado['Estado'] === $estado) {
                    $mensaje = 'La campaña ya tiene ese estado.';
                    $tipoMensaje = 'info';
                } else {
                    $mensaje = 'El estado cambió en otra solicitud. Revisa la campaña y vuelve a intentar.';
                    $tipoMensaje = 'warning';
                }
            } else {
                $mensaje = 'No se pudo actualizar el estado de la campaña.';
                $tipoMensaje = 'danger';
            }
        }
    }
}

$resultadoCampanas = $modeloCampana->obtenerCampanas($municipio, $busqueda);
if (!$resultadoCampanas) {
    throw new RuntimeException('No se pudieron consultar las campañas.');
}
$campanasBase = $resultadoCampanas->fetch_all(MYSQLI_ASSOC);
$conteosEstado = array_fill_keys($estadosPermitidos, 0);
foreach ($campanasBase as $campana) {
    if (isset($conteosEstado[$campana['Estado']])) {
        $conteosEstado[$campana['Estado']]++;
    }
}

$campanas = $estadoFiltro === ''
    ? $campanasBase
    : array_values(array_filter($campanasBase, static function ($campana) use ($estadoFiltro) {
        return $campana['Estado'] === $estadoFiltro;
    }));

$resultadoHistorial = $modeloCampana->obtenerHistorialEstadosCampanas($municipio, $busqueda);
if (!$resultadoHistorial) {
    throw new RuntimeException('No se pudo consultar el historial de estados.');
}
$historialPorCampana = [];
foreach ($resultadoHistorial->fetch_all(MYSQLI_ASSOC) as $cambio) {
    $historialPorCampana[(int) $cambio['IdCampana']][] = $cambio;
}

require_once VIEW_PATH . 'super/estado_campana.php';