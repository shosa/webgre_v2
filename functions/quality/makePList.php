<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';
require_once BASE_PATH . '/vendor/autoload.php';
$db = getDbInstance();

// Includi l'header
require_once BASE_PATH . '/components/header.php';

// Inizializza le variabili
$ddt_numero = '';
$ddt_data = '';
$cartellini = '';
$results = [];
$error = '';
$success = '';
$pdf_files = [];

// Gestione del form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ddt_numero = filter_input(INPUT_POST, 'ddt_numero', FILTER_UNSAFE_RAW);
    $ddt_data = filter_input(INPUT_POST, 'ddt_data', FILTER_UNSAFE_RAW);
    $cartellini = filter_input(INPUT_POST, 'cartellini', FILTER_UNSAFE_RAW);

    // Valida i dati di input
    if (empty($ddt_numero) || empty($ddt_data) || empty($cartellini)) {
        $error = 'Tutti i campi sono obbligatori.';
    } else {
        // Processa i cartellini
        $cartellini_list = preg_split('/\r\n|\r|\n|,/', $cartellini);
        $cartellini_list = array_map('trim', $cartellini_list);
        $cartellini_list = array_filter($cartellini_list);

        if (empty($cartellini_list)) {
            $error = 'Inserisci almeno un cartellino valido.';
        } else {
            // Prepara la query con placeholders
            $placeholders = implode(',', array_fill(0, count($cartellini_list), '?'));
            $sql = "SELECT * FROM cq_records WHERE cartellino IN ($placeholders) ORDER BY cartellino, test, calzata";

            // Prepara ed esegui la query
            $stmt = $db->prepare($sql);
            if ($stmt) {
                $i = 1;
                foreach ($cartellini_list as $cartellino) {
                    $stmt->bindValue($i++, $cartellino);
                }
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($results)) {
                    $error = 'Nessun risultato trovato per i cartellini inseriti.';
                } else {
                    $success = 'Dati recuperati con successo. Generazione PDF in corso...';

                    // Organizza i dati per articolo
                    $report_data = [];
                    $articoli = [];

                    foreach ($results as $row) {
                        $cod_articolo = $row['cod_articolo'];

                        $articoli[$cod_articolo] = [
                            'nome' => $row['articolo'],
                            'commessa' => $row['commessa']
                        ];

                        if (!isset($report_data[$cod_articolo])) {
                            $report_data[$cod_articolo] = [];
                        }

                        $report_data[$cod_articolo][] = $row;
                    }

                    // Genera un PDF per ogni articolo
                    $pdf_files = [];
                    foreach ($articoli as $cod_articolo => $articolo_info) {
                        // Crea il PDF
                        $pdf_files[$cod_articolo] = generatePDF($ddt_numero, $ddt_data, $cod_articolo, $articolo_info, $report_data[$cod_articolo]);
                    }
                }
            } else {
                $error = 'Errore nella preparazione della query.';
            }
        }
    }
}

/**
 * Funzione per generare il PDF della check list usando mPDF con approccio HTML
 */
function generatePDF($ddt_numero, $ddt_data, $cod_articolo, $articolo_info, $tests)
{
    // Crea directory se non esiste
    $report_dir = BASE_PATH . '/temp/reports/';
    if (!file_exists($report_dir)) {
        mkdir($report_dir, 0755, true);
    }

    // Nome file PDF
    $filename = 'ddt_report_' . $ddt_numero . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $cod_articolo) . '.pdf';
    $file_path = $report_dir . '/' . $filename;

    // Formatta la data in formato italiano
    $formatted_date = date('d/m/Y', strtotime($ddt_data));
    
    // Raccoglie tutte le commesse uniche per questo articolo
    $commesse = [];
    foreach ($tests as $test) {
        if (!empty($test['commessa']) && !in_array($test['commessa'], $commesse)) {
            $commesse[] = $test['commessa'];
        }
    }
    
    // Formatta la stringa delle commesse
    $commesse_str = implode(', ', $commesse);

    // Prepara il contenuto HTML
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Check list Controllo Qualità</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
                font-size: 10pt;
            }
            .container {
                width: 100%;
            }
            .header {
                text-align: center;
                margin-bottom: 15px;
                position: relative;
                border-bottom: 1px solid #000;
                padding-bottom: 5px;
            }
            .logo {
                position: absolute;
                top: 0;
                left: 0;
                max-width: 150px;
            }
            .doc-title {
                font-size: 18pt;
                font-weight: bold;
                margin: 0;
                padding-top: 15px;
            }
            .doc-code {
                position: absolute;
                top: 10px;
                right: 10px;
                font-size: 10pt;
            }
            .info-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 15px;
            }
            .info-table td {
                border: 1px solid #000;
                padding: 4px 8px;
            }
            .info-table .bold {
                font-weight: bold;
            }
            .main-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
                font-size: 9pt;
            }
            .main-table th, .main-table td {
                border: 1px solid #000;
                padding: 4px;
                text-align: center;
                vertical-align: middle;
            }
            .main-table th {
                background-color: #f0f0f0;
                font-weight: bold;
            }
            .main-table .left-align {
                text-align: left;
            }
            .row-number {
                width: 20px;
            }
            .esito-check {
                width: 30px;
            }
            .paia-non-conformi {
                width: 50px;
            }
            .requisito {
                width: 120px;
            }
            .non-conformi-bg {
                background-color: #ffdddd;
            }
            .check-mark {
                font-weight: bold;
                font-size: 14pt;
            }
            .signatures {
                margin-top: 30px;
                width: 100%;
            }
            .signature-line {
                display: inline-block;
                width: 45%;
                border-top: 1px solid #000;
                padding-top: 5px;
                font-weight: bold;
                margin: 0 2%;
            }
            .commesse-info {
                margin-top: 20px;
                font-size: 9pt;
                border-top: 1px dashed #ccc;
                padding-top: 10px;
            }
            .page-number {
                text-align: center;
                font-size: 8pt;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <img class="logo" src="' . BASE_PATH . '/img/logo.png" alt="Logo">
                <div class="doc-title">Check list di orlatura</div>
                
            </div>
            
            <div class="article-info">
                <table class="info-table">
                    <tr>
                        <td colspan="4" class="bold">Check list articolo: ' . htmlspecialchars($articolo_info['nome']) . ' (' . htmlspecialchars($cod_articolo) . ')</td>
                    </tr>
                    <tr>
                        <td><span class="bold">Tomaificio: Emmegiemme Shoes</span></td>
                        <td><span class="bold">Data:</span> ' . $formatted_date . '</td>
                        <td><span class="bold">N° bolla/e:</span> ' . htmlspecialchars($ddt_numero) . '</td>
                        <td><span class="bold">Quantità totale:</span> ' . count($tests) . '</td>
                    </tr>
                </table>
            </div>
            
            <div class="main-content">
                <table class="main-table">
                    <thead>
                        <tr>
                            <th rowspan="2" class="row-number">#</th>
                            <th colspan="2">Controllo tomaificio</th>
                            <th rowspan="2" class="requisito">Requisito di qualità</th>
                            <th rowspan="2">Tipo di controllo</th>
                            <th rowspan="2">Tolleranze/ parametri</th>
                            <th rowspan="2">Riferimento per i requisiti</th>
                            <th rowspan="2">Frequenza controllo</th>
                            <th rowspan="2">Eventuale azione correttiva</th>
                            <th colspan="2">Controllo interno</th>
                        </tr>
                        <tr>
                            <th class="esito-check">Esito controllo</th>
                            <th class="paia-non-conformi">Paia non conformi</th>
                            <th class="esito-check">Esito controllo</th>
                            <th class="paia-non-conformi">Paia non conformi</th>
                        </tr>
                    </thead>
                    <tbody>';

    // Aggiungi le righe per i test
    $counter = 1;
    foreach ($tests as $test) {
        $html .= '<tr>
                <td>' . $counter . '</td>
                <td class="esito-check">' . ($test['esito'] == 'V' ? '<span class="check-mark">✓</span>' : '') . '</td>
                <td class="paia-non-conformi ' . ($test['esito'] == 'X' ? 'non-conformi-bg' : '') . '">' . 
                    ($test['esito'] == 'X' ? '1' : '') . '</td>
                <td class="left-align">' . htmlspecialchars($test['test']) . ' [' . htmlspecialchars($test['calzata']) . ']</td>
                <td>TEST</td>
                <td></td>
                <td></td>
                <td></td>
                <td class="left-align">' . ($test['esito'] == 'X' && !empty($test['note']) ? htmlspecialchars($test['note']) : '') . '</td>
                <td></td>
                <td></td>
            </tr>';
        $counter++;
    }

    // Aggiungi righe vuote fino a 10 se necessario
    $remaining = 10 - count($tests);
    for ($i = 0; $i < $remaining; $i++) {
        $html .= '<tr>
                <td>' . $counter . '</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>';
        $counter++;
    }

    $html .= '</tbody>
                </table>
            </div>
            
            <div class="signatures">
                <div class="signature-line">Firma controllore tomaificio:</div>
                <div class="signature-line">Firma controllore interno:</div>
            </div>
            
            <div class="commesse-info">
                <strong>Commesse:</strong> ' . htmlspecialchars($commesse_str) . '
            </div>
            
            <div class="page-number">Pagina 1 di 1</div>
        </div>
    </body>
    </html>';

    // Crea l'istanza di mPDF
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4-L', // Formato A4 orizzontale
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 10,
        'margin_header' => 0,
        'margin_footer' => 0
    ]);

    // Imposta i metadati del documento
    $mpdf->SetTitle('Check list di orlatura - ' . $articolo_info['nome']);
    $mpdf->SetAuthor('Sistema Controllo Qualità');
    $mpdf->SetCreator('Sistema CQ');

    // Aggiungi il contenuto HTML
    $mpdf->WriteHTML($html);

    // Salva il PDF
    $mpdf->Output($file_path, 'F');

    return $filename;
}
?>

<style>
    /* Stili per la pagina web */
    .card-header {
        font-weight: bold;
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
    
    .textarea-container {
        height: 150px;
    }
    
    textarea.form-control {
        height: 100%;
        resize: none;
    }
    
    .preview-iframe {
        width: 100%;
        height: 600px;
        border: 1px solid #ddd;
    }
    
    .table-results {
        font-size: 0.9rem;
    }
    
    .esito-v {
        background-color: #d4edda;
        color: #155724;
    }
    
    .esito-x {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    /* Stili per PDF preview */
    .pdf-preview-container {
        position: relative;
        margin-top: 20px;
    }
    
    .pdf-controls {
        margin-bottom: 15px;
    }
    
    .pdf-preview-spinner {
        position: absolute;
        top: 45%;
        left: 45%;
        display: none;
    }
</style>

<body id="page-top">
    <div id="wrapper">
        <?php include(BASE_PATH . "/components/navbar.php"); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include(BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Generatore Report DDT</h1>
                    </div>

                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../functions/quality/home">Home</a></li>
                        <li class="breadcrumb-item active">Generatore Report DDT</li>
                    </ol>

                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Inserisci i dati DDT</h6>
                                </div>
                                <div class="card-body">
                                    <form method="post" action="">
                                        <div class="form-group">
                                            <label for="ddt_numero">Numero DDT / Bolla:</label>
                                            <input type="text" class="form-control" id="ddt_numero" name="ddt_numero"
                                                value="<?php echo $ddt_numero; ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="ddt_data">Data DDT:</label>
                                            <input type="date" class="form-control" id="ddt_data" name="ddt_data"
                                                value="<?php echo $ddt_data; ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="cartellini">Numeri Cartellino (uno per riga o separati da
                                                virgola):</label>
                                            <div class="textarea-container">
                                                <textarea class="form-control" id="cartellini" name="cartellini"
                                                    required><?php echo $cartellini; ?></textarea>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary">Genera Report PDF</button>
                                    </form>
                                </div>
                            </div>

                            <?php if (!empty($results)): ?>
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Test Trovati</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-results">
                                                <thead>
                                                    <tr>
                                                        <th>Cartellino</th>
                                                        <th>Test</th>
                                                        <th>Calzata</th>
                                                        <th>Esito</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($results as $row): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($row['cartellino']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['test']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['calzata']); ?></td>
                                                            <td
                                                                class="<?php echo ($row['esito'] == 'V') ? 'esito-v' : 'esito-x'; ?>">
                                                                <?php echo htmlspecialchars($row['esito']); ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($pdf_files)): ?>
                            <div class="col-md-8">
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Report PDF Generati</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="pdf-controls mb-3">
                                            <button id="downloadAllBtn" class="btn btn-success">
                                                <i class="fas fa-download"></i> Scarica tutti i PDF
                                            </button>
                                        </div>
                                        
                                        <div class="list-group mb-4">
                                            <?php foreach ($articoli as $cod_articolo => $articolo_info): ?>
                                                <?php
                                                $filename = $pdf_files[$cod_articolo];
                                                $file_url = '../../temp/reports/' . $filename;
                                                ?>
                                                <a href="<?php echo $file_url; ?>" target="_blank"
                                                    class="list-group-item list-group-item-action pdf-link"
                                                    data-url="<?php echo $file_url; ?>">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <h5 class="mb-1"><?php echo htmlspecialchars($articolo_info['nome']); ?>
                                                        </h5>
                                                        <small>Cartellini:
                                                            <?php echo count($report_data[$cod_articolo]); ?></small>
                                                    </div>
                                                    <p class="mb-1">Codice: <?php echo htmlspecialchars($cod_articolo); ?></p>
                                                    <small>Clicca per visualizzare/scaricare il PDF</small>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>

                                        <?php
                                        // Mostra l'anteprima del primo PDF
                                        reset($pdf_files);
                                        $first_cod_articolo = key($pdf_files);
                                        $first_filename = $pdf_files[$first_cod_articolo];
                                        $first_file_url = '../../temp/reports/' . $first_filename;
                                        ?>

                                        <div class="pdf-preview-container">
                                            <h6 class="font-weight-bold">Anteprima Report:</h6>
                                            <div class="text-center pdf-preview-spinner">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="sr-only">Caricamento...</span>
                                                </div>
                                            </div>
                                            <iframe id="pdfPreview" src="<?php echo $first_file_url; ?>" class="preview-iframe"></iframe>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>

    <?php include_once BASE_PATH . '/components/scripts.php'; ?>
    
    <?php if (!empty($pdf_files)): ?>
    <script>
        // Script per gestire il download di tutti i PDF
        document.addEventListener('DOMContentLoaded', function() {
            // Gestisci il click sui link PDF per l'anteprima
            document.querySelectorAll('.pdf-link').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    var pdfUrl = this.getAttribute('data-url');
                    var previewFrame = document.getElementById('pdfPreview');
                    var spinner = document.querySelector('.pdf-preview-spinner');
                    
                    // Mostra lo spinner
                    spinner.style.display = 'block';
                    
                    // Carica il PDF
                    previewFrame.onload = function() {
                        spinner.style.display = 'none';
                    };
                    previewFrame.src = pdfUrl;
                });
            });
            
            // Gestisci il download di tutti i PDF
            document.getElementById('downloadAllBtn').addEventListener('click', function() {
                document.querySelectorAll('.pdf-link').forEach(function(link, index) {
                    var pdfUrl = link.getAttribute('data-url');
                    
                    // Crea un timeout per ogni download per evitare blocchi del browser
                    setTimeout(function() {
                        window.open(pdfUrl, '_blank');
                    }, index * 1000); // Ritarda ogni download di 1 secondo
                });
            });
        });
    </script>
    <?php endif; ?>
</body>