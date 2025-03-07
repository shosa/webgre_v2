<?php

require_once '../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartel = $_POST['cartel'];
    $commessa = $_POST['commessa'];
    $articolo = $_POST['articolo'];
    $descrizioneArticolo = $_POST['descrizioneArticolo'];
    $ln = $_POST['ln'];
    $ragioneSociale = $_POST['ragioneSociale'];
    $ordine = $_POST['ordine'];

    try {
        $pdo = getDbInstance();

        $sql = 'SELECT Cartel, `Commessa Cli`, Articolo, `Descrizione Articolo`, Ln, `Ragione Sociale`, Tot 
                FROM dati 
                WHERE 1=1';
        $params = [];

        if (!empty($cartel)) {
            $sql .= ' AND Cartel LIKE :cartel';
            $params['cartel'] = "$cartel%";
        }
        if (!empty($commessa)) {
            $sql .= ' AND `Commessa Cli` LIKE :commessa';
            $params['commessa'] = "$commessa%";
        }
        if (!empty($articolo)) {
            $sql .= ' AND Articolo LIKE :articolo';
            $params['articolo'] = "%$articolo%";
        }
        if (!empty($descrizioneArticolo)) {
            $sql .= ' AND `Descrizione Articolo` LIKE :descrizioneArticolo';
            $params['descrizioneArticolo'] = "%$descrizioneArticolo%";
        }
        if (!empty($ln)) {
            $sql .= ' AND Ln LIKE :ln';
            $params['ln'] = "%$ln%";
        }
        if (!empty($ragioneSociale)) {
            $sql .= ' AND `Ragione Sociale` LIKE :ragioneSociale';
            $params['ragioneSociale'] = "%$ragioneSociale%";
        }
        if (!empty($ordine)) {
            $sql .= ' AND Ordine LIKE :ordine';
            $params['ordine'] = "%$ordine%";
        }

        // Aggiungi ORDER BY per ordinare i risultati per Cartel in ordine crescente
        $sql .= ' ORDER BY Cartel ASC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($results);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
