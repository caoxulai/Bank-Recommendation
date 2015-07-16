<?php
session_set_cookie_params(0);
session_start();
session_destroy();
header('Location: /gcm/login.php');
?>
