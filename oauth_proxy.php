<?php
/*
 * @author tifan
 */
include('include/simple_html_dom.php'); 
/* Credit: */

function oauth_proxy($oAuthEntryPage, $twitterAccount, $twitterPassword) {
    /* form: https://twitter.com/oauth/authenticate
    authenticity_token -> 抓
            oauth_token -> 抓
            session[username_or_email] -> twitterAccount
            session[password] -> twitterPassword
    */
    /* After this page, we should validate the returning page.
            Statud 403 -> No longer valid / wrong password.
            Status 200 ->
                    if (contain_Allow) {
                            post_allow;
                            get_oauth_strings;
                            post_oauth_strings_to_oauth.php;
                    } else {
                            get_oauth_strings;
                            ...
                    }
    */
    $page_auth = file_get_html($oAuthEntryPage);
    if($page_auth === FALSE){
        echo "Cannot load http resource using file_get_contents";
        exit();
    }
    $oauth_token = $page_auth->find('input[name=oauth_token]', 0)->attr['value'];
    $authenticity_token = $page_auth->find('input[name=authenticity_token]', 0)->attr['value'];
    $login_fields = Array(
        'oauth_token' => urlencode($oauth_token),
        'authenticity_token' => urlencode($authenticity_token),
        'session[username_or_email]' => urlencode($twitterAccount),
        'session[password]' => urlencode($twitterPassword)
    );
    foreach($login_fields as $key=>$value) {
        $login_string .= $key.'='.$value.'&';
    }
    $ckfile = tempnam ("/tmp", "CURLCOOKIE");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.twitter.com/oauth/authorize');
    curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, count($login_fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $login_string);
    $login_result = curl_exec($ch);
    curl_close($ch);
    $login_obj = str_get_html($login_result);
    $login_error = $login_obj->find('div[class=error notice] p', 0)->innertext;
    if(strlen($login_error) > 8) {
        /* This is a workaround coz oauth_errors can be "&nbsp;" */
        echo "There must be something wrong with your user account and password combination.<br/>";
        echo "Twitter said: <b>$login_error</b>\n";
        die(-1);
    }
    $code = $login_obj->find('code', 0)->innertext;
    return $code;
}
