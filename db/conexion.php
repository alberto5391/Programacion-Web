<?php
//error_reporting(E_ALL);
//error_reporting(0);
            
$db = Connection("plagueweb_db");

function Connection($database,$usr="root",$pass=""){
    $obj= new DBObject("localhost",$database,$usr,$pass);
    $obj->execute("set names utf8",array());
    return($obj);
}

class DBObject {

    private $conn;
    private $rs;
    private $mode;
    private $debug=false;

    function __construct($host,$database,$usuario,$password=""){
        try{
            $this->conn = new PDO("mysql:host={$host};dbname={$database}", $usuario, $password);
       	    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->fetchObject();
        } catch(PDOException $e){
            echo "ERROR: " . $e->getMessage();
        }
    }

    function debug($d=true){
      $this->debug=$d;
    }
    function showDebug($sql,$e){
        switch($e->getCode()){
        case 23000:
            $_SESSION["msg"]="Lo sentimos la información está duplicada o no existe.";
            break;
        default:
            $_SESSION["msg"]= "";
        }
        if($this->debug) echo "<p>$sql<br><font color=red>" . $e->getMessage() . "</font><p>";
    }

    function fetchObject(){
        $this->mode = PDO::FETCH_OBJ;
    }

    function fetchArray(){
        $this->mode = PDO::FETCH_BOTH;
    }

	function query($sql,$params=array())
	{
        //if($mode=="ARRAY") $this->fetchArray(); else $this->fetchObject();
	    try {
           $this->rs = $this->conn->prepare($sql);
           $this->rs->execute($params);
           $this->rs->setFetchMode($this->mode);
           return($this->rs);  // devuelve un recordset
        }
        catch(Exception $e) {
           $this->showDebug($sql,$e);
        }
	}

    function count() {
        return($this->rs->rowCount());  // devuelve un numero despues de un query
    }

	function recordSet($sql,$params=array())
	{
	    return($this->query($sql,$params)); // devuelve un recordset
	}

	function recordSetArray($sql,$params=array())
	{
	    $this->fetchArray();
	    return($this->query($sql,$params)); // devuelve un recordset de arrays
	}

	function record($sql,$params=array(), $array=false)
	{
	    $this->fetchObject();
	    $this->query($sql,$params);
    	return ($this->next($this->rs)); // devuelve un registro
	}
	function recordArray($sql,$params=array())
	{
	    $this->fetchArray();
	    $this->query($sql,$params);
	    $this->fetchObject();
    	return ($this->next()); // devuelve un registro
	}
	function field($sql,$params=array())
	{
    	$row = $this->recordArray($sql,$params);
    	return ($row[0]);
	}
	function execute($sql,$params=array())
	{
	    try {
           $this->rs = $this->conn->prepare($sql);
           $this->rs->execute($params);
        }
        catch(Exception $e) {
           $this->showDebug($sql,$e);
        }
	}

    function lastId(){
      return $this->conn->lastInsertId();
    }

	function next($rs)
	{
	    if($rs)
            return($rs->fetch());
        else
    		return($this->rs->fetch());   // devuelve un registro
	}

    function saveRecord($table,$keyfield) {
            $id = $_POST["fldid"];
            if($id) $sql = "UPDATE {$table} SET "; else $sql = "INSERT INTO {$table} SET ";

            $fields="";
            foreach($_POST as $key=>$value){
                if($key<>"fldid" and $key<>"SAVE_RECORD") {
                    // quitar las letras fld
                    $key = substr($key,3);
                    // quitar comillas y apostrofos
                    $value = str_replace("'","",$value);
                    $value = str_replace('"',"",$value);
                    $fields .= ", " . $key . "='" . $value . "'";
                }
            }
            $fields = substr($fields,2); // quitar la primera coma
            $sql .= $fields;
            if($id) $sql .= " WHERE {$keyfield}='{$id}'";

			//die($sql);

            $this->execute($sql,array());
    }

    function deleteRecord($table,$filter) {
         $sql = "DELETE FROM {$table} WHERE {$filter} ";
         $this->execute($sql,array());
    }

}

?>