<?php

require 'fb/facebook.php';

//echo 'KK';

// Create our Application instance (replace this with your appId and secret).
$facebook = new Facebook(array(
    'appId'  => '1458231741142145',
    'secret' => '15062b8f08370f45ef068cc30633fd12',
));

// This is access token can be obtained from android user
$access_token_origin = 'CAAUuQQjuVIEBAN5rWfJ23tJBNe1Tl8W6PMmqHdx1KSpINLbU11jLlDzMcBD6W72FVBuKKyLWzdksvSBSZCFdM38F8uFrgC7WwXbDoy7ayZAfTyrcQcXnU47um5ZCj5D4EbXRdwRIZCoYCMB1klddUatTgZANSjgOVMDUHAfIepeeUyxW4r3HuZBzTFaZAIxE1Qvk2EV8iAehDNZA6blpTiC4';
// Set Access Token
$facebook->setAccessToken($access_token_origin);
//echo '####$access_token_origin '.$access_token_origin;

// Get User ID    ---only valid after setAccessToken, and it return the node id that has been received from android device as well
$user = $facebook->getUser();
//echo $user.'++';

if ($user) {
  try {
      // Proceed knowing you have a logged in user who's authenticated.
      $user_profile = $facebook->api('search?q=finance&type=group&limit=5&fields=name');
//      echo '$user_profile'.$user_profile;
      
      $fp = fopen('results.json', 'w');
      fwrite($fp, json_encode($user_profile));
      fclose($fp);
      // fetch data as you described
                  


  } catch (FacebookApiException $e) {
      error_log($e);
      $user = null;
  }
}

// Login or logout url will be needed depending on current user state.
if ($user) {
    $logoutUrl = $facebook->getLogoutUrl();
} else {
    $loginUrl = $facebook->getLoginUrl();
}

?>



<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>Facebook PHP SDK</title>
  </head>
  <body>
    <h1>Facebook PHP SDK</h1>

    <?php if ($user): ?>
      <a href="<?php echo $logoutUrl; ?>">Logout</a>
    <?php else: ?>
      <div>
        Login using OAuth 2.0 handled by the PHP SDK:
        <a href="<?php echo $loginUrl; ?>">Login with Facebook</a>
      </div>
    <?php endif ?>

    <h3>PHP Session</h3>
    <pre>I don't really understand <?php print_r($_SESSION); ?></pre>

    <?php if ($user): ?>
      <h3>You</h3>
      <img src="https://graph.facebook.com/<?php echo $user; ?>/picture">

      <h3>Data you got</h3>
      <pre><?php print_r($user_profile); ?></pre>
    <?php else: ?>
      <strong><em>You are not Connected.</em></strong>
    <?php endif ?>
</html>
