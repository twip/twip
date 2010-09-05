<?php
session_start();
require('include/twitteroauth.php');
require('oauth_key.php');
if(!isset($_GET['oauth_verifier'])){
    $connection = new TwitterOAuth(OAUTH_KEY, OAUTH_SECRET);
    $request_token = $connection->getRequestToken($_SERVER['SCRIPT_URI']);

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
else{
    $connection = new TwitterOAuth(OAUTH_KEY, OAUTH_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
    $access_token = $connection->getAccessToken($_GET['oauth_verifier']);
    if($connection->http_code == 200){
        for ($i=0; $i<6; $i++) {
            $d=rand(1,30)%2;
            $secret_string .= $d ? chr(rand(65,90)) : chr(rand(48,57));
        } 
        foreach(glob('oauth/'.$access_token['screen_name'].'.*') as $file){
            unlink($file);
        }
        file_put_contents('oauth/'.$access_token['screen_name'].'.'.$secret_string,serialize($access_token));
        echo 'Your API URL is:'."\n";
        echo preg_replace('/(.*)\/oauth.php/','${1}',$_SERVER['SCRIPT_URI']).'/o/'.$access_token['screen_name'].'/'.$secret_string;
    }
    else {
        echo 'error';
    }


}
?>
