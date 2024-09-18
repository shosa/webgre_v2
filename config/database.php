<?php
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
$debug = true;
?>