<?php
session_start();
require('twip.php');
require('oauth_key.php');
$options['oauth_key'] = OAUTH_KEY;
$options['oauth_secret'] = OAUTH_SECRET;
$options['base_url'] = 'http://yegle.net/twip/';
$twip = new twip($options);
?>
