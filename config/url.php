<?php
define('BASE_PATH', dirname(__DIR__));

// Determina la tua cartella app in modo statico
define('APP_FOLDER', '/webgre');

// URL base dell'app
$base_url = 'https://' . $_SERVER['HTTP_HOST'] . APP_FOLDER;
define('BASE_URL', $base_url);

// Define the URL of the dominio
$dominio = BASE_URL;

?>