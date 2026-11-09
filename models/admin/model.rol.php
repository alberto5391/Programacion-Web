<?php
class ModelRol {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    /**
     * Obtiene todos los roles disponibles en el sistema.
     * Ideal para llenar dinámicamente el <select> del formulario de usuarios.
     */
    public function obtenerRoles() {
        // Si tu tabla tiene un campo de 'Estado', puedes agregar: WHERE Estado = 'Activo'
        $query = "SELECT IdRol, NombreRol FROM Roles ORDER BY IdRol ASC";
        return $this->conexion->query($query);
    }

    /**
     * Obtiene el ID numérico de un rol a partir de su nombre en texto.
     * Útil cuando se recibe el texto desde un formulario y se necesita guardar el ID.
     */
    public function obtenerIdRolPorNombre($nombreRol) {
        $stmt = $this->conexion->prepare("SELECT IdRol FROM Roles WHERE NombreRol = ?");
        $stmt->bind_param("s", $nombreRol);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            return $resultado->fetch_assoc()['IdRol'];
        }
        return false;
    }
}
?>