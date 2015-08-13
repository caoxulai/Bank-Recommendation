// 01. fetch training set from Facebook API

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
        

        $search_result = $facebook->api('search?&q=banking&type=group&type=page&limit=5000&fields=name,description');
        $data_array = $search_result['data'];
        $fp = fopen('raw_banking.json', 'w');
        fwrite($fp, json_encode($data_array));
        fclose($fp);
        
        $search_result = $facebook->api('search?&q=finance&type=group&type=page&limit=5000&fields=name,description');      
        $data_array = $search_result['data'];        
        $fp = fopen('raw_finance.json', 'w');
        fwrite($fp, json_encode($data_array));
        fclose($fp);
        
        $search_result = $facebook->api('search?&q=travel&type=group&type=page&limit=5000&fields=name,description'); 
        $data_array = $search_result['data'];      
        $fp = fopen('raw_travel.json', 'w');
        fwrite($fp, json_encode($data_array));
        fclose($fp);
        
        $search_result = $facebook->api('search?&q=credit&type=group&type=page&limit=5000&fields=name,description');      
        $data_array = $search_result['data'];      
        $fp = fopen('raw_credit.json', 'w');
        fwrite($fp, json_encode($data_array));
        fclose($fp);
        
        $search_result = $facebook->api('search?&q=credit%20card&type=group&type=page&limit=5000&fields=name,description');      
        $data_array = $search_result['data'];      
        $fp = fopen('raw_credit_card.json', 'w');
        fwrite($fp, json_encode($data_array));
        fclose($fp);
        

        $search_result = $facebook->api('search?&q=invest&type=group&type=page&limit=5000&fields=name,description');      
        $data_array = $search_result['data'];      
        $fp = fopen('raw_invest.json', 'w');
        fwrite($fp, json_encode($data_array));
        fclose($fp);
        
        $search_result = $facebook->api('search?&q=wealth&type=group&type=page&limit=5000&fields=name,description');
        $data_array = $search_result['data'];      
        $fp = fopen('raw_wealth.json', 'w');
        fwrite($fp, json_encode($data_array));
        fclose($fp);
        
        $search_result = $facebook->api('search?&q=mortgage&type=group&type=page&limit=5000&fields=name,description'); 
        $data_array = $search_result['data'];      
        $fp = fopen('raw_mortgage.json', 'w');
        fwrite($fp, json_encode($data_array));
        fclose($fp);
        
        $search_result = $facebook->api('search?&q=house%20sale&type=group&type=page&limit=5000&fields=name,description');      
        $data_array = $search_result['data'];      
        $fp = fopen('raw_house_sale.json', 'w');
        fwrite($fp, json_encode($data_array));
        fclose($fp);
        
        $search_result = $facebook->api('search?&q=apartment%20sale&type=group&type=page&limit=5000&fields=name,description');
        $data_array = $search_result['data'];        
        $fp = fopen('raw_apartment_sale.json', 'w');
        fwrite($fp, json_encode($data_array));
        fclose($fp);
        
        $search_result = $facebook->api('search?&q=saving&type=group&type=page&limit=5000&fields=name,description');
        $data_array = $search_result['data'];      
        $fp = fopen('raw_saving.json', 'w');
        fwrite($fp, json_encode($data_array));
        fclose($fp);
        

        
        $search_result = $facebook->api('search?&q=travel%20insurance&type=group&type=page&limit=5000&fields=name,description');     
        $data_array = $search_result['data'];      
        $fp = fopen('raw_travel_insurance.json', 'w');
        fwrite($fp, json_encode($data_array));
        fclose($fp);
        
        $search_result = $facebook->api('search?&q=vacation&type=group&type=page&limit=5000&fields=name,description'); 
        $data_array = $search_result['data'];        
        $fp = fopen('raw_vacation.json', 'w');
        fwrite($fp, json_encode($data_array));
        fclose($fp);
        
        
        
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
