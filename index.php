<?php
	$webroot = '/twip/trunk'; // where you put your twip index.php file.
	$twitter = 'http://twitter.com'; //the upper api address. you can set this to another api proxy.
	$logfile = 'log.txt';
	date_default_timezone_set('Etc/GMT-8'); //define your timezone. If you are in China, leave this as it is. #ChinaBlocksTwitter!


	$request = array();
	$url = substr( $_SERVER['REQUEST_URI'] , -strlen($_SERVER['REQUEST_URI']) + strlen($webroot) );
	$method = $_SERVER['REQUEST_METHOD'];
	$ch = curl_init($twitter.$url);
	$curlopts = array();
	if(isset($_SERVER['PHP_AUTH_USER'])){
		$isauth = 'auth';
		$curlopts[CURLOPT_USERPWD] = $_SERVER['PHP_AUTH_USER'].':'.$_SERVER['PHP_AUTH_PW'];
	}
	if( $method =='POST' || $method == 'DELETE' ){
		//file_put_contents('post',implode($_POST));
		$curlopts[CURLOPT_POST] = true;
		if(get_magic_quotes_gpc()){
			foreach($_POST as $key => $value){
				$_POST[$key] = stripslashes($_POST[$key]);
			}
		}
		$curlopts[CURLOPT_POSTFIELDS] = $_POST;
	}
	$curlopts[CURLOPT_RETURNTRANSFER] = true;
	$curlopts[CURLOPT_HTTPHEADER] = array('Expect:');
	$curlopts[CURLOPT_HEADER] = true;
	curl_setopt_array($ch,$curlopts);
	$ret = curl_exec($ch);
	curl_exec($ch);
	$log = date('Y-m-d H:i:s').' '.$_SERVER['REMOTE_ADDR'].' '.$url.' '.$method.' '.$isauth."\n";
	file_put_contents($logfile,$log,FILE_APPEND);
	$headerlen = curl_getinfo( $ch,CURLINFO_HEADER_SIZE );
	$header = substr($ret,0,$headerlen);
	$headerarr = explode('<br />',nl2br($header));
	foreach($headerarr as $item){
		header(trim($item));
	}
	echo substr($ret,-strlen($ret) + $headerlen  );
	//file_put_contents('ret',$ret);
?>
