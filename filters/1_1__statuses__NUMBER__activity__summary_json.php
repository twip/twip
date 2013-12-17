<?php

$filterName = basename(__FILE__, '.php');

$this->filters[$filterName] = function($args) {
    // TODO: should we try to parse some official API and get the correct response?
    return '{"retweeters_count":"0","retweeters":[],"repliers_count":"0","repliers":[],"favoriters":[],"favoriters_count":"0"}';
};
