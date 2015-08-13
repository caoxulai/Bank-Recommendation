// 02. Give each category a label

<?php

$file = "travel_insurance";
$new_array = filter_n_label($file,11);

$file = "travel";
filter_n_label($file,12);

$file = "vacation";
filter_n_label($file,13);

$file = "mortgage";
filter_n_label($file,21);

$file = "house_sale";
filter_n_label($file,22);

$file = "apartment_sale";
filter_n_label($file,23);

$file = "credit_card";
filter_n_label($file,31);

$file = "saving";
filter_n_label($file,32);

$file = "invest";
filter_n_label($file,41);

$file = "wealth";
filter_n_label($file,42);

$file = "banking";
filter_n_label($file,51);

$file = "credit";
filter_n_label($file,52);

$file = "finance";
filter_n_label($file,53);

?>


<?php
// label different categories with its label, why label?
function filter_n_label($file, $ori_label) {
    
    $json_url = "http://www.novramedialabs.com/nathan/raw_".$file.".json";
    $data_array = json_decode(file_get_contents($json_url), true);
    
    $result_array = array();

    foreach($data_array as $node){
        if (array_key_exists('description',$node)) {
            $new_node = array('ori_label'=>$ori_label,'name' => $node['name'], 'description' => $node['description']);
            $result_array[] = $new_node;
        }    
    }    
    
    $elementCount  = count($result_array);
    echo "###".$elementCount;
        
    $file_name = $file.'.json';
    $fp = fopen($file_name, 'w');
    fwrite($fp, json_encode($result_array));
    fclose($fp);   
        
    return $result_array; 
}
?>



<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>Display Json Data</title>
  </head>
  <body>
    <h1>Display Json Data</h1>

    <?php if ($new_array): ?>
            
      <h3>$new_array</h3>
      <pre><?php print_r($new_array); ?></pre> 
      
    <?php else: ?>
      <strong><em>No data found.</em></strong>
    <?php endif ?>
</html>
