<?php
require_once 'config/config.php';
require_once 'utils/helpers.php';
session_start();
session_destroy();


if (isset($_COOKIE['series_id']) && isset($_COOKIE['remember_token'])) {
	clearAuthCookie();
}
header('Location:index.php');
exit;

?>