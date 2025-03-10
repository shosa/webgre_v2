<?php
require_once '../../config/config.php';


// Set headers for SQL download
header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename=database_backup_' . DB_NAME . '_' . date('Y-m-d') . '.sql');

try {
    $db = getDbInstance();
    
    // Generate SQL header
    echo "-- Backup completo del database " . DB_NAME . " generato il " . date('Y-m-d H:i:s') . "\n";
    echo "-- Server version: " . $db->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n\n";
    
    // Get all tables
    $tablesStmt = $db->query("SHOW TABLES");
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        // Create table structure
        echo "-- Struttura della tabella `$table`\n";
        echo "DROP TABLE IF EXISTS `$table`;\n";
        
        $createTableStmt = $db->query("SHOW CREATE TABLE `$table`");
        $createTable = $createTableStmt->fetch(PDO::FETCH_ASSOC);
        echo $createTable['Create Table'] . ";\n\n";
        
        // Get table data
        $dataStmt = $db->query("SELECT * FROM `$table`");
        $data = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Insert data
        if (!empty($data)) {
            echo "-- Dump dei dati della tabella `$table`\n";
            
            foreach ($data as $row) {
                $escapedValues = array_map(function($value) use ($db) {
                    if ($value === null) {
                        return 'NULL';
                    }
                    return $db->quote($value);
                }, $row);
                
                echo "INSERT INTO `$table` (`" . implode('`, `', array_keys($row)) . "`) VALUES (" . implode(', ', $escapedValues) . ");\n";
            }
            
            echo "\n";
        }
    }
} catch (PDOException $e) {
    die('Errore durante il backup: ' . $e->getMessage());
}