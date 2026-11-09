<?php
require_once CLASS_PATH . 'class.connection.php';

class ModelBase {
    protected $db;

    public function __construct() {
        $connObj = new Connection();
        $this->db = $connObj->getConexion();
    }
}
?>