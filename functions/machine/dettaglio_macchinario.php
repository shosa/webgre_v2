<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';

// Ottieni l'istanza del database
$pdo = getDbInstance();

// Verifica che l'ID sia stato passato
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

// Recupera i dati del macchinario
try {
    $stmt = $pdo->prepare("SELECT * FROM mac_anag WHERE id = ?");
    $stmt->execute([$id]);
    $macchinario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$macchinario) {
        $_SESSION['error'] = "Macchinario non trovato.";
        header("Location: lista_macchinari");
        exit;
    }
    
    // Calcolo età in anni
    $dataAcquisto = new DateTime($macchinario['data_acquisto']);
    $oggi = new DateTime();
    $eta = $dataAcquisto->diff($oggi)->y;
    
    // Stato basato sull'età
    $statoClass = 'success';
    $statoText = 'Acquistato da';
    
    // Recupera la cronologia delle manutenzioni (se esiste la tabella)
    $hasManutenzioni = false;
    try {
        $stmtManutenzioni = $pdo->prepare("SELECT * FROM mac_manutenzioni WHERE mac_id = ? ORDER BY data_manutenzione DESC LIMIT 5");
        $stmtManutenzioni->execute([$id]);
        $manutenzioni = $stmtManutenzioni->fetchAll(PDO::FETCH_ASSOC);
        $hasManutenzioni = true;
    } catch (PDOException $e) {
        // La tabella manutenzioni potrebbe non esistere ancora
        $manutenzioni = [];
    }
    
    // Recupera gli allegati dalla tabella mac_anag_allegati
    $hasAllegati = false;
    try {
        $stmtAllegati = $pdo->prepare("SELECT * FROM mac_anag_allegati WHERE mac_id = ? ORDER BY data_caricamento DESC");
        $stmtAllegati->execute([$id]);
        $allegati = $stmtAllegati->fetchAll(PDO::FETCH_ASSOC);
        $hasAllegati = $stmtAllegati->rowCount() > 0;
    } catch (PDOException $e) {
        // La tabella potrebbe non esistere ancora
        $allegati = [];
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Errore nel recupero dei dati: " . $e->getMessage();
    header("Location: lista_macchinari");
    exit;
}

// Inclusione dell'header
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
                            <i class="fas fa-clipboard-list text-gray-500 mr-2"></i>
                            Dettaglio Macchinario
                        </h1>
                        <div>
                            <a href="edit_macchinario?id=<?= $id ?>" class="btn btn-primary btn-sm shadow-sm">
                                <i class="fas fa-edit fa-sm text-white-50"></i> Modifica
                            </a>
                            <a href="manutenzioni.php?id=<?= $id ?>" class="btn btn-warning btn-sm shadow-sm ml-2">
                                <i class="fas fa-tools fa-sm text-white-50"></i> Manutenzioni
                            </a>
                            <a href="makePDF.php?id=<?= $id ?>"  class="btn btn-info btn-sm shadow-sm ml-2">
                                <i class="fas fa-print fa-sm text-white-50"></i> Stampa Scheda
                            </a>
                            <a href="qrcode?id=<?= $id ?>" class="btn btn-indigo btn-sm shadow-sm ml-2">
                                <i class="fas fa-qrcode fa-sm text-white-50"></i> QR Code
                            </a>
                        </div>
                    </div>
                    
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="home">Macchinari</a></li>
                        <li class="breadcrumb-item"><a href="lista_macchinari">Lista Macchinari</a></li>
                        <li class="breadcrumb-item active">Dettaglio</li>
                    </ol>
                    
                    <div class="row">
                        <!-- Colonna principale con dettagli -->
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        Informazioni Macchinario
                                    </h6>
                                    <span class="badge badge-<?= $statoClass ?> px-3 py-2">
                                        <?= $statoText ?> <?= $eta ?> anni
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="row no-gutters">
                                        <div class="col-md-4 mb-3">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Matricola
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= htmlspecialchars($macchinario['matricola']) ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Tipologia
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= htmlspecialchars($macchinario['tipologia']) ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Data Acquisto
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= date('d/m/Y', strtotime($macchinario['data_acquisto'])) ?>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Rif. Fattura
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= htmlspecialchars($macchinario['rif_fattura'] ?: '-') ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Fornitore
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= htmlspecialchars($macchinario['fornitore']) ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Locazione Documenti
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= htmlspecialchars($macchinario['locazione_documenti'] ?: '-') ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row no-gutters">
                                        <div class="col-md-4 mb-3">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Marca
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= htmlspecialchars($macchinario['marca'] ?: '-') ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Modello
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= htmlspecialchars($macchinario['modello']) ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Anno di Costruzione
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= htmlspecialchars($macchinario['anno_costruzione'] ?: '-') ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($macchinario['note'])): ?>
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Note
                                            </div>
                                            <div class="p-3 bg-gray-100 rounded">
                                                <?= nl2br(htmlspecialchars($macchinario['note'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Data Inserimento
                                            </div>
                                            <div class="text-gray-800">
                                                <?= date('d/m/Y H:i', strtotime($macchinario['data_creazione'])) ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Ultimo Aggiornamento
                                            </div>
                                            <div class="text-gray-800">
                                                <?= date('d/m/Y H:i', strtotime($macchinario['data_aggiornamento'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Manutenzioni recenti -->
                            <?php if ($hasManutenzioni): ?>
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-tools mr-1"></i> Manutenzioni Recenti
                                    </h6>
                                    <a href="manutenzioni?mac_id=<?= $id ?>" class="btn btn-sm btn-primary">
                                        Vedi Tutte <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                                <div class="card-body">
                                    <?php if (count($manutenzioni) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm" width="100%" cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th>Data</th>
                                                        <th>Tipo</th>
                                                        <th>Operatore</th>
                                                        <th>Descrizione</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($manutenzioni as $manutenzione): ?>
                                                        <tr>
                                                            <td><?= date('d/m/Y', strtotime($manutenzione['data_manutenzione'])) ?></td>
                                                            <td><?= htmlspecialchars($manutenzione['tipo_id']) ?></td>
                                                            <td><?= htmlspecialchars($manutenzione['operatore']) ?></td>
                                                            <td><?= htmlspecialchars($manutenzione['descrizione']) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle mr-1"></i> Nessuna manutenzione registrata per questo macchinario.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Colonna laterale con info aggiuntive -->
                        <div class="col-lg-4">
                            <!-- Scheda riassuntiva -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-info-circle mr-1"></i> Stato Macchinario
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-4">
                                        <div class="position-relative d-inline-block">
                                            <i class="fas fa-cog fa-6x text-gray-300"></i>
                                            <span class="position-absolute bottom-0 right-0 transform-translate-middle badge rounded-pill bg-<?= $statoClass ?> p-2">
                                                <i class="fas fa-<?= ($statoClass == 'success' || $statoClass == 'info') ? 'check' : 'exclamation' ?>"></i>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <h4 class="text-center mb-3">
                                        <?= htmlspecialchars($macchinario['marca'] ?: '') ?> 
                                        <?= htmlspecialchars($macchinario['modello']) ?>
                                    </h4>
                                    
                                    <div class="mb-3">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                                            Età Macchinario
                                        </div>
                                        <div class="progress mb-1">
                                            <?php 
                                            // Percentuale di età rispetto a 10 anni (vita utile stimata)
                                            $percentualeEta = min(100, ($eta / 10) * 100);
                                            ?>
                                            <div class="progress-bar bg-<?= $statoClass ?>" role="progressbar" 
                                                style="width: <?= $percentualeEta ?>%" 
                                                aria-valuenow="<?= $eta ?>" aria-valuemin="0" aria-valuemax="10">
                                                <?= $eta ?> anni
                                            </div>
                                        </div>
                                        
                                    </div>
                                    
                                    <?php if ($hasManutenzioni): ?>
                                    <div class="mb-3">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                                            Ultima Manutenzione
                                        </div>
                                        <?php if (count($manutenzioni) > 0): 
                                            $ultimaManutenzione = new DateTime($manutenzioni[0]['data_manutenzione']);
                                            $giorniTrascorsi = $ultimaManutenzione->diff($oggi)->days;
                                            $statoManutenzioneBadge = $giorniTrascorsi > 180 ? 'danger' : 'success';
                                        ?>
                                            <div class="font-weight-bold text-<?= $statoManutenzioneBadge ?>">
                                                <?= date('d/m/Y', strtotime($manutenzioni[0]['data_manutenzione'])) ?>
                                                (<?= $giorniTrascorsi ?> giorni fa)
                                            </div>
                                            <div class="text-xs text-gray-600">
                                                <?= htmlspecialchars($manutenzioni[0]['tipo_id']) ?> - 
                                                <?= htmlspecialchars($manutenzioni[0]['operatore']) ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-warning">
                                                Nessuna manutenzione registrata
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="text-center mt-4">
                                        <a href="manutenzioni?mac_id=<?= $id ?>&action=new" class="btn btn-sm btn-primary">
                                            <i class="fas fa-tools mr-1"></i> Nuova Manutenzione
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Allegati del macchinario -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-file-alt mr-1"></i> Allegati
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php if ($hasAllegati): ?>
                                        <ul class="list-group">
                                            <?php foreach ($allegati as $allegato): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="fas fa-file-<?= getFileIcon($allegato['tipo_file']) ?> mr-2"></i>
                                                        <?= htmlspecialchars($allegato['nome_file']) ?>
                                                        <span class="badge badge-info ml-2"><?= htmlspecialchars(ucfirst($allegato['categoria'])) ?></span>
                                                        <?php if (!empty($allegato['descrizione'])): ?>
                                                            <small class="d-block text-muted">
                                                                <?= htmlspecialchars($allegato['descrizione']) ?>
                                                            </small>
                                                        <?php endif; ?>
                                                        <small class="d-block text-muted">
                                                            <?= date('d/m/Y', strtotime($allegato['data_caricamento'])) ?>
                                                            (<?= formatFileSize($allegato['dimensione']) ?>)
                                                        </small>
                                                    </div>
                                                    <div>
                                                        <a href="allegati/download?id=<?= $allegato['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle mr-1"></i> Nessun allegato disponibile per questo macchinario.
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="text-center mt-3">
                                        <a href="edit_macchinario?id=<?= $id ?>#allegati" class="btn btn-sm btn-primary">
                                            <i class="fas fa-file-upload mr-1"></i> Gestisci Allegati
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            
            <script>
                function printDetails() {
                    window.print();
                }
            </script>
            
            <?php
            // Helper per determinare l'icona del file
            function getFileIcon($fileType) {
                if (empty($fileType)) return 'alt';
                
                $type = strtolower(pathinfo($fileType, PATHINFO_EXTENSION));
                if (empty($type)) {
                    // Try to get the MIME type part
                    $parts = explode('/', $fileType);
                    $type = end($parts);
                }
                
                switch ($type) {
                    case 'pdf':
                        return 'pdf';
                    case 'doc':
                    case 'docx':
                    case 'msword':
                    case 'vnd.openxmlformats-officedocument.wordprocessingml.document':
                        return 'word';
                    case 'xls':
                    case 'xlsx':
                    case 'vnd.ms-excel':
                    case 'vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                        return 'excel';
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                    case 'gif':
                    case 'image':
                        return 'image';
                    default:
                        return 'alt';
                }
            }
            
            // Helper per formattare la dimensione del file
            function formatFileSize($bytes) {
                if ($bytes >= 1073741824) {
                    return number_format($bytes / 1073741824, 2) . ' GB';
                } elseif ($bytes >= 1048576) {
                    return number_format($bytes / 1048576, 2) . ' MB';
                } elseif ($bytes >= 1024) {
                    return number_format($bytes / 1024, 2) . ' KB';
                } else {
                    return $bytes . ' bytes';
                }
            }
            ?>
            
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>
</html>