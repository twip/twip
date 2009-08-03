<?php
	function echoheader($ch,$str){
		header($str);
		return strlen($str);
	}
	function dolog(){
		global $requesturl;
		global $method;
		global $isauth;
		$log = date('Y-m-d H:i:s').' '.$_SERVER['REMOTE_ADDR'].' '.$requesturl.' '.$method.' '.$isauth."\n";
		file_put_contents($logfile,$log,FILE_APPEND);
	}
?>
