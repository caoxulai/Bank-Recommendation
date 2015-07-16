// Messages for debugging is shown in #status and #response
// TODO: Make an interface and button to request messages within a certain time frame

"use strict";

var devicesTable;

function getActionData(id) {
	var action = id.replace("_btn",""),
		data = { action : action };
	
	// Set extra post data according to action selected
	if (action == "message") {
		data = { message : $("#message").val() };
	} else if (action == "install") {
		var selection = $("#current_apps_selection > option").filter(":selected");
		data["web_directory_path"] = selection.val();
		data["file_name"] = selection.text();
	} else if (action == "uninstall") {
		var selection = $("#device_apps > option").filter(":selected");
		data["package_name"] = selection.text();
	} else if (action == "profile") {
		data["profile"] = $("#device_setting").val();
	}

	// Add other data
	data["options"] = [];
	$("#options_fieldset").find("select[id^=option_]").each(function () {
		var option_name = this.id.replace("option_", ""),
			val = $(this).children("option").filter(":selected").val();
		if (val) {
			data[option_name] = val;
			data["options"].push(option_name);
		}
	});
	if (data["options"].length == 0) delete data["options"];
	
	// Add list of selected devices to message
	data["devices"] = devicesTable.selectedDevices();
	
	return data;
}

// Document Ready
$(document).ready(function() {
	init_main();
	
	// Devices Table
	devicesTable = new DevicesTable();
	
	// Installable Apps list
	startJSTemplate();
});
