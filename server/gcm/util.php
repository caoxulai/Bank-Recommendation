<?php 

// Let's make sure the timezone is the same
date_default_timezone_set('America/Toronto');

// Converts Objects to Array
// From "If Not True Then False" article "PHP stdClass to Array and Array to stdClass - stdClass Object"
// http://www.if-not-true-then-false.com/2009/php-tip-convert-stdclass-object-to-multidimensional-array-and-convert-multidimensional-array-to-stdclass-object/
function objectToArray($d) {
	if (is_object($d)) {
		// Gets the properties of the given object
		// with get_object_vars function
		$d = get_object_vars($d);
	}

	if (is_array($d)) {
		/*
		* Return array converted to object
		* Using __FUNCTION__ (Magic constant)
		* for recursive call
		*/
		return array_map(__FUNCTION__, $d);
	}
	else {
		// Return array
		return $d;
	}
}

/**
 * Returns the URL of the path to the current page
 */
function curPageURL() {
	$isHTTPS = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
	$port = (isset($_SERVER['SERVER_PORT']) && ((!$isHTTPS && $_SERVER['SERVER_PORT'] != '80') || ($isHTTPS && $_SERVER['SERVER_PORT'] != '443')));
	$port = ($port) ? ':'.$_SERVER['SERVER_PORT'] : '';
	$request_uri = explode('/', $_SERVER['REQUEST_URI']);
	array_pop($request_uri);
	$url = ($isHTTPS ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].$port.implode('/', $request_uri);
	return $url;
}

/**
 * Serialize 
 *
 * @param array row associative array of keys to values, 
 * where values of the key 'data' is encoded as a string represention of the JSON
 * @param array keys array of additional keys to encode as a string representation of the JSON
 */
function serializeData($row) {
	// Optional parameters
	if (func_num_args() == 2) {
		$keys = func_get_arg(1);
		if (!array_search('data', $keys))
			$keys[] = 'data';
	} else {
		$keys = array( 'data' );
	}
	
	foreach ($keys as $key) {
		if (isset($row[$key]) and !is_string($row[$key])) {
			$row[$key] = json_encode($row[$key]);
		}
	}
	return $row;
}

function unserializeData($row) {
	// Optional parameters
	if (func_num_args() > 2)
		$stripslash = func_get_arg(2);
	if (func_num_args() > 1) {
		$keys = func_get_arg(1);
		if (!array_search('data', $keys))
			$keys[] = 'data';
	} else {
		$keys = array( 'data' );
	}
	
	foreach ($keys as $key) {
		if (isset($row[$key]) and is_string($row[$key])) {
			if ($stripslash)
				$row[$key] = stripslashes($row[$key]);
			$row[$key] = json_decode($row[$key]);
		}
	}
	return $row;
}

function redirect($page) {
	echo '
<script type="text/javascript">
<!--
window.location = "'.$page.'";
-->
</script>';
}

?>