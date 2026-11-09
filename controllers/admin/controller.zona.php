<?php
// 1. Validar sesión integrada
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: index.php?modulo=login");
    exit();
}

// 2. Cargar el modelo de Zona
require_once MODEL_PATH . 'admin/model.zona.php';
$modeloZona = new ModelZona($conexion);

$mensaje = "";

// 3. LÓGICA DE EDICIÓN (GET)
// 3. LÓGICA DE EDICIÓN O ELIMINACIÓN (GET)
$zonaEditar = null;
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id_get = intval($_GET['id']);
    
    if ($_GET['accion'] === 'editar') {
        $zonaEditar = $modeloZona->obtenerZonaPorId($id_get);
    } elseif ($_GET['accion'] === 'eliminar') {
        try {
            $modeloZona->eliminarZona($id_get);
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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo_zona = trim($_POST['codigo_zona']);
    $municipio = trim($_POST['municipio']);
    $nombre_zona = trim($_POST['nombre_zona']);
    $referencias = trim($_POST['referencias']);
    $estado = isset($_POST['estado']) ? trim($_POST['estado']) : 'Activa';

    try {
        if (!empty($_POST['id_zona'])) {
            // Es una ACTUALIZACIÓN
            $id_zona = intval($_POST['id_zona']);
            $modeloZona->actualizarZona($id_zona, $codigo_zona, $nombre_zona, $municipio, $referencias, $estado);
            $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Zona urbana actualizada con éxito.</div>";
            $zonaEditar = null; // Limpiar el formulario después de actualizar
        } else {
            // Es un REGISTRO NUEVO
            $modeloZona->registrarZona($codigo_zona, $nombre_zona, $municipio, $referencias);
            $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Zona urbana registrada con éxito.</div>";
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error: El Código de Zona '{$codigo_zona}' ya está registrado. Usa uno diferente.</div>";
        } else {
            $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error de base de datos: " . $e->getMessage() . "</div>";
        }
    }
}

// 5. LÓGICA DE CONSULTA (LECTURA)
$resultadoZonas = $modeloZona->obtenerZonas();

// 6. CARGAR LA VISTA
require_once VIEW_PATH . 'admin/zonas.php';
?>