<?php
require_once '../../config/config.php';
require_once BASE_PATH.'/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if(isset($_POST['upload'])) {
    $target_dir = BASE_PATH."/uploads/";
    $target_file = $target_dir.basename($_FILES["excel_file"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Verifica se il file è un file Excel
    if($imageFileType != "xls" && $imageFileType != "xlsx") {
        echo "Siamo spiacenti, solo file Excel sono ammessi.";
        $uploadOk = 0;
    }

    // Controlla se ci sono errori durante il caricamento
    if($uploadOk == 0) {
        echo "Siamo spiacenti, il tuo file non è stato caricato.";
    } else {
        if(move_uploaded_file($_FILES["excel_file"]["tmp_name"], $target_file)) {
            $spreadsheet = IOFactory::load($target_file);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();
            // Ricerca dinamica dell'indice della colonna "Qtà"
            $qtyIndex = array_search('Qtà', $sheetData[0]);

            // Riepilogo dei dati
            $lancioIndex = array_search('Lancio', $sheetData[0]);
            $articoloIndex = array_search('Articolo', $sheetData[0]);
            $descrizioneIndex = array_search('Descrizione Articolo', $sheetData[0]);
            $cartelliniIndex = array_search('Cartel.', $sheetData[0]);  // Aggiunto

            $cartelliniLetti = count(array_unique(array_column($sheetData, $cartelliniIndex))) - 1;
            $paiaTotali = array_sum(array_map(function ($value) use ($qtyIndex) {
                return isset($value[$qtyIndex]) && is_numeric($value[$qtyIndex]) ? (int)$value[$qtyIndex] : 0;
            }, array_slice($sheetData, 1)));

            // Utilizza gli indici ottenuti per accedere ai dati
            $lancio = isset($lancioIndex) ? $sheetData[1][$lancioIndex] : '';
            $articolo = isset($articoloIndex) ? $sheetData[1][$articoloIndex] : '';
            $descrizione = isset($descrizioneIndex) ? $sheetData[1][$descrizioneIndex] : '';


        } else {
            echo "Siamo spiacenti, si è verificato un errore durante il caricamento del file.";
        }
    }
}

?>