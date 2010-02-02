<?
    require('../config.php');
    require('../lib/twip.php');
    $options['dest_api'] = 'http://twitter.com';
    $options['api_dir'] = $options['webroot'].'/api';
    $twip = new twip($options);
?>