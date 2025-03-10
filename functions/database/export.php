<?php
require_once '../../config/config.php';


if (!isset($_GET['table']) || empty($_GET['table']) || !isset($_GET['format'])) {
    die('Parametri mancanti');
}

$table = $_GET['table'];
$format = $_GET['format'];

// Semplice validazione del nome tabella (per prevenire SQL injection)
if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
    die('Nome tabella non valido');
}

// Validate format
$allowedFormats = ['csv', 'sql', 'json'];
if (!in_array($format, $allowedFormats)) {
    die('Formato non valido');
}

try {
    $db = getDbInstance();
    
    // Get table data
    $stmt = $db->query("SELECT * FROM `$table`");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get table structure
    $structStmt = $db->query("DESCRIBE `$table`");
    $structure = $structStmt->fetchAll(PDO::FETCH_ASSOC);
    
    switch ($format) {
        case 'csv':
            // Set headers for CSV download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $table . '_export_' . date('Y-m-d') . '.csv');
            
            // Create output stream
            $output = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            if (!empty($data)) {
                // Add headers
                fputcsv($output, array_keys($data[0]));
                
                // Add data rows
                foreach ($data as $row) {
                    fputcsv($output, $row);
                }
            }
            
            fclose($output);
            break;
            
        case 'sql':
            // Set headers for SQL download
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $table . '_export_' . date('Y-m-d') . '.sql');
            
            // Generate SQL header
            echo "-- Export di $table generato il " . date('Y-m-d H:i:s') . "\n";
            echo "-- Server version: " . $db->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n\n";
            
            // Create table structure
            echo "-- Struttura della tabella `$table`\n";
            echo "DROP TABLE IF EXISTS `$table`;\n";
            
            $createTableStmt = $db->query("SHOW CREATE TABLE `$table`");
            $createTable = $createTableStmt->fetch(PDO::FETCH_ASSOC);
            echo $createTable['Create Table'] . ";\n\n";
            
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
            }
            break;
            
        case 'json':
            // Set headers for JSON download
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $table . '_export_' . date('Y-m-d') . '.json');
            
            $export = [
                'table' => $table,
                'created' => date('Y-m-d H:i:s'),
                'structure' => $structure,
                'data' => $data
            ];
            
            echo json_encode($export, JSON_PRETTY_PRINT);
            break;
    }
} catch (PDOException $e) {
    die('Errore durante l\'esportazione: ' . $e->getMessage());
}