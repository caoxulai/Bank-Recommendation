<?php
require_once 'connection.php';
require_once 'Log.php';

// This php deals with two distinct tasks: 
// 1. Processes messages from devices
// 2. Lets manage.php use long polling to check for messages from devices
// TODO: Make this object oriented

/**
 * Logs the transaction then outputs the result
// */
//function end_message($result, $tag) {
//	// Log message
//	if ($tag != 'check' and $tag != 'ping') {
//		if (isset($tag)) {
//			Log::w($result, $tag);
//		} else {
//			Log::w($result);
//		}
//	}
//	echo $result;
//}



// TODO: For backwards compatibility with Version < 0.2.8, they use reg_id


echo 'Package Received/n';
//echo 'access_token '.$_POST['access_token'];

//echo 'isset '.isset($_POST['access_token']);


    
    echo 'access_token Received/n';
    
    echo 'somthing 1';

    $con = new NMConnection('Customer');
    echo 'somthing 2';
    
    $user['uid']=$_POST['uid'];
    $user['nodeid']=$_POST['nodeid'];
    $user['access_token']=$_POST['access_token'];
    
    echo 'somthing before';
    echo $user;
    
    $con->addRows(array($user));
      
    echo 'somthing after';

//
//    $action = $_POST['action'];
//    $data = array();
//    $error = array();


// TODO: the 'success' field is kept only for backwards compatibility. Version >= 0.2.8.2 doesn't use it
//    if (empty($error)) end_message(json_encode(array( 'success' => empty($error), 'data' => $data )), $action);
//    else end_message(json_encode(array( 'success' => empty($error), 'data' => $data, 'error' => $error )), $action);
 
?>