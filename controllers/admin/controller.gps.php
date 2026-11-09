<?php
$roles_permitidos = ['Administrador', 'Tecnico']; // Roles permitidos para acceder a esta página

if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['rol'], $roles_permitidos)) {
    header("Location: index.php?modulo=login");
    exit();
}
// Validar sesión integrada
/*if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: index.php?modulo=login");
    exit();
}*/

// Cargar la vista directamente, sin modelo ni base de datos
require_once VIEW_PATH . 'admin/coordenadas_gps.php';
?>