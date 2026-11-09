<?php
/**
 * Clase de conexión a la base de datos plagueweb_db (Puerto 3307)
 */
class Connection {
    private $servidor = "localhost";
    private $usuario_bd = "root";
    private $password_bd = "";
    private $base_datos = "plagueweb_db";
    private $puerto = 3307;
    protected $conexion;

    public function __construct() {
        // Hace que mysqli lance excepciones si algo falla
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->conexion = new mysqli(
                $this->servidor,
                $this->usuario_bd,
                $this->password_bd,
                $this->base_datos,
                $this->puerto
            );
            $this->conexion->set_charset("utf8mb4");
        } catch (mysqli_sql_exception $e) {
            die("Error al conectar con la base de datos. Verifica que MySQL esté encendido en XAMPP en el puerto 3307.");
        }
    }

    // Método para obtener la instancia de la conexión desde los modelos
    public function getConexion() {
        return $this->conexion;
    }
}
?>