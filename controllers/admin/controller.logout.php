<?php
/**
 * Controlador de Cierre de Sesión (Logout)
 * Ubicación: controllers/controller.logout.php
 */

// Vaciamos todas las variables de la sesión actual
$_SESSION = [];

// Destruimos la sesión en el servidor
session_destroy();

// Redirigimos al Front Controller para que cargue la vista del login
header("Location: index.php?modulo=login");
exit();
?>