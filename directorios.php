<?php
/**
 * Archivo: directorios.php
 * Descripción: Define las constantes de rutas absolutas para el sistema PLAGUE.
 * Esto evita problemas con require_once al cambiar de carpetas.
 */

// __DIR__ obtiene la ruta absoluta de la carpeta donde está este archivo (raíz del proyecto)
define('BASE_PATH', __DIR__ . '/');

// Definir las rutas de las carpetas principales de la arquitectura MVC
define('CONTROLLER_PATH', BASE_PATH . 'controllers/');
define('MODEL_PATH',      BASE_PATH . 'models/');
define('VIEW_PATH',       BASE_PATH . 'views/');
define('RESOURCE_PATH',   BASE_PATH . 'resources/');

// URL base para los enlaces en HTML, imágenes y CSS (ajusta si cambias el nombre de la carpeta en XAMPP/WAMP)
// Esto es útil para los atributos href="" y src="" en tus vistas
$protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
define('BASE_URL', $protocolo . '://' . $host . '/plague/');
?>