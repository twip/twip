<?php

$filterName = basename(__FILE__, '.php');

$this->filters[$filterName] = function($args) {
    $url = sprintf("https://api.twitter.com/%s", $args['path']);

    if ($args['method'] === 'POST') {
        return $args['self']->connection_get->post($url, $args['params']);
    } else {
        return $args['self']->connection_get->get($url, $args['params']);
    }
};
