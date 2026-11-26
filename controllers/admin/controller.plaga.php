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

// 2. Cargar el modelo de Plagas
require_once MODEL_PATH . 'admin/model.plaga.php';
$modeloPlaga = new ModelPlaga($conexion);

$mensaje = "";
$esEliminacion = $_SERVER["REQUEST_METHOD"] === "POST" && ($_POST['accion'] ?? null) === 'eliminar';

// 3. LÓGICA DE EDICIÓN (GET)
$plagaEditar = null;
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id_get = intval($_GET['id']);
    
    if ($_GET['accion'] === 'editar') {
        $plagaEditar = $modeloPlaga->obtenerPlagaPorId($id_get);
    }
}

if ($esEliminacion) {
    $idEliminar = $_POST['id'] ?? null;
    if (!is_string($idEliminar) || !ctype_digit($idEliminar) || (int)$idEliminar < 1) {
        $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>La plaga seleccionada no es válida.</div>";
    } else {
        try {
            $modeloPlaga->eliminarPlaga((int)$idEliminar);
            $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Plaga eliminada con éxito del catálogo.</div>";
        } catch (mysqli_sql_exception $e) {
            // Código 1451: Error de restricción de llave foránea (ON DELETE RESTRICT)
            if ($e->getCode() == 1451) {
                $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error: No se puede eliminar esta plaga porque ya ha sido identificada y vinculada en acciones de campaña. Te sugerimos cambiar su estado a 'Inactiva'.</div>";
            } else {
                $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error al eliminar la plaga: " . $e->getMessage() . "</div>";
            }
        }
    }
}

// 4. LÓGICA DE REGISTRO O ACTUALIZACIÓN (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$esEliminacion) {
    $codigo_plaga = trim($_POST['codigo_plaga']);
    $nombre_comun = trim($_POST['nombre_plaga']);
    
    // Los campos opcionales pueden estar vacíos
    $nombre_cientifico = !empty(trim($_POST['nombre_cientifico'])) ? trim($_POST['nombre_cientifico']) : NULL;
    $descripcion = trim($_POST['descripcion']);
    $tratamiento = !empty(trim($_POST['tratamiento'])) ? trim($_POST['tratamiento']) : NULL;
    $estado = isset($_POST['estado']) ? trim($_POST['estado']) : 'Activa';

    try {
        if (!empty($_POST['id_plaga'])) {
            // Es una ACTUALIZACIÓN
            $id_plaga = intval($_POST['id_plaga']);
            $modeloPlaga->actualizarPlaga($id_plaga, $codigo_plaga, $nombre_comun, $nombre_cientifico, $descripcion, $tratamiento, $estado);
            $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Plaga actualizada con éxito en el catálogo.</div>";
            $plagaEditar = null; // Limpiar el formulario después de actualizar
        } else {
            // Es un REGISTRO NUEVO
            $modeloPlaga->registrarPlaga($codigo_plaga, $nombre_comun, $nombre_cientifico, $descripcion, $tratamiento);
            $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Plaga registrada con éxito en el catálogo.</div>";
        }
    } catch (mysqli_sql_exception $e) {
        // Manejo de error 1062 para CodigoPlaga UNIQUE
        if ($e->getCode() == 1062) {
            $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error: El Código de Plaga '{$codigo_plaga}' ya existe. Intenta con otro código.</div>";
        } else {
            $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error de base de datos: " . $e->getMessage() . "</div>";
        }
    }
}

// 5. LÓGICA DE CONSULTA (LECTURA)
$resultadoPlagas = $modeloPlaga->obtenerPlagas();

// 6. CARGAR LA VISTA
require_once VIEW_PATH . 'admin/plagas.php';
?>