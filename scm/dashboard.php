<?php
session_start();
require_once '../config/config.php';

// Controllo login
if (!isset($_SESSION['laboratorio_id'])) {
    header('Location: index.php');
    exit;
}

$laboratorio_id = $_SESSION['laboratorio_id'];
$laboratorio_nome = $_SESSION['laboratorio_nome'];

try {
    $pdo = getDbInstance();

    // Query per ottenere i lanci divisi per stato
    $lanci_preparazione = [];
    $lanci_lavorazione = [];
    $lanci_completi = [];

    // Lanci in preparazione
    $stmt = $pdo->prepare("
        SELECT 
            l.id,
            l.numero_lancio,
            l.ciclo_fasi,
            l.data_lancio,
            l.stato_generale,
            l.note,
            COUNT(DISTINCT a.id) as totale_articoli,
            COALESCE(SUM(a.quantita_totale), 0) as totale_paia,
            COALESCE(SUM(a.quantita_completata), 0) as paia_completate,
            (SELECT COUNT(*) FROM scm_note WHERE lancio_id = l.id) as numero_note,
            MAX(av.data_aggiornamento) as ultimo_aggiornamento
        FROM scm_lanci l
        LEFT JOIN scm_articoli_lancio a ON l.id = a.lancio_id
        LEFT JOIN scm_avanzamento av ON l.id = av.lancio_id
        WHERE l.laboratorio_id = ? AND l.stato_generale IN ('IN_PREPARAZIONE', 'LANCIATO')
        GROUP BY l.id, l.numero_lancio, l.ciclo_fasi, l.data_lancio, l.stato_generale, l.note
        ORDER BY l.data_lancio DESC
    ");
    $stmt->execute([$laboratorio_id]);
    $lanci_preparazione = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lanci in lavorazione
    $stmt = $pdo->prepare("
        SELECT 
            l.id,
            l.numero_lancio,
            l.ciclo_fasi,
            l.data_lancio,
            l.stato_generale,
            l.note,
            COUNT(DISTINCT a.id) as totale_articoli,
            COALESCE(SUM(a.quantita_totale), 0) as totale_paia,
            COALESCE(SUM(a.quantita_completata), 0) as paia_completate,
            (SELECT COUNT(*) FROM scm_note WHERE lancio_id = l.id) as numero_note,
            MAX(av.data_aggiornamento) as ultimo_aggiornamento
        FROM scm_lanci l
        LEFT JOIN scm_articoli_lancio a ON l.id = a.lancio_id
        LEFT JOIN scm_avanzamento av ON l.id = av.lancio_id
        WHERE l.laboratorio_id = ? AND l.stato_generale = 'IN_LAVORAZIONE'
        GROUP BY l.id, l.numero_lancio, l.ciclo_fasi, l.data_lancio, l.stato_generale, l.note
        ORDER BY l.data_lancio DESC
    ");
    $stmt->execute([$laboratorio_id]);
    $lanci_lavorazione = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lanci completi
    $stmt = $pdo->prepare("
        SELECT 
            l.id,
            l.numero_lancio,
            l.ciclo_fasi,
            l.data_lancio,
            l.stato_generale,
            l.note,
            COUNT(DISTINCT a.id) as totale_articoli,
            COALESCE(SUM(a.quantita_totale), 0) as totale_paia,
            COALESCE(SUM(a.quantita_completata), 0) as paia_completate,
            (SELECT COUNT(*) FROM scm_note WHERE lancio_id = l.id) as numero_note,
            MAX(av.data_aggiornamento) as ultimo_aggiornamento
        FROM scm_lanci l
        LEFT JOIN scm_articoli_lancio a ON l.id = a.lancio_id
        LEFT JOIN scm_avanzamento av ON l.id = av.lancio_id
        WHERE l.laboratorio_id = ? AND l.stato_generale = 'COMPLETO'
        GROUP BY l.id, l.numero_lancio, l.ciclo_fasi, l.data_lancio, l.stato_generale, l.note
        ORDER BY l.data_lancio DESC
    ");
    $stmt->execute([$laboratorio_id]);
    $lanci_completi = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Funzione per calcolare statistiche fasi per ogni array di lanci
    function calcolaStatisticheFasi($pdo, &$lanci) {
        foreach ($lanci as &$lancio) {
            // Conta le fasi del ciclo
            $fasi_ciclo = explode(';', $lancio['ciclo_fasi']);
            $fasi_ciclo_count = count(array_filter(array_map('trim', $fasi_ciclo)));
            
            // Conta fasi totali necessarie (fasi_ciclo * articoli)
            $fasi_totali_necessarie = $fasi_ciclo_count * $lancio['totale_articoli'];
            
            // Conta fasi completate e in corso per questo lancio
            $stmt_fasi = $pdo->prepare("
                SELECT 
                    COUNT(CASE WHEN stato_fase = 'COMPLETATA' THEN 1 END) as fasi_completate,
                    COUNT(CASE WHEN stato_fase = 'IN_CORSO' THEN 1 END) as fasi_in_corso,
                    COUNT(*) as fasi_create
                FROM scm_fasi_lancio 
                WHERE lancio_id = ?
            ");
            $stmt_fasi->execute([$lancio['id']]);
            $fasi_stats = $stmt_fasi->fetch(PDO::FETCH_ASSOC);
            
            $lancio['fasi_completate'] = $fasi_stats['fasi_completate'] ?? 0;
            $lancio['fasi_in_corso'] = $fasi_stats['fasi_in_corso'] ?? 0;
            $lancio['fasi_totali_necessarie'] = $fasi_totali_necessarie;
            $lancio['fasi_create'] = $fasi_stats['fasi_create'] ?? 0;
        }
    }

    // Calcola statistiche per tutti i gruppi
    calcolaStatisticheFasi($pdo, $lanci_preparazione);
    calcolaStatisticheFasi($pdo, $lanci_lavorazione);
    calcolaStatisticheFasi($pdo, $lanci_completi);

    // Statistiche generali
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as totale_lanci,
            COUNT(CASE WHEN stato_generale IN ('IN_PREPARAZIONE', 'LANCIATO') THEN 1 END) as in_preparazione,
            COUNT(CASE WHEN stato_generale = 'IN_LAVORAZIONE' THEN 1 END) as in_lavorazione,
            COUNT(CASE WHEN stato_generale = 'COMPLETO' THEN 1 END) as completati
        FROM scm_lanci 
        WHERE laboratorio_id = ?
    ");
    $stmt->execute([$laboratorio_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = 'Errore database: ' . ($debug ? $e->getMessage() : 'Errore generico');
}

// Funzione per renderizzare la tabella dei lanci
function renderTabellaLanci($lanci, $tipo, $pdo) {
    if (empty($lanci)) {
        $messaggi = [
            'preparazione' => 'Nessun lancio in preparazione',
            'lavorazione' => 'Nessun lancio in lavorazione',
            'completi' => 'Nessun lancio completato'
        ];
        echo '<div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">' . $messaggi[$tipo] . '</h5>
                <p class="text-muted">Al momento non ci sono lanci in questo stato.</p>
              </div>';
        return;
    }
    ?>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Lancio</th>
                    <th>Articoli/Paia</th>
                    <th>Data Lancio</th>
                    <th>Stato</th>
                    <th>Avanzamento</th>
                    <th>Progresso Fasi</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lanci as $lancio): ?>
                    <?php
                    // Carica gli articoli per questo lancio specifico
                    $stmt_articoli = $pdo->prepare("
                        SELECT codice_articolo, quantita_totale, quantita_completata 
                        FROM scm_articoli_lancio 
                        WHERE lancio_id = ? 
                        ORDER BY ordine_articolo
                    ");
                    $stmt_articoli->execute([$lancio['id']]);
                    $articoli_lancio = $stmt_articoli->fetchAll(PDO::FETCH_ASSOC);
                    
                    $totale_paia_reale = array_sum(array_column($articoli_lancio, 'quantita_totale'));
                    $paia_completate_reali = array_sum(array_column($articoli_lancio, 'quantita_completata'));
                    
                    $percentuale_generale = 0;
                    if ($totale_paia_reale > 0) {
                        $percentuale_generale = round(($paia_completate_reali / $totale_paia_reale) * 100, 1);
                    }

                    $badge_class = match ($lancio['stato_generale']) {
                        'IN_PREPARAZIONE' => 'bg-secondary',
                        'LANCIATO' => 'bg-info',
                        'IN_LAVORAZIONE' => 'bg-warning',
                        'COMPLETO' => 'bg-success',
                        default => 'bg-secondary'
                    };
                    ?>
                    <tr>
                        <td>
                            <div>
                                <strong><?= htmlspecialchars($lancio['numero_lancio']) ?></strong>
                            </div>
                        </td>
                        <td>
                            <div class="small">
                                <div class="mb-2">
                                    <strong><?= count($articoli_lancio) ?></strong> articoli |
                                    <strong><?= number_format($totale_paia_reale) ?></strong> PA totali
                                </div>
                                <?php foreach ($articoli_lancio as $index => $art): ?>
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="badge bg-primary me-2" style="min-width: 20px;">
                                            <?= $index + 1 ?>
                                        </span>
                                        <div class="flex-grow-1" style="font-size: 0.85rem;">
                                            <strong><?= htmlspecialchars($art['codice_articolo']) ?></strong>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td>
                            <?= date('d/m/Y', strtotime($lancio['data_lancio'])) ?>
                        </td>
                        <td>
                            <span class="badge <?= $badge_class ?>">
                                <?= str_replace('_', ' ', $lancio['stato_generale']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="progress mb-1" style="height: 20px;">
                                <div class="progress-bar bg-success"
                                    style="width: <?= $percentuale_generale ?>%"
                                    title="<?= $percentuale_generale ?>% completato">
                                    <?= $percentuale_generale ?>%
                                </div>
                            </div>
                            <?php if ($lancio['ultimo_aggiornamento']): ?>
                                <small class="text-muted d-block">
                                    Agg: <?= date('d/m/Y', strtotime($lancio['ultimo_aggiornamento'])) ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="small">
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i><?= $lancio['fasi_completate'] ?>
                                </span>
                                <?php if ($lancio['fasi_in_corso'] > 0): ?>
                                    <span class="badge bg-warning ms-1">
                                        <i class="fas fa-play me-1"></i><?= $lancio['fasi_in_corso'] ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="text-muted small mt-1">
                                su <?= $lancio['fasi_totali_necessarie'] ?> totali
                            </div>
                        </td>
                        <td>
                            <!-- Pulsante Dettagli sempre disponibile -->
                            <button type="button" class="btn btn-outline-primary btn-sm me-1" 
                                    onclick="mostraDettagli(<?= $lancio['id'] ?>)"
                                    title="Visualizza dettagli">
                                <i class="fas fa-eye"></i>
                            </button>
                            
                            <!-- Pulsante Lavora solo per lanci in lavorazione -->
                            <?php if ($tipo === 'lavorazione'): ?>
                                <a href="lavora_lancio.php?id=<?= $lancio['id'] ?>"
                                    class="btn btn-outline-success btn-sm" title="Lavora su questo lancio">
                                    <i class="fas fa-tools"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCM Dashboard - <?= htmlspecialchars($laboratorio_nome) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .card-stats {
            border-left: 4px solid #007bff;
            transition: transform 0.2s;
        }

        .card-stats:hover {
            transform: translateY(-2px);
        }
        
        .fase-badge {
            font-size: 0.75rem;
            margin: 1px;
        }
        
        .articolo-item {
            border-left: 3px solid #007bff;
            background: #f8f9fa;
            margin-bottom: 0.5rem;
            padding: 0.75rem;
            border-radius: 0.375rem;
        }
        
        .articolo-row {
            background: #f8f9fa;
            border-radius: 0.375rem;
            padding: 0.5rem;
            margin-bottom: 0.25rem;
        }
        
        .articolo-badge {
            min-width: 20px;
            font-size: 0.7rem;
        }
        
        .nav-tabs .nav-link {
            color: #6c757d;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 1rem 1.5rem;
            font-weight: 500;
        }
        
        .nav-tabs .nav-link:hover {
            border-color: transparent;
            color: #007bff;
            border-bottom-color: #dee2e6;
        }
        
        .nav-tabs .nav-link.active {
            color: #007bff;
            background-color: transparent;
            border-bottom-color: #007bff;
            font-weight: 600;
        }
        
        .tab-icon {
            margin-right: 0.5rem;
        }
        
        .tab-badge {
            margin-left: 0.5rem;
            font-size: 0.75em;
        }
    </style>
</head>

<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-industry me-2"></i>
             SCM Terzisti - Emmegiemme
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-building me-1"></i>
                    <?= htmlspecialchars($laboratorio_nome) ?>
                </span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Statistiche -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card card-stats h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-muted mb-0">Totale Lanci</h6>
                                <h2 class="mb-0"><?= $stats['totale_lanci'] ?? 0 ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-list-alt fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card card-stats h-100" style="border-left-color: #6c757d;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-muted mb-0">In Preparazione</h6>
                                <h2 class="mb-0 text-secondary"><?= $stats['in_preparazione'] ?? 0 ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x text-secondary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card card-stats h-100" style="border-left-color: #ffc107;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-muted mb-0">In Lavorazione</h6>
                                <h2 class="mb-0 text-warning"><?= $stats['in_lavorazione'] ?? 0 ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-cogs fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card card-stats h-100" style="border-left-color: #28a745;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-muted mb-0">Completati</h6>
                                <h2 class="mb-0 text-success"><?= $stats['completati'] ?? 0 ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabella Lanci con Tabs -->
        <div class="card">
            <div class="card-header p-0">
                <ul class="nav nav-tabs card-header-tabs" id="lanciTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="preparazione-tab" data-bs-toggle="tab" data-bs-target="#preparazione" type="button" role="tab">
                            <i class="fas fa-clock tab-icon"></i>
                            In Preparazione
                            <span class="badge bg-secondary tab-badge"><?= count($lanci_preparazione) ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="lavorazione-tab" data-bs-toggle="tab" data-bs-target="#lavorazione" type="button" role="tab">
                            <i class="fas fa-cogs tab-icon"></i>
                            In Lavorazione
                            <span class="badge bg-warning tab-badge"><?= count($lanci_lavorazione) ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="completi-tab" data-bs-toggle="tab" data-bs-target="#completi" type="button" role="tab">
                            <i class="fas fa-check-circle tab-icon"></i>
                            Completati
                            <span class="badge bg-success tab-badge"><?= count($lanci_completi) ?></span>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-0">
                <div class="tab-content" id="lanciTabsContent">
                    <!-- Tab Preparazione -->
                    <div class="tab-pane fade" id="preparazione" role="tabpanel">
                        <?php renderTabellaLanci($lanci_preparazione, 'preparazione', $pdo); ?>
                    </div>

                    <!-- Tab Lavorazione (attivo di default) -->
                    <div class="tab-pane fade show active" id="lavorazione" role="tabpanel">
                        <?php renderTabellaLanci($lanci_lavorazione, 'lavorazione', $pdo); ?>
                    </div>

                    <!-- Tab Completi -->
                    <div class="tab-pane fade" id="completi" role="tabpanel">
                        <?php renderTabellaLanci($lanci_completi, 'completi', $pdo); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Dettagli Lancio -->
    <div class="modal fade" id="modalDettagli" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle me-2"></i>
                        Dettagli Lancio
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="dettagli-content">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Caricamento...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
    function mostraDettagli(lancioId) {
        const modal = new bootstrap.Modal(document.getElementById('modalDettagli'));
        const content = document.getElementById('dettagli-content');
        
        // Mostra spinner
        content.innerHTML = `
            <div class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Caricamento...</span>
                </div>
            </div>
        `;
        
        modal.show();
        
        // Fetch dettagli via AJAX
        fetch(`get_dettagli_lancio.php?id=${lancioId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    content.innerHTML = generaHtmlDettagli(data.lancio, data.articoli);
                } else {
                    content.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Errore nel caricamento dei dettagli: ${data.error || 'Errore sconosciuto'}
                        </div>
                    `;
                }
            })
            .catch(error => {
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Errore di connessione: ${error.message}
                    </div>
                `;
            });
    }
    
    function generaHtmlDettagli(lancio, articoli) {
        const fasi = lancio.ciclo_fasi.split(';').filter(f => f.trim());
        
        let html = `
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6><i class="fas fa-rocket me-2"></i>Informazioni Lancio</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Numero:</strong></td><td>${lancio.numero_lancio}</td></tr>
                        <tr><td><strong>Data:</strong></td><td>${new Date(lancio.data_lancio).toLocaleDateString('it-IT')}</td></tr>
                        <tr><td><strong>Stato:</strong></td><td><span class="badge bg-info">${lancio.stato_generale.replace('_', ' ')}</span></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-list me-2"></i>Ciclo di Lavorazione</h6>
                    <div class="d-flex flex-wrap gap-1">
        `;
        
        fasi.forEach((fase, index) => {
            html += `<span class="badge bg-primary">${index + 1}. ${fase.trim()}</span>`;
        });
        
        html += `
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">${fasi.length} fasi totali</small>
                    </div>
                </div>
            </div>
            
            <h6><i class="fas fa-boxes me-2"></i>Articoli del Lancio</h6>
        `;
        
        articoli.forEach(articolo => {
            const percentuale = articolo.quantita_totale > 0 ? 
                Math.round((articolo.quantita_completata / articolo.quantita_totale) * 100) : 0;
                
            html += `
                <div class="articolo-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${articolo.codice_articolo}</h6>
                            <div class="small text-muted">
                                Ordine: ${articolo.ordine_articolo} | 
                                Totale: <strong>${parseInt(articolo.quantita_totale).toLocaleString()}</strong> paia | 
                                Completate: <strong class="text-success">${parseInt(articolo.quantita_completata).toLocaleString()}</strong> paia
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="progress" style="width: 120px; height: 20px;">
                                <div class="progress-bar bg-success" style="width: ${percentuale}%">
                                    ${percentuale}%
                                </div>
                            </div>
                        </div>
                    </div>
                    ${articolo.note_articolo ? `<div class="mt-2 small text-muted"><i class="fas fa-sticky-note me-1"></i>${articolo.note_articolo}</div>` : ''}
                </div>
            `;
        });
        
        if (lancio.note) {
            html += `
                <div class="mt-4">
                    <h6><i class="fas fa-comment me-2"></i>Note del Lancio</h6>
                    <div class="alert alert-info">
                        ${lancio.note.replace(/\n/g, '<br>')}
                    </div>
                </div>
            `;
        }
        
        return html;
    }
    
    // Aggiorna contatori dei badge quando si cambia tab
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('[data-bs-toggle="tab"]');
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(e) {
                // Puoi aggiungere logica per aggiornare i contatori se necessario
                console.log('Tab attivato:', e.target.id);
            });
        });
    });
    </script>
</body>

</html>