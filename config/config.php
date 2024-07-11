<?php
// Mostra tutti gli errori PHP (solo in fase di sviluppo)
error_reporting(E_ALL);
ini_set('display_errors', 'On');

// Ottieni la directory del file config.php
define('BASE_PATH', dirname(__DIR__));

// Determina la tua cartella app in modo statico
define('APP_FOLDER', '');

// URL base dell'app
$base_url = 'http://' . $_SERVER['HTTP_HOST'] .  APP_FOLDER;
define('BASE_URL', $base_url);

// Define the URL of the dominio
$dominio = BASE_URL;

define('DB_HOST', "localhost");
define('DB_USER', "root");
define('DB_PASSWORD', "");
define('DB_NAME', "my_webgre");
// Replace these values with your actual database credentials
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