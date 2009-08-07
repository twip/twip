<?php
	$webroot = ''; // If the $webroot doesn't recognize correctly, you can manually specify one here.
	$twitter = 'http://twitter.com'; //the upper api address. you can set this to another api proxy.
	$twsearch = 'http://search.twitter.com'; //the upper search api address. you can set this to another search api proxy.
	$dolog = true;
	$logfile = 'log.txt';
	date_default_timezone_set('Etc/GMT-8'); //define your timezone. If you are in China, leave this as it is. #ChinaBlocksTwitter!

	if ( $webroot == '' ){
		$webroot  = dirname(substr(__FILE__,strlen($_SERVER['DOCUMENT_ROOT'])));
	}

?>
