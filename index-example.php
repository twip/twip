<?
    require('twip.php');
    $options['webroot'] = '/twip';    //set this option to '/' if you are using host root
    $options['debug'] = false;
    $options['dolog'] = true;
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
        "allowed_users" => "user_1,user_2,user_3",
        );

    $twip = new twip($options);
?>
