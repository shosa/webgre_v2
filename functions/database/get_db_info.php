<?php
require_once '../../config/config.php';


try {
    $db = getDbInstance();
    
    // Get MySQL version
    $versionStmt = $db->query("SELECT VERSION() as version");
    $mysqlVersion = $versionStmt->fetch(PDO::FETCH_ASSOC)['version'];
    
    // Get database name
    $dbName = DB_NAME;
    
    // Get tables list
    $tablesStmt = $db->query("SHOW TABLES");
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
    $totalTables = count($tables);
    
    // Get total database size
    $sizeQuery = "SELECT 
                    SUM(data_length + index_length) / 1024 / 1024 as size_mb 
                  FROM 
                    information_schema.TABLES 
                  WHERE 
                    table_schema = :dbname";
    
    $sizeStmt = $db->prepare($sizeQuery);
    $sizeStmt->execute(['dbname' => $dbName]);
    $sizeData = $sizeStmt->fetch(PDO::FETCH_ASSOC);
    $totalSize = number_format($sizeData['size_mb'], 2) . ' MB';
    
    // Prepare response
    $response = [
        'dbName' => $dbName,
        'mysqlVersion' => $mysqlVersion,
        'totalTables' => $totalTables,
        'totalSize' => $totalSize
    ];
    
    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Errore nel recupero delle informazioni del database: ' . $e->getMessage()]);
}