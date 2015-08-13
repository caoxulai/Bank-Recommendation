<?php



//$file = "raw_data";
//$file = "raw_travel";
$file = "description_list";

$json_url = "http://www.novramedialabs.com/nathan/".$file.".json";
$json = json_decode(file_get_contents($json_url), true);



?>





<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>Display Json Data</title>
  </head>
  <body>
    <h1>Display Json Data</h1>



    <h3>Data Display</h3>

    <?php if ($json): ?>
      <h3>$json</h3>
      <pre><?php print_r($json); ?></pre> 
      
    
    <?php else: ?>
      <strong><em>No data found.</em></strong>
    <?php endif ?>
</html>
