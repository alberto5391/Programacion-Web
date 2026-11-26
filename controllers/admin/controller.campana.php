<?php

if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['rol'], ['Administrador', 'Supervisor', 'Tecnico'], true)) {
    header("Location: index.php?modulo=login");
    exit();
}
// 1. Validar sesión integrada
/*if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: index.php?modulo=login");
    exit();
}*/

// 2. Cargar los modelos necesarios
require_once MODEL_PATH . 'admin/model.campana.php';
require_once MODEL_PATH . 'admin/model.zona.php'; 
require_once MODEL_PATH . 'admin/model.solicitante.php';
require_once MODEL_PATH . 'admin/model.evidencia.php';

$modeloCampana = new ModelCampana($conexion);
$modeloZona = new ModelZona($conexion);
$modeloSolicitante = new ModelSolicitante($conexion);
$modeloEvidencia = new ModelEvidencia($conexion);

$mensaje = "";
$esEliminacion = $_SERVER["REQUEST_METHOD"] === "POST" && ($_POST['accion'] ?? null) === 'eliminar';

// 3. LÓGICA DE EDICIÓN (GET)
$campanaEditar = null;
$solicitantesSeleccionados = [];
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id_get = intval($_GET['id']);
    
    if ($_GET['accion'] === 'editar') {
        $campanaEditar = $modeloCampana->obtenerCampanaPorId($id_get);
        if ($campanaEditar) {
            $resultadoSolicitantesCampana = $modeloSolicitante->obtenerSolicitantesPorCampana($id_get);
            while ($solicitanteVinculado = $resultadoSolicitantesCampana->fetch_assoc()) {
                $solicitantesSeleccionados[(int)$solicitanteVinculado['IdSolicitante']] = $solicitanteVinculado['FechaSolicitud'];
            }
        }
    }
}

if ($esEliminacion) {
    $idEliminar = $_POST['id'] ?? null;
    if (!is_string($idEliminar) || !ctype_digit($idEliminar) || (int)$idEliminar < 1) {
        $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>La campaña seleccionada no es válida.</div>";
    } else {
        try {
            $resultadoRutas = $modeloEvidencia->obtenerRutasPorCampana((int)$idEliminar);
            $rutasEvidencia = [];
            while ($evidencia = $resultadoRutas->fetch_assoc()) {
                $rutasEvidencia[] = $evidencia['RutaArchivo'];
            }

            if (!$modeloCampana->eliminarCampana((int)$idEliminar)) {
                throw new RuntimeException('No se pudo eliminar la campaña.');
            }
            $archivosNoEliminados = 0;
            foreach ($rutasEvidencia as $rutaEvidencia) {
                if (!is_string($rutaEvidencia) || !preg_match('#^resources/uploads/evidencias/[a-f0-9]{32}\.(jpg|png|webp)$#D', $rutaEvidencia)) {
                    $archivosNoEliminados++;
                    error_log('Se encontró una ruta de evidencia con formato inesperado al eliminar una campaña.');
                    continue;
                }

                $nombreArchivo = substr($rutaEvidencia, strlen('resources/uploads/evidencias/'));
                $rutaAbsoluta = RESOURCE_PATH . 'uploads' . DIRECTORY_SEPARATOR . 'evidencias' . DIRECTORY_SEPARATOR . $nombreArchivo;
                if (is_file($rutaAbsoluta) && !unlink($rutaAbsoluta)) {
                    $archivosNoEliminados++;
                    error_log('No se pudo retirar una fotografía asociada a una campaña eliminada.');
                }
            }

            if ($archivosNoEliminados > 0) {
                $mensaje = "<div style='color: #856404; background-color: #fff3cd; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>La campaña se eliminó, pero no se pudieron retirar todas sus evidencias del almacenamiento. Revisa el registro del servidor.</div>";
            } else {
                $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Campaña y evidencias eliminadas correctamente.</div>";
            }
        } catch (mysqli_sql_exception $e) {
            $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error al eliminar la campaña. Revisa si aún tiene registros operativos vinculados.</div>";
        } catch (RuntimeException $e) {
            $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>No se pudo eliminar la campaña.</div>";
        }
    }
}

// 4. LÓGICA DE REGISTRO O ACTUALIZACIÓN (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$esEliminacion) {
    $codigo_campana = trim($_POST['codigo_campana']);
    $nombre_campana = trim($_POST['nombre_campana']);
    $id_zona = $_POST['id_zona'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : NULL;
    $estado = $_POST['estado_campana'];
    $idCampanaEntrada = $_POST['id_campana'] ?? '';
    $editandoCampana = $idCampanaEntrada !== '';
    $idCampanaValido = !$editandoCampana
        || (is_string($idCampanaEntrada) && ctype_digit($idCampanaEntrada) && (int)$idCampanaEntrada > 0);
    if ($editandoCampana && $idCampanaValido) {
        $campanaEditar = [
            'IdCampana' => (int)$idCampanaEntrada,
            'CodigoCampana' => $codigo_campana,
            'NombreCampana' => $nombre_campana,
            'IdZona' => $id_zona,
            'FechaInicio' => $fecha_inicio,
            'FechaFin' => $fecha_fin,
            'Estado' => $estado
        ];
    }
    $idSolicitantesEntrada = $_POST['solicitantes'] ?? [];
    $fechasSolicitantesEntrada = $_POST['fechas_solicitud'] ?? [];
    $solicitantesCampana = [];
    $solicitantesValidos = is_array($idSolicitantesEntrada);
    if ($solicitantesValidos && is_array($fechasSolicitantesEntrada)) {
        foreach ($idSolicitantesEntrada as $idSolicitanteEntrada) {
            if (!is_string($idSolicitanteEntrada) || !ctype_digit($idSolicitanteEntrada) || (int)$idSolicitanteEntrada < 1) {
                $solicitantesValidos = false;
                break;
            }
            $idSolicitante = (int)$idSolicitanteEntrada;
            $fechaSolicitanteEntrada = $fechasSolicitantesEntrada[$idSolicitante] ?? null;
            $fechaSolicitante = is_string($fechaSolicitanteEntrada)
                ? DateTime::createFromFormat('!Y-m-d', $fechaSolicitanteEntrada)
                : false;
            if (!$fechaSolicitante || $fechaSolicitante->format('Y-m-d') !== $fechaSolicitanteEntrada) {
                $solicitantesValidos = false;
                break;
            }
            $solicitantesCampana[$idSolicitante] = $fechaSolicitanteEntrada;
        }
    } elseif (!is_array($fechasSolicitantesEntrada)) {
        $solicitantesValidos = false;
    }
    $solicitantesSeleccionados = $solicitantesCampana;

    if (!$solicitantesValidos || !$idCampanaValido) {
        $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Revisa el ID de campaña, los solicitantes seleccionados y sus fechas de solicitud.</div>";
    } else {
        $transaccionIniciada = false;
        try {
            $conexion->begin_transaction();
            $transaccionIniciada = true;

            if ($editandoCampana) {
                $id_campana = (int)$idCampanaEntrada;
                if (!$modeloCampana->actualizarCampana($id_campana, $codigo_campana, $nombre_campana, $id_zona, $fecha_inicio, $fecha_fin, $estado)) {
                    throw new RuntimeException('No se pudo actualizar la campaña.');
                }
            } else {
                $id_campana = $modeloCampana->registrarCampana($codigo_campana, $nombre_campana, $id_zona, $fecha_inicio, $fecha_fin, $estado);
                if (!$id_campana) {
                    throw new RuntimeException('No se pudo registrar la campaña.');
                }
            }

            if (!$modeloSolicitante->reemplazarSolicitantesCampana((int)$id_campana, $solicitantesCampana)) {
                throw new RuntimeException('No se pudieron guardar los solicitantes de la campaña.');
            }

            $conexion->commit();
            $transaccionIniciada = false;
            $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Campaña y solicitantes guardados correctamente.</div>";
            $campanaEditar = null;
            $solicitantesSeleccionados = [];
        } catch (mysqli_sql_exception $e) {
            if ($transaccionIniciada) {
                $conexion->rollback();
            }
            if ($e->getCode() == 1062) {
                $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error: El Código de Campaña '{$codigo_campana}' ya está en uso. Usa uno diferente.</div>";
            } else {
                $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error de base de datos al guardar la campaña y sus solicitantes.</div>";
            }
        } catch (RuntimeException $e) {
            if ($transaccionIniciada) {
                $conexion->rollback();
            }
            $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</div>";
        }
    }
}

// 5. OBTENER DATOS PARA LA VISTA
// Solicitamos las zonas para el formulario
$resultadoZonas = $modeloZona->obtenerZonas(); 
$resultadoSolicitantes = $modeloSolicitante->obtenerSolicitantes();
// Solicitamos las campañas para la tabla
$resultadoCampanas = $modeloCampana->obtenerCampanas();

// 6. CARGAR LA VISTA
require_once VIEW_PATH . 'admin/campanas.php';
?>