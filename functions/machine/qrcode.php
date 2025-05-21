<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';
require_once '../../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\Result\PngResult;


// Get database instance
$pdo = getDbInstance();

// Check if ID was passed
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID macchinario non specificato.";
    header("Location: lista_macchinari");
    exit;
}

$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['error'] = "ID macchinario non valido.";
    header("Location: lista_macchinari");
    exit;
}

// Get machine details
try {
    $stmt = $pdo->prepare("SELECT * FROM mac_anag WHERE id = ?");
    $stmt->execute([$id]);
    $macchinario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$macchinario) {
        $_SESSION['error'] = "Macchinario non trovato.";
        header("Location: lista_macchinari");
        exit;
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Errore nel recupero dei dati: " . $e->getMessage();
    header("Location: lista_macchinari");
    exit;
}

// Generate QR Code (only when download or print is requested)
$qrCodeImage = '';
$qrCodePath = '';

if (isset($_GET['action']) && ($_GET['action'] == 'download' || $_GET['action'] == 'print')) {
    // URL for maintenance form that will be encoded in QR code
    $maintenanceUrl = $dominio . "/functions/machine/manutenzione-form?id=" . $id . "&token=" . md5($macchinario['matricola'] . $id);
    
    // Generate QR code
    $qrCode = QrCode::create($maintenanceUrl)
        ->setEncoding(new Encoding('UTF-8'))
        ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
        ->setSize(300)
        ->setMargin(10)
        ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
        ->setForegroundColor(new Color(0, 0, 0))
        ->setBackgroundColor(new Color(255, 255, 255));
    
    $writer = new PngWriter();
    $result = $writer->write($qrCode);
    
    // Handle download action
    if ($_GET['action'] == 'download') {
        $filename = 'QRCode_' . $macchinario['matricola'] . '.png';
        header('Content-Type: ' . $result->getMimeType());
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $result->getString();
        exit;
    }
    
    // For print action, we'll save to a temporary file and show in the page
    if ($_GET['action'] == 'print') {
        $qrCodePath = '../../uploads/qrcodes/';
        if (!file_exists($qrCodePath)) {
            mkdir($qrCodePath, 0755, true);
        }
        
        $qrFileName = 'qrcode_' . $id . '_' . time() . '.png';
        $qrFilePath = $qrCodePath . $qrFileName;
        
        file_put_contents($qrFilePath, $result->getString());
        $qrCodeImage = BASE_URL . '/uploads/qrcodes/' . $qrFileName;
    }
    
    // Log QR code generation
    try {
        $stmt = $pdo->prepare("INSERT INTO mac_qrcode_logs (mac_id, ip_address, user_agent, azione) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $id, 
            $_SERVER['REMOTE_ADDR'], 
            $_SERVER['HTTP_USER_AGENT'], 
            'generazione_' . $_GET['action']
        ]);
    } catch (PDOException $e) {
        // Silently fail, this is just logging
    }
}

// Include header
require_once BASE_PATH . '/components/header.php';
?>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include(BASE_PATH . "/components/navbar.php"); ?>
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php include(BASE_PATH . "/components/topbar.php"); ?>
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <?php include(BASE_PATH . "/utils/alerts.php"); ?>
                    
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-qrcode text-gray-500 mr-2"></i>
                            QR Code Macchinario
                        </h1>
                        <div>
                            <a href="dettaglio_macchinario?id=<?= $id ?>" class="btn btn-info btn-sm shadow-sm">
                                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Torna al Dettaglio
                            </a>
                            <a href="?id=<?= $id ?>&action=download" class="btn btn-primary btn-sm shadow-sm ml-2">
                                <i class="fas fa-download fa-sm text-white-50"></i> Scarica QR Code
                            </a>
                            <a href="?id=<?= $id ?>&action=print" class="btn btn-success btn-sm shadow-sm ml-2">
                                <i class="fas fa-print fa-sm text-white-50"></i> Stampa Etichetta
                            </a>
                        </div>
                    </div>
                    
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="home">Macchinari</a></li>
                        <li class="breadcrumb-item"><a href="lista_macchinari">Lista Macchinari</a></li>
                        <li class="breadcrumb-item"><a href="dettaglio_macchinario?id=<?= $id ?>">Dettaglio</a></li>
                        <li class="breadcrumb-item active">QR Code</li>
                    </ol>
                    
                    <div class="row">
                        <!-- QR Code Card -->
                        <div class="col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        QR Code per <?= htmlspecialchars($macchinario['matricola']) ?>
                                    </h6>
                                </div>
                                <div class="card-body text-center">
                                    <?php if (!empty($qrCodeImage)): ?>
                                        <div class="mb-3">
                                            <img src="<?= $qrCodeImage ?>" class="img-fluid" alt="QR Code" style="max-width: 300px;">
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            Scegli un'azione per generare il QR Code: 
                                            <a href="?id=<?= $id ?>&action=print" class="alert-link">Stampa</a> o 
                                            <a href="?id=<?= $id ?>&action=download" class="alert-link">Scarica</a>
                                        </div>
                                        <div class="mb-3">
                                            <i class="fas fa-qrcode fa-10x text-gray-300"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mt-3">
                                        <p class="mb-1">
                                            <strong>Matricola:</strong> <?= htmlspecialchars($macchinario['matricola']) ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Tipo:</strong> <?= htmlspecialchars($macchinario['tipologia']) ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Modello:</strong> <?= htmlspecialchars($macchinario['modello']) ?>
                                        </p>
                                    </div>
                                    
                                    <div class="my-4">
                                        <div class="alert alert-warning">
                                            <p class="mb-0">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                Scansionando questo codice QR è possibile registrare una nuova manutenzione per questo macchinario.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Print Options Card -->
                        <div class="col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        Opzioni di Stampa
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($qrCodeImage)): ?>
                                        <div class="d-print-none mb-4">
                                            <button onclick="window.print();" class="btn btn-success btn-block">
                                                <i class="fas fa-print mr-2"></i> Stampa Etichetta
                                            </button>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="copie"><strong>Numero di Copie:</strong></label>
                                            <select class="form-control" id="copie">
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="6">6</option>
                                                <option value="8">8</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="dimensione"><strong>Dimensione Etichetta:</strong></label>
                                            <select class="form-control" id="dimensione">
                                                <option value="small">Piccola (50x25mm)</option>
                                                <option value="medium" selected>Media (70x40mm)</option>
                                                <option value="large">Grande (100x70mm)</option>
                                            </select>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            Genera il QR Code per visualizzare le opzioni di stampa.
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mt-4">
                                        <h6 class="font-weight-bold">Istruzioni per l'uso:</h6>
                                        <ol class="pl-3">
                                            <li>Genera il QR Code cliccando su "Stampa Etichetta"</li>
                                            <li>Scegli il formato e il numero di copie</li>
                                            <li>Clicca sul pulsante "Stampa Etichetta" per stampare</li>
                                            <li>Applica l'etichetta stampata sul macchinario</li>
                                            <li>I manutentori potranno scansionare il QR per registrare interventi</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Print Template (shown only during print) -->
                    <?php if (!empty($qrCodeImage)): ?>
                    <div class="d-none d-print-block">
                        <div id="printContainer"></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            
            <?php if (!empty($qrCodeImage)): ?>
            <script>
                $(document).ready(function() {
                    // Create print template when options change
                    function updatePrintTemplate() {
                        var copies = parseInt($('#copie').val());
                        var size = $('#dimensione').val();
                        var templateHtml = '';
                        
                        // Set size classes
                        var sizeClass = '';
                        switch(size) {
                            case 'small':
                                sizeClass = 'col-3'; // 25% width
                                break;
                            case 'medium':
                                sizeClass = 'col-4'; // 33% width
                                break;
                            case 'large':
                                sizeClass = 'col-6'; // 50% width
                                break;
                            default:
                                sizeClass = 'col-4';
                        }
                        
                        // Create row with specified number of copies
                        templateHtml += '<div class="row">';
                        for (var i = 0; i < copies; i++) {
                            templateHtml += '<div class="' + sizeClass + ' mb-3 p-2 text-center">';
                            templateHtml += '<div><img src="<?= $qrCodeImage ?>" class="img-fluid" style="max-height: 150px;"></div>';
                            templateHtml += '<div class="small mt-1"><strong><?= htmlspecialchars($macchinario['matricola']) ?></strong></div>';
                            templateHtml += '<div class="small"><?= htmlspecialchars($macchinario['modello']) ?></div>';
                            templateHtml += '</div>';
                        }
                        templateHtml += '</div>';
                        
                        $('#printContainer').html(templateHtml);
                    }
                    
                    // Update when options change
                    $('#copie, #dimensione').change(updatePrintTemplate);
                    
                    // Initialize template
                    updatePrintTemplate();
                });
            </script>
            <?php endif; ?>
            
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>