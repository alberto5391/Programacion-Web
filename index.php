<?php
/**
 * FRONT CONTROLLER - PLAGUE
 * Punto único de entrada para toda la aplicación (Arquitectura MVC)
 */

// 1. Iniciar sesión en el punto más alto del sistema
session_start();

// 2. Cargar las constantes de los directorios absolutos
require_once 'directorios.php';

// 3. Cargar e inicializar la conexión a la Base de Datos
// Al hacerlo aquí, la variable $conexion estará disponible para todos los controladores y modelos
require_once RESOURCE_PATH . 'class/class.connection.php';
$objConexion = new Connection();
$conexion = $objConexion->getConexion();

// 4. Capturar el módulo solicitado
// Usamos $_REQUEST para que lea 'modulo' tanto de la URL (?modulo=x) como de formularios POST ocultos
$modulo = isset($_REQUEST['modulo']) ? $_REQUEST['modulo'] : 'home';

// 5. Enrutador (Switch) - Llama al controlador adecuado según el módulo
switch ($modulo) {

    case 'home':
        require_once VIEW_PATH . 'home.php';
        break;
    
    // --- AUTENTICACIÓN ---
    case 'login':
        require_once CONTROLLER_PATH . 'controller.login.php';
        break;
        
    case 'logout':
        require_once CONTROLLER_PATH . 'admin/controller.logout.php';
        break;

    // --- PANEL PRINCIPAL ---
    case 'dashboard':
            require_once CONTROLLER_PATH . 'admin/controller.dashboard.php';
        break;

    // --- MÓDULOS DE ADMINISTRACIÓN (Catálogos y Registros) ---
    case 'usuarios':
        // Maneja RF02, RF03, RF04 y RF05
        require_once CONTROLLER_PATH . 'admin/controller.usuario.php';
        break;

    // case 'zonas':
    case 'coordenadas_gps':
        require_once CONTROLLER_PATH . 'admin/controller.gps.php';
        break;

    case 'plagas':
        // Maneja RF09
        require_once CONTROLLER_PATH . 'admin/controller.plaga.php';
        break;

    case 'productos':
        // Maneja RF10 y RF17
        require_once CONTROLLER_PATH . 'admin/controller.producto.php';
        break;

    case 'tecnicos':
        // Maneja RF11
        require_once CONTROLLER_PATH . 'admin/controller.tecnico.php';
        break;

    case 'evidencias':
        // Maneja RF22 y las evidencias
        require_once CONTROLLER_PATH . 'admin/controller.evidencia.php';
        break;

    case 'solicitantes':
        require_once CONTROLLER_PATH . 'admin/controller.solicitante.php';
        break;

    case 'notificaciones':
        require_once CONTROLLER_PATH . 'admin/controller.notificacion.php';
        break;

    // --- REPORTES Y BITÁCORAS ---
    case 'bitacora':
        require_once CONTROLLER_PATH . 'admin/controller.bitacora.php';
        break;

    case 'informacion_historica':
        // Maneja la bitácora de auditoría y consultas históricas
        require_once CONTROLLER_PATH . 'admin/controller.informacion_historica.php';
        break;

    case 'estadisticas':
        // Maneja el panel de estadísticas y mapas
        require_once CONTROLLER_PATH . 'admin/controller.estadistica.php'; // (Asegúrate de crear este controlador o integrarlo donde prefieras)
        break;
    
    case 'control_acceso':
        require_once CONTROLLER_PATH . 'admin/controller.control_acceso.php';
        break;

    case 'zonas':
        require_once CONTROLLER_PATH . 'admin/controller.zona.php';
        break;
    
    case 'campanas':
        require_once CONTROLLER_PATH . 'admin/controller.campana.php';
        break;
    
    case 'notificacion_modificar':
        require_once VIEW_PATH . 'admin/notificacion_modificar.php';
        break;

    case 'notificacion_desactivar':
        require_once VIEW_PATH . 'admin/notificacion_desactivar.php';
        break;

    case 'super_dashboard':
        require_once CONTROLLER_PATH . 'super/controller.super_dashboard.php';
        break;
    
    case 'graficas':
        require_once CONTROLLER_PATH . 'super/controller.graficas.php';
        break;

    case 'graficas_productos':
        require_once CONTROLLER_PATH . 'super/controller.graficas_productos.php';
        break;

    case 'graficas_incidencias':
        require_once CONTROLLER_PATH . 'super/controller.graficas_incidencias.php';
        break;

    case 'graficas_plagas':
        require_once CONTROLLER_PATH . 'super/controller.graficas_plagas.php';
        break;

    case 'graficas_zonas':
        require_once CONTROLLER_PATH . 'super/controller.graficas_zonas.php';
        break;
    
    case 'registro_campanas':
        require_once CONTROLLER_PATH . 'super/controller.registro_campanas.php';
        break;

    case 'historial_zonas':
        require_once CONTROLLER_PATH . 'super/controller.historial_zonas.php';
        break;

    case 'estado_campana':
        require_once CONTROLLER_PATH . 'super/controller.estado_campana.php';
        break;

    case 'zonas_prioritarias':
        require_once CONTROLLER_PATH . 'super/controller.zonas_prioritarias.php';
        break;
    
    case 'notificacion_exportar':
        require_once CONTROLLER_PATH . 'super/controller.notificacion_exportar.php';
        break;

    case 'reporte_campana':
        require_once CONTROLLER_PATH . 'super/controller.reporte_campana.php';
        break;

    case 'reporte_inspecciones':
        require_once CONTROLLER_PATH . 'super/controller.reporte_inspecciones.php';
        break;

    case 'reporte_aplicaciones':
        require_once CONTROLLER_PATH . 'super/controller.reporte_aplicaciones.php';
        break;

    case 'seguimiento_posterior':
        require_once CONTROLLER_PATH . 'super/controller.seguimiento_posterior.php';
        break;

    case 'incidencias':
        require_once CONTROLLER_PATH . 'super/controller.incidencias.php';
        break;

    case 'tecnico_dashboard':
        require_once CONTROLLER_PATH . 'tec/controller.tecnico.php';
        break;
        
    case 'inspeccion_diagnostico':
        require_once CONTROLLER_PATH . 'tec/controller.inspeccion_diagnostico.php';
        break;

    case 'tratamientos':
        require_once CONTROLLER_PATH . 'tec/controller.tratamientos.php';
        break;

    case 'responsables':
        require_once CONTROLLER_PATH . 'tec/controller.responsables.php';
        break;

    case 'recuperar_contraseña':
        require_once CONTROLLER_PATH . 'tec/controller.recuperar_contraseña.php';
        break;
    
    
    // --- RUTA POR DEFECTO (Error 404) ---
    default:
        // Si el usuario escribe un módulo que no existe en la URL, mostramos un error genérico
        echo "<!DOCTYPE html>
              <html lang='es'>
              <head>
                  <meta charset='UTF-8'>
                  <title>Error 404 - PLAGUE</title>
                  <style>
                      body { font-family: sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; color: #333; text-align: center;}
                      .error-box { background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
                      h1 { color: #dc3545; }
                      a { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #007bff; color: #fff; text-decoration: none; border-radius: 5px; }
                  </style>
              </head>
              <body>
                  <div class='error-box'>
                      <h1>Error 404</h1>
                      <p>El módulo <strong>" . htmlspecialchars($modulo) . "</strong> no existe o fue movido.</p>
                      <a href='" . BASE_URL . "index.php?modulo=dashboard'>Regresar al inicio</a>
                  </div>
              </body>
              </html>";
        break;
}
?>