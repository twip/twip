<?php
	function checkConfig() {
		global $webroot;
		if ( !isset($webroot) ){
			$webroot  = dirname(substr(__FILE__,strlen($_SERVER['DOCUMENT_ROOT'])));
			if ( $webroot == '/' ) {
				$webroot = '';
			}
		}
	
		//if user set $twitter or $twsearch to the twip itself, server will lose response
		global $twitter;
		global $twsearch;
		if( (strpos($twitter, $_SERVER['HTTP_HOST']) !== false) 
				|| (strpos($twsearch, $_SERVER['HTTP_HOST']) !== false) ){
			////fixme: need output some error headers or msgs?
			exit();
		}
		
		//remove the end / in $twitter if it exists
		if(eregi("/$", $twitter)) {
			$twitter=eregi_replace("/$", "", $twitter);
		}
		
		//remove the end / in $twsearch if it exists
		if(eregi("/$", $twsearch)) {
			$twsearch=eregi_replace("/$", "", $twsearch);
		}
	}

	function echoheader($ch,$str){
		if(strpos($str,'Content-Length:') === false ){
			if(strpos($str,'Set-Cookie')!==false){
				$str = str_replace('.twitter.com',$_SERVER['SERVER_NAME'],$str);
				header($str);
			}
			else header($str);
			header($str);
		}
		return strlen($str);
	}
	function dolog($str = ''){
		global $dolog;
		if($dolog) {
			global $logfile;
			global $requesturl;
			global $method;
			global $isauth;
			$log = date('Y-m-d H:i:s').' '.$_SERVER['REMOTE_ADDR'].' '.$_SERVER['HTTP_USER_AGENT'].' '.$requesturl.' '.$method.' '.$isauth.' '.$str."\n";
			file_put_contents($logfile,$log,FILE_APPEND);
		}
	}
	function isSSL(){
		if(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 1 || $_SERVER['HTTPS'] == 'on' ) ) return true;
		else return false;
	}
?>
