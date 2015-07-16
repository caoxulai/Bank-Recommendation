'use strict';

// jQuery plug-in for an Enter keypress event by "Click Upvote", from Stack Overflow
// http://stackoverflow.com/questions/6524288/jquery-event-for-user-pressing-enter-in-a-textbox
jQuery.fn.onEnterKeyUp = function(callback)
{
    this.keyup(function(e)
        {
            if(e.keyCode == 13)
            {
                e.preventDefault();
                if (typeof callback == 'function')
                    callback.apply(this);
            }
        }
    );
    return this;
}
jQuery.fn.onEscapeKeyUp = function(callback)
{
    this.keyup(function(e)
        {
            if(e.keyCode == 27)
            {
                e.preventDefault();
                if (typeof callback == 'function')
                    callback.apply(this);
            }
        }
    );
    return this;
}

function hasClass(element, cls) {
  return (' ' + element.className + ' ').indexOf(' ' + cls + ' ') > -1;
}

/**
 * Parses date from 'yyyy-mm-dd hh:mm:ss' format
 * @return the parsed Date object
 */
function parseDate(sdatetime) {
  var parts = sdatetime.split(' '),
      date;

  if (parts.length == 0) return NaN;

  parts = parts[0].split('-').concat(parts[1].split(':'));
  date = new Date(parts[0],parts[1]-1,parts[2],parts[3],parts[4],parts[5]); // months are 0-based
  return date;
}

/* DevicesTable class */
var DevicesTable = function() {
	var $table;
	var $status;
	var $templateRow;  // Template row used to make new rows
	var that = this;

	/**
	 * Retrieve the IDs of all devices on the list
	 */
	this.allDevices = function () {
		var devices = [];
		$('tbody .device_gcm_id',$table).each(function() {
			var gcm_id = $(this).text();
			devices.push(gcm_id);
		});
		return devices;
	};

	/**
	 * Retrieve the IDs of all selected devices
	 * Refer to table format below for column order
	 */
	this.selectedDevices = function () {
		var devices = [];
		if ($("thead input[name='selected[]']",$table).is(':checked'))
			devices = this.allDevices();
		$('tr',$table).has("input[name='selected[]']:checked").each(function() {
			var gcm_id = $(this).find('.device_gcm_id').text();
			devices.push(gcm_id);
		});
		return devices;
	};

	/**
	 * Retrieve the IDs of all devices on the list.
	 * Ignores any devices with com.novramedia.test as a package_name
	 */
	this.allTestDevices = function () {
		var devices = [];
		$('tbody .device_gcm_id',$table).each(function() {
			if ($(this).siblings('.device_package_name').text() != 'com.novramedia.test')
				return;
			var gcm_id = $(this).text();
			devices.push(gcm_id);
		});
		return devices;
	};

	/**
	 * Retrieve the IDs of all selected devices
	 * Refer to table format below for column order
	 * Ignores any devices with com.novramedia.test as a package_name
	 */
	this.selectedTestDevices = function () {
		var devices = [];
		if ($("thead input[name='selected[]']",$table).is(':checked'))
			devices = this.allTestDevices();
		$('tbody tr',$table).has("input[name='selected[]']:checked").each(function() {
			if ($(this).find('.device_package_name').text() != 'com.novramedia.test')
				return;
			var gcm_id = $(this).find('.device_gcm_id').text();
			devices.push(gcm_id);
		});
		return devices;
	};

	this.formatTimeLastSeen = (function() {
		var sec = 1000,  // JS uses ms
			min = 60 * sec,
			hour = 60 * min,
			day = 24 * hour,
			week = 7 * day,
			month = 31 * day,
			year = 365 * day;
		return function(interval) {
			// Some tablets have clocks that are ahead of the server
			var sign = (interval > 0) ? '' : '-';
			// TODO: What to do for messages from the future?
			//interval = Math.abs(interval);
			if (interval < 0) return '0s';
			// Choose which time term to use
			if (interval > year) return sign + Math.floor(interval/year)+' yrs';
			else if (interval > month) return sign + Math.floor(interval/month)+' mths';
			else if (interval > week) return sign + Math.floor(interval/week)+' wks';
			else if (interval > day) return sign + Math.floor(interval/day)+' days';
			else if (interval > hour) return sign + Math.floor(interval/hour)+'h';
			else if (interval > min) return sign + Math.floor(interval/min)+'m';
			else if (interval > sec) return sign + Math.floor(interval/sec)+'s';
			else return interval;
		};
	})();

	this.loadDevicesTable = function (options) {
		$templateRow = $('tbody tr:first-child',$table).detach();
		this.reloadDevicesTable(options).success(function() {
			sendMessage({
				action : 'query',
				devices : that.allDevices()
			})
			.success(function() {
				that.reloadDevicesTable();
			})
			.error(function() {
				that.reloadDevicesTable();
			});
		});
	};

	this.reloadDevicesTable = function (options) {
		var data = {
			action : 'get',
			table : 'devices',
			opt: {
              request : [
                'last_seen', 'name', 'mac', 'serial', 'bar_status', 
                'service_status', 'package_name', 'ssid', 'internal_ip', 
                'external_ip', 'version', 'uid', 'gcm_id'
              ],
              order_by : 'name'
            },
			data : [{ enabled_status : 1 }]
		};
		var post = $.post('db.php', data,
			function(response) {
				try {
					var json = JSON.parse(response);
					$status.addClass('none').text(json);

					// Populate Table with Devices
					var rows = [],
						device, $row, row_id, td, key;
					for (var i = 0; i < json.length; i++) {
						// Is device in the table already?
						device = json[i];
						row_id = device['mac'].replace(/:/gi,'')+device['uid']+device['package_name'].replace(/\./gi,'');
						$row = $('#'+row_id);
						if ($row.length == 0) {
							$row = $templateRow.clone();
							$row.attr('id', row_id);
						} else {
							$row.detach();
						}
						// Put values in the right col using data-key attribute
						$row.find('td').each(function() {
							td = $(this);
							key = td.data('key');
							if (key)
								td.text(json[i][key]);
						});
						// Don't show unregistered devices
						if ($row.children('.device_gcm_id').text() == '') continue;

						rows.push($row);  // new rows
					}
					$('tbody tr',$table).remove();  // clean deleted devices
					$table.append(rows);

					// Populate device info section
					that.populateSelectedDeviceInfo();

					// Format some columns
					// last seen
					var now = new Date().getTime();
					$('tbody .device_last_seen',$table).each(function() {
						var last = parseDate($(this).text()),
                diff = now - last;
						$(this).text(that.formatTimeLastSeen(diff));
					});
				} catch (e) {
					$status.removeClass('none');
					$status.text((new Date())+e+response);
				}
			}
		);
		// TODO: Make this status an overlay over the (faded or visible) table or sth
		$status.text('Loading table...');
		return post;
	};

	this.listDeviceInfo = function (uid, package_name, mac) {
		var data = {
			action : 'get',
			table : 'devices',
			opt : {
              request : ['uid', 'installed_third_party_apps']
            },
			data : [{ uid : uid }]
		};
		// Check if it has a UID (> 0.2.3)
		if (data.data[0].uid.length == 0) {
			delete data.data[0].uid;
			data.data[0].mac = mac;
			data.data[0].package_name = package_name;
			if (mac.length == 0 || package_name.length == 0) {
				$status.text('No UID or MAC from device').removeClass('none');
			}
		}
		$.post('db.php', data,
			function(response) {
				try {
					var device_apps = $('#device_apps');
					response = JSON.parse(response);
					device_apps.text('');
					if (response.length == 0)
						return;

					// Populate App view
					var apps = response[0].installed_third_party_apps;
					if (apps != '') {
						apps = JSON.parse(apps);
					} else {
						apps = [apps];
					}
					for (var i = 0; i < apps.length; i++) {
						var app = apps[i];
						$('<option value="'+app+'">'+app+'</option>').appendTo(device_apps);
					}

					// Populate Settings
					that.listDeviceSettings(response[0].uid);
				} catch (e) {
					$status.append((new Date())+response).removeClass('none');
				}
			}
		);
		$status.text('Loading app list...').addClass('none');
	};

	this.listDeviceSettings = function (uid) {
		var data = {
			action : 'get',
			table : 'devices',
			opt: {
              request: ['settings']
            },
			data : [{ uid : uid }]
		};
		// Get settings for device
		$.post('db.php', data,
			function(response) {
				try {
					var device_setting = $('#device_setting');
					response = JSON.parse(response);
					if (response.length == 0)
						return;

					// Populate Settings view
					var settings = response[0].settings;
					device_setting.text(settings);
				} catch (e) {
					$status.append((new Date())+response).removeClass('none');
				}
			}
		);
		$status.text('Loading device settings...').addClass('none');
	};

	this.populateSelectedDeviceInfo = function () {
		var tr = $('tr.selected_device',$table);
		if (tr.length == 0)
			return;
		that.listDeviceInfo(
			tr.children('.device_uid').text(),
			tr.children('.device_package_name').text(),
			tr.children('.device_mac').text()
		);
	};

	var onDeviceNameDblClick = function() {
		var $this = $(this),
			old_value = this.innerHTML,
			editor;
		// Already editing?
		if ($this.children('input').length > 0)
			return;
		this.innerHTML = '';
		$status.addClass('none');
		longPolling('stop');

		// Name editor text input
		editor = $('<input type="text" maxlength="255" name="new_name" value="'+old_value+'" />')
			.onEnterKeyUp(function() {
				var data = {
					action : 'set',
					table : 'devices',
					data : [{ name : editor.val() }],
					row : [{
						uid : $this.siblings('.device_uid').text(),
						package_name : $this.siblings('.device_package_name').text()
					}]
				};
				// Check if it has a UID (> v0.2.3)
				if (data.row[0].uid.length == 0) {
					delete data.row[0].uid;
					data.row[0].mac = $this.siblings('.device_mac').text();
				}
				// Disable the input field during update
				this.disabled = 'disabled';
				// Update database
				$.post('db.php', data,
					function(response) {
						try {
							response = JSON.parse(response);
							if (response.success) {
								$this.html(editor.val());
								that.reloadDevicesTable();
								longPolling();
							} else {
								throw (new Date())+'Update error';
							}
						} catch (e) {
							$status.text((new Date())+response).removeClass('none');
						}
					}
				)
				.error(function(jqxhr,status,error) {
					editor.prop('disabled', false);
				});
			}).onEscapeKeyUp(function() {
				if ($this.children('input').length > 0) {
					$this.html(old_value);
					longPolling();
				}
			}).blur(function() {
				// Does the editor still have keyboard focus?
				// (Was it a mouse blur event?)
				if (document.activeElement == this)
					return;
				if ($this.children('input').length > 0) {
					$this.text(old_value);
					longPolling();
				}
			});
		$this.html(editor);
		editor.focus();
	};

	(function () {
		$table = $('#devices_tbl');
		$status = $('#devices_tbl_status');
		// Device focus
		$('tbody',$table).on('click', 'tr', function(event) {
			if (event.target.nodeName == 'INPUT') return;
			var tr = $(this).toggleClass('selected_device');

			if (tr.hasClass('selected_device')) {
				// Show device info section
				$('.selected_device_info').removeClass('none');
				// Clear all other device info selection
				tr.siblings('.selected_device').removeClass('selected_device');
				// Populate device info section
				that.populateSelectedDeviceInfo();
			} else {
				// Hide device info section
				$('.selected_device_info').addClass('none');
				// Clear device info section
				$('#device_apps').html('<option value="">No device selected</option>');
				$('#device_setting').text('');
			}
		});
		// Device name change
		$('tbody',$table).on('dblclick', '.device_name', onDeviceNameDblClick);
		// Toggle button
		$('#toggle_device_selection_btn').click(function() {
			$("tbody input[name='selected[]']",$table).click();
		});
		that.loadDevicesTable();
	}());

	return this;
}
/**
 * Send a message to selected devices
 */
var sendMessage = function(data) {
	var post = $.post('kiosk.php', data,
		function(response) {
			try {
				response = JSON.parse(response);
				$('#response').text(JSON.stringify(response));
				// TODO: Deal with unsent messages (see kiosk.php)
			} catch (e) {
				// Response is not JSON
				$('#response').text((new Date())+response);
			}
			$('#status').text('Received response');
		}
	)
	.error(function(jqxhr,status,error) {
		$('#response').text('Send error: '+status+','+error);
		$('#status').text('Send error: '+status+','+error);
	});
	$('#response').text('');
	$('#sent').text(JSON.stringify(data));
	$('#status').text('Sending message');
	return post;
};

/**
 * Long Polling
 * Is a closure so anyone else can cancel the polling by passing any value
 * when calling it
 */
// TODO: This is polling, not long polling. Gotta change the PHP, and keep a constant connection
var longPolling = (function() {
	var post, timeout;

	return function(stop) {
		if (typeof stop !== 'undefined') {
			post.abort();
			clearTimeout(timeout);
			return;
		}
		post = $.post('message.php', { action : 'check' , later_than : new Date().getTime()/1000 - 60*60 },
			function(response) {
				try {
					var data = JSON.parse(response.data),
						i;
					for (i = 0; i < data.length; i++) {
						delete data[i].data;  // Hide the message data, some of them are too big
					}
					data = JSON.stringify(data);
				} catch (e) {}
				$('#device_message').text((new Date())+'\n'+response);
				if (typeof devicesTable !== 'undefined') {
					devicesTable.reloadDevicesTable();
				}
				timeout = setTimeout(longPolling, 5000);
			}
		)
		.error(function(jqxhr,status,error) {
			$('#device_message').text((new Date())+'\nPolling error: '+status+','+error);
			timeout = setTimeout(longPolling, 5000);
		});
	}
})();

function startJSTemplate() {
	if ($('#current_apps_selection').length > 0) {
		$.post('template_pieces.php', { get : 'current_apps_selection' },
			function(response) {
				$('#current_apps_selection').html(response);
			}
		);
	}
}

function init_main() {
	$('.action_btn').click(function (event) {
		sendMessage(getActionData(event.target.id));
	});
	$('#toggle_debug').click(function () {
		$('.debug').toggleClass('none');
	});
	$('#message_clear_btn').click(function (event) {
		if (confirm('This action cannot be reverted. Clear all saved messages?')) {
			$.post('db.php', { action : 'clear', table : 'messages' },
				function (response) {
					try {
						response = JSON.parse(response);
						$('#status').html(JSON.stringify(response));
					} catch (e) {
						$('#status').html((new Date())+response);
					}
				}
			);
		}
	});
	$('#login_btn').click(function () {
		$('section').removeClass('none');
		$('section.login').addClass('none');
	});
	$('.logout').click(function() {
		window.location = '/gcm/logout.php';
	});

	// Register Ajax error handler
	$(document).ajaxError(function (event,jqxhr,settings,error) {
		if (settings.url == 'kiosk.php') {
			return;
		}
		$('#status').html((new Date())+': '+error);
	});

	// Message events
	$.ajaxSetup({ timeout: 15000 });
	longPolling();
}
