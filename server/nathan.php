<?php
require_once 'connection.php';
require_once 'Log.php';

// This php deals with two distinct tasks: 
// 1. Processes messages from devices
// 2. Lets manage.php use long polling to check for messages from devices



// TODO: For backwards compatibility with Version < 0.2.8, they use reg_id


echo 'Package Received/n';
//echo 'access_token '.$_POST['access_token'];
//echo 'isset '.isset($_POST['access_token']);


if (isset($_POST['access_token'])) {
    
    echo 'access_token Received/n';
    
    $con = new NMConnection('Customer');
    
    $user['uid']=$_POST['uid'];
    $user['nodeid']=$_POST['nodeid'];
    $user['access_token']=$_POST['access_token'];
        

//    echo 'somthing before';
   echo '####$user = '.json_encode($user);
    
    $criteria['uid']=$user['uid'];
    $criteria['nodeid']=$user['nodeid'];
//    $opt['request']=array('access_token');
    
//    $query = array($criteria,$opt);
    $query = array($criteria);
//    echo '####$query = '.json_encode($query);
    
//    $result = $con->getRows(array($query));    
    $result = $con->getRows($query);
    
    echo '####result = '.json_encode($result);
    echo '####token = '.$result[0]['access_token'];
    
    if($result[0]== NULL)
    {  
        
        echo '####1';
        $con->addRows(array($user));
        
        echo '####2';
    } 
    else if($result[0]['access_token'] != $user['access_token'])
    {        
        
        echo '####3';
        $orig['uid']=$_POST['uid'];
        $orig['nodeid']=$_POST['nodeid'];
        
        $con->updateRows(array($orig),array('access_token'=>$user['access_token']));
        
        echo '####4';
    }
 
    
    
    $con->addRows(array($user));
      
    echo 'somthing after';
    
    
    



// TODO: the 'success' field is kept only for backwards compatibility. Version >= 0.2.8.2 doesn't use it
//    if (empty($error)) end_message(json_encode(array( 'success' => empty($error), 'data' => $data )), $action);
//    else end_message(json_encode(array( 'success' => empty($error), 'data' => $data, 'error' => $error )), $action);
 
}
?>