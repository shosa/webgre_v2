<?php
require_once '../../config/config.php';

try {
    // Connessione al database utilizzando PDO
    $pdo = getDbInstance();
    $commessas = json_decode($_POST['commessas'], true);
    $data = [];

    foreach ($commessas as $commessa) {
        $stmt = $pdo->prepare("SELECT Articolo, `Descrizione Articolo`, Tot FROM dati WHERE Cartel = ?");
        $stmt->execute([$commessa]);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }
    }

    // Raggruppa per Articolo e calcola il totale per ogni articolo
    $groupedData = [];
    foreach ($data as $row) {
        $articolo = $row['Articolo'];
        if (!isset($groupedData[$articolo])) {
            $groupedData[$articolo] = [
                'Articolo' => $row['Articolo'],
                'Descrizione Articolo' => $row['Descrizione Articolo'],
                'Tot' => 0
            ];
        }
        $groupedData[$articolo]['Tot'] += $row['Tot'];
    }

    // Calcola il totale generale
    $total = array_sum(array_column($data, 'Tot'));

    echo json_encode(['data' => array_values($groupedData), 'total' => $total]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>