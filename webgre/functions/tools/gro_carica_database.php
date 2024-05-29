<?php
session_start();
require_once '../../config/config.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Connessione al database
    $db = getDbInstance();
    $sheetData = $_SESSION['sheetData'];

    // Svuota la tabella temp_dati_gruppi
    $db->rawQuery('TRUNCATE TABLE temp_dati_gruppi');

    // Carica i dati dalla tabella Excel escludendo la prima riga (intestazione)
    foreach(array_slice($sheetData, 1) as $row) {
        $data = array(
            'cartellino' => $row[0],
            'commessa' => $row[1],
            'articolo' => $row[2],
            'descrizione' => $row[3],
            'qta' => $row[4],
            'lancio' => $row[5],
            'P01' => $row[6],
            'P02' => $row[7],
            'P03' => $row[8],
            'P04' => $row[9],
            'P05' => $row[10],
            'P06' => $row[11],
            'P07' => $row[12],
            'P08' => $row[13],
            'P09' => $row[14],
            'P10' => $row[15],
            'P11' => $row[16],
            'P12' => $row[17],
            'P13' => $row[18],
            'P14' => $row[19],
            'P15' => $row[20],
            'P16' => $row[21],
            'P17' => $row[22],
            'P18' => $row[23],
            'P19' => $row[24],
            'P20' => $row[25],
            'nu' => $row[26],
            // Aggiungi tutte le colonne necessarie
        );
        $db->insert('temp_dati_gruppi', $data);
    }

    echo json_encode(array('success' => true));
} else {
    echo json_encode(array('success' => false));
}
