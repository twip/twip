<?php
require('twip.php');
require('config.php');
$options['oauth_key'] = @constant('OAUTH_KEY');
$options['oauth_secret'] = @constant('OAUTH_SECRET');
$options['oauth_key_get'] = @constant('OAUTH_KEY_GET');
$options['oauth_secret_get'] = @constant('OAUTH_SECRET_GET');
$options['base_url'] = @constant('BASE_URL');
$options['o_mode_parse_entities'] = @constant('O_MODE_PARSE_ENTITIES');
$options['debug'] = @constant('DEBUG');
$options['dolog'] = @constant('DOLOG');
$options['compress'] = @constant('COMPRESS');
$options['api_version'] = @constant('API_VERSION');
$twip = new twip($options);
?>
