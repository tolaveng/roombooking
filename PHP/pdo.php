<?php
$connection;
$hostName = "db628238005.db.1and1.com";
$dbName = "db628238005";
$dbUser = "dbo628238005";
$dbPass = "vatoLeng@26";

//connect
try{
  $connection = new PDO("mysql:host=".$hostName.";dbname=".$dbName,$dbUser,$dbPass);
  // set error mode
  $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
  echo "connection success";
}catch(PDOException $e){
  echo "connection failed : ".$e->getMessage();
}
?>
