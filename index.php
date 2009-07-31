<?php
	$webroot = '/twip/trunk'; // where you put your twip index.php file.
	$twitter = 'http://twitter.com'; //the upper api address. you can set this to another api proxy.
	$logfile = 'log.txt';
	//$authlist=array(
	//	'account/verify_credentials\.(json|xml)',
	//);
	$request = array();
	$request['url'] = substr( $_SERVER['REQUEST_URI'] , -strlen($_SERVER['REQUEST_URI']) + strlen($webroot) );
	$request['method'] = ( $_POST==false ) ? 'get' : 'post';
	$ch = curl_init($twitter.$request['url']);
	$curlopts = array();
	if(isset($_SERVER['PHP_AUTH_USER'])){
		$isauth = 'auth';
		$curlopts[CURLOPT_USERPWD] = $_SERVER['PHP_AUTH_USER'].':'.$_SERVER['PHP_AUTH_PW'];
	}
	if($request['method']=='post'){
		$curlopts[CURLOPT_POST] = true;
		$curlopts[CURLOPT_POSTFIELDS] = $_POST;
	}
	$curlopts[CURLOPT_RETURNTRANSFER] = true;
	$curlopts[CURLOPT_HTTPHEADER] = array('Expect:');
	curl_setopt_array($ch,$curlopts);
	$ret = curl_exec($ch);
	$log = date('YmdHis').' '.$_SERVER['REMOTE_ADDR'].' '.$request['url'].' '.$request['method'].' '.$isauth."\n";
	file_put_contents($logfile,$log,FILE_APPEND);
	file_put_contents('ret',$ret);
	echo $ret;
?>
