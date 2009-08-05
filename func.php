<?php
	function echoheader($ch,$str){
		if(strpos($str,'Content-Length:') !== false ){
			header($str);
		}
		return strlen($str);
	}
	function dolog($str = ''){
		global $logfile;
		global $requesturl;
		global $method;
		global $isauth;
		$log = date('Y-m-d H:i:s').' '.$_SERVER['REMOTE_ADDR'].' '.$requesturl.' '.$method.' '.$isauth.' '.$str."\n";
		file_put_contents($logfile,$log,FILE_APPEND);
	}
	function isSSL(){
		if($_SERVER['HTTPS'] == 1 || $_SERVER['HTTPS'] == 'on') return true;
		else return false;
	}
?>
