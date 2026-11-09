<?php
class ModelProducto {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Registrar un nuevo producto
    public function registrarProducto($codigo, $nombre, $ingrediente, $tipo, $unidad_medida, $presentacion) {
        $stmt = $this->conexion->prepare("INSERT INTO Productos (CodigoProducto, NombreComercial, IngredienteActivo, TipoProducto, UnidadMedida, PresentacionDosis) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $codigo, $nombre, $ingrediente, $tipo, $unidad_medida, $presentacion);
        return $stmt->execute();
    }

    // Obtener todos los productos ordenados por los más recientes
    public function obtenerProductos() {
        $query = "SELECT IdProducto, CodigoProducto, NombreComercial, TipoProducto, IngredienteActivo, Estado FROM Productos ORDER BY IdProducto DESC";
        return $this->conexion->query($query);
    }

    // Obtener un producto específico por su ID
    public function obtenerProductoPorId($idProducto) {
        $stmt = $this->conexion->prepare("SELECT IdProducto, CodigoProducto, NombreComercial, IngredienteActivo, TipoProducto, UnidadMedida, PresentacionDosis, Estado FROM Productos WHERE IdProducto = ?");
        $stmt->bind_param("i", $idProducto);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    // Actualizar los datos de un producto existente
    public function actualizarProducto($idProducto, $codigo, $nombre, $ingrediente, $tipo, $unidad_medida, $presentacion, $estado) {
        $stmt = $this->conexion->prepare("UPDATE Productos SET CodigoProducto = ?, NombreComercial = ?, IngredienteActivo = ?, TipoProducto = ?, UnidadMedida = ?, PresentacionDosis = ?, Estado = ? WHERE IdProducto = ?");
        $stmt->bind_param("sssssssi", $codigo, $nombre, $ingrediente, $tipo, $unidad_medida, $presentacion, $estado, $idProducto);
        return $stmt->execute();
    }

    // Eliminar un producto por su ID
    public function eliminarProducto($idProducto) {
        $stmt = $this->conexion->prepare("DELETE FROM Productos WHERE IdProducto = ?");
        $stmt->bind_param("i", $idProducto);
        return $stmt->execute();
    }
}
?>