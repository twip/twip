<?
	require('twip.php');
	$options['webroot'] = '/s/branches/yegle';
	$options['debug'] = false;
	$options['dolog'] = true;
	$options['logfile'] = 'log.txt';

	$options['replace_shorturl'] = true;

	$twip = new twip($options);
?>
