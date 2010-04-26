<?php
/*
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is Twip.
 *
 * The Initial Developer of the Original Code is yegle <http://yegle.net> and 
 * xmxsuperstar <http://www.xmxsuperstar.com/>. All Rights Reserved.
 *
 * Contributor(s): bronco <http://heybronco.net>
 */
	if (!empty($_GET['login']))//force to re-auth
	{
		session_start();
		$_SESSION['dologin'] = true;
		header("Location: ./login.php");
	}

    require('twip.php');
    $options['webroot'] = '/twip';    //set this option to '/' if you are using host root
    $options['debug'] = false;
    $options['dolog'] = false;
    $options['enable_oauth'] = true;
    $options['logfile'] = 'log.txt';
    $options['replace_shorturl'] = true;
    $options['docompress'] = false;
    $options['cgi_workaround'] = false; //change this to "YES I DO NEED THE WORKAROUND!" to make this work
    $options['parent_api'] = 'http://api.twitter.com/1';
    $options['parent_search_api'] = 'http://search.twitter.com';

    //if you want to setup an API for limited users
    //set private_api true and set allowed_users accordinary
    //no space is allowed in allowed_users variable
    //seperate users using comma.
    $options['private_api'] = array(
        false,
        "allowed_users" => "",
        );

    $twip = new twip($options);

?>
