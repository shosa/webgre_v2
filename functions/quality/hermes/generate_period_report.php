<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/utils/helpers.php';
require_once BASE_PATH . '/vendor/autoload.php';

// Verifica che i parametri necessari siano presenti
if (!isset($_POST['startDate']) || !isset($_POST['endDate']) || !isset($_POST['periodReportType']) || !isset($_POST['periodReportFormat'])) {
    die("Parametri mancanti per la generazione del report");
}

$startDate = $_POST['startDate'];
$endDate = $_POST['endDate'];
$reportType = $_POST['periodReportType'];
$reportFormat = $_POST['periodReportFormat'];

$pdo = getDbInstance();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Formatta le date per le query e il titolo
$formattedStartDate = date('Y-m-d', strtotime($startDate));
$formattedEndDate = date('Y-m-d', strtotime($endDate));
$displayStartDate = date('d/m/Y', strtotime($startDate));
$displayEndDate = date('d/m/Y', strtotime($endDate));

// Imposta il titolo del report in base al tipo
switch ($reportType) {
    case 'summary':
        $reportTitle = "Riepilogo Periodo CQ Hermes - Dal $displayStartDate al $displayEndDate";
        break;
    case 'byDepartment':
        $reportTitle = "Analisi per Reparto CQ Hermes - Dal $displayStartDate al $displayEndDate";
        break;
    case 'byDefect':
        $reportTitle = "Analisi per Tipo Difetto CQ Hermes - Dal $displayStartDate al $displayEndDate";
        break;
    case 'byOperator':
        $reportTitle = "Analisi per Operatore CQ Hermes - Dal $displayStartDate al $displayEndDate";
        break;
    default:
        $reportTitle = "Report Periodo CQ Hermes - Dal $displayStartDate al $displayEndDate";
}

// Ottieni i dati per il report in base al tipo
$statisticheGenerali = [];
$statistichePerGiorno = [];
$statistichePerReparto = [];
$statistichePerDifetto = [];
$statistichePerOperatore = [];

// Statistiche generali per tutti i tipi di report
$stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT r.id) as totale_cartellini,
        SUM(r.paia_totali) as totale_paia,
        COUNT(DISTINCT CASE WHEN r.ha_eccezioni = 1 THEN r.id END) as cartellini_con_eccezioni,
        COUNT(e.id) as totale_eccezioni,
        ROUND((COUNT(DISTINCT CASE WHEN r.ha_eccezioni = 1 THEN r.id END) / COUNT(DISTINCT r.id)) * 100, 2) as percentuale_cartellini_eccezioni
    FROM cq_hermes_records r
    LEFT JOIN cq_hermes_eccezioni e ON r.id = e.cartellino_id
    WHERE DATE(r.data_controllo) BETWEEN :start_date AND :end_date
");
$stmt->execute(['start_date' => $formattedStartDate, 'end_date' => $formattedEndDate]);
$statisticheGenerali = $stmt->fetch(PDO::FETCH_ASSOC);

// Statistiche per giorno (per il riepilogo periodo)
if ($reportType == 'summary') {
    $stmt = $pdo->prepare("
        SELECT 
            DATE(r.data_controllo) as giorno,
            COUNT(DISTINCT r.id) as cartellini,
            SUM(r.paia_totali) as paia,
            COUNT(DISTINCT CASE WHEN r.ha_eccezioni = 1 THEN r.id END) as cartellini_eccezioni,
            COUNT(e.id) as eccezioni
        FROM cq_hermes_records r
        LEFT JOIN cq_hermes_eccezioni e ON r.id = e.cartellino_id
        WHERE DATE(r.data_controllo) BETWEEN :start_date AND :end_date
        GROUP BY DATE(r.data_controllo)
        ORDER BY DATE(r.data_controllo)
    ");
    $stmt->execute(['start_date' => $formattedStartDate, 'end_date' => $formattedEndDate]);
    $statistichePerGiorno = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Statistiche per reparto
if ($reportType == 'summary' || $reportType == 'byDepartment') {
    $stmt = $pdo->prepare("
        SELECT 
            r.reparto,
            COUNT(DISTINCT r.id) as cartellini,
            SUM(r.paia_totali) as paia,
            COUNT(DISTINCT CASE WHEN r.ha_eccezioni = 1 THEN r.id END) as cartellini_eccezioni,
            COUNT(e.id) as eccezioni,
            ROUND((COUNT(e.id) / SUM(r.paia_totali)) * 100, 2) as percentuale_eccezioni_paia,
            ROUND((COUNT(DISTINCT CASE WHEN r.ha_eccezioni = 1 THEN r.id END) / COUNT(DISTINCT r.id)) * 100, 2) as percentuale_cartellini_eccezioni
        FROM cq_hermes_records r
        LEFT JOIN cq_hermes_eccezioni e ON r.id = e.cartellino_id
        WHERE DATE(r.data_controllo) BETWEEN :start_date AND :end_date
        GROUP BY r.reparto
        ORDER BY 
            CASE 
                WHEN :report_type = 'byDepartment' THEN COUNT(e.id)
                ELSE r.reparto
            END DESC
    ");
    $stmt->execute(['start_date' => $formattedStartDate, 'end_date' => $formattedEndDate, 'report_type' => $reportType]);
    $statistichePerReparto = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Se il report è per reparto, ottieni anche i dettagli dei giorni per ogni reparto
    if ($reportType == 'byDepartment') {
        $statisticheRepartoPerGiorno = [];

        $stmt = $pdo->prepare("
            SELECT 
                r.reparto,
                DATE(r.data_controllo) as giorno,
                COUNT(DISTINCT r.id) as cartellini,
                SUM(r.paia_totali) as paia,
                COUNT(DISTINCT CASE WHEN r.ha_eccezioni = 1 THEN r.id END) as cartellini_eccezioni,
                COUNT(e.id) as eccezioni
            FROM cq_hermes_records r
            LEFT JOIN cq_hermes_eccezioni e ON r.id = e.cartellino_id
            WHERE DATE(r.data_controllo) BETWEEN :start_date AND :end_date
            GROUP BY r.reparto, DATE(r.data_controllo)
            ORDER BY r.reparto, DATE(r.data_controllo)
        ");
        $stmt->execute(['start_date' => $formattedStartDate, 'end_date' => $formattedEndDate]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Organizza i dati per reparto
        foreach ($result as $row) {
            if (!isset($statisticheRepartoPerGiorno[$row['reparto']])) {
                $statisticheRepartoPerGiorno[$row['reparto']] = [];
            }
            $statisticheRepartoPerGiorno[$row['reparto']][] = $row;
        }
    }
}

// Statistiche per tipo di difetto
if ($reportType == 'summary' || $reportType == 'byDefect') {
    $stmt = $pdo->prepare("
        SELECT 
            e.tipo_difetto,
            COUNT(e.id) as occorrenze,
            COUNT(DISTINCT r.id) as cartellini,
            COUNT(DISTINCT r.reparto) as reparti
        FROM cq_hermes_eccezioni e
        INNER JOIN cq_hermes_records r ON e.cartellino_id = r.id
        WHERE DATE(r.data_controllo) BETWEEN :start_date AND :end_date
        GROUP BY e.tipo_difetto
        ORDER BY 
            CASE 
                WHEN :report_type = 'byDefect' THEN COUNT(e.id)
                ELSE e.tipo_difetto
            END DESC
    ");
    $stmt->execute(['start_date' => $formattedStartDate, 'end_date' => $formattedEndDate, 'report_type' => $reportType]);
    $statistichePerDifetto = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Se il report è per tipo di difetto, ottieni anche i dettagli dei reparti per ogni tipo di difetto
    if ($reportType == 'byDefect') {
        $statisticheDifettoPerReparto = [];

        $stmt = $pdo->prepare("
            SELECT 
                e.tipo_difetto,
                r.reparto,
                COUNT(e.id) as occorrenze,
                COUNT(DISTINCT r.id) as cartellini
            FROM cq_hermes_eccezioni e
            INNER JOIN cq_hermes_records r ON e.cartellino_id = r.id
            WHERE DATE(r.data_controllo) BETWEEN :start_date AND :end_date
            GROUP BY e.tipo_difetto, r.reparto
            ORDER BY e.tipo_difetto, COUNT(e.id) DESC
        ");
        $stmt->execute(['start_date' => $formattedStartDate, 'end_date' => $formattedEndDate]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Organizza i dati per tipo di difetto
        foreach ($result as $row) {
            if (!isset($statisticheDifettoPerReparto[$row['tipo_difetto']])) {
                $statisticheDifettoPerReparto[$row['tipo_difetto']] = [];
            }
            $statisticheDifettoPerReparto[$row['tipo_difetto']][] = $row;
        }
    }
}

// Statistiche per operatore
if ($reportType == 'summary' || $reportType == 'byOperator') {
    $stmt = $pdo->prepare("
        SELECT 
            r.operatore,
            COUNT(DISTINCT r.id) as cartellini,
            SUM(r.paia_totali) as paia,
            COUNT(DISTINCT CASE WHEN r.ha_eccezioni = 1 THEN r.id END) as cartellini_eccezioni,
            COUNT(e.id) as eccezioni,
            ROUND((COUNT(e.id) / SUM(r.paia_totali)) * 100, 2) as percentuale_eccezioni_paia,
            ROUND((COUNT(DISTINCT CASE WHEN r.ha_eccezioni = 1 THEN r.id END) / COUNT(DISTINCT r.id)) * 100, 2) as percentuale_cartellini_eccezioni
        FROM cq_hermes_records r
        LEFT JOIN cq_hermes_eccezioni e ON r.id = e.cartellino_id
        WHERE DATE(r.data_controllo) BETWEEN :start_date AND :end_date
        GROUP BY r.operatore
        ORDER BY 
            CASE 
                WHEN :report_type = 'byOperator' THEN COUNT(e.id)
                ELSE r.operatore
            END DESC
    ");
    $stmt->execute(['start_date' => $formattedStartDate, 'end_date' => $formattedEndDate, 'report_type' => $reportType]);
    $statistichePerOperatore = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Se il report è per operatore, ottieni anche i dettagli dei tipi di difetto per ogni operatore
    if ($reportType == 'byOperator') {
        $statisticheOperatorePerDifetto = [];

        $stmt = $pdo->prepare("
            SELECT 
                r.operatore,
                e.tipo_difetto,
                COUNT(e.id) as occorrenze
            FROM cq_hermes_eccezioni e
            INNER JOIN cq_hermes_records r ON e.cartellino_id = r.id
            WHERE DATE(r.data_controllo) BETWEEN :start_date AND :end_date
            GROUP BY r.operatore, e.tipo_difetto
            ORDER BY r.operatore, COUNT(e.id) DESC
        ");
        $stmt->execute(['start_date' => $formattedStartDate, 'end_date' => $formattedEndDate]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Organizza i dati per operatore
        foreach ($result as $row) {
            if (!isset($statisticheOperatorePerDifetto[$row['operatore']])) {
                $statisticheOperatorePerDifetto[$row['operatore']] = [];
            }
            $statisticheOperatorePerDifetto[$row['operatore']][] = $row;
        }
    }
}

// Genera il report in base al formato richiesto
if ($reportFormat == 'pdf') {
    // Configurazione dell'istanza mPDF
    $mpdf = new \Mpdf\Mpdf([
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 15,
        'margin_bottom' => 15
    ]);

    // Imposta il titolo del documento
    $mpdf->SetTitle($reportTitle);

    // Inizia il buffer di output
    ob_start();

    // Stile CSS per il report
    echo '
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
        }
        h1 {
            font-size: 16pt;
            color: #4e73df;
            text-align: center;
            margin-bottom: 20px;
        }
        h2 {
            font-size: 14pt;
            color: #1cc88a;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        h3 {
            font-size: 12pt;
            color: #36b9cc;
            margin-top: 15px;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #4e73df;
            color: white;
            font-weight: bold;
            text-align: left;
            padding: 5px;
        }
        td {
            border: 1px solid #ddd;
            padding: 5px;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .summary-box {
            background-color: #f8f9fc;
            border: 1px solid #e3e6f0;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
        }
        .summary-item {
            margin-bottom: 5px;
        }
        .summary-label {
            font-weight: bold;
            color: #5a5c69;
        }
        .footer {
            text-align: center;
            font-size: 8pt;
            color: #858796;
            margin-top: 20px;
        }
        .exception {
            background-color: #ffecec;
        }
        .section-break {
            page-break-before: always;
        }
    </style>
    ';

    // Intestazione con logo e titolo
    echo '<div style="text-align: center; margin-bottom: 20px;">
            <img src="../../img/logo.png" style="max-height: 50px;" />
            <h1>' . $reportTitle . '</h1>
          </div>';

    // Riepilogo generale per tutti i tipi di report
    echo '<div class="summary-box">
            <h2>Riepilogo Generale</h2>
            <div class="summary-item"><span class="summary-label">Periodo:</span> ' . $displayStartDate . ' - ' . $displayEndDate . '</div>
            <div class="summary-item"><span class="summary-label">Cartellini Totali:</span> ' . $statisticheGenerali['totale_cartellini'] . '</div>
            <div class="summary-item"><span class="summary-label">Paia Totali:</span> ' . $statisticheGenerali['totale_paia'] . '</div>
            <div class="summary-item"><span class="summary-label">Cartellini con Eccezioni:</span> ' . $statisticheGenerali['cartellini_con_eccezioni'] . ' (' . $statisticheGenerali['percentuale_cartellini_eccezioni'] . '%)</div>
            <div class="summary-item"><span class="summary-label">Eccezioni Totali:</span> ' . $statisticheGenerali['totale_eccezioni'] . '</div>
          </div>';

    // Contenuto specifico in base al tipo di report
    switch ($reportType) {
        case 'summary':
            // Statistiche per giorno
            echo '<h2>Statistiche per Giorno</h2>';
            echo '<table>
                    <tr>
                        <th>Giorno</th>
                        <th>Cartellini</th>
                        <th>Paia</th>
                        <th>Cartellini con Eccezioni</th>
                        <th>Eccezioni</th>
                    </tr>';

            foreach ($statistichePerGiorno as $giorno) {
                echo '<tr>
                        <td>' . date('d/m/Y', strtotime($giorno['giorno'])) . '</td>
                        <td>' . $giorno['cartellini'] . '</td>
                        <td>' . $giorno['paia'] . '</td>
                        <td>' . $giorno['cartellini_eccezioni'] . '</td>
                        <td>' . $giorno['eccezioni'] . '</td>
                      </tr>';
            }

            echo '</table>';

            // Statistiche per reparto
            echo '<h2>Statistiche per Reparto</h2>';
            echo '<table>
                    <tr>
                        <th>Reparto</th>
                        <th>Cartellini</th>
                        <th>Paia</th>
                        <th>Cartellini con Eccezioni</th>
                        <th>Eccezioni</th>
                        <th>% Eccezioni/Paia</th>
                    </tr>';

            foreach ($statistichePerReparto as $reparto) {
                echo '<tr>
                        <td>' . htmlspecialchars($reparto['reparto']) . '</td>
                        <td>' . $reparto['cartellini'] . '</td>
                        <td>' . $reparto['paia'] . '</td>
                        <td>' . $reparto['cartellini_eccezioni'] . '</td>
                        <td>' . $reparto['eccezioni'] . '</td>
                        <td>' . $reparto['percentuale_eccezioni_paia'] . '%</td>
                        <td>' . $reparto['percentuale_cartellini_eccezioni'] . '%</td>
                      </tr>';
            }

            echo '</table>';

            // Dettaglio giornaliero per ogni reparto
            foreach ($statistichePerReparto as $reparto) {
                echo '<div class="section-break"></div>';
                echo '<h3>Dettaglio per "' . htmlspecialchars($reparto['reparto']) . '"</h3>';

                // Mostra solo se ci sono dati per questo reparto
                if (isset($statisticheRepartoPerGiorno[$reparto['reparto']])) {
                    echo '<table>
                            <tr>
                                <th>Giorno</th>
                                <th>Cartellini</th>
                                <th>Paia</th>
                                <th>Cartellini con Eccezioni</th>
                                <th>Eccezioni</th>
                            </tr>';

                    foreach ($statisticheRepartoPerGiorno[$reparto['reparto']] as $giorno) {
                        echo '<tr>
                                <td>' . date('d/m/Y', strtotime($giorno['giorno'])) . '</td>
                                <td>' . $giorno['cartellini'] . '</td>
                                <td>' . $giorno['paia'] . '</td>
                                <td>' . $giorno['cartellini_eccezioni'] . '</td>
                                <td>' . $giorno['eccezioni'] . '</td>
                              </tr>';
                    }

                    echo '</table>';
                } else {
                    echo '<p>Nessun dato disponibile per questo reparto.</p>';
                }
            }
            break;

        case 'byDefect':
            // Statistiche per tipo di difetto
            echo '<h2>Analisi per Tipo di Difetto</h2>';
            echo '<table>
                    <tr>
                        <th>Tipo Difetto</th>
                        <th>Occorrenze</th>
                        <th>Cartellini</th>
                        <th>Reparti</th>
                    </tr>';

            foreach ($statistichePerDifetto as $difetto) {
                echo '<tr>
                        <td>' . htmlspecialchars($difetto['tipo_difetto']) . '</td>
                        <td>' . $difetto['occorrenze'] . '</td>
                        <td>' . $difetto['cartellini'] . '</td>
                        <td>' . $difetto['reparti'] . '</td>
                      </tr>';
            }

            echo '</table>';

            // Dettaglio per reparto per ogni tipo di difetto
            foreach ($statistichePerDifetto as $difetto) {
                echo '<div class="section-break"></div>';
                echo '<h3>Dettaglio per "' . htmlspecialchars($difetto['tipo_difetto']) . '"</h3>';

                // Mostra solo se ci sono dati per questo tipo di difetto
                if (isset($statisticheDifettoPerReparto[$difetto['tipo_difetto']])) {
                    echo '<table>
                            <tr>
                                <th>Reparto</th>
                                <th>Occorrenze</th>
                                <th>Cartellini</th>
                            </tr>';

                    foreach ($statisticheDifettoPerReparto[$difetto['tipo_difetto']] as $reparto) {
                        echo '<tr>
                                <td>' . htmlspecialchars($reparto['reparto']) . '</td>
                                <td>' . $reparto['occorrenze'] . '</td>
                                <td>' . $reparto['cartellini'] . '</td>
                              </tr>';
                    }

                    echo '</table>';
                } else {
                    echo '<p>Nessun dato disponibile per questo tipo di difetto.</p>';
                }
            }
            break;

        case 'byOperator':
            // Statistiche per operatore
            echo '<h2>Analisi per Operatore</h2>';
            echo '<table>
                    <tr>
                        <th>Operatore</th>
                        <th>Cartellini</th>
                        <th>Paia</th>
                        <th>Cartellini con Eccezioni</th>
                        <th>Eccezioni</th>
                        <th>% Eccezioni/Paia</th>
                        <th>% Cart. con Eccezioni</th>
                    </tr>';

            foreach ($statistichePerOperatore as $operatore) {
                echo '<tr>
                        <td>' . htmlspecialchars($operatore['operatore']) . '</td>
                        <td>' . $operatore['cartellini'] . '</td>
                        <td>' . $operatore['paia'] . '</td>
                        <td>' . $operatore['cartellini_eccezioni'] . '</td>
                        <td>' . $operatore['eccezioni'] . '</td>
                        <td>' . $operatore['percentuale_eccezioni_paia'] . '%</td>
                        <td>' . $operatore['percentuale_cartellini_eccezioni'] . '%</td>
                      </tr>';
            }

            echo '</table>';

            // Dettaglio per tipo di difetto per ogni operatore
            foreach ($statistichePerOperatore as $operatore) {
                // Salta gli operatori senza eccezioni
                if ($operatore['eccezioni'] == 0) {
                    continue;
                }

                echo '<div class="section-break"></div>';
                echo '<h3>Dettaglio per "' . htmlspecialchars($operatore['operatore']) . '"</h3>';

                // Mostra solo se ci sono dati per questo operatore
                if (isset($statisticheOperatorePerDifetto[$operatore['operatore']])) {
                    echo '<table>
                            <tr>
                                <th>Tipo Difetto</th>
                                <th>Occorrenze</th>
                            </tr>';

                    foreach ($statisticheOperatorePerDifetto[$operatore['operatore']] as $difetto) {
                        echo '<tr>
                                <td>' . htmlspecialchars($difetto['tipo_difetto']) . '</td>
                                <td>' . $difetto['occorrenze'] . '</td>
                              </tr>';
                    }

                    echo '</table>';
                } else {
                    echo '<p>Nessun dato disponibile per questo operatore.</p>';
                }
            }
            break;
    }

    // Footer
    echo '<div class="footer">
            Report generato il ' . date('d/m/Y H:i:s') . ' da ' . htmlspecialchars($_SESSION['username']) .
        '</div>';

    // Ottieni il contenuto dal buffer e svuotalo
    $html = ob_get_clean();

    // Scrivi l'HTML nel PDF
    $mpdf->WriteHTML($html);

    // Genera un nome file per il download
    $filename = "CQ_Hermes_Periodo_" . date('Ymd', strtotime($startDate)) . "-" . date('Ymd', strtotime($endDate)) . "_" . $reportType . ".pdf";

    // Output del PDF
    $mpdf->Output($filename, 'D');
    exit;
} elseif ($reportFormat == 'excel') {
    // Imposta le intestazioni per il download Excel
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="CQ_Hermes_Periodo_' . date('Ymd', strtotime($startDate)) . '-' . date('Ymd', strtotime($endDate)) . '_' . $reportType . '.xls"');
    header('Cache-Control: max-age=0');

    // Apri l'output buffer
    ob_start();

    // Crea il documento HTML che Excel interpreterà
    echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . $reportTitle . '</title>
            <style>
                table {
                    border-collapse: collapse;
                    width: 100%;
                }
                th, td {
                    border: 1px solid #000000;
                    padding: 5px;
                    text-align: left;
                }
                th {
                    background-color: #4e73df;
                    color: #ffffff;
                }
                .exception {
                    background-color: #ffecec;
                }
            </style>
        </head>
        <body>';

    // Titolo del report
    echo '<h1>' . $reportTitle . '</h1>';

    // Riepilogo generale per tutti i tipi di report
    echo '<h2>Riepilogo Generale</h2>';
    echo '<table>
            <tr>
                <th>Cartellini Totali</th>
                <th>Paia Totali</th>
                <th>Cartellini con Eccezioni</th>
                <th>% Cartellini con Eccezioni</th>
                <th>Eccezioni Totali</th>
            </tr>
            <tr>
                <td>' . $statisticheGenerali['totale_cartellini'] . '</td>
                <td>' . $statisticheGenerali['totale_paia'] . '</td>
                <td>' . $statisticheGenerali['cartellini_con_eccezioni'] . '</td>
                <td>' . $statisticheGenerali['percentuale_cartellini_eccezioni'] . '%</td>
                <td>' . $statisticheGenerali['totale_eccezioni'] . '</td>
            </tr>
          </table>';

    // Contenuto specifico in base al tipo di report
    switch ($reportType) {
        case 'summary':
            // Statistiche per giorno
            echo '<h2>Statistiche per Giorno</h2>';
            echo '<table>
                    <tr>
                        <th>Giorno</th>
                        <th>Cartellini</th>
                        <th>Paia</th>
                        <th>Cartellini con Eccezioni</th>
                        <th>Eccezioni</th>
                    </tr>';

            foreach ($statistichePerGiorno as $giorno) {
                echo '<tr>
                        <td>' . date('d/m/Y', strtotime($giorno['giorno'])) . '</td>
                        <td>' . $giorno['cartellini'] . '</td>
                        <td>' . $giorno['paia'] . '</td>
                        <td>' . $giorno['cartellini_eccezioni'] . '</td>
                        <td>' . $giorno['eccezioni'] . '</td>
                      </tr>';
            }

            echo '</table>';

            // Statistiche per reparto
            echo '<h2>Statistiche per Reparto</h2>';
            echo '<table>
                    <tr>
                        <th>Reparto</th>
                        <th>Cartellini</th>
                        <th>Paia</th>
                        <th>Cartellini con Eccezioni</th>
                        <th>Eccezioni</th>
                        <th>% Eccezioni/Paia</th>
                    </tr>';

            foreach ($statistichePerReparto as $reparto) {
                echo '<tr>
                        <td>' . htmlspecialchars($reparto['reparto']) . '</td>
                        <td>' . $reparto['cartellini'] . '</td>
                        <td>' . $reparto['paia'] . '</td>
                        <td>' . $reparto['cartellini_eccezioni'] . '</td>
                        <td>' . $reparto['eccezioni'] . '</td>
                        <td>' . $reparto['percentuale_eccezioni_paia'] . '%</td>
                      </tr>';
            }

            echo '</table>';

            // Statistiche per tipo di difetto
            if (!empty($statistichePerDifetto)) {
                echo '<h2>Statistiche per Tipo di Difetto</h2>';
                echo '<table>
                        <tr>
                            <th>Tipo Difetto</th>
                            <th>Occorrenze</th>
                            <th>Cartellini</th>
                            <th>Reparti</th>
                        </tr>';

                foreach ($statistichePerDifetto as $difetto) {
                    echo '<tr>
                            <td>' . htmlspecialchars($difetto['tipo_difetto']) . '</td>
                            <td>' . $difetto['occorrenze'] . '</td>
                            <td>' . $difetto['cartellini'] . '</td>
                            <td>' . $difetto['reparti'] . '</td>
                          </tr>';
                }

                echo '</table>';
            }

            // Statistiche per operatore
            echo '<h2>Statistiche per Operatore</h2>';
            echo '<table>
                    <tr>
                        <th>Operatore</th>
                        <th>Cartellini</th>
                        <th>Paia</th>
                        <th>Cartellini con Eccezioni</th>
                        <th>Eccezioni</th>
                        <th>% Cart. con Eccezioni</th>
                    </tr>';

            foreach ($statistichePerOperatore as $operatore) {
                echo '<tr>
                        <td>' . htmlspecialchars($operatore['operatore']) . '</td>
                        <td>' . $operatore['cartellini'] . '</td>
                        <td>' . $operatore['paia'] . '</td>
                        <td>' . $operatore['cartellini_eccezioni'] . '</td>
                        <td>' . $operatore['eccezioni'] . '</td>
                        <td>' . $operatore['percentuale_cartellini_eccezioni'] . '%</td>
                      </tr>';
            }

            echo '</table>';
            break;

        case 'byDepartment':
            // Statistiche per reparto
            echo '<h2>Analisi per Reparto</h2>';
            echo '<table>
                    <tr>
                        <th>Reparto</th>
                        <th>Cartellini</th>
                        <th>Paia</th>
                        <th>Cartellini con Eccezioni</th>
                        <th>Eccezioni</th>
                        <th>% Eccezioni/Paia</th>
                        <th>% Cart. con Eccezioni</th>
                    </tr>';

            foreach ($statistichePerReparto as $reparto) {
                echo '<tr>
                        <td>' . htmlspecialchars($reparto['reparto']) . '</td>
                        <td>' . $reparto['cartellini'] . '</td>
                        <td>' . $reparto['paia'] . '</td>
                        <td>' . $reparto['cartellini_eccezioni'] . '</td>
                        <td>' . $reparto['eccezioni'] . '</td>
                        <td>' . $reparto['percentuale_eccezioni_paia'] . '%</td>
                        <td>' . $reparto['percentuale_cartellini_eccezioni'] . '%</td>
                      </tr>';
            }

            echo '</table>';

            // Dettaglio giornaliero per ogni reparto in un nuovo foglio
            foreach ($statistichePerReparto as $reparto) {
                echo '<h3>Dettaglio per "' . htmlspecialchars($reparto['reparto']) . '"</h3>';

                // Mostra solo se ci sono dati per questo reparto
                if (isset($statisticheRepartoPerGiorno[$reparto['reparto']])) {
                    echo '<table>
                            <tr>
                                <th>Giorno</th>
                                <th>Cartellini</th>
                                <th>Paia</th>
                                <th>Cartellini con Eccezioni</th>
                                <th>Eccezioni</th>
                            </tr>';

                    foreach ($statisticheRepartoPerGiorno[$reparto['reparto']] as $giorno) {
                        echo '<tr>
                                <td>' . date('d/m/Y', strtotime($giorno['giorno'])) . '</td>
                                <td>' . $giorno['cartellini'] . '</td>
                                <td>' . $giorno['paia'] . '</td>
                                <td>' . $giorno['cartellini_eccezioni'] . '</td>
                                <td>' . $giorno['eccezioni'] . '</td>
                              </tr>';
                    }

                    echo '</table>';
                } else {
                    echo '<p>Nessun dato disponibile per questo reparto.</p>';
                }
            }
            break;

        case 'byDefect':
            // Statistiche per tipo di difetto
            echo '<h2>Analisi per Tipo di Difetto</h2>';
            echo '<table>
                    <tr>
                        <th>Tipo Difetto</th>
                        <th>Occorrenze</th>
                        <th>Cartellini</th>
                        <th>Reparti</th>
                    </tr>';

            foreach ($statistichePerDifetto as $difetto) {
                echo '<tr>
                        <td>' . htmlspecialchars($difetto['tipo_difetto']) . '</td>
                        <td>' . $difetto['occorrenze'] . '</td>
                        <td>' . $difetto['cartellini'] . '</td>
                        <td>' . $difetto['reparti'] . '</td>
                      </tr>';
            }

            echo '</table>';

            // Dettaglio per reparto per ogni tipo di difetto
            foreach ($statistichePerDifetto as $difetto) {
                echo '<h3>Dettaglio per "' . htmlspecialchars($difetto['tipo_difetto']) . '"</h3>';

                // Mostra solo se ci sono dati per questo tipo di difetto
                if (isset($statisticheDifettoPerReparto[$difetto['tipo_difetto']])) {
                    echo '<table>
                            <tr>
                                <th>Reparto</th>
                                <th>Occorrenze</th>
                                <th>Cartellini</th>
                            </tr>';

                    foreach ($statisticheDifettoPerReparto[$difetto['tipo_difetto']] as $reparto) {
                        echo '<tr>
                                <td>' . htmlspecialchars($reparto['reparto']) . '</td>
                                <td>' . $reparto['occorrenze'] . '</td>
                                <td>' . $reparto['cartellini'] . '</td>
                              </tr>';
                    }

                    echo '</table>';
                } else {
                    echo '<p>Nessun dato disponibile per questo tipo di difetto.</p>';
                }
            }
            break;

        case 'byOperator':
            // Statistiche per operatore
            echo '<h2>Analisi per Operatore</h2>';
            echo '<table>
                    <tr>
                        <th>Operatore</th>
                        <th>Cartellini</th>
                        <th>Paia</th>
                        <th>Cartellini con Eccezioni</th>
                        <th>Eccezioni</th>
                        <th>% Eccezioni/Paia</th>
                        <th>% Cart. con Eccezioni</th>
                    </tr>';

            foreach ($statistichePerOperatore as $operatore) {
                echo '<tr>
                        <td>' . htmlspecialchars($operatore['operatore']) . '</td>
                        <td>' . $operatore['cartellini'] . '</td>
                        <td>' . $operatore['paia'] . '</td>
                        <td>' . $operatore['cartellini_eccezioni'] . '</td>
                        <td>' . $operatore['eccezioni'] . '</td>
                        <td>' . $operatore['percentuale_eccezioni_paia'] . '%</td>
                        <td>' . $operatore['percentuale_cartellini_eccezioni'] . '%</td>
                      </tr>';
            }

            echo '</table>';

            // Dettaglio per tipo di difetto per ogni operatore
            foreach ($statistichePerOperatore as $operatore) {
                // Salta gli operatori senza eccezioni
                if ($operatore['eccezioni'] == 0) {
                    continue;
                }

                echo '<h3>Dettaglio per "' . htmlspecialchars($operatore['operatore']) . '"</h3>';

                // Mostra solo se ci sono dati per questo operatore
                if (isset($statisticheOperatorePerDifetto[$operatore['operatore']])) {
                    echo '<table>
                            <tr>
                                <th>Tipo Difetto</th>
                                <th>Occorrenze</th>
                            </tr>';

                    foreach ($statisticheOperatorePerDifetto[$operatore['operatore']] as $difetto) {
                        echo '<tr>
                                <td>' . htmlspecialchars($difetto['tipo_difetto']) . '</td>
                                <td>' . $difetto['occorrenze'] . '</td>
                              </tr>';
                    }

                    echo '</table>';
                } else {
                    echo '<p>Nessun dato disponibile per questo operatore.</p>';
                }
            }
            break;
    }

    // Chiudi il documento HTML
    echo '</body></html>';

    // Invia l'output
    ob_end_flush();
    exit;
}