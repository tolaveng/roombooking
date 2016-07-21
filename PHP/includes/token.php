<?php
/*
	Filename : token.php
	Generate token session
	Check valid token
*/
if(session_id()==""){
	session_start();
}
if(empty($_SESSION['tokenid'])){
	$_SESSION['tokenid'] = hash('sha256',md5(uniqid().rand()));
}

// Get token id
function tokenId(){
	if(empty($_SESSION['tokenid']))
		return false;
	else
		return $_SESSION['tokenid'];
}
// Check token
function tokenCheck(){
	if(isset($_GET[tokenId()]) || isset($_POST[tokenId()]))
		return true;
	else
	return false;
}
// generate token input element
function tokenForm(){
	return '<input type="hidden" name="'.tokenId().'" value="1">';
}
?>