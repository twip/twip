<?php

/**
 * @file
 * A single location to store oauth configuration.
 */

//IMPORTANT! never ever set OAUTH_DIR where web user can access! 
//End with '/'
define('OAUTH_DIR','/home/user/oauth/');
define('CONSUMER_KEY', '');
define('CONSUMER_SECRET', '');
define('OAUTH_CALLBACK', 'http://127.0.0.1/twip/callback.php');
define('SECURE_KEY','kpxaZj8nSoCt2OFddE3xI');