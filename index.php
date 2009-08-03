<?php
	require('config.php');
	require('func.php');

	if(isSSL()){
		$apiurl = 'https://'.$_SERVER['HTTP_HOST'].$webroot.'/';
	}
	else{
		$apiurl = 'http://'.$_SERVER['HTTP_HOST'].$webroot.'/';
	}
	$requesturl = substr( $_SERVER['REQUEST_URI'] , -strlen($_SERVER['REQUEST_URI']) + strlen($webroot) );
	if($requesturl == '/'){
		echo '<html><head><title>Twip,Twitter API proxy in PHP.</title></head><body><h1>Twip,Twitter API proxy in PHP.</h1><p>This is a Twitter API proxy,and is not intend to be viewed in a browser.<br />Please use '.$apiurl.' in your Twitter Client.<br />Visit <a href="http://code.google.com/p/twip/">Twip </a> for more details.</p></body></html>';
		exit();
	}

	//fixme: this is ugly...but it works...
	//if you have any good ideas,tell me~
	$type = 'json';
	if( strpos($requesturl,'.xml') !== false ){
		$type='xml';
	}
	else if( strpos($requesturl,'.json') !== false ){
		$type='json';
	}
	else{
		//since We only need to make twitter client to work
		header($_SERVER["SERVER_PROTOCOL"]." 501 Not Implemented");	
		exit();
	}
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
		foreach($_POST as $key => $value){
			$_POST[$key] = $key.'='.urlencode($value);
		}
		$curlopts[CURLOPT_POSTFIELDS] = implode('&',$_POST);
	}
	$curlopts[CURLOPT_RETURNTRANSFER] = true;
	$curlopts[CURLOPT_HTTPHEADER] = array('Expect:');
	$curlopts[CURLOPT_HEADERFUNCTION] = 'echoheader';
	curl_setopt_array($ch,$curlopts);
	$ret = curl_exec($ch);
	if($type == 'json'){
		$ret = str_replace(json_encode("http://static.twitter.com/images/default_profile_normal.png"),json_encode($apiurl."default_profile_normal.png"),$ret);
	}
	else if ($type == 'xml'){
		$ret = str_replace('http://static.twitter.com/images/default_profile_normal.png',$apiurl.'default_profile_normal.png',$ret);
	}
	echo $ret;
	file_put_contents('ret',$ret);
	if( $ret === false ){
		dolog(curl_error($ch));
		exit();
	}
	dolog();
?>
