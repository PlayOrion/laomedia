<?php

//UNCOMMENT SECTION BELOW FOR USE WITH COSIGN/WEBACCESS

/*	
if(isset($_SERVER['REMOTE_USER'])){
	$userID = $_SERVER['REMOTE_USER'];
}else{
	echo"<div style='text-align:center;margin-top:30px;'><h3>Permisson Denied.</h3></div>";
	exit();
	}
*/	

/*
ASSIGN $userID BELOW FOR LOCAL INSTALLATION 
*/

$userID = "srt142";//comment out this line when in production or using Cosign 


$userID=strtolower($userID);

?>