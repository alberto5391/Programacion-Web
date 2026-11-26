<?php
if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['rol'], ['Administrador', 'Supervisor', 'Tecnico'], true)) {
    header("Location: index.php?modulo=login");
    exit();
}

require_once MODEL_PATH . 'admin/model.evidencia.php';
require_once MODEL_PATH . 'admin/model.campana.php';

$modeloEvidencia = new ModelEvidencia($conexion);
$modeloCampana = new ModelCampana($conexion);
$mensaje = '';
$idCampanaEntrada = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? ($_POST['id_campana'] ?? null)
    : ($_GET['id_campana'] ?? null);
$idCampanaSeleccionada = null;

if ($idCampanaEntrada !== null && $idCampanaEntrada !== '') {
    if (is_string($idCampanaEntrada) && ctype_digit($idCampanaEntrada) && (int)$idCampanaEntrada > 0) {
        $idCampanaSeleccionada = (int)$idCampanaEntrada;
    } else {
        $mensaje = "<div class='alert alert-danger'>La campaña seleccionada no es válida.</div>";
    }
}

$tiposPermitidos = ['Inspección', 'Aplicación', 'Seguimiento'];
$guardarArchivoEvidencia = static function ($archivo) {
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

    $directorioSubida = RESOURCE_PATH . 'uploads' . DIRECTORY_SEPARATOR . 'evidencias';
    if (!is_dir($directorioSubida) && !mkdir($directorioSubida, 0750, true) && !is_dir($directorioSubida)) {
        throw new RuntimeException('No se pudo preparar el almacenamiento de evidencias.');
    }

    $nombreArchivo = bin2hex(random_bytes(16)) . '.' . $extensionesPermitidas[$mime];
    $rutaAbsoluta = $directorioSubida . DIRECTORY_SEPARATOR . $nombreArchivo;
    if (!move_uploaded_file($archivo['tmp_name'], $rutaAbsoluta)) {
        throw new RuntimeException('No se pudo guardar la fotografía.');
    }

    return [
        $rutaAbsoluta,
        'resources/uploads/evidencias/' . $nombreArchivo
    ];
};
$evidenciaEditar = null;
if (($_GET['accion'] ?? '') === 'editar' && isset($_GET['id'])) {
    $idEvidenciaEntrada = $_GET['id'];
    if (is_string($idEvidenciaEntrada) && ctype_digit($idEvidenciaEntrada) && (int)$idEvidenciaEntrada > 0) {
        $evidenciaEditar = $modeloEvidencia->obtenerEvidenciaPorId((int)$idEvidenciaEntrada);
        if ($evidenciaEditar) {
            $idCampanaSeleccionada = (int)$evidenciaEditar['IdCampana'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? null) === 'actualizar_evidencia') {
    $idEvidenciaEntrada = $_POST['id_evidencia'] ?? '';
    $idCampanaEntrada = $_POST['id_campana'] ?? '';
    $idTipoAccionEntrada = $_POST['id_tipo_accion'] ?? '';
    $tipoActividad = $_POST['tipo_actividad'] ?? null;
    if (
        !is_string($idEvidenciaEntrada)
        || !ctype_digit($idEvidenciaEntrada)
        || (int)$idEvidenciaEntrada < 1
        || !is_string($idCampanaEntrada)
        || !ctype_digit($idCampanaEntrada)
        || (int)$idCampanaEntrada < 1
        || !is_string($idTipoAccionEntrada)
        || !ctype_digit($idTipoAccionEntrada)
        || (int)$idTipoAccionEntrada < 1
        || !is_string($tipoActividad)
        || !in_array($tipoActividad, $tiposPermitidos, true)
    ) {
        $mensaje = "<div class='alert alert-danger'>La evidencia, campaña o tipos de acción/actividad no son válidos.</div>";
    } else {
        $evidenciaActual = $modeloEvidencia->obtenerEvidenciaPorId((int)$idEvidenciaEntrada);
        $campanaNueva = $modeloCampana->obtenerCampanaPorId((int)$idCampanaEntrada);
        $tipoAccionExiste = $modeloEvidencia->tipoAccionExiste((int)$idTipoAccionEntrada);
        if ($evidenciaActual) {
            $evidenciaEditar = $evidenciaActual;
            $evidenciaEditar['IdCampana'] = (int)$idCampanaEntrada;
            $evidenciaEditar['idTipoAccion'] = (int)$idTipoAccionEntrada;
            $evidenciaEditar['TipoActividad'] = $tipoActividad;
        }
        $archivo = $_FILES['foto'] ?? null;
        $archivoGuardado = null;
        $rutaRelativaNueva = null;
        if (!$evidenciaActual) {
            $mensaje = "<div class='alert alert-danger'>No se encontró la evidencia para actualizar.</div>";
        } elseif (!$campanaNueva) {
            $mensaje = "<div class='alert alert-danger'>La campaña seleccionada no existe.</div>";
        } elseif (!$tipoAccionExiste) {
            $mensaje = "<div class='alert alert-danger'>El tipo de acción seleccionado no existe.</div>";
        } elseif (
            $archivo !== null
            && (!is_array($archivo) || !isset($archivo['error']) || !is_int($archivo['error']) || $archivo['error'] !== UPLOAD_ERR_NO_FILE)
        ) {
            try {
                [$archivoGuardado, $rutaRelativaNueva] = $guardarArchivoEvidencia($archivo);
            } catch (RuntimeException $e) {
                $mensaje = "<div class='alert alert-danger'>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</div>";
            }
        }

        if ($evidenciaActual && $campanaNueva && $tipoAccionExiste && $mensaje === '') {
            try {
                $actualizado = $modeloEvidencia->actualizarEvidencia(
                    (int)$idEvidenciaEntrada,
                    (int)$idCampanaEntrada,
                    (int)$idTipoAccionEntrada,
                    $tipoActividad,
                    $rutaRelativaNueva
                );
                if (!$actualizado) {
                    throw new RuntimeException('No se pudo actualizar la evidencia.');
                }

                $idCampanaSeleccionada = (int)$idCampanaEntrada;
                $evidenciaEditar = null;
                $mensaje = "<div class='alert alert-success'>Evidencia actualizada correctamente.</div>";
                if ($rutaRelativaNueva !== null) {
                    $rutaAnterior = $evidenciaActual['RutaArchivo'];
                    if (
                        is_string($rutaAnterior)
                        && preg_match('#^resources/uploads/evidencias/[a-f0-9]{32}\.(jpg|png|webp)$#D', $rutaAnterior)
                    ) {
                        $nombreAnterior = substr($rutaAnterior, strlen('resources/uploads/evidencias/'));
                        $rutaAbsolutaAnterior = RESOURCE_PATH . 'uploads' . DIRECTORY_SEPARATOR . 'evidencias' . DIRECTORY_SEPARATOR . $nombreAnterior;
                        if (is_file($rutaAbsolutaAnterior) && !unlink($rutaAbsolutaAnterior)) {
                            error_log('No se pudo retirar el archivo anterior de una evidencia actualizada.');
                            $mensaje = "<div class='alert alert-warning'>La evidencia se actualizó, pero no se pudo borrar su imagen anterior.</div>";
                        }
                    }
                }
            } catch (mysqli_sql_exception $e) {
                if ($archivoGuardado !== null && is_file($archivoGuardado) && !unlink($archivoGuardado)) {
                    error_log('No se pudo retirar una imagen cuyo cambio en la base de datos falló.');
                }
                $mensaje = "<div class='alert alert-danger'>Error de base de datos al actualizar la evidencia.</div>";
            } catch (RuntimeException $e) {
                if ($archivoGuardado !== null && is_file($archivoGuardado) && !unlink($archivoGuardado)) {
                    error_log('No se pudo retirar una imagen después de un error al actualizar la evidencia.');
                }
                $mensaje = "<div class='alert alert-danger'>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</div>";
            }
        } else {
            if ($archivoGuardado !== null && is_file($archivoGuardado) && $mensaje !== '' && !unlink($archivoGuardado)) {
                error_log('No se pudo retirar una imagen de evidencia después de validar el formulario.');
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? null) === 'eliminar') {
    $idEvidenciaEntrada = $_POST['id'] ?? '';
    if (!is_string($idEvidenciaEntrada) || !ctype_digit($idEvidenciaEntrada) || (int)$idEvidenciaEntrada < 1) {
        $mensaje = "<div class='alert alert-danger'>La evidencia seleccionada no es válida.</div>";
    } else {
        $evidenciaEliminar = $modeloEvidencia->obtenerEvidenciaPorId((int)$idEvidenciaEntrada);
        if (!$evidenciaEliminar || !$modeloEvidencia->eliminarEvidencia((int)$idEvidenciaEntrada)) {
            $mensaje = "<div class='alert alert-danger'>No se encontró la evidencia para eliminar.</div>";
        } else {
            $idCampanaSeleccionada = (int)$evidenciaEliminar['IdCampana'];
            $ruta = $evidenciaEliminar['RutaArchivo'];
            if (
                is_string($ruta)
                && preg_match('#^resources/uploads/evidencias/[a-f0-9]{32}\.(jpg|png|webp)$#D', $ruta)
            ) {
                $nombreArchivo = substr($ruta, strlen('resources/uploads/evidencias/'));
                $rutaAbsoluta = RESOURCE_PATH . 'uploads' . DIRECTORY_SEPARATOR . 'evidencias' . DIRECTORY_SEPARATOR . $nombreArchivo;
                if (is_file($rutaAbsoluta) && !unlink($rutaAbsoluta)) {
                    error_log('No se pudo eliminar el archivo de una evidencia borrada.');
                    $mensaje = "<div class='alert alert-warning'>La evidencia se quitó de la base de datos, pero no se pudo borrar su archivo.</div>";
                } else {
                    $mensaje = "<div class='alert alert-success'>Evidencia eliminada correctamente.</div>";
                }
            } else {
                error_log('Se encontró una ruta inesperada al borrar una evidencia.');
                $mensaje = "<div class='alert alert-warning'>La evidencia se quitó de la base de datos, pero su archivo no se borró por tener una ruta no reconocida.</div>";
            }
        }
    }
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['accion'] ?? null) === 'guardar_evidencia'
    && $idCampanaSeleccionada === null
    && $mensaje === ''
) {
    $mensaje = "<div class='alert alert-danger'>Selecciona una campaña válida antes de subir la evidencia.</div>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? null) === 'guardar_evidencia' && $idCampanaSeleccionada !== null) {
    $campana = $modeloCampana->obtenerCampanaPorId($idCampanaSeleccionada);
    $idTipoAccionEntrada = $_POST['id_tipo_accion'] ?? null;
    $tipoActividad = $_POST['tipo_actividad'] ?? null;
    $archivo = $_FILES['foto'] ?? null;
    $archivoGuardado = null;

    if (!$campana) {
        $mensaje = "<div class='alert alert-danger'>La campaña indicada no existe.</div>";
    } elseif (
        !is_string($idTipoAccionEntrada)
        || !ctype_digit($idTipoAccionEntrada)
        || (int)$idTipoAccionEntrada < 1
        || !$modeloEvidencia->tipoAccionExiste((int)$idTipoAccionEntrada)
    ) {
        $mensaje = "<div class='alert alert-danger'>Selecciona un tipo de acción válido.</div>";
    } elseif (!is_string($tipoActividad) || !in_array($tipoActividad, $tiposPermitidos, true)) {
        $mensaje = "<div class='alert alert-danger'>Selecciona un tipo de actividad válido.</div>";
    } elseif (
        !is_array($archivo)
        || !isset($archivo['error'], $archivo['size'], $archivo['tmp_name'])
        || !is_int($archivo['error'])
        || !is_numeric($archivo['size'])
        || !is_string($archivo['tmp_name'])
        || $archivo['error'] !== UPLOAD_ERR_OK
    ) {
        $mensaje = "<div class='alert alert-danger'>No se recibió la fotografía correctamente. Verifica el archivo e inténtalo de nuevo.</div>";
    } elseif ((int)$archivo['size'] < 1 || (int)$archivo['size'] > 5 * 1024 * 1024 || !is_uploaded_file($archivo['tmp_name'])) {
        $mensaje = "<div class='alert alert-danger'>La fotografía debe pesar entre 1 byte y 5 MB.</div>";
    } else {
        try {
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

            set_error_handler(function ($severity, $message) {
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

            $directorioSubida = RESOURCE_PATH . 'uploads' . DIRECTORY_SEPARATOR . 'evidencias';
            if (!is_dir($directorioSubida) && !mkdir($directorioSubida, 0750, true) && !is_dir($directorioSubida)) {
                throw new RuntimeException('No se pudo preparar el almacenamiento de evidencias.');
            }

            $nombreArchivo = bin2hex(random_bytes(16)) . '.' . $extensionesPermitidas[$mime];
            $archivoGuardado = $directorioSubida . DIRECTORY_SEPARATOR . $nombreArchivo;
            if (!move_uploaded_file($archivo['tmp_name'], $archivoGuardado)) {
                $archivoGuardado = null;
                throw new RuntimeException('No se pudo guardar la fotografía.');
            }

            $rutaRelativa = 'resources/uploads/evidencias/' . $nombreArchivo;
            if (!$modeloEvidencia->registrarEvidencia(
                $idCampanaSeleccionada,
                (int)$idTipoAccionEntrada,
                $rutaRelativa,
                $tipoActividad
            )) {
                throw new RuntimeException('No se pudo registrar la evidencia en la campaña.');
            }
            $mensaje = "<div class='alert alert-success'>Evidencia registrada correctamente en la campaña.</div>";
        } catch (mysqli_sql_exception $e) {
            if ($archivoGuardado !== null && is_file($archivoGuardado) && !unlink($archivoGuardado)) {
                error_log('No se pudo retirar una evidencia cuyo registro en base de datos falló.');
            }
            $mensaje = "<div class='alert alert-danger'>Error de base de datos al registrar la evidencia.</div>";
        } catch (RuntimeException $e) {
            if ($archivoGuardado !== null && is_file($archivoGuardado) && !unlink($archivoGuardado)) {
                error_log('No se pudo retirar una evidencia después de un error al guardarla.');
            }
            $mensaje = "<div class='alert alert-danger'>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</div>";
        }
    }
}

if ($idCampanaSeleccionada !== null) {
    $campanaConsultada = $modeloCampana->obtenerCampanaPorId($idCampanaSeleccionada);
    if (!$campanaConsultada) {
        $mensaje = "<div class='alert alert-danger'>La campaña seleccionada no existe.</div>";
        $idCampanaSeleccionada = null;
    }
}

$resultadoCampanas = $modeloCampana->obtenerCampanas();
$campanas = $resultadoCampanas ? $resultadoCampanas->fetch_all(MYSQLI_ASSOC) : [];
$resultadoTiposAccion = $modeloEvidencia->obtenerTiposAccion();
$tiposAccion = $resultadoTiposAccion ? $resultadoTiposAccion->fetch_all(MYSQLI_ASSOC) : [];
$resultadoEvidencias = $modeloEvidencia->buscarTodasLasEvidencias();

require_once VIEW_PATH . 'admin/evidencias.php';
