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
if (!isset($_POST['reportDate']) || !isset($_POST['reportType']) || !isset($_POST['reportFormat'])) {
    die("Parametri mancanti per la generazione del report");
}

$reportDate = $_POST['reportDate'];
$reportType = $_POST['reportType'];
$reportFormat = $_POST['reportFormat'];

$pdo = getDbInstance();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Formatta la data per le query e il titolo
$formattedDate = date('Y-m-d', strtotime($reportDate));
$displayDate = date('d/m/Y', strtotime($reportDate));

// Imposta il titolo del report in base al tipo
switch ($reportType) {
    case 'summary':
        $reportTitle = "Riepilogo Giornaliero CQ Hermes - $displayDate";
        break;
    case 'detailed':
        $reportTitle = "Dettaglio Completo CQ Hermes - $displayDate";
        break;
    case 'exceptions':
        $reportTitle = "Eccezioni CQ Hermes - $displayDate";
        break;
    default:
        $reportTitle = "Report CQ Hermes - $displayDate";
}

// Ottieni i dati per il report in base al tipo
$cartelliniData = [];
$eccezioniData = [];
$statisticheData = [];

// Query per recuperare i dati richiesti
if ($reportType == 'summary' || $reportType == 'detailed') {
    // Recupera tutti i cartellini per la data specificata
    $stmt = $pdo->prepare("
        SELECT r.*, COUNT(e.id) as num_eccezioni 
        FROM cq_hermes_records r
        LEFT JOIN cq_hermes_eccezioni e ON r.id = e.cartellino_id
        WHERE DATE(r.data_controllo) = :date
        GROUP BY r.id
        ORDER BY r.data_controllo DESC
    ");
    $stmt->execute(['date' => $formattedDate]);
    $cartelliniData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcola le statistiche generali
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT r.id) as totale_cartellini,
            SUM(r.paia_totali) as totale_paia,
            COUNT(DISTINCT CASE WHEN r.ha_eccezioni = 1 THEN r.id END) as cartellini_con_eccezioni,
            COUNT(e.id) as totale_eccezioni,
            ROUND((COUNT(DISTINCT CASE WHEN r.ha_eccezioni = 1 THEN r.id END) / COUNT(DISTINCT r.id)) * 100, 2) as percentuale_cartellini_eccezioni
        FROM cq_hermes_records r
        LEFT JOIN cq_hermes_eccezioni e ON r.id = e.cartellino_id
        WHERE DATE(r.data_controllo) = :date
    ");
    $stmt->execute(['date' => $formattedDate]);
    $statisticheData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Statistiche per reparto
    $stmt = $pdo->prepare("
        SELECT 
            r.reparto,
            COUNT(DISTINCT r.id) as cartellini,
            SUM(r.paia_totali) as paia,
            COUNT(DISTINCT CASE WHEN r.ha_eccezioni = 1 THEN r.id END) as cartellini_eccezioni,
            COUNT(e.id) as eccezioni
        FROM cq_hermes_records r
        LEFT JOIN cq_hermes_eccezioni e ON r.id = e.cartellino_id
        WHERE DATE(r.data_controllo) = :date
        GROUP BY r.reparto
        ORDER BY r.reparto
    ");
    $stmt->execute(['date' => $formattedDate]);
    $statistichePerReparto = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Statistiche per tipo di difetto
    $stmt = $pdo->prepare("
        SELECT 
            e.tipo_difetto,
            COUNT(e.id) as occorrenze
        FROM cq_hermes_eccezioni e
        INNER JOIN cq_hermes_records r ON e.cartellino_id = r.id
        WHERE DATE(r.data_controllo) = :date
        GROUP BY e.tipo_difetto
        ORDER BY occorrenze DESC
    ");
    $stmt->execute(['date' => $formattedDate]);
    $statistichePerDifetto = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($reportType == 'detailed' || $reportType == 'exceptions') {
    // Recupera tutte le eccezioni per la data specificata
    $stmt = $pdo->prepare("
        SELECT e.*, r.numero_cartellino, r.cod_articolo, r.articolo, r.reparto 
        FROM cq_hermes_eccezioni e
        INNER JOIN cq_hermes_records r ON e.cartellino_id = r.id
        WHERE DATE(r.data_controllo) = :date
        ORDER BY e.data_creazione DESC
    ");
    $stmt->execute(['date' => $formattedDate]);
    $eccezioniData = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    </style>
    ';

    // Intestazione con logo e titolo
    echo '<div style="text-align: center; margin-bottom: 20px;">
            <img src="../../../img/logo.png" style="max-height: 50px;" />
            <h1>' . $reportTitle . '</h1>
          </div>';

    // Contenuto del report
    if ($reportType == 'summary' || $reportType == 'detailed') {
        // Riepilogo generale
        echo '<div class="summary-box">
                <h2>Riepilogo</h2>
                <div class="summary-item"><span class="summary-label">Cartellini Totali:</span> ' . $statisticheData['totale_cartellini'] . '</div>
                <div class="summary-item"><span class="summary-label">Paia Totali:</span> ' . $statisticheData['totale_paia'] . '</div>
                <div class="summary-item"><span class="summary-label">Cartellini con Eccezioni:</span> ' . $statisticheData['cartellini_con_eccezioni'] . ' (' . $statisticheData['percentuale_cartellini_eccezioni'] . '%)</div>
                <div class="summary-item"><span class="summary-label">Eccezioni Totali:</span> ' . $statisticheData['totale_eccezioni'] . '</div>
              </div>';

        // Statistiche per reparto
        echo '<h2>Statistiche per Reparto</h2>';
        echo '<table>
                <tr>
                    <th>Reparto</th>
                    <th>Cartellini</th>
                    <th>Paia</th>
                    <th>Cartellini con Eccezioni</th>
                    <th>Eccezioni</th>
                </tr>';

        foreach ($statistichePerReparto as $reparto) {
            echo '<tr>
                    <td>' . htmlspecialchars($reparto['reparto']) . '</td>
                    <td>' . $reparto['cartellini'] . '</td>
                    <td>' . $reparto['paia'] . '</td>
                    <td>' . $reparto['cartellini_eccezioni'] . '</td>
                    <td>' . $reparto['eccezioni'] . '</td>
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
                    </tr>';

            foreach ($statistichePerDifetto as $difetto) {
                echo '<tr>
                        <td>' . htmlspecialchars($difetto['tipo_difetto']) . '</td>
                        <td>' . $difetto['occorrenze'] . '</td>
                      </tr>';
            }

            echo '</table>';
        }

        // Elenco cartellini
        echo '<h2>Cartellini ' . $displayDate . '</h2>';
        echo '<table>
                <tr>
                    <th>ID</th>
                    <th>Numero Cartellino</th>
                    <th>Reparto</th>
                    <th>Data Controllo</th>
                    <th>Operatore</th>
                    <th>Tipo CQ</th>
                    <th>Paia</th>
                    <th>Cod. Articolo</th>
                    <th>Articolo</th>
                    <th>Linea</th>
                    <th>Note</th>
                    <th>Eccezioni</th>
                </tr>';

        foreach ($cartelliniData as $cartellino) {
            $rowClass = $cartellino['ha_eccezioni'] == 1 ? ' class="exception"' : '';
            echo '<tr' . $rowClass . '>
                    <td>' . $cartellino['id'] . '</td>
                    <td>' . htmlspecialchars($cartellino['numero_cartellino']) . '</td>
                    <td>' . htmlspecialchars($cartellino['reparto']) . '</td>
                    <td>' . date('d/m/Y H:i', strtotime($cartellino['data_controllo'])) . '</td>
                    <td>' . htmlspecialchars($cartellino['operatore']) . '</td>
                    <td>' . $cartellino['tipo_cq'] . '</td>
                    <td>' . $cartellino['paia_totali'] . '</td>
                    <td>' . htmlspecialchars($cartellino['cod_articolo']) . '</td>
                    <td>' . htmlspecialchars($cartellino['articolo']) . '</td>
                    <td>' . htmlspecialchars($cartellino['linea']) . '</td>
                    <td>' . htmlspecialchars($cartellino['note']) . '</td>
                    <td>' . $cartellino['num_eccezioni'] . '</td>
                  </tr>';
        }

        echo '</table>';
    }

    if ($reportType == 'detailed' || $reportType == 'exceptions') {
        // Elenco eccezioni
        echo '<h2>Eccezioni ' . $displayDate . '</h2>';

        if (empty($eccezioniData)) {
            echo '<p>Nessuna eccezione registrata per questa data.</p>';
        } else {
            echo '<table>
                    <tr>
                        <th>ID</th>
                        <th>Cartellino</th>
                        <th>Reparto</th>
                        <th>Articolo</th>
                        <th>Taglia</th>
                        <th>Tipo Difetto</th>
                        <th>Note</th>
                        <th>Data</th>
                    </tr>';

            foreach ($eccezioniData as $eccezione) {
                echo '<tr>
                        <td>' . $eccezione['id'] . '</td>
                        <td>' . htmlspecialchars($eccezione['numero_cartellino']) . '</td>
                        <td>' . htmlspecialchars($eccezione['reparto']) . '</td>
                        <td>' . htmlspecialchars($eccezione['articolo']) . '</td>
                        <td>' . htmlspecialchars($eccezione['taglia']) . '</td>
                        <td>' . htmlspecialchars($eccezione['tipo_difetto']) . '</td>
                        <td>' . htmlspecialchars($eccezione['note_operatore']) . '</td>
                        <td>' . date('d/m/Y H:i', strtotime($eccezione['data_creazione'])) . '</td>
                      </tr>';
            }

            echo '</table>';
        }
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
    $filename = "CQ_Hermes_" . date('Ymd', strtotime($reportDate)) . "_" . $reportType . ".pdf";

    // Output del PDF
    $mpdf->Output($filename, 'D');
    exit;
} elseif ($reportFormat == 'excel') {
    // Imposta le intestazioni per il download Excel
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="CQ_Hermes_' . date('Ymd', strtotime($reportDate)) . '_' . $reportType . '.xls"');
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

    // Contenuto del report
    if ($reportType == 'summary' || $reportType == 'detailed') {
        // Riepilogo generale
        echo '<h2>Riepilogo</h2>';
        echo '<table>
                <tr>
                    <th>Cartellini Totali</th>
                    <th>Paia Totali</th>
                    <th>Cartellini con Eccezioni</th>
                    <th>% Cartellini con Eccezioni</th>
                    <th>Eccezioni Totali</th>
                </tr>
                <tr>
                    <td>' . $statisticheData['totale_cartellini'] . '</td>
                    <td>' . $statisticheData['totale_paia'] . '</td>
                    <td>' . $statisticheData['cartellini_con_eccezioni'] . '</td>
                    <td>' . $statisticheData['percentuale_cartellini_eccezioni'] . '%</td>
                    <td>' . $statisticheData['totale_eccezioni'] . '</td>
                </tr>
              </table>';

        // Statistiche per reparto
        echo '<h2>Statistiche per Reparto</h2>';
        echo '<table>
                <tr>
                    <th>Reparto</th>
                    <th>Cartellini</th>
                    <th>Paia</th>
                    <th>Cartellini con Eccezioni</th>
                    <th>Eccezioni</th>
                </tr>';

        foreach ($statistichePerReparto as $reparto) {
            echo '<tr>
                    <td>' . htmlspecialchars($reparto['reparto']) . '</td>
                    <td>' . $reparto['cartellini'] . '</td>
                    <td>' . $reparto['paia'] . '</td>
                    <td>' . $reparto['cartellini_eccezioni'] . '</td>
                    <td>' . $reparto['eccezioni'] . '</td>
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
                    </tr>';

            foreach ($statistichePerDifetto as $difetto) {
                echo '<tr>
                        <td>' . htmlspecialchars($difetto['tipo_difetto']) . '</td>
                        <td>' . $difetto['occorrenze'] . '</td>
                      </tr>';
            }

            echo '</table>';
        }

        // Elenco cartellini
        echo '<h2>Cartellini ' . $displayDate . '</h2>';
        echo '<table>
                <tr>
                    <th>ID</th>
                    <th>Numero Cartellino</th>
                    <th>Reparto</th>
                    <th>Data Controllo</th>
                    <th>Operatore</th>
                    <th>Tipo CQ</th>
                    <th>Paia</th>
                    <th>Codice Articolo</th>
                    <th>Articolo</th>
                    <th>Linea</th>
                    <th>Note</th>
                    <th>Eccezioni</th>
                </tr>';
        foreach ($cartelliniData as $cartellino) {
            $rowClass = $cartellino['ha_eccezioni'] == 1 ? ' class="exception"' : '';
            echo '<tr' . $rowClass . '>
                    <td>' . $cartellino['id'] . '</td>
                    <td>' . htmlspecialchars($cartellino['numero_cartellino']) . '</td>
                    <td>' . htmlspecialchars($cartellino['reparto']) . '</td>
                    <td>' . date('d/m/Y H:i', strtotime($cartellino['data_controllo'])) . '</td>
                    <td>' . htmlspecialchars($cartellino['operatore']) . '</td>
                    <td>' . $cartellino['tipo_cq'] . '</td>
                    <td>' . $cartellino['paia_totali'] . '</td>
                    <td>' . htmlspecialchars($cartellino['cod_articolo']) . '</td>
                    <td>' . htmlspecialchars($cartellino['articolo']) . '</td>
                    <td>' . htmlspecialchars($cartellino['linea']) . '</td>
                    <td>' . htmlspecialchars($cartellino['note']) . '</td>
                    <td>' . $cartellino['num_eccezioni'] . '</td>
                  </tr>';
        }

        echo '</table>';
    }

    if ($reportType == 'detailed' || $reportType == 'exceptions') {
        // Elenco eccezioni
        echo '<h2>Eccezioni ' . $displayDate . '</h2>';

        if (empty($eccezioniData)) {
            echo '<p>Nessuna eccezione registrata per questa data.</p>';
        } else {
            echo '<table>
                    <tr>
                        <th>ID</th>
                        <th>Cartellino</th>
                        <th>Reparto</th>
                        <th>Articolo</th>
                        <th>Taglia</th>
                        <th>Tipo Difetto</th>
                        <th>Note</th>
                        <th>Data</th>
                    </tr>';

            foreach ($eccezioniData as $eccezione) {
                echo '<tr>
                        <td>' . $eccezione['id'] . '</td>
                        <td>' . htmlspecialchars($eccezione['numero_cartellino']) . '</td>
                        <td>' . htmlspecialchars($eccezione['reparto']) . '</td>
                        <td>' . htmlspecialchars($eccezione['articolo']) . '</td>
                        <td>' . htmlspecialchars($eccezione['taglia']) . '</td>
                        <td>' . htmlspecialchars($eccezione['tipo_difetto']) . '</td>
                        <td>' . htmlspecialchars($eccezione['note_operatore']) . '</td>
                        <td>' . date('d/m/Y H:i', strtotime($eccezione['data_creazione'])) . '</td>
                      </tr>';
            }

            echo '</table>';
        }
    }

    // Chiudi il documento HTML
    echo '</body></html>';

    // Invia l'output
    ob_end_flush();
    exit;
}