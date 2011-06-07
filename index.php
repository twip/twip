<?php
require('twip.php');
require('config.php');
$options['oauth_key'] = @constant('OAUTH_KEY');
$options['oauth_secret'] = @constant('OAUTH_SECRET');
$options['base_url'] = @constant('BASE_URL');
$options['debug'] = @constant('DEBUG');
$options['dolog'] = @constant('DOLOG');
$options['compress'] = @constant('COMPRESS');
$options['api_version'] = @constant('API_VERSION');
$options['proxy'] = @constant('PROXY');
$options['proxy_type'] = @constant('PROXY_TYPE');
$twip = new twip($options);
?>
