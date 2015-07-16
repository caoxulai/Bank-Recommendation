<?php 
session_set_cookie_params(0);
session_start();
require_once 'connection.php';
require_once 'util.php';

$db = new NMConnection('sessions');
$response = $db->getRows(array(array( 'session_id' => $_SESSION['session_id'], 'user_ip' => $_SERVER['REMOTE_ADDR'] )));
if (count($response) == 0) redirect(curPageURL().'/login.php');
?>
<!DOCTYPE html>
<html>
<head>
	<title>Novramedia Tablet Kiosk - Registration Tests</title>
	
	<meta charset="UTF-8">
	
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<link href='http://fonts.googleapis.com/css?family=Istok+Web:400,700' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css" href="main.css" />
	<script src="main.js"></script>
	<script src="test.js"></script>

</head>
<body>

<header>
	<div class="logout">Log out</div>
</header>
<div class="clear"></div>

<section>
	<table id="devices_tbl">
		<thead>
			<tr>
				<th class="select_chkbox"><input type="checkbox" name="selected[]" /></th>
				<th class="device_last_seen">Online</th>
				<th class="device_name">Name</th>
				<th class="device_mac">MAC Address</th>
				<th class="device_uid">UID</th>
				<th class="device_package_name none">Related App</th>
				<th class="device_ssid">SSID</th>
				<th class="device_internal_ip">Internal IP</th>
				<th class="device_external_ip">External IP</th>
				<th class="device_service_status">Service</th>
				<th class="device_bar_status">Bar</th>
				<th class="device_gcm_id none">GCM Registration ID</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="select_chkbox"><input type="checkbox" name="selected[]" value="0" /></td>
				<td class="device_last_seen" data-key="last_seen"></td>
				<td class="device_name" data-key="name"></td>
				<td class="device_mac" data-key="mac"></td>
				<td class="device_uid" data-key="uid"></td>
				<td class="device_package_name none" data-key="package_name"></td>
				<td class="device_ssid" data-key="ssid"></td>
				<td class="device_internal_ip" data-key="internal_ip"></td>
				<td class="device_external_ip" data-key="external_ip"></td>
				<td class="device_service_status" data-key="service_status"></td>
				<td class="device_bar_status" data-key="bar_status"></td>
				<td class="device_gcm_id none" data-key="gcm_id"></td>
			</tr>
		</tbody>
	</table><br />
	<button id="toggle_device_selection_btn">Toggle Device Selection</button><br /><br />
	
	<label for="device_apps">Uninstallable Apps on device: </label><br />
	<select id="device_apps">
		<option value="">No device selected</option>
	</select>
	<button class="action_btn" id="uninstall_btn">Uninstall</button><br /><br />
	<div id="devices_tbl_status"></div>
	
	<fieldset>
		<legend>Unregister tests</legend>
		<label>Single Device with GCM Registration ID</label>
		<button class="action_btn" id="single_gcm_id_unregister_btn">Unregister</button><br />
		<label>Single Device with MAC and App name</label>
		<button class="action_btn" id="single_mac_unregister_btn">Unregister</button><br />
		<label>All selected devices</label>
		<button class="action_btn" id="unregister_btn">Unregister</button><br />
	</fieldset>
	
	<fieldset id="new_test_device">
		<legend>Register a new Device!</legend>
		
		<label>Name: </label>
		<input type="text" maxlength="255" name="name"></input><br />
		<label>MAC Address: </label>
		<input type="text" maxlength="17" name="mac"></input><br />
		<label>GCM Registration ID: </label>
		<input type="text" name="gcm_id"></input><br />
		<button class="action_btn" id="register_btn">Register</button><br />
		<button class="action_btn" id="random_register_btn">Register Random device</button>
	</fieldset><br />
</section>

<section>
	<button id="toggle_debug">Debug</button><br />
	
	<fieldset class="debug">
		<legend>Current Action Status</legend>
		<div id="status"></div>
	</fieldset>
	
	<fieldset class="debug none">
		<legend>Sent Message Content</legend>
		<div id="sent"></div>
	</fieldset>

	<fieldset class="debug none">
		<legend>GCM Return Message</legend>
		<div id="response"></div>
	</fieldset>

	<fieldset class="debug none">
		<legend>Device Return Message</legend>
		<div id="device_message"></div>
	</fieldset>
	<button class="debug none" id="message_clear_btn">Clear All Messages</button>
</section>

<div class="clear"></div>

</body>
</html>