<?php
require_once 'lib/PasswordHash.php';

// Base-2 logarithm of the iteration count used for password stretching
$hash_cost_log2 = 8;
// Do we require the hashes to be portable to older systems (less secure)?
$hash_portable = FALSE;

function encrypt_password($pass) {
	$hasher = new PasswordHash($hash_cost_log2, $hash_portable);
	$hash = $hasher->HashPassword($pass);
	if (strlen($hash) < 20)
		die('Failed to hash new password');
	unset($hasher);
	return $hash;
}

function check_password($hash, $pass) {
	$hasher = new PasswordHash($hash_cost_log2, $hash_portable);
	return $hasher->CheckPassword($pass, $hash);
}

unset($hash_cost_log2,$hash_portable);

?>