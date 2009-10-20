<?
	require('twip.php');
	$options['webroot'] = '/s/branches/yegle';
	$options['debug'] = false;
	$options['dolog'] = true;
	$options['logfile'] = 'log.txt';

	$options['replace_shorturl'] = true;


	//define your bit.ly login and API key here,needed to expand bit.ly and j.mp short URLs
	//you can find it here:http://bit.ly/account/
	$options['bitly_login'] = '';
	$options['bitly_api'] = '';

	//define your friendfeed login and remote key here
	//you can find it here: http://friendfeed.com/remotekey
	$options['ff_login'] = '';
	$options['ff_remotekey'] = '';
	$twip = new twip($options);
?>
