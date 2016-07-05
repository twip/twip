<?php

$filterName = basename(__FILE__, '.php');

$this->filters[$filterName] = function($args) {
    $url = sprintf("https://upload.twitter.com/%s", $args['path']);

    $headers = OAuthUtil::get_headers();
    // Check actually media uplaod
    if(strpos(@$headers['Content-Type'], 'multipart/form-data') === FALSE) {
        return $args['self']->connection->post($url, $args['params']);
    }

    $auth_headers = $args['self']->connection->getOAuthRequest(
        $url, $args['method'], null)->to_header();
    $forwarded_headers = array(
        "Host: upload.twitter.com",
        $auth_headers,
        "Expect:");

    $parameters = $args['params'];
    $media = @$_FILES['media'];
    if ($media) {
        $fn = is_array($media['tmp_name']) ? $media['tmp_name'][0] : $media['tmp_name'];

        if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
            $cfn = curl_file_create($fn, 'application/octet-stream', 'media');
            $parameters["media"] = $cfn;
        } else {
            $parameters = preg_replace('/^@/', "\0@", $_POST);
            $parameters["media"] = '@' . $fn;
        }
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $forwarded_headers);
    curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($args['self'],'headerfunction'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $ret = curl_exec($ch);
    return $ret;
};
