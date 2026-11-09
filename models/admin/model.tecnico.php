<?php
class ModelTecnico {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion =$conexion;
    }

    // Registrar un nuevo técnico
    public function registrarTecnico($id_usuario, $numero_empleado,$especialidad) {
        $stmt =$this->conexion->prepare("INSERT INTO Tecnicos (IdUsuario, NumeroEmpleado, Especialidad) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $id_usuario, $numero_empleado,$especialidad);
        return $stmt->execute();
    }

    // Obtener los usuarios activos que AÚN NO son técnicos
    public function obtenerUsuariosDisponibles() {
        $query = "SELECT u.IdUsuario, u.Nombre, u.Apellidos 
                  FROM Usuarios u
                  LEFT JOIN Tecnicos t ON u.IdUsuario = t.IdUsuario
                  WHERE u.Estado = 'Activo' AND t.IdTecnico IS NULL";
        return $this->conexion->query($query);
    }

    // Obtener la lista de todos los técnicos con sus datos de usuario
    public function obtenerTecnicos() {
        $query = "SELECT t.IdTecnico, t.NumeroEmpleado, t.Especialidad, 
                         u.Nombre, u.Apellidos, u.Contacto, u.Estado 
                  FROM Tecnicos t
                  INNER JOIN Usuarios u ON t.IdUsuario = u.IdUsuario
                  ORDER BY t.IdTecnico DESC";
        return $this->conexion->query($query);
    }

    // Obtener un técnico específico por su ID
    public function obtenerTecnicoPorId($idTecnico) {
        $stmt =$this->conexion->prepare("SELECT t.IdTecnico, t.IdUsuario, t.NumeroEmpleado, t.Especialidad, u.Nombre, u.Apellidos 
                                          FROM Tecnicos t 
                                          INNER JOIN Usuarios u ON t.IdUsuario = u.IdUsuario 
                                          WHERE t.IdTecnico = ?");
        $stmt->bind_param("i", $idTecnico);$stmt->execute();
        $resultado =$stmt->get_result();
        return $resultado->fetch_assoc();
    }

    // Actualizar los datos de un técnico (No se actualiza el IdUsuario por integridad)
    public function actualizarTecnico($idTecnico, $numero_empleado,$especialidad) {
        $stmt =$this->conexion->prepare("UPDATE Tecnicos SET NumeroEmpleado = ?, Especialidad = ? WHERE IdTecnico = ?");
        $stmt->bind_param("ssi", $numero_empleado, $especialidad,$idTecnico);
        return $stmt->execute();
    }

    // Eliminar un técnico por su ID
    public function eliminarTecnico($idTecnico) {
        $stmt =$this->conexion->prepare("DELETE FROM Tecnicos WHERE IdTecnico = ?");
        $stmt->bind_param("i", $idTecnico);
        return $stmt->execute();
    }
}
?>