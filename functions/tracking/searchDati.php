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
    $ordine = $_POST['ordine']; // Aggiunto il filtro per l'Ordine

    try {
        $pdo = getDbInstance();

        $sql = 'SELECT Cartel, `Commessa Cli`, Articolo, `Descrizione Articolo`, Ln, `Ragione Sociale`, Tot 
                FROM dati 
                WHERE 1=1';
        $params = [];

        if (!empty($cartel)) {
            $sql .= ' AND Cartel LIKE :cartel';
            $params['cartel'] = "$cartel%"; // Modifica: Inizia con il valore di $cartel
        }
        if (!empty($commessa)) {
            $sql .= ' AND `Commessa Cli` LIKE :commessa';
            $params['commessa'] = "$commessa%"; // Modifica: Inizia con il valore di $commessa
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
        if (!empty($ordine)) { // Aggiunta della condizione per il filtro "Ordine"
            $sql .= ' AND Ordine LIKE :ordine';
            $params['ordine'] = "%$ordine%";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($results);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
