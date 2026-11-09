<?php

if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['rol'], ['Administrador', 'Supervisor'])) {
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

$modeloCampana = new ModelCampana($conexion);
$modeloZona = new ModelZona($conexion);

$mensaje = "";

// 3. LÓGICA DE EDICIÓN O ELIMINACIÓN (GET)
$campanaEditar = null;
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id_get = intval($_GET['id']);
    
    if ($_GET['accion'] === 'editar') {
        $campanaEditar = $modeloCampana->obtenerCampanaPorId($id_get);
    } elseif ($_GET['accion'] === 'eliminar') {
        try {
            $modeloCampana->eliminarCampana($id_get);
            $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Campaña eliminada con éxito. (Nota: Cualquier registro asociado también ha sido eliminado).</div>";
        } catch (mysqli_sql_exception $e) {
            $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error al eliminar la campaña: " . $e->getMessage() . "</div>";
        }
    }
}

// 4. LÓGICA DE REGISTRO O ACTUALIZACIÓN (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo_campana = trim($_POST['codigo_campana']);
    $nombre_campana = trim($_POST['nombre_campana']);
    $id_zona = $_POST['id_zona'];
    $fecha_inicio = $_POST['fecha_inicio'];
    // La fecha fin puede estar vacía
    $fecha_fin = !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : NULL;
    $estado = $_POST['estado_campana'];

    try {
        if (!empty($_POST['id_campana'])) {
            // Es una ACTUALIZACIÓN
            $id_campana = intval($_POST['id_campana']);
            $modeloCampana->actualizarCampana($id_campana, $codigo_campana, $nombre_campana, $id_zona, $fecha_inicio, $fecha_fin, $estado);
            $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Campaña actualizada con éxito.</div>";
            $campanaEditar = null; // Limpiar formulario
        } else {
            // Es un REGISTRO NUEVO
            $modeloCampana->registrarCampana($codigo_campana, $nombre_campana, $id_zona, $fecha_inicio, $fecha_fin, $estado);
            $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Campaña registrada y programada con éxito.</div>";
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error: El Código de Campaña '{$codigo_campana}' ya está en uso. Usa uno diferente.</div>";
        } else {
            $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error de base de datos: " . $e->getMessage() . "</div>";
        }
    }
}

// 5. OBTENER DATOS PARA LA VISTA
// Solicitamos las zonas para el formulario
$resultadoZonas = $modeloZona->obtenerZonas(); 
// Solicitamos las campañas para la tabla
$resultadoCampanas = $modeloCampana->obtenerCampanas();

// 6. CARGAR LA VISTA
require_once VIEW_PATH . 'admin/campanas.php';
?>