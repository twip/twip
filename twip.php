<?php
require('include/twitteroauth.php');
class twip{
    const PARENT_API = 'https://api.twitter.com/';
    const PARENT_SEARCH_API = 'http://search.twitter.com/';
    const ERR_LOGFILE = 'err.txt';
    const LOGFILE = 'log.txt';
    const LOGTIMEZONE = 'Etc/GMT-8';
    const BASE_URL = 'http://yegle.net/twip/';

    public function twip($options = null){
        $this->parse_variables($options);

        ob_start();
        $compressed = $this->compress && Extension_Loaded('zlib') && ob_start("ob_gzhandler");
        
        if($this->mode=='t'){
            $this->transparent_mode();
        }
        else if($this->mode=='o'){
            $this->override_mode();
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
        file_put_contents('log',$this->method.' '.$this->request_uri."\n",FILE_APPEND);
    }

    private function echo_token(){
            $str = 'oauth_token='.$this->access_token['oauth_token']."&oauth_token_secret=".$this->access_token['oauth_token_secret']."&user_id=".$this->access_token['user_id']."&screen_name=".$this->access_token['screen_name'].'&x_auth_expires=0'."\n";
            echo $str;
    }

    private function parse_variables($options){
        //parse options
        $this->parent_api = isset($options['parent_api']) ? $options['parent_api'] : self::PARENT_API;
        $this->parent_search_api = isset($options['parent_search_api']) ? $options['parent_search_api'] : self::PARENT_SEARCH_API;
        $this->debug = isset($options['debug']) ? !!$options['debug'] : FALSE;
        $this->compress = isset($options['compress']) ? !!$options['compress'] : FALSE;
        $this->oauth_key = $options['oauth_key'];
        $this->oauth_secret = $options['oauth_secret'];

        $this->base_url = isset($options['base_url']) ? trim($options['base_url'],'/').'/' : self::BASE_URL;
        if(strpos($this->base_url,'https://')===0){
            $this->base_url = preg_replace('/https:\/\/(.*)/','http://${1}',$this->base_url);
        }
        if(strpos($this->base_url,'http://')===FALSE){
            $this->base_url = 'http://'.$this->base_url;
        }

        //parse $_SERVER
        $this->method = $_SERVER['REQUEST_METHOD'];


        $this->parse_request_uri();
    }

    private function override_mode(){
        $access_token = @file_get_contents('oauth/'.$this->username.'.'.$this->password);
        if($access_token === FALSE){
            echo 'You are not allowed to use this API proxy';
            exit();
        }
        $access_token = unserialize($access_token);
        $this->access_token = $access_token;
        if($this->request_uri == 'oauth/access_token'){
            $this->echo_token();
            exit();
        }
        if($this->request_uri == null){
            echo 'click <a href="'.$this->base_url.'oauth.php">HERE</a> to get your API url';
            exit();
        }
        $this->uri_fixer();
        $this->connection = new TwitterOAuth($this->oauth_key, $this->oauth_secret, $this->access_token['oauth_token'], $this->access_token['oauth_token_secret']);
        if($this->method=='POST'){
            echo $this->connection->post($this->request_uri,$_POST);
        }
        else{
            echo $this->connection->get($this->request_uri);
        }
    }

    private function transparent_mode(){
        $this->uri_fixer();
        $ch = curl_init($this->request_uri);
        $this->request_headers = OAuthUtil::get_headers();
        if($this->api_type == 'search'){
            $this->request_headers['Host'] = 'search.twitter.com';
        }
        else{
            $this->request_headers['Host'] = 'api.twitter.com';
        }
        $forwarded_headers = array(
            'Host',
            'User-Agent',
            'Authorization',
            'Content-Type'
            );
        foreach($forwarded_headers as $header){
            if(isset($this->request_headers[$header])){
                $this->forwarded_headers[] = $header.': '.$this->request_headers[$header];
            }
        }
        curl_setopt($ch,CURLOPT_HTTPHEADER,$this->forwarded_headers);
        curl_setopt($ch,CURLOPT_HEADERFUNCTION,array($this,'headerfunction'));
        if($this->method == 'POST'){
            curl_setopt($ch,CURLOPT_POST,TRUE);
            curl_setopt($ch,CURLOPT_POSTFIELDS,@file_get_contents('php://input'));
            $this->post_fields = @file_get_contents('php://input');
        }
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        $ret = curl_exec($ch);
        //fixme:redirect request back to twip,this is nasty and insecure...
        if(strpos($this->request_uri,'oauth/authorize?oauth_token=')!==NULL){
            $ret = str_replace('<form action="https://api.twitter.com/oauth/authorize"','<form action="'.$this->base_url.'t/oauth/authorize"',$ret);
        }
        echo $ret;
    }

    private function uri_fixer(){
        if(preg_match('/^Twitter\/[^ ]+ CFNetwork\/[^ ]+ Darwin\/[^ ]+$/',$_SERVER['HTTP_USER_AGENT'])){
            if(strpos($this->request_uri,'trends') === 0){
                $this->request_uri = '1/'.$this->request_uri;
            }
        }
        if( substr($_SERVER['HTTP_USER_AGENT'],0,6) == 'twhirl' ){
            $this->request_uri = substr($this->request_uri,4);//remove "api/"
        }
        if($this->api_type == 'search'){
            $this->request_uri = $this->parent_search_api.$this->request_uri;
        }
        else{
            $this->request_uri = $this->parent_api.$this->request_uri;
        }
    }


    private function parse_request_uri(){
        $full_request_uri = substr('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],strlen($this->base_url));
        if(strpos($full_request_uri,'o/')===0){
            list($this->mode,$this->username,$this->password,$this->request_uri) = explode('/',$full_request_uri,4);
            $this->mode == 'o';
        }
        elseif(strpos($full_request_uri,'t/')===0){
            list($this->mode,$this->request_uri) = explode('/',$full_request_uri,2);
            $this->mode == 't';
        }
        if((strpos($this->request_uri,'search.') === 0)){
            $this->api_type = 'search';
        }
    }

    private function headerfunction($ch,$str){
        if(strpos($str,'Content-Length:')!==NULL){
            header($str);
        }
        $this->response_headers[] = $str;
        return strlen($str);
    }
}
?>
