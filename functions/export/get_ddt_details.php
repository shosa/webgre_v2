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

       

        $totalPaia = 0;
        foreach ($lanci as $lancio) {
            $totalPaia += $lancio['paia'];


            echo '<li><strong>#</strong>' . htmlspecialchars($lancio['lancio']) . ' | <b>' . htmlspecialchars($lancio['articolo']) . '</b> | ' . htmlspecialchars($lancio['paia']) . ' PA</li>';
          

        }

        echo '<div style="text-align: right; margin-top: 1.5rem;">';
        echo '<p style="font-size: 1.2rem;"><strong>TOTALE: ' . $totalPaia . ' PA</strong></p>';
        echo '</div>';

        echo '</div>';

    } catch (PDOException $e) {
        // Log dell'errore
        error_log("Errore nel recupero dei dettagli del documento {$recordId}: " . $e->getMessage());

        // Mostra un messaggio di errore
        echo '<div class="alert alert-danger">Si è verificato un errore durante il recupero dei dati</div>';
    }
}
?>