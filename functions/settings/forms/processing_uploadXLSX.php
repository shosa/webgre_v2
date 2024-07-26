<?php

session_start();

require_once '../../../config/config.php';
require_once BASE_PATH . '/utils/helpers.php';
require BASE_PATH . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];

    try {
        // Cancella i dati esistenti nella tabella
        $pdo = getDbInstance();
        $pdo->exec("TRUNCATE TABLE dati");

        // Carica il file XLSX
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        // Ignora la prima riga se contiene i nomi delle colonne
        array_shift($data);

        // Prepara la query di inserimento
        $sql = "INSERT INTO dati (St, Ordine, Rg, CCli, `Ragione Sociale`, Cartel, `Commessa Cli`, PO , Articolo, `Descrizione Articolo`, Nu, `Marca Etich`, Ln, P01, P02, P03, P04, P05, P06, P07, P08, P09, P10, P11, P12, P13, P14, P15, P16, P17, P18, P19, P20, Tot) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        // Verifica che ogni riga abbia esattamente 34 colonne
        foreach ($data as $row) {
            // Se la riga contiene meno di 34 colonne, aggiungi valori nulli
            while (count($row) < 34) {
                $row[] = null;
            }
            // Se la riga contiene più di 34 colonne, tronca la riga
            if (count($row) > 34) {
                $row = array_slice($row, 0, 34);
            }



            $stmt->execute($row);
        }

        $_SESSION['message'] = "Dati importati con successo!";

    } catch (Exception $e) {
        $_SESSION['message'] = "Errore durante l'importazione del file: " . $e->getMessage();
    }

    header('Location: ../settings');
    exit;
} else {
    $_SESSION['message'] = "Errore durante l'importazione del file!";
    header('Location: ../settings');
    exit;
}
?>