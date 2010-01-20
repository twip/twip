<?
    require('twip.php');
    $options['webroot'] = '/twip';    //set this option to '/' if you are using host root
    $options['debug'] = false;
    $options['dolog'] = true;
    $options['logfile'] = 'log.txt';
    $options['replace_shorturl'] = true;
    $options['docompress'] = false;
    $options['cgi_workaround'] = false; //change this to "YES I DO NEED THE WORKAROUND!" to make this work
    $options['parent_api'] = 'http://twitter.com';
    $options['parent_search_api'] = 'http://search.twitter.com';

    $twip = new twip($options);
?>
