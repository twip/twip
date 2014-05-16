<?php
require(__ROOT__.'/include/twitteroauth.php');
require(__ROOT__.'/image_proxy.php');
class twip{
    const PARENT_API = 'https://api.twitter.com/';
    const PARENT_SEARCH_API = 'http://search.twitter.com/';
    const ERR_LOGFILE = 'err.txt';
    const LOGFILE = 'log.txt';
    const LOGTIMEZONE = 'Etc/GMT-8';
    const BASE_URL = 'http://yegle.net/twip/';
    const API_VERSION = '1.1';

    private function push_entities(&$status, &$entities, $type) {
        if (isset($status->entities->$type)) {
            foreach($status->entities->$type as &$entity) {
                $entity->from = $type;
                array_push($entities, $entity);
            }
            $status->entities->$type = array();
        }
        if ($type === 'media' && isset($status->extended_entities->$type)) {
            $status->extended_entities->$type = array();
        }
    }
    public function replace_tco_json(&$status){
        if(!isset($status->entities)){
            return;
        }

        $shift=0;
        mb_internal_encoding('UTF-8');

        $entities = array();
        $this->push_entities($status, $entities, 'urls');
        $this->push_entities($status, $entities, 'media');
        $this->push_entities($status, $entities, 'symbols');
        $this->push_entities($status, $entities, 'hashtags');
        $this->push_entities($status, $entities, 'user_mentions');

        // note that usort is not stable
        usort($entities, function($a, $b) {
            return $a->indices[0] - $b->indices[0];
        });

        $last = -1; // last start
        $lastlen = 0;
        $diff = 0;
        foreach($entities as &$entity){
            if (isset($entity->media_url_https)) {
                $newtext = $entity->media_url_https;
            }
            elseif (isset($entity->expanded_url)) {
                $newtext = $entity->expanded_url;
            }
            else {
                $newtext = NULL;
            }
            if ($entity->indices[0] === $last) {
                $entity->indices[0] += $shift - $diff + $lastlen;
                $entity->indices[1] += $shift - $diff + $lastlen;
                if ($newtext) {
                    $status->text = mb_substr($status->text, 0, $entity->indices[0] - 1) . ' ' . $newtext .  mb_substr($status->text, $entity->indices[0] - 1);
                    $shift += mb_strlen($newtext) + 1;
                }
            }
            else {
                $last = $entity->indices[0];
                $entity->indices[0] += $shift;
                $entity->indices[1] += $shift;
                if ($newtext) {
                    $status->text = mb_substr($status->text, 0, $entity->indices[0]) . $newtext . mb_substr($status->text, $entity->indices[1]);
                    $diff = mb_strlen($newtext) - mb_strlen($entity->url);
                    $shift += $diff;
                }
            }
            if ($newtext) {
                $entity->indices[1] = $entity->indices[0] + mb_strlen($newtext);
                $entity->url = $newtext;
                $lastlen = mb_strlen($newtext) + 1;
            }

            $from = $entity->from;
            unset($entity->from);
            array_push($status->entities->$from, $entity);
            if ($from === 'media' && isset($status->extended_entities->$from)) {
                array_push($status->extended_entities->$from, $entity);
            }
        }
    }

    public function json_x86_decode($in){
        $in = preg_replace('/id":(\d+)/', 'id":"s\1"', $in);
        return json_decode($in);
    }
    public function json_x86_encode($in){
        $in = json_encode($in);
        return preg_replace('/id":"s(\d+)"/', 'id":\1', $in);
    }

    public function parse_entities($status){
        if($this->o_mode_parse_entities){
            $j = is_string($status) ? $this->json_x86_decode($status) : $status;
            if(is_array($j)){
                foreach($j as &$s){
                    $s = $this->parse_entities($s);
                }
            }
            else {
                $this->replace_tco_json($j);
                if(isset($j->status)){
                    $this->replace_tco_json($j->status);
                }
                if(isset($j->retweeted_status)){
                    $this->replace_tco_json($j->retweeted_status);
                }
                if(isset($j->status->retweeted_status)){
                    $this->replace_tco_json($j->status->retweeted_status);
                }
            }
            return is_string($status) ? $this->json_x86_encode($j) : $j;
        }
        return $status;
    }



    function __construct($options = null){
        $this->parse_variables($options);

        # Import all filters
        foreach(glob('filters/*.php') as $f) {
            include_once($f);
        }
        unset($f);

        ob_start();
        $compressed = $this->compress && Extension_Loaded('zlib') && ob_start("ob_gzhandler");

        if($this->mode=='t'){
            $this->transparent_mode();
        }
        else if($this->mode=='o'){
            $this->override_mode();
        }
        else if($this->mode=='i'){
            $this->override_mode(true);
        }
        else{
            header('HTTP/1.0 400 Bad Request');
            exit();
        }

        $str = ob_get_contents();
        if ($compressed) ob_end_flush();
        header('Content-Length: '.ob_get_length());
        ob_flush();

        if($this->debug){
            print_r($this);
            print_r($_SERVER);
            file_put_contents('debug',ob_get_contents().$str);
            ob_clean();
        }
        if($this->dolog){
            file_put_contents('log',$this->method.' '.$this->request_uri."\n",FILE_APPEND);
        }
    }

    private function parse_variables($options){
        //parse options
        $this->parent_api = isset($options['parent_api']) ? $options['parent_api'] : self::PARENT_API;
        $this->api_version = isset($options['api_version']) ? $options['api_version'] : self::API_VERSION;
        $this->debug = isset($options['debug']) ? !!$options['debug'] : FALSE;
        $this->dolog = isset($options['dolog']) ? !!$options['dolog'] : FALSE;
        $this->compress = isset($options['compress']) ? !!$options['compress'] : FALSE;
        $this->oauth_key = $options['oauth_key'];
        $this->oauth_secret = $options['oauth_secret'];
        $this->oauth_key_get = $options['oauth_key_get'];
        $this->oauth_secret_get = $options['oauth_secret_get'];
        $this->o_mode_parse_entities = isset($options['o_mode_parse_entities']) ? !!$options['o_mode_parse_entities'] : FALSE;

        if(substr($this->parent_api, -1) !== '/') $this->parent_api .= '/';

        $this->base_url = isset($options['base_url']) ? trim($options['base_url'],'/').'/' : self::BASE_URL;
        if(preg_match('/^https?:\/\//i',$this->base_url) == 0){
            $this->base_url = 'http://'.$this->base_url;
        }

        //parse $_SERVER
        $this->method = $_SERVER['REQUEST_METHOD'];


        $this->parse_request_uri();
    }

    private function override_mode($imageproxy = FALSE){
        $tokenfile = glob('oauth/'.$this->password.'.*');
        if(!empty($tokenfile)){
            $access_token = @file_get_contents($tokenfile[0]);
        }
        if(empty($access_token)){
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Basic realm="Twip4 Override Mode"');
            echo 'You are not allowed to use this API proxy';
            exit();
        }
        $access_token = unserialize($access_token);
        $this->access_token = $access_token;
        $this->has_get_token = isset($access_token['oauth_token_get']);

        if($imageproxy){
            if($this->method=='POST'){
                echo imageUpload($this->oauth_key, $this->oauth_secret, $this->access_token);
            }else{
                echo 'The image proxy needs POST method.';
            }
            return;
        }

        if($this->request_uri == null){
            echo 'click <a href="'.$this->base_url.'oauth.php">HERE</a> to get your API url';
            return;
        }
        $this->parameters = $this->get_parameters();
        foreach(array('pc', 'earned') as $param) {
            unset($this->parameters[$param]);
        }
        $this->connection = new TwitterOAuth($this->oauth_key, $this->oauth_secret, $this->access_token['oauth_token'], $this->access_token['oauth_token_secret']);
        $this->connection_get = $this->has_get_token ? new TwitterOAuth($this->oauth_key_get, $this->oauth_secret_get, $this->access_token['oauth_token_get'], $this->access_token['oauth_token_secret_get']) : $this->connection;

        $filterName = Twip::encode_uri($this->forwarded_request_uri);
        if (!array_key_exists($filterName, $this->filters)) {
            $filterName = '_default';
        }
        $parts = parse_url($this->forwarded_request_uri);
        $raw_response = $this->filters[$filterName](array(
            'path' => $parts['path'],
            'method' => $this->method,
            'params' => $this->parameters,
            'self' => $this,
        ));
        echo $this->parse_entities($raw_response);
        return;
    }

    private function transparent_mode(){
        $this->uri_fixer();
        $ch = curl_init($this->request_uri);
        $this->request_headers = OAuthUtil::get_headers();

        // Don't parse POST arguments as array if emulating a browser submit
        if(isset($this->request_headers['Content-Type']) && 
                strpos($this->request_headers['Content-Type'], 'application/x-www-form-urlencoded') !== FALSE){
            $this->parameters = $this->get_parameters(false);
        }else{
            $this->parameters = $this->get_parameters(true);
        }

        // Process Upload image (currently only first file will proxy to Twitter)
        if(strpos($this->request_uri,'statuses/update_with_media') !== FALSE &&
            strpos(@$this->request_headers['Content-Type'], 'multipart/form-data') !== FALSE) {

            $this->parameters = preg_replace('/^@/', "\0@", $_POST);
            if(count($_FILES) > 0 && isset($_FILES['media'])) {
                $media = $_FILES['media'];
                $fn = is_array($media['tmp_name']) ? $media['tmp_name'][0] : $media['tmp_name'];
                $this->parameters["media[]"] = '@' . $fn;
                unset($this->request_headers['Content-Type']);
            }
        }

        $forwarded_headers = array(
            'User-Agent',
            'Authorization',
            'Content-Type',
            'X-Forwarded-For',
            'Expect',
            );
        foreach($forwarded_headers as $header){
            if(isset($this->request_headers[$header])){
                $this->forwarded_headers[] = $header.': '.$this->request_headers[$header];
            }
        }
        if(!isset($this->forwarded_headers['Expect'])) $this->forwarded_headers[] = 'Expect:';
        curl_setopt($ch,CURLOPT_HTTPHEADER,$this->forwarded_headers);
        curl_setopt($ch,CURLOPT_HEADERFUNCTION,array($this,'headerfunction'));
        if($this->method != 'GET'){
            curl_setopt($ch,CURLOPT_CUSTOMREQUEST,$this->method);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$this->parameters);
        }
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        $ret = curl_exec($ch);
        //fixme:redirect request back to twip,this is nasty and insecure...
        if(strpos($this->request_uri,'oauth/authorize?oauth_token=')!==FALSE){
            $ret = str_replace('<form action="https://api.twitter.com/oauth/authorize"','<form action="'.$this->base_url.'t/oauth/authorize"',$ret);
            $ret = str_replace('<div id="signin_form">','<h1><strong style="color:red">Warning!This page is proxied by twip and therefore you may leak your password to API proxy owner!</strong></h1><div id="signin_form">',$ret);
        }
        echo $ret;
    }

    private function uri_fixer(){
        // $api is the API request without version number
        list($version, $api) = $this->extract_uri_version($this->request_uri);

        // If user specified version, use that version. Else use default version
        $version = ($version == "") ? $this->api_version : $version;

        $this->request_headers['Host'] = 'api.twitter.com';

        if($version === "1") {
            header("HTTP/1.0 410 Gone");
            die();
        }

        $replacement = array(
            'pc=true' => 'pc=false', //change pc=true to pc=false
            '&earned=true' => '', //remove "&earned=true"
        );

        $api = str_replace(array_keys($replacement), array_values($replacement), $api);

        if( strpos($api,'oauth/') === 0 ) {
            // These API requests don't needs version string
            $this->request_uri = sprintf("%s%s", $this->parent_api, $api);
        }else{
            $this->request_uri = sprintf("%s%s/%s", $this->parent_api, $version, $api);
        }
    }

    public function extract_uri_version($uri){
        $re = '/^(([0-9.]+)\/)?(.*)/';

        preg_match($re, $uri, $matches);

        $version = $matches[2];
        $api = $matches[3];
        return array($version, $api);
    }

    private function parse_request_uri(){
        // old value
        //$full_request_uri = substr($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],strlen(preg_replace('/^https?:\/\//i','',$this->base_url)));
		if (strlen(dirname($_SERVER['SCRIPT_NAME'])) > 1) {
			$full_request_uri = substr(
				$_SERVER['REQUEST_URI'],
				strlen(dirname($_SERVER['SCRIPT_NAME'])));
		} else {
			$full_request_uri = $_SERVER['REQUEST_URI'];
		}

        $prefix = substr($full_request_uri, 0, 3);
        switch($prefix) {
            case '/o/':
                // full_request_uri:   /o/PASSWORD/1.1/xxx/xxx.json
                $this->mode = 'o';
                list($this->password, $forwarded_request_uri) = explode('/', substr($full_request_uri, 3), 2);
                $this->forwarded_request_uri = $this->request_uri = $forwarded_request_uri;
                break;
            case '/i/':
                $this->mode = 'i';
                // full_request_uri:   /i/?????
                // does this mode need this parsing anyway? @yegle
                list($this->password, $forwarded_request_uri) = explode('/', substr($full_request_uri, 3), 2);
                $this->forwarded_request_uri = $this->request_uri = $forwarded_request_uri;
                break;
            case '/t/':
                // full_request_uri:   /t/1.1/xxx/xxx.json
                $this->mode = 't';
                $this->request_uri = substr($full_request_uri, 3);
                break;
            default:
                $this->mode = 'UNKNOWN';
                break;
        }
    }

    public function headerfunction($ch,$str){
        if(strpos($str,'Content-Length:')!==FALSE){
            header($str);
        }
        $this->response_headers[] = $str;
        return strlen($str);
    }

    private function get_parameters($returnArray = TRUE){
        if($returnArray) {
            return $_REQUEST;
        }
        else {
            return http_build_query($_REQUEST);
        }
    }

    public static function encode_uri($raw_uri) {
        $parts = parse_url($raw_uri);
        $path = $parts['path'];
        $replacements = array(
            '/' => '__',
            '.' => '_',
        );
        $regex_replacements = array(
            '/\d{2,}/' => 'NUMBER',
        );

        $path = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $path
        );
        $path = preg_replace(
            array_keys($regex_replacements),
            array_values($regex_replacements),
            $path
        );
        return $path;
    }
}
?>
