<?php
// 1. Validar sesión integrada
$roles_permitidos = ['Administrador', 'Supervisor'];

if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['rol'], $roles_permitidos)) {
    header("Location: index.php?modulo=login");
    exit();
}



// 2. Cargar el modelo de Notificaciones




// 3. CARGAR LA VISTA
require_once VIEW_PATH . 'admin/notificaciones.php';
?>

