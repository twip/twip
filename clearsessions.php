<?php
/**
 * @file
 * Clears PHP sessions and redirects to the connect page.
 */

/* Load and clear sessions */
require_once('oauth_config.php');
session_start();

if(!empty($_GET['removetoken']))
	if (!empty($_SESSION['user']) && file_exists(OAUTH_DIR.$_SESSION['user'].'.oauth'))
		unlink(OAUTH_DIR.$_SESSION['user'].'.oauth');

session_destroy();
session_start();
/* Redirect to page with the connect to Twitter option. */
header('Location: ./index.php');
