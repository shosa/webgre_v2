<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';

// Get database instance
$pdo = getDbInstance();

// Check if ID was passed
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID manutenzione non specificato.";
    header("Location: manutenzioni");
    exit;
}

$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['error'] = "ID manutenzione non valido.";
    header("Location: manutenzioni");
    exit;
}

// Process operations (approve, reject)
$operationMessage = '';
$operationType = '';

// Approve maintenance
if (isset($_GET['approve']) && $_GET['approve'] == 1) {
    try {
        $stmt = $pdo->prepare("UPDATE mac_manutenzioni SET 
            stato = 'approvata', 
            approvata_da = ?, 
            data_approvazione = NOW() 
            WHERE id = ?");
            
        $result = $stmt->execute([
            $_SESSION['user_name'] ?? 'Admin',
            $id
        ]);
        
        if ($result) {
            $operationMessage = "Manutenzione approvata con successo.";
            $operationType = "success";
        } else {
            $operationMessage = "Errore durante l'approvazione.";
            $operationType = "danger";
        }
    } catch (PDOException $e) {
        $operationMessage = "Errore durante l'approvazione: " . $e->getMessage();
        $operationType = "danger";
    }
}

// Reject maintenance
if (isset($_POST['action']) && $_POST['action'] === 'reject') {
    try {
        $stmt = $pdo->prepare("UPDATE mac_manutenzioni SET 
            stato = 'rifiutata', 
            approvata_da = ?, 
            note_approvazione = ?,
            data_approvazione = NOW() 
            WHERE id = ?");
            
        $result = $stmt->execute([
            $_SESSION['user_name'] ?? 'Admin',
            $_POST['note_rifiuto'],
            $id
        ]);
        
        if ($result) {
            $operationMessage = "Manutenzione rifiutata con successo.";
            $operationType = "warning";
        } else {
            $operationMessage = "Errore durante il rifiuto.";
            $operationType = "danger";
        }
    } catch (PDOException $e) {
        $operationMessage = "Errore durante l'operazione: " . $e->getMessage();
        $operationType = "danger";
    }
}

// Retrieve maintenance data
try {
    $stmt = $pdo->prepare("
        SELECT m.*, a.matricola, a.tipologia, a.fornitore, a.modello, t.nome as tipo_nome, t.colore as tipo_colore 
        FROM mac_manutenzioni m 
        JOIN mac_anag a ON m.mac_id = a.id 
        JOIN mac_manutenzioni_tipi t ON m.tipo_id = t.id 
        WHERE m.id = ?");
    $stmt->execute([$id]);
    $manutenzione = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$manutenzione) {
        $_SESSION['error'] = "Manutenzione non trovata.";
        header("Location: manutenzioni");
        exit;
    }
    
    // Get attachments
    $stmt = $pdo->prepare("SELECT * FROM mac_manutenzioni_allegati WHERE manutenzione_id = ? ORDER BY data_caricamento");
    $stmt->execute([$id]);
    $allegati = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Errore nel recupero dei dati: " . $e->getMessage();
    header("Location: manutenzioni");
    exit;
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
                    
                    <?php if (!empty($operationMessage)): ?>
                    <div class="alert alert-<?= $operationType ?> alert-dismissible fade show" role="alert">
                        <?= $operationMessage ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-clipboard-list text-gray-500 mr-2"></i>
                            Dettaglio Manutenzione
                        </h1>
                        <div>
                            <?php if ($manutenzione['stato'] == 'richiesta' || $manutenzione['stato'] == 'completata'): ?>
                            <a href="?id=<?= $id ?>&approve=1" class="btn btn-success btn-sm shadow-sm">
                                <i class="fas fa-check fa-sm text-white-50"></i> Approva
                            </a>
                            <button type="button" class="btn btn-warning btn-sm shadow-sm ml-2" id="showRejectModal">
                                <i class="fas fa-times fa-sm text-white-50"></i> Rifiuta
                            </button>
                            <?php endif; ?>
                            <a href="manutenzioni?mac_id=<?= $manutenzione['mac_id'] ?>" class="btn btn-primary btn-sm shadow-sm ml-2">
                                <i class="fas fa-tools fa-sm text-white-50"></i> Manutenzioni Macchinario
                            </a>
                            <a href="dettaglio_macchinario?id=<?= $manutenzione['mac_id'] ?>" class="btn btn-info btn-sm shadow-sm ml-2">
                                <i class="fas fa-clipboard-list fa-sm text-white-50"></i> Scheda Macchinario
                            </a>
                            <a href="#" onclick="window.print()" class="btn btn-secondary btn-sm shadow-sm ml-2 d-print-none">
                                <i class="fas fa-print fa-sm text-white-50"></i> Stampa
                            </a>
                        </div>
                    </div>
                    
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="home">Macchinari</a></li>
                        <li class="breadcrumb-item"><a href="manutenzioni">Manutenzioni</a></li>
                        <li class="breadcrumb-item active">Dettaglio</li>
                    </ol>
                    
                    <div class="row">
                        <!-- Left Column - Maintenance Details -->
                        <div class="col-lg-8">
                            <!-- Status Card -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        Dettagli Intervento
                                    </h6>
                                    <?php
                                    $statusLabels = [
                                        'richiesta' => '<span class="badge badge-warning px-3 py-2">In Attesa di Revisione</span>',
                                        'in_corso' => '<span class="badge badge-info px-3 py-2">In Corso</span>',
                                        'completata' => '<span class="badge badge-primary px-3 py-2">Completata</span>',
                                        'approvata' => '<span class="badge badge-success px-3 py-2">Approvata</span>',
                                        'rifiutata' => '<span class="badge badge-danger px-3 py-2">Rifiutata</span>'
                                    ];
                                    echo $statusLabels[$manutenzione['stato']] ?? '<span class="badge badge-secondary px-3 py-2">Sconosciuto</span>';
                                    ?>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Tipo Manutenzione
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold">
                                                <span class="badge badge-pill px-3 py-2" style="background-color: <?= htmlspecialchars($manutenzione['tipo_colore']) ?>; color: white;">
                                                    <?= htmlspecialchars($manutenzione['tipo_nome']) ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Data Intervento
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= date('d/m/Y', strtotime($manutenzione['data_manutenzione'])) ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Operatore / Tecnico
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= htmlspecialchars($manutenzione['operatore']) ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-md-6 mb-3">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Tempo Impiegato
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $manutenzione['tempo_impiegato'] ? $manutenzione['tempo_impiegato'] . ' ore' : 'Non specificato' ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Costo Intervento
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $manutenzione['costo'] ? '€ ' . number_format($manutenzione['costo'], 2, ',', '.') : 'Non specificato' ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Descrizione Intervento
                                            </div>
                                            <div class="p-3 bg-light rounded">
                                                <?= nl2br(htmlspecialchars($manutenzione['descrizione'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($manutenzione['lavori_eseguiti'])): ?>
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Lavori Eseguiti
                                            </div>
                                            <div class="p-3 bg-light rounded">
                                                <?= nl2br(htmlspecialchars($manutenzione['lavori_eseguiti'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($manutenzione['ricambi_utilizzati'])): ?>
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Ricambi Utilizzati
                                            </div>
                                            <div class="p-3 bg-light rounded">
                                                <?= nl2br(htmlspecialchars($manutenzione['ricambi_utilizzati'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($manutenzione['stato'] == 'rifiutata' && !empty($manutenzione['note_approvazione'])): ?>
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Motivo Rifiuto
                                            </div>
                                            <div class="p-3 bg-light rounded border-left-danger">
                                                <?= nl2br(htmlspecialchars($manutenzione['note_approvazione'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Attachments Card -->
                            <?php if (count($allegati) > 0): ?>
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-paperclip mr-1"></i> Allegati (<?= count($allegati) ?>)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php foreach ($allegati as $allegato): ?>
                                            <div class="col-md-4 mb-3">
                                                <div class="card">
                                                    <?php 
                                                    $fileType = strtolower(pathinfo($allegato['nome_file'], PATHINFO_EXTENSION));
                                                    $isImage = in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']);
                                                    
                                                    if ($isImage): 
                                                    ?>
                                                        <div class="text-center">
                                                            <img src="<?= BASE_URL . '/' . $allegato['percorso_file'] ?>" class="card-img-top img-thumbnail" alt="<?= htmlspecialchars($allegato['nome_file']) ?>" style="max-height: 150px; object-fit: contain;">
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="text-center p-3">
                                                            <?php
                                                            $iconClass = 'fa-file';
                                                            switch ($fileType) {
                                                                case 'pdf': $iconClass = 'fa-file-pdf'; break;
                                                                case 'doc': 
                                                                case 'docx': $iconClass = 'fa-file-word'; break;
                                                                case 'xls': 
                                                                case 'xlsx': $iconClass = 'fa-file-excel'; break;
                                                                case 'ppt': 
                                                                case 'pptx': $iconClass = 'fa-file-powerpoint'; break;
                                                                case 'zip': 
                                                                case 'rar': $iconClass = 'fa-file-archive'; break;
                                                                case 'txt': $iconClass = 'fa-file-alt'; break;
                                                            }
                                                            ?>
                                                            <i class="fas <?= $iconClass ?> fa-4x text-gray-400"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="card-body p-2">
                                                        <p class="card-text small text-truncate">
                                                            <?= htmlspecialchars($allegato['nome_file']) ?>
                                                        </p>
                                                        <p class="card-text small text-muted">
                                                            <?= round($allegato['dimensione'] / 1024, 1) ?> KB
                                                        </p>
                                                        <a href="download_allegato?id=<?= $allegato['id'] ?>" class="btn btn-sm btn-primary btn-block">
                                                            <i class="fas fa-download mr-1"></i> Scarica
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Right Column - Machine Info and Meta -->
                        <div class="col-lg-4">
                            <!-- Machine Info Card -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-cog mr-1"></i> Informazioni Macchinario
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <i class="fas fa-cog fa-4x text-gray-300"></i>
                                    </div>
                                    
                                    <h4 class="text-center mb-3">
                                        <?= htmlspecialchars($manutenzione['fornitore']) ?> <?= htmlspecialchars($manutenzione['modello']) ?>
                                    </h4>
                                    
                                    <div class="mb-3">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Matricola
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= htmlspecialchars($manutenzione['matricola']) ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Tipologia
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= htmlspecialchars($manutenzione['tipologia']) ?>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center mt-4">
                                        <a href="dettaglio_macchinario?id=<?= $manutenzione['mac_id'] ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-clipboard-list mr-1"></i> Scheda Completa
                                        </a>
                                        
                                        <a href="manutenzioni?mac_id=<?= $manutenzione['mac_id'] ?>" class="btn btn-primary btn-sm ml-2">
                                            <i class="fas fa-tools mr-1"></i> Tutte le Manutenzioni
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Meta Info Card -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-info-circle mr-1"></i> Metadati
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Data Registrazione
                                        </div>
                                        <div class="text-gray-800">
                                            <?= date('d/m/Y H:i', strtotime($manutenzione['data_creazione'])) ?>
                                        </div>
                                    </div>
                                    
                                    <?php if ($manutenzione['stato'] == 'approvata' || $manutenzione['stato'] == 'rifiutata'): ?>
                                    <div class="mb-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            <?= $manutenzione['stato'] == 'approvata' ? 'Approvata da' : 'Rifiutata da' ?>
                                        </div>
                                        <div class="text-gray-800">
                                            <?= htmlspecialchars($manutenzione['approvata_da']) ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Data <?= $manutenzione['stato'] == 'approvata' ? 'Approvazione' : 'Rifiuto' ?>
                                        </div>
                                        <div class="text-gray-800">
                                            <?= date('d/m/Y H:i', strtotime($manutenzione['data_approvazione'])) ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="mb-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            ID Manutenzione
                                        </div>
                                        <div class="text-gray-800">
                                            #<?= $manutenzione['id'] ?>
                                        </div>
                                    </div>
                                    
                                    <?php if ($manutenzione['is_programmata']): ?>
                                    <div class="alert alert-info mt-3 mb-0">
                                        <i class="fas fa-calendar-check mr-1"></i>
                                        Questa è una manutenzione programmata.
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reject Modal -->
            <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="rejectModalLabel">Rifiuta Manutenzione</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="reject">
                            <div class="modal-body">
                                <p>Stai per rifiutare la manutenzione eseguita da <strong><?= htmlspecialchars($manutenzione['operatore']) ?></strong>.</p>
                                <div class="form-group">
                                    <label for="note_rifiuto"><strong>Motivo del rifiuto</strong></label>
                                    <textarea name="note_rifiuto" id="note_rifiuto" class="form-control" rows="3" required placeholder="Inserisci il motivo del rifiuto..."></textarea>
                                </div>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    Il tecnico verrà informato del rifiuto e del motivo.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                                <button type="submit" class="btn btn-warning">Rifiuta Manutenzione</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            
            <script>
                $(document).ready(function() {
                    // Show reject modal
                    $('#showRejectModal').click(function() {
                        $('#rejectModal').modal('show');
                    });
                });
            </script>
            
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>