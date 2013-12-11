<?php

$filterName = basename(__FILE__, '.php');

$this->filters[$filterName] = function($args) {
    return sprintf(
        "oauth_token=%s&oauth_token_secret=%s&user_id=%s&screen_name=%s&x_auth_expires=0\n",
        $args['self']->access_token['oauth_token'],
        $args['self']->access_token['oauth_token_secret'],
        $args['self']->access_token['user_id'],
        $args['self']->access_token['screen_name']
    );
};
