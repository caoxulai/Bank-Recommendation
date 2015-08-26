<?php
require_once 'connection.php';
require_once 'util.php';
require_once 'Log.php';

// This php deals with two distinct tasks: 
// 1. Processes messages from devices
// 2. Lets manage.php use long polling to check for messages from devices
// TODO: Make this object oriented

/**
 * Logs the transaction then outputs the result
 */
function end_message($result, $tag) {
	// Log message
	if ($tag != 'check' and $tag != 'ping') {
		if (isset($tag)) {
			Log::w($result, $tag);
		} else {
			Log::w($result);
		}
	}
	echo $result;
}



$db = new NMConnection('Customer');
$tag = $_POST['action'];

if($tag == 'check'){
    // TODO: figure out how much of the messages should be retrived, and any filters
    $later_than = ($_POST['later_than']) ? $_POST['later_than'] : time();// in seconds

    $data = $db->getRows(array(array( "last_updated > '".date('Y-m-d H:i:s', $later_than)."'" )));    
//    $data = $db->getRows(array(array( "last_updated > '".date('Y-m-d H:i:s', $later_than)."'"  , "beacon_address != NULL" )));
//    echo json_encode($data);

    if(!empty($data)){
        // Unserialize the data columns
        foreach ($data as &$row) {
            $mining_result = $row['result'];
            $beacon_address = $row['beacon_address'];
            
            if(!is_null($mining_result) && !is_null($beacon_address)){                
                switch ($mining_result) {
                    case 1:
                        $clip_name = "BMO_STD_ENG_07_TravelInsurance_071415";
                        break;
                    case 2:
                        $clip_name = "BMO_STD_ENG_03A_Mortgage_051215";
                        break;
                    case 3:
                        $clip_name = "BMO_STD_ENG_09_WEMC_060115_v2";
                        break;
                    case 4:
                        $clip_name = "BMO_STD_ENG_11_aDQuantitiveAnalysis_121014";
                        break;
                }
                $response = array('clip_name'=>$clip_name,'beacon_address'=>$beacon_address);
                $response = json_encode($response);
                echo $response;            
            }
        }    
    }
}

else if($tag == 'success'){
    $beacon_address = $_POST['beacon_address'];    
    
    $criteria['beacon_address'] = $beacon_address;
    $query = array($criteria);
    echo json_encode($query);
    $db->updateRows($query,array('beacon_address'=>null,'last_updated'=>null));
}

?>