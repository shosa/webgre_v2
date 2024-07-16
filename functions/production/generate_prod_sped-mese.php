<?php
// Include la libreria TCPDF e la configurazione del database
require ("../../config/config.php");
require_once (BASE_PATH . "/vendor/tcpdf/tcpdf.php");

// Ricevi i parametri month dalla richiesta GET
$month = isset($_GET['month']) ? $_GET['month'] : 'GENNAIO';

// Ottieni la connessione PDO dal config.php
$db = getDbInstance();

// Query per ottenere i dati di produzione
$prod_sql = "SELECT * FROM prod_mesi WHERE MESE = :mese";
$prod_stmt = $db->prepare($prod_sql);
$prod_stmt->execute(['mese' => $month]);
$prod_data = $prod_stmt->fetchAll(PDO::FETCH_ASSOC);

// Query per ottenere i dati di spedizione
$sped_sql = "SELECT * FROM sped_mesi WHERE MESE = :mese";
$sped_stmt = $db->prepare($sped_sql);
$sped_stmt->execute(['mese' => $month]);
$sped_data = $sped_stmt->fetchAll(PDO::FETCH_ASSOC);

// Crea il PDF
$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Produzione e Spedizione - ' . $month);

// Imposta i margini
$pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Aggiungi una pagina
$pdf->AddPage();

// Titolo
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Produzione e Spedizione - ' . $month, 0, 1, 'C');
$pdf->Ln(5);

// Imposta il font
$pdf->SetFont('helvetica', '', 9);

// Inizializza i totali
$totals = array_fill(0, 18, 0);

// Tabelle
$html = '<table style="text-align: center; 
    vertical-align: middle;"border="1" cellspacing="0" cellpadding="2">
    <thead>
        <tr style="background-color: #D3E2F4; text-align: center;">
            <th rowspan="2" colspan="2" width="80"><b>GIORNO</b></th>
            <th colspan="9" width="342"><b>PRODUZIONE</b></th>
            <th style="background-color:#FFE78F;" colspan="9" width="342"><b>SPEDIZIONE</b></th>
        </tr>
        <tr style="background-color: #D3E2F4; text-align: center;">
            <th width="38"><b>MAN1</b></th>
            <th width="38"><b>MAN2</b></th>
            <th width="38"><b>MAN3</b></th>
            <th width="38"><b>ORL1</b></th>
            <th width="38"><b>ORL2</b></th>
            <th width="38"><b>ORL3</b></th>
            <th width="38"><b>ORL4</b></th>
            <th width="38"><b>TAGL1</b></th>
            <th width="38"><b>TAGL2</b></th>
            <th style="background-color:#FFE78F;" width="38"><b>MAN1</b></th>
            <th style="background-color:#FFE78F;" width="38"><b>RESO</b></th>
            <th style="background-color:#FFE78F;" width="38"><b>MAN2</b></th>
            <th style="background-color:#FFE78F;" width="38"><b>MAN3</b></th>
            <th style="background-color:#FFE78F;" width="38"><b>ORL1</b></th>
            <th style="background-color:#FFE78F;" width="38"><b>ORL2</b></th>
            <th style="background-color:#FFE78F;" width="38"><b>ORL3</b></th>
            <th style="background-color:#FFE78F;" width="38"><b>ORL4</b></th>
            <th style="background-color:#FFE78F;" width="38"><b>TOM ESTERO</b></th>
        </tr>
    </thead>
    <tbody>';

for ($i = 0; $i < max(count($prod_data), count($sped_data)); $i++) {
    $prod_row = $prod_data[$i] ?? [];
    $sped_row = $sped_data[$i] ?? [];
    // Salta le righe che sono "DOMENICA"
    if (isset($prod_row['NOMEGIORNO']) && $prod_row['NOMEGIORNO'] == 'DOMENICA') {
        continue;
    }
    if (isset($sped_row['NOMEGIORNO']) && $sped_row['NOMEGIORNO'] == 'DOMENICA') {
        continue;
    }
    // Alterna il colore di sfondo delle righe
    $bg_color = ($i % 2 == 0) ? '#FFFFFF' : '#F0F0F0';

    $html .= '<tr style="background-color: ' . $bg_color . ';">';
    $html .= '<td width="20" align="center">' . ($prod_row['GIORNO'] ?? '') . '</td>';
    $html .= '<td width="60">' . ($prod_row['NOMEGIORNO'] ?? '') . '</td>';

    // Produzione
    for ($j = 0; $j < 9; $j++) {
        $key = ['MANOVIA1', 'MANOVIA2', 'MANOVIA3', 'ORLATURA1', 'ORLATURA2', 'ORLATURA3', 'ORLATURA4', 'TAGLIO1', 'TAGLIO2'][$j];
        $value = $prod_row[$key] ?? '';
        $html .= '<td width="38" align="center">' . $value . '</td>';
        $totals[$j] += (float) $value;
    }

    // Spedizione
    for ($j = 9; $j < 18; $j++) {
        $key = ['MANOVIA1', 'MANOVIA1RESO', 'MANOVIA2', 'MANOVIA3', 'ORLATURA1', 'ORLATURA2', 'ORLATURA3', 'ORLATURA4', 'TOMESTERO'][$j - 9];
        $value = $sped_row[$key] ?? '';
        $html .= '<td width="38" align="center">' . $value . '</td>';
        $totals[$j] += (float) $value;
    }
    $html .= '</tr>';
}

// Righe Totali
$html .= '<tr style="background-color: #D3E2F4; text-align: center;">';
$html .= '<td colspan="2"><b>TOTALE</b></td>';

for ($j = 0; $j < 9; $j++) {
    $html .= '<td><b>' . $totals[$j] . '</b></td>';
}
for ($j = 9; $j < 18; $j++) {
    $html .= '<td style="background-color:#FFE78F;"><b>' . $totals[$j] . '</b></td>';
}

$html .= '</tr>';
$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');

// Chiudi e salva il PDF
$pdf->Output('report_produzione_spedizione_' . $month . '.pdf', 'I');
?>