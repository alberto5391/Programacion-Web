<?php
// Validar sesión integrada
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: index.php?modulo=login");
    exit();
}

$rolePermissions = PermissionPolicy::roleActions();

require_once VIEW_PATH . 'admin/control_acceso.php';
?>