<?php
define('BASE_PATH', dirname(__DIR__));

// Determina la tua cartella app in modo statico
define('APP_FOLDER', '');

// URL base dell'app
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . APP_FOLDER;
define('BASE_URL', $base_url);

// Define the URL of the dominio
$dominio = BASE_URL;

?>