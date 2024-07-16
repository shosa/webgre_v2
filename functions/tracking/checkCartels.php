<?php
require_once '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commessa = isset($_POST['commessa']) ? $_POST['commessa'] : null;

    if (!$commessa) {
        echo json_encode(['error' => 'Parametro commessa non ricevuto']);
        exit;
    }

    try {
        $pdo = getDbInstance();
        $stmt = $pdo->prepare('SELECT `Descrizione Articolo`, `Tot` FROM dati WHERE Cartel = :commessa');
        $stmt->execute(['commessa' => $commessa]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($results) > 0) {
            $cartelDetails = [];
            foreach ($results as $row) {
                $cartelDetails[] = [
                    'Descrizione Articolo' => $row['Descrizione Articolo'],
                    'Dati' => [
                        'Tot' => $row['Tot']
                    ]
                ];
            }
            echo json_encode([
                'exists' => true,
                'cartel' => $cartelDetails
            ]);
        } else {
            echo json_encode(['exists' => false]);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
