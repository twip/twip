<?php
session_start();
require('twip.php');
$options['oauth_key'] = '7YyEToJbicfnTFVUNAl3yQ';
$options['oauth_secret'] = 'cnJu23PWgiPKv4xFtGKNnTO5MqQafnwdd3zGIDtMA';
$options['base_url'] = 'http://yegle.net/twip/';
$twip = new twip($options);
?>
