<?php

$filterName = basename(__FILE__, '.php');

$this->filters[$filterName] = function($args) {
    $url = sprintf("https://api.twitter.com/%s", $args['path']);
    $headers = OAuthUtil::get_headers();
    // Check actually media uplaod
    if(strpos(@$headers['Content-Type'], 'multipart/form-data') === FALSE
        or count($_FILES) == 0 or !isset($_FILES['media'])) {
            header('HTTP/1.0 400 Bad Request');
            return;
        }

    $auth_headers = $args['self']->connection->getOAuthRequest(
        $url, $args['method'], null)->to_header();
    $forwarded_headers = array(
        "Host: api.twitter.com",
        $auth_headers,
        "Expect:");
    $parameters = preg_replace('/^@/', "\0@", $_POST);

    $media = $_FILES['media'];
    $fn = is_array($media['tmp_name']) ? $media['tmp_name'][0] : $media['tmp_name'];
    $parameters["media[]"] = '@' . $fn;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $forwarded_headers);
    curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($args['self'],'headerfunction'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $ret = curl_exec($ch);
    return $ret;
};
