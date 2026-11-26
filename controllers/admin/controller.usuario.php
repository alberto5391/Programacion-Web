<?php
// 1. Validar sesión integrada (El session_start ya está en index.php)
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: index.php?modulo=login");
    exit();
}

// 2. Cargar los modelos necesarios
require_once MODEL_PATH . 'admin/model.rol.php';
require_once MODEL_PATH . 'admin/model.tecnico.php';
require_once MODEL_PATH . 'model.usuario.php'; 

$modeloRol = new ModelRol($conexion);
$modeloTecnico = new ModelTecnico($conexion);
$modeloUsuario = new ModelUsuario($conexion); 

$mensaje = "";
$esEliminacion = $_SERVER["REQUEST_METHOD"] === "POST" && ($_POST['accion'] ?? null) === 'eliminar';

// 3. LÓGICA DE EDICIÓN (GET)
$usuarioEditar = null;
$tecnicoEditar = null;
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id_get = intval($_GET['id']);
    
    if ($_GET['accion'] === 'editar') {
        $usuarioEditar = $modeloUsuario->obtenerUsuarioPorId($id_get);
        if ($usuarioEditar) {
            $tecnicoEditar = $modeloTecnico->obtenerTecnicoPorUsuarioId((int)$usuarioEditar['IdUsuario']);
        }
    }
}

if ($esEliminacion) {
    $idEliminar = $_POST['id'] ?? null;
    if (!is_string($idEliminar) || !ctype_digit($idEliminar) || (int)$idEliminar < 1) {
        $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>El usuario seleccionado no es válido.</div>";
    } else {
        try {
            $modeloUsuario->eliminarUsuario((int)$idEliminar);
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
$formularioGuardado = false;
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$esEliminacion) {
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $contacto = trim($_POST['contacto']);
    $nombreRol = trim($_POST['rol']); // Viene del <select> dinámico
    $correo = trim($_POST['correo']);
    $curp = trim($_POST['curp']);
    $rfc = trim($_POST['rfc']);
    $estado = isset($_POST['estado']) ? trim($_POST['estado']) : 'Activo';
    $numeroEmpleadoEntrada = $_POST['numero_empleado'] ?? '';
    $especialidadEntrada = $_POST['especialidad'] ?? '';
    $numeroEmpleado = is_string($numeroEmpleadoEntrada) ? trim($numeroEmpleadoEntrada) : '';
    $especialidad = is_string($especialidadEntrada) ? trim($especialidadEntrada) : '';
    $esTecnico = $nombreRol === 'Tecnico';
    
    // Solo encriptar la contraseña si el usuario escribió algo
    $password_hash = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    if ($esTecnico && (
        $numeroEmpleado === ''
        || strlen($numeroEmpleado) > 20
        || !in_array($especialidad, ['Inspección', 'Aplicación', 'Ambas'], true)
    )) {
        $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Para asignar el rol Técnico, ingresa un número de empleado de hasta 20 caracteres y selecciona una especialidad válida.</div>";
    } else {
        $transaccionIniciada = false;
        try {
            $idRol = $modeloRol->obtenerIdRolPorNombre($nombreRol);

            if (!$idRol) {
                $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error: El rol seleccionado no existe.</div>";
            } else {
                $conexion->begin_transaction();
                $transaccionIniciada = true;

                if (!empty($_POST['id_usuario'])) {
                    $idUsuarioEntrada = $_POST['id_usuario'];
                    if (!is_string($idUsuarioEntrada) || !ctype_digit($idUsuarioEntrada) || (int)$idUsuarioEntrada < 1) {
                        throw new RuntimeException('El usuario seleccionado no es válido.');
                    }
                    $id_usuario = (int)$idUsuarioEntrada;
                    $usuarioActual = $modeloUsuario->obtenerUsuarioPorId($id_usuario);
                    if (!$usuarioActual) {
                        throw new RuntimeException('El usuario ya no existe.');
                    }
                    $perfilTecnico = $modeloTecnico->obtenerTecnicoPorUsuarioId($id_usuario);

                    if (!$modeloUsuario->actualizarUsuario($id_usuario, $idRol, $nombre, $apellidos, $contacto, $correo, $curp, $rfc, $estado, $password_hash)) {
                        throw new RuntimeException('No se pudo actualizar el usuario.');
                    }

                    if ($esTecnico) {
                        if ($perfilTecnico) {
                            $guardadoTecnico = $modeloTecnico->actualizarTecnicoPorUsuarioId($id_usuario, $numeroEmpleado, $especialidad);
                        } else {
                            $guardadoTecnico = $modeloTecnico->registrarTecnico($id_usuario, $numeroEmpleado, $especialidad);
                        }
                        if (!$guardadoTecnico) {
                            throw new RuntimeException('No se pudo guardar el perfil técnico.');
                        }
                    } elseif (
                        $perfilTecnico
                        && $usuarioActual['NombreRol'] === 'Tecnico'
                        && !$modeloTecnico->eliminarTecnicoPorUsuarioId($id_usuario)
                    ) {
                        throw new RuntimeException('No se pudo retirar el perfil técnico.');
                    }

                    $conexion->commit();
                    $transaccionIniciada = false;
                    $formularioGuardado = true;
                    $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Usuario actualizado con éxito.</div>";
                    $usuarioEditar = null;
                    $tecnicoEditar = null;
                } else {
                    $id_usuario = $modeloUsuario->registrarUsuario($idRol, $nombre, $apellidos, $contacto, $correo, $curp, $rfc, $password_hash);
                    if (!$id_usuario) {
                        throw new RuntimeException('No se pudo registrar el usuario.');
                    }
                    if ($esTecnico && !$modeloTecnico->registrarTecnico((int)$id_usuario, $numeroEmpleado, $especialidad)) {
                        throw new RuntimeException('No se pudo registrar el perfil técnico.');
                    }

                    $conexion->commit();
                    $transaccionIniciada = false;
                    $formularioGuardado = true;
                    $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Usuario registrado con éxito.</div>";
                }
            }
        } catch (mysqli_sql_exception $e) {
            if ($transaccionIniciada) {
                $conexion->rollback();
            }
            if ($e->getCode() == 1062) {
                $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error: El correo, CURP, RFC o número de empleado ya está registrado.</div>";
            } elseif ($e->getCode() == 1451) {
                $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>No se puede cambiar el rol porque el perfil técnico tiene acciones de campaña vinculadas.</div>";
            } else {
                $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error de base de datos al guardar el usuario y su perfil.</div>";
            }
        } catch (RuntimeException $e) {
            if ($transaccionIniciada) {
                $conexion->rollback();
            }
            $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</div>";
        }
    }
}

// 5. PREPARAR DATOS PARA LA VISTA (RF04 / RF05 y Selects)
$rolSeleccionado = $usuarioEditar['NombreRol'] ?? '';
$numeroEmpleadoFormulario = $tecnicoEditar['NumeroEmpleado'] ?? '';
$especialidadFormulario = $tecnicoEditar['Especialidad'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$esEliminacion) {
    if ($formularioGuardado) {
        $rolSeleccionado = '';
        $numeroEmpleadoFormulario = '';
        $especialidadFormulario = '';
    } else {
        $rolEntrada = $_POST['rol'] ?? null;
        $numeroEmpleadoEntrada = $_POST['numero_empleado'] ?? null;
        $especialidadEntrada = $_POST['especialidad'] ?? null;
        if (is_string($rolEntrada)) {
            $rolSeleccionado = $rolEntrada;
        }
        if (is_string($numeroEmpleadoEntrada)) {
            $numeroEmpleadoFormulario = $numeroEmpleadoEntrada;
        }
        if (is_string($especialidadEntrada)) {
            $especialidadFormulario = $especialidadEntrada;
        }
    }
}
$resultadoRoles = $modeloRol->obtenerRoles();
$resultadoUsuarios = $modeloUsuario->obtenerUsuarios();

// 6. CARGAR LA VISTA
require_once VIEW_PATH . 'admin/usuarios.php';
?>