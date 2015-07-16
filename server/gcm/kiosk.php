<?php
require_once 'connection.php';
require_once 'Log.php';

// BROWSER API key for Google APIs
$apiKey = getenv("GCM_API_KEY");

// TODO: For backwards compatibility with Version < 0.2.8, they use reg_id
if (isset($_POST['reg_id'])) {
	$_POST['gcm_id'] = $_POST['reg_id'];
	unset($_POST['reg_id']);
}

// Cient registration IDs
$regIDs = array();
$regIDs = $_POST['devices'];
unset($_POST['devices']);

// Message to be sent
if (!empty($_POST['message'])) {
	$data['message'] = $_POST['message'];
}

// Set up action params
$action = $_POST['action'];
unset($_POST['action']);
$error = array();
if (!empty($action)) {
	$data['action'] = $action;
	if ($action == 'ping') {
	} else if ($action == 'opt') {
	} else if ($action == 'install') {
		$data['file_name'] = $_POST['file_name'];
		$data['web_directory_path'] = $_POST['web_directory_path'];
	} else if ($action == 'uninstall') {
		$data['package_name'] = $_POST['package_name'];
	} else if (strpos($action,'query_') === 0) {
		$data['action'] = 'query';
		$queryAction = substr($action,6);//query_ (6 letters before the queried expression)
		if (!empty($queryAction)) $data[$queryAction] = true;
		else $data['everything'] = true;
	} else if ($action == 'profile') {
		$data['write'] = true;
		$data['clear'] = false;
		if (!empty($_POST['profile'])) $data['profile'] = $_POST['profile'];
	} else if ($action == 'boot') {
		$data['show_ui'] = false;
	} else if ($action == 'stop') {
		$data['show_ui'] = false;
	}

	// Optional actions
	if ($action != 'ping' and is_array($_POST['options'])) {
		foreach ($_POST['options'] as $option) {
			$data[$option] = $_POST[$option];
		}
	}
}

// Check if there's anything to send
if (empty($data)) {
	die('No data to send');
} else {
	$data['time'] = time()*1000;  // Java uses ms, PHP uses seconds
}

// Set POST variables
$url = 'https://android.googleapis.com/gcm/send';
$fields = array(
	'registration_ids' => $regIDs,
	'data'             => $data
);
$headers = array(
	'Authorization: key='.$apiKey,
	'Content-Type: application/json'
);

// Open connection
$ch = curl_init();

// Set the url, number of POST vars, POST data
// POST data must not contain any null elements
curl_setopt( $ch, CURLOPT_URL, $url );
curl_setopt( $ch, CURLOPT_POST, true );
curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $fields ) );

// Execute post
$result = curl_exec($ch);

$response = json_decode($result, true);
//$info = curl_getinfo($ch);

// Interpret GCM's Success response
// http://developer.android.com/intl/ko/guide/google/gcm/gcm.html#success
if ($response['failure'] > 0 || $response['canonical_ids'] > 0) {
	$unsent = array();				// List of device IDs which didn't receive the message
	$obsolete_ids = array();		// List of old GCM IDs (in the same order as the canonical IDs)
	$replacement_ids = array();		// List of new canonical GCM IDs
	$delete_ids = array();			// List of device IDs that should be removed from the database

	// Parse each result
	for ($i = 0, $c = count($response['results']); $i < $c; $i++) {
		if (isset($response['results'][$i]['registration_id'])) {
			$obsolete_ids[] = array('gcm_id' => $regIDs[$i]);
			$replacement_ids[] = array('gcm_id' => $response['results'][$i]['registration_id']);
		}
		if (isset($response['results'][$i]['error'])) {
			$errormsg = $response['results'][$i]['error'];
			if ($errormsg == 'Unavailable') {
				// TODO: Could retry sending the request
				$unsent[] = $regIDs[$i];
			} else if ($errormsg == 'NotRegistered') {
				// Remove the registration ID from the server database since
				// the application was uninstalled from the device
				// or it does not have a broadcast receiver configured
				// to receive com.google.android.c2dm.intent.RECEIVE intents
				$delete_ids[] = array('gcm_id' => $regIDs[$i]);
			}
		}
	}

	// Delegate actions
	$db = new NMConnection('devices');
	if ($response['canonical_ids'] > 0) {
		if (!$db->updateRows($obsolete_ids, $replacement_ids)) {
			$error[] = array_pop($db->history);
		}
	}
	if (count($delete_ids) > 0) {
		if (!$db->updateRows($delete_ids, array( array( 'gcm_id' => '' ) ))) {
			$error[] = array_pop($db->history);
		}
	}
}

//echo "<p>httpcode: $info['http_code']</p>";
//echo var_dump($fields);
if ($response == 0)
	echo 'Result: '.$result;
else if (!empty($unsent))
	echo json_encode(array( 'data' => array( 'data' => $data, 'response' => $response ), 'unsent' => $unsent ));
else if (!empty($error))
	echo json_encode(array( 'data' => array( 'data' => $data, 'response' => $response ), 'error' => $error ));
else
	echo json_encode(array( 'data' => array( 'data' => $data, 'response' => $response ) ));

// Log activity
Log::w($result);

// Close connection
curl_close($ch);

?>
