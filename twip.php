<?php
require('include/twitteroauth.php');
class twip{
    const PARENT_API = 'http://api.twitter.com/';
    const PARENT_SEARCH_API = 'http://search.twitter.com/';
    const ERR_LOGFILE = 'err.txt';
    const LOGFILE = 'log.txt';
    const LOGTIMEZONE = 'Etc/GMT-8';
    const BASE_URL = 'http://yegle.net/twip/';

    public function twip($options = null){
        ob_start();
        $this->parse_variables($options);
        if($this->mode=='t'){
            $this->transparent_mode();
        }
        else if($this->mode=='o'){
            $this->override_mode();
        }
        $str = ob_get_contents();
        file_put_contents('debug',$str);
        file_put_contents('log',$this->request_uri."\n",FILE_APPEND);
    }

    private function echo_token(){
            $str = 'oauth_token='.$this->access_token['oauth_token']."&oauth_token_secret=".$this->access_token['oauth_token_secret']."&user_id=".$this->access_token['user_id']."&screen_name=".$this->access_token['screen_name'].'&x_auth_expires=0'."\n";
            echo $str;
    }

    private function parse_variables($options){
        //parse options
        $this->parent_api = isset($options['parent_api']) ? $options['parent_api'] : self::PARENT_API;
        $this->parent_search_api = isset($options['parent_search_api']) ? $options['parent_search_api'] : self::PARENT_SEARCH_API;
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


        $this->request_uri = $this->parse_request_uri();
    }

    private function override_mode(){
        $access_token = @file_get_contents('oauth/'.$this->username.'.'.$this->password);
        if($access_token === FALSE){
            echo 'You are not allowed to use this API proxy';
            exit();
        }
        $access_token = unserialize($access_token);
        $this->access_token = $access_token;

        $this->url_fixer();
    }

    private function url_fixer(){
        if($_GET['source']=='Twitter for iPhone'){
            if(strpos($this->request_uri,'trends') === 0){
                $this->request_uri = '1/'.$this->request_uri;
            }
        }
        if((strpos($this->request_uri,'search.') === 0)){
            $this->request_uri = $this->parent_search_api.$this->request_url;
        }
        else{
            $this->request_uri = $this->parent_api.$this->request_url;
        }
    }


    private function parse_request_uri(){
        $full_request_uri = substr('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],strlen($this->base_url));
        list($this->mode,$this->username,$this->password,$ret) = explode('/',$full_request_uri,4);
        return $ret;
    }
    private function do_request(){
        if($this->request_uri == 'oauth/access_token'){
            $this->echo_token();
            exit();
        }
        if($this->request_uri == null){
            echo 'click <a href="oauth.php">HERE</a> to get your API url';
            exit();
        }
        $this->connection = new TwitterOAuth($this->oauth_key, $this->oauth_secret, $this->access_token['oauth_token'], $this->access_token['oauth_token_secret']);
        if($this->method=='POST'){
            echo $this->connection->post($this->request_uri,$_POST);
        }
        else{
            echo $this->connection->get($this->request_uri);
        }
    }

    private function headerfunction($ch,$str){
        //if(strpos($str,'Content-Length:')===NULL){
        //}
        //$newstr = str_replace('HTTP/1.1 401 Unauthorized','HTTP/1.1 200 OK',$str);
        header($newstr);
        $this->response_headers[] = $newstr;
        return strlen($str);
    }
}
?>
