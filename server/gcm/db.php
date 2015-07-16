<?php
require_once 'connection.php';

if ($_POST['action'] == 'get_all') {
	// Client devices array
	$db = new NMConnection($_POST['table']);
	$result = $db->getAllRows();
	foreach ($result as &$row) {
		if (array_key_exists('installed_third_party_apps', $row))
			$row['installed_third_party_apps'] = stripslashes($row['installed_third_party_apps']);
		if (array_key_exists('installed_apps', $row))
			$row['installed_apps'] = stripslashes($row['installed_apps']);
	}
	echo json_encode($result);
} else if ($_POST['action'] == 'get') {
	// Get requested info from table
	$db = new NMConnection($_POST['table']);
    if (isset($_POST['opt'])) {
        $result = $db->getRows($_POST['data'], $_POST['opt']);
	} else {
        $result = $db->getRows($_POST['data']);
	}
    if ($result) {
      if (count($result) > 0 and array_key_exists('installed_third_party_apps', $result[0])) {
          foreach ($result as &$row) {
              $row['installed_third_party_apps'] = stripslashes($row['installed_third_party_apps']);
          }
      }
      if (count($result) > 0 and array_key_exists('installed_apps', $result[0])) {
          foreach ($result as &$row) {
              $row['installed_apps'] = stripslashes($row['installed_apps']);
          }
      }
    } else {
      $result = $db->history;
    }
	echo json_encode($result);
} else if ($_POST['action'] == 'set') {
	$db = new NMConnection($_POST['table']);
	$success = $db->updateRows($_POST['row'], $_POST['data']);
	echo json_encode(array( 'success' => $success ));
} else if ($_POST['action'] == 'add') {
	$db = new NMConnection($_POST['table']);
	$success = $db->addRows($_POST['data']);
	echo json_encode(array( 'success' => $success ));
} else if ($_POST['action'] == 'clear') {
	$db = new NMConnection($_POST['table']);
	if (!$db->deleteAllRows())
		die('Failed to clear table');
	echo json_encode($db->getAllRows());
}

?>