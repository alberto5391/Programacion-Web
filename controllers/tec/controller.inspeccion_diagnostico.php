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

// --- Fotografía de la inspección o diagnóstico (mismo esquema que el módulo de evidencias) ---
// La imagen se guarda en resources/uploads/inspecciones/ y en la base de datos
// (tabla fotosinspeccion, una foto por registro) se guarda su ruta relativa.
$carpetaFotosInspeccion = 'resources/uploads/inspecciones/';

// Valida y guarda la fotografía. Devuelve [rutaAbsoluta, rutaRelativa].
$guardarFotoInspeccion = static function ($archivo) use ($carpetaFotosInspeccion) {
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

    $directorioSubida = RESOURCE_PATH . 'uploads' . DIRECTORY_SEPARATOR . 'inspecciones';
    if (!is_dir($directorioSubida) && !mkdir($directorioSubida, 0750, true) && !is_dir($directorioSubida)) {
        throw new RuntimeException('No se pudo preparar el almacenamiento de fotografías de inspección.');
    }

    // Nombre aleatorio para que no se pueda adivinar ni sobrescribir otro archivo.
    $nombreArchivo = bin2hex(random_bytes(16)) . '.' . $extensionesPermitidas[$mime];
    $rutaAbsoluta = $directorioSubida . DIRECTORY_SEPARATOR . $nombreArchivo;
    if (!move_uploaded_file($archivo['tmp_name'], $rutaAbsoluta)) {
        throw new RuntimeException('No se pudo guardar la fotografía.');
    }

    return [$rutaAbsoluta, $carpetaFotosInspeccion . $nombreArchivo];
};

// Borra del disco una fotografía de inspección. Solo acepta rutas generadas por este módulo.
$borrarFotoInspeccion = static function ($rutaRelativa) use ($carpetaFotosInspeccion) {
    if (
        !is_string($rutaRelativa)
        || !preg_match('#^resources/uploads/inspecciones/[a-f0-9]{32}\.(jpg|png|webp)$#D', $rutaRelativa)
    ) {
        return true;
    }
    $rutaAbsoluta = RESOURCE_PATH . 'uploads' . DIRECTORY_SEPARATOR . 'inspecciones'
        . DIRECTORY_SEPARATOR . substr($rutaRelativa, strlen($carpetaFotosInspeccion));
    if (is_file($rutaAbsoluta) && !unlink($rutaAbsoluta)) {
        error_log('No se pudo borrar la fotografía de una inspección.');
        return false;
    }
    return true;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $idEntrada = $_POST['id_accion'] ?? '';

    if ($accion === 'eliminar') {
        $idEliminar = $_POST['id'] ?? '';
        $tipoEliminar = $_POST['tipo_accion'] ?? '';
        $registroEliminar = is_string($idEliminar) && ctype_digit($idEliminar) && (int)$idEliminar > 0
            ? $modeloOperaciones->obtenerAccionInspeccionDiagnosticoPorId((int)$idEliminar)
            : null;
        if (
            $registroEliminar
            && is_string($tipoEliminar) && in_array($tipoEliminar, $tiposAccion, true)
            && $modeloOperaciones->eliminarAccionPorTipo((int)$idEliminar, $tipoEliminar)
        ) {
            // La fila de fotosinspeccion se borra sola (ON DELETE CASCADE); aquí se borra el archivo.
            $mensaje = $borrarFotoInspeccion($registroEliminar['RutaFoto'] ?? null)
                ? 'Registro eliminado.'
                : 'El registro se eliminó, pero no se pudo borrar su fotografía.';
        } else {
            $mensaje = 'No se encontró el registro para eliminar.';
        }
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
                    [$archivoGuardado, $rutaFotoNueva] = $guardarFotoInspeccion($archivo);
                }

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
                    $rutaFotoAnterior = $accionExistente['RutaFoto'] ?? null;
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

                if ($rutaFotoNueva !== null && !$modeloOperaciones->guardarFotoInspeccion($idAccion, $rutaFotoNueva)) {
                    throw new RuntimeException('No se pudo guardar la fotografía del registro.');
                }

                $conexion->commit();
                $transaccionIniciada = false;
                $mensaje = 'Registro guardado correctamente.';
                // Si se reemplazó la fotografía, se borra la anterior del disco.
                if ($rutaFotoNueva !== null && !$borrarFotoInspeccion($rutaFotoAnterior)) {
                    $mensaje = 'El registro se guardó, pero no se pudo borrar su fotografía anterior.';
                }
            } catch (mysqli_sql_exception $e) {
                if ($transaccionIniciada) {
                    $conexion->rollback();
                }
                if ($archivoGuardado !== null && is_file($archivoGuardado)) {
                    unlink($archivoGuardado);
                }
                error_log('Error al guardar una inspección o diagnóstico: ' . $e->getMessage());
                $mensaje = 'No se pudo guardar el registro. Verifica las referencias y vuelve a intentarlo.';
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