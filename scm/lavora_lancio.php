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

// Controllo ID lancio
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'ID lancio non valido';
    header('Location: dashboard.php');
    exit;
}

$lancio_id = (int)$_GET['id'];

// Gestione aggiornamento fase
if ($_POST && isset($_POST['aggiorna_fase'])) {
    $fase_nome = trim($_POST['fase_nome'] ?? '');
    $articolo_id = (int)($_POST['articolo_id'] ?? 0);
    $nuovo_stato = trim($_POST['nuovo_stato'] ?? '');
    $note = trim($_POST['note'] ?? '');
    $conferma_precedenti = isset($_POST['conferma_precedenti']);
    
    if (!empty($fase_nome) && $articolo_id > 0 && !empty($nuovo_stato)) {
        try {
            $pdo = getDbInstance();
            
            // Carica i dati del lancio per controlli
            $stmt = $pdo->prepare("SELECT * FROM scm_lanci WHERE id = ?");
            $stmt->execute([$lancio_id]);
            $lancio = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Carica articoli
            $stmt = $pdo->prepare("SELECT * FROM scm_articoli_lancio WHERE lancio_id = ? ORDER BY ordine_articolo");
            $stmt->execute([$lancio_id]);
            $articoli = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Ottieni la quantità totale dell'articolo
            $articolo_corrente = null;
            foreach ($articoli as $art) {
                if ($art['id'] == $articolo_id) {
                    $articolo_corrente = $art;
                    break;
                }
            }
            $quantita_lavorata = $articolo_corrente['quantita_totale']; // Sempre quantità totale
            
            // Se sto completando o avviando una fase, controllo le precedenti
            $warning_precedenti = [];
            if ($nuovo_stato === 'COMPLETATA' || $nuovo_stato === 'IN_CORSO') {
                $fasi_ciclo = explode(';', $lancio['ciclo_fasi']);
                $fasi_ciclo = array_map('trim', $fasi_ciclo);
                $indice_fase_corrente = array_search($fase_nome, $fasi_ciclo);
                
                if ($indice_fase_corrente > 0) {
                    // Controllo fasi precedenti per questo specifico articolo
                    for ($i = 0; $i < $indice_fase_corrente; $i++) {
                        $fase_precedente = $fasi_ciclo[$i];
                        $stmt = $pdo->prepare("
                            SELECT stato_fase FROM scm_fasi_lancio 
                            WHERE lancio_id = ? AND nome_fase = ? AND articolo_id = ?
                        ");
                        $stmt->execute([$lancio_id, $fase_precedente, $articolo_id]);
                        $stato_precedente = $stmt->fetchColumn();
                        
                        if ($stato_precedente !== 'COMPLETATA') {
                            $warning_precedenti[] = $fase_precedente;
                        }
                    }
                }
            }
            
            // Se ci sono fasi precedenti non completate e non ho la conferma
            if (!empty($warning_precedenti) && !$conferma_precedenti) {
                $azione = ($nuovo_stato === 'COMPLETATA') ? 'completando' : 'avviando';
                $_SESSION['warning'] = [
                    'message' => "Attenzione! Stai {$azione} la fase \"{$fase_nome}\" ma le seguenti fasi precedenti non sono completate: " . implode(', ', $warning_precedenti) . '. Vuoi procedere e completare automaticamente anche le fasi precedenti?',
                    'fase_nome' => $fase_nome,
                    'articolo_id' => $articolo_id,
                    'nuovo_stato' => $nuovo_stato,
                    'note' => $note,
                    'fasi_precedenti' => $warning_precedenti
                ];
                header("Location: lavora_lancio.php?id=$lancio_id");
                exit;
            }
            
            $pdo->beginTransaction();
            
            // Se ho la conferma, completo automaticamente le fasi precedenti
            if (!empty($warning_precedenti) && $conferma_precedenti) {
                foreach ($warning_precedenti as $fase_precedente) {
                    // Controlla se la fase precedente esiste già per questo specifico articolo
                    $stmt = $pdo->prepare("
                        SELECT id FROM scm_fasi_lancio 
                        WHERE lancio_id = ? AND nome_fase = ? AND articolo_id = ?
                    ");
                    $stmt->execute([$lancio_id, $fase_precedente, $articolo_id]);
                    $fase_prec_id = $stmt->fetchColumn();
                    
                    if ($fase_prec_id) {
                        // Aggiorna fase precedente esistente
                        $stmt = $pdo->prepare("
                            UPDATE scm_fasi_lancio SET
                            stato_fase = 'COMPLETATA',
                            quantita_fase = ?,
                            data_completamento = NOW(),
                            note_fase = CONCAT(COALESCE(note_fase, ''), ' - Completata automaticamente'),
                            operatore = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([$quantita_lavorata, $laboratorio_nome, $fase_prec_id]);
                    } else {
                        // Prima ottieni il prossimo ordine_fase
                        $stmt = $pdo->prepare("SELECT COALESCE(MAX(ordine_fase), 0) + 1 FROM scm_fasi_lancio WHERE lancio_id = ?");
                        $stmt->execute([$lancio_id]);
                        $prossimo_ordine = $stmt->fetchColumn();
                        
                        // Crea nuova fase precedente
                        $stmt = $pdo->prepare("
                            INSERT INTO scm_fasi_lancio 
                            (lancio_id, nome_fase, ordine_fase, articolo_id, stato_fase, quantita_fase, note_fase, operatore, data_completamento)
                            VALUES (?, ?, ?, ?, 'COMPLETATA', ?, 'Completata automaticamente', ?, NOW())
                        ");
                        $stmt->execute([$lancio_id, $fase_precedente, $prossimo_ordine, $articolo_id, $quantita_lavorata, $laboratorio_nome]);
                    }
                    
                    // Inserisci record avanzamento per fase precedente
                    $stmt = $pdo->prepare("
                        SELECT id FROM scm_fasi_lancio 
                        WHERE lancio_id = ? AND nome_fase = ? AND articolo_id = ?
                    ");
                    $stmt->execute([$lancio_id, $fase_precedente, $articolo_id]);
                    $fase_prec_id = $stmt->fetchColumn();
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO scm_avanzamento 
                        (lancio_id, fase_id, articolo_id, data_aggiornamento, percentuale_completamento, 
                         quantita_lavorata, stato_fase, note_avanzamento, operatore) 
                        VALUES (?, ?, ?, CURDATE(), 100, ?, 'COMPLETATA', 'Completata automaticamente', ?)
                    ");
                    $stmt->execute([$lancio_id, $fase_prec_id, $articolo_id, $quantita_lavorata, $laboratorio_nome]);
                }
            }
            
            // Prima controlla se la fase esiste già per questo specifico articolo
            $stmt = $pdo->prepare("
                SELECT id FROM scm_fasi_lancio 
                WHERE lancio_id = ? AND nome_fase = ? AND articolo_id = ?
            ");
            $stmt->execute([$lancio_id, $fase_nome, $articolo_id]);
            $fase_id_esistente = $stmt->fetchColumn();
            
            if ($fase_id_esistente) {
                // Aggiorna la fase esistente
                $stmt = $pdo->prepare("
                    UPDATE scm_fasi_lancio SET
                    stato_fase = ?,
                    quantita_fase = ?,
                    data_inizio = CASE WHEN ? = 'IN_CORSO' AND data_inizio IS NULL THEN NOW() ELSE data_inizio END,
                    data_completamento = CASE WHEN ? = 'COMPLETATA' THEN NOW() ELSE NULL END,
                    note_fase = ?,
                    operatore = ?
                    WHERE id = ?
                ");
                $stmt->execute([$nuovo_stato, $quantita_lavorata, $nuovo_stato, $nuovo_stato, $note, $laboratorio_nome, $fase_id_esistente]);
                $fase_id = $fase_id_esistente;
            } else {
                // Prima ottieni il prossimo ordine_fase per la fase corrente
                $stmt = $pdo->prepare("SELECT COALESCE(MAX(ordine_fase), 0) + 1 FROM scm_fasi_lancio WHERE lancio_id = ?");
                $stmt->execute([$lancio_id]);
                $prossimo_ordine_corrente = $stmt->fetchColumn();
                
                // Crea nuova fase
                $stmt = $pdo->prepare("
                    INSERT INTO scm_fasi_lancio 
                    (lancio_id, nome_fase, ordine_fase, articolo_id, stato_fase, quantita_fase,
                     data_inizio, data_completamento, note_fase, operatore)
                    VALUES (?, ?, ?, ?, ?, ?,
                           CASE WHEN ? = 'IN_CORSO' THEN NOW() ELSE NULL END,
                           CASE WHEN ? = 'COMPLETATA' THEN NOW() ELSE NULL END,
                           ?, ?)
                ");
                $stmt->execute([$lancio_id, $fase_nome, $prossimo_ordine_corrente, $articolo_id, $nuovo_stato, $quantita_lavorata, 
                               $nuovo_stato, $nuovo_stato, $note, $laboratorio_nome]);
                $fase_id = $pdo->lastInsertId();
            }
            
            // Inserisci record avanzamento
            $percentuale = ($nuovo_stato === 'COMPLETATA') ? 100 : (($nuovo_stato === 'IN_CORSO') ? 50 : 0);
            $stmt = $pdo->prepare("
                INSERT INTO scm_avanzamento 
                (lancio_id, fase_id, articolo_id, data_aggiornamento, percentuale_completamento, 
                 quantita_lavorata, stato_fase, note_avanzamento, operatore) 
                VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$lancio_id, $fase_id, $articolo_id, $percentuale, $quantita_lavorata, $nuovo_stato, $note, $laboratorio_nome]);
            
            // Aggiorna quantità completata articolo se fase completata
            if ($nuovo_stato === 'COMPLETATA') {
                $stmt = $pdo->prepare("
                    UPDATE scm_articoli_lancio 
                    SET quantita_completata = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$quantita_lavorata, $articolo_id]);
            }
            
            // Aggiorna stato generale lancio
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(DISTINCT nome_fase) as fasi_totali_ciclo,
                    COUNT(CASE WHEN stato_fase = 'COMPLETATA' THEN 1 END) as fasi_completate,
                    COUNT(CASE WHEN stato_fase = 'IN_CORSO' THEN 1 END) as fasi_in_corso
                FROM scm_fasi_lancio 
                WHERE lancio_id = ?
            ");
            $stmt->execute([$lancio_id]);
            $fasi_stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $fasi_ciclo_count = count(explode(';', $lancio['ciclo_fasi']));
            $articoli_count = count($articoli);
            $fasi_totali_necessarie = $fasi_ciclo_count * $articoli_count;
            
            $nuovo_stato_lancio = 'LANCIATO';
            if ($fasi_stats['fasi_completate'] == $fasi_totali_necessarie) {
                $nuovo_stato_lancio = 'COMPLETO';
            } elseif ($fasi_stats['fasi_completate'] > 0 || $fasi_stats['fasi_in_corso'] > 0) {
                $nuovo_stato_lancio = 'IN_LAVORAZIONE';
            }
            
            $stmt = $pdo->prepare("UPDATE scm_lanci SET stato_generale = ? WHERE id = ?");
            $stmt->execute([$nuovo_stato_lancio, $lancio_id]);
            
            $pdo->commit();
            
            // Rimuovi warning dalla sessione
            unset($_SESSION['warning']);
            
            $messaggio_successo = 'Aggiornamento salvato con successo';
            if (!empty($warning_precedenti) && $conferma_precedenti) {
                $messaggio_successo .= '. Sono state completate automaticamente anche le fasi precedenti: ' . implode(', ', $warning_precedenti);
            }
            $_SESSION['success'] = $messaggio_successo;
            
        } catch (PDOException $e) {
            $pdo->rollback();
           $_SESSION['error'] = 'Errore durante l\'aggiornamento: ' . $e->getMessage() . ' - Codice: ' . $e->getCode() . ' - File: ' . $e->getFile() . ' - Linea: ' . $e->getLine();
        }
    } else {
        $_SESSION['error'] = 'Dati obbligatori mancanti';
    }
    
    header("Location: lavora_lancio.php?id=$lancio_id");
    exit;
}

// Caricamento dati
try {
    $pdo = getDbInstance();
    
    // Dati lancio
    $stmt = $pdo->prepare("
        SELECT l.*, lab.nome_laboratorio
        FROM scm_lanci l
        LEFT JOIN scm_laboratori lab ON l.laboratorio_id = lab.id
        WHERE l.id = ? AND l.laboratorio_id = ?
    ");
    $stmt->execute([$lancio_id, $laboratorio_id]);
    $lancio = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$lancio) {
        $_SESSION['error'] = 'Lancio non trovato';
        header('Location: dashboard.php');
        exit;
    }
    
    // Articoli del lancio
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
            percentuale_completamento,
            quantita_fase,
            note_fase
        FROM scm_fasi_lancio 
        WHERE lancio_id = ?
    ");
    $stmt->execute([$lancio_id]);
    $stati_fasi = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organizza stati in matrice per facile accesso
    $matrice_stati = [];
    foreach ($stati_fasi as $stato) {
        $matrice_stati[$stato['nome_fase']][$stato['articolo_id']] = $stato;
    }
    
    // Verifica se l'articolo è completamente spedito (tutte le fasi completate)
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
    
    // Carica note del lancio
    $stmt = $pdo->prepare("
        SELECT * FROM scm_note 
        WHERE lancio_id = ? 
        ORDER BY data_creazione DESC
    ");
    $stmt->execute([$lancio_id]);
    $note_lancio = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = 'Errore database: ' . ($debug ? $e->getMessage() : 'Errore generico');
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lavora su Lancio <?= htmlspecialchars($lancio['numero_lancio'] ?? '') ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .matrice-cell {
            min-width: 120px;
            min-height: 80px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        .matrice-cell:hover {
            border-color: #007bff;
            transform: scale(1.02);
        }
        .matrice-cell.non-cliccabile {
            cursor: not-allowed;
            opacity: 0.7;
        }
        .matrice-cell.non-cliccabile:hover {
            border-color: #dee2e6;
            transform: none;
        }
        .stato-NON_INIZIATA { background-color: #f8f9fa; color: #6c757d; }
        .stato-IN_CORSO { background-color: #fff3cd; color: #856404; border-color: #ffc107; }
        .stato-COMPLETATA { background-color: #d1ecf1; color: #0c5460; border-color: #17a2b8; }
        .stato-SPEDITO { background-color: #d4edda; color: #155724; border-color: #28a745; font-weight: bold; }
        .stato-BLOCCATA { background-color: #f8d7da; color: #721c24; border-color: #dc3545; }
        
        .header-articolo {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
        }
        .header-articolo.completato {
            background: linear-gradient(45deg, #28a745, #1e7e34);
        }
        .header-fase {
            background: linear-gradient(45deg, #28a745, #1e7e34);
            color: white;
        }
        
        .riga-completata {
            background-color: #d4edda !important;
        }
        
        .note-card {
            border-left: 4px solid #007bff;
            margin-bottom: 1rem;
        }
        .note-PROBLEMA { border-left-color: #dc3545; }
        .note-URGENTE { border-left-color: #fd7e14; }
        .note-QUALITA { border-left-color: #6f42c1; }
        .note-LOGISTICA { border-left-color: #20c997; }
        
        .priorita-badge {
            font-size: 0.75rem;
        }
        .priorita-BASSA { background-color: #28a745; }
        .priorita-MEDIA { background-color: #ffc107; color: #000; }
        .priorita-ALTA { background-color: #fd7e14; }
        .priorita-CRITICA { background-color: #dc3545; }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-industry me-2"></i>
                <?= htmlspecialchars($laboratorio_nome) ?>
            </a>
            <div class="navbar-nav ms-auto">
                <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Alert messaggi -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['warning'])): ?>
            <div class="alert alert-warning">
                <h6 class="alert-heading">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Conferma Operazione
                </h6>
                <p><?= $_SESSION['warning']['message'] ?></p>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="aggiorna_fase" value="1">
                    <input type="hidden" name="fase_nome" value="<?= htmlspecialchars($_SESSION['warning']['fase_nome']) ?>">
                    <input type="hidden" name="articolo_id" value="<?= $_SESSION['warning']['articolo_id'] ?>">
                    <input type="hidden" name="nuovo_stato" value="<?= htmlspecialchars($_SESSION['warning']['nuovo_stato']) ?>">
                    <input type="hidden" name="note" value="<?= htmlspecialchars($_SESSION['warning']['note']) ?>">
                    <input type="hidden" name="conferma_precedenti" value="1">
                    <button type="submit" class="btn btn-warning me-2">
                        <i class="fas fa-check me-1"></i>Sì, Procedi e Completa Fasi Precedenti
                    </button>
                </form>
                <a href="lavora_lancio.php?id=<?= $lancio_id ?>" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i>Annulla
                </a>
            </div>
            <?php unset($_SESSION['warning']); ?>
        <?php endif; ?>

        <!-- Header lancio -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">
                            <i class="fas fa-rocket me-2"></i>
                            Lancio <?= htmlspecialchars($lancio['numero_lancio']) ?>
                        </h4>
                        <small class="text-muted">
                            Data: <?= date('d/m/Y', strtotime($lancio['data_lancio'])) ?> | 
                            Stato: <span class="badge bg-info"><?= str_replace('_', ' ', $lancio['stato_generale']) ?></span>
                        </small>
                    </div>
                    <div class="text-end">
                        <div class="small">Articoli: <strong><?= count($articoli) ?></strong></div>
                        <div class="small">Fasi: <strong><?= count($fasi_ciclo) ?></strong></div>
                        <?php if (!empty($articoli_completati)): ?>
                            <div class="small text-success">
                                <i class="fas fa-shipping-fast me-1"></i>
                                <strong><?= count($articoli_completati) ?></strong> articoli spediti
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Matrice lavorazione -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-th me-2"></i>
                    Matrice Lavorazione
                </h5>
                <small class="text-muted">Clicca su una cella per aggiornare lo stato della lavorazione</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th class="bg-light text-center " style="width: 200px;">Articolo \ Fase</th>
                                <?php foreach ($fasi_ciclo as $index => $fase): ?>
                                    <th class="header-fase text-center p-3 text-white">
                                        <div class="fw-bold text-white"><?= htmlspecialchars($fase) ?></div>
                                        <small class="text-white">Fase <?= ($index + 1) ?></small>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($articoli as $articolo): ?>
                                <?php 
                                $articolo_completato = in_array($articolo['id'], $articoli_completati);
                                $classe_riga = $articolo_completato ? 'riga-completata' : '';
                                ?>
                                <tr class="<?= $classe_riga ?>">
                                    <td class="header-articolo <?= $articolo_completato ? 'completato' : '' ?> p-3 text-center text-white">
                                        <div class="fw-bold"><?= htmlspecialchars($articolo['codice_articolo']) ?></div>
                                        <small><?= number_format($articolo['quantita_totale']) ?> PAIA</small>
                                        <div class="small mt-1">
                                            <?php if ($articolo_completato): ?>
                                                <i class="fas fa-shipping-fast me-1"></i>SPEDITO
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
                                        
                                        // Se è l'ultima fase e tutte le fasi sono completate, mostra SPEDITO
                                        $ultima_fase = ($index === count($fasi_ciclo) - 1);
                                        if ($ultima_fase && $articolo_completato && $stato === 'COMPLETATA') {
                                            $stato_display = 'SPEDITO';
                                            $classe_stato = 'stato-SPEDITO';
                                        } else {
                                            $stato_display = str_replace('_', ' ', $stato);
                                            $classe_stato = 'stato-' . $stato;
                                        }
                                        
                                        // Le fasi completate non sono cliccabili
                                        $cliccabile = $stato !== 'COMPLETATA';
                                        $classe_cliccabile = $cliccabile ? '' : 'non-cliccabile';
                                        $onclick = $cliccabile ? "apriModal('".addslashes($fase)."', ".$articolo['id'].", '".addslashes($articolo['codice_articolo'])."', '".$stato."', ".$articolo['quantita_totale'].")" : '';
                                        ?>
                                        <td class="p-2 <?= $classe_riga ?>">
                                            <div class="matrice-cell <?= $classe_stato ?> <?= $classe_cliccabile ?>"
                                                 <?= $onclick ? "onclick=\"$onclick\"" : '' ?>>
                                                <div class="fw-bold small"><?= $stato_display ?></div>
                                                <?php if ($quantita > 0): ?>
                                                    <div class="small"><?= $quantita ?> paia</div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Note del Lancio -->
        <?php if (!empty($note_lancio)): ?>
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-sticky-note me-2"></i>
                    Note del Lancio
                </h5>
            </div>
            <div class="card-body">
                <?php foreach ($note_lancio as $nota): ?>
                    <div class="card note-card note-<?= $nota['tipo_nota'] ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <?php if (!empty($nota['titolo'])): ?>
                                        <h6 class="card-title mb-1"><?= htmlspecialchars($nota['titolo']) ?></h6>
                                    <?php endif; ?>
                                    <div class="d-flex gap-2 mb-2">
                                        <span class="badge bg-secondary"><?= str_replace('_', ' ', $nota['tipo_nota']) ?></span>
                                        <span class="badge priorita-<?= $nota['priorita'] ?> priorita-badge"><?= $nota['priorita'] ?></span>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    <?= date('d/m/Y H:i', strtotime($nota['data_creazione'])) ?>
                                </small>
                            </div>
                            <p class="card-text mb-1"><?= nl2br(htmlspecialchars($nota['contenuto'])) ?></p>
                            <?php if (!empty($nota['autore'])): ?>
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i>
                                    <?= htmlspecialchars($nota['autore']) ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-sticky-note me-2"></i>
                    Note del Lancio
                </h5>
            </div>
            <div class="card-body text-center text-muted">
                <i class="fas fa-sticky-note fa-3x mb-3 opacity-50"></i>
                <p>Nessuna nota presente per questo lancio.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modal aggiornamento -->
    <div class="modal fade" id="modalAggiornamento" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>
                        Aggiorna Lavorazione
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="aggiorna_fase" value="1">
                        <input type="hidden" name="fase_nome" id="fase_nome">
                        <input type="hidden" name="articolo_id" id="articolo_id">
                        
                        <div class="alert alert-info">
                            <strong>Fase:</strong> <span id="info_fase"></span><br>
                            <strong>Articolo:</strong> <span id="info_articolo"></span>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nuovo_stato" class="form-label">Stato Lavorazione</label>
                            <select class="form-select" name="nuovo_stato" id="nuovo_stato" required>
                                <option value="NON_INIZIATA">Non Iniziata</option>
                                <option value="IN_CORSO">In Corso</option>
                                <option value="COMPLETATA">Completata</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="quantita_lavorata" class="form-label" hidden>Quantità Lavorata (paia)</label>
                            <input type="number" class="form-control" name="quantita_lavorata" id="quantita_lavorata" 
                                   min="0" value="0" hidden>
                        </div>
                        
                        <div class="mb-3">
                            <label for="note" class="form-label">Note</label>
                            <textarea class="form-control" name="note" id="note" rows="3" 
                                      placeholder="Aggiungi note sulla lavorazione..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Salva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
    function apriModal(fase, articoloId, articoloCodice, statoAttuale, quantitaAttuale) {
        document.getElementById('fase_nome').value = fase;
        document.getElementById('articolo_id').value = articoloId;
        document.getElementById('info_fase').textContent = fase;
        document.getElementById('info_articolo').textContent = articoloCodice;
        document.getElementById('nuovo_stato').value = statoAttuale;
        document.getElementById('quantita_lavorata').value = quantitaAttuale;
        
        new bootstrap.Modal(document.getElementById('modalAggiornamento')).show();
    }
    </script>
</body>
</html>