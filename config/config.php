<?php
// Mostra tutti gli errori PHP (solo in fase di sviluppo)
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require ("url.php");

require ("database.php");
/*
define('DB_HOST', "localhost");
define('DB_USER', "root");
define('DB_PASSWORD', "");
define('DB_NAME', "my_webgre");

function getDbInstance()
{
	$host = 'localhost';
	$dbname = 'my_webgre';
	$username = 'root';
	$password = '';

	$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $pdo;
}
*/
$debug = true;