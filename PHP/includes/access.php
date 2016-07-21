<?php
session_start();
session_regenerate_id(true);
date_default_timezone_set('Australia/Melbourne');

/*
File name: access.php
Check user is logged in
Store user table in global user
*/

// global user data
$user = null;
$db = null;

// include require classes and functions
require_once('dbhandler.php');
require_once('functions.php');

// create database;
$db = new dbhandler();
if($db->error!=''){
	// database error occurred, exit
	echo $db->error;
	exit();
}

// restricted direct access
if( !isset($_SESSION['user_id'])){
	//echo "session not set";
	header("location: login.php");
	exit();
}else{
	// check user id and get data
	$sql = 'SELECT user_id,firstname,lastname,phone,role,password from tb_user where user_id="'.$db->clean($_SESSION['user_id']).'"';
	$data = $db->selectQuery($sql);
	if(count($data)>0){
		foreach($data[0] as $key=>$val){
			$user[$key] = $val;
		}
	}else{
		//echo "sql error";
		header("location: login.php");
		exit();
	}
}

?>
