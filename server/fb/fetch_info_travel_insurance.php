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

if ($user) {
    try {
        // Proceed knowing you have a logged in user who's authenticated.
        $search_result = $facebook->api('search?&q=travel%20insurance&type=group&type=page&limit=5000&fields=name,description');
        //      echo '$user_profile'.$user_profile;
      
        $data_array = $search_result['data'];
      
        $fp = fopen('raw_travel_insurance.json', 'w');
        fwrite($fp, json_encode($data_array));
        fclose($fp);
        // fetch data as you described
             
    } catch (FacebookApiException $e) {
        error_log($e);
        $user = null;
    }
}
?>





<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>Display Json Data</title>
  </head>
  <body>
    <h1>Display Json Data</h1>

    <?php if ($search_result): ?>
      <h3>Data</h3>
      <pre><?php print_r($data_array); ?></pre> 

      <h3>Data you got</h3>
      <pre><?php print_r($search_result); ?></pre>
    <?php else: ?>
      <strong><em>No data found.</em></strong>
    <?php endif ?>
</html>
