<?php

session_start();

require_once '../../../config/config.php';
require_once BASE_PATH . '/utils/helpers.php';
require BASE_PATH . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];

    try {
        $pdo = getDbInstance();
        
        // Ottieni i valori esistenti in altre tabelle
        $valuesToPreserve = [];

        $stmt = $pdo->query("SELECT DISTINCT cartel FROM track_links");
        $trackLinks = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $valuesToPreserve = array_merge($valuesToPreserve, $trackLinks);

        $stmt = $pdo->query("SELECT DISTINCT CARTELLINO FROM riparazioni");
        $riparazioni = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $valuesToPreserve = array_merge($valuesToPreserve, $riparazioni);

        // Carica il file XLSX
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        // Ignora la prima riga se contiene i nomi delle colonne
        array_shift($data);

        $preservedValues = [];
        $errors = [];

        // Prepara la query di inserimento
        $sql = "INSERT INTO dati (St, Ordine, Rg, CCli, `Ragione Sociale`, Cartel, `Commessa Cli`, PO , Articolo, `Descrizione Articolo`, Nu, `Marca Etich`, Ln, P01, P02, P03, P04, P05, P06, P07, P08, P09, P10, P11, P12, P13, P14, P15, P16, P17, P18, P19, P20, Tot) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        // Verifica che ogni riga abbia esattamente 34 colonne
        foreach ($data as $index => $row) {
            // Se la riga contiene meno di 34 colonne, aggiungi valori nulli
            while (count($row) < 34) {
                $row[] = null;
            }
            // Se la riga contiene più di 34 colonne, tronca la riga
            if (count($row) > 34) {
                $row = array_slice($row, 0, 34);
            }

            try {
                // Verifica se il valore di Cartel deve essere preservato
                if (in_array($row[5], $valuesToPreserve)) {
                    $preservedValues[] = $row[5];
                }

                // Esegui l'inserimento
                $stmt->execute($row);
            } catch (Exception $e) {
                // Memorizza l'errore senza interrompere l'importazione
                $errors[] = "Errore alla riga " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        if (!empty($preservedValues)) {
            $preservedValuesString = implode(', ', array_unique($preservedValues));
            $_SESSION['info'] = "I seguenti Cartellini sono stati preservati perchè utilizzati in altre tabelle: " . $preservedValuesString;
        }

        // Aggiungi i messaggi di errore alla sessione
        if (!empty($errors)) {
            $_SESSION['unknown'] = $errors;
        }

        $_SESSION['success'] = "Dati importati con successo!";

    } catch (Exception $e) {
        $_SESSION['danger'] = "Errore durante l'importazione del file: " . $e->getMessage();
    }

    header('Location: ../settings');
    exit;
} else {
    $_SESSION['danger'] = "Errore durante l'importazione del file!";
    header('Location: ../settings');
    exit;
}
?>
