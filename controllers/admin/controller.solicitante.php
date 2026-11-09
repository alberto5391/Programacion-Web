<?php
// 1. Validar sesión integrada (El session_start ya está en index.php)
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: index.php?modulo=solicitantes");
    exit();
}

// 2. Aquí iría la conexión al modelo ModelBitacora en el futuro cuando se conecte a la BD
// require_once MODEL_PATH . 'model.bitacora.php';
// $modeloBitacora = new ModelBitacora($conexion);

// 3. Cargar la vista
require_once VIEW_PATH . 'admin/solicitantes.php';
?>