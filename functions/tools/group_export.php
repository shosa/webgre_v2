<?php
require_once('../../config/config.php');
require_once(BASE_PATH . '/assets/tcpdf/tcpdf.php');
require_once(BASE_PATH . '/assets/tcpdf/tcpdf_barcodes_1d.php');
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="gruppi.pdf"');

// Function to create PDF for all groups
function createAllGroupsPDF()
{
    $pdf = new TCPDF();
    $pdf->SetAutoPageBreak(false);
    $pdf->SetMargins(5, 5, 5, true);
    // Imposta la pagina in orizzontale
    $pdf->setPageOrientation('L');  // 'L' sta per Landscape (orizzontale)

    // Imposta il font per le intestazioni
    $pdf->SetFont('helvetica', 'B', 10);

    // Fetch all distinct groups
    $db = getDbInstance();
    $groups = $db->groupBy('Gruppo')->get('temp_dati_gruppi', null, ['Gruppo']);
    $db->join('id_numerate', 'temp_dati_gruppi.nu = id_numerate.ID', 'LEFT');
    $rowstotali = $db->get('temp_dati_gruppi');
    $totale = 0;
    foreach ($rowstotali as $row) {
        $totale += $row['qta'];
    }
    foreach ($groups as $group) {
        // Fetch data for the specific group
        $db->join('id_numerate', 'temp_dati_gruppi.nu = id_numerate.ID', 'LEFT');
        $db->where('Gruppo', $group['Gruppo']);
        $rows = $db->get('temp_dati_gruppi');

        // Fetch header data from id_numerate for the specific group
        $db->where('ID', $rows[0]['nu']);
        $headerData = $db->get('id_numerate', null, ['ID', 'N01', 'N02', 'N03', 'N04', 'N05', 'N06', 'N07', 'N08', 'N09', 'N10', 'N11', 'N12', 'N13', 'N14', 'N15', 'N16', 'N17', 'N18', 'N19', 'N20']);


        // Add header
        $html = '<table border="0">';
        $html .= '<tr>';
        $html .= '<td colspan="3" style="font-size:15pt; text-align:center; vertical-align: top;background-color:black;color:white; border: 1px solid black;"><b>BOLLA DI LAVORAZIONE</b></td>';
        $html .= '<td style="font-size:15pt; text-align:center; vertical-align: top; border: 1px solid black;"><b>CEDOLA N°</b></td>';
        $html .= '<td style="text-align:center;font-size:15pt;background-color:black;color:white;border: 1px solid black;"><b>' . $rows[0]['lancio'] . '-' . $group['Gruppo'] . '</b></td>';
        $html .= '</tr>';
        $html .= '</table>';
        $html .= '<div style="height:5px;"></div>';
        $html .= '<table border="0" cellpadding="3">';
        $html .= '<tr>';
        $html .= '<td style="font-size:15pt; text-align:center; border: 1px solid black;"><b>LANCIO</b></td>';
        $html .= '<td style="text-align:center; font-size:15pt;background-color:black;color:white; border: 1px solid black;"><b>#' . $rows[0]['lancio'] . '</b></td>';
        $html .= '<td></td>';
        $html .= '<td width="30" style="font-size:15pt; text-align:center; border: 1px solid black;"><b>DI</b></td>';
        $html .= '<td style="text-align:center; font-size:15pt;background-color:black;color:white; border: 1px solid black;"><b>' . $totale . '</b></td>';
        $html .= '<td style="font-size:15pt; text-align:center; border: 1px solid black;"><b>PAIA</b></td>';
        $html .= '<td ></td><td></td>';
        $html .= '<td width="133" style="font-size:15pt; text-align:center; border: 1px solid black;"><b>GRUPPO N°</b></td>';
        $html .= '<td style="font-size:15pt; text-align:center;background-color:black;color:white;border: 1px solid black;"><b>' . $group['Gruppo'] . '</b></td>';
        $html .= '</tr>';
        $html .= '</table>';
        // Aggiungi uno spazio
        $html .= '<div style="height:20px;"></div>';

        $html .= '<table border="1" cellpadding="3">';
        $html .= '<tr>';
        $html .= '<td style="font-size:15pt; text-align:center; vertical-align: top;"><b>ARTICOLO:</b></td>';
        $html .= '<td colspan="3" style="font-size:15pt;text-align:center;"><b>' . $rows[0]['descrizione'] . '</b></td>';
        $html .= '</tr>';

        $html .= '</table>';

        $html .= '<table border="1">';
        $html .= '<tr style="background-color:#d9d9d9; color:black;">';
        $html .= '<th width="80" style="text-align:center; vertical-align: middle;"><b>CARTELLINO</b></th>';
        $html .= '<th width="103.5" style="text-align:center; vertical-align: middle;"><b>COMMESSA</b></th>';

        // Add headers from id_numerate
        foreach ($headerData as $header) {
            for ($i = 1; $i <= 20; $i++) {
                $campo = 'N' . sprintf('%02d', $i);
                $html .= '<th width="30" style="text-align:center; vertical-align: middle;"><b>' . $header[$campo] . '</b></th>';
            }
            break; // Break after adding headers once
        }

        $html .= '<th width="30" style="text-align:center; vertical-align: middle;"><b>QTA</b></th></tr>';
        $pdf->SetFont('helvetica', 'N', 11);

        // Add data rows
        $totalQta = 0;
        $totalP = array_fill(1, 20, 0);

        for ($rowIndex = 1; $rowIndex <= 27; $rowIndex++) {
            // Fetch data for the specific row
            $row = isset($rows[$rowIndex - 1]) ? $rows[$rowIndex - 1] : [];

            // Alterna i colori delle righe
            $rowColor = $rowIndex % 2 == 0 ? 'background-color:#fff;' : 'background-color:#f7f7f7;';
            $html .= '<tr style="' . $rowColor . ';font-size:10pt;">';
            $html .= '<td style="height:13px;text-align:center; vertical-align: middle;"><i>' . ($row['cartellino'] ?? '') . '</i></td>';
            $html .= '<td style="text-align:center; vertical-align: middle;"><i>' . ($row['commessa'] ?? '') . '</i></td>';

            // Add data dynamically
            for ($i = 1; $i <= 20; $i++) {
                $campo = 'P' . sprintf('%02d', $i);
                $cellValue = isset($row[$campo]) && $row[$campo] != 0 ? $row[$campo] : '';
                $html .= '<td style="text-align:center; vertical-align: middle;">' . $cellValue . '</td>';
                $totalP[$i] += $row[$campo] ?? 0;
            }

            $totalQta += $row['qta'] ?? 0;

            $html .= '<td style="text-align:center; vertical-align: middle;">' . ($row['qta'] ?? '') . '</td>';
            $html .= '</tr>';
        }


        $html .= '<tr style="background-color:#ffffff; color:#333;">';
        $html .= '<td colspan="2" style="background-color:black; color:#fffff;font-size:15pt; text-align:center; vertical-align: top;"><b></b></td>';
        $html .= '<td colspan="21" style="font-size:15pt;text-align:center;"><b>' . $rows[0]['descrizione'] . '</b></td>';
        $html .= '</tr>';

        $html .= '<tr style="background-color:#ffffff; color:#black">';
        $html .= '<td colspan="2"> </td>';
        $html .= '<td colspan="21" style="font-size:15pt;text-align:center;">';
        $html .= '<div style="font-size:40pt;color:white;">Contenuto della cella</div>'; // Sostituisci 30px con l'altezza desiderata
        $html .= '</td>';
        $html .= '</tr>';





        $html .= '<tr style="background-color:#d9d9d9; color:black;">';
        $html .= '<td colspan="2" style="text-align:center; vertical-align: middle;"><span style="font-weight:bold;font-family: helvetica; font-size: 10px;"></span></td>';

        for ($i = 1; $i <= 20; $i++) {
            for ($i = 1; $i <= 20; $i++) {
                $campo = 'N' . sprintf('%02d', $i);
                $html .= '<th width="30" style="text-align:center;font-weight:bold;font-family: helvetica; font-size: 10px;"><b>' . $header[$campo] . '</b></th>';
            }
            break; // Break after adding headers once
        }

        // Utilizza un tag span anche per la cella QTA
        $html .= '<td style="text-align:center; vertical-align: middle;"><span style="font-weight:bold;font-family: helvetica; font-size: 10px;">TOT</span></td>';
        $html .= '</tr>';

        $html .= '<tr style="background-color:#ffffff; color:#333;">';
        $html .= '<td colspan="2" style="text-align:center; vertical-align: middle;"><span style="font-weight:bold;font-family: helvetica; font-size: 14px;"></span></td>';

        for ($i = 1; $i <= 20; $i++) {
            // Utilizza un tag span per impostare il font specifico per questa cella
            $cellValue = ($totalP[$i] != 0) ? $totalP[$i] : " ";
            $html .= '<td style="text-align:center; vertical-align: middle;"><span style="font-family: helvetica; font-size: 14px;">' . $cellValue . '</span></td>';
        }

        // Utilizza un tag span anche per la cella QTA
        $html .= '<td style="text-align:center; vertical-align: middle;"><span style="font-family: helvetica; font-size: 14px;">' . $totalQta . '</span></td>';
        $html .= '</tr>';

        // Ripristina il font
        $pdf->SetFont('helvetica', 'N', 11);

        $html .= '</table>';
        $pdf->AddPage();
        $pdf->SetFillColor(255, 255, 255);

        $pdf->Rect(5.4, $pdf->getPageHeight() - 35.8, 64.1, 28, 'F');
        $imagePath = BASE_PATH . $rows[0]['path_to_img'];
        $pdf->Image($imagePath, 6.4, $pdf->getPageHeight() - 34.8, 59.1, 25, '', '', '', false, 300, '', false, false, 0);
        // Output HTML content

        $pdf->writeHTML($html, true, false, true, false, '');

    }

    // Output PDF content
    $pdf->Output('GRUPPI LANCIO ' . $rows[0]['lancio'], 'I');
}

// Create PDF for all groups
createAllGroupsPDF();
?>