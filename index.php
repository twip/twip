<?php
	if(!file_exists('config.php')) {
		//if no config.php provided by user, use config-example.php
		copy('config-example.php', 'config.php');
	}

	require('config.php');
	require('func.php');
	require_once( 'include/twitterOAuth.php' );
	
	checkConfig();

	if(isSSL()){
		$apiurl = 'https://'.$_SERVER['HTTP_HOST'].$webroot.'/';
	}
	else{
		$apiurl = 'http://'.$_SERVER['HTTP_HOST'].$webroot.'/';
	}
	$requesturl = substr( $_SERVER['REQUEST_URI'] , strlen($webroot) );
	if($requesturl == '/'){
		echo '<html><head><title>Twip,Twitter API proxy in PHP.</title></head><body><h2>Twip,Twitter API proxy in PHP.</h1><p>This is a Twitter API proxy,and is not intend to be viewed in a browser.<br />Please use '.$apiurl.'  as a Twitter API URI in your Twitter Client.<br />Visit <a href="http://code.google.com/p/twip/">Twip </a> for more details. View test page <a href="test.php">HERE</a>.View oauth page <a href="oauth.php">HERE</a></p></body></html>';
		exit();
	}
	else if(substr($requesturl,0,4)=='/api') $requesturl = substr($requesturl,4);

	$method = $_SERVER['REQUEST_METHOD'];
	
	//oauth
	if( $enable_oauth && isset( $_SERVER['PHP_AUTH_USER'] ) && $_SERVER['PHP_AUTH_USER'] && file_exists( $OAUTH_DIR.$_SERVER['PHP_AUTH_USER'] ) ){
		if( $limit_user && !userallowed($_SERVER['PHP_AUTH_USER'])){
			header('HTTP/1.1 403 Forbidden');
			exit();
		}
		list( $access_token, $access_token_secret ) = explode( '|', file_get_contents($OAUTH_DIR.file_get_contents( $OAUTH_DIR.$_SERVER['PHP_AUTH_USER'] )) );
		$to = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET, $access_token, $access_token_secret );
		list( $url, $args ) = explode( '?', $requesturl );
		if( $method == 'POST' ){
			if( get_magic_quotes_gpc() ){
				foreach( $_POST as $key =>$value ){
					$_POST[$key] = stripslashes( $_POST[$key] );
				}
			}
			$content = $to->OAuthRequest( $twitter.$url, $_POST, $method );
		}
		else{
			$args = explode( '&',$args );
			$arr = array();
			foreach( $args as $arg ){
				list( $key, $value ) = explode( '=', $arg );
				$arr[$key] = $value;
			}
			$content = $to->OAuthRequest( $twitter.$url, $arr , $method );
		}
		header('Content-Length: '.strlen($content));
		$isauth = 'oauth.'.$_SERVER['PHP_AUTH_USER'];
		if($docompress && Extension_Loaded('zlib')) {
			if(!Ob_Start('ob_gzhandler')){
				Ob_Start();
			}
			else $isauth.='.gzip';
		}

		echo $content;
		dolog();

		if($docompress && Extension_Loaded('zlib')) {
			Ob_End_Flush();
		}
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
	
	//with Twitter search API
	if(strpos($requesturl,'/search.') !== false || strpos($requesturl,'/trends') !== false ){
		$ch = curl_init($twsearch.$requesturl);
	}
	else {
		$ch = curl_init($twitter.$requesturl);
	}
	
	//workaround for running PHP in cgi mode
	//fixme : this works on a godaddy virtual host, but I didn't test much.
	if( !isset($_SERVER['PHP_AUTH_USER']) ){
		$auth = empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION']:$_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
		$a = base64_decode( substr($auth,6)) ;
		list($name, $password) = explode(':', $a);
		$_SERVER['PHP_AUTH_USER'] = $name;
		$_SERVER['PHP_AUTH_PW']    = $password;
		if( $name == '') $isauth = 'noauth';
		else $isauth = 'cgiauth.'.$_SERVER['PHP_AUTH_USER'];
	}
	else {
		$isauth = 'modauth.'.$_SERVER['PHP_AUTH_USER'];
	}
	
	$curlopts = array();
	if(isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] !='' ){
		if( $limit_user && !userallowed($_SERVER['PHP_AUTH_USER'])){
			header('HTTP/1.1 403 Forbidden');
			exit();
		}
		$curlopts[CURLOPT_USERPWD] = $_SERVER['PHP_AUTH_USER'].':'.$_SERVER['PHP_AUTH_PW'];
	}
	//cache support
	//only cache non-auth and GET request
	//fixme: no log if cache hits
	$pattern = 'since_id=[0-9]*';
	$replace = 'since_id=';
	$cache_file = $cache_dir . '/' . urlencode(ereg_replace($pattern,$replace,$requesturl));
	if( $isauth =='noauth' && $cache && $method == 'GET' ) {
		if(strpos($requesturl,'/search.') === false  ){
			if(!file_exists( $cache_dir )){
				mkdir( $cache_dir );
			}
			if( is_dir($cache_dir) ){
				if( file_exists($cache_file) && (time() - filemtime($cache_file) <= $cache_timeout) ){
					readfile($cache_file);
					exit();
				}
			}
		}
	}
	if( $method =='POST' || $method == 'DELETE' ){
		$curlopts[CURLOPT_POST] = true;
		foreach($_POST as $key => $value){
			if(get_magic_quotes_gpc()){
				$_POST[$key] = $key .'='.urlencode(stripslashes($_POST[$key]));
			}
			else{
				$_POST[$key] = $key.'='.urlencode($value);
			}
		}
		$curlopts[CURLOPT_POSTFIELDS] = implode('&',$_POST);
	}
	$curlopts[CURLOPT_RETURNTRANSFER] = true;
	$curlopts[CURLOPT_HTTPHEADER] = array('Expect:');
	$curlopts[CURLOPT_HEADERFUNCTION] = 'echoheader';
	if( isset( $_SERVER['HTTP_USER_AGENT'] ) ) $curlopts[CURLOPT_USERAGENT] = $_SERVER['HTTP_USER_AGENT'] ;

	//proxy support
	if( $useproxy && isset($proxy_type)){
		if( $proxy_type =='http' ) {
			$curlopts[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP;
		}
		else if( $proxy_type =='socks5' ) {
			$curlopts[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5;
		}

		if( isset($proxy) && $proxy !='' ){
			$curlopts[CURLOPT_PROXY] = $proxy;
			if(isset($proxy_auth) && $proxy_auth !='' ){
				$curlopts[CURLOPT_PROXYUSERPWD] = $proxy_auth;
			}
		}
	}

	curl_setopt_array($ch,$curlopts);
	$ret = curl_exec($ch);
	if($type == 'json'){
		$ret = str_replace(json_encode("http://static.twitter.com/images/default_profile_normal.png"),json_encode($apiurl."default_profile_normal.png"),$ret);
	}
	else if ($type == 'xml'){
		$ret = str_replace('http://static.twitter.com/images/default_profile_normal.png',$apiurl.'default_profile_normal.png',$ret);
	}
	header('Content-Length: '.strlen($ret));

	if($docompress && Extension_Loaded('zlib')) {
		if(!Ob_Start('ob_gzhandler')){
			Ob_Start();
		}
		else $isauth.='.gzip';
	}

	echo $ret;
	
	if($docompress && Extension_Loaded('zlib')) {
		Ob_End_Flush();
	}
	
	if( $isauth == 'noauth' && $cache && $method == 'GET'){
		if(strpos($requesturl,'/search.') === false ){
			file_put_contents( $cache_file , $ret);
		}
	}
	
	dolog();
?>
