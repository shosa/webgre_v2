<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';
require_once BASE_PATH . '/components/header.php';

// Filtro laboratorio specifico
$filtro_laboratorio = $_GET['laboratorio'] ?? '';

// Caricamento dati
try {
    $pdo = getDbInstance();

    // Carica tutti i laboratori per il filtro
    $stmt = $pdo->query("SELECT id, nome_laboratorio FROM scm_laboratori WHERE attivo = TRUE ORDER BY nome_laboratorio");
    $laboratori_filtro = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Query principale per i laboratori con i loro lanci
    $where_clause = "";
    $params = [];

    if (!empty($filtro_laboratorio) && is_numeric($filtro_laboratorio)) {
        $where_clause = "WHERE lab.id = ?";
        $params[] = $filtro_laboratorio;
    } else {
        $where_clause = "WHERE lab.attivo = TRUE";
    }

    $stmt = $pdo->prepare("
        SELECT 
            lab.id as laboratorio_id,
            lab.nome_laboratorio,
            lab.email,
            lab.username,
            lab.ultimo_accesso,
            COUNT(l.id) as totale_lanci,
            COUNT(CASE WHEN l.stato_generale = 'IN_PREPARAZIONE' THEN 1 END) as lanci_preparazione,
            COUNT(CASE WHEN l.stato_generale = 'LANCIATO' THEN 1 END) as lanci_lanciati,
            COUNT(CASE WHEN l.stato_generale = 'IN_LAVORAZIONE' THEN 1 END) as lanci_lavorazione,
            COUNT(CASE WHEN l.stato_generale = 'COMPLETO' THEN 1 END) as lanci_completi,
            MAX(av.data_aggiornamento) as ultimo_aggiornamento
        FROM scm_laboratori lab
        LEFT JOIN scm_lanci l ON lab.id = l.laboratorio_id
        LEFT JOIN scm_avanzamento av ON l.id = av.lancio_id
        $where_clause
        GROUP BY lab.id, lab.nome_laboratorio, lab.email, lab.username, lab.ultimo_accesso
        ORDER BY lab.nome_laboratorio
    ");
    $stmt->execute($params);
    $laboratori = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Per ogni laboratorio, calcola le paia corrette e carica i dettagli dei lanci
    foreach ($laboratori as &$laboratorio) {
        // Calcola le paia corrette per il laboratorio
        $stmt_paia = $pdo->prepare("
            SELECT 
                COALESCE(SUM(a.quantita_totale), 0) as totale_paia,
                COALESCE(SUM(a.quantita_completata), 0) as paia_completate
            FROM scm_lanci l
            LEFT JOIN scm_articoli_lancio a ON l.id = a.lancio_id
            WHERE l.laboratorio_id = ?
        ");
        $stmt_paia->execute([$laboratorio['laboratorio_id']]);
        $paia_stats = $stmt_paia->fetch(PDO::FETCH_ASSOC);

        $laboratorio['totale_paia'] = $paia_stats['totale_paia'];
        $laboratorio['paia_completate'] = $paia_stats['paia_completate'];

        // Carica i lanci del laboratorio
        $stmt_lanci = $pdo->prepare("
            SELECT 
                l.*,
                COUNT(DISTINCT a.id) as numero_articoli,
                COUNT(CASE WHEN f.stato_fase = 'COMPLETATA' THEN 1 END) as fasi_completate,
                COUNT(CASE WHEN f.stato_fase = 'IN_CORSO' THEN 1 END) as fasi_in_corso,
                COUNT(DISTINCT f.id) as totale_fasi,
                (SELECT COUNT(*) FROM scm_note WHERE lancio_id = l.id) as numero_note
            FROM scm_lanci l
            LEFT JOIN scm_articoli_lancio a ON l.id = a.lancio_id
            LEFT JOIN scm_fasi_lancio f ON l.id = f.lancio_id
            WHERE l.laboratorio_id = ?
            GROUP BY l.id
            ORDER BY l.data_lancio DESC
        ");
        $stmt_lanci->execute([$laboratorio['laboratorio_id']]);
        $laboratorio['lanci'] = $stmt_lanci->fetchAll(PDO::FETCH_ASSOC);

        // Per ogni lancio, calcola le paia corrette
        foreach ($laboratorio['lanci'] as &$lancio) {
            $stmt_paia_lancio = $pdo->prepare("
                SELECT 
                    COALESCE(SUM(quantita_totale), 0) as totale_paia_lancio,
                    COALESCE(SUM(quantita_completata), 0) as paia_completate_lancio
                FROM scm_articoli_lancio 
                WHERE lancio_id = ?
            ");
            $stmt_paia_lancio->execute([$lancio['id']]);
            $paia_lancio = $stmt_paia_lancio->fetch(PDO::FETCH_ASSOC);

            $lancio['totale_paia_lancio'] = $paia_lancio['totale_paia_lancio'];
            $lancio['paia_completate_lancio'] = $paia_lancio['paia_completate_lancio'];
        }
    }

} catch (PDOException $e) {
    $error = 'Errore database: ' . ($debug ? $e->getMessage() : 'Errore generico');
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
                            <div class="mr-3 bg-gradient-info text-white p-3 rounded shadow-sm">
                                <i class="fas fa-building fa-2x"></i>
                            </div>
                            <div>
                                <h1 class="h3 mb-0 text-gray-800">Vista per Laboratorio</h1>
                                <p class="mb-0 text-gray-600">Visualizzazione lanci raggruppati per laboratorio</p>
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
                        <li class="breadcrumb-item active">Controllo Laboratorio</li>
                    </ol>

                    <!-- Filtri -->
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-filter mr-2"></i>
                                Filtri
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <form method="GET" class="row align-items-end">
                                <div class="col-md-4">
                                    <label for="laboratorio" class="small">Laboratorio</label>
                                    <select class="form-control form-control-sm" id="laboratorio" name="laboratorio">
                                        <option value="">Tutti i laboratori</option>
                                        <?php foreach ($laboratori_filtro as $lab): ?>
                                            <option value="<?= $lab['id'] ?>" <?= ($filtro_laboratorio == $lab['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($lab['nome_laboratorio']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-sm mr-1">
                                        <i class="fas fa-search"></i> Filtra
                                    </button>
                                    <a href="vista_laboratori.php" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <!-- Laboratori e loro lanci -->
                    <?php if (empty($laboratori)): ?>
                        <div class="card shadow">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Nessun laboratorio trovato</h5>
                                <p class="text-muted">Non ci sono laboratori che corrispondono ai criteri di ricerca</p>
                                <?php if ($filtro_laboratorio): ?>
                                    <a href="vista_laboratori.php" class="btn btn-outline-primary">Mostra tutti</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($laboratori as $laboratorio): ?>
                            <div class="card shadow mb-4">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0">
                                                <i class="fas fa-building mr-2 text-primary"></i>
                                                <?= htmlspecialchars($laboratorio['nome_laboratorio']) ?>
                                            </h5>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($laboratorio['email'] ?? 'Nessuna email') ?> |
                                                Username: <code><?= htmlspecialchars($laboratorio['username']) ?></code>
                                            </small>
                                        </div>
                                        <div class="text-right">
                                            <div class="small">
                                                <strong><?= $laboratorio['totale_lanci'] ?></strong> lanci totali |
                                                <strong><?= number_format($laboratorio['totale_paia']) ?></strong> paia totali
                                            </div>
                                            <div class="small text-muted">
                                                <?php if ($laboratorio['ultimo_aggiornamento']): ?>
                                                    Ultimo aggiornamento:
                                                    <?= date('d/m/Y', strtotime($laboratorio['ultimo_aggiornamento'])) ?>
                                                <?php else: ?>
                                                    Nessun aggiornamento
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Statistiche laboratorio -->
                                <div class="card-body border-bottom">
                                    <div class="row text-center">
                                        <div class="col">
                                            <div class="h5 mb-0 text-secondary">
                                                <?= $laboratorio['lanci_preparazione'] + $laboratorio['lanci_lanciati'] ?>
                                            </div>
                                            <div class="small text-muted">In Preparazione</div>
                                        </div>
                                        <div class="col">
                                            <div class="h5 mb-0 text-warning"><?= $laboratorio['lanci_lavorazione'] ?></div>
                                            <div class="small text-muted">In Lavorazione</div>
                                        </div>
                                        <div class="col">
                                            <div class="h5 mb-0 text-success"><?= $laboratorio['lanci_completi'] ?></div>
                                            <div class="small text-muted">Completati</div>
                                        </div>
                                        <div class="col">
                                            <div class="h5 mb-0 text-info"><?= number_format($laboratorio['paia_completate']) ?>
                                            </div>
                                            <div class="small text-muted">Paia Completate</div>
                                        </div>
                                        <div class="col">
                                            <?php
                                            $perc_lab = $laboratorio['totale_paia'] > 0 ?
                                                round(($laboratorio['paia_completate'] / $laboratorio['totale_paia']) * 100, 1) : 0;
                                            ?>
                                            <div class="h5 mb-0 text-primary"><?= $perc_lab ?>%</div>
                                            <div class="small text-muted">Avanzamento</div>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($laboratorio['lanci_lavorazione'] > 0): ?>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                            onclick="generaPdfLanci(<?= $laboratorio['laboratorio_id'] ?>, '<?= htmlspecialchars($laboratorio['nome_laboratorio']) ?>')"
                                            title="Genera PDF lanci in lavorazione">
                                            <i class="fas fa-file-pdf mr-1"></i>
                                            PDF Lanci in Lavorazione (<?= $laboratorio['lanci_lavorazione'] ?>)
                                        </button>
                                    </div>
                                <?php endif; ?>
                                <!-- Lanci del laboratorio -->
                                <div class="card-body p-0">
                                    <?php if (empty($laboratorio['lanci'])): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-rocket fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">Nessun lancio assegnato a questo laboratorio</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Lancio</th>
                                                        <th>Data</th>
                                                        <th>Stato</th>
                                                        <th>Articoli/Paia</th>
                                                        <th>Avanzamento</th>
                                                        <th>Fasi</th>
                                                        <th>Note</th>
                                                        <th>Azioni</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($laboratorio['lanci'] as $lancio): ?>
                                                        <?php
                                                        $percentuale_lancio = $lancio['totale_paia_lancio'] > 0 ?
                                                            round(($lancio['paia_completate_lancio'] / $lancio['totale_paia_lancio']) * 100, 1) : 0;

                                                        switch ($lancio['stato_generale']) {
                                                            case 'IN_PREPARAZIONE':
                                                                $badge_class = 'badge-secondary';
                                                                break;
                                                            case 'LANCIATO':
                                                                $badge_class = 'badge-info';
                                                                break;
                                                            case 'IN_LAVORAZIONE':
                                                                $badge_class = 'badge-success';
                                                                break;
                                                            case 'COMPLETO':
                                                                $badge_class = 'badge-primary';
                                                                break;
                                                            case 'SOSPESO':
                                                                $badge_class = 'badge-warning';
                                                                break;
                                                            default:
                                                                $badge_class = 'badge-secondary';
                                                                break;
                                                        }
                                                        ?>
                                                        <tr>
                                                            <td>
                                                                <strong><?= htmlspecialchars($lancio['numero_lancio']) ?></strong>
                                                                <br><small class="text-muted">ID: <?= $lancio['id'] ?></small>
                                                            </td>
                                                            <td><?= date('d/m/Y', strtotime($lancio['data_lancio'])) ?></td>
                                                            <td>
                                                                <span class="badge <?= $badge_class ?>">
                                                                    <?= str_replace('_', ' ', $lancio['stato_generale']) ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="small">
                                                                    <strong><?= $lancio['numero_articoli'] ?></strong> articoli<br>
                                                                    <strong><?= number_format($lancio['totale_paia_lancio']) ?></strong>
                                                                    paia<br>
                                                                    <span class="text-success">
                                                                        <strong><?= number_format($lancio['paia_completate_lancio']) ?></strong>
                                                                        completate
                                                                    </span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="progress mb-1" style="height: 15px;">
                                                                    <div class="progress-bar bg-success"
                                                                        style="width: <?= $percentuale_lancio ?>%">
                                                                    </div>
                                                                </div>
                                                                <small class="text-center d-block"><?= $percentuale_lancio ?>%</small>
                                                            </td>
                                                            <td class="text-center">
                                                                <div class="small">
                                                                    <?php if ($lancio['fasi_in_corso'] > 0): ?>
                                                                        <span class="badge badge-warning">
                                                                            <i class="fas fa-play mr-1"></i><?= $lancio['fasi_in_corso'] ?>
                                                                        </span>
                                                                    <?php endif; ?>
                                                                    <?php if ($lancio['fasi_completate'] > 0): ?>
                                                                        <span class="badge badge-success ml-1">
                                                                            <i
                                                                                class="fas fa-check mr-1"></i><?= $lancio['fasi_completate'] ?>
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="text-muted small">
                                                                    / <?= $lancio['totale_fasi'] ?> totali
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
                                                                    <button type="button" class="btn btn-outline-info btn-sm"
                                                                        title="Matrice Avanzamento"
                                                                        onclick="apriMatriceAvanzamento(<?= $lancio['id'] ?>, '<?= htmlspecialchars($lancio['numero_lancio']) ?>')">
                                                                        <i class="fas fa-th"></i>
                                                                    </button>
                                                                    <a href="crea_lancio.php?id=<?= $lancio['id'] ?>"
                                                                        class="btn btn-outline-success btn-sm" title="Modifica">
                                                                        <i class="fas fa-edit"></i>
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
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            </div>
            <script src="<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
            <script src="<?php echo BASE_URL ?>/js/sb-admin-2.min.js"></script>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
            <SCRIPT>
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

                function generaPdfLanci(laboratorioId, nomeLaboratorio) {
                    // Conferma prima di generare
                    if (confirm('Vuoi generare il PDF con i lanci in lavorazione per ' + nomeLaboratorio + '?')) {
                        // Apri il PDF in una nuova finestra/tab
                        window.open('pdf_lab_lanc.php?laboratorio_id=' + laboratorioId, '_blank');
                    }
                }
            </SCRIPT>
        </div>
    </div>
</body>
<!-- Modal Matrice Avanzamento -->
<div class="modal fade" id="modalMatriceAvanzamento" tabindex="-1" role="dialog" aria-labelledby="modalMatriceLabel"
    aria-hidden="true">
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

    .table-matrice td,
    .table-matrice th {
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