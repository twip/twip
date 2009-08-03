<?php
	require('func.php');
	require('config.php');


	$requesturl = substr( $_SERVER['REQUEST_URI'] , -strlen($_SERVER['REQUEST_URI']) + strlen($webroot) );
	$method = $_SERVER['REQUEST_METHOD'];
	$ch = curl_init($twitter.$requesturl);

	$curlopts = array();
	if(isset($_SERVER['PHP_AUTH_USER'])){
		$isauth = 'auth';
		$curlopts[CURLOPT_USERPWD] = $_SERVER['PHP_AUTH_USER'].':'.$_SERVER['PHP_AUTH_PW'];
	}
	if( $method =='POST' || $method == 'DELETE' ){
		$curlopts[CURLOPT_POST] = true;
		if(get_magic_quotes_gpc()){
			foreach($_POST as $key => $value){
				$_POST[$key] = stripslashes($_POST[$key]);
			}
		}
		$curlopts[CURLOPT_POSTFIELDS] = $_POST;
	}
	$curlopts[CURLOPT_HTTPHEADER] = array('Expect:');
	$curlopts[CURLOPT_HEADERFUNCTION] = 'echoheader';
	curl_setopt_array($ch,$curlopts);
	curl_exec($ch);
	dolog();
?>
