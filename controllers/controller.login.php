<?php
/**
 * Controlador de Autenticación (RF01 y RF06)
 */
require_once MODEL_PATH . 'model.usuario.php';

// Si la petición es POST, procesamos el inicio de sesión
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Capturamos exactamente lo que envía el HTML ('correo' y 'password')
    $correo   = trim($_POST["correo"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($correo === "" || $password === "") {
        header("Location: index.php?modulo=login&error=credenciales");
        exit;
    }

    // Instanciamos el modelo con la conexión
    $usuarioModel = new ModelUsuario($conexion);
    $datos = $usuarioModel->obtenerPorCorreo($correo);

    // Verificamos si existe el usuario y coincide la contraseña
    $passwordValida = false;
    if ($datos) {
        if ($password === $datos["Password"] || password_verify($password, $datos["Password"])) {
            $passwordValida = true;
        }
    }

    if (!$passwordValida) {
        header("Location: index.php?modulo=login&error=credenciales");
        exit;
    }

    // Inicio de sesión correcto
    session_regenerate_id(true);
    $_SESSION["id_usuario"] = $datos["IdUsuario"];
    $_SESSION["nombre"]     = $datos["Nombre"] . " " . $datos["Apellidos"];
    $_SESSION["rol"]        = $datos["NombreRol"];

    // Redirección MVC
    switch ($datos["NombreRol"]) {
        case "Administrador":
            header("Location: index.php?modulo=dashboard");
            break;
        case "Supervisor":
            header("Location: index.php?modulo=super_dashboard");
            break;
        case "Tecnico":
            header("Location: index.php?modulo=tecnico_dashboard");
            break;
        default:
            session_destroy();
            header("Location: index.php?modulo=login&error=credenciales");
            break;
    }
    exit;
}

// Si es GET, mostramos el login
require_once VIEW_PATH . 'auth/login.php';
?>