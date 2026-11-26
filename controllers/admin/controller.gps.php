<?php
if (
    isset($_SESSION['id_usuario'])
    && in_array($_SESSION['rol'] ?? '', ['Administrador', 'Supervisor'], true)
) {
    header("Location: index.php?modulo=zonas");
    exit();
}

if (!isset($_SESSION['id_usuario']) || ($_SESSION['rol'] ?? '') !== 'Tecnico') {
    header("Location: index.php?modulo=login");
    exit();
}

require_once MODEL_PATH . 'admin/model.zona.php';
$modeloZona = new ModelZona($conexion);
$mensaje = '';
$zonaSeleccionada = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? null) === 'actualizar_coordenadas') {
    $idZonaEntrada = $_POST['id_zona'] ?? null;
    $latitudEntrada = $_POST['latitud'] ?? null;
    $longitudEntrada = $_POST['longitud'] ?? null;

    $datosValidos = is_string($idZonaEntrada)
        && ctype_digit($idZonaEntrada)
        && (int)$idZonaEntrada > 0
        && is_string($latitudEntrada)
        && is_string($longitudEntrada)
        && ModelZona::coordenadasValidas($latitudEntrada, $longitudEntrada);

    if (!$datosValidos) {
        $mensaje = "<div class='alert alert-danger'>Selecciona una zona e ingresa coordenadas válidas (latitud: -90 a 90; longitud: -180 a 180).</div>";
    } else {
        $zonaSeleccionada = (int)$idZonaEntrada;
        $zona = $modeloZona->obtenerZonaPorId($zonaSeleccionada);
        if (!$zona || $zona['Estado'] !== 'Activa') {
            $mensaje = "<div class='alert alert-danger'>Selecciona una zona activa válida.</div>";
        } else {
            try {
                $guardado = $modeloZona->actualizarCoordenadas(
                    $zonaSeleccionada,
                    (float)$latitudEntrada,
                    (float)$longitudEntrada
                );
                $mensaje = $guardado
                    ? "<div class='alert alert-success'>Coordenadas registradas correctamente en la zona.</div>"
                    : "<div class='alert alert-warning'>Esta zona ya tiene coordenadas. Solo Administración o Supervisión puede modificarlas.</div>";
            } catch (mysqli_sql_exception $e) {
                $mensaje = "<div class='alert alert-danger'>No se pudieron guardar las coordenadas. Inténtalo nuevamente.</div>";
            }
        }
    }
}

$resultadoZonas = $modeloZona->obtenerZonasSinCoordenadasActivas();
require_once VIEW_PATH . 'admin/coordenadas_gps.php';
?>