<?php
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: index.php?modulo=login");
    exit();
}

require_once MODEL_PATH . 'admin/model.solicitante.php';
$modeloSolicitante = new ModelSolicitante($conexion);
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? null) === 'registrar_solicitante') {
    $tipo = $_POST['tipo_origen'] ?? null;
    $nombre = $_POST['nombre_solicitante'] ?? null;
    $contacto = $_POST['telefono_solicitante'] ?? null;
    $descripcion = $_POST['descripcion_solicitud'] ?? null;
    $estado = $_POST['estado_solicitud'] ?? null;
    $tiposPermitidos = ['Reporte ciudadano', 'Cliente privado', 'Programa de salud público', 'Institución', 'Solicitud interna', 'Otro'];

    if (
        !is_string($tipo) || !in_array($tipo, $tiposPermitidos, true)
        || !is_string($nombre) || trim($nombre) === ''
        || !is_string($contacto) || trim($contacto) === '' || strlen(trim($contacto)) > 20
        || !is_string($descripcion) || trim($descripcion) === ''
        || !is_string($estado) || !in_array($estado, ['Activo', 'Inactivo'], true)
    ) {
        $mensaje = "<div class='alert alert-danger'>Completa los datos, usa un teléfono de hasta 20 caracteres y selecciona un tipo y estado válidos.</div>";
    } else {
        try {
            $registrado = $modeloSolicitante->registrarSolicitante(
                $tipo,
                trim($nombre),
                trim($contacto),
                trim($descripcion),
                $estado
            );
            if (!$registrado) {
                throw new RuntimeException('No se pudo guardar el solicitante.');
            }
            $mensaje = "<div class='alert alert-success'>Solicitante registrado correctamente. Ya puedes asociarlo a una campaña.</div>";
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