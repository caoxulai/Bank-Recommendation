// Messages for debugging is shown in #status and #response
// TODO: Make an interface and button to request messages within a certain time frame

"use strict";

var devicesTable;
var random_chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_";

function get_random_str(min_length, max_length) {
	max_length = (max_length === undefined) ? min_length : max_length;  // Optional parameter
	var r = [],
		len = min_length + Math.floor(Math.random()*(max_length-min_length));
	
	for (var i = 0; i < len; i++)
		r.push(random_chars.charAt(Math.floor(Math.random()*random_chars.length)));
	
	return r.join("");
}

var random_names = [
		"The Bass's Tablet",
		"The Hat's Tablet",
		"The Wolf's Tablet",
		"The Pen's Tablet",
		"The Calm's Tablet",
		"The Keyboard's Tablet",
		"The Brain's Tablet",
		"The Spirit's Tablet",
		"The Head's Tablet",
		"The Secret Stash's Tablet",
		get_random_str(7, 36),
		get_random_str(7, 36)
	];

function getActionData(id) {
	// Set post data according to action selected
	var data = [];
	if (id == "register_btn") {
		data = { action : "register",
			gcm_id : $("#new_test_device input[name=gcm_id]").val(),
			mac : $("#new_test_device input[name=mac]").val(),
			package_name : "com.novramedia.test"
		};
		data.name = $("#new_test_device input[name=name]").val();
		if (data.name == "") delete data.name;
	} else if (id == "random_register_btn") {
		data = { action : "register",
			name : random_names[Math.floor(Math.random()*random_names.length)],
			gcm_id : get_random_str(37, 100),
			mac : get_random_str(17),
			package_name : "com.novramedia.test"
		};
	} else if (id == "unregister_btn") {
		data = { action : "unregister",
			devices : devicesTable.selectedTestDevices()
		};
	} else if (id == "single_gcm_id_unregister_btn") {
		var selected = $("#devices_tbl tbody tr").has("input[name='selected[]']:checked").last(),
			gcm_id = "";
		if (selected)
			gcm_id = selected.find("td.device_gcm_id").text();
		data = { action : "unregister", gcm_id : gcm_id };
	} else if (id == "single_mac_unregister_btn") {
		var selected = $("#devices_tbl tbody tr").has("input[name='selected[]']:checked").last(),
			mac = "",
			package_name = "";
		if (selected) {
			mac = selected.find("td.device_mac").text();
			package_name = selected.find("td.device_package_name").text();
		}
		data = { action : "unregister",
			mac : mac,
			package_name : package_name
		};
	} else if (id == "message_clear_btn") {
		data = { action : "clear", table : "messages" };
		return data;
	}
	return data;
}

/**
 * Register fake device or unregister selected fake devices
 */
var registerDevices = function(data) {
	$.post("register.php", data,
		function(response) {
			try {
				response = JSON.parse(response);
				$("#response").html(JSON.stringify(response));
				if (response.success) devicesTable.reloadDevicesTable();
			} catch (e) {
				// Response is not JSON
				$("#response").html((new Date())+response);
			}
			$("#status").html("Received response");
		}
	)
	.error(function(jqxhr,status,error) {
		$("#response").html("Send error: "+status+","+error);
		$("#status").html("Send error: "+status+","+error);
	});
	$("#response").html("");
	$("#sent").html(JSON.stringify(data));
	$("#status").html("Sending message");
};

// Document Ready
$(document).ready(function() {
	init_main();
	
	$("button.action_btn").click(function (event) {
		registerDevices(getActionData(event.target.id));
	});
	
	// Devices Table
	devicesTable = new DevicesTable();
});
