<?php
require_once 'connection.php';
require_once 'Log.php';

// TODO: For backwards compatibility with Version < 0.2.8, they use reg_id
if (isset($_POST['reg_id'])) {
	$_POST['gcm_id'] = $_POST['reg_id'];
	unset($_POST['reg_id']);
}

$action = $_POST['action'];
// response variables
$success = false;
$error = array();

if (empty($action)) {
	// Get the post data from the header
	$_POST[] = file_get_contents('php://input');
	$action = $_POST['action'];
}

if (!isset($_POST['uid'])
	&& !(isset($_POST['mac']) && isset($_POST['package_name']))
	&& !isset($_POST['gcm_id'])
	&& !isset($_POST['devices'])) {
	$error[] = 'Not enough information to '.$action.': '
		.'either the device ID, or MAC address + package name must be provided';
} else if ($action != 'register' and $action != 'unregister') {
	$error[] = 'Invalid action: '.$action;
} else {
	// Set up the criteria to find the device(s)
	// MAC address + package name was used as the Unique ID until version 0.2.3
	$db = new NMConnection('devices');
	if (isset($_POST['uid'])) {
		$devices[] = array('uid' => $_POST['uid']);
	} else if (isset($_POST['gcm_id'])) {
		$devices[] = array('gcm_id' => $_POST['gcm_id']);
	} else if (isset($_POST['mac']) && isset($_POST['package_name'])) {
		$devices[] = array('mac' => $_POST['mac'], 'package_name' => $_POST['package_name']);
	}
	
	$existing = $db->getRows($devices);
	if ($action == 'register') {
		if (count($existing) == 1) {
			// Already registered, update the DB
			foreach ($existing as $row) {
				$new[] = array( 'gcm_id' => $_POST['gcm_id'] );
			}
			$success = $db->updateRows($existing, $new);
			if (!$success) $error[] = $db->history;
		} else if (count($existing) > 1) {
			$error[] = 'This device is already registered with multiple IDs: '
				.json_encode($existing);
		} else {
			// Organize the device info to be put into database
			$data = $_POST;
			if (is_array($data['data'])) $data = array_merge($data, $data['data']);
			unset($data['action'],$data['source'],$data['time'],$data['data']);
			
			
			// Register this device
			$success = $db->addRows(array( $data ));
			if (!$success) $error[] = array_pop($db->history);
		}
	} else if ($action == 'unregister') {
		if (count($existing) > 0) {
			// Unregister this device
			$success = $db->updateRows($devices, array(array( 'gcm_id' => '' )));
			if (!$success) {
				$error[] = 'Unregister query failed: '.array_pop($db->history);
			}
		} else {
			//$error[] = 'Device(s) not registered: '.array_pop($db->history);
		}
	}
}

Log::w(json_encode($error),$action);
echo json_encode(array( 'success' => $success, 'error' => $error ));
