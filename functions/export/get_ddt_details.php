<?php
require_once '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $recordId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    try {
        $conn = getDbInstance();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Recupera i dati del documento
        $stmt = $conn->prepare("SELECT * FROM exp_documenti WHERE id = :id");
        $stmt->bindParam(':id', $recordId, PDO::PARAM_INT);
        $stmt->execute();
        $ddt = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Recupera i dati del terzista
        $stmt = $conn->prepare("SELECT ragione_sociale, nazione FROM exp_terzisti WHERE id = :id");
        $stmt->bindParam(':id', $ddt['id_terzista'], PDO::PARAM_INT);
        $stmt->execute();
        $terzista = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Recupera i lanci associati
        $stmt = $conn->prepare("SELECT lancio, articolo, paia, note FROM exp_dati_lanci_ddt WHERE id_doc = :id_doc");
        $stmt->bindParam(':id_doc', $recordId, PDO::PARAM_INT);
        $stmt->execute();
        $lanci = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        include BASE_PATH . '/utils/alerts.php';
        
        $totalPaia = 0;  // Variabile per accumulare il totale delle paia
        
        echo '<p><strong>Numero Documento:</strong> ' . $ddt['id'] . '</p>';
        echo '<p><strong>Destinatario:</strong> ' . htmlspecialchars($terzista['ragione_sociale']) . ' (' . htmlspecialchars($terzista['nazione']) . ')</p>';
        echo '<p><strong>Data:</strong> ' . htmlspecialchars($ddt['data']) . '</p>';
        echo '<p><strong>Stato:</strong> ' . htmlspecialchars($ddt['stato']) . '</p>';
        
        echo '<h4>Dettagli Lanci DDT</h4>';
        echo '<table class="table table-bordered">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Lancio</th>';
        echo '<th>Codice Articolo</th>';
        echo '<th>Paia</th>';
        echo '<th>Note</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($lanci as $lancio) {
            $totalPaia += $lancio['paia'];  // Aggiungi il numero di paia al totale
            echo '<tr>';
            echo '<td>' . htmlspecialchars($lancio['lancio']) . '</td>';
            echo '<td>' . htmlspecialchars($lancio['articolo']) . '</td>';
            echo '<td>' . htmlspecialchars($lancio['paia']) . '</td>';
            echo '<td>' . htmlspecialchars($lancio['note']) . '</td>';
            echo '</tr>';
        }
        echo '<tr>';
        echo '<td colspan="2"><strong>Totale Paia</strong></td>';
        echo '<td><strong>' . $totalPaia . '</strong></td>';
        echo '<td></td>';
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';
        
    } catch (PDOException $e) {
        // Log dell'errore
        error_log("Errore nel recupero dei dettagli del documento {$recordId}: " . $e->getMessage());
        
        // Mostra un messaggio di errore
        echo '<div class="alert alert-danger">Si è verificato un errore durante il recupero dei dati</div>';
    }
}
?>