<?php

$json_url = "http://www.novramedialabs.com/nathan/results.json";
$json = json_decode(file_get_contents($json_url), true);


$data_array = $json['data'];
//$name_array = array();
//
//
//
//foreach($data_array as $data){
//    $name_array[] = $data['name'];
//}     


$name_array = extract_field($data_array, 'name');

$fp = fopen('name.json', 'w');
fwrite($fp, json_encode($name_array));
fclose($fp);


?>


<?php
function extract_field($array, $field) {
    
    $result_array = array();

    foreach($array as $node){
    $result_array[] = $node[$field];
    }    
    
    return $result_array;
}
?>



<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>Display Json Data</title>
  </head>
  <body>
    <h1>Display Json Data</h1>



    <h3>PHP Session</h3>
    <pre>I don't really understand <?php print_r($_SESSION); ?></pre>

    <?php if ($json): ?>
      <h3>Data</h3>
      <pre><?php print_r($data_array); ?></pre> 
      
      <h3>Name</h3>
      <pre><?php print_r($name_array); ?></pre> 
      
      <h3>Data you got</h3>
      <pre><?php print_r($json); ?></pre>
    <?php else: ?>
      <strong><em>No data found.</em></strong>
    <?php endif ?>
</html>
