<?
	require('twip.php');
	if(!file_exists('config.php')) {
		//if no config.php provided by user, use config-example.php
		copy('config-example.php', 'config.php');
	}
	require('config.php');

	$twip = new twip($options);
?>
