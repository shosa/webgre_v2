<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';

// Controllo parametri
if (!isset($_GET['lancio_id']) || !is_numeric($_GET['lancio_id'])) {
    echo '<div class="alert alert-danger m-3">ID lancio non valido</div>';
    exit;
}

$lancio_id = (int)$_GET['lancio_id'];

try {
    $pdo = getDbInstance();
    
    // Carica dati del lancio
    $stmt = $pdo->prepare("
        SELECT l.*, lab.nome_laboratorio
        FROM scm_lanci l
        LEFT JOIN scm_laboratori lab ON l.laboratorio_id = lab.id
        WHERE l.id = ?
    ");
    $stmt->execute([$lancio_id]);
    $lancio = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$lancio) {
        echo '<div class="alert alert-danger m-3">Lancio non trovato</div>';
        exit;
    }
    
    // Carica articoli del lancio
    $stmt = $pdo->prepare("
        SELECT * FROM scm_articoli_lancio 
        WHERE lancio_id = ? 
        ORDER BY ordine_articolo
    ");
    $stmt->execute([$lancio_id]);
    $articoli = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fasi del ciclo
    $fasi_ciclo = explode(';', $lancio['ciclo_fasi']);
    $fasi_ciclo = array_map('trim', $fasi_ciclo);
    
    // Stati attuali delle fasi per articolo
    $stmt = $pdo->prepare("
        SELECT 
            nome_fase,
            articolo_id,
            stato_fase,
            quantita_fase,
            note_fase,
            data_inizio,
            data_completamento,
            operatore
        FROM scm_fasi_lancio 
        WHERE lancio_id = ?
        ORDER BY ordine_fase
    ");
    $stmt->execute([$lancio_id]);
    $stati_fasi = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organizza stati in matrice per facile accesso
    $matrice_stati = [];
    foreach ($stati_fasi as $stato) {
        $matrice_stati[$stato['nome_fase']][$stato['articolo_id']] = $stato;
    }
    
    // Verifica articoli completati
    $articoli_completati = [];
    foreach ($articoli as $articolo) {
        $fasi_completate_articolo = 0;
        foreach ($fasi_ciclo as $fase) {
            if (isset($matrice_stati[$fase][$articolo['id']]) && 
                $matrice_stati[$fase][$articolo['id']]['stato_fase'] === 'COMPLETATA') {
                $fasi_completate_articolo++;
            }
        }
        if ($fasi_completate_articolo === count($fasi_ciclo)) {
            $articoli_completati[] = $articolo['id'];
        }
    }
    
} catch (PDOException $e) {
    echo '<div class="alert alert-danger m-3">Errore database: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit;
}
?>

<!-- Header informazioni lancio -->
<div class="bg-light p-3 border-bottom">
    <div class="row">
        <div class="col-md-8">
            <h6 class="mb-1">
                <i class="fas fa-info-circle mr-2"></i>
                Informazioni Lancio
            </h6>
            <div class="small">
                <strong>Laboratorio:</strong> <?= htmlspecialchars($lancio['nome_laboratorio'] ?? 'Non assegnato') ?> |
                <strong>Data Lancio:</strong> <?= date('d/m/Y', strtotime($lancio['data_lancio'])) ?> |
                <strong>Stato:</strong> <span class="badge badge-info"><?= str_replace('_', ' ', $lancio['stato_generale']) ?></span>
            </div>
        </div>
        <div class="col-md-4 text-right">
            <div class="small">
                <strong><?= count($articoli) ?></strong> Articoli | 
                <strong><?= count($fasi_ciclo) ?></strong> Fasi |
                <?php if (!empty($articoli_completati)): ?>
                    <span class="text-success">
                        <i class="fas fa-shipping-fast mr-1"></i>
                        <strong><?= count($articoli_completati) ?></strong> Spediti
                    </span>
                <?php else: ?>
                    <span class="text-muted">Nessun articolo spedito</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Matrice Avanzamento -->
<div class="table-responsive">
    <table class="table table-matrice">
        <thead>
            <tr>
                <th class="bg-light text-center align-middle" style="width: 200px; min-width: 150px;">
                    <strong>Articolo \ Fase</strong>
                </th>
                <?php foreach ($fasi_ciclo as $index => $fase): ?>
                    <th class="header-fase-modal text-center text-white" style="min-width: 120px;">
                        <div class="font-weight-bold"><?= htmlspecialchars($fase) ?></div>
                        <small>Fase <?= ($index + 1) ?></small>
                    </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articoli as $articolo): ?>
                <?php 
                $articolo_completato = in_array($articolo['id'], $articoli_completati);
                $classe_riga = $articolo_completato ? 'riga-completata-modal' : '';
                ?>
                <tr class="<?= $classe_riga ?>">
                    <td class="header-articolo-modal <?= $articolo_completato ? 'completato' : '' ?> text-white" style="min-width: 120px;">
                        <div class="font-weight-bold small"><?= htmlspecialchars($articolo['codice_articolo']) ?></div>
                        <small><?= number_format($articolo['quantita_totale']) ?> PAIA</small>
                        <div class="small mt-1">
                            <?php if ($articolo_completato): ?>
                                <i class="fas fa-shipping-fast mr-1"></i>SPEDITO
                            <?php else: ?>
                                Completate: <strong><?= number_format($articolo['quantita_completata']) ?></strong>
                            <?php endif; ?>
                        </div>
                    </td>
                    <?php foreach ($fasi_ciclo as $index => $fase): ?>
                        <?php 
                        $stato_cella = $matrice_stati[$fase][$articolo['id']] ?? null;
                        $stato = $stato_cella['stato_fase'] ?? 'NON_INIZIATA';
                        $quantita = $stato_cella['quantita_fase'] ?? 0;
                        $operatore = $stato_cella['operatore'] ?? '';
                        $data_completamento = $stato_cella['data_completamento'] ?? '';
                        $note = $stato_cella['note_fase'] ?? '';
                        
                        // Se è l'ultima fase e tutte le fasi sono completate, mostra SPEDITO
                        $ultima_fase = ($index === count($fasi_ciclo) - 1);
                        if ($ultima_fase && $articolo_completato && $stato === 'COMPLETATA') {
                            $stato_display = 'SPEDITO';
                            $classe_stato = 'stato-SPEDITO';
                        } else {
                            $stato_display = str_replace('_', ' ', $stato);
                            $classe_stato = 'stato-' . $stato;
                        }
                        ?>
                        <td class="p-1 align-middle <?= $classe_riga ?>">
                            <div class="matrice-cell-readonly <?= $classe_stato ?>" 
                                 <?php if ($stato !== 'NON_INIZIATA'): ?>
                                     title="<?= htmlspecialchars("Operatore: $operatore" . ($data_completamento ? "\nCompletata: " . date('d/m/Y H:i', strtotime($data_completamento)) : '') . ($note ? "\nNote: $note" : '')) ?>"
                                 <?php endif; ?>>
                                <div class="font-weight-bold small"><?= $stato_display ?></div>
                                <?php if ($quantita > 0): ?>
                                    <div class="small"><?= number_format($quantita) ?></div>
                                <?php endif; ?>
                                <?php if ($stato !== 'NON_INIZIATA' && $data_completamento): ?>
                                    <div class="small text-muted" style="font-size: 0.65rem;">
                                        <?= date('d/m', strtotime($data_completamento)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

