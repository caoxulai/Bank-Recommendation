<?php 
//session_set_cookie_params(0);
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
	<title>Novramedia Tablet Kiosk - Device Management</title>
	
	<meta charset="UTF-8">
	
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<link href='http://fonts.googleapis.com/css?family=Istok+Web:400,700' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css" href="main.css" />
	<script src="main.js"></script>
	<script src="manage.js"></script>

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
				<th class="device_serial">Serial</th>
				<th class="device_bar_status">Bar</th>
				<th class="device_service_status">Service</th>
				<th class="device_package_name none">Related App</th>
				<th class="device_ssid">SSID</th>
				<th class="device_internal_ip">Internal IP</th>
				<th class="device_external_ip">External IP</th>
				<th class="device_version">Version</th>
				<th class="device_uid none">UID</th>
				<th class="device_gcm_id none">GCM Registration ID</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="select_chkbox"><input type="checkbox" name="selected[]" value="0" /></td>
				<td class="device_last_seen" data-key="last_seen"></td>
				<td class="device_name" data-key="name"></td>
				<td class="device_mac" data-key="mac"></td>
				<td class="device_serial" data-key="serial"></td>
				<td class="device_bar_status" data-key="bar_status"></td>
				<td class="device_service_status" data-key="service_status"></td>
				<td class="device_package_name none" data-key="package_name"></td>
				<td class="device_ssid" data-key="ssid"></td>
				<td class="device_internal_ip" data-key="internal_ip"></td>
				<td class="device_external_ip" data-key="external_ip"></td>
				<td class="device_version" data-key="version"></td>
				<td class="device_uid none" data-key="uid"></td>
				<td class="device_gcm_id none" data-key="gcm_id"></td>
			</tr>
		</tbody>
	</table>
	<br />
	<button id="toggle_device_selection_btn">Toggle Device Selection</button><br />
	<br />
	<div class="selected_device_info clear none">
		<label for="device_apps">Uninstallable Apps on device: </label><br />
		<select id="device_apps">
			<option value="">No device selected</option>
		</select>
		<button class="action_btn" id="uninstall_btn">Uninstall</button><br />
		<fieldset id="device_settings">
			<legend>Schedules</legend>
			<label for="device_setting">Name: </label><br />
			<textarea
				id="device_setting"
				placeholder="No profile"></textarea><br />
			<button class="action_btn" id="profile_btn">Send Profile to selected devices</button><br />
		</fieldset><br />
		<div class="clear"></div>
	</div>
	<label for="current_apps_selection">Installable Apps</label><br />
	<select id="current_apps_selection"></select>
	<button class="action_btn" id="install_btn">Install</button><br /><br />
	<div id="devices_tbl_status"></div>

	<div class="actions">
		<!--
		TODO: Should have a database of device action settings for each manager so 
		they can change whether an action wakes up the app, shows UI, etc?
		-->
		<!-- TODO: Use an "all" query for syncing everything -->
		<button class="action_btn" id="ping_btn">Ping</button><br />
		<label for="query_everything_btn">Query: </label>
		<button class="action_btn" id="query_everything_btn">Device</button>
		<button class="action_btn" id="query_installed_third_party_apps_btn">App</button>
		<button class="action_btn" id="query_settings_btn">Profile</button><br />
		<label for="boot_btn">Service: </label>
		<button class="action_btn" id="boot_btn">Start</button>
		<button class="action_btn" id="stop_btn">Stop</button><br />
	</div>
</section>

<section>
	<textarea
		id="message"
		placeholder="Type in a message to send to selected devices."
		maxlength="255"></textarea><br />
	<button class="action_btn" id="message_btn">Message Devices</button>
	<button id="toggle_debug">Debug</button><br />
	
	<fieldset id="options_fieldset" class="options">
		<legend>Optional Actions to send</legend>
		<label for="option_lock_bar" class="list2col">System Bar: </label>
		<select id="option_lock_bar">
			<option value="" selected="selected">---</option>
			<option value="false">On</option>
			<option value="true">Off</option>
		</select><br />
		<label for="option_screen_on">Screen: </label>
		<select id="option_screen_on">
			<option value="" selected="selected">---</option>
			<option value="true">On</option>
			<option value="false">Off</option>
		</select><br />
		<label for="option_keep_screen_on">Keep Screen on: </label>
		<select id="option_keep_screen_on">
			<option value="" selected="selected">---</option>
			<option value="true">yes</option>
			<option value="false">no</option>
		</select><br />
		<label for="option_show_ui">Show Kiosk Management Settings: </label>
		<select id="option_show_ui">
			<option value="" selected="selected">---</option>
			<option value="true">Show</option>
			<option value="false">Hide</option>
		</select><br />
		<label for="option_run_startup_app">Run Startup App: </label>
		<select id="option_run_startup_app">
			<option value="" selected="selected">---</option>
			<option value="true">Run</option>
		</select><br />
		<label for="option_allow_settings">Allow Settings: </label>
		<select id="option_allow_settings">
			<option value="" selected="selected">---</option>
			<option value="true">Allow</option>
			<option value="false">Don't Allow</option>
		</select><br />
		<label for="option_restart">Reboot: </label>
		<select id="option_restart">
			<option value="" selected="selected">---</option>
			<option value="true">Reboot</option>
		</select><br />
		<p>Note: Pinging ignores any extra actions sent</p>
		<button class="action_btn" id="opt_btn">Send only optional actions</button>
	</fieldset>

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