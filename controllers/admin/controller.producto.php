<?php
if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['rol'], ['Administrador', 'Supervisor'])) {
    header("Location: index.php?modulo=login");
    exit();
}
// 1. Validar sesión integrada
/*if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: index.php?modulo=login");
    exit();
}*/

// 2. Cargar el modelo de Producto
require_once MODEL_PATH . 'admin/model.producto.php';
$modeloProducto = new ModelProducto($conexion);

$mensaje = "";

// 3. LÓGICA DE EDICIÓN O ELIMINACIÓN (GET)
$productoEditar = null;
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id_get = intval($_GET['id']);
    
    if ($_GET['accion'] === 'editar') {
        $productoEditar = $modeloProducto->obtenerProductoPorId($id_get);
    } elseif ($_GET['accion'] === 'eliminar') {
        try {
            $modeloProducto->eliminarProducto($id_get);
            $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Producto eliminado exitosamente del catálogo.</div>";
        } catch (mysqli_sql_exception $e) {
            // Código 1451: Error de restricción de llave foránea (ON DELETE RESTRICT - Ej. tabla InsumosUsados)
            if ($e->getCode() == 1451) {
                $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error: No se puede eliminar este producto porque ya fue utilizado en acciones de campaña (Insumos Usados). Te sugerimos cambiar su estado a 'Inactivo'.</div>";
            } else {
                $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error al eliminar el producto: " . $e->getMessage() . "</div>";
            }
        }
    }
}

// 4. LÓGICA DE REGISTRO O ACTUALIZACIÓN (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo_producto = trim($_POST['codigo_producto']);
    $nombre_comercial = trim($_POST['nombre_comercial']);
    $ingrediente_activo = !empty(trim($_POST['ingrediente_activo'])) ? trim($_POST['ingrediente_activo']) : NULL;
    $tipo_producto = $_POST['tipo_producto'];
    $presentacion = !empty(trim($_POST['presentacion'])) ? trim($_POST['presentacion']) : NULL;
    $unidad_medida = "Por Definir"; // Valor por defecto ya que no está en el form
    $estado = isset($_POST['estado']) ? trim($_POST['estado']) : 'Activo';

    try {
        if (!empty($_POST['id_producto'])) {
            // Es una ACTUALIZACIÓN
            $id_producto = intval($_POST['id_producto']);
            // Si el formulario original no tiene unidad de medida, conservamos el valor por defecto o el que ya tenía.
            $modeloProducto->actualizarProducto($id_producto, $codigo_producto, $nombre_comercial, $ingrediente_activo, $tipo_producto, $unidad_medida, $presentacion, $estado);
            $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Producto actualizado exitosamente en el catálogo.</div>";
            $productoEditar = null; // Limpiar formulario
        } else {
            // Es un REGISTRO NUEVO
            $modeloProducto->registrarProducto($codigo_producto, $nombre_comercial, $ingrediente_activo, $tipo_producto, $unidad_medida, $presentacion);
            $mensaje = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Producto registrado exitosamente en el catálogo.</div>";
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error: El Código de Producto '{$codigo_producto}' ya existe. Verifica e intenta con otro código.</div>";
        } else {
            $mensaje = "<div style='color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Error de base de datos: " . $e->getMessage() . "</div>";
        }
    }
}

// 5. LÓGICA DE CONSULTA (LECTURA)
$resultadoProductos = $modeloProducto->obtenerProductos();

// 6. CARGAR LA VISTA
require_once VIEW_PATH . 'admin/productos.php';
?>