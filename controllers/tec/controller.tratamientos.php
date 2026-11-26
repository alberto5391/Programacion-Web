<?php
// 1. Validar sesión integrada (El session_start ya está en index.php)
if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['rol'], ['Administrador', 'Supervisor', 'Tecnico'], true)) {
    header("Location: index.php?modulo=login");
    exit();
}

require_once MODEL_PATH . 'model.operaciones.php';
$modeloOperaciones = new ModelOperaciones($conexion);
$tipoAplicacion = 'Aplicación';
$mensaje = '';
$aplicacionEditar = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'eliminar') {
        $idEliminar = $_POST['id'] ?? '';
        $mensaje = is_string($idEliminar) && ctype_digit($idEliminar) && (int)$idEliminar > 0
            && $modeloOperaciones->eliminarAccionPorTipo((int)$idEliminar, $tipoAplicacion)
                ? 'Aplicación eliminada.'
                : 'No se encontró la aplicación para eliminar.';
    } elseif ($accion === 'guardar_aplicacion') {
        $idEntrada = $_POST['id_accion'] ?? '';
        $idCampanaEntrada = $_POST['id_campana'] ?? '';
        $idTecnicoEntrada = $_POST['id_tecnico'] ?? '';
        $idProductoEntrada = $_POST['id_producto'] ?? '';
        $cantidadEntrada = $_POST['cantidad'] ?? '';
        $fechaEntrada = $_POST['fecha_hora'] ?? '';
        $observacionesEntrada = $_POST['observaciones'] ?? '';
        $idValido = $idEntrada === ''
            || (is_string($idEntrada) && ctype_digit($idEntrada) && (int)$idEntrada > 0);
        $campanaValida = is_string($idCampanaEntrada)
            && ctype_digit($idCampanaEntrada)
            && (int)$idCampanaEntrada > 0
            && $modeloOperaciones->campanaOperativa((int)$idCampanaEntrada);
        $producto = is_string($idProductoEntrada)
            && ctype_digit($idProductoEntrada)
            && (int)$idProductoEntrada > 0
                ? $modeloOperaciones->obtenerProductoPorId((int)$idProductoEntrada)
                : null;
        $cantidadValida = is_string($cantidadEntrada)
            && is_numeric($cantidadEntrada)
            && (float)$cantidadEntrada > 0
            && (float)$cantidadEntrada <= 99999999.99;
        $fecha = is_string($fechaEntrada)
            ? DateTime::createFromFormat('!Y-m-d\TH:i', $fechaEntrada)
            : false;
        $fechaValida = $fecha && $fecha->format('Y-m-d\TH:i') === $fechaEntrada;
        $observaciones = is_string($observacionesEntrada) ? trim($observacionesEntrada) : '';

        if ($_SESSION['rol'] === 'Tecnico') {
            $idTecnico = $modeloOperaciones->obtenerTecnicoPorUsuario((int)$_SESSION['id_usuario']);
        } else {
            $idTecnicoEntrada = $_POST['id_tecnico'] ?? '';
            $idTecnico = is_string($idTecnicoEntrada)
                && ctype_digit($idTecnicoEntrada)
                && (int)$idTecnicoEntrada > 0
                    ? (int)$idTecnicoEntrada
                    : null;
        }

        if (
            !$idValido
            || !$campanaValida
            || !$producto
            || !$cantidadValida
            || !$fechaValida
            || $idTecnico === null
            || !$modeloOperaciones->tecnicoActivo($idTecnico)
        ) {
            $mensaje = 'Completa los datos de campaña, responsable, producto, cantidad y fecha.';
        } else {
            $cantidad = (float)$cantidadEntrada;
            $detalles = 'Producto: ' . $producto['NombreComercial'] . '; cantidad: '
                . number_format($cantidad, 2, '.', '') . ' ' . $producto['UnidadMedida'];
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
                        $tipoAplicacion,
                        $fechaHora,
                        $detalles,
                        $observaciones
                    )) {
                        throw new RuntimeException('No se pudo registrar la aplicación.');
                    }
                    $idAccion = (int)$conexion->insert_id;
                } else {
                    $idAccion = (int)$idEntrada;
                    if (!$modeloOperaciones->obtenerAccionPorIdYTipo($idAccion, $tipoAplicacion)) {
                        throw new RuntimeException('No se encontró la aplicación para modificar.');
                    }
                    if (!$modeloOperaciones->actualizarAccion(
                        $idAccion,
                        (int)$idCampanaEntrada,
                        $idTecnico,
                        $tipoAplicacion,
                        $fechaHora,
                        $detalles,
                        $observaciones
                    )) {
                        throw new RuntimeException('No se pudo actualizar la aplicación.');
                    }
                }

                if (!$modeloOperaciones->reemplazarInsumoUsado(
                    $idAccion,
                    (int)$idProductoEntrada,
                    $cantidad,
                    $producto['UnidadMedida']
                )) {
                    throw new RuntimeException('No se pudo guardar el producto aplicado.');
                }
                $conexion->commit();
                $transaccionIniciada = false;
                $mensaje = 'Aplicación guardada correctamente.';
            } catch (mysqli_sql_exception $e) {
                if ($transaccionIniciada) {
                    $conexion->rollback();
                }
                error_log('Error al guardar una aplicación: ' . $e->getMessage());
                $mensaje = 'No se pudo guardar la aplicación. Verifica las referencias y vuelve a intentarlo.';
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
        $aplicacionEditar = $modeloOperaciones->obtenerAccionAplicacionPorId((int)$idEditar);
    }
}

$resultadoCampanas = $modeloOperaciones->obtenerCampanas();
$resultadoTecnicos = $modeloOperaciones->obtenerTecnicosActivos();
$resultadoProductos = $modeloOperaciones->obtenerProductosActivos();
$resultadoAplicaciones = $modeloOperaciones->obtenerAccionesAplicacion();
if (!$resultadoCampanas || !$resultadoTecnicos || !$resultadoProductos || !$resultadoAplicaciones) {
    throw new RuntimeException('No se pudieron consultar las aplicaciones y los productos.');
}

require_once VIEW_PATH . 'tec/tratamientos.php';
?>