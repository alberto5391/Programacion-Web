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

// --- Fotografía de la incidencia (mismo esquema que el módulo de evidencias) ---
// La imagen se guarda en resources/uploads/incidencias/ y en la base de datos
// (columna Incidencias.RutaFoto) se guarda su ruta relativa.
$carpetaFotosIncidencias = 'resources/uploads/incidencias/';

// Valida y guarda la fotografía. Devuelve [rutaAbsoluta, rutaRelativa].
$guardarFotoIncidencia = static function ($archivo) use ($carpetaFotosIncidencias) {
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

    $directorioSubida = RESOURCE_PATH . 'uploads' . DIRECTORY_SEPARATOR . 'incidencias';
    if (!is_dir($directorioSubida) && !mkdir($directorioSubida, 0750, true) && !is_dir($directorioSubida)) {
        throw new RuntimeException('No se pudo preparar el almacenamiento de fotografías de incidencias.');
    }

    // Nombre aleatorio para que no se pueda adivinar ni sobrescribir otro archivo.
    $nombreArchivo = bin2hex(random_bytes(16)) . '.' . $extensionesPermitidas[$mime];
    $rutaAbsoluta = $directorioSubida . DIRECTORY_SEPARATOR . $nombreArchivo;
    if (!move_uploaded_file($archivo['tmp_name'], $rutaAbsoluta)) {
        throw new RuntimeException('No se pudo guardar la fotografía.');
    }

    return [$rutaAbsoluta, $carpetaFotosIncidencias . $nombreArchivo];
};

// Borra del disco una fotografía de incidencia. Solo acepta rutas generadas por este módulo.
$borrarFotoIncidencia = static function ($rutaRelativa) use ($carpetaFotosIncidencias) {
    if (
        !is_string($rutaRelativa)
        || !preg_match('#^resources/uploads/incidencias/[a-f0-9]{32}\.(jpg|png|webp)$#D', $rutaRelativa)
    ) {
        return true;
    }
    $rutaAbsoluta = RESOURCE_PATH . 'uploads' . DIRECTORY_SEPARATOR . 'incidencias'
        . DIRECTORY_SEPARATOR . substr($rutaRelativa, strlen($carpetaFotosIncidencias));
    if (is_file($rutaAbsoluta) && !unlink($rutaAbsoluta)) {
        error_log('No se pudo borrar la fotografía de una incidencia.');
        return false;
    }
    return true;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'eliminar') {
        $idEntrada = $_POST['id'] ?? '';
        if (is_string($idEntrada) && ctype_digit($idEntrada) && (int)$idEntrada > 0) {
            $incidenciaEliminar = $modeloOperaciones->obtenerIncidenciaPorId((int)$idEntrada);
            if ($incidenciaEliminar && $modeloOperaciones->eliminarIncidencia((int)$idEntrada)) {
                $mensaje = $borrarFotoIncidencia($incidenciaEliminar['RutaFoto'] ?? null)
                    ? 'Incidencia eliminada.'
                    : 'La incidencia se eliminó, pero no se pudo borrar su fotografía.';
            } else {
                $mensaje = 'No se encontró la incidencia para eliminar.';
            }
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

        // La fotografía es opcional: solo se procesa si el usuario eligió un archivo.
        $archivo = $_FILES['foto'] ?? null;
        $seEnvioFoto = is_array($archivo)
            && isset($archivo['error'])
            && $archivo['error'] !== UPLOAD_ERR_NO_FILE;

        $incidenciaActual = null;
        if ($idEntrada !== '' && $idValido) {
            $incidenciaActual = $modeloOperaciones->obtenerIncidenciaPorId((int)$idEntrada);
        }

        if (
            !$idCampanaValido
            || !$idValido
            || !$tipoIncidenciaValido
            || !is_string($actividad)
            || !in_array($actividad, $actividadesPermitidas, true)
            || $descripcion === ''
        ) {
            $mensaje = 'Completa una campaña, actividad, tipo de incidencia y descripción válidos.';
        } elseif ($idEntrada !== '' && !$incidenciaActual) {
            $mensaje = 'No se encontró la incidencia para actualizar.';
        } elseif (!$modeloOperaciones->campanaOperativa((int)$idCampanaEntrada)) {
            $mensaje = 'La campaña seleccionada no existe o está cancelada.';
        } else {
            $archivoGuardado = null;
            $rutaFotoNueva = null;
            try {
                if ($seEnvioFoto) {
                    [$archivoGuardado, $rutaFotoNueva] = $guardarFotoIncidencia($archivo);
                }

                if ($idEntrada === '') {
                    $guardado = $modeloOperaciones->registrarIncidencia(
                        (int)$idCampanaEntrada,
                        $actividad,
                        $descripcion,
                        (int)$idTipoIncidenciaEntrada,
                        $rutaFotoNueva
                    );
                } else {
                    $guardado = $modeloOperaciones->actualizarIncidencia(
                        (int)$idEntrada,
                        (int)$idCampanaEntrada,
                        $actividad,
                        $descripcion,
                        (int)$idTipoIncidenciaEntrada,
                        $rutaFotoNueva
                    );
                }

                if (!$guardado) {
                    throw new RuntimeException('No se pudo guardar la incidencia. Verifica que la campaña exista.');
                }

                $mensaje = 'Incidencia guardada correctamente.';
                // Si se reemplazó la fotografía, se borra la anterior del disco.
                if ($rutaFotoNueva !== null && $incidenciaActual) {
                    if (!$borrarFotoIncidencia($incidenciaActual['RutaFoto'] ?? null)) {
                        $mensaje = 'La incidencia se guardó, pero no se pudo borrar su fotografía anterior.';
                    }
                }
            } catch (mysqli_sql_exception $e) {
                if ($archivoGuardado !== null && is_file($archivoGuardado)) {
                    unlink($archivoGuardado);
                }
                $mensaje = 'Error de base de datos al guardar la incidencia.';
            } catch (RuntimeException $e) {
                if ($archivoGuardado !== null && is_file($archivoGuardado)) {
                    unlink($archivoGuardado);
                }
                $mensaje = $e->getMessage();
            }

            // Si algo falló al editar, se mantiene abierto el formulario de edición.
            if ($mensaje !== 'Incidencia guardada correctamente.' && $incidenciaActual) {
                $incidenciaEditar = $incidenciaActual;
            }
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
