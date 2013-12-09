<?php

$filterName = basename(__FILE__, '.php');

$this->filters[$filterName] = function($args) {
    $url = "https://api.twitter.com/1.1/statuses/home_timeline.json";
    if ($args['method'] === 'POST') {
        $ret = $args['self']->connection->post($url, $args['params']);
    } else {
        $ret = $args['self']->connection_get->get($url, $args['params']);
    }
    $raw_obj = json_decode($ret);
    $ret = array(
        "twitter_objects" => array(
            "users" => array(),
            "tweets" => array(),
            "event_summaries" => array(),
        ),
        "response" => array(
            "timeline" => array(),
        ),
    );
    $tweets = &$ret['twitter_objects']['tweets'];
    $users = &$ret['twitter_objects']['users'];
    $timeline = &$ret['response']['timeline'];
    foreach($raw_obj as $tweet) {
        $user = $tweet->user;
        $users[$user->id_str] = $user;
        unset($tweet->user);
        $tweet->user = array(
            "id" => $user->id,
            "id_str" => $user->id_str,
        );
        $tweets[strval($tweet->id)] = $tweet;
        $timeline[] = array(
            'tweet' => array(
                'id' => strval($tweet->id),
            ),
            'entity_id' => array(
                'type' => 'tweet',
                'ids' => array(
                    strval($tweet->id),
                ),
            ),
        );
    }
    return json_encode($ret);
};
