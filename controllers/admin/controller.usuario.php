<?php
// 1. Validar sesión integrada (El session_start ya está en index.php)
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: index.php?modulo=login");
    exit();
}

// 2. Cargar los modelos necesarios
require_once MODEL_PATH . 'admin/model.rol.php';
require_once MODEL_PATH . 'model.usuario.php'; 

$modeloRol = new ModelRol($conexion);
$modeloUsuario = new ModelUsuario($conexion); 

$mensaje = "";

// 3. LÓGICA DE EDICIÓN O ELIMINACIÓN (GET)
$usuarioEditar = null;
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id_get = intval($_GET['id']);
    
    if ($_GET['accion'] === 'editar') {
        $usuarioEditar = $modeloUsuario->obtenerUsuarioPorId($id_get);
    } elseif ($_GET['accion'] === 'eliminar') {
        try {
            $modeloUsuario->eliminarUsuario($id_get);
            $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Usuario eliminado con éxito.</div>";
        } catch (mysqli_sql_exception $e) {
            // Código 1451: Error de restricción de llave foránea (ON DELETE RESTRICT - Ej. tabla Bitácora)
            if ($e->getCode() == 1451) {
                $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error: No se puede eliminar este usuario porque tiene registros vinculados en el sistema (ej. registros en bitácora o técnico asignado). Te sugerimos cambiar su estado a 'Inactivo'.</div>";
            } else {
                $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error al eliminar el usuario: " . $e->getMessage() . "</div>";
            }
        }
    }
}

// 4. LÓGICA DE REGISTRO O ACTUALIZACIÓN (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $contacto = trim($_POST['contacto']);
    $nombreRol = trim($_POST['rol']); // Viene del <select> dinámico
    $correo = trim($_POST['correo']);
    $curp = trim($_POST['curp']);
    $rfc = trim($_POST['rfc']);
    $estado = isset($_POST['estado']) ? trim($_POST['estado']) : 'Activo';
    
    // Solo encriptar la contraseña si el usuario escribió algo
    $password_hash = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    try {
        // Usamos el modeloRol para traducir el nombre del rol a su ID
        $idRol = $modeloRol->obtenerIdRolPorNombre($nombreRol); 

        if ($idRol) {
            if (!empty($_POST['id_usuario'])) {
                // Es una ACTUALIZACIÓN
                $id_usuario = intval($_POST['id_usuario']);
                $modeloUsuario->actualizarUsuario($id_usuario, $idRol, $nombre, $apellidos, $contacto, $correo, $curp, $rfc, $estado, $password_hash);
                $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Usuario actualizado con éxito.</div>";
                $usuarioEditar = null; // Limpiar el formulario
            } else {
                // Es un REGISTRO NUEVO
                $modeloUsuario->registrarUsuario($idRol, $nombre, $apellidos, $contacto, $correo, $curp, $rfc, $password_hash);
                $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Usuario registrado con éxito.</div>";
            }
        } else {
            $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error: El rol seleccionado no existe.</div>";
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error: El Correo, CURP o RFC ya está registrado. Verifica los datos.</div>";
        } else {
            $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error de base de datos: " . $e->getMessage() . "</div>";
        }
    }
}

// 5. PREPARAR DATOS PARA LA VISTA (RF04 / RF05 y Selects)
$resultadoRoles = $modeloRol->obtenerRoles();
$resultadoUsuarios = $modeloUsuario->obtenerUsuarios();

// 6. CARGAR LA VISTA
require_once VIEW_PATH . 'admin/usuarios.php';
?>