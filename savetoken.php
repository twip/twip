<?php
/*
 * Copyright (c) 2010 bronco - http://heybronco.net
 */
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("HTTP/1.1 405 Method Not Allowed");
	header('Allow: POST');
    header("Content-type: text/plain");
    exit;
}

require_once('oauth_config.php');
require_once('include/utility.php');
session_start();

if(!empty($_SESSION['access_token']))
{
	$access_token = $_SESSION['access_token'];
	$user = strtolower($access_token['screen_name']);
	$passwd = $_POST["password"];
	if (function_exists('mcrypt_module_open'))
	{
		$savetoken = encrypt(serialize($access_token),$passwd.SECURE_KEY);
		$filecontents = '1,';//flag that the access_token has been crypted
	}else{
		$savetoken = serialize($access_token);
		$filecontents = '0,';
	}
	$filecontents .= md5(md5($passwd).SECURE_KEY).','.$savetoken;
	file_put_contents(OAUTH_DIR.$user.'.oauth',$filecontents);
}
header('Location: ./index.php');
?>