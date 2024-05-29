<?php

session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';

try {
    // Connessione al database usando PDO
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Selezione delle colonne desiderate
    $select = array('IDRIP', 'ARTICOLO', 'QTA', 'CARTELLINO', 'COMMESSA', 'LABORATORIO', 'DATA', 'LINEA');

    $chunk_size = 100;
    $offset = 0;

    // Ottieni il numero totale di righe
    $stmt_count = $pdo->prepare("SELECT COUNT(*) as total_count FROM riparazioni");
    $stmt_count->execute();
    $total_count = $stmt_count->fetch(PDO::FETCH_ASSOC)['total_count'];

    $handle = fopen('php://memory', 'w');

    fputcsv($handle, $select);
    $filename = 'Vista Riparazioni.csv';

    $num_queries = ceil($total_count / $chunk_size);

    // Evita memory leak per un grande numero di righe usando limit e offset:
    for ($i = 0; $i < $num_queries; $i++) {
        $stmt = $pdo->prepare("SELECT IDRIP, ARTICOLO, QTA, CARTELLINO, COMMESSA, LABORATORIO, DATA, LINEA FROM riparazioni LIMIT :offset, :chunk_size");
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':chunk_size', $chunk_size, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $offset += $chunk_size;
        foreach ($rows as $row) {
            fputcsv($handle, array_values($row));
        }
    }

    // Resetta il puntatore del file all'inizio del file
    fseek($handle, 0);
    // Indica al browser che sarà un file csv
    header('Content-Type: application/csv');
    // Salva invece di visualizzare la stringa csv
    header('Content-Disposition: attachment; filename="' . $filename . '";');
    // Invia le righe csv generate direttamente al browser
    fpassthru($handle);

} catch (PDOException $e) {
    // Se si verifica un errore, visualizza il messaggio di errore
    echo "Errore: " . $e->getMessage();
}
?>
