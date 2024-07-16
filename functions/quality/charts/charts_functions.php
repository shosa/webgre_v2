<?php
require_once '../../config/config.php';

function calculate_percentage($date, $type)
{
    $pdo = getDbInstance();

    // Prepara le query in base al tipo di calcolo richiesto
    if ($type === 'difetti') {
        $stmt1 = $pdo->prepare("SELECT COUNT(DISTINCT cq_records.cartellino) AS unique_cartellino, COUNT(*) AS num_defects FROM cq_records WHERE data = :current_date");
        $stmt2 = $pdo->prepare("SELECT SUM(pa) as totalPa FROM (SELECT DISTINCT cartellino, pa FROM cq_records WHERE data = :current_date) as distinct_pa");
    } else if ($type === 'scarti') {
        $stmt1 = $pdo->prepare("SELECT COUNT(DISTINCT cq_records.cartellino) AS unique_cartellino, COUNT(*) AS num_defects FROM cq_records WHERE data = :current_date AND esito='X'");
        $stmt2 = $pdo->prepare("SELECT SUM(pa) as totalPa FROM (SELECT DISTINCT cartellino, pa FROM cq_records WHERE esito='X' AND data = :current_date) as distinct_pa");
    } else {
        return 0; // Tipo non riconosciuto
    }

    // Esegui la prima query
    $stmt1->bindParam(':current_date', $date, PDO::PARAM_STR);
    $stmt1->execute();
    $result1 = $stmt1->fetch(PDO::FETCH_ASSOC);

    // Esegui la seconda query
    $stmt2->bindParam(':current_date', $date, PDO::PARAM_STR);
    $stmt2->execute();
    $result2 = $stmt2->fetch(PDO::FETCH_ASSOC);

    // Calcola la percentuale
    if ($result1 && $result2 && $result1['num_defects'] > 0 && $result2['totalPa'] > 0) {
        return ($result1['num_defects'] * 100) / $result2['totalPa'];
    } else {
        return 0; // Evita divisione per zero e gestisci i casi in cui non ci sono dati
    }
}
?>