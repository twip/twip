<?
class twip{

	
		const DEBUG = false;
		const WEBROOT = 'twip';
		const PARENT_API = 'https://twitter.com';
		const ERR_LOGFILE = 'err.log';


		public function twip ( $options = null ){
				$this->check_server();
				$this->method = $_SERVER['REQUEST_METHOD'];
				$this->debug = !!$options['debug'] || self::DEBUG;
				$this->webroot = !empty($options['webroot']) ? $this->mytrim($options['webroot']) : self::WEBROOT;
				$this->parent_api = !empty($options['parent_api']) ? $this->mytrim($options['parent_api']) : self::PARENT_API;
				$this->err_logfile = !empty($options['err_logfile']) ? $options['err_logfile'] : self::ERR_LOGFILE;
				$this->replace_shorturl = !!$options['replace_shorturl'] && !empty($options['bitly_login']) && !empty($options['bitly_api']) && !empty($options['ff_login']) && !empty($options['ff_remotekey']);
				if($this->replace_shorturl){
						$this->bitly_login = $options['bitly_login'];
						$this->bitly_api = $options['bitly_api'];
						$this->ff_login = $options['ff_login'];
						$this->ff_apikey = $options['ff_remotekey'];
				}
				$this->pre_request();
				$this->dorequest();
				$this->post_request();
		}


		private function pre_request(){
				$this->request_api = strval(substr($_SERVER['REQUEST_URI'],strlen($this->webroot)+2));
				if($this->request_api =='' || strpos($this->request_api,'index.php')!==false){
						$this->err();
				}
				$arr = array();
				if($this->method == 'POST'){
						foreach($_POST as $key => $value){
								$arr[] = $key.'='.$value;
						}
						$this->post_data = implode('&',$arr);
				}
		}


		private function dorequest(){
				$url = $this->parent_api.'/'.$this->request_api;
				$ch = curl_init($url);
				$option = array();
				if($this->method == 'POST'){
					$option[CURLOPT_POST] = true;
					$option[CURLOPT_POSTFIELDS] = $this->post_data;
				}
				$option[CURLOPT_USERAGENT] = $_SERVER['HTTP_USER_AGENT'];
				$option[CURLOPT_RETURNTRANSFER] = true;
				$option[CURLOPT_USERPWD] = $this->user_pw();
				$option[CURLOPT_HEADERFUNCTION] = array($this,'echoheader');
				curl_setopt_array($ch,$option);
				$this->ret = curl_exec($ch);
				curl_close($ch);
		}
		private function post_request(){
				$this->replace_shorturl();
				header('Content-Length: '.strlen($this->ret));
				echo $this->ret;
				$this->dolog();
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
				else{
						$this->username = 'nobody';
						return '';
				}
		}
		private function mytrim($str){
				return trim($str,'/');
		}
		private function check_server(){
				if(		!function_exists('curl_init') &&
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
							Visit Twip for more details. View test page HERE.View oauth page HERE.<br />
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
		private function echoheader($ch,$str){
				if(strpos($str,'Content-Length:') === false ){
						header($str);
				}
				return strlen($str);
		}

		private function errlog($str){
				$msg = date('Y-m-d H:i:s').' '.$this->request_api.' '.$this->post_data.' '.$str;
				file_put_contents($this->err_logfile,$msg,FILE_APPEND);
		}
		private function replace_shorturl(){
				$short2long = array();
				//bit.ly and j.mp
				if(preg_match_all('/(http:\/\/(bit\.ly|j\.mp)\/([a-z0-9]+))/i',$this->ret,$match)){
						foreach($match[0] as $key => $short_url){
								$url_id = $match[3][$key];
								$url = 'http://api.bit.ly/expand?version=2.0.1&shortUrl='.$short_url.'&login='.$this->bitly_login.'&apiKey='.$this->bitly_api;
								$arr = json_decode(file_get_contents($url),TRUE);
								if($arr['statusCode']=='OK'){
										$short2long[$short_url] = $arr['results'][$url_id]['longUrl'];
								}
						}
				}
				//ff.im,I hate ff.im!It sucks!
				//NOTE:not all friendfeed links can be expanded, since some of the links are private.
				if(preg_match_all('/http:\/\/ff\.im\/([-a-z0-9]+)/i',$this->ret,$match)){
						foreach($match[0] as $key => $short_url){
								$url_id = $match[1][$key];
								$url = 'http://'.$this->ff_login.':'.$this->ff_remotekey.'@friendfeed-api.com/v2/short/'.$url_id;
								$arr = json_decode(file_get_contents($url),TRUE);
								if(strval($arr->url)!=''){
										$short2long[$short_url] = strval($arr->url);
								}
						}
				}
				//the actual replace
				foreach($short2long as $short_url => $long_url){
						$new_short_url = file_get_contents('http://tinyurl.com/api-create.php?url='.urlencode($long_url));
						if($new_short_url!=''){
								$this->ret = str_replace($short_url,$new_short_url,$this->ret);
						}
				}
		}
		private function dolog(){
				return;
		}
}
?>
