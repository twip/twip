<?php
session_start();
require('include/twitteroauth.php');
require('config.php');
if(isset($_POST['my_suffix'])){
    $_SESSION['my_suffix'] = preg_replace('/[^a-zA-Z0-9]/','',$_POST['my_suffix']);
}
if(!empty($_POST)){
    if(!isset($_GET['type']) || $_GET['type']==1){
        $connection = new TwitterOAuth(OAUTH_KEY, OAUTH_SECRET);
        $request_token = $connection->getRequestToken(BASE_URL.'oauth.php');

        /* Save request token to session */
        $_SESSION['oauth_token'] = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

        switch ($connection->http_code) {
          case 200:
            /* Build authorize URL */
            $url = $connection->getAuthorizeURL($_SESSION['oauth_token']);
            header('Location: ' . $url); 
            break;
          default:
            echo 'Could not connect to Twitter. Refresh the page or try again later.';
            echo "\n Error code:".$connection->http_code;
            break;
        }
    }
    exit();
}
if(isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])){
    $connection = new TwitterOAuth(OAUTH_KEY, OAUTH_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
    $access_token = $connection->getAccessToken($_GET['oauth_verifier']);
    if($connection->http_code == 200){
        $old_tokens = glob('oauth/'.$access_token['screen_name'].'.*');
        if(!empty($old_tokens)){
            foreach($old_tokens as $file){
                unlink($file);
            }
        }
        if($_SESSION['my_suffix']==''){
            for ($i=0; $i<6; $i++) {
                $d=rand(1,30)%2;
                $suffix_string .= $d ? chr(rand(65,90)) : chr(rand(48,57));
            } 
        }
        else{
            $suffix_string = $_SESSION['my_suffix'];
        }
        file_put_contents('oauth/'.$access_token['screen_name'].'.'.$suffix_string,serialize($access_token));
        $url = BASE_URL.'o/'.$access_token['screen_name'].'/'.$suffix_string;
        header('Location: getapi.php?api='.$url);
    }
    else {
        echo 'error '.$connection->http_code."\n";
        print_r($connection);
    }
    exit();
}
?>
<!DOCTYPE HTML>
<html lang="en-US">
<head>
<meta charset="UTF-8">
<title>Twip 4 - Configuration</title>
<link rel="stylesheet" type="text/css" href="style.css" media="all" />
<meta name="robots" content="noindex,nofollow">
</head>
<body>
	<h1><a href="index.html">Twip<sup title="Version 4">4</sup></a></h1>
	<h2>Twitter API Proxy, redefined.</h2>
<?
if(!isset($_GET['type']) || $_GET['type']==1){
?>
	<div>
	
		<h3>Twip 配置</h3>
		
		<form action="" method="post">
		
			<ul class="clearfix">
				<li><a class="active" href="oauth.php?type=1">OAuth 验证</a></li>
				<li><a href="oauth.php?type=2">模拟 OAuth 验证</a></li>
			</ul>
			
			<hr class="clear" />
			
			<p>
				<label for="url_suffix">自定义 URL 地址</label>
                <input class="half" type="text" value="<?php echo BASE_URL.'o/';?>" id="base_url" disabled autocomplete="off" />
				<input class="half" type="text" value="" id="url_suffix" autocomplete="off" name="my_suffix" />
			</p>
			
			<input type="submit" value="提交认证" class="button">
		
		</form>
	</div>		
	<div id="footer">
		2010 &copy; <a href="http://code.google.com/p/twip/">Twip Project</a>
	</div>
</body>
</html>
<?
    exit();
}
else{
?>
	<div>
	
		<h3>Twip 配置</h3>
		
		<form action="" method="post">
		
			<ul class="clearfix">
				<li><a href="oauth.php?type=1">OAuth 验证</a></li>
				<li><a class="active" href="oauth.php?type=2">模拟 OAuth 验证</a></li>
			</ul>
			
			<hr class="clear" />
			
			<p>
				<label for="url_suffix">1. 自定义 URL 地址</label>
                <input class="half" type="text" value="<?php echo BASE_URL.'o/';?>" id="base_url" disabled autocomplete="off" />
				<input class="half" type="text" value="" id="url_suffix" autocomplete="off" />
			</p>
			
			<p>
				<label for="username">2. 你的 Twitter 用户名</label>
				<input type="text" value="" id="username" autocomplete="off" />
			</p>
			
			<p>
				<label for="password">3. 你的 Twitter 密码</label>
				<input type="password" value="" id="password" autocomplete="off" />
			</p>
			
			<input type="submit" value="该功能尚未完成" class="button" disabled>
		
		</form>
		
	</div>		
	<div id="footer">
		2010 &copy; <a href="http://code.google.com/p/twip/">Twip Project</a>
	</div>
</body>
</html>
<?
    exit();
}
?>
