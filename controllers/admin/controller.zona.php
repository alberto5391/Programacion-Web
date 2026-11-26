<?php
// 1. Validar sesión integrada
if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['rol'], ['Administrador', 'Supervisor', 'Tecnico'], true)) {
    header("Location: index.php?modulo=login");
    exit();
}

// 2. Cargar el modelo de Zona
require_once MODEL_PATH . 'admin/model.zona.php';
$modeloZona = new ModelZona($conexion);

$mensaje = "";
$esEliminacion = $_SERVER["REQUEST_METHOD"] === "POST" && ($_POST['accion'] ?? null) === 'eliminar';

// 3. LÓGICA DE EDICIÓN (GET)
$zonaEditar = null;
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id_get = intval($_GET['id']);
    
    if ($_GET['accion'] === 'editar') {
        $zonaEditar = $modeloZona->obtenerZonaPorId($id_get);
    }
}

if ($esEliminacion) {
    $idEliminar = $_POST['id'] ?? null;
    if (!is_string($idEliminar) || !ctype_digit($idEliminar) || (int)$idEliminar < 1) {
        $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>La zona seleccionada no es válida.</div>";
    } else {
        try {
            $modeloZona->eliminarZona((int)$idEliminar);
            $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Zona urbana eliminada con éxito.</div>";
        } catch (mysqli_sql_exception $e) {
            // Código 1451: Error de restricción de llave foránea (ON DELETE RESTRICT)
            if ($e->getCode() == 1451) {
                $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error: No se puede eliminar esta zona porque ya tiene campañas asignadas. Te sugerimos cambiar su estado a 'Inactiva'.</div>";
            } else {
                $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error al eliminar la zona: " . $e->getMessage() . "</div>";
            }
        }
    }
}

// 4. LÓGICA DE REGISTRO O ACTUALIZACIÓN (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$esEliminacion) {
    $codigo_zona = trim($_POST['codigo_zona']);
    $municipio = trim($_POST['municipio']);
    $nombre_zona = trim($_POST['nombre_zona']);
    $referencias = trim($_POST['referencias']);
    $estado = isset($_POST['estado']) ? trim($_POST['estado']) : 'Activa';
    $latitudEntrada = $_POST['latitud'] ?? null;
    $longitudEntrada = $_POST['longitud'] ?? null;
    $coordenadasVacias = $latitudEntrada === '' && $longitudEntrada === '';
    $coordenadasValidas = is_string($latitudEntrada)
        && is_string($longitudEntrada)
        && (
            $coordenadasVacias
            || ModelZona::coordenadasValidas($latitudEntrada, $longitudEntrada)
        );

    if (!$coordenadasValidas) {
        $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Las coordenadas deben estar vacías o incluir una latitud entre -90 y 90 y una longitud entre -180 y 180.</div>";
    } else {
        $latitud = $coordenadasVacias ? null : (float)$latitudEntrada;
        $longitud = $coordenadasVacias ? null : (float)$longitudEntrada;

        try {
            if (!empty($_POST['id_zona'])) {
                $id_zona = intval($_POST['id_zona']);
                $modeloZona->actualizarZona($id_zona, $codigo_zona, $nombre_zona, $municipio, $referencias, $latitud, $longitud, $estado);
                $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Zona urbana actualizada con éxito.</div>";
                $zonaEditar = null;
            } else {
                $modeloZona->registrarZona($codigo_zona, $nombre_zona, $municipio, $referencias, $latitud, $longitud);
                $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Zona urbana registrada con éxito.</div>";
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error: El Código de Zona '{$codigo_zona}' ya está registrado. Usa uno diferente.</div>";
            } else {
                $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error de base de datos al guardar la zona.</div>";
            }
        }
    }
}

// 5. LÓGICA DE CONSULTA (LECTURA)
$resultadoZonas = $modeloZona->obtenerZonas();

// 6. CARGAR LA VISTA
require_once VIEW_PATH . 'admin/zonas.php';
?>