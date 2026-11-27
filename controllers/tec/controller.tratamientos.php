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

// --- Fotografía del tratamiento (mismo esquema que el módulo de evidencias) ---
// La imagen se guarda en resources/uploads/tratamientos/ y en la base de datos
// (tabla fotostratamiento, una foto por aplicación) se guarda su ruta relativa.
$carpetaFotosTratamiento = 'resources/uploads/tratamientos/';

// Valida y guarda la fotografía. Devuelve [rutaAbsoluta, rutaRelativa].
$guardarFotoTratamiento = static function ($archivo) use ($carpetaFotosTratamiento) {
    if (
        !is_array($archivo)
        || !isset($archivo['error'], $archivo['size'], $archivo['tmp_name'])
        || !is_int($archivo['error'])
        || !is_numeric($archivo['size'])
        || !is_string($archivo['tmp_name'])
        || $archivo['error'] !== UPLOAD_ERR_OK
        || (int)$archivo['size'] < 1
        || (int)$archivo['size'] > 5 * 1024 * 1024
        || !is_uploaded_file($archivo['tmp_name'])
    ) {
        throw new RuntimeException('La fotografía debe recibirse correctamente y pesar entre 1 byte y 5 MB.');
    }

    // Se revisa el contenido real del archivo, no solo su extensión.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($archivo['tmp_name']);
    $extensionesPermitidas = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];
    if (!isset($extensionesPermitidas[$mime])) {
        throw new RuntimeException('Solo se permiten fotografías JPEG, PNG o WebP.');
    }

    set_error_handler(static function ($severity, $message) {
        throw new RuntimeException('El archivo no es una imagen válida.');
    });
    try {
        $datosImagen = getimagesize($archivo['tmp_name']);
    } finally {
        restore_error_handler();
    }
    if ($datosImagen === false || ($datosImagen['mime'] ?? null) !== $mime) {
        throw new RuntimeException('El archivo no es una imagen válida.');
    }

    $directorioSubida = RESOURCE_PATH . 'uploads' . DIRECTORY_SEPARATOR . 'tratamientos';
    if (!is_dir($directorioSubida) && !mkdir($directorioSubida, 0750, true) && !is_dir($directorioSubida)) {
        throw new RuntimeException('No se pudo preparar el almacenamiento de fotografías de tratamientos.');
    }

    // Nombre aleatorio para que no se pueda adivinar ni sobrescribir otro archivo.
    $nombreArchivo = bin2hex(random_bytes(16)) . '.' . $extensionesPermitidas[$mime];
    $rutaAbsoluta = $directorioSubida . DIRECTORY_SEPARATOR . $nombreArchivo;
    if (!move_uploaded_file($archivo['tmp_name'], $rutaAbsoluta)) {
        throw new RuntimeException('No se pudo guardar la fotografía.');
    }

    return [$rutaAbsoluta, $carpetaFotosTratamiento . $nombreArchivo];
};

// Borra del disco una fotografía de tratamiento. Solo acepta rutas generadas por este módulo.
$borrarFotoTratamiento = static function ($rutaRelativa) use ($carpetaFotosTratamiento) {
    if (
        !is_string($rutaRelativa)
        || !preg_match('#^resources/uploads/tratamientos/[a-f0-9]{32}\.(jpg|png|webp)$#D', $rutaRelativa)
    ) {
        return true;
    }
    $rutaAbsoluta = RESOURCE_PATH . 'uploads' . DIRECTORY_SEPARATOR . 'tratamientos'
        . DIRECTORY_SEPARATOR . substr($rutaRelativa, strlen($carpetaFotosTratamiento));
    if (is_file($rutaAbsoluta) && !unlink($rutaAbsoluta)) {
        error_log('No se pudo borrar la fotografía de un tratamiento.');
        return false;
    }
    return true;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'eliminar') {
        $idEliminar = $_POST['id'] ?? '';
        $aplicacionEliminar = is_string($idEliminar) && ctype_digit($idEliminar) && (int)$idEliminar > 0
            ? $modeloOperaciones->obtenerAccionAplicacionPorId((int)$idEliminar)
            : null;
        if ($aplicacionEliminar && $modeloOperaciones->eliminarAccionPorTipo((int)$idEliminar, $tipoAplicacion)) {
            // La fila de fotostratamiento se borra sola (ON DELETE CASCADE); aquí se borra el archivo.
            $mensaje = $borrarFotoTratamiento($aplicacionEliminar['RutaFoto'] ?? null)
                ? 'Aplicación eliminada.'
                : 'La aplicación se eliminó, pero no se pudo borrar su fotografía.';
        } else {
            $mensaje = 'No se encontró la aplicación para eliminar.';
        }
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

            // La fotografía es opcional: solo se procesa si el usuario eligió un archivo.
            $archivo = $_FILES['foto'] ?? null;
            $seEnvioFoto = is_array($archivo)
                && isset($archivo['error'])
                && $archivo['error'] !== UPLOAD_ERR_NO_FILE;
            $archivoGuardado = null;
            $rutaFotoNueva = null;
            $rutaFotoAnterior = null;

            try {
                if ($seEnvioFoto) {
                    [$archivoGuardado, $rutaFotoNueva] = $guardarFotoTratamiento($archivo);
                }

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
                    $aplicacionExistente = $modeloOperaciones->obtenerAccionAplicacionPorId($idAccion);
                    if (!$aplicacionExistente) {
                        throw new RuntimeException('No se encontró la aplicación para modificar.');
                    }
                    $rutaFotoAnterior = $aplicacionExistente['RutaFoto'] ?? null;
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

                if ($rutaFotoNueva !== null && !$modeloOperaciones->guardarFotoTratamiento($idAccion, $rutaFotoNueva)) {
                    throw new RuntimeException('No se pudo guardar la fotografía de la aplicación.');
                }

                $conexion->commit();
                $transaccionIniciada = false;
                $mensaje = 'Aplicación guardada correctamente.';
                // Si se reemplazó la fotografía, se borra la anterior del disco.
                if ($rutaFotoNueva !== null && !$borrarFotoTratamiento($rutaFotoAnterior)) {
                    $mensaje = 'La aplicación se guardó, pero no se pudo borrar su fotografía anterior.';
                }
            } catch (mysqli_sql_exception $e) {
                if ($transaccionIniciada) {
                    $conexion->rollback();
                }
                if ($archivoGuardado !== null && is_file($archivoGuardado)) {
                    unlink($archivoGuardado);
                }
                error_log('Error al guardar una aplicación: ' . $e->getMessage());
                $mensaje = 'No se pudo guardar la aplicación. Verifica las referencias y vuelve a intentarlo.';
            } catch (RuntimeException $e) {
                if ($transaccionIniciada) {
                    $conexion->rollback();
                }
                if ($archivoGuardado !== null && is_file($archivoGuardado)) {
                    unlink($archivoGuardado);
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