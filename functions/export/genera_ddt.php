<?php
require_once '../../config/config.php';
require_once BASE_PATH . '/vendor/autoload.php';

$progressivo = $_GET['progressivo'];
$dir = 'temp/';
$destDir = 'src/';

$files = scandir($dir);
$files = array_diff($files, array('.', '..'));
$db = getDbInstance();
$db->where('id_documento', $progressivo)->delete('exp_dati_articoli');

// Crea la cartella se non esiste
if (!file_exists($destDir . $progressivo)) {
    mkdir($destDir . $progressivo, 0777, true);
}

foreach ($files as $file) {
    $filePath = $dir . $file;
    $destFilePath = $destDir . $progressivo . '/' . $file;

    // Sposta il file nella nuova cartella
    rename($filePath, $destFilePath);

    $workbook = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($destFilePath);
    $worksheet = $reader->load($destFilePath)->getActiveSheet();

    $lancio = $worksheet->getCell('B2')->getValue();
    $articolo = $worksheet->getCell('B1')->getValue();
    $paia = $worksheet->getCell('B3')->getValue();

    $dataLanci = [
        'id_doc' => $progressivo,
        'lancio' => $lancio,
        'articolo' => $articolo,
        'paia' => $paia
    ];

    $db->insert('exp_dati_lanci_ddt', $dataLanci);

    $rows = $worksheet->toArray();
    $rows = array_slice($rows, 6);

    foreach ($rows as $row) {
        // Salta la riga con 'ORLATURA' nella cella A
        if ($row[0] === 'ORLATURA') {
            continue;
        }

        $data = [
            'id_documento' => $progressivo,
            'codice_articolo' => $row[1],
            'descrizione' => $row[2],
            'um' => $row[3],
            'qta_originale' => $row[5],
            'qta_reale' => $row[5]
        ];

        $db->insert('exp_dati_articoli', $data);
    }
}

// Controllo e rimozione dei duplicati
$query = "SELECT codice_articolo, descrizione, um, voce_doganale, ROUND(SUM(qta_originale), 2) as qta_originale, ROUND(SUM(qta_reale), 2) as qta_reale
          FROM exp_dati_articoli
          WHERE id_documento = ?
          GROUP BY codice_articolo";

$duplicateItems = $db->rawQuery($query, [$progressivo]);

// Elimina tutti i record esistenti per il progressivo
$db->where('id_documento', $progressivo)->delete('exp_dati_articoli');

// Re-inserisci i dati aggiornati
foreach ($duplicateItems as $item) {
    $data = [
        'id_documento' => $progressivo,
        'codice_articolo' => $item['codice_articolo'],
        'descrizione' => $item['descrizione'],  // Assumendo che sia la stessa descrizione per tutti gli elementi con lo stesso codice_articolo
        'voce_doganale' => $item['voce_doganale'],
        'um' => $item['um'],  // Assumendo che sia la stessa um per tutti gli elementi con lo stesso codice_articolo
        'qta_originale' => $item['qta_originale'],
        'qta_reale' => $item['qta_reale']
    ];

    $db->insert('exp_dati_articoli', $data);
}

echo json_encode(['success' => true, 'message' => 'DDT generati con successo']);
?>
