<?php
/*
 * Copyright (c) 2010 bronco - http://heybronco.net
 */
session_start();

if(empty($_SESSION["dologin"]) || !is_bool($_SESSION["dologin"]))
	header("Location: ./index.php");

if(!$_SESSION["dologin"] && (!empty($_SERVER['PHP_AUTH_USER']) || !empty($_SERVER['HTTP_AUTHORIZATION'])))
{
	header("Location: ./index.php");
}
else
{
	$_SESSION["dologin"] = false;
	header("WWW-Authenticate: Basic realm=\"Twip login\""); 
	header("HTTP/1.0 401 Unauthorized"); 
	echo '<a href="./index.php">Home</a>';
	exit;
}
?>