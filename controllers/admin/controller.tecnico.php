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

// 2. Cargar el modelo de Técnicos
require_once MODEL_PATH . 'admin/model.tecnico.php';
$modeloTecnico = new ModelTecnico($conexion);

$mensaje = "";

// 3. LÓGICA DE EDICIÓN O ELIMINACIÓN (GET)
$tecnicoEditar = null;
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id_get = intval($_GET['id']);
    
    if ($_GET['accion'] === 'editar') {
        $tecnicoEditar = $modeloTecnico->obtenerTecnicoPorId($id_get);
    } elseif ($_GET['accion'] === 'eliminar') {
        try {
            $modeloTecnico->eliminarTecnico($id_get);
            $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Registro de técnico eliminado correctamente.</div>";
        } catch (mysqli_sql_exception $e) {
            // Código 1451: Error de restricción de llave foránea (ON DELETE RESTRICT - Ej. tabla AccionesCampana)
            if ($e->getCode() == 1451) {
                $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error: No se puede eliminar a este técnico porque ya tiene registros de acciones de campaña (Inspecciones o Aplicaciones) asignadas a él. Te sugerimos ir a 'Gestión de Usuarios' y cambiar su estado a Inactivo.</div>";
            } else {
                $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error al eliminar el técnico: " . $e->getMessage() . "</div>";
            }
        }
    }
}

// 4. LÓGICA DE REGISTRO O ACTUALIZACIÓN (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numero_empleado = trim($_POST['numero_empleado']);
    $especialidad = $_POST['especialidad'];

    try {
        if (!empty($_POST['id_tecnico'])) {
            // Es una ACTUALIZACIÓN
            $id_tecnico = intval($_POST['id_tecnico']);
            $modeloTecnico->actualizarTecnico($id_tecnico, $numero_empleado, $especialidad);
            $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Datos del técnico actualizados correctamente.</div>";
            $tecnicoEditar = null; // Limpiar formulario
        } else {
            // Es un REGISTRO NUEVO
            $id_usuario = $_POST['id_usuario'];
            $modeloTecnico->registrarTecnico($id_usuario, $numero_empleado, $especialidad);
            $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Técnico registrado correctamente.</div>";
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error: El Número de Empleado '{$numero_empleado}' ya está en uso. Usa uno distinto.</div>";
        } else {
            $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error de base de datos: " . $e->getMessage() . "</div>";
        }
    }
}

// 5. OBTENER DATOS PARA LA VISTA
$resultadoUsuarios = $modeloTecnico->obtenerUsuariosDisponibles();
$resultadoTecnicos = $modeloTecnico->obtenerTecnicos();

// 6. CARGAR LA VISTA
require_once VIEW_PATH . 'admin/tecnicos.php';
?>