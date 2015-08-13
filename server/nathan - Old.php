<?php
require_once 'connection.php';
require_once 'Log.php';

require 'fb/facebook.php';


// This php deals with two distinct tasks: 
// 1. Processes messages from devices
// 2. Lets manage.php use long polling to check for messages from devices


$debug_mode = 0;

// TODO: For backwards compatibility with Version < 0.2.8, they use reg_id


if($debug_mode == 1)    echo 'Package Received/n';
//if($debug_mode == 1)    echo 'access_token '.$_POST['access_token'];
//if($debug_mode == 1)    echo 'isset '.isset($_POST['access_token']);


if (isset($_POST['access_token'])) {
    
    if($debug_mode == 1)    echo 'access_token Received/n';
    
    $con = new NMConnection('Customer');
    
    $user['uid']=$_POST['uid'];
    $user['nodeid']=$_POST['nodeid'];
    $user['access_token']=$_POST['access_token'];
        

//    if($debug_mode == 1)    echo 'somthing before';
   if($debug_mode == 1)    echo '####$user = '.json_encode($user);
    
    $criteria['uid']=$user['uid'];
    $criteria['nodeid']=$user['nodeid'];

    
    $query = array($criteria);
//    if($debug_mode == 1)    echo '####$query = '.json_encode($query);
    

    $result = $con->getRows($query);
    
    if($debug_mode == 1)    echo '####result = '.json_encode($result);
    if($debug_mode == 1)    echo '####token = '.$result[0]['access_token'];
    
    $new_token = 0;
    
    if($result[0]== NULL)
    {  
        
        if($debug_mode == 1)    echo '####1';
        $con->addRows(array($user));
        $new_token = 1;
        
        if($debug_mode == 1)    echo '####2';
    } 
    else if($result[0]['access_token'] != $user['access_token'])
    {        
        
        if($debug_mode == 1)    echo '####3';
        $orig['uid']=$_POST['uid'];
        $orig['nodeid']=$_POST['nodeid'];
        
        $con->updateRows(array($orig),array('access_token'=>$user['access_token']));
        $new_token = 1;
        
        if($debug_mode == 1)    echo '####4';
    }
     
    
//    $con->addRows(array($user));
      
    if($debug_mode == 1)    echo 'somthing after';
    
    if($new_token == 1){
        // Create our Application instance (replace this with your appId and secret).
        $facebook = new Facebook(array(
            'appId'  => '1458231741142145',
            'secret' => '15062b8f08370f45ef068cc30633fd12',
        ));

        // This is access token can be obtained from android user
        $access_token_origin = $user['access_token'];
        // Set Access Token
        $facebook->setAccessToken($access_token_origin);
        //if($debug_mode == 1)    echo '####$access_token_origin '.$access_token_origin;
        
        // Get User ID    ---only valid after setAccessToken, and it return the node id that has been received from android device as well
        $user = $facebook->getUser();
        //if($debug_mode == 1)    echo $user.'++';
        
        if ($user) {
            try {
                // Proceed knowing you have a logged in user who's authenticated.
                $user_profile = $facebook->api('/me?fields=likes{id,name,description,category},groups{name,description}');
                //      if($debug_mode == 1)    echo '$user_profile'.$user_profile;
                                  
                $fp = fopen('raw_data.json', 'w');
                fwrite($fp, json_encode($user_profile));
                fclose($fp);       
                
                $des_list = description_n_label($user_profile, 0);
                
                $fp = fopen('data_mining_py/description_list.json', 'w');
                fwrite($fp, json_encode($des_list));
                fclose($fp);       
                
                
            } catch (FacebookApiException $e) {
                error_log($e);
                $user = null;
            }
            
        }
        
    }
 
}

// label different categories with its label, why label?
function description_n_label($data_array, $ori_label) {
    
    $result_array = array();

    foreach($data_array['likes']['data'] as $node){
        if (array_key_exists('description',$node)) {
            $new_node = array('ori_label'=>$ori_label,'name' => $node['name'], 'description' => $node['description']);
            $result_array[] = $new_node;
        }    
    }    
    
    foreach($data_array['groups']['data'] as $node){
        if (array_key_exists('description',$node)) {
            $new_node = array('ori_label'=>$ori_label,'name' => $node['name'], 'description' => $node['description']);
            $result_array[] = $new_node;
        }    
    }    
    
//    $elementCount  = count($result_array);
//    if($debug_mode == 1)    echo "###".$elementCount;
        
//    $file_name = $file.'.json';
//    $fp = fopen($file_name, 'w');
//    fwrite($fp, json_encode($result_array));
//    fclose($fp);   
        
    return $result_array; 
}


?>