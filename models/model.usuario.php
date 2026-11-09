<?php
class ModelUsuario {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerPorCorreo($correo) {
        // Consulta con JOIN para traer el NombreRol desde la tabla Roles
        $sql = "SELECT u.*, r.NombreRol 
                FROM Usuarios u 
                INNER JOIN Roles r ON u.IdRol = r.IdRol 
                WHERE u.Correo = ?";
                
        // Lógica de preparación y ejecución nativa para MySQLi
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        return $resultado->fetch_assoc();
    }

    // Registrar un nuevo usuario (RF02)
    public function registrarUsuario($idRol, $nombre, $apellidos, $contacto, $correo, $curp, $rfc, $password_hash) {
        $stmt = $this->conexion->prepare("INSERT INTO Usuarios (IdRol, Nombre, Apellidos, Contacto, Correo, Curp, Rfc, Password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $idRol, $nombre, $apellidos, $contacto, $correo, $curp, $rfc, $password_hash);
        return $stmt->execute();
    }

    // Obtener todos los usuarios para la tabla (RF04/RF05)
    public function obtenerUsuarios() {
        $query = "SELECT u.IdUsuario, u.Nombre, u.Apellidos, u.Contacto, u.Correo, u.Curp, u.Rfc, u.Estado, r.NombreRol 
                  FROM Usuarios u 
                  INNER JOIN Roles r ON u.IdRol = r.IdRol 
                  ORDER BY u.FechaCreacion DESC";
        return $this->conexion->query($query);
    }

    // Obtener un usuario específico por su ID
    public function obtenerUsuarioPorId($idUsuario) {
        $stmt = $this->conexion->prepare("SELECT u.*, r.NombreRol FROM Usuarios u INNER JOIN Roles r ON u.IdRol = r.IdRol WHERE u.IdUsuario = ?");
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    // Actualizar los datos de un usuario
    public function actualizarUsuario($idUsuario, $idRol, $nombre, $apellidos, $contacto, $correo, $curp, $rfc, $estado, $password_hash = null) {
        if ($password_hash) {
            // Se actualiza incluyendo la contraseña
            $stmt = $this->conexion->prepare("UPDATE Usuarios SET IdRol = ?, Nombre = ?, Apellidos = ?, Contacto = ?, Correo = ?, Curp = ?, Rfc = ?, Estado = ?, Password = ? WHERE IdUsuario = ?");
            $stmt->bind_param("issssssssi", $idRol, $nombre, $apellidos, $contacto, $correo, $curp, $rfc, $estado, $password_hash, $idUsuario);
        } else {
            // Se actualiza omitiendo la contraseña (para mantener la anterior)
            $stmt = $this->conexion->prepare("UPDATE Usuarios SET IdRol = ?, Nombre = ?, Apellidos = ?, Contacto = ?, Correo = ?, Curp = ?, Rfc = ?, Estado = ? WHERE IdUsuario = ?");
            $stmt->bind_param("isssssssi", $idRol, $nombre, $apellidos, $contacto, $correo, $curp, $rfc, $estado, $idUsuario);
        }
        return $stmt->execute();
    }

    // Eliminar un usuario por su ID
    public function eliminarUsuario($idUsuario) {
        $stmt = $this->conexion->prepare("DELETE FROM Usuarios WHERE IdUsuario = ?");
        $stmt->bind_param("i", $idUsuario);
        return $stmt->execute();
    }
}
?>