<?php
require_once('../../config/config.php');
require_once(BASE_PATH . '/vendor/tcpdf/tcpdf.php');

// Crea un oggetto TCPDF
$pdf = new TCPDF();
$pdf->SetAutoPageBreak(true, 10);
$pdf->SetMargins(10, 10, 10);
$deposito = isset($_GET['deposito']) ? $_GET['deposito'] : '';
// Imposta il nome del file PDF
$filename = 'inventario_completo.pdf';

// Imposta gli header per il PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');

// Fetch data from inv_list
$db = getDbInstance();
$db->where('dep', $deposito);
$db->orderBy('art', 'ASC'); // Aggiunta della clausola di ordinamento
$invListData = $db->get('inv_list');

// Verifica se ci sono errori nella query
if ($db->getLastErrno()) {
    die('Errore nella query: ' . $db->getLastError());
}

// Verifica se ci sono dati nella tabella
if (empty($invListData)) {
    die('Nessun dato trovato nella tabella inv_list.');
}

// Set font for the table content
$pdf->SetFont('helvetica', '', 10);

// Itera sui dati unici della colonna 'cm'
$uniqueCategories = array_unique(array_column($invListData, 'cm'));

foreach ($uniqueCategories as $category) {
    // Aggiungi una pagina al PDF per ogni categoria
    $pdf->AddPage();

    // Set font for the table headers
    $pdf->SetFont('helvetica', 'B', 12);

    // Aggiungi intestazione
    $pdf->Cell(0, 10, 'Inventario completo del deposito: ' . $deposito, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Categoria: ' . $category, 0, 1, 'C');

    // Filtra i dati solo per la categoria corrente
    $categoryData = array_filter($invListData, function ($row) use ($category) {
        return $row['cm'] === $category;
    });
    $pdf->SetFont('helvetica', 'N', 10);
    // Costruisci l'HTML della tabella
    $htmlTable = '<table border="1">';
    $htmlTable .= '<tr>';
    $htmlTable .= '<th style="width: 20%; text-align:left;"><b>Codice Articolo</b></th>';
    $htmlTable .= '<th style="width: 70%; text-align:left;"><b>Descrizione</b></th>';
    $htmlTable .= '<th style="width: 10%; text-align:left;"><b>Quantità</b></th>';
    $htmlTable .= '</tr>';

    foreach ($categoryData as $row) {
        if ($row['is_num'] == 1) {
            // Se is_num è 1, aggiungi una riga vuota
            $htmlTable .= '<tr>';
            $htmlTable .= '<td style="width: 20%; text-align:left;">' . $row['art'] . '</td>';
            $htmlTable .= '<td colspan="16">' . $row['des'] . '</td>';
            $htmlTable .= '</tr>';

            // Aggiungi una tabella di 20 colonne e 2 righe
            $numValues = explode(';', $row['num']);
            $qtaValues = explode(';', $row['qta']);

            // Assicurati che entrambe le righe abbiano lo stesso numero di colonne
            $numCount = count($numValues);
            $qtaCount = count($qtaValues);
            $maxCount = max($numCount, $qtaCount);

            // Riempi le righe con un numero costante di celle
            $htmlTable .= '<tr>';
            for ($i = 0; $i < 20; $i++) { // Ho impostato 20, ma puoi cambiare a seconda delle tue esigenze
                $htmlTable .= '<td style="background-color:#ededed;width: 5%; text-align:center;">' . ($i < $numCount ? $numValues[$i] : '') . '</td>';
            }
            $htmlTable .= '</tr><tr>';
            for ($i = 0; $i < 20; $i++) { // Anche qui, ho impostato 20
                $htmlTable .= '<td style="width: 5%; text-align:center;">' . ($i < $qtaCount ? $qtaValues[$i] : '') . '</td>';
            }
            $htmlTable .= '</tr>';
        } else {
            // Altrimenti, aggiungi una riga normale
            $htmlTable .= '<tr>';
            $htmlTable .= '<td style="width: 20%; text-align:left;">' . $row['art'] . '</td>';
            $htmlTable .= '<td>' . $row['des'] . '</td>';
            $htmlTable .= '<td style="width: 10%; text-align:center;">' . $row['qta'] . '</td>';
            $htmlTable .= '</tr>';
        }
    }

    $htmlTable .= '</table>';

    // Aggiungi la tabella al PDF
    $pdf->writeHTML($htmlTable, true, false, true, false, '');
}

// Chiudi il PDF e visualizzalo
try {
    $pdf->Output();
} catch (Exception $e) {
    die('Errore durante la generazione del PDF: ' . $e->getMessage());
}
?>