<?php
require_once 'connection.php';
require_once 'Log.php';


// This php deals with two distinct tasks: 
// 1. Receives mobile user location info (identified by beacon address)
// 2. communicate

$debug_mode = 1;


if($debug_mode == 1)    echo  'Package Received/n';
//if($debug_mode == 1)    echo  'access_token '.$_POST['access_token'];
//if($debug_mode == 1)    echo  'isset '.isset($_POST['access_token']);


if (isset($_POST['beacon_address'])) {
    
    if($debug_mode == 1)    echo  'access_token Received/n';
    
    // Customer is name of Database
    $con = new NMConnection('Customer');
    
    $user['uid']=$_POST['uid'];
    $user['nodeid']=$_POST['nodeid'];
    $user['beacon_address']=$_POST['beacon_address'];
        

    if($debug_mode == 1)    echo  '####$user = '.json_encode($user);
    
    $criteria['uid']=$user['uid'];
    $criteria['nodeid']=$user['nodeid'];

    
    $query = array($criteria);
    if($debug_mode == 1)    echo  '####$query = '.json_encode($query);
    

    $result = $con->getRows($query);
    
    if($debug_mode == 1)    echo  '####result = '.json_encode($result);
    if($debug_mode == 1)    echo  '####token = '.$result[0]['access_token'];
    
//    $new_token = 0;
    
    $mining_result = $result[0]['result'];
    
    if($mining_result != NULL)
    {        
        if($debug_mode == 1)    echo  '#### $mining_result = '.$mining_result;
    } 
   
}


?>