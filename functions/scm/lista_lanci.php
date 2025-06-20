<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';
require_once BASE_PATH . '/components/header.php';

// Gestione eliminazione
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $lancio_id = (int)$_GET['delete'];
    
    try {
        $pdo = getDbInstance();
        $stmt = $pdo->prepare("DELETE FROM scm_lanci WHERE id = ?");
        $stmt->execute([$lancio_id]);
        $_SESSION['success'] = 'Lancio eliminato con successo';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Errore durante l\'eliminazione: ' . ($debug ? $e->getMessage() : 'Errore generico');
    }
    
    header('Location: lista_lanci.php');
    exit;
}

// Tab attivo - default su IN_LAVORAZIONE
$tab_attivo = $_GET['tab'] ?? 'IN_LAVORAZIONE';

// Validazione tab
$tab_validi = ['IN_PREPARAZIONE', 'IN_LAVORAZIONE', 'COMPLETATI'];
if (!in_array($tab_attivo, $tab_validi)) {
    $tab_attivo = 'IN_LAVORAZIONE';
}

// Filtri
$filtro_laboratorio = $_GET['laboratorio'] ?? '';
$filtro_data_da = $_GET['data_da'] ?? '';
$filtro_data_a = $_GET['data_a'] ?? '';

// Costruzione query con filtri
$where_conditions = ["1=1"];
$params = [];

// Filtro per tab attivo
if ($tab_attivo == 'COMPLETATI') {
    $where_conditions[] = "l.stato_generale IN ('COMPLETO')";
} else {
    $where_conditions[] = "l.stato_generale = ?";
    $params[] = $tab_attivo;
}

if (!empty($filtro_laboratorio)) {
    $where_conditions[] = "l.laboratorio_id = ?";
    $params[] = $filtro_laboratorio;
}

if (!empty($filtro_data_da)) {
    $where_conditions[] = "l.data_lancio >= ?";
    $params[] = $filtro_data_da;
}

if (!empty($filtro_data_a)) {
    $where_conditions[] = "l.data_lancio <= ?";
    $params[] = $filtro_data_a;
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Caricamento lanci
try {
    $pdo = getDbInstance();
    
    $stmt = $pdo->prepare("
        SELECT l.*, 
               lab.nome_laboratorio,
               COUNT(DISTINCT a.id) as numero_articoli,
               COUNT(DISTINCT f.id) as numero_fasi,
               COUNT(CASE WHEN f.stato_fase = 'COMPLETATA' THEN 1 END) as fasi_completate,
               COUNT(CASE WHEN f.stato_fase = 'IN_CORSO' THEN 1 END) as fasi_in_corso,
               (SELECT COUNT(*) FROM scm_note WHERE lancio_id = l.id) as numero_note,
               MAX(av.data_aggiornamento) as ultimo_aggiornamento,
               DATEDIFF(CURRENT_DATE, l.data_lancio) as giorni_dal_lancio
        FROM scm_lanci l
        LEFT JOIN scm_laboratori lab ON l.laboratorio_id = lab.id
        LEFT JOIN scm_articoli_lancio a ON l.id = a.lancio_id
        LEFT JOIN scm_fasi_lancio f ON l.id = f.lancio_id
        LEFT JOIN scm_avanzamento av ON l.id = av.lancio_id
        $where_clause
        GROUP BY l.id
        ORDER BY l.data_lancio DESC, l.id DESC
    ");
    $stmt->execute($params);
    $lanci_base = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Per ogni lancio, calcola le paia corrette
    $lanci = [];
    foreach ($lanci_base as $lancio) {
        $stmt_paia = $pdo->prepare("
            SELECT 
                COALESCE(SUM(quantita_totale), 0) as totale_paia,
                COALESCE(SUM(quantita_completata), 0) as paia_completate
            FROM scm_articoli_lancio 
            WHERE lancio_id = ?
        ");
        $stmt_paia->execute([$lancio['id']]);
        $paia_stats = $stmt_paia->fetch(PDO::FETCH_ASSOC);
        
        $lancio['totale_paia'] = $paia_stats['totale_paia'];
        $lancio['paia_completate'] = $paia_stats['paia_completate'];
        
        // Calcola percentuale corretta
        $lancio['percentuale_generale'] = $lancio['totale_paia'] > 0 ? 
            round(($lancio['paia_completate'] / $lancio['totale_paia']) * 100, 1) : 0;
        
        $lanci[] = $lancio;
    }
    
    // Caricamento laboratori per filtro
    $stmt = $pdo->query("SELECT id, nome_laboratorio FROM scm_laboratori ORDER BY nome_laboratorio");
    $laboratori = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Conta i lanci per ogni tab
    $stmt_count = $pdo->prepare("
        SELECT 
            stato_generale,
            COUNT(*) as count
        FROM scm_lanci 
        WHERE stato_generale IN ('IN_PREPARAZIONE', 'IN_LAVORAZIONE', 'COMPLETO', 'SOSPESO')
        GROUP BY stato_generale
    ");
    $stmt_count->execute();
    $count_stati = $stmt_count->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $count_preparazione = $count_stati['IN_PREPARAZIONE'] ?? 0;
    $count_lavorazione = $count_stati['IN_LAVORAZIONE'] ?? 0;
    $count_completati = ($count_stati['COMPLETO'] ?? 0);
    
} catch (PDOException $e) {
    $error = 'Errore database: ' . ($debug ? $e->getMessage() : 'Errore generico');
    $lanci = [];
    $laboratori = [];
}
?>

<body id="page-top">
    <div id="wrapper">
        <?php include(BASE_PATH . "/components/navbar.php"); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include(BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid">
                    <?php include(BASE_PATH . "/utils/alerts.php"); ?>
                    
                    <!-- Header -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <div class="d-flex align-items-center">
                            <div class="mr-3 bg-gradient-success text-white p-3 rounded shadow-sm">
                                <i class="fas fa-rocket fa-2x"></i>
                            </div>
                            <div>
                                <h1 class="h3 mb-0 text-gray-800">Lista Lanci</h1>
                                <p class="mb-0 text-gray-600">Gestisci tutti i lanci di produzione</p>
                            </div>
                        </div>
                        <div>
                            <a href="crea_lancio.php" class="btn btn-success">
                                <i class="fas fa-plus mr-2"></i>Nuovo Lancio
                            </a>
                        
                        </div>
                        
                    </div>
                     <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index">SCM</a></li>
                        <li class="breadcrumb-item active">Lista Lanci</li>
                    </ol>
                    <!-- Tab Navigation -->
                    <div class="card shadow mb-4">
                        <div class="card-header p-0">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link <?= $tab_attivo == 'IN_PREPARAZIONE' ? 'active' : '' ?>" 
                                       href="?tab=IN_PREPARAZIONE<?= !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['tab' => ''])) : '' ?>">
                                        <i class="fas fa-clipboard-list mr-2"></i>
                                        In Preparazione
                                        <?php if ($count_preparazione > 0): ?>
                                            <span class="badge badge-light ml-2"><?= $count_preparazione ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= $tab_attivo == 'IN_LAVORAZIONE' ? 'active' : '' ?>" 
                                       href="?tab=IN_LAVORAZIONE<?= !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['tab' => ''])) : '' ?>">
                                        <i class="fas fa-cogs mr-2"></i>
                                        In Lavorazione
                                        <?php if ($count_lavorazione > 0): ?>
                                            <span class="badge badge-light ml-2"><?= $count_lavorazione ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= $tab_attivo == 'COMPLETATI' ? 'active' : '' ?>" 
                                       href="?tab=COMPLETATI<?= !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['tab' => ''])) : '' ?>">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        Completati
                                        <?php if ($count_completati > 0): ?>
                                            <span class="badge badge-light ml-2"><?= $count_completati ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Filtri -->
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-filter mr-2"></i>
                                Filtri di Ricerca
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row">
                                <input type="hidden" name="tab" value="<?= $tab_attivo ?>">
                                
                                <div class="col-md-4">
                                    <label for="laboratorio" class="small">Laboratorio</label>
                                    <select class="form-control form-control-sm" id="laboratorio" name="laboratorio">
                                        <option value="">Tutti</option>
                                        <?php foreach ($laboratori as $lab): ?>
                                            <option value="<?= $lab['id'] ?>" <?= ($filtro_laboratorio == $lab['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($lab['nome_laboratorio']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-2">
                                    <label for="data_da" class="small">Data Da</label>
                                    <input type="date" 
                                           class="form-control form-control-sm" 
                                           id="data_da" 
                                           name="data_da" 
                                           value="<?= htmlspecialchars($filtro_data_da) ?>">
                                </div>
                                
                                <div class="col-md-2">
                                    <label for="data_a" class="small">Data A</label>
                                    <input type="date" 
                                           class="form-control form-control-sm" 
                                           id="data_a" 
                                           name="data_a" 
                                           value="<?= htmlspecialchars($filtro_data_a) ?>">
                                </div>
                                
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary btn-sm mr-1" title="Applica filtri">
                                        <i class="fas fa-search"></i> Cerca
                                    </button>
                                    <a href="?tab=<?= $tab_attivo ?>" class="btn btn-outline-secondary btn-sm" title="Rimuovi filtri">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="small">Azioni Rapide</label>
                                    <div>
                                        <?php if ($tab_attivo == 'IN_LAVORAZIONE'): ?>
                                            <a href="vista_lanci.php?tab=IN_LAVORAZIONE&avanzamento_min=0&avanzamento_max=25" class="btn btn-outline-danger btn-sm" title="Lanci con poco avanzamento">
                                                <i class="fas fa-exclamation"></i> < 25%
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <!-- Statistiche del Tab -->
                    <?php if (!empty($lanci)): ?>
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card border-left-primary h-100">
                                    <div class="card-body py-3">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Risultati</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($lanci) ?></div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-list fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-left-success h-100">
                                    <div class="card-body py-3">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Totale Paia</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format(array_sum(array_column($lanci, 'totale_paia'))) ?></div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-boxes fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-left-info h-100">
                                    <div class="card-body py-3">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Paia Completate</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format(array_sum(array_column($lanci, 'paia_completate'))) ?></div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-check fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-left-warning h-100">
                                    <div class="card-body py-3">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                    <?= $tab_attivo == 'IN_PREPARAZIONE' ? 'Media Paia per Lancio' : 'Avanzamento Medio' ?>
                                                </div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php 
                                                    if ($tab_attivo == 'IN_PREPARAZIONE') {
                                                        echo count($lanci) > 0 ? number_format(array_sum(array_column($lanci, 'totale_paia')) / count($lanci)) : '0';
                                                    } else {
                                                        $totale_paia = array_sum(array_column($lanci, 'totale_paia'));
                                                        $paia_completate = array_sum(array_column($lanci, 'paia_completate'));
                                                        $avg = $totale_paia > 0 ? round(($paia_completate / $totale_paia) * 100, 1) : 0;
                                                        echo $avg . '%';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-chart-pie fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Tabella Lanci -->
                    <div class="card shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-list mr-2"></i>
                                <?php
                                $tab_titles = [
                                    'IN_PREPARAZIONE' => 'Lanci in Preparazione',
                                    'IN_LAVORAZIONE' => 'Lanci in Lavorazione',
                                    'COMPLETATI' => 'Lanci Completati'
                                ];
                                echo $tab_titles[$tab_attivo];
                                ?> (<?= count($lanci) ?>)
                            </h6>
                            <div class="small text-muted">
                                <?php if ($filtro_laboratorio || $filtro_data_da || $filtro_data_a): ?>
                                    <i class="fas fa-filter mr-1"></i>Filtri attivi
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($lanci)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-rocket fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Nessun lancio trovato</h5>
                                    <p class="text-muted">
                                        <?php if ($filtro_laboratorio || $filtro_data_da || $filtro_data_a): ?>
                                            Nessun lancio corrisponde ai criteri di ricerca in questo stato.
                                            <br><a href="?tab=<?= $tab_attivo ?>" class="btn btn-outline-primary btn-sm mt-2">Rimuovi filtri</a>
                                        <?php else: ?>
                                            Non ci sono lanci in stato "<?= str_replace('_', ' ', $tab_attivo) ?>".
                                            <?php if ($tab_attivo == 'IN_PREPARAZIONE'): ?>
                                                <br><a href="crea_lancio.php" class="btn btn-success btn-sm mt-2">
                                                    <i class="fas fa-plus mr-1"></i>Crea nuovo lancio
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Lancio</th>
                                                <th>Laboratorio</th>
                                                <th>Ciclo Fasi</th>
                                                <th>Data / Età</th>
                                                <th>Articoli/Paia</th>
                                                <?php if ($tab_attivo != 'IN_PREPARAZIONE'): ?>
                                                    <th>Avanzamento</th>
                                                <?php endif; ?>
                                                <th>Fasi</th>
                                                <th>Note</th>
                                                <th>Azioni</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($lanci as $lancio): ?>
                                                <?php
                                                // Badge colore per stato
                                                $badge_class = match($lancio['stato_generale']) {
                                                    'IN_PREPARAZIONE' => 'badge-secondary',
                                                    'LANCIATO' => 'badge-info',
                                                    'IN_LAVORAZIONE' => 'badge-success',
                                                    'COMPLETO' => 'badge-primary',
                                                    'SOSPESO' => 'badge-warning',
                                                    default => 'badge-secondary'
                                                };
                                                
                                                // Formatta ciclo fasi
                                                $fasi = explode(';', $lancio['ciclo_fasi']);
                                                $fasi = array_map('trim', $fasi);
                                                $ciclo_formattato = count($fasi) <= 3 ? 
                                                    implode(' → ', $fasi) : 
                                                    implode(' → ', array_slice($fasi, 0, 3)) . '... (+' . (count($fasi) - 3) . ')';
                                                
                                                // Evidenziazione righe in base all'avanzamento (solo per lanci in lavorazione)
                                                $row_class = '';
                                                if ($tab_attivo == 'IN_LAVORAZIONE') {
                                                  
                                                } elseif ($tab_attivo == 'COMPLETATI') {
                                                    $row_class = 'table-light';
                                                }
                                                ?>
                                                <tr class="<?= $row_class ?>">
                                                    <td>
                                                        <strong><?= htmlspecialchars($lancio['numero_lancio']) ?></strong>
                                                        <br>
                                                        <small class="text-muted">ID: <?= $lancio['id'] ?></small>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars($lancio['nome_laboratorio'] ?? 'Non assegnato') ?>
                                                    </td>
                                                    <td class="small">
                                                        <?= htmlspecialchars($ciclo_formattato) ?>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <?= date('d/m/Y', strtotime($lancio['data_lancio'])) ?>
                                                        </div>
                                                        <small class="<?= $lancio['giorni_dal_lancio'] > 60 ? 'text-danger' : ($lancio['giorni_dal_lancio'] > 30 ? 'text-warning' : 'text-muted') ?>">
                                                            <?= $lancio['giorni_dal_lancio'] ?> giorni fa
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <div class="small">
                                                            <strong><?= $lancio['numero_articoli'] ?></strong> articoli<br>
                                                            <strong><?= number_format($lancio['totale_paia']) ?></strong> paia<br>
                                                            <?php if ($tab_attivo != 'IN_PREPARAZIONE'): ?>
                                                                <span class="text-success">
                                                                    <strong><?= number_format($lancio['paia_completate']) ?></strong> completate
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <?php if ($tab_attivo != 'IN_PREPARAZIONE'): ?>
                                                        <td>
                                                            <?php
                                                            $progress_color = 'bg-success';
                                                            if ($lancio['percentuale_generale'] < 25) {
                                                                $progress_color = 'bg-danger';
                                                            } elseif ($lancio['percentuale_generale'] < 50) {
                                                                $progress_color = 'bg-warning';
                                                            }
                                                            ?>
                                                            <div class="progress mb-1" style="height: 15px; min-width: 60px;">
                                                                <div class="progress-bar <?= $progress_color ?>" 
                                                                     style="width: <?= $lancio['percentuale_generale'] ?>%"
                                                                     title="<?= $lancio['percentuale_generale'] ?>% paia completate">
                                                                </div>
                                                            </div>
                                                            <small class="text-center d-block"><?= $lancio['percentuale_generale'] ?>%</small>
                                                            <?php if ($lancio['ultimo_aggiornamento']): ?>
                                                                <small class="text-muted d-block">
                                                                    Agg: <?= date('d/m', strtotime($lancio['ultimo_aggiornamento'])) ?>
                                                                </small>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endif; ?>
                                                    <td class="text-center">
                                                        <div class="small mb-1">
                                                            <?php if ($lancio['fasi_in_corso'] > 0): ?>
                                                                <span class="badge badge-warning">
                                                                    <i class="fas fa-play mr-1"></i><?= $lancio['fasi_in_corso'] ?>
                                                                </span>
                                                            <?php endif; ?>
                                                            <?php if ($lancio['fasi_completate'] > 0): ?>
                                                                <span class="badge badge-success ml-1">
                                                                    <i class="fas fa-check mr-1"></i><?= $lancio['fasi_completate'] ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="text-muted small">
                                                            / <?= $lancio['numero_fasi'] ?> fasi totali
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if ($lancio['numero_note'] > 0): ?>
                                                            <span class="badge badge-info">
                                                                <i class="fas fa-sticky-note mr-1"></i>
                                                                <?= $lancio['numero_note'] ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="dettaglio_lancio.php?id=<?= $lancio['id'] ?>" 
                                                               class="btn btn-outline-info btn-sm" 
                                                               title="Visualizza dettagli">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <?php if ($tab_attivo != 'COMPLETATI'): ?>
                                                                <a href="crea_lancio.php?id=<?= $lancio['id'] ?>" 
                                                                   class="btn btn-outline-success btn-sm" 
                                                                   title="Modifica">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="btn btn-outline-secondary btn-sm disabled" 
                                                                      title="Lancio completato - Non modificabile">
                                                                    <i class="fas fa-lock"></i>
                                                                </span>
                                                            <?php endif; ?>
                                                            <a href="lista_lanci.php?tab=<?= $tab_attivo ?>&delete=<?= $lancio['id'] ?>" 
                                                               class="btn btn-outline-danger btn-sm" 
                                                               title="Elimina"
                                                               onclick="return confirm('Sei sicuro di voler eliminare questo lancio?\\n\\nNota: verranno eliminati anche tutti gli avanzamenti, le note e i dati delle fasi associate.')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                </div>
            </div>
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
    
    <style>
        /* Personalizzazione tab migliorata */
        .nav-tabs {
            border-bottom: 1px solid #e3e6f0 !important;
            background: linear-gradient(135deg, #f8f9fc 0%, #eaecf4 100%);
        }

        .nav-tabs .nav-link {
            border: none !important;
            color: #5a5c69 !important;
            padding: 1rem 1.5rem !important;
            font-weight: 600 !important;
            font-size: 0.9rem !important;
            border-radius: 0 !important;
            position: relative;
            transition: all 0.3s ease;
            background: transparent;
        }

        .nav-tabs .nav-link::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, #4e73df, #36b9cc);
            transition: width 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            color: #4e73df !important;
            background: rgba(78, 115, 223, 0.05) !important;
            transform: translateY(-1px);
        }

        .nav-tabs .nav-link:hover::before {
            width: 100%;
        }

        .nav-tabs .nav-link.active {
            color: #ffffff !important;
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%) !important;
            border-radius: 0.5rem 0.5rem 0 0 !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.3);
        }

        .nav-tabs .nav-link.active::before {
            width: 100% !important;
            background: #ffffff;
            height: 2px;
            bottom: -1px;
        }

        .nav-tabs .nav-link .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            font-weight: 500;
        }

        .nav-tabs .nav-link.active .badge {
            background-color: rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
        }

        .nav-item .active {
            width: 105% !important;
        }
    </style>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-submit form quando si cambiano i filtri principali
        const selectElements = document.querySelectorAll('#laboratorio');
        selectElements.forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
        
        // Salvataggio automatico del tab attivo
        const currentTab = '<?= $tab_attivo ?>';
        localStorage.setItem('lista_lanci_last_tab', currentTab);
        
        // Gestione dello stato dei filtri per tab
        const form = document.querySelector('form');
        if (form) {
            // Carica filtri salvati per il tab corrente
            const savedFilters = localStorage.getItem(`lista_lanci_filters_${currentTab}`);
            if (savedFilters && !window.location.search.includes('laboratorio')) {
                const filters = JSON.parse(savedFilters);
                Object.keys(filters).forEach(key => {
                    const input = document.querySelector(`[name="${key}"]`);
                    if (input && !input.value && key !== 'tab') {
                        input.value = filters[key];
                    }
                });
            }
            
            // Salva filtri correnti per il tab
            form.addEventListener('submit', function() {
                const formData = new FormData(this);
                const filters = {};
                for (let [key, value] of formData.entries()) {
                    if (value && key !== 'tab') {
                        filters[key] = value;
                    }
                }
                localStorage.setItem(`lista_lanci_filters_${currentTab}`, JSON.stringify(filters));
            });
        }
        
        // Aggiunta di notifiche per cambio tab
        const tabLinks = document.querySelectorAll('.nav-link');
        tabLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (!this.classList.contains('active')) {
                    // Mostra indicatore di caricamento
                    const badge = this.querySelector('.badge');
                    if (badge) {
                        badge.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    }
                }
            });
        });
        
        // Evidenziazione dinamica delle righe
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const progressBar = row.querySelector('.progress-bar');
            if (progressBar) {
                const percentage = parseFloat(progressBar.parentElement.nextElementSibling.textContent);
                
                // Tooltip con informazioni dettagliate
                progressBar.setAttribute('title', 
                    `Avanzamento: ${percentage}%\nClicca per vedere i dettagli`
                );
                
                // Animazione al hover
                progressBar.addEventListener('mouseenter', function() {
                    this.style.transform = 'scaleY(1.2)';
                    this.style.transition = 'transform 0.2s';
                });
                
                progressBar.addEventListener('mouseleave', function() {
                    this.style.transform = 'scaleY(1)';
                });
            }
        });
        
        // Conferma per azioni sui lanci completati
        const lockedButtons = document.querySelectorAll('.btn.disabled');
        lockedButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                alert('Questo lancio è completato e non può essere modificato.');
            });
        });
    });
    </script>
</body>