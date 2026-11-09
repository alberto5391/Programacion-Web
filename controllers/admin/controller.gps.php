<?php
// Validar sesión integrada
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: index.php?modulo=login");
    exit();
}

// Cargar la vista directamente, sin modelo ni base de datos
require_once VIEW_PATH . 'admin/coordenadas_gps.php';
?>