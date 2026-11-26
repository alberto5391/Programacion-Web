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
    $tipoActividad = $_POST['tipo_actividad'] ?? null;
    $tiposPermitidos = ['Inspección', 'Aplicación', 'Seguimiento'];
    $archivo = $_FILES['foto'] ?? null;
    $archivoGuardado = null;

    if (!$campana) {
        $mensaje = "<div class='alert alert-danger'>La campaña indicada no existe.</div>";
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
            if (!$modeloEvidencia->registrarEvidencia($idCampanaSeleccionada, $rutaRelativa, $tipoActividad)) {
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
$resultadoEvidencias = $idCampanaSeleccionada !== null
    ? $modeloEvidencia->buscarEvidenciasPorCampana($idCampanaSeleccionada)
    : null;

require_once VIEW_PATH . 'admin/evidencias.php';
