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
        if($this->request_uri == 'oauth/access_token'){
            $str = 'oauth_token='.$this->access_token['oauth_token']."&oauth_token_secret=".$this->access_token['oauth_token_secret']."&user_id=".$this->access_token['user_id']."&screen_name=".$this->access_token['screen_name'].'&x_auth_expires=0'."\n";
            echo $str;
            exit();
        }
        $this->do_request();
        $str = ob_get_contents();
        //file_put_contents('debug',$str);
        file_put_contents('log',$this->request_uri."\n",FILE_APPEND);
    }

    private function parse_variables($options){
        //parse options
        $this->parent_api = isset($options['parent_api']) ? $options['parent_api'] : self::PARENT_API;
        $this->parent_search_api = isset($options['parent_search_api']) ? $options['parent_search_api'] : self::PARENT_SEARCH_API;
        $this->base_url = isset($options['base_url']) ? $options['base_url'] : self::BASE_URL;
        $this->oauth_key = $options['oauth_key'];
        $this->oauth_secret = $options['oauth_secret'];

        //parse $_SERVER
        $this->method = $_SERVER['REQUEST_METHOD'];

        //something more
        $this->request_uri = $this->parse_request_uri();
        //$request_headers = getallheaders();
        //if(strpos($request_headers['Accept-Encoding'],'gzip')!==NULL){
        //    $this->gzip = true;
        //}
        //foreach($request_headers as $key=>$value){
        //    if($key!=='Accept-Encoding' && $key!=='Host'){
        //        $this->request_headers[] = $key.': '.$value;
        //    }
        //}
        $access_token = file_get_contents('oauth/'.$this->username.'.'.$this->password);
        $access_token = unserialize($access_token);
        $this->access_token = $access_token;
    }

    private function parse_request_uri(){
        $full_request_uri = substr('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],strlen($this->base_url));
        $first_slash_pos = strpos($full_request_uri,'/');
        $this->username = substr($full_request_uri,0,$first_slash_pos);
        $full_request_uri = substr($full_request_uri,$first_slash_pos+1);
        $second_slash_pos = strpos($full_request_uri,'/');
        $this->password = substr($full_request_uri,0,$second_slash_pos);
        return substr($full_request_uri,$second_slash_pos+1);
    }
    private function do_request(){
        $this->connection = new TwitterOAuth($this->oauth_key, $this->oauth_secret, $this->access_token['oauth_token'], $this->access_token['oauth_token_secret']);
        if((strpos($this->request_uri,'search.') === 0)){
            $this->connection->host = 'http://search.twitter.com/';
        }
        if(strpos($this->request_uri,'trends') === 0){
            $this->request_uri = '1/'.$this->request_uri;
        }
        if($this->method=='POST'){
            echo $this->connection->post($this->request_uri,$_POST);
        }
        else{
            echo $this->connection->get($this->request_uri);
        }
        //$this->url = $this->parent_api.$this->request_uri;
        //$ch = curl_init($this->url);
        //curl_setopt($ch,CURLOPT_HTTPHEADER,$this->request_headers);
        //curl_setopt($ch,CURLOPT_HEADERFUNCTION,array($this,'headerfunction'));
        //curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        //if($this->method=='POST'){
        //    curl_setopt($ch,CURLOPT_POST,TRUE);
        //    curl_setopt($ch,CURLOPT_POSTFIELDS,@file_get_contents('php://input'));
        //    $this->post_fileds = @file_get_contents('php://input');
        //}
        //$this->response_body = curl_exec($ch);
        ////$this->response_body . = 'oauth_token='.$this->access_token['oauth_token']."&oauth_token_secret=".$this->access_token['oauth_token_secret']."&user_id=".$this->access_token['user_id']."&screen_name=".$this->access_token['screen_name'].'&x_auth_expires=0';
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
