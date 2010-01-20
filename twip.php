<?
class twip{
    const DEBUG = false;
    const DOLOG = true;
    const WEBROOT = 'twip';
    const PARENT_API = 'http://twitter.com';
    const PARENT_SEARCH_API = 'http://search.twitter.com';
    const ERR_LOGFILE = 'err.txt';
    const LOGFILE = 'log.txt';
    const LOGTIMEZONE = 'Etc/GMT-8';
    const CGI_WORKAROUND = false;


    public function twip ( $options = null ){
        $this->check_server();
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->debug = !!$options['debug'] || self::DEBUG;
        $this->dolog = !!$options['dolog'] & self::DOLOG;
        $this->webroot = !empty($options['webroot']) ? $this->mytrim($options['webroot']) : self::WEBROOT;
        $this->parent_api = !empty($options['parent_api']) ? $this->mytrim($options['parent_api']) : self::PARENT_API;
        $this->parent_search_api = !empty($options['parent_search_api']) ? $this->mytrim($options['parent_search_api']) : self::PARENT_SEARCH_API;
        $this->err_logfile = !empty($options['err_logfile']) ? $options['err_logfile'] : self::ERR_LOGFILE;
        $this->logfile = !empty($options['logfile']) ? $options['logfile'] : self::LOGFILE;
        $this->log_timezone = !empty($options['log_timezone']) ? $options['log_timezone'] : self::LOGTIMEZONE;
        $this->replace_shorturl = !!$options['replace_shorturl'];
        $this->docompress = !!$options['docompress'];
        $this->cgi_workaround = ($options['cgi_workaround']==="YES I DO NEED THE WORKAROUND!") ? true : self::CGI_WORKAROUND;



        $this->pre_request();
        $this->dorequest();
        $this->post_request();
    }


    private function pre_request(){
        if(strlen($this->webroot) == 0){//use "/" as webroot
            $this->request_api = strval(substr($_SERVER['REQUEST_URI'],1));
        }else{
            if(stripos($_SERVER['REQUEST_URI'],$this->webroot) !== false){
                $this->request_api =strval(substr($_SERVER['REQUEST_URI'],strlen($this->webroot) + 2));
            }else{
                $this->err();
            }
        }

        if($this->request_api =='' || strpos($this->request_api,'index.php')!==false){
            $this->err();
        }

        $this->request_api = $this->mytrim($this->request_api);
        if($this->method == 'POST'){
            $this->post_data = @file_get_contents('php://input');
        }
    }


    private function dorequest(){
        $this->pwd = $this->user_pw();
        if( strpos($this->request_api,'api/') === 0 ){//workaround for twhirl
            $this->request_api = substr($this->request_api,4);
        }
        if( strpos($this->request_api,'search')===FALSE && strpos($this->request_api,'trends')===FALSE){
            $url = $this->parent_api.'/'.$this->request_api;
        }
        else{
            $url = $this->parent_search_api.'/'.$this->request_api;
        }
        $ch = curl_init($url);
        $curl_opt = array();
        if($this->method == 'POST'){
            $curl_opt[CURLOPT_POST] = true;
            $curl_opt[CURLOPT_POSTFIELDS] = $this->post_data;
        }
        $curl_opt[CURLOPT_USERAGENT] = $_SERVER['HTTP_USER_AGENT'];
        $curl_opt[CURLOPT_RETURNTRANSFER] = true;
        $curl_opt[CURLOPT_USERPWD] = $this->pwd;
        $curl_opt[CURLOPT_HEADERFUNCTION] = create_function('$ch,$str','if(strpos($str,\'Content-Length:\') === false ) { header($str); } return strlen($str);');
        $curl_opt[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1 ;//avoid the "Expect: 100-continue" error
        curl_setopt_array($ch,$curl_opt);
        $this->ret = curl_exec($ch);
        curl_close($ch);
    }
    private function post_request(){
        if($this->replace_shorturl){
            $this->replace_shorturl();
        }
        header('Content-Length: '.strlen($this->ret));


        if($this->docompress && Extension_Loaded('zlib')) {
            if(!Ob_Start('ob_gzhandler')){
                Ob_Start();
            }
        }

        echo $this->ret;

        if($this->docompress && Extension_Loaded('zlib')) {
            Ob_End_Flush();
        }
        if($this->dolog){
            $this->dolog();
        }
    }
    private function user_pw(){
        if(!empty($_SERVER['PHP_AUTH_USER'])){
            $this->username = $_SERVER['PHP_AUTH_USER'];
            return $_SERVER['PHP_AUTH_USER'].':'.$_SERVER['PHP_AUTH_PW'];
        }
        else if(!empty($_SERVER['HTTP_AUTHORIZATION'])||!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])){
            $auth = empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION']:$_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            $a = base64_decode( substr($auth,6)) ;
            list($name, $password) = explode(':', $a);
            $this->username = $name;
            return $name.':'.$password;
        }
        else if($this->cgi_workaround){
            $pattern = '/^([^:]*):([^\/]*)[\/]+(.*)$/';
            if(preg_match($pattern,$this->request_api,$matches)){
                $this->request_api = $matches[3];
                $this->username = $matches[1];
                return $matches[1].':'.$matches[2];
            }
            else{
                $this->username = 'nobody';
                return '';
            }
        }
    }
    private function mytrim($str){
        return trim($str,'/');
    }
    private function check_server(){
        if(!function_exists('curl_init') &&
        !function_exists('curl_setopt_array') &&
        !function_exists('curl_exec') &&
        !function_exists('curl_close')){
            $this->err("curl functions doesn't exists!");
        }
        else if(!function_exists('file_get_contents') && !function_exists('file_put_contents')){
            $this->err("PHP 5 is needed!");
        }
    }
    private function err($str=null){
        if(empty($str)){
            $str = 'Seems every thing is fine.';
        }
        else{
            errlog($str);
        }
        $msg ="
                <html>
                <head>
                <title>Twip Message Page</title>
                </head>
                <body>
                <h1>Twip Message Page</h1>
                <div>
                This is a Twitter API proxy,and is not intend to be viewed in a browser.<br />
                Visit Twip for more details. View test page <a herf=\"test.html\">HERE</a>.View oauth page HERE.<br />
                </div>
                <div>
                ".nl2br($str)."
                </div>
                </body>
                </html>
                ";
        echo $msg;
        exit();
    }
    private function errlog($str){
        date_default_timezone_set($this->log_timezone);		//set timezone
        $msg = date('Y-m-d H:i:s').' '.$this->request_api.' '.$this->post_data.' '.$this->username.' '.$str."\n";
        file_put_contents($this->err_logfile,$msg,FILE_APPEND);
    }
    private function replace_shorturl(){
        $url_pattern = "/http:\/\/(?:j\.mp|bit\.ly|ff\.im)\/[\w|\-]+/";

        if(preg_match_all($url_pattern,$this->ret,$matches)){
            $query_arr = array();
            foreach($matches[0] as $shorturl){
                $query_arr[] = "q=".$shorturl;
            }
            $offset = 0;
            $query_count = 5;
            $replace_arr = array();
            do{
                $tmp_arr = array_slice($query_arr,$offset,$query_count);
                $query_str = implode("&",$tmp_arr);
                $json_str = @file_get_contents("http://www.longurlplease.com/api/v1.1?".$query_str);
                if( $json_str !==FALSE ){
                    $json_arr = json_decode($json_str,true);
                    $replace_arr = array_merge($json_arr,$replace_arr);
                }
                $offset+=$query_count;
            }while( count($tmp_arr)===$query_count );//split the queries to avoid a too long query string.:
           	$this->ret = str_replace(array_keys($replace_arr),array_values($replace_arr),$this->ret);
        }

    }
    private function dolog(){
        date_default_timezone_set($this->log_timezone);		//set timezone
        $msg = date('Y-m-d H:i:s').' '.$this->request_api.' '.$this->username.' compress: '.(!!$this->docompress?'yes':'no')."\n";
        file_put_contents($this->logfile,$msg,FILE_APPEND);
    }
}
?>
