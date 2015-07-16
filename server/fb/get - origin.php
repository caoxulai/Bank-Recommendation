<?php

require 'fb/facebook.php';

echo 'KK';

// Create our Application instance (replace this with your appId and secret).
$facebook = new Facebook(array(
  'appId'  => '1458231741142145',
  'secret' => '15062b8f08370f45ef068cc30633fd12',
));

// Get User ID
$user = $facebook->getUser();

echo $user.'++';

if ($user) {
  try {
    // Proceed knowing you have a logged in user who's authenticated.
    $user_profile = $facebook->api('/me');
      echo '$user_profile'.$user_profile;
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
    <pre><?php print_r($_SESSION); ?></pre>

    <?php if ($user): ?>
      <h3>You</h3>
      <img src="https://graph.facebook.com/<?php echo $user; ?>/picture">

      <h3>Your User Object (/me)</h3>
      <pre><?php print_r($user_profile); ?></pre>
    <?php else: ?>
      <strong><em>You are not Connected.</em></strong>
    <?php endif ?>
</html>
