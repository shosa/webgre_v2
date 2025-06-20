<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';
require_once BASE_PATH . '/components/header.php';

// Tab attivo - default su IN_LAVORAZIONE
$tab_attivo = $_GET['tab'] ?? 'IN_LAVORAZIONE';

// Validazione tab
$tab_validi = ['IN_PREPARAZIONE', 'IN_LAVORAZIONE', 'COMPLETATI'];
if (!in_array($tab_attivo, $tab_validi)) {
    $tab_attivo = 'IN_LAVORAZIONE';
}

// Filtri (senza stato generale che è gestito dai tab)
$filtro_laboratorio = $_GET['laboratorio'] ?? '';
$filtro_data_da = $_GET['data_da'] ?? '';
$filtro_data_a = $_GET['data_a'] ?? '';
$filtro_avanzamento_min = $_GET['avanzamento_min'] ?? '';
$filtro_avanzamento_max = $_GET['avanzamento_max'] ?? '';
$filtro_cerca = $_GET['cerca'] ?? '';
$ordinamento = $_GET['ord'] ?? 'data_desc';

// Caricamento dati
try {
    $pdo = getDbInstance();

    // Carica laboratori per filtro
    $stmt = $pdo->query("SELECT id, nome_laboratorio FROM scm_laboratori ORDER BY nome_laboratorio");
    $laboratori_filtro = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    if (!empty($filtro_cerca)) {
        $where_conditions[] = "(l.numero_lancio LIKE ? OR l.articoli LIKE ? OR l.note LIKE ? OR lab.nome_laboratorio LIKE ?)";
        $cerca_param = "%{$filtro_cerca}%";
        $params = array_merge($params, [$cerca_param, $cerca_param, $cerca_param, $cerca_param]);
    }

    $where_clause = "WHERE " . implode(" AND ", $where_conditions);

    // Ordinamento
    $order_clause = match ($ordinamento) {
        'data_asc' => 'ORDER BY l.data_lancio ASC',
        'data_desc' => 'ORDER BY l.data_lancio DESC',
        'numero_asc' => 'ORDER BY l.numero_lancio ASC',
        'numero_desc' => 'ORDER BY l.numero_lancio DESC',
        'laboratorio' => 'ORDER BY lab.nome_laboratorio, l.data_lancio DESC',
        'avanzamento' => 'ORDER BY percentuale_generale DESC',
        default => 'ORDER BY l.data_lancio DESC'
    };

    // Query principale
    $stmt = $pdo->prepare("
        SELECT 
            l.*,
            lab.nome_laboratorio,
            lab.email as lab_email,
            COUNT(DISTINCT a.id) as numero_articoli,
            COUNT(CASE WHEN f.stato_fase = 'COMPLETATA' THEN 1 END) as fasi_completate,
            COUNT(CASE WHEN f.stato_fase = 'IN_CORSO' THEN 1 END) as fasi_in_corso,
            COUNT(DISTINCT f.id) as totale_fasi,
            (SELECT COUNT(*) FROM scm_note WHERE lancio_id = l.id) as numero_note,
            MAX(av.data_aggiornamento) as ultimo_aggiornamento,
            (SELECT COUNT(DISTINCT nome_fase) FROM scm_fasi_lancio WHERE lancio_id = l.id) as fasi_ciclo_count
        FROM scm_lanci l
        LEFT JOIN scm_laboratori lab ON l.laboratorio_id = lab.id
        LEFT JOIN scm_articoli_lancio a ON l.id = a.lancio_id
        LEFT JOIN scm_fasi_lancio f ON l.id = f.lancio_id
        LEFT JOIN scm_avanzamento av ON l.id = av.lancio_id
        $where_clause
        GROUP BY l.id
        $order_clause
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

    // Applica filtro avanzamento se specificato
    if (!empty($filtro_avanzamento_min) || !empty($filtro_avanzamento_max)) {
        $lanci = array_filter($lanci, function ($lancio) use ($filtro_avanzamento_min, $filtro_avanzamento_max) {
            $percentuale = $lancio['percentuale_generale'];
            $min_ok = empty($filtro_avanzamento_min) || $percentuale >= $filtro_avanzamento_min;
            $max_ok = empty($filtro_avanzamento_max) || $percentuale <= $filtro_avanzamento_max;
            return $min_ok && $max_ok;
        });
    }

    // Statistiche sui risultati
    $totale_risultati = count($lanci);
    $totale_paia_risultati = array_sum(array_column($lanci, 'totale_paia'));
    $paia_completate_risultati = array_sum(array_column($lanci, 'paia_completate'));
    $avanzamento_medio = $totale_paia_risultati > 0 ?
        round(($paia_completate_risultati / $totale_paia_risultati) * 100, 1) : 0;

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
                            <div class="mr-3 bg-gradient-warning text-white p-3 rounded shadow-sm">
                                <i class="fas fa-search fa-2x"></i>
                            </div>
                            <div>
                                <h1 class="h3 mb-0 text-gray-800">Vista per Lancio</h1>
                                <p class="mb-0 text-gray-600">Gestione e monitoraggio dei lanci di produzione</p>
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
                        <li class="breadcrumb-item active">Vista per Lanci</li>
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

                        <!-- Filtri per il tab corrente -->
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-warning">
                                <i class="fas fa-filter mr-2"></i>
                                Filtri di Ricerca
                            </h6>
                        </div>

                        <!-- Filtri per il tab corrente -->
                        <div class="card-body">
                            <form method="GET">
                                <input type="hidden" name="tab" value="<?= $tab_attivo ?>">

                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="cerca" class="small">Ricerca Generale</label>
                                        <input type="text" class="form-control form-control-sm" id="cerca" name="cerca"
                                            value="<?= htmlspecialchars($filtro_cerca) ?>"
                                            placeholder="Numero, articolo, note...">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="laboratorio" class="small">Laboratorio</label>
                                        <select class="form-control form-control-sm" id="laboratorio"
                                            name="laboratorio">
                                            <option value="">Tutti</option>
                                            <?php foreach ($laboratori_filtro as $lab): ?>
                                                <option value="<?= $lab['id'] ?>" <?= ($filtro_laboratorio == $lab['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($lab['nome_laboratorio']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="ord" class="small">Ordinamento</label>
                                        <select class="form-control form-control-sm" id="ord" name="ord">
                                            <option value="data_desc" <?= ($ordinamento == 'data_desc') ? 'selected' : '' ?>>Data ↓</option>
                                            <option value="data_asc" <?= ($ordinamento == 'data_asc') ? 'selected' : '' ?>>
                                                Data ↑</option>
                                            <option value="numero_asc" <?= ($ordinamento == 'numero_asc') ? 'selected' : '' ?>>Numero ↑</option>
                                            <option value="numero_desc" <?= ($ordinamento == 'numero_desc') ? 'selected' : '' ?>>Numero ↓</option>
                                            <option value="laboratorio" <?= ($ordinamento == 'laboratorio') ? 'selected' : '' ?>>Laboratorio</option>
                                            <?php if ($tab_attivo != 'IN_PREPARAZIONE'): ?>
                                                <option value="avanzamento" <?= ($ordinamento == 'avanzamento') ? 'selected' : '' ?>>Avanzamento ↓</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="small">&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-warning btn-sm mr-1">
                                                <i class="fas fa-search"></i> Cerca
                                            </button>
                                            <a href="?tab=<?= $tab_attivo ?>" class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-eraser"></i>
                                            </a>
                                        </div>
                                    </div>

                                </div>

                                <hr class="my-3">

                                <div class="row">
                                    <div class="col-md-2">
                                        <label for="data_da" class="small">Data Da</label>
                                        <input type="date" class="form-control form-control-sm" id="data_da"
                                            name="data_da" value="<?= htmlspecialchars($filtro_data_da) ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="data_a" class="small">Data A</label>
                                        <input type="date" class="form-control form-control-sm" id="data_a"
                                            name="data_a" value="<?= htmlspecialchars($filtro_data_a) ?>">
                                    </div>
                                    <?php if ($tab_attivo != 'IN_PREPARAZIONE'): ?>

                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Statistiche Risultati -->
                    <?php if (!empty($lanci)): ?>
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card border-left-primary h-100">
                                    <div class="card-body py-3">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Risultati</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totale_risultati ?>
                                                </div>
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
                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                    Totale Paia</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?= number_format($totale_paia_risultati) ?></div>
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
                                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Paia
                                                    Completate</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?= number_format($paia_completate_risultati) ?></div>
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
                                                    <?= $tab_attivo == 'IN_PREPARAZIONE' ?
                                                        ($totale_risultati > 0 ? number_format($totale_paia_risultati / $totale_risultati) : '0') :
                                                        $avanzamento_medio . '%' ?>
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

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <!-- Risultati -->
                    <div class="card shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-warning">
                                <i class="fas fa-table mr-2"></i>
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
                                <?php
                                $filtri_attivi = array_filter([
                                    $filtro_cerca,
                                    $filtro_laboratorio,
                                    $filtro_data_da,
                                    $filtro_data_a,
                                    $filtro_avanzamento_min,
                                    $filtro_avanzamento_max
                                ]);
                                if (!empty($filtri_attivi)):
                                    ?>
                                    <i class="fas fa-filter mr-1"></i><?= count($filtri_attivi) ?> filtri attivi
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($lanci)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Nessun lancio trovato</h5>
                                    <p class="text-muted">
                                        <?php if (!empty($filtri_attivi)): ?>
                                            Nessun lancio corrisponde ai criteri di ricerca in questo stato.
                                            <br><a href="?tab=<?= $tab_attivo ?>"
                                                class="btn btn-outline-warning btn-sm mt-2">Rimuovi filtri</a>
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
                                                <th>
                                                    <a href="?<?= http_build_query(array_merge($_GET, ['ord' => $ordinamento == 'numero_asc' ? 'numero_desc' : 'numero_asc'])) ?>"
                                                        class="text-decoration-none">
                                                        Lancio
                                                        <?= $ordinamento == 'numero_asc' ? '↑' : ($ordinamento == 'numero_desc' ? '↓' : '') ?>
                                                    </a>
                                                </th>
                                                <th>
                                                    <a href="?<?= http_build_query(array_merge($_GET, ['ord' => 'laboratorio'])) ?>"
                                                        class="text-decoration-none">
                                                        Laboratorio
                                                    </a>
                                                </th>
                                                <th>
                                                    <a href="?<?= http_build_query(array_merge($_GET, ['ord' => $ordinamento == 'data_asc' ? 'data_desc' : 'data_asc'])) ?>"
                                                        class="text-decoration-none">
                                                        Data
                                                        <?= $ordinamento == 'data_asc' ? '↑' : ($ordinamento == 'data_desc' ? '↓' : '') ?>
                                                    </a>
                                                </th>
                                                <th>Articoli/Paia</th>
                                                <?php if ($tab_attivo != 'IN_PREPARAZIONE'): ?>
                                                    <th>
                                                        <a href="?<?= http_build_query(array_merge($_GET, ['ord' => 'avanzamento'])) ?>"
                                                            class="text-decoration-none">
                                                            Avanzamento <?= $ordinamento == 'avanzamento' ? '↓' : '' ?>
                                                        </a>
                                                    </th>
                                                <?php endif; ?>
                                                <th>Azioni</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($lanci as $lancio): ?>
                                                <?php
                                                // Evidenziazione in base all'avanzamento (solo per lanci in lavorazione)
                                                $row_class = '';
                                                if ($tab_attivo == 'IN_LAVORAZIONE') {
                                                    
                                                } elseif ($tab_attivo == 'COMPLETATI') {
                                                    $row_class = 'table-light';
                                                }
                                                ?>
                                                <tr class="<?= $row_class ?>">
                                                    <td>
                                                        <strong><?= htmlspecialchars($lancio['numero_lancio']) ?></strong>
                                                        <br><small class="text-muted">ID: <?= $lancio['id'] ?></small>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars($lancio['nome_laboratorio'] ?? 'Non assegnato') ?>
                                                    </td>
                                                    <td>
                                                        <?= date('d/m/Y', strtotime($lancio['data_lancio'])) ?>
                                                        <?php if ($lancio['ultimo_aggiornamento']): ?>
                                                            <br><small class="text-muted">
                                                                Agg: <?= date('d/m', strtotime($lancio['ultimo_aggiornamento'])) ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="small">
                                                            <strong><?= $lancio['numero_articoli'] ?></strong> articoli<br>
                                                            <strong><?= number_format($lancio['totale_paia']) ?></strong>
                                                            paia<br>
                                                            <?php if ($tab_attivo != 'IN_PREPARAZIONE'): ?>
                                                                <span class="text-success">
                                                                    <strong><?= number_format($lancio['paia_completate']) ?></strong>
                                                                    completate
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <?php if ($tab_attivo != 'IN_PREPARAZIONE'): ?>
                                                        <td>
                                                            <div class="progress mb-1" style="height: 20px;">
                                                                <div class="progress-bar 
                                                                    <?= $lancio['percentuale_generale'] >= 90 ? 'bg-success' :
                                                                        ($lancio['percentuale_generale'] >= 50 ? 'bg-warning' : 'bg-danger') ?>"
                                                                    style="width: <?= $lancio['percentuale_generale'] ?>%">
                                                                    <?= $lancio['percentuale_generale'] ?>%
                                                                </div>
                                                            </div>
                                                            <small class="text-muted">
                                                                <?= number_format($lancio['paia_completate']) ?> /
                                                                <?= number_format($lancio['totale_paia']) ?>
                                                            </small>
                                                        </td>
                                                    <?php endif; ?>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button type="button" 
                                                                    class="btn btn-outline-info btn-sm" 
                                                                    title="Matrice Avanzamento"
                                                                    onclick="apriMatriceAvanzamento(<?= $lancio['id'] ?>, '<?= htmlspecialchars($lancio['numero_lancio']) ?>')">
                                                                <i class="fas fa-th"></i>
                                                            </button>
                                                          
                                                            <?php if ($tab_attivo != 'COMPLETATI'): ?>
                                                                <a href="crea_lancio.php?id=<?= $lancio['id'] ?>"
                                                                    class="btn btn-outline-success btn-sm" title="Modifica">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="btn btn-outline-secondary btn-sm disabled"
                                                                    title="Lancio completato - Non modificabile">
                                                                    <i class="fas fa-lock"></i>
                                                                </span>
                                                            <?php endif; ?>
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
              <script src="<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
            <script src="<?php echo BASE_URL ?>/js/sb-admin-2.min.js"></script>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
    
    <!-- Modal Matrice Avanzamento -->
    <div class="modal fade" id="modalMatriceAvanzamento" tabindex="-1" role="dialog" aria-labelledby="modalMatriceLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMatriceLabel">
                        <i class="fas fa-th mr-2"></i>
                        Matrice Avanzamento - <span id="numeroLancioModal"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div id="matriceContent">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Caricamento...</span>
                            </div>
                            <p class="mt-2">Caricamento matrice avanzamento...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                </div>
            </div>
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
        
        /* Stili per la matrice avanzamento nel modal */
        .matrice-cell-readonly {
            min-width: 80px;
            min-height: 50px;
            border: 2px solid #dee2e6;
            border-radius: 4px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            font-size: 0.75rem;
        }
        
        .stato-NON_INIZIATA { 
            background-color: #f8f9fa; 
            color: #6c757d; 
            border-color: #dee2e6;
        }
        .stato-IN_CORSO { 
            background-color: #fff3cd; 
            color: #856404; 
            border-color: #ffc107;
        }
        .stato-COMPLETATA { 
            background-color: #d1ecf1; 
            color: #0c5460; 
            border-color: #17a2b8;
        }
        .stato-SPEDITO { 
            background-color: #d4edda; 
            color: #155724; 
            border-color: #28a745; 
            font-weight: bold;
        }
        
        .header-articolo-modal {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            padding: 0.5rem;
            text-align: center;
            min-width: 120px;
        }
        .header-articolo-modal.completato {
            background: linear-gradient(45deg, #28a745, #1e7e34);
        }
        .header-fase-modal {
            background: linear-gradient(45deg, #28a745, #1e7e34);
            color: white;
            padding: 0.5rem;
            text-align: center;
            min-width: 100px;
        }
        
        .riga-completata-modal {
            background-color: #d4edda !important;
        }
        
        .table-matrice {
            margin-bottom: 0;
            font-size: 0.85rem;
        }
        
        .table-matrice td, .table-matrice th {
            border: 1px solid #dee2e6;
            vertical-align: middle;
            padding: 0.25rem;
        }
        
        .modal-lg {
            max-width: 80%;
        }
        
        @media (max-width: 768px) {
            .modal-lg {
                max-width: 95%;
            }
            .matrice-cell-readonly {
                min-width: 60px;
                min-height: 40px;
                font-size: 0.7rem;
            }
            .header-articolo-modal {
                min-width: 100px;
            }
            .header-fase-modal {
                min-width: 80px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Auto-submit form quando si cambiano i filtri
            const selectElements = document.querySelectorAll('#laboratorio, #ord');
            selectElements.forEach(select => {
                select.addEventListener('change', function () {
                    this.form.submit();
                });
            });

            // Gestione del form di ricerca con Enter
            const searchInput = document.getElementById('cerca');
            if (searchInput) {
                searchInput.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        this.form.submit();
                    }
                });
            }

            // Evidenziazione righe in base ai criteri
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const progressBar = row.querySelector('.progress-bar');
                if (progressBar) {
                    const percentage = parseFloat(progressBar.textContent);

                    // Aggiunta tooltip con informazioni dettagliate
                    progressBar.setAttribute('title',
                        `Avanzamento: ${percentage}%\nClicca per vedere i dettagli`
                    );

                    // Animazione al hover
                    progressBar.addEventListener('mouseenter', function () {
                        this.style.transform = 'scaleY(1.2)';
                        this.style.transition = 'transform 0.2s';
                    });

                    progressBar.addEventListener('mouseleave', function () {
                        this.style.transform = 'scaleY(1)';
                    });
                }
            });

            // Salvataggio automatico del tab attivo
            const currentTab = '<?= $tab_attivo ?>';
            localStorage.setItem('vista_lanci_last_tab', currentTab);

            // Gestione dello stato dei filtri per tab
            const form = document.querySelector('form');
            if (form) {
                // Carica filtri salvati per il tab corrente
                const savedFilters = localStorage.getItem(`vista_lanci_filters_${currentTab}`);
                if (savedFilters && !window.location.search.includes('cerca') && !window.location.search.includes('laboratorio')) {
                    const filters = JSON.parse(savedFilters);
                    Object.keys(filters).forEach(key => {
                        const input = document.querySelector(`[name="${key}"]`);
                        if (input && !input.value) {
                            input.value = filters[key];
                        }
                    });
                }

                // Salva filtri correnti per il tab
                form.addEventListener('submit', function () {
                    const formData = new FormData(this);
                    const filters = {};
                    for (let [key, value] of formData.entries()) {
                        if (value && key !== 'tab') {
                            filters[key] = value;
                        }
                    }
                    localStorage.setItem(`vista_lanci_filters_${currentTab}`, JSON.stringify(filters));
                });
            }

            // Conferma per azioni sui lanci completati
            const lockedButtons = document.querySelectorAll('.btn.disabled');
            lockedButtons.forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                });
            });

            // Aggiunta di notifiche per cambio tab
            const tabLinks = document.querySelectorAll('.nav-link');
            tabLinks.forEach(link => {
                link.addEventListener('click', function () {
                    if (!this.classList.contains('active')) {
                        // Mostra indicatore di caricamento
                        const badge = this.querySelector('.badge');
                        if (badge) {
                            badge.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                        }
                    }
                });
            });
        });

        // Funzione per aprire il modal della matrice avanzamento
        function apriMatriceAvanzamento(lancioId, numeroLancio) {
            // Imposta il numero lancio nel titolo del modal
            document.getElementById('numeroLancioModal').textContent = numeroLancio;
            
            // Mostra il modal usando jQuery (come negli altri modali)
            $('#modalMatriceAvanzamento').modal('show');
            
            // Carica i dati della matrice via AJAX
            fetch('get_matrice_avanzamento.php?lancio_id=' + lancioId)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('matriceContent').innerHTML = html;
                })
                .catch(error => {
                    console.error('Errore nel caricamento:', error);
                    document.getElementById('matriceContent').innerHTML = 
                        '<div class="alert alert-danger m-3">' +
                        '<i class="fas fa-exclamation-triangle mr-2"></i>' +
                        'Errore nel caricamento della matrice. Riprova più tardi.' +
                        '</div>';
                });
        }
    </script>
</body>