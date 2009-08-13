<?php
	//$webroot = '';
	// If the $webroot doesn't recognize correctly, you can manually specify one here.
	$twitter = 'http://twitter.com'; //the upper api address. you can set this to another api proxy.
	$twsearch = 'http://search.twitter.com'; //the upper search api address. you can set this to another search api proxy.
	$dolog = true;
	$logfile = 'log.txt';
	date_default_timezone_set('Etc/GMT-8'); //define your timezone. If you are in China, leave this as it is. #ChinaBlocksTwitter!

	$debug = false;
	$useproxy = false;

	$cache = false;
	$cache_dir = 'cache';
	$cache_timeout = 300 ;
	
	//proxy setting, in case you need it...
	//note: if your proxy doesn't need authentication, leave $proxy_auth blank, or comment it out.

	//if you want to make twip work with tor...
	//$proxy_type = 'socks5';
	//$proxy = '127.0.0.1:9050';
	//$proxy_auth = 'username:password';
	
	//or if you want to make twip with a normal http proxy
	//$proxy_type = 'http';
	//$proxy = 'ip:port';
	//$proxy_auth = 'username:password';
?>
