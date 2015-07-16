<?php 
//session_set_cookie_params(0);
session_start();

require_once 'connection.php';
require_once 'util.php';
require_once 'password.php';

$url = curPageURL().'/manage.php';
$username = '';
$password = '';
$db = new NMConnection('sessions');
$response = $db->getRows(array(array( 'session_id' => $_SESSION['session_id'], 'user_ip' => $_SERVER['REMOTE_ADDR'] )));
//$infos[] = 'Session: '.var_export($_SESSION, true).' Session ID: '.session_id();

if (count($response) == 0) {
	$success = true;
	$login_class = '';
	$register_class = ' none';
	$db->select('users');
	
	if ($_POST['action'] == 'login') {
		$username = $_POST['username'];
    $password = $_POST['password'];
  	$time = date('Y-m-d H:i:s'); 
    // $time = date('Y-m-d H:i:s', $_POST['time']);
    // "last_visit" timestamp should correctly update now (mchoi)
		
		// Check values
		if (empty($username) or empty($password)) {
			$errors[] = 'Please enter a username and password';
			$success = false;
		} else if (!preg_match('/^[a-zA-Z0-9_]{1,60}$/', $username)) {
			$errors[] = 'Invalid username';
			$success = false;
		}
		
		// Attempt to Log In
		if ($success) {
			// Retrieve user info
			$response = $db->getRows(array(array( 'username' => $username )));
			if (count($response) == 1) {
				$response = $response[0];
				// Match password with password hash
				if (check_password($response['password'], $password)) {
					// Successful login, update visiting time
					$db->updateRows(
						array(array( 'username' => $username )), 
						array(array(
							'last_visit' => $time,
							'failed_login_count' => 0,
							'login_count' => $response['login_count'] + 1
						))
					);
					
					// Create a session
					if ($response['enabled'] == 1) {
						$_SESSION['user'] = $response['username'];
						$_SESSION['session_id'] = md5($response['cookie_string'].session_id());
						$db->select('sessions');
						$db->addRows(array(array(
							'user_id' => $response['id'],
							'session_id' => $_SESSION['session_id'],
							'user_ip' => $_SERVER['REMOTE_ADDR']
						)));
						$infos[] = 'You\'re logged in!';
						$infos[] = 'New Session: '.var_export($_SESSION, true);
						session_write_close();
						redirect($url);
					} else {
						$infos[] = 'Registration successful! Please wait for confirmation';
					}
				} else {
					// Record login failure into DB
					$db->updateRows(
						array(array( 'username' => $username )), 
						array(array( 'failed_login_count' => $response['failed_login_count'] + 1 ))
					);
					$errors[] = 'Authentication failed';
					$success = false;
				}
			} else {
				$errors[] = 'Username not found';
				$success = false;
			}
		}
	} else if ($_POST['action'] == 'register') {
		$realname = $_POST['realname'];
		$username = $_POST['username'];
		$password = $_POST['password'];
		$email = $_POST['email'];
		$time = date('Y-m-d H:i:s', time());
		
		// Check values
		if ($password != $_POST['password_confirmation']) {
			$errors[] = 'Passwords do not match';
			$success = false;
		}
		if (strlen($password) < 6) {
			$errors[] = 'Password is too short (less than 6)';
			$success = false;
		}
		if (empty($username)) {
			$errors[] = 'Please enter a username';
			$success = false;
		} else if (!preg_match('/^[a-zA-Z0-9_]{1,60}$/', $username)) {
			$errors[] = 'Invalid username';
			$success = false;
		}
		$email_pattern = '/[^@ ]+@[^@ ]+.[^@ ]+/';
		if (empty($email)) {
			$errors[] = 'Please enter an email';
			$success = false;
		} else if (!preg_match($email_pattern, $email) || strlen(preg_replace($email_pattern, '', $_POST['email'])) > 0) {
			$errors[] = 'Please enter a valid email address';
			$success = false;
		}
		unset($email_pattern);
		$response = $db->getRows(array(array( 'username' => $username )));
		if (count($response) > 0) {
			$errors[] = 'Username already exists';
			$success = false;
		}
		
		// Attempt to register
		if ($success) {
			$record = array(
				'realname' => $realname,
				'username' => $username,
				'password' => encrypt_password($password),// Encrypt password into password hash
				'email' => $email,
				'date_created' => $time,
				'cookie_string' => md5($username.$password.$email.rand())
			);
			$success = $db->addRows(array($record));
			if ($success) {
				$infos[] = 'Registration successful! After confirmation, you can log in normally';
			}
		}
	}
	
	// TODO: Put these in a template file, to display across all pages
	echo '<!DOCTYPE html>
<html>
<head>
	<title>Novramedia Tablet Kiosk - Log In</title>
	
	<meta charset="UTF-8">
	
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<link href="http://fonts.googleapis.com/css?family=Istok+Web:400,700" rel="stylesheet" type="text/css">
	<link rel="stylesheet" type="text/css" href="main.css" />
	<script src="login.js"></script>

</head>
<body>
';

	if (count($infos) > 0) {
		// TODO: Make a light green info box
		echo '<div class="info"><ul>';
		foreach ($infos as $info) {
			echo '
    <li>'.$info.'</li>';
		}
		echo '</ul></div><br />';
		if ($_POST['action'] == 'register') {
			$login_class = ' none';
			$register_class = '';
		}
	}
	if (count($errors) > 0) {
		// TODO: Make a light red info box
		echo '<div class="error"><ul>';
		foreach ($errors as $error) {
			echo '
    <li>'.$error.'</li>';
		}
		echo '</ul></div><br />';
		if ($_POST['action'] == 'register') {
			$login_class = ' none';
			$register_class = '';
		}
	}
	
	// TODO: Ideally you should put all the login/register logic in another php file, and use ajax to retrieve the results
	echo '
<section>
	<fieldset class="login'.$login_class.'">
		<legend>Log In</legend>
		<form class="center" action="login.php" method="post">
			<input type="text" maxlength="64" name="username" value="'.$username.'"
				placeholder="Username" /><br />
			<input type="password" maxlength="255" name="password"
				placeholder="Password" /><br />
			<input type="hidden" name="url" value="'.$url.'" />
			<input type="hidden" name="action" value="login" />
			<button id="btn_login">Log In</button>&nbsp;
			<a class="small toggle_login_form">Register</a>
		</form>
	</fieldset>
	<fieldset class="register'.$register_class.'">
		<legend>Register</legend>
		<form class="center" action="login.php" method="post">
			<label>Name: </label>
			<input type="text" maxlength="64" name="realname" value="'.$realname.'"
				placeholder="Real Name" /><br />
			<label>Username: </label>
			<input type="text" maxlength="64" name="username" value="'.$username.'"
				placeholder="Username" /><br />
			<label>Password: </label>
			<input type="password" name="password"
				placeholder="Password" /><br />
			<label>Confirm<br />Password: </label>
			<input type="password" name="password_confirmation"
				placeholder="Re-type Password" /><br />
			<label>Email: </label>
			<input type="email" name="email" value="'.$email.'"
				placeholder="Valid Email" /><br />
			<input type="hidden" class="none" name="action" value="register" />
			<button id="btn_register">Register</button>&nbsp;
			<a class="small toggle_login_form">Log In</a>
		</form>
	</fieldset>
</section>
</body>
</html>';
} else {
	redirect($url);
}
