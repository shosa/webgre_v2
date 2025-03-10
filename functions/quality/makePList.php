<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';
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
            $sql = "SELECT * FROM cq_records WHERE cartellino IN ($placeholders) ORDER BY cartellino, calzata";
            
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
                    $success = 'Dati recuperati con successo.';
                    
                    // Organizza i dati per articolo e cartellino
                    $report_data = [];
                    $articoli = [];
                    
                    foreach ($results as $row) {
                        $articoli[$row['cod_articolo']] = [
                            'nome' => $row['articolo'],
                            'commessa' => $row['commessa']
                        ];
                        
                        if (!isset($report_data[$row['cod_articolo']][$row['cartellino']])) {
                            $report_data[$row['cod_articolo']][$row['cartellino']] = [];
                        }
                        
                        $report_data[$row['cod_articolo']][$row['cartellino']][] = $row;
                    }
                }
            } else {
                $error = 'Errore nella preparazione della query.';
            }
        }
    }
}
?>

<style>
    /* Stili generali */
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
    
    /* Stili per la preview del report */
    .report-preview {
        border: 1px solid #ddd;
        padding: 20px;
        margin-top: 20px;
        background-color: #f9f9f9;
    }
    
    .report-header {
        text-align: center;
        margin-bottom: 20px;
    }
    
    .report-header img {
        max-height: 60px;
        margin-bottom: 10px;
    }
    
    .report-title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .report-code {
        font-size: 14px;
        color: #555;
    }
    
    .report-info {
        margin-bottom: 20px;
    }
    
    .report-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    
    .report-table th, .report-table td {
        border: 1px solid #000;
        padding: 8px;
        text-align: left;
    }
    
    .report-table th {
        background-color: #f2f2f2;
    }
    
    .checkboxes {
        display: flex;
        justify-content: center;
    }
    
    .checkbox {
        width: 20px;
        height: 20px;
        border: 1px solid #000;
        margin: 0 auto;
        background-color: #fff;
    }
    
    .esito-v {
        background-color: #d4edda;
    }
    
    .esito-x {
        background-color: #f8d7da;
    }
    
    .signature-area {
        margin-top: 30px;
        display: flex;
        justify-content: space-between;
    }
    
    .signature-box {
        border-top: 1px solid #000;
        padding-top: 5px;
        width: 45%;
    }
    
    /* Stili per la stampa */
    @media print {
        .no-print {
            display: none !important;
        }
        
        .report-preview {
            border: none;
            padding: 0;
            margin: 0;
            background-color: #fff;
        }
        
        body {
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        
        .container-fluid {
            padding: 0;
        }
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
                        <div class="col-md-5 no-print">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Inserisci i dati DDT</h6>
                                </div>
                                <div class="card-body">
                                    <form method="post" action="">
                                        <div class="form-group">
                                            <label for="ddt_numero">Numero DDT / Bolla:</label>
                                            <input type="text" class="form-control" id="ddt_numero" name="ddt_numero" value="<?php echo $ddt_numero; ?>" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="ddt_data">Data DDT:</label>
                                            <input type="date" class="form-control" id="ddt_data" name="ddt_data" value="<?php echo $ddt_data; ?>" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="cartellini">Numeri Cartellino (uno per riga o separati da virgola):</label>
                                            <div class="textarea-container">
                                                <textarea class="form-control" id="cartellini" name="cartellini" required><?php echo $cartellini; ?></textarea>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Genera Report</button>
                                    </form>
                                </div>
                            </div>
                            
                            <?php if (!empty($results)): ?>
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Controlli</h6>
                                    </div>
                                    <div class="card-body">
                                        <button onclick="window.print();" class="btn btn-success mb-3">
                                            <i class="fas fa-print"></i> Stampa Report
                                        </button>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Cartellino</th>
                                                        <th>Articolo</th>
                                                        <th>Test</th>
                                                        <th>Esito</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($results as $row): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($row['cartellino']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['articolo']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['test']); ?></td>
                                                            <td class="<?php echo ($row['esito'] == 'V') ? 'esito-v' : 'esito-x'; ?>">
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
                        
                        <?php if (!empty($results)): ?>
                            <?php foreach ($articoli as $cod_articolo => $articolo_info): ?>
                                <div class="col-md-7">
                                    <div class="report-preview">
                                        <div class="report-header">
                                            <img src="../../img/logo.png" alt="Logo" class="mb-2">
                                            <div class="report-title">Check list di orlatura</div>
                                            <div class="report-code">MO-RCT-001_01</div>
                                        </div>
                                        
                                        <div class="report-info">
                                            <table class="report-table">
                                                <tr>
                                                    <td colspan="4"><strong>Check list articolo: <?php echo htmlspecialchars($articolo_info['nome']); ?> (<?php echo htmlspecialchars($cod_articolo); ?>)</strong></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tomaificio:</strong></td>
                                                    <td><strong>Data:</strong> <?php echo htmlspecialchars($ddt_data); ?></td>
                                                    <td><strong>N° bolla/e:</strong> <?php echo htmlspecialchars($ddt_numero); ?></td>
                                                    <td><strong>Quantità totale:</strong> <?php echo count($report_data[$cod_articolo]); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                        
                                        <div class="report-content">
                                            <table class="report-table">
                                                <tr>
                                                    <th rowspan="2">#</th>
                                                    <th colspan="2">Controllo tomaificio</th>
                                                    <th rowspan="2">Requisito di qualità</th>
                                                    <th rowspan="2">Tipo di controllo</th>
                                                    <th rowspan="2">Tolleranze/ parametri</th>
                                                    <th rowspan="2">Riferimento per i requisiti</th>
                                                    <th rowspan="2">Frequenza controllo</th>
                                                    <th rowspan="2">Eventuale azione correttiva</th>
                                                    <th colspan="2">Controllo interno</th>
                                                </tr>
                                                <tr>
                                                    <th>Esito controllo</th>
                                                    <th>Paia non conformi</th>
                                                    <th>Esito controllo</th>
                                                    <th>Paia non conformi</th>
                                                </tr>
                                                
                                                <?php 
                                                $counter = 1;
                                                foreach ($report_data[$cod_articolo] as $cartellino => $tests): 
                                                    // Determina l'esito complessivo per questo cartellino
                                                    $esito_complessivo = 'V'; // Assume tutti i test siano ok
                                                    foreach ($tests as $test) {
                                                        if ($test['esito'] == 'X') {
                                                            $esito_complessivo = 'X';
                                                            break;
                                                        }
                                                    }
                                                ?>
                                                <tr>
                                                    <td><?php echo $counter++; ?></td>
                                                    <td class="checkboxes">
                                                        <div class="checkbox">
                                                            <?php if ($esito_complessivo == 'V'): ?>
                                                                <div style="text-align: center;">✓</div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td class="<?php echo ($esito_complessivo == 'X') ? 'esito-x' : ''; ?>">
                                                        <?php if ($esito_complessivo == 'X'): ?>
                                                            <?php echo htmlspecialchars($cartellino); ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        // Mostra i test effettuati su questo cartellino
                                                        $test_list = array_map(function($test) {
                                                            return $test['test'] . ' [' . $test['calzata'] . ']';
                                                        }, $tests);
                                                        echo htmlspecialchars(implode(', ', $test_list));
                                                        ?>
                                                    </td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td>
                                                        <?php
                                                        // Mostra le note per i test falliti
                                                        $note_list = [];
                                                        foreach ($tests as $test) {
                                                            if ($test['esito'] == 'X' && !empty($test['note'])) {
                                                                $note_list[] = $test['test'] . ': ' . $test['note'];
                                                            }
                                                        }
                                                        echo htmlspecialchars(implode('; ', $note_list));
                                                        ?>
                                                    </td>
                                                    <td class="checkboxes">
                                                        <div class="checkbox"></div>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <?php endforeach; ?>
                                                
                                                <?php 
                                                // Righe rimanenti vuote fino a 10
                                                $remaining = 10 - count($report_data[$cod_articolo]);
                                                for ($i = 0; $i < $remaining; $i++): 
                                                ?>
                                                <tr>
                                                    <td><?php echo $counter++; ?></td>
                                                    <td class="checkboxes"><div class="checkbox"></div></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td class="checkboxes"><div class="checkbox"></div></td>
                                                    <td></td>
                                                </tr>
                                                <?php endfor; ?>
                                            </table>
                                        </div>
                                        
                                        <div class="signature-area">
                                            <div class="signature-box">
                                                <strong>Firma controllore tomaificio:</strong>
                                            </div>
                                            <div class="signature-box">
                                                <strong>Firma controllore interno:</strong>
                                            </div>
                                        </div>
                                        
                                        <div style="text-align: center; margin-top: 20px;">
                                            <small>Pagina 1 di 1</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
    <?php include_once BASE_PATH . '/components/scripts.php'; ?>
</body>