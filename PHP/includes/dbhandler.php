<?php
/*
	Database handler
*/
class dbhandler{
	private $connection, $host, $dbName, $user, $pass;
	public $error;
	public $sql;
	public $lastInsertId;

	public function __construct(){
		if(func_num_args()==0){
			// set default options
			// load configuration from db-config.php
			if(is_file(__DIR__.'/../db-config.php')){
				include_once(__DIR__.'/../db-config.php');
			}
			// if not defined, set default
			$this->host = defined('HOST_NAME')?HOST_NAME:'localhost';
			$this->dbName = defined('DATABASE_NAME')?DATABASE_NAME:'roombooking_db';
			$this->user = defined('DATABASE_USER')?DATABASE_USER:'root';
			$this->pass = defined('DATABASE_PASSWORD')?DATABASE_PASSWORD:'';
			$this->error = '';
			$this->connect();
		}elseif(func_num_args()==4){ // host,batabase,user,password
			$this->host = func_get_arg(0);
			$this->dbName = func_get_arg(1);
			$this->user = func_get_arg(2);
			$this->pass = func_get_arg(3);
			$this->error = '';
			$this->connect();
		}else{
			$this->error = 'Invalid arguments';
		}
	} // end construct

	public function __destruct(){
		$this->connection = null;
	} // end destruct

	private function connect(){
		try{
			$this->connection = new PDO("mysql:host=".$this->host.";dbname=".$this->dbName,$this->user,$this->pass);
			// set error mode
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
			return true;
		}catch(PDOException $e){
			$this->error = $e->getMessage();
			$this->connection = null;
			return false;
		}
	} // end connect


	/*
		select query
		param Sql statement
		return array of data
	*/
	public function selectQuery($sql){
		$this->error = '';
		$data = array();
		$this->sql = $sql;
		// check connection
		if($this->connection==null || $this->connection==false){
			$this->error = 'connection failed';
			return false;
		}
		// query object
		$query = $this->connection->query($this->sql);
		if($query==false){
			// error
			$errInfo = $this->connection->errorInfo();
			$this->error = $errInfo[2];
		}else{
			while($record = $query->fetch(PDO::FETCH_ASSOC)){
				array_push($data,$record);
			}
		}
		return $data;
	} // end select query


	/*
		insert to database
		param sql statement
	*/
	public function insertQuery($sql){
		$this->error = '';
		$this->sql = $sql;
		// check connection
		if($this->connection==null || $this->connection==false){
			$this->error = 'connection failed';
			return false;
		}
		$query = $this->connection->prepare($this->sql);
		$query->execute();
		$errInfo = $query->errorInfo();
		$this->error = $errInfo[2];
		if($this->error==''){
			$this->lastInsertId = $this->connection->lastInsertId();
			return $this->lastInsertId;
		}
		return false;
	}


	/*
		update table
		param sql statement
	*/
	public function updateQuery($sql){
		$this->error = '';
		$this->sql = $sql;
		// check connection
		if($this->connection==null || $this->connection==false){
			$this->error = 'connection failed';
			return false;
		}
		$query = $this->connection->prepare($this->sql);
		$query->execute();
		$errInfo = $query->errorInfo();
		$this->error = $errInfo[2];
		if($this->error==''){
			return $query->rowCount();
		}
		return false;
	}


	/*
		delete table from database
		param sql statement
	*/
	public function deleteQuery($sql){
		$this->error = '';
		$this->sql = $sql;
		// check connection
		if($this->connection==null || $this->connection==false){
			$this->error = 'connection failed';
			return false;
		}
		$query = $this->connection->prepare($this->sql);
		$query->execute();
		$errInfo = $query->errorInfo();
		$this->error = $errInfo[2];
		if($this->error==''){
			return $query->rowCount();
		}
		return false;
	}

	// Additional function

	// adding quote to column name
	public function quote($str){
		return $this->connection->quote($str);
	}


	/*
		clean sanitize input
	*/
	// one line, number or string
	public function clean($str){
		$str = trim($str);
		$str = strip_tags($str);
		$str = str_replace(array("'",'"',"\r","\n",";"),'',$str);
		if(!get_magic_quotes_gpc()){
			$str = addslashes($str);
		}
		if(function_exists('filter_var')){
			$str = filter_var($str,FILTER_SANITIZE_STRING);
		}
		return $str;
	}

	// clean text multiple line	no tags, html support
	public function cleanText($str,$html=null){
		$str = trim($str);
		if($html){
			//$str = nl2br($str);
		}else{
			$str = strip_tags($str);
		}
		if(!get_magic_quotes_gpc()){
			$str = addslashes($str);
		}
		return $str;
	}


} // end DbHandler
?>
