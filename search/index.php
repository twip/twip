<?
    require('../config.php');
    require('../lib/twip.php');
    $options['dest_api'] = 'http://search.twitter.com';
    $options['api_dir'] = $options['webroot'].'/search';
    $twip = new twip($options);
?>