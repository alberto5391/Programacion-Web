<?php
if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['rol'], ['Administrador', 'Supervisor', 'Tecnico'], true)) {
    header("Location: index.php?modulo=login");
    exit();
}

require_once MODEL_PATH . 'admin/model.solicitante.php';
$modeloSolicitante = new ModelSolicitante($conexion);
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? null) === 'eliminar') {
    $idEntrada = $_POST['id'] ?? '';
    if (is_string($idEntrada) && ctype_digit($idEntrada) && (int)$idEntrada > 0) {
        $mensaje = $modeloSolicitante->eliminarSolicitante((int)$idEntrada)
            ? "<div class='alert alert-success'>Solicitante eliminado.</div>"
            : "<div class='alert alert-danger'>No se encontró el solicitante.</div>";
    } else {
        $mensaje = "<div class='alert alert-danger'>El solicitante seleccionado no es válido.</div>";
    }
}

$solicitanteEditar = null;
if (($_GET['accion'] ?? '') === 'editar' && isset($_GET['id'])) {
    $idEntrada = $_GET['id'];
    if (is_string($idEntrada) && ctype_digit($idEntrada) && (int)$idEntrada > 0) {
        $solicitanteEditar = $modeloSolicitante->obtenerSolicitantePorId((int)$idEntrada);
    }
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && in_array($_POST['accion'] ?? null, ['guardar_solicitante', 'registrar_solicitante'], true)
) {
    $tipo = $_POST['tipo_origen'] ?? null;
    $nombre = $_POST['nombre_solicitante'] ?? null;
    $contacto = $_POST['telefono_solicitante'] ?? null;
    $descripcion = $_POST['descripcion_solicitud'] ?? null;
    $estado = $_POST['estado_solicitud'] ?? null;
    $idEntrada = $_POST['id_solicitante'] ?? '';
    $idValido = $idEntrada === ''
        || (is_string($idEntrada) && ctype_digit($idEntrada) && (int)$idEntrada > 0);
    $tiposPermitidos = ['Reporte ciudadano', 'Cliente privado', 'Programa de salud público', 'Institución', 'Solicitud interna', 'Otro'];

    if (
        !$idValido
        || !is_string($tipo) || !in_array($tipo, $tiposPermitidos, true)
        || !is_string($nombre) || trim($nombre) === ''
        || !is_string($contacto) || trim($contacto) === '' || strlen(trim($contacto)) > 20
        || !is_string($descripcion) || trim($descripcion) === ''
        || !is_string($estado) || !in_array($estado, ['Activo', 'Inactivo'], true)
    ) {
        $mensaje = "<div class='alert alert-danger'>Completa los datos, usa un teléfono de hasta 20 caracteres y selecciona un tipo y estado válidos.</div>";
    } else {
        try {
            $registrado = $idEntrada === ''
                ? $modeloSolicitante->registrarSolicitante(
                    $tipo,
                    trim($nombre),
                    trim($contacto),
                    trim($descripcion),
                    $estado
                )
                : $modeloSolicitante->actualizarSolicitante(
                    (int)$idEntrada,
                    $tipo,
                    trim($nombre),
                    trim($contacto),
                    trim($descripcion),
                    $estado
                );
            if (!$registrado) {
                throw new RuntimeException('No se pudo guardar el solicitante.');
            }
            $mensaje = $idEntrada === ''
                ? "<div class='alert alert-success'>Solicitante registrado correctamente. Ya puedes asociarlo a una campaña.</div>"
                : "<div class='alert alert-success'>Solicitante actualizado correctamente.</div>";
            $solicitanteEditar = null;
        } catch (mysqli_sql_exception $e) {
            $mensaje = "<div class='alert alert-danger'>Error de base de datos al registrar el solicitante.</div>";
        } catch (RuntimeException $e) {
            $mensaje = "<div class='alert alert-danger'>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</div>";
        }
    }
}

$resultadoSolicitantes = $modeloSolicitante->obtenerSolicitantes();
require_once VIEW_PATH . 'admin/solicitantes.php';
?>