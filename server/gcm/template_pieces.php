<?php
include 'util.php';

if ($_POST['get'] == 'current_apps_selection') {
	// App list array
	$apks = array();
	// loop through app directory
	$path = curPageURL().'/app/';
	$system_path = getcwd().'/app/';
	if ($dir = opendir($system_path)) {
		while (false !== ($entry = readdir($dir))) {
			// TODO: Get package name from file (if possible, or some other way like user interaction)
			if (!is_dir($entry) && strlen($entry) > 4 && substr($entry,-4,4) == '.apk') {
				$apks[] = array( 'path' => $path, 'name' => $entry );
			}
		}
		closedir($dir);
	}

	foreach ($apks as $apk) {
		echo '<option value="'.$apk['path'].'">'.$apk['name'].'</option>';
	}
}

?>