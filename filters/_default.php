<?php

$filterName = basename(__FILE__, '.php');

$this->filters[$filterName] = function($args) {
    if (substr($args['path'], 0, 4) != '1.1/') {
        $url = sprintf("https://api.twitter.com/1.1/%s", $args['path']);
    } else {
        $url = sprintf("https://api.twitter.com/%s", $args['path']);
    }
    if ($args['method'] === 'POST') {
        return $args['self']->connection->post($url, $args['params']);
    } else {
        return $args['self']->connection_get->get($url, $args['params']);
    }
};
