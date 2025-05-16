<?php
/**
 * File: continue_ddt.php
 * 
 * Gestisce la visualizzazione e la modifica dei dettagli di un DDT.
 */
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/utils/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';
require_once BASE_PATH . '/vendor/autoload.php';

// Recupera e valida il progressivo
$progressivo = filter_input(INPUT_GET, 'progressivo', FILTER_VALIDATE_INT);
if (!$progressivo) {
    $_SESSION['failure'] = "ID documento non valido";
    header('location: documenti.php');
    exit();
}

// Recupera i file Excel presenti nella directory
$dir = 'src/' . $progressivo;
$files = glob($dir . '/*.xlsx');

try {
    // Ottieni istanza del database
    $conn = getDbInstance();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Recupera i dati del documento
    $stmt = $conn->prepare("SELECT * FROM exp_documenti WHERE id = :id ");
    $stmt->bindParam(':id', $progressivo, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        $_SESSION['failure'] = "Documento non trovato";
        header('location: documenti.php');
        exit();
    }

    $documento = $stmt->fetch(PDO::FETCH_ASSOC);

    // Recupera gli articoli
    $stmt = $conn->prepare("SELECT * FROM exp_dati_articoli WHERE id_documento = :id_documento ORDER BY is_mancante ASC, voce_doganale ASC , codice_articolo ASC");
    $stmt->bindParam(':id_documento', $progressivo, PDO::PARAM_INT);
    $stmt->execute();
    $articoli = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recupera i dati del terzista
    $stmt = $conn->prepare("SELECT * FROM exp_terzisti WHERE id = :id");
    $stmt->bindParam(':id', $documento['id_terzista'], PDO::PARAM_INT);
    $stmt->execute();
    $terzista = $stmt->fetch(PDO::FETCH_ASSOC);

    // Recupera i lanci associati
    $stmt = $conn->prepare("SELECT lancio, articolo, paia FROM exp_dati_lanci_ddt WHERE id_doc = :id_doc");
    $stmt->bindParam(':id_doc', $progressivo, PDO::PARAM_INT);
    $stmt->execute();
    $lanci = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcola il totale
    $total = 0;
    foreach ($articoli as $articolo) {
        $total += round($articolo['qta_reale'] * $articolo['prezzo_unitario'], 2);
    }

    // Ottieni il conteggio delle mancanze
    $stmt = $conn->prepare("SELECT COUNT(*) FROM exp_dati_mancanti WHERE id_documento = :id_documento");
    $stmt->bindParam(':id_documento', $progressivo, PDO::PARAM_INT);
    $stmt->execute();
    $mancanzeCount = $stmt->fetchColumn();

} catch (PDOException $e) {
    $_SESSION['failure'] = "Errore nel recupero dei dati: " . $e->getMessage();
    header('location: documenti.php');
    exit();
}

/**
 * Ottiene i codici doganali univoci dagli articoli
 * 
 * @param array $articoli Lista degli articoli
 * @return array Lista dei codici doganali univoci
 */
function getUniqueDoganaleCodes($articoli)
{
    $codes = [];
    foreach ($articoli as $articolo) {
        if (!empty($articolo['voce_doganale']) && !in_array($articolo['voce_doganale'], $codes)) {
            $codes[] = $articolo['voce_doganale'];
        }
    }
    return $codes;
}

// Includi l'header della pagina
include(BASE_PATH . "/components/header.php");
?>

<style>
    /* CSS Style del document non modificato */
    .card {
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        margin-bottom: 20px;
        border: none;
    }

    .card:hover {
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    .card-header {
        border-radius: 12px 12px 0 0 !important;
        padding: 15px 20px;
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
    }

    .info-card {
        height: 100%;
        transition: all 0.3s ease;
    }

    .info-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .info-card .card-body {
        padding: 15px;
    }

    .info-card .card-icon {
        transition: transform 0.3s ease;
    }

    .info-card:hover .card-icon {
        transform: scale(1.1);
    }

    /* Info cards */
    .ddt-number {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .ddt-date {
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.9);
    }

    .info-title {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 10px;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .info-text {
        font-size: 0.85rem;
        color: #6c757d;
    }

    /* Tabella */
    .table-responsive {
        border-radius: 0 0 12px 12px;
        overflow: hidden;
    }

    .table {
        margin-bottom: 0;
    }

    .table th {
        background-color: #f8f9fc;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 15px;
        border-top: none;
    }

    .table td {
        padding: 12px 15px;
        vertical-align: middle;
    }

    .table [contenteditable="true"] {
        padding: 8px 10px;
        border-radius: 4px;
        transition: all 0.2s ease;
        cursor: pointer;
        min-height: 38px;
    }

    .table [contenteditable="true"]:hover {
        background-color: #f8f9fc;
    }

    .table [contenteditable="true"]:focus {
        outline: none;
        background-color: #f0f7ff;
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
    }

    /* Badges e bottoni */
    .badge {
        padding: 6px 10px;
        font-weight: 600;
        font-size: 0.75rem;
        border-radius: 30px;
    }

    .btn {
        border-radius: 8px;
        padding: 8px 15px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-icon {
        display: inline-flex;
        align-items: center;
    }

    .btn-icon i {
        margin-right: 8px;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Attachments */
    .attachment-icon {
        font-size: 2rem;
        color: #28a745;
        transition: all 0.3s ease;
    }

    .attachment-icon:hover {
        transform: scale(1.1);
    }

    /* Modali */
    .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        border-radius: 12px 12px 0 0;
        background-color: #f8f9fc;
        padding: 15px 20px;
    }

    .modal-body {
        padding: 20px;
    }

    .modal-footer {
        padding: 15px 20px;
        border-top: 1px solid #e9ecef;
        border-radius: 0 0 12px 12px;
    }

    /* Form controls */
    .form-control {
        border-radius: 8px;
        border: 1px solid #d1d3e2;
        padding: 10px 15px;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    .form-label {
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 8px;
        color: #5a5c69;
    }

    /* Scrollbar personalizzata */
    .custom-scrollbar {
        max-height: 130px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #4e73df #f8f9fc;
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f8f9fc;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #4e73df;
        border-radius: 10px;
    }

    /* Tabella totali */
    .totals-row {
        background-color: #f8f9fc;
        font-weight: 700;
    }

    .totals-value {
        background-color: #e8f4f0 !important;
        color: #1cc88a;
        font-weight: 700;
        font-size: 1.1rem;
    }

    /* Action buttons container */
    .action-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
    }

    .action-buttons .btn {
        margin-bottom: 0;
    }

    /* Stile per l'intestazione dei mancanti */
    .mancanti-header {
        background-color: #6c757d;
        color: white;
        font-weight: bold;
    }

    /* Stile per le righe dei mancanti */
    .table-info td {
        background-color: #e3f6fc;
    }

    /* Responsiveness */
    @media (max-width: 768px) {
        .action-buttons {
            justify-content: center;
        }

        .info-card {
            margin-bottom: 15px;
        }

        .table td,
        .table th {
            padding: 8px;
        }

        .btn {
            padding: 6px 12px;
            font-size: 0.9rem;
        }
    }
</style>

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
                    <?php require_once(BASE_PATH . "/utils/alerts.php"); ?>

                    <div class="card mb-4">
                        <div class="card-body py-3">
                            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                                <h1 class="h3 mb-0 text-gray-800">
                                    <span class="text-primary">DA CONFERMARE</span> - DDT n° <?php echo $progressivo; ?>
                                </h1>
                                <div class="text-right"> <!-- Aggiunto text-right per garantire l'allineamento -->
                                    <?php if ($documento['stato'] == 'Aperto'): ?>
                                        <button class="btn btn-success float-right" onclick="completaDdt()">
                                            <!-- Aggiunto float-right -->
                                            <i class="fas fa-check-circle mr-1"></i> TERMINA
                                        </button>
                                    <?php else: ?>
                                        <span class="badge badge-success p-2 float-right">COMPLETATO</span>
                                        <!-- Aggiunto float-right -->
                                    <?php endif; ?>
                                </div>
                            </div>


                            <ol class="breadcrumb mb-4">
                                <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="documenti.php">Documenti</a></li>
                                <li class="breadcrumb-item active">Dettaglio DDT</li>
                            </ol>

                            <!-- Informazioni Documento -->
                            <div class="row mb-4">
                                <!-- Documento Box -->
                                <div class="col-lg-3 mb-3">
                                    <div class="card bg-warning text-white shadow info-card">
                                        <div class="card-body py-3">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                        Documento</div>
                                                    <div class="ddt-number">DDT N° <?php echo $progressivo; ?></div>
                                                    <div class="ddt-date">Del: <?php echo $documento['data']; ?></div>
                                                </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-file-invoice fa-3x text-white-50 card-icon"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Destinatario Box -->
                                <div class="col-lg-3 mb-3">
                                    <div class="card bg-white shadow info-card">
                                        <div class="card-body py-3">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="info-title text-primary">Destinatario</div>
                                                    <div class="info-value">
                                                        <?php echo htmlspecialchars($terzista['ragione_sociale']); ?>
                                                    </div>
                                                    <div class="info-text">
                                                        <?php echo htmlspecialchars($terzista['indirizzo_1']); ?>
                                                    </div>
                                                    <?php if (!empty($terzista['indirizzo_2'])): ?>
                                                        <div class="info-text">
                                                            <?php echo htmlspecialchars($terzista['indirizzo_2']); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($terzista['indirizzo_3'])): ?>
                                                        <div class="info-text">
                                                            <?php echo htmlspecialchars($terzista['indirizzo_3']); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="info-text font-weight-bold">
                                                        <?php echo htmlspecialchars($terzista['nazione']); ?>
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-user-tie fa-2x text-primary card-icon"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lanci Associati Box -->
                                <div class="col-lg-3 mb-3">
                                    <div class="card bg-info text-white shadow info-card">
                                        <div class="card-body py-3">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="info-title text-white">Lanci Associati</div>
                                                    <div class="custom-scrollbar">
                                                        <ul class="list-unstyled mb-0">
                                                            <?php foreach ($lanci as $lancio): ?>
                                                                <li class="mb-1">
                                                                    <span
                                                                        class="badge badge-light mr-1"><?php echo $lancio['lancio']; ?></span>
                                                                    <small>
                                                                        <strong>Art:</strong>
                                                                        <?php echo $lancio['articolo']; ?> |
                                                                        <strong>Paia:</strong>
                                                                        <?php echo $lancio['paia']; ?>
                                                                    </small>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-tags fa-2x text-white-50 card-icon"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Allegati Box -->
                                <div class="col-lg-3 mb-3">
                                    <div class="card bg-white shadow info-card">
                                        <div class="card-body py-3">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="info-title text-success">Allegati</div>
                                                    <div class="custom-scrollbar">
                                                        <div class="d-flex flex-wrap">
                                                            <?php foreach ($files as $file): ?>
                                                                <div class="text-center mx-2 mb-2">
                                                                    <a href="<?php echo $file; ?>" download
                                                                        class="text-decoration-none">
                                                                        <i class="fas fa-file-excel attachment-icon"></i>
                                                                        <div class="small mt-1">
                                                                            <?php echo basename($file); ?>
                                                                        </div>
                                                                    </a>
                                                                </div>
                                                            <?php endforeach; ?>
                                                            <?php if (empty($files)): ?>
                                                                <div class="text-muted">Nessun allegato disponibile</div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <?php if (!empty($files)): ?>
                                                        <div class="mt-3 text-center">
                                                            <a href="download_all_attachments.php?progressivo=<?php echo $progressivo; ?>"
                                                                class="btn btn-sm btn-success">
                                                                <i class="fas fa-download mr-1"></i> Scarica tutti (ZIP)
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-paperclip fa-2x text-success card-icon"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="card mb-4">
                                <div class="card-body ">
                                    <div class="row">
                                        <div class="col-md-5 mb-2">
                                            <div class="btn-group" role="group" aria-label="Azioni">
                                                <button class="btn btn-secondary" onclick="openModal()">
                                                    <i class="fal fa-weight mr-2"></i> Pesi e Aspetto Merce
                                                </button>
                                                <button class="btn btn-primary" onclick="openAutorizzazioneModal()">
                                                    <i class="fal fa-pencil-alt mr-2"></i> Autorizzazione
                                                </button>
                                                <button class="btn btn-light text-primary border-primary" onclick="openCommentoModal()">
                                                    <i class="fal fa-pencil-alt mr-2"></i> Commento
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-7 mb-2">
                                            <div class="action-buttons justify-content-md-end">
                                                <!-- Primo gruppo di pulsanti -->
                                                <div class="btn-group mr-2" role="group"
                                                    aria-label="Gestione documento">
                                                    <?php if ($documento['first_boot'] == 1): ?>
                                                        <button class="btn btn-success" onclick="cercaNcECosti()">
                                                            <i class="fal fa-search-plus mr-2"></i> Cerca Voci e Costi
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-warning" onclick="elaboraMancanti()">
                                                        <i class="fal fa-sync-alt mr-2"></i> Elabora Mancanti
                                                    </button>
                                                    <button class="btn btn-info" disabled>
                                                        <i class="fal fa-exclamation-triangle mr-2"></i> Mancanze
                                                        <?php if ($mancanzeCount > 0): ?>
                                                            <span
                                                                class="badge badge-danger"><?php echo $mancanzeCount; ?></span>
                                                        <?php endif; ?>
                                                    </button>
                                                    <button class="btn btn-indigo" onclick="openMancantiModal()">
                                                        <i class="fal fa-plus-circle mr-2"></i> Aggiungi Mancanti
                                                    </button>
                                                </div>

                                                <!-- Secondo gruppo di pulsanti (esportazione e visualizzazione) -->
                                                <div class="btn-group" role="group" aria-label="Esportazione">
                                                    <button class="btn btn-light text-success border-success" onclick="exportToExcel()">
                                                        <i class="fal fa-file-excel mr-2"></i> Excel
                                                    </button>
                                                    <a target="_blank"
                                                        href="view_ddt_export?progressivo=<?php echo $progressivo; ?>"
                                                        class="btn btn-light text-danger border-danger">
                                                        <i class="fal fa-file-invoice mr-2"></i> PDF
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabella Articoli con mancanti raggruppati alla fine -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Corpo Documento</h6>
                                    <span class="badge badge-pill badge-primary"><?php echo count($articoli); ?>
                                        articoli</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="dataTable" width="100%"
                                        cellspacing="0">
                                        <thead>
                                            <tr class="text-dark font-weight-bold">
                                                <th>CODICE ARTICOLO</th>
                                                <th>DESCRIZIONE</th>
                                                <th>VOCE DOGANALE</th>
                                                <th>UM</th>
                                                <th>QTA</th>
                                                <th>QTA REALE</th>
                                                <th>COSTO UNIT.</th>
                                                <th>TOTALE</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Ordiniamo gli articoli: prima i normali, poi i mancanti
                                            $articoliNormali = array_filter($articoli, function ($art) {
                                                return $art['is_mancante'] == 0;
                                            });
                                            $articoliMancanti = array_filter($articoli, function ($art) {
                                                return $art['is_mancante'] == 1;
                                            });

                                            // Raggruppiamo i mancanti per DDT di origine
                                            $mancantiByDDT = [];
                                            foreach ($articoliMancanti as $articolo) {
                                                $rif = $articolo['rif_mancante'] ?: 'Senza riferimento';
                                                if (!isset($mancantiByDDT[$rif])) {
                                                    $mancantiByDDT[$rif] = [];
                                                }
                                                $mancantiByDDT[$rif][] = $articolo;
                                            }

                                            // Prima visualizziamo gli articoli normali
                                            foreach ($articoliNormali as $articolo):
                                                $subtotal = round($articolo['qta_reale'] * $articolo['prezzo_unitario'], 2);
                                                $qta_mancante = $articolo['qta_originale'] - $articolo['qta_reale'];
                                                $style = ($qta_mancante > 0) ? 'style="background-color: #ffe8c3"' : '';
                                                ?>
                                                <tr>
                                                    <td contenteditable="false" style="background-color:#f0f0f0;">
                                                        <?php echo htmlspecialchars($articolo['codice_articolo']); ?>
                                                    </td>
                                                    <td contenteditable="true"
                                                        onBlur="updateData(<?php echo $articolo['id']; ?>, 'descrizione', this)">
                                                        <?php echo htmlspecialchars($articolo['descrizione']); ?>
                                                    </td>
                                                    <td contenteditable="true"
                                                        onBlur="updateData(<?php echo $articolo['id']; ?>, 'voce_doganale', this)">
                                                        <?php echo htmlspecialchars($articolo['voce_doganale']); ?>
                                                    </td>
                                                    <td contenteditable="false" style="background-color:#f0f0f0;">
                                                        <?php echo htmlspecialchars($articolo['um']); ?>
                                                    </td>
                                                    <td contenteditable="false" style="background-color:#f0f0f0;">
                                                        <?php echo htmlspecialchars($articolo['qta_originale']); ?>
                                                    </td>
                                                    <td contenteditable="true" <?php echo $style; ?>
                                                        onBlur="updateData(<?php echo $articolo['id']; ?>, 'qta_reale', this)">
                                                        <?php echo htmlspecialchars($articolo['qta_reale']); ?>
                                                    </td>
                                                    <td contenteditable="true"
                                                        onBlur="updateData(<?php echo $articolo['id']; ?>, 'prezzo_unitario', this)">
                                                        <?php echo htmlspecialchars($articolo['prezzo_unitario']); ?>
                                                    </td>
                                                    <td style="background-color:#d9fae2;">
                                                        <?php echo number_format($subtotal, 2, ',', '.'); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>

                                            <!-- Ora visualizziamo i mancanti, raggruppati per DDT di origine -->
                                            <?php foreach ($mancantiByDDT as $rif => $mancanti): ?>
                                                <tr class="mancanti-header bg-secondary text-white">
                                                    <td colspan="8" class="text-center font-weight-bold">
                                                        MANCANTI SU <?php echo htmlspecialchars($rif); ?>
                                                    </td>
                                                </tr>
                                                <?php foreach ($mancanti as $articolo):
                                                    $subtotal = round($articolo['qta_reale'] * $articolo['prezzo_unitario'], 2);
                                                    ?>
                                                    <tr class="table-info">
                                                        <td contenteditable="false" style="background-color:#e3f6fc;">
                                                            <?php echo htmlspecialchars($articolo['codice_articolo']); ?>
                                                        </td>
                                                        <td contenteditable="true"
                                                            onBlur="updateData(<?php echo $articolo['id']; ?>, 'descrizione', this)">
                                                            <?php echo htmlspecialchars($articolo['descrizione']); ?>
                                                        </td>
                                                        <td contenteditable="true"
                                                            onBlur="updateData(<?php echo $articolo['id']; ?>, 'voce_doganale', this)">
                                                            <?php echo htmlspecialchars($articolo['voce_doganale']); ?>
                                                        </td>
                                                        <td contenteditable="false" style="background-color:#e3f6fc;">
                                                            <?php echo htmlspecialchars($articolo['um']); ?>
                                                        </td>
                                                        <td contenteditable="false" style="background-color:#e3f6fc;">
                                                            <?php echo htmlspecialchars($articolo['qta_originale']); ?>
                                                        </td>
                                                        <td contenteditable="true"
                                                            onBlur="updateData(<?php echo $articolo['id']; ?>, 'qta_reale', this)">
                                                            <?php echo htmlspecialchars($articolo['qta_reale']); ?>
                                                        </td>
                                                        <td contenteditable="true"
                                                            onBlur="updateData(<?php echo $articolo['id']; ?>, 'prezzo_unitario', this)">
                                                            <?php echo htmlspecialchars($articolo['prezzo_unitario']); ?>
                                                        </td>
                                                        <td style="background-color:#d9fae2;">
                                                            <?php echo number_format($subtotal, 2, ',', '.'); ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="totals-row">
                                                <td colspan="7" class="text-right"><strong>Totale in €:</strong></td>
                                                <td id="totalValue" class="totals-value"
                                                    data-total="<?php echo $total; ?>">
                                                    <?php echo number_format($total, 2, ',', '.'); ?>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODALE PESI E ASPETTO MERCE -->
                    <div class="modal fade" id="pesiModal" tabindex="-1" aria-labelledby="pesiModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="pesiModalLabel">
                                        <i class="fas fa-weight mr-2"></i> Dati piede documento
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="aspettoMerce" class="form-label">ASPETTO MERCE:</label>
                                        <input type="text" class="form-control" id="aspettoMerce"
                                            placeholder="Es. Scatole, Pacchi, ecc.">
                                    </div>
                                    <div class="mb-3">
                                        <label for="numeroColli" class="form-label">NUMERO COLLI:</label>
                                        <input type="number" class="form-control" id="numeroColli"
                                            placeholder="Inserisci il numero di colli">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="pesoLordo" class="form-label">PESO LORDO (kg):</label>
                                                <input type="number" step="0.01" class="form-control" id="pesoLordo"
                                                    placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="pesoNetto" class="form-label">PESO NETTO (kg):</label>
                                                <input type="number" step="0.01" class="form-control" id="pesoNetto"
                                                    placeholder="0.00">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="trasportatore" class="form-label">TRASPORTATORE:</label>
                                        <input type="text" class="form-control" id="trasportatore"
                                            placeholder="Nome del trasportatore">
                                    </div>
                                    <div class="mb-3">
                                        <label for="consegnato" class="form-label">MATERIALE CONSEGNATO PER LA
                                            REALIZZAZIONE DI:</label>
                                        <input type="text" class="form-control" id="consegnato"
                                            placeholder="Tomaie , sottopiedi , parti di tomaia">
                                    </div>

                                    <div class="card mt-4">
                                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 font-weight-bold">Dettaglio Voci Doganali</h6>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="presentiSottopiedi"
                                                    onchange="toggleSottopiedi()">
                                                <label class="form-check-label" for="presentiSottopiedi">
                                                    Presenti SOTTOPIEDI
                                                </label>
                                            </div>
                                        </div>
                                        <div class="card-body p-0">
                                            <table class="table mb-0">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Voce Doganale</th>
                                                        <th>Peso Netto (kg)</th>
                                                        <th>Somma QTA</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="doganaleTableBody">
                                                    <!-- I dati verranno caricati dinamicamente -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger btn-icon" onclick="resetPesiData()">
                                        <i class="fas fa-trash-alt mr-1"></i> Resetta
                                    </button>
                                    <button type="button" class="btn btn-primary btn-icon" onclick="savePesiData()">
                                        <i class="fas fa-save mr-1"></i> Salva
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODALE AUTORIZZAZIONE -->
                    <div class="modal fade" id="autorizzazioneModal" tabindex="-1"
                        aria-labelledby="autorizzazioneModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="autorizzazioneModalLabel">
                                        <i class="fas fa-pencil-alt mr-2"></i> Modifica Autorizzazione
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="autorizzazione" class="form-label">AUTORIZZAZIONE:</label>
                                        <textarea class="form-control" id="autorizzazione" rows="4"
                                            placeholder="Inserisci il testo dell'autorizzazione"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger btn-icon"
                                        onclick="resetAutorizzazioneData()">
                                        <i class="fas fa-trash-alt mr-1"></i> Resetta
                                    </button>
                                    <button type="button" class="btn btn-primary btn-icon"
                                        onclick="saveAutorizzazioneData()">
                                        <i class="fas fa-save mr-1"></i> Salva
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODALE COMMENTO -->
                    <div class="modal fade" id="commentoModal" tabindex="-1" aria-labelledby="commentoModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="commentoModalLabel">
                                        <i class="fas fa-pencil-alt mr-2"></i> Aggiungi Commento
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="commento" class="form-label">COMMENTO:</label>
                                        <textarea class="form-control" id="commento" rows="4"
                                            placeholder="Inserisci il testo "></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger btn-icon" onclick="resetCommentoData()">
                                        <i class="fas fa-trash-alt mr-1"></i> Resetta
                                    </button>
                                    <button type="button" class="btn btn-primary btn-icon" onclick="saveCommentoData()">
                                        <i class="fas fa-save mr-1"></i> Salva
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODALE MANCANTI -->
                    <div class="modal fade" id="mancantiModal" tabindex="-1" aria-labelledby="mancantiModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="mancantiModalLabel">
                                        <i class="fas fa-dolly-flatbed mr-2"></i> Seleziona Mancanti da Aggiungere
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3 text-muted">
                                        <i class="fas fa-info-circle mr-1"></i> Seleziona i mancanti che desideri
                                        aggiungere al DDT corrente. Gli articoli selezionati verranno rimossi
                                        dall'elenco mancanti e aggiunti al DDT.
                                    </div>

                                    <div id="mancantiContainer">
                                        <div class="text-center py-5">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="sr-only">Caricamento...</span>
                                            </div>
                                            <p class="mt-2">Caricamento mancanti...</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-dismiss="modal">Annulla</button>
                                    <button type="button" class="btn btn-primary"
                                        onclick="aggiungiMancantiSelezionati()">
                                        <i class="fas fa-save mr-1"></i> Aggiungi Selezionati
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
</body>

<script src="<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
<script src="<?php echo BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?php echo BASE_URL ?>/js/sb-admin-2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.1.1/exceljs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.2/FileSaver.min.js"></script>

<script>
    /**
     * JavaScript per la gestione dei DDT
     */

    // =============================================
    // FUNZIONI DI ESPORTAZIONE E GESTIONE ARTICOLI
    // =============================================

    /**
     * Esporta i dati degli articoli in un file Excel
     */
    function exportToExcel() {
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('DDT');

        // Definizione delle colonne
        worksheet.columns = [
            { header: 'CODICE ARTICOLO', key: 'codice_articolo', width: 17 },
            { header: 'DESCRIZIONE', key: 'descrizione', width: 75 },
            { header: 'VOCE DOGANALE', key: 'voce_doganale', width: 15 },
            { header: 'UM', key: 'um', width: 10 },
            { header: 'QTA', key: 'qta_reale', width: 10 },
            { header: 'TOTALE', key: 'subtotal', width: 15 },
        ];

        // Aggiunta delle righe con i dati degli articoli
        <?php foreach ($articoli as $articolo): ?>
            worksheet.addRow({
                codice_articolo: '<?php echo addslashes($articolo['codice_articolo']); ?>',
                descrizione: '<?php echo addslashes($articolo['descrizione']); ?>',
                voce_doganale: '<?php echo addslashes($articolo['voce_doganale']); ?>',
                um: '<?php echo addslashes($articolo['um']); ?>',
                qta_reale: Number('<?php echo str_replace(',', '.', $articolo['qta_reale']); ?>'),
                subtotal: Number('<?php echo number_format($articolo['qta_reale'] * $articolo['prezzo_unitario'], 2, '.', ''); ?>')
            });
        <?php endforeach; ?>

        // Aggiunta delle voci doganali uniche
        const uniqueDoganaleCodes = <?php echo json_encode(getUniqueDoganaleCodes($articoli)); ?>;
        let rowCount = worksheet.rowCount + 2;
        uniqueDoganaleCodes.forEach(code => {
            worksheet.addRow({
                descrizione: "NC. " + code + " PESO NETTO KG."
            });
        });

        // Generazione e download del file Excel
        workbook.xlsx.writeBuffer().then((data) => {
            const blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            saveAs(blob, `DDT_<?php echo $progressivo; ?>.xlsx`);

            // Notifica all'utente
            Swal.fire({
                icon: 'success',
                title: 'File Excel generato!',
                text: 'Il download dovrebbe iniziare automaticamente',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        });
    }

    /**
     * Avvia l'elaborazione dei mancanti
     */
    function elaboraMancanti() {
        Swal.fire({
            title: 'Sei sicuro?',
            text: "I mancanti del documento in questione verranno ricalcolati!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Conferma',
            cancelButtonText: 'Annulla'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostra un loader
                Swal.fire({
                    title: 'Elaborazione in corso',
                    text: 'Attendere prego...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('elabora_mancanti.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `progressivo=<?php echo $progressivo; ?>`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Operazione completata!',
                                text: data.message,
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Errore!',
                                text: data.message,
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Errore!',
                            text: 'Si è verificato un errore durante l\'elaborazione dei mancanti',
                        });
                    });
            }
        });
    }

    /**
     * Aggiorna i dati di un articolo
     */
    function updateData(id, field, element) {
        const newValue = element.innerText;

        // Mostra un mini loader nell'elemento
        const originalText = element.innerText;
        element.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch('ddt_update_data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}&field=${field}&value=${newValue}`
        })
            .then(response => response.json())
            .then(data => {
                // Ripristina il testo originale
                element.innerText = originalText;

                if (!data.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Errore!',
                        text: data.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    // Feedback visivo di successo
                    element.style.backgroundColor = '#d4edda';
                    setTimeout(() => {
                        element.style.backgroundColor = '';
                    }, 1000);

                    // Ricalcola il totale
                    let total = 0;
                    document.querySelectorAll("#dataTable tbody tr").forEach(row => {
                        // Salta le righe di intestazione dei mancanti
                        if (row.classList.contains('mancanti-header')) return;

                        const qta_reale = parseFloat(row.cells[5].innerText.replace(',', '.'));
                        const qta_originale = parseFloat(row.cells[4].innerText.replace(',', '.'));
                        const prezzo_unitario = parseFloat(row.cells[6].innerText.replace(',', '.'));
                        const subtotal = round(qta_reale * prezzo_unitario, 2);
                        row.cells[7].innerText = number_format(subtotal, 2, ',', '.');
                        total += subtotal;

                        // Applica lo stile arancione se qta_reale è inferiore a qta_originale
                        if (qta_reale < qta_originale && !row.classList.contains('table-info')) {
                            row.cells[5].style.backgroundColor = "#ffe8c3";
                        } else if (!row.classList.contains('table-info')) {
                            row.cells[5].style.backgroundColor = ""; // resetta il colore di sfondo
                        }
                    });
                    document.getElementById('totalValue').setAttribute('data-total', total);
                    document.getElementById('totalValue').innerText = number_format(total, 2, ',', '.');
                }
            })
            .catch(error => {
                // Ripristina il testo originale in caso di errore
                element.innerText = originalText;

                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Errore!',
                    text: 'Si è verificato un errore durante l\'aggiornamento dei dati',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            });
    }

    // ======================
    // FUNZIONI DI UTILITÀ
    // ======================

    /**
     * Arrotonda un numero al numero specificato di decimali
     */
    function round(value, decimals) {
        return Number(Math.round(value + 'e' + decimals) + 'e-' + decimals);
    }

    /**
     * Formatta un numero con separatori delle migliaia e decimali
     */
    function number_format(number, decimals, dec_point, thousands_sep) {
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function (n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };

        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }

        return s.join(dec);
    }

    /**
     * Gestisce la pressione del tasto invio nelle celle editabili
     */
    function handleKeyPress(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            event.target.blur();
        }
    }

    // Aggiunge l'event listener a tutte le celle editabili
    document.querySelectorAll('[contenteditable="true"]').forEach(function (cell) {
        cell.addEventListener('keydown', handleKeyPress);
    });

    // ======================
    // FUNZIONI DEI MODALI
    // ======================

    /**
     * Apre il modal per i pesi e l'aspetto della merce
     */
    /**
 * Apre il modal per i pesi e l'aspetto della merce
 */
    function openModal() {
        // Mostra un loader
        Swal.fire({
            title: 'Caricamento dati',
            text: 'Recupero informazioni in corso...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Prima recupera le informazioni sulle quantità totali per voce doganale
        fetch('get_unique_doganale.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `progressivo=<?php echo $progressivo; ?>`
        })
            .then(response => response.json())
            .then(vociQuantita => {
                // Crea un oggetto per un accesso più facile ai dati per voce doganale
                const quantitaMap = {};
                vociQuantita.forEach(item => {
                    quantitaMap[item.voce_doganale] = {
                        totale_quantita: item.totale_quantita,
                        um: item.um
                    };
                });

                // Ora recupera i dati salvati
                return fetch('check_piedi_data.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `progressivo=<?php echo $progressivo; ?>`
                })
                    .then(response => response.json())
                    .then(data => {
                        // Chiudi il loader
                        Swal.close();

                        if (data.success) {
                            // Carica i dati esistenti nel form
                            document.getElementById('aspettoMerce').value = data.data.aspetto_colli;
                            document.getElementById('numeroColli').value = data.data.n_colli;
                            document.getElementById('pesoLordo').value = data.data.tot_peso_lordo;
                            document.getElementById('pesoNetto').value = data.data.tot_peso_netto;
                            document.getElementById('trasportatore').value = data.data.trasportatore;
                            document.getElementById('consegnato').value = data.data.consegnato_per;

                            // Carica i dati delle voci doganali
                            let doganaleTableBody = document.getElementById('doganaleTableBody');
                            doganaleTableBody.innerHTML = '';
                            let hasSottopiedi = false;

                            // Raccogli tutte le voci doganali per mostrare (sia dalle voci che da data)
                            const allVoci = new Set();

                            // Aggiungi le voci da quantitaMap
                            Object.keys(quantitaMap).forEach(voce => {
                                allVoci.add(voce);
                            });

                            // Trova il numero massimo di voci nei dati
                            let maxVoceIndex = 0;
                            for (const key in data.data) {
                                if (key.startsWith('voce_')) {
                                    const index = parseInt(key.replace('voce_', ''));
                                    maxVoceIndex = Math.max(maxVoceIndex, index);
                                }
                            }

                            // Aggiungi le voci dai dati salvati
                            for (let i = 1; i <= maxVoceIndex; i++) {
                                if (data.data['voce_' + i]) {
                                    allVoci.add(data.data['voce_' + i]);

                                    // Verifica se esiste già la voce SOTTOPIEDI
                                    if (data.data['voce_' + i] === 'SOTTOPIEDI') {
                                        hasSottopiedi = true;
                                    }
                                }
                            }

                            // Ora visualizza tutte le voci, inclusa SOTTOPIEDI se presente
                            let index = 0;
                            allVoci.forEach(voceDoganale => {
                                // Trova il peso corrispondente nei dati salvati (se esiste)
                                let pesoValue = '0.00';
                                for (let i = 1; i <= maxVoceIndex; i++) {
                                    if (data.data['voce_' + i] === voceDoganale) {
                                        pesoValue = data.data['peso_' + i];
                                        break;
                                    }
                                }

                                // Ottieni informazioni sulla quantità per questa voce doganale
                                let quantityInfo = '';
                                if (quantitaMap[voceDoganale]) {
                                    const formattedQuantity = parseFloat(quantitaMap[voceDoganale].totale_quantita).toFixed(2);
                                    const um = quantitaMap[voceDoganale].um;
                                    quantityInfo = `<span class="badge badge-info">${formattedQuantity}</span> <span class="badge badge-secondary">${um}</span>`;
                                } else {
                                    quantityInfo = '<span class="badge badge-secondary">...</span>';
                                }

                                doganaleTableBody.innerHTML += `
                            <tr data-voce="${voceDoganale}">
                                <td>${voceDoganale}</td>
                                <td>
                                    <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" name="pesoDoganale[]" value="${pesoValue}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">kg</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-left">
                                    ${quantityInfo}
                                </td>
                            </tr>
                            `;

                                index++;
                            });

                            // Imposta la casella di spunta in base alla presenza della voce SOTTOPIEDI
                            document.getElementById('presentiSottopiedi').checked = hasSottopiedi;
                            document.getElementById('presentiSottopiedi').setAttribute("disabled", "disabled");

                            // Se SOTTOPIEDI è selezionato ma non è nella tabella, verrà aggiunto
                            // dalla funzione toggleSottopiedi esistente quando viene modificata la casella
                            // Non chiamiamo direttamente toggleSottopiedi qui per mantenere il comportamento esistente

                        } else {
                            // Se non ci sono dati esistenti, carica le voci doganali univoche
                            loadUniqueDoganale();
                        }
                    });
            })
            .catch(error => {
                console.error('Errore durante il recupero dei dati:', error);
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Errore',
                    text: 'Si è verificato un errore durante il recupero dei dati.'
                });
            });

        // Mostra il modal
        $('#pesiModal').modal('show');
    }

    /**
     * Carica le voci doganali univoche dal server
     */
    function loadUniqueDoganale() {
        fetch('get_unique_doganale.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `progressivo=<?php echo $progressivo; ?>`
        })
            .then(response => response.json())
            .then(data => {
                let doganaleTableBody = document.getElementById('doganaleTableBody');
                doganaleTableBody.innerHTML = '';

                data.forEach(item => {
                    // Formatta la quantità con 2 decimali
                    const formattedQuantity = parseFloat(item.totale_quantita).toFixed(2);

                    doganaleTableBody.innerHTML += `
                <tr data-voce="${item.voce_doganale}">
                    <td>${item.voce_doganale}</td>
                    <td>
                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" name="pesoDoganale[]">
                            <div class="input-group-append">
                                <span class="input-group-text">kg</span>
                            </div>
                        </div>
                    </td>
                    <td class="text-left">
                        <span class="badge badge-info">
                            ${formattedQuantity} 
                        </span>
                        <span class="badge badge-secondary">
                        ${item.um}
                        </span>
                    </td>
                </tr>
                `;
                });

                // Controlla se la casella SOTTOPIEDI è spuntata e aggiungi la voce se necessario
                if (document.getElementById('presentiSottopiedi').checked) {
                    addSottopiediRow();
                }
            });
    }

    /**
     * Gestisce l'aggiunta o la rimozione della voce SOTTOPIEDI
     */
    function addSottopiediRow() {
        // Controlla se la riga dei sottopiedi esiste già
        const existingRow = Array.from(document.querySelectorAll('#doganaleTableBody tr')).find(row =>
            row.getAttribute('data-voce') === 'SOTTOPIEDI');

        if (!existingRow) {
            const tableBody = document.getElementById('doganaleTableBody');

            // Aggiungi una nuova riga per i sottopiedi
            const newRow = document.createElement('tr');
            newRow.setAttribute('data-voce', 'SOTTOPIEDI');

            newRow.innerHTML = `
            <td>SOTTOPIEDI</td>
            <td>
                <div class="input-group">
                    <input type="number" step="0.01" class="form-control" name="pesoDoganale[]">
                    <div class="input-group-append">
                        <span class="input-group-text">kg</span>
                    </div>
                </div>
            </td>
            <td class="text-left">
                <span class="badge badge-secondary">...</span>
            </td>
        `;

            tableBody.appendChild(newRow);
        }
    }

    /**
     * Aggiunge una riga SOTTOPIEDI alla tabella delle voci doganali
     */
    function toggleSottopiedi() {
        const isChecked = document.getElementById('presentiSottopiedi').checked;
        const tableBody = document.getElementById('doganaleTableBody');
        const existingSottopiedi = tableBody.querySelector('tr[data-voce="SOTTOPIEDI"]');

        if (isChecked && !existingSottopiedi) {
            // Aggiungi la voce SOTTOPIEDI
            addSottopiediRow();
        } else if (!isChecked && existingSottopiedi) {
            // Rimuovi la voce SOTTOPIEDI
            existingSottopiedi.remove();
        }
    }

    /**
     * Salva i dati dei pesi e dell'aspetto della merce
     */
    function savePesiData() {
        let aspettoMerce = document.getElementById('aspettoMerce').value;
        let numeroColli = document.getElementById('numeroColli').value;
        let pesoLordo = document.getElementById('pesoLordo').value;
        let pesoNetto = document.getElementById('pesoNetto').value;
        let trasportatore = document.getElementById('trasportatore').value;
        let consegnato = document.getElementById('consegnato').value;

        // Validazione
        if (!aspettoMerce || !numeroColli || !pesoLordo || !pesoNetto) {
            Swal.fire({
                icon: 'warning',
                title: 'Campi mancanti',
                text: 'Compila tutti i campi obbligatori',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        // Raccolta dati delle voci doganali
        let vociDoganali = [];
        document.querySelectorAll('#doganaleTableBody tr').forEach(row => {
            let voce = row.cells[0].innerText;
            let peso = row.cells[1].querySelector('input').value;
            vociDoganali.push({ voce: voce, peso: peso });
        });

        // Mostra un loader
        Swal.fire({
            title: 'Salvataggio in corso',
            text: 'Attendere prego...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Invio dei dati al server
        fetch('save_pesi_data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                progressivo: <?php echo $progressivo; ?>,
                aspettoMerce: aspettoMerce,
                numeroColli: numeroColli,
                pesoLordo: pesoLordo,
                pesoNetto: pesoNetto,
                trasportatore: trasportatore,
                consegnato: consegnato,
                vociDoganali: vociDoganali
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Aggiornamento riuscito!',
                        text: data.message,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Errore!',
                        text: data.message,
                    });
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Errore!',
                    text: 'Si è verificato un errore durante l\'aggiornamento dei dati',
                });
            });
    }

    /**
     * Resetta i dati dei pesi e dell'aspetto della merce
     */
    function resetPesiData() {
        Swal.fire({
            title: 'Sei sicuro?',
            text: "Questa azione cancellerà il record esistente. Vuoi procedere?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sì, cancella!',
            cancelButtonText: 'Annulla'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('reset_pesi_data.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        progressivo: <?php echo $progressivo; ?>
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Cancellazione riuscita!',
                                text: data.message,
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Errore!',
                                text: data.message,
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Fetch Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Errore!',
                            text: 'Si è verificato un errore durante la cancellazione dei dati',
                        });
                    });
            }
        });
    }

    /**
     * Apre il modal per commento
     */
    function openCommentoModal() {
        // Mostra un loader
        Swal.fire({
            title: 'Caricamento dati',
            text: 'Recupero informazioni in corso...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData();
        formData.append('progressivo', <?php echo $progressivo; ?>);
        formData.append('azione', 'get');

        fetch('op_commento.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                // Chiudi il loader
                Swal.close();

                if (data.success) {
                    document.getElementById('commento').value = data.data.commento;
                } else {
                    document.getElementById('commento').value = '';
                }
                $('#commentoModal').modal('show');
            });
    }

    /**
     * Salva i dati del commento
     */
    function saveCommentoData() {
        const formData = new FormData();
        formData.append('progressivo', <?php echo $progressivo; ?>);
        formData.append('azione', 'save');
        formData.append('data', JSON.stringify({
            'commento': document.getElementById('commento').value
        }));

        // Mostra un loader
        Swal.fire({
            title: 'Salvataggio in corso',
            text: 'Attendere prego...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('op_commento.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(text => {
                if (text.startsWith('Dati salvati con successo')) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Aggiornamento riuscito!',
                        text: text,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Errore!',
                        text: text,
                    });
                }
            });
    }

    /**
     * Resetta i dati del commento
     */
    function resetCommentoData() {
        Swal.fire({
            title: 'Sei sicuro?',
            text: "Questa azione resetterà il campo commento",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sì, ripristina!',
            cancelButtonText: 'Annulla'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('progressivo', <?php echo $progressivo; ?>);
                formData.append('azione', 'reset');

                fetch('op_commento.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.text())
                    .then(text => {
                        if (text.startsWith('Dati ripristinati con successo')) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Ripristino riuscito!',
                                text: text,
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Errore!',
                                text: text,
                            });
                        }
                    });
            }
        });
    }

    /**
   * Apre il modal per l'autorizzazione
   */
    function openAutorizzazioneModal() {
        // Mostra un loader
        Swal.fire({
            title: 'Caricamento dati',
            text: 'Recupero informazioni in corso...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData();
        formData.append('progressivo', <?php echo $progressivo; ?>);
        formData.append('azione', 'get');

        fetch('op_autorizzazione.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                // Chiudi il loader
                Swal.close();

                if (data.success) {
                    document.getElementById('autorizzazione').value = data.data.autorizzazione;
                } else {
                    document.getElementById('autorizzazione').value = '';
                }
                $('#autorizzazioneModal').modal('show');
            });
    }
    /**
    * Salva i dati dell'autorizzazione
    */
    function saveAutorizzazioneData() {
        const formData = new FormData();
        formData.append('progressivo', <?php echo $progressivo; ?>);
        formData.append('azione', 'save');
        formData.append('data', JSON.stringify({
            'autorizzazione': document.getElementById('autorizzazione').value
        }));

        // Mostra un loader
        Swal.fire({
            title: 'Salvataggio in corso',
            text: 'Attendere prego...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('op_autorizzazione.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(text => {
                if (text.startsWith('Dati salvati con successo')) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Aggiornamento riuscito!',
                        text: text,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Errore!',
                        text: text,
                    });
                }
            });
    }

    /**
     * Resetta i dati dell'autorizzazione
     */
    function resetAutorizzazioneData() {
        Swal.fire({
            title: 'Sei sicuro?',
            text: "Questa azione ripristinerà i dati predefiniti. Vuoi procedere?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sì, ripristina!',
            cancelButtonText: 'Annulla'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('progressivo', <?php echo $progressivo; ?>);
                formData.append('azione', 'reset');

                fetch('op_autorizzazione.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.text())
                    .then(text => {
                        if (text.startsWith('Dati ripristinati con successo')) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Ripristino riuscito!',
                                text: text,
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Errore!',
                                text: text,
                            });
                        }
                    });
            }
        });
    }

    /**
     * Cerca voci doganali e costi
     */
    function cercaNcECosti() {
        Swal.fire({
            title: 'Conferma',
            text: "Tutti i dati inseriti verranno sovrascritti. Sei sicuro di voler continuare?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sì, continua',
            cancelButtonText: 'Annulla'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostra un loader
                Swal.fire({
                    title: 'Ricerca in corso',
                    text: 'Attendere prego...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Effettua la ricerca e sovrascrivi i dati
                fetch('cerca_dati_presenti.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `progressivo=<?php echo $progressivo; ?>`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Operazione completata!',
                                text: 'I dati sono stati aggiornati correttamente.',
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Errore!',
                                text: data.message || 'Si è verificato un errore durante l\'operazione',
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Errore!',
                            text: 'Si è verificato un errore durante l\'aggiornamento dei dati',
                        });
                    });
            }
        });
    }

    // ========================
    // FUNZIONI PER I MANCANTI
    // ========================

    /**
     * Apre il modal per la gestione dei mancanti
     */
    function openMancantiModal() {
        // Mostra il modal
        $('#mancantiModal').modal('show');

        // Carica i mancanti
        loadMancanti();
    }

    /**
     * Carica i mancanti disponibili dal server
     */
    function loadMancanti() {
        // Mostra il loader nel contenitore
        $('#mancantiContainer').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Caricamento...</span>
            </div>
            <p class="mt-2">Caricamento mancanti...</p>
        </div>
    `);

        // Effettua la chiamata al server - passiamo il progressivo solo per escluderlo dai risultati
        fetch('get_mancanti.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `progressivo=<?php echo $progressivo; ?>`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Organizza i mancanti per documento
                    const mancantiByDocumento = {};
                    data.mancanti.forEach(mancante => {
                        if (!mancantiByDocumento[mancante.id_documento]) {
                            mancantiByDocumento[mancante.id_documento] = {
                                documento: `DDT ${mancante.id_documento} - ${mancante.data_documento || 'N/A'}`,
                                id_documento: mancante.id_documento,
                                items: []
                            };
                        }
                        mancantiByDocumento[mancante.id_documento].items.push(mancante);
                    });

                    // Costruisci l'HTML per i mancanti
                    let html = '';

                    if (Object.keys(mancantiByDocumento).length === 0) {
                        // Non ci sono mancanti
                        html = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i> Non ci sono mancanti disponibili da aggiungere.
                    </div>
                `;
                    } else {
                        // Ci sono mancanti, mostra il gruppo per ogni documento
                        Object.values(mancantiByDocumento).forEach(gruppo => {
                            // Usa l'ID del documento come identificatore sicuro per jQuery
                            const groupId = `gruppo_${gruppo.id_documento}`;

                            html += `
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 font-weight-bold">${gruppo.documento}</h6>
                                    <div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input select-all-group" 
                                                id="selectAll_${groupId}" 
                                                data-group="${groupId}">
                                            <label class="custom-control-label" 
                                                for="selectAll_${groupId}">
                                                Seleziona tutti
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;"></th>
                                                <th>Codice Articolo</th>
                                                <th>Descrizione</th>
                                                <th>Qtà Mancante</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                    `;

                            gruppo.items.forEach(item => {
                                html += `
                            <tr>
                                <td>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input mancante-checkbox" 
                                            id="mancante_${item.id}" 
                                            value="${item.id}" 
                                            data-group="${groupId}">
                                        <label class="custom-control-label" for="mancante_${item.id}"></label>
                                    </div>
                                </td>
                                <td><strong>${item.codice_articolo}</strong></td>
                                <td>${item.descrizione || 'N/A'}</td>
                                <td>${item.qta_mancante}</td>
                            </tr>
                        `;
                            });

                            html += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                        });
                    }

                    // Inserisci l'HTML nel contenitore
                    $('#mancantiContainer').html(html);

                    // Inizializza gli eventi per le checkbox
                    initCheckboxes();
                } else {
                    // Errore nel caricamento
                    $('#mancantiContainer').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle mr-2"></i> 
                    ${data.message || 'Si è verificato un errore durante il caricamento dei mancanti.'}
                </div>
            `);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                $('#mancantiContainer').html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle mr-2"></i> 
                Si è verificato un errore durante il caricamento dei mancanti.
            </div>
        `);
            });
    }

    /**
     * Inizializza gli eventi per le checkbox di selezione
     */
    function initCheckboxes() {
        // Gestione "Seleziona tutti" per gruppo
        $('.select-all-group').on('change', function () {
            const group = $(this).data('group');
            const isChecked = $(this).prop('checked');
            $(`.mancante-checkbox[data-group="${group}"]`).prop('checked', isChecked);
        });

        // Aggiorna lo stato del "Seleziona tutti" quando cambia una checkbox
        $('.mancante-checkbox').on('change', function () {
            const group = $(this).data('group');
            const totalCheckboxes = $(`.mancante-checkbox[data-group="${group}"]`).length;
            const checkedCheckboxes = $(`.mancante-checkbox[data-group="${group}"]:checked`).length;

            $(`#selectAll_${group}`).prop('checked', totalCheckboxes === checkedCheckboxes);
        });
    }

    /**
     * Aggiunge i mancanti selezionati al DDT corrente
     */
    function aggiungiMancantiSelezionati() {
        // Recupera gli ID dei mancanti selezionati
        const selectedIds = [];
        $('.mancante-checkbox:checked').each(function () {
            selectedIds.push($(this).val());
        });

        // Verifica se ci sono mancanti selezionati
        if (selectedIds.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Nessun mancante selezionato',
                text: 'Seleziona almeno un mancante da aggiungere.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        // Chiedi conferma all'utente
        Swal.fire({
            title: 'Conferma aggiunta',
            text: `Sei sicuro di voler aggiungere ${selectedIds.length} mancanti a questo DDT?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sì, aggiungi',
            cancelButtonText: 'Annulla'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostra un loader
                Swal.fire({
                    title: 'Aggiunta in corso',
                    text: 'Attendere prego...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Invia la richiesta al server
                fetch('add_mancanti_to_ddt.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        progressivo: <?php echo $progressivo; ?>,
                        mancantiIds: selectedIds
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Mancanti aggiunti!',
                                text: data.message || 'I mancanti selezionati sono stati aggiunti con successo.',
                            }).then(() => {
                                // Chiudi il modal e ricarica la pagina
                                $('#mancantiModal').modal('hide');
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Errore!',
                                text: data.message || 'Si è verificato un errore durante l\'aggiunta dei mancanti.',
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Errore!',
                            text: 'Si è verificato un errore durante l\'aggiunta dei mancanti.',
                        });
                    });
            }
        });
    }

    // Inizializzazione
    $(function () {
        // Tooltip
        $('[data-toggle="tooltip"]').tooltip();




        // Evidenzia le celle editabili
        $('[contenteditable="true"]').hover(
            function () {
                $(this).addClass('border border-primary');
            },
            function () {
                $(this).removeClass('border border-primary');
            }
        );
    });
    function completaDdt() {
        Swal.fire({
            title: 'Conferma completamento',
            text: "Sei sicuro di voler completare questo DDT? Questa azione lo segnerà come 'Chiuso'.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sì, completa',
            cancelButtonText: 'Annulla'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostra un loader
                Swal.fire({
                    title: 'Completamento in corso',
                    text: 'Attendere prego...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Effettua la richiesta al server
                fetch('completa_ddt.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `progressivo=<?php echo $progressivo; ?>`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'DDT completato!',
                                text: data.message || 'Il DDT è stato contrassegnato come completato.',
                            }).then(() => {
                                window.location.href = 'documenti';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Errore!',
                                text: data.message || 'Si è verificato un errore durante il completamento del DDT.',
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Errore!',
                            text: 'Si è verificato un errore durante il completamento del DDT.',
                        });
                    });
            }
        });
    }
</script>